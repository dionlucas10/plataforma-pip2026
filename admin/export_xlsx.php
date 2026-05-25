<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../app/helpers/auth.php';
require_Admin_Login();

$appBase = dirname(__DIR__) . '/app';
$config  = require $appBase . '/config/db.php';

$pdo = new PDO(
    "mysql:host={$config['host']};dbname={$config['dbname']};port={$config['port']};charset={$config['charset']}",
    $config['user'],
    $config['pass'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);


require_once dirname(__DIR__) . '/vendor/xlsxwriter/xlsxwriter.class.php';

$premiacaoId = (int)($_GET['premiacao_id'] ?? 0);
$faseId      = (int)($_GET['fase_id']      ?? 0);

if ($premiacaoId <= 0 || $faseId <= 0) {
    die('Parâmetros inválidos.');
}

// ── Metadados para o nome do arquivo ───────────────────────────────────
$stMeta = $pdo->prepare(
    'SELECT p.nome AS premiacao_nome, p.ano, pf.nome AS fase_nome
     FROM premiacao_fases pf
     INNER JOIN premiacoes p ON p.id = pf.premiacao_id
     WHERE pf.id = ? AND pf.premiacao_id = ? LIMIT 1'
);
$stMeta->execute([$faseId, $premiacaoId]);
$meta = $stMeta->fetch();
if (!$meta) { die('Fase não encontrada.'); }

$safeNome = preg_replace('/[^a-z0-9]+/i', '_', $meta['premiacao_nome']);
$safeFase = preg_replace('/[^a-z0-9]+/i', '_', $meta['fase_nome']);
$today    = date('Ymd');
$filename = 'classificados_' . $safeNome . '_' . $meta['ano'] . '_' . $safeFase . '_' . $today . '.xlsx';

// ── Query principal ───────────────────────────────────────────────────────────────
$sql = '
    SELECT
        pc.posicao,
        pc.origem,
        cat.nome                              AS categoria,
        n.nome_fantasia,
        COALESCE(n.municipio,    "")          AS municipio,
        COALESCE(n.estado,       "")          AS estado,
        COALESCE(n.site,         "")          AS site,
        COALESCE(n.linkedin,     "")          AS linkedin,
        COALESCE(n.instagram,    "")          AS instagram,
        COALESCE(n.facebook,     "")          AS facebook,
        COALESCE(n.tiktok,       "")          AS tiktok,
        COALESCE(n.youtube,      "")          AS youtube,
        COALESCE(e.nome,         "")          AS empreendedor_nome,
        COALESCE(e.sobrenome,    "")          AS empreendedor_sobrenome,
        COALESCE(e.email,        "")          AS empreendedor_email,
        COALESCE(e.celular,      "")          AS empreendedor_celular,
        COALESCE(n.cep,          "")          AS cep,
        COALESCE(n.rua,          "")          AS rua,
        COALESCE(n.numero,       "")          AS numero,
        COALESCE(n.complemento,  "")          AS complemento
    FROM premiacao_classificados pc
    INNER JOIN premiacao_categorias cat ON cat.id = pc.categoria_id
    INNER JOIN negocios n               ON n.id   = pc.negocio_id
    INNER JOIN premiacao_inscricoes pi  ON pi.negocio_id = pc.negocio_id AND pi.premiacao_id = ?
    INNER JOIN empreendedores e         ON e.id   = pi.empreendedor_id
    WHERE pc.fase_id = ?
    ORDER BY cat.ordem ASC, pc.posicao ASC
';
$st = $pdo->prepare($sql);
$st->execute([$premiacaoId, $faseId]);
$rows = $st->fetchAll();

// ── Montar XLSX ────────────────────────────────────────────────────────────────
$writer = new XLSXWriter();
$writer->setAuthor('PIP2026 Admin');

$header = [
    'Posição'       => 'string',
    'Categoria'     => 'string',
    'Nome Fantasia' => 'string',
    'Município'     => 'string',
    'Estado'        => 'string',
    'Site'          => 'string',
    'LinkedIn'      => 'string',
    'Instagram'     => 'string',
    'Facebook'      => 'string',
    'TikTok'        => 'string',
    'YouTube'       => 'string',
    'Nome'          => 'string',
    'Sobrenome'     => 'string',
    'E-mail'        => 'string',
    'Celular'       => 'string',
    'Origem Classificação'        => 'string',
    'CEP'           => 'string',
    'Rua'           => 'string',
    'Número'        => 'string',
    'Complemento'   => 'string',
];

$colWidths = [8,28,32,22,10,28,28,20,20,16,22,20,20,30,18,14,12,28,10,20];

$writer->writeSheetHeader('Classificados', $header, ['widths' => $colWidths]);

foreach ($rows as $r) {
    $posicao = ($r['posicao'] !== null && $r['posicao'] !== '') ? $r['posicao'] . 'º' : '';
    $writer->writeSheetRow('Classificados', [
        $posicao,
        $r['categoria'],
        $r['nome_fantasia'],
        $r['municipio'],
        $r['estado'],
        $r['site'],
        $r['linkedin'],
        $r['instagram'],
        $r['facebook'],
        $r['tiktok'],
        $r['youtube'],
        $r['empreendedor_nome'],
        $r['empreendedor_sobrenome'],
        $r['empreendedor_email'],
        $r['empreendedor_celular'],
        $r['origem'],
        (string)$r['cep'],
        $r['rua'],
        (string)$r['numero'],
        $r['complemento'],
    ]);
}

// ── Enviar como download ─────────────────────────────────────────────────────
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
$writer->writeToStdOut();
exit;