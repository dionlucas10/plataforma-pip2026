<?php
// /premiacao/votar_juri.php — Endpoint POST para registrar voto do júri
// Usa autenticação padrão do sistema ($_SESSION['user_role'] / $_SESSION['user_id'])
// Júri vota 1 vez por categoria, escolhendo 1 negócio classificado.

ob_start();
session_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);
require_once __DIR__ . '/../app/helpers/auth.php';

header('Content-Type: application/json; charset=utf-8');

// ── Verificar autenticação via sessão padrão ──────────────────────────────────
$role   = $_SESSION['user_role'] ?? '';
$userId = (int)($_SESSION['user_id'] ?? 0);

if (!$userId || $role !== 'juri') {
    http_response_code(401);
    echo json_encode(['ok' => false, 'erro' => 'Você precisa estar autenticado como jurado.']);
    exit;
}

$config = require __DIR__ . '/../app/config/db.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};port={$config['port']};charset={$config['charset']}",
        $config['user'], $config['pass'],
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'erro' => 'Erro na conexão com banco de dados']);
    exit;
}

function jsonErro(string $msg, int $code = 400): never {
    http_response_code($code);
    echo json_encode(['ok' => false, 'erro' => $msg]);
    exit;
}

function jsonOk(string $msg, array $extra = []): never {
    echo json_encode(array_merge(['ok' => true, 'msg' => $msg], $extra));
    exit;
}

// ── Validações básicas ────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonErro('Método não permitido.', 405);
}

// ── Extrair parâmetros ────────────────────────────────────────────────────────
$negocioId   = (int)($_POST['negocio_id']   ?? 0);
$faseId      = (int)($_POST['fase_id']      ?? 0);
$categoriaId = (int)($_POST['categoria_id'] ?? 0);
$redirect    = $_POST['redirect'] ?? null;

// Whitelist de redirecionamento
$redirectValidos = ['/admin/votos_tecnicos.php', '/premiacao/painel_juri.php'];
$redirect = in_array($redirect, $redirectValidos, true)
    ? $redirect
    : '/admin/votos_tecnicos.php';

if ($negocioId <= 0 || $faseId <= 0 || $categoriaId <= 0) {
    jsonErro('Dados inválidos.');
}

try {
    // ── Valida fase: deve ter permite_juri_final = 1 e estar em_andamento ───
    $stmtFase = $pdo->prepare("
        SELECT id, premiacao_id, data_inicio, data_fim
        FROM premiacao_fases
        WHERE id = ?
          AND permite_juri_final = 1
          AND status = 'em_andamento'
        LIMIT 1
    ");
    $stmtFase->execute([$faseId]);
    $fase = $stmtFase->fetch();

    if (!$fase) {
        jsonErro('Fase de votação do júri não encontrada ou encerrada.');
    }

    // ── Valida janela de tempo ────────────────────────────────────────────────
    date_default_timezone_set('America/Sao_Paulo');
    $agora = new DateTime('now');
    $ini   = DateTime::createFromFormat('Y-m-d H:i:s', $fase['data_inicio']);
    $fim   = DateTime::createFromFormat('Y-m-d H:i:s', $fase['data_fim']);

    if (!$ini || !$fim || $agora < $ini || $agora > $fim) {
        jsonErro('O período de votação do júri não está aberto no momento.');
    }

    // ── Valida categoria ──────────────────────────────────────────────────────
    $stmtCat = $pdo->prepare("
        SELECT id FROM premiacao_categorias
        WHERE id = ?
          AND premiacao_id = ?
        LIMIT 1
    ");
    $stmtCat->execute([$categoriaId, $fase['premiacao_id']]);
    if (!$stmtCat->fetch()) {
        jsonErro('Categoria não encontrada.');
    }

    // ── Valida negócio classificado na fase ───────────────────────────────────
    // Verifica na tabela premiacao_classificados (negocio_id + fase_id + categoria_id)
    $stmtCl = $pdo->prepare("
        SELECT cl.id
        FROM premiacao_classificados cl
        WHERE cl.negocio_id   = ?
          AND cl.fase_id      = ?
          AND cl.categoria_id = ?
        LIMIT 1
    ");
    $stmtCl->execute([$negocioId, $faseId, $categoriaId]);
    $classificado = $stmtCl->fetch();

    if (!$classificado) {
        jsonErro('Negócio não está classificado nesta fase/categoria.');
    }

    // ── Busca inscricao_id para registrar o voto ──────────────────────────────
    $stmtInsc = $pdo->prepare("
        SELECT pi.id
        FROM premiacao_inscricoes pi
        WHERE pi.negocio_id    = ?
          AND pi.premiacao_id  = ?
        LIMIT 1
    ");
    $stmtInsc->execute([$negocioId, $fase['premiacao_id']]);
    $inscricao = $stmtInsc->fetch();

    if (!$inscricao) {
        jsonErro('Inscrição do negócio não encontrada.');
    }

    $inscricaoId = (int)$inscricao['id'];

    // ── Verifica voto duplicado (1 voto por categoria por jurado) ─────────────
    $stmtDup = $pdo->prepare("
        SELECT COUNT(*) FROM premiacao_votos_juri
        WHERE fase_id      = ?
          AND categoria_id = ?
          AND user_id      = ?
    ");
    $stmtDup->execute([$faseId, $categoriaId, $userId]);
    if ((int)$stmtDup->fetchColumn() > 0) {
        jsonErro('Você já votou nesta categoria. Um voto por categoria é permitido.');
    }

    // ── Registra o voto do júri ───────────────────────────────────────────────
    $stmtInsert = $pdo->prepare("
        INSERT INTO premiacao_votos_juri
            (premiacao_id, fase_id, categoria_id, inscricao_id, user_id, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmtInsert->execute([
        $fase['premiacao_id'],
        $faseId,
        $categoriaId,
        $inscricaoId,
        $userId,
    ]);

    // ── Responde ──────────────────────────────────────────────────────────────
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
           && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    if ($isAjax) {
        jsonOk('Seu voto foi registrado com sucesso!');
    }

    $_SESSION['flash_success'] = 'Seu voto foi registrado com sucesso!';
    header('Location: ' . $redirect);
    exit;

} catch (PDOException $e) {
    error_log('Erro ao registrar voto de júri: ' . $e->getMessage());
    jsonErro('Erro ao processar seu voto. Tente novamente mais tarde.', 500);
}
