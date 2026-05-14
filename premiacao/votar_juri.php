<?php
// /premiacao/votar_juri.php — Endpoint POST para registrar voto do júri
// Estrutura espelhada de votar_tecnico.php (que funciona corretamente).
// Júri vota 1 vez por categoria na fase final.

ob_start();
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../app/helpers/premiacao_auth.php';

header('Content-Type: application/json; charset=utf-8');

$config = require __DIR__ . '/../app/config/db.php';
$pdo = new PDO(
    "mysql:host={$config['host']};dbname={$config['dbname']};port={$config['port']};charset={$config['charset']}",
    $config['user'], $config['pass'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

function jsonErro(string $msg, int $code = 400): never {
    http_response_code($code);
    echo json_encode(['ok' => false, 'erro' => $msg]);
    exit;
}

function jsonOk(string $msg, array $extra = []): never {
    echo json_encode(array_merge(['ok' => true, 'msg' => $msg], $extra));
    exit;
}

// ── Método ───────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonErro('Método não permitido.', 405);
}

// ── Autenticação: somente tipo = juri ──────────────────────────────────────
$actor = premiacao_current_actor();

if (!$actor || $actor['tipo'] !== 'juri') {
    jsonErro('Você precisa estar logado como jurado para votar.', 401);
}

$userId = $actor['id'];

$inscricaoId = (int)($_POST['inscricao_id'] ?? 0);
$faseId      = (int)($_POST['fase_id']      ?? 0);
$categoriaId = (int)($_POST['categoria_id'] ?? 0);
$redirect    = $_POST['redirect'] ?? '/admin/votos_tecnicos.php';

if ($inscricaoId <= 0 || $faseId <= 0) {
    jsonErro('Dados inválidos.');
}

// ── Valida fase: deve ter permite_juri_final = 1 ──────────────────────────
// O status 'em_andamento' NÃO é checado aqui — igual ao votar_tecnico.php
// A janela real é validada pelo intervalo de datas abaixo.
$stmtFase = $pdo->prepare("
    SELECT id, premiacao_id, data_inicio, data_fim, tipo_fase
    FROM premiacao_fases
    WHERE id = ?
      AND permite_juri_final = 1
    LIMIT 1
");
$stmtFase->execute([$faseId]);
$fase = $stmtFase->fetch(PDO::FETCH_ASSOC);

if (!$fase) {
    jsonErro('Fase de votação do júri não encontrada ou sem votação de júri habilitada.');
}

$agora = time();
$ini   = $fase['data_inicio'] ? strtotime($fase['data_inicio']) : 0;
$fim   = $fase['data_fim']    ? strtotime($fase['data_fim'])    : 0;
if (!$ini || !$fim || $agora < $ini || $agora > $fim) {
    jsonErro('A votação do júri não está aberta no momento.');
}

// ── Valida inscrição pertencente à mesma premiação ────────────────────────
$stmtInsc = $pdo->prepare("
    SELECT pi.id, pi.negocio_id, pi.categoria
    FROM premiacao_inscricoes pi
    WHERE pi.id = ?
      AND pi.premiacao_id = ?
    LIMIT 1
");
$stmtInsc->execute([$inscricaoId, $fase['premiacao_id']]);
$inscricao = $stmtInsc->fetch(PDO::FETCH_ASSOC);

if (!$inscricao) {
    jsonErro('Inscrição não encontrada.');
}

$negocioId = (int)$inscricao['negocio_id'];

// ── Busca categoria_id ───────────────────────────────────────────────────
if ($categoriaId <= 0) {
    // Se não foi enviado, busca pelo nome da categoria da inscrição
    $stmtCat = $pdo->prepare("
        SELECT id FROM premiacao_categorias
        WHERE premiacao_id = ? AND nome = ?
        LIMIT 1
    ");
    $stmtCat->execute([$fase['premiacao_id'], $inscricao['categoria']]);
    $categoriaRow = $stmtCat->fetch(PDO::FETCH_ASSOC);
    if (!$categoriaRow) {
        jsonErro('Categoria da inscrição não encontrada na premiação.');
    }
    $categoriaId = (int)$categoriaRow['id'];
}

// ── Valida que o negócio está classificado na fase final ──────────────────
$stmtFaseAnt = $pdo->prepare("
    SELECT id FROM premiacao_fases
    WHERE premiacao_id = ?
      AND tipo_fase = 'classificatoria'
    ORDER BY rodada DESC
    LIMIT 1
");
$stmtFaseAnt->execute([$fase['premiacao_id']]);
$faseAntRow = $stmtFaseAnt->fetch(PDO::FETCH_ASSOC);

if ($faseAntRow) {
    $stmtValida = $pdo->prepare("
        SELECT COUNT(*) FROM premiacao_classificados
        WHERE fase_id      = ?
          AND categoria_id = ?
          AND negocio_id   = ?
    ");
    $stmtValida->execute([(int)$faseAntRow['id'], $categoriaId, $negocioId]);
    if ((int)$stmtValida->fetchColumn() === 0) {
        jsonErro('Este negócio não é finalista.');
    }
}

// ── Verifica voto duplicado (1 voto por categoria por jurado) ───────────────
$stmtDup = $pdo->prepare("
    SELECT COUNT(*) FROM premiacao_votos_juri
    WHERE fase_id = ? AND categoria_id = ? AND user_id = ?
");
$stmtDup->execute([$faseId, $categoriaId, $userId]);
if ((int)$stmtDup->fetchColumn() > 0) {
    jsonErro('Você já votou nesta categoria. Um voto por categoria é permitido.');
}

// ── Registra o voto ────────────────────────────────────────────────────
ob_end_clean();

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

// ── Resposta ───────────────────────────────────────────────────────────────
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
       && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($isAjax) {
    jsonOk('Voto do júri registrado com sucesso!');
}

$_SESSION['flash_success'] = 'Seu voto foi registrado com sucesso!';
header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
exit;
