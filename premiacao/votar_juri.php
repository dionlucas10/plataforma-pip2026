<?php
// /premiacao/votar_juri.php — Endpoint para registrar voto do júri
// Aceita POST (form) e JSON. Também aceita GET com parâmetros (redirect de servidor).

ob_start();
session_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../app/helpers/premiacao_auth.php';

header('Content-Type: application/json; charset=utf-8');

function jsonErro(string $msg, int $code = 400): never {
    ob_end_clean();
    http_response_code($code);
    echo json_encode(['ok' => false, 'erro' => $msg]);
    exit;
}

function jsonOk(string $msg, array $extra = []): never {
    ob_end_clean();
    echo json_encode(array_merge(['ok' => true, 'msg' => $msg], $extra));
    exit;
}

// ── Aceita POST ou GET (GET pode ocorrer por redirect 301 do servidor) ──────
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$contentType = strtolower($_SERVER['CONTENT_TYPE'] ?? '');

// Extrai parâmetros tanto de POST quanto de GET quanto de JSON body
if (str_contains($contentType, 'application/json')) {
    $body        = json_decode(file_get_contents('php://input'), true) ?? [];
    $negocioId   = (int)($body['negocio_id']   ?? 0);
    $faseId      = (int)($body['fase_id']       ?? 0);
    $categoriaId = (int)($body['categoria_id']  ?? 0);
    $redirect    = $body['redirect'] ?? null;
} elseif ($method === 'POST') {
    $negocioId   = (int)($_POST['negocio_id']   ?? 0);
    $faseId      = (int)($_POST['fase_id']      ?? 0);
    $categoriaId = (int)($_POST['categoria_id'] ?? 0);
    $redirect    = $_POST['redirect'] ?? null;
} else {
    // GET: pode vir por redirect 301/302 do servidor web
    $negocioId   = (int)($_GET['negocio_id']   ?? 0);
    $faseId      = (int)($_GET['fase_id']      ?? 0);
    $categoriaId = (int)($_GET['categoria_id'] ?? 0);
    $redirect    = $_GET['redirect'] ?? null;
}

// Se não há parâmetros válidos, retorna erro de método (origem real do bug)
if ($negocioId <= 0 && $faseId <= 0) {
    jsonErro('Método não permitido.', 405);
}

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
       && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
$isJson      = str_contains($contentType, 'application/json');
$respondJson = $isAjax || $isJson;

// Whitelist de redirecionamento
$redirectValidos = ['/admin/votos_tecnicos.php', '/premiacao/painel_juri.php'];
$redirect = in_array($redirect, $redirectValidos, true)
    ? $redirect
    : '/admin/votos_tecnicos.php';

// ── Autenticação ─────────────────────────────────────────────────────────
$actor  = premiacao_current_actor();
$userId = null;

if ($actor) {
    $contexto = $actor['contexto'] ?? '';
    $tipo     = $actor['tipo']     ?? '';
    $role     = $actor['role']     ?? '';

    // 'backend' = tecnico ou juri; 'admin' = administrador
    if (
        $contexto === 'backend' && in_array($tipo, ['juri'], true)
        || $contexto === 'admin'
        || ($contexto === 'backend' && $role === 'juri')
        || ($contexto === 'backend' && $tipo === 'admin_user')
    ) {
        $userId = (int)$actor['id'];
    }
}

// Fallback: sessão padrão do admin/juri
if ($userId === null) {
    $roleSession = strtolower(trim((string)($_SESSION['user_role'] ?? '')));
    $uidSession  = (int)($_SESSION['user_id'] ?? 0);
    if ($uidSession > 0 && in_array($roleSession, ['juri', 'admin', 'superadmin'], true)) {
        $userId = $uidSession;
    }
}

if ($userId === null) {
    jsonErro('Você precisa estar autenticado como jurado.', 401);
}

if ($negocioId <= 0 || $faseId <= 0 || $categoriaId <= 0) {
    jsonErro('Dados inválidos. Informe negocio_id, fase_id e categoria_id.');
}

// ── Conexão com banco ──────────────────────────────────────────────────
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
    jsonErro('Erro na conexão com banco de dados.', 500);
}

try {
    // ── Valida fase ─────────────────────────────────────────────────────────
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

    // ── Valida janela de tempo ───────────────────────────────────────
    date_default_timezone_set('America/Sao_Paulo');
    $agora = new DateTime('now');
    $ini   = DateTime::createFromFormat('Y-m-d H:i:s', $fase['data_inicio']);
    $fim   = DateTime::createFromFormat('Y-m-d H:i:s', $fase['data_fim']);

    if (!$ini || !$fim || $agora < $ini || $agora > $fim) {
        jsonErro('O período de votação do júri não está aberto no momento.');
    }

    // ── Valida categoria ─────────────────────────────────────────────
    $stmtCat = $pdo->prepare("
        SELECT id FROM premiacao_categorias
        WHERE id = ? AND premiacao_id = ?
        LIMIT 1
    ");
    $stmtCat->execute([$categoriaId, $fase['premiacao_id']]);
    if (!$stmtCat->fetch()) {
        jsonErro('Categoria não encontrada.');
    }

    // ── Valida negócio classificado na fase ─────────────────────────────
    $stmtCl = $pdo->prepare("
        SELECT cl.id
        FROM premiacao_classificados cl
        WHERE cl.negocio_id   = ?
          AND cl.fase_id      = ?
          AND cl.categoria_id = ?
        LIMIT 1
    ");
    $stmtCl->execute([$negocioId, $faseId, $categoriaId]);
    if (!$stmtCl->fetch()) {
        jsonErro('Negócio não está classificado nesta fase/categoria.');
    }

    // ── Busca inscricao_id ─────────────────────────────────────────────
    $stmtInsc = $pdo->prepare("
        SELECT id FROM premiacao_inscricoes
        WHERE negocio_id = ? AND premiacao_id = ?
        LIMIT 1
    ");
    $stmtInsc->execute([$negocioId, $fase['premiacao_id']]);
    $inscricao = $stmtInsc->fetch();

    if (!$inscricao) {
        jsonErro('Inscrição do negócio não encontrada.');
    }

    $inscricaoId = (int)$inscricao['id'];

    // ── Verifica voto duplicado ─────────────────────────────────────────
    $stmtDup = $pdo->prepare("
        SELECT COUNT(*) FROM premiacao_votos_juri
        WHERE fase_id = ? AND categoria_id = ? AND user_id = ?
    ");
    $stmtDup->execute([$faseId, $categoriaId, $userId]);
    if ((int)$stmtDup->fetchColumn() > 0) {
        jsonErro('Você já votou nesta categoria. Um voto por categoria é permitido.');
    }

    // ── Registra o voto ────────────────────────────────────────────────
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

    // ── Resposta ──────────────────────────────────────────────────────────
    if ($respondJson) {
        jsonOk('Seu voto foi registrado com sucesso!');
    }

    $_SESSION['flash_success'] = 'Seu voto foi registrado com sucesso!';
    ob_end_clean();
    header('Location: ' . $redirect);
    exit;

} catch (PDOException $e) {
    error_log('Erro ao registrar voto de júri: ' . $e->getMessage());
    jsonErro('Erro ao processar seu voto. Tente novamente mais tarde.', 500);
}
