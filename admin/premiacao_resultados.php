<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../app/helpers/auth.php';
require_admin_login();

$appBase = dirname(__DIR__) . '/app';
$config  = require $appBase . '/config/db.php';

$dsn  = sprintf('mysql:host=%s;dbname=%s;port=%s;charset=%s',
    $config['host'], $config['dbname'], $config['port'], $config['charset']);
$opts = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
    $pdo = new PDO($dsn, $config['user'], $config['pass'], $opts);
} catch (PDOException $e) {
    die('Erro na conexão: ' . $e->getMessage());
}

function h(?string $v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
function dataBr(?string $d): string
{
    if (empty($d) || str_starts_with($d, '0000')) return '—';
    return date('d/m/Y H:i', strtotime($d));
}

// Função de query central: busca classificados completos de uma fase
function queryClassificados(PDO $pdo, int $premiacaoId, int $faseId): array
{
    $st = $pdo->prepare("
        SELECT
            pc.posicao,
            pc.origem,
            pc.apurado_em,
            cat.nome   AS categoria_nome,
            cat.ordem  AS categoria_ordem,
            n.nome_fantasia,
            n.municipio,
            n.estado,
            n.site,
            n.linkedin,
            n.instagram,
            n.facebook,
            n.tiktok,
            n.youtube,
            e.nome     AS empreendedor_nome,
            e.sobrenome AS empreendedor_sobrenome,
            e.email    AS empreendedor_email,
            e.celular  AS empreendedor_celular,
            na.logo_negocio,
            (SELECT COUNT(*) FROM premiacao_votos_populares vp WHERE vp.inscricao_id = pi.id AND vp.fase_id = pc.fase_id) AS votos_pop,
            (SELECT COUNT(*) FROM premiacao_votos_tecnicos vt WHERE vt.inscricao_id = pi.id AND vt.fase_id = pc.fase_id) AS votos_tec,
            (SELECT COUNT(*) FROM premiacao_votos_juri     vj WHERE vj.inscricao_id = pi.id AND vj.fase_id = pc.fase_id) AS votos_juri,
            COALESCE((SELECT prf.publicar_resultado FROM premiacao_resultados_finais prf WHERE prf.inscricao_id = pi.id AND prf.premiacao_id = :pid1 LIMIT 1),0) AS publicado,
            COALESCE((SELECT prf.qtd_voto_popular   FROM premiacao_resultados_finais prf WHERE prf.inscricao_id = pi.id AND prf.premiacao_id = :pid2 LIMIT 1),0) AS pub_voto_popular,
            COALESCE((SELECT prf.qtd_voto_tecnica   FROM premiacao_resultados_finais prf WHERE prf.inscricao_id = pi.id AND prf.premiacao_id = :pid3 LIMIT 1),0) AS pub_voto_tecnica,
            COALESCE((SELECT prf.qtd_voto_juri       FROM premiacao_resultados_finais prf WHERE prf.inscricao_id = pi.id AND prf.premiacao_id = :pid4 LIMIT 1),0) AS pub_voto_juri,
            COALESCE((SELECT prf.total_votos         FROM premiacao_resultados_finais prf WHERE prf.inscricao_id = pi.id AND prf.premiacao_id = :pid5 LIMIT 1),0) AS pub_total_votos
        FROM premiacao_classificados pc
        INNER JOIN premiacao_categorias cat ON cat.id = pc.categoria_id
        INNER JOIN negocios n               ON n.id   = pc.negocio_id
        INNER JOIN premiacao_inscricoes pi  ON pi.negocio_id = pc.negocio_id AND pi.premiacao_id = :pid6
        INNER JOIN empreendedores e         ON e.id   = pi.empreendedor_id
        LEFT  JOIN negocio_apresentacao na  ON na.negocio_id = n.id
        WHERE pc.fase_id = :fase_id
        ORDER BY cat.ordem ASC, pc.posicao ASC
    ");
    $st->execute([
        ':pid1'    => $premiacaoId,
        ':pid2'    => $premiacaoId,
        ':pid3'    => $premiacaoId,
        ':pid4'    => $premiacaoId,
        ':pid5'    => $premiacaoId,
        ':pid6'    => $premiacaoId,
        ':fase_id' => $faseId,
    ]);
    return $st->fetchAll();
}

$msg     = '';
$msgType = 'success';

// ── POST: publicar ganhadores da fase final ───────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao        = $_POST['acao'] ?? '';
    $premiacaoId = (int)($_POST['premiacao_id'] ?? 0);
    $faseId      = (int)($_POST['fase_id'] ?? 0);

    try {
        if ($acao === 'publicar_ganhadores' && $faseId > 0) {
            $stGan = $pdo->prepare("
                SELECT pc.negocio_id, pc.categoria_id
                FROM premiacao_classificados pc
                WHERE pc.fase_id = ? AND pc.posicao = 1
            ");
            $stGan->execute([$faseId]);
            $ganhadores = $stGan->fetchAll();

            if (empty($ganhadores)) {
                throw new Exception('Nenhum 1º colocado encontrado na fase final apurada. Verifique a apuração.');
            }

            $pdo->beginTransaction();

            foreach ($ganhadores as $g) {
                $stInsc = $pdo->prepare("
                    SELECT id FROM premiacao_inscricoes
                    WHERE negocio_id = ? AND premiacao_id = ?
                    LIMIT 1
                ");
                $stInsc->execute([$g['negocio_id'], $premiacaoId]);
                $inscricaoId = (int)($stInsc->fetchColumn() ?: 0);
                if (!$inscricaoId) continue;

                $stPop = $pdo->prepare("SELECT COUNT(*) FROM premiacao_votos_populares WHERE inscricao_id = ? AND premiacao_id = ?");
                $stPop->execute([$inscricaoId, $premiacaoId]);
                $qtdPopular = (int)$stPop->fetchColumn();

                $stTec = $pdo->prepare("SELECT COUNT(*) FROM premiacao_votos_tecnicos WHERE inscricao_id = ? AND premiacao_id = ?");
                $stTec->execute([$inscricaoId, $premiacaoId]);
                $qtdTecnica = (int)$stTec->fetchColumn();

                $stJuri = $pdo->prepare("SELECT COUNT(*) FROM premiacao_votos_juri WHERE inscricao_id = ? AND premiacao_id = ?");
                $stJuri->execute([$inscricaoId, $premiacaoId]);
                $qtdJuri = (int)$stJuri->fetchColumn();

                $totalVotos = $qtdPopular + $qtdTecnica + $qtdJuri;

                $pdo->prepare("
                    INSERT INTO premiacao_resultados_finais
                        (premiacao_id, categoria_id, inscricao_id,
                         qtd_voto_popular, qtd_voto_tecnica, qtd_voto_juri, total_votos,
                         vencedor, publicar_resultado, publicado_em, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 1, 1, NOW(), NOW(), NOW())
                    ON DUPLICATE KEY UPDATE
                        qtd_voto_popular   = VALUES(qtd_voto_popular),
                        qtd_voto_tecnica   = VALUES(qtd_voto_tecnica),
                        qtd_voto_juri      = VALUES(qtd_voto_juri),
                        total_votos        = VALUES(total_votos),
                        vencedor           = 1,
                        publicar_resultado = 1,
                        publicado_em       = IF(publicado_em IS NULL, NOW(), publicado_em),
                        updated_at         = NOW()
                ")->execute([$premiacaoId, $g['categoria_id'], $inscricaoId, $qtdPopular, $qtdTecnica, $qtdJuri, $totalVotos]);
            }

            $pdo->commit();
            $msg = count($ganhadores) . ' ganhador(es) publicado(s) com sucesso!';
        }
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $msg     = 'Erro: ' . $e->getMessage();
        $msgType = 'danger';
    }

    $qs = http_build_query(array_filter([
        'premiacao_id' => $premiacaoId ?: '',
        'fase_id'      => $faseId ?: '',
        '_msg'         => $msg,
        '_tipo'        => $msgType,
    ]));
    header("Location: premiacao_resultados.php?$qs");
    exit;
}

if (!empty($_GET['_msg'])) {
    $msg     = $_GET['_msg'];
    $msgType = $_GET['_tipo'] ?? 'success';
}

// ── Filtros comuns ───────────────────────────────────────────────────────────────
$filtroPremiacao = (int)($_GET['premiacao_id'] ?? 0);
$filtroFase      = (int)($_GET['fase_id']      ?? 0);

$premiacoes = $pdo->query("SELECT id, nome, ano FROM premiacoes ORDER BY ano DESC, id DESC")->fetchAll();

if ($filtroPremiacao <= 0 && !empty($premiacoes)) {
    $filtroPremiacao = (int)$premiacoes[0]['id'];
}

// Fases com classificados
$todasFases = [];
if ($filtroPremiacao > 0) {
    $stFases = $pdo->prepare("
        SELECT pf.id, pf.nome, pf.tipo_fase, pf.status, pf.ordem_exibicao
        FROM premiacao_fases pf
        WHERE pf.premiacao_id = ?
          AND pf.status IN ('apurada','encerrada')
          AND EXISTS (SELECT 1 FROM premiacao_classificados pc WHERE pc.fase_id = pf.id)
        ORDER BY pf.ordem_exibicao ASC
    ");
    $stFases->execute([$filtroPremiacao]);
    $todasFases = $stFases->fetchAll();
}

if ($filtroFase <= 0 && !empty($todasFases)) {
    $filtroFase = (int)$todasFases[0]['id'];
}

$faseAtiva = null;
foreach ($todasFases as $f) {
    if ((int)$f['id'] === $filtroFase) { $faseAtiva = $f; break; }
}

// Nome da edição ativa (para o nome do arquivo CSV)
$nomeEdicao = '';
foreach ($premiacoes as $pr) {
    if ((int)$pr['id'] === $filtroPremiacao) {
        $nomeEdicao = $pr['nome'] . '_' . $pr['ano'];
        break;
    }
}

// ──────────────────────────────────────────────────────────────────────────────
// EXPORTAR CSV
// ──────────────────────────────────────────────────────────────────────────────
if (isset($_GET['export']) && $_GET['export'] === 'csv' && $filtroPremiacao > 0 && $filtroFase > 0) {

    $rows = queryClassificados($pdo, $filtroPremiacao, $filtroFase);

    $faseName   = $faseAtiva['nome'] ?? 'fase';
    $safeName   = preg_replace('/[^\w\-]/u', '_', $nomeEdicao . '_' . $faseName);
    $filename   = 'classificados_' . $safeName . '_' . date('Ymd') . '.csv';

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');

    $out = fopen('php://output', 'w');

    // BOM UTF-8 para Excel abrir corretamente
    fputs($out, "\xEF\xBB\xBF");

    // Cabeçalho
    fputcsv($out, [
        'Posição',
        'Categoria',
        'Nome Fantasia',
        'Município',
        'Estado',
        'Site',
        'LinkedIn',
        'Instagram',
        'Facebook',
        'TikTok',
        'YouTube',
        'Nome',
        'Sobrenome',
        'E-mail',
        'Celular',
        'Origem',
        'Votos Populares',
        'Votos Técnicos',
        'Votos Júri',
        'Total Votos',
        'Apurado em',
    ], ';');

    $isFinalCsv = ($faseAtiva['tipo_fase'] ?? '') === 'final';

    foreach ($rows as $r) {
        $pos   = (int)$r['posicao'];
        $vPop  = $isFinalCsv ? (int)$r['pub_voto_popular']  : (int)$r['votos_pop'];
        $vTec  = $isFinalCsv ? (int)$r['pub_voto_tecnica']  : (int)$r['votos_tec'];
        $vJuri = $isFinalCsv ? (int)$r['pub_voto_juri']     : (int)$r['votos_juri'];
        $vTot  = $isFinalCsv ? (int)$r['pub_total_votos']   : ($vPop + $vTec + $vJuri);

        fputcsv($out, [
            $pos . 'º',
            $r['categoria_nome']           ?? '',
            $r['nome_fantasia']            ?? '',
            $r['municipio']                ?? '',
            $r['estado']                   ?? '',
            $r['site']                     ?? '',
            $r['linkedin']                 ?? '',
            $r['instagram']                ?? '',
            $r['facebook']                 ?? '',
            $r['tiktok']                   ?? '',
            $r['youtube']                  ?? '',
            $r['empreendedor_nome']         ?? '',
            $r['empreendedor_sobrenome']    ?? '',
            $r['empreendedor_email']        ?? '',
            $r['empreendedor_celular']      ?? '',
            $r['origem']                   ?? '',
            $vPop,
            $vTec,
            $vJuri,
            $vTot,
            dataBr($r['apurado_em']),
        ], ';');
    }

    fclose($out);
    exit;
}
// ──────────────────────────────────────────────────────────────────────────────

// ── Fase final apurada (botão publicar) ───────────────────────────────────────
$faseFinalApurada = null;
$jaPublicado      = false;

if ($filtroPremiacao > 0) {
    $stFF = $pdo->prepare("
        SELECT pf.id, pf.nome
        FROM premiacao_fases pf
        WHERE pf.premiacao_id = ? AND pf.tipo_fase = 'final' AND pf.status = 'apurada'
        LIMIT 1
    ");
    $stFF->execute([$filtroPremiacao]);
    $faseFinalApurada = $stFF->fetch() ?: null;

    if ($faseFinalApurada) {
        $stPub = $pdo->prepare("SELECT COUNT(*) FROM premiacao_resultados_finais WHERE premiacao_id = ? AND publicar_resultado = 1");
        $stPub->execute([$filtroPremiacao]);
        $jaPublicado = (int)$stPub->fetchColumn() > 0;
    }
}

// ── Fase de resultado (evento presencial) ─────────────────────────────────────
$faseResultado = null;
if ($filtroPremiacao > 0) {
    $stRes = $pdo->prepare("
        SELECT pf.id, pf.nome, pf.data_fim, pf.url_evento
        FROM premiacao_fases pf
        WHERE pf.premiacao_id = ? AND pf.tipo_fase = 'resultado'
        ORDER BY pf.ordem_exibicao ASC LIMIT 1
    ");
    $stRes->execute([$filtroPremiacao]);
    $faseResultado = $stRes->fetch() ?: null;
}

// ── Classificados da fase selecionada (para a tela) ──────────────────────────
$statsTotal = 0;
$statsCats  = 0;
$porCat     = [];

if ($faseAtiva) {
    $rows = queryClassificados($pdo, $filtroPremiacao, $filtroFase);
    foreach ($rows as $r) {
        $cn = $r['categoria_nome'];
        if (!isset($porCat[$cn])) $porCat[$cn] = ['ordem' => $r['categoria_ordem'], 'itens' => []];
        $porCat[$cn]['itens'][] = $r;
        $statsTotal++;
    }
    $statsCats = count($porCat);
}

// URL de exportação com filtros atuais
$csvUrl = '?' . http_build_query([
    'premiacao_id' => $filtroPremiacao,
    'fase_id'      => $filtroFase,
    'export'       => 'csv',
]);

require_once $appBase . '/views/admin/header.php';
?>

<div class="container-fluid py-4 px-3 px-md-4">

<?php if ($msg): ?>
    <div class="alert alert-<?= h($msgType) ?> alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-<?= $msgType === 'success' ? 'check-circle' : 'exclamation-triangle' ?>-fill me-2"></i>
        <?= h($msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- ═══ HERO ══════════════════════════════════════════════════════════════════ -->
<div class="res-hero">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-trophy-fill" style="font-size:1.4rem;color:#CDDE00;"></i>
                <h1 class="mb-0 fs-4 fw-bold">Resultados da Premiação</h1>
            </div>
            <p class="mb-0 opacity-75" style="font-size:.85rem;">
                Classificados por fase · selecione a edição e a fase abaixo
            </p>
            <?php if ($faseAtiva): ?>
                <div class="d-flex flex-wrap gap-2 mt-2">
                    <span class="badge-fase"><i class="bi bi-layers-fill"></i><?= h($faseAtiva['nome']) ?></span>
                    <span class="badge-fase"><i class="bi bi-people-fill"></i><?= $statsTotal ?> classificado<?= $statsTotal !== 1 ? 's' : '' ?></span>
                    <span class="badge-fase"><i class="bi bi-award-fill"></i><?= $statsCats ?> categoria<?= $statsCats !== 1 ? 's' : '' ?></span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Ação publicar / status publicado -->
        <?php if ($faseFinalApurada && !$jaPublicado): ?>
            <form method="POST"
                  onsubmit="return confirm('Publicar os ganhadores (1º colocado de cada categoria) da fase final?\n\nEsta ação grava os totais de votos e publica no site. Confirme apenas após a apuração final.')">
                <input type="hidden" name="acao"           value="publicar_ganhadores">
                <input type="hidden" name="premiacao_id"   value="<?= $filtroPremiacao ?>">
                <input type="hidden" name="fase_id"        value="<?= (int)$faseFinalApurada['id'] ?>">
                <input type="hidden" name="fase_id_filtro" value="<?= $filtroFase ?>">
                <button type="submit" class="btn fw-bold px-4"
                        style="background:#CDDE00;color:#1E3425;border:1px solid #dfe980;">
                    <i class="bi bi-trophy-fill me-2"></i>Publicar Ganhadores
                </button>
            </form>
        <?php elseif ($jaPublicado): ?>
            <div class="pub-banner">
                <i class="bi bi-check-circle-fill fs-5" style="color:#97A327;"></i>
                <div>
                    <strong class="d-block" style="font-size:.85rem;">Ganhadores publicados</strong>
                    <?php if ($faseResultado && !empty($faseResultado['data_fim'])): ?>
                        <span style="font-size:.75rem;color:#555;">
                            Encontro: <?= h(date('d/m/Y', strtotime($faseResultado['data_fim']))) ?>
                            <?php if (!empty($faseResultado['url_evento'])): ?>
                                · <a href="<?= h($faseResultado['url_evento']) ?>" target="_blank" rel="noopener noreferrer">ver evento ↗</a>
                            <?php endif; ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ═══ BARRA DE FILTROS ══════════════════════════════════════════════════════ -->
<div class="filter-bar">

    <!-- Filtro Edição -->
    <div>
        <label><i class="bi bi-calendar3 me-1"></i>Edição</label>
        <select id="sel-premiacao" class="form-select form-select-sm" style="min-width:220px;"
                onchange="aplicarFiltroEdicao(this.value)">
            <?php foreach ($premiacoes as $pr): ?>
                <option value="<?= (int)$pr['id'] ?>" <?= $filtroPremiacao === (int)$pr['id'] ? 'selected' : '' ?>>
                    <?= h($pr['nome']) ?> (<?= (int)$pr['ano'] ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Filtro Fase (pills) -->
    <?php if (!empty($todasFases)): ?>
    <div class="flex-grow-1">
        <label><i class="bi bi-layers me-1"></i>Fase</label>
        <div class="fase-pills">
            <?php foreach ($todasFases as $f):
                $fId      = (int)$f['id'];
                $isActive = $fId === $filtroFase;
                $icon = match($f['tipo_fase']) {
                    'final'           => '🏆',
                    'classificatoria' => '📋',
                    'resultado'       => '🎉',
                    default           => '📌',
                };
                $url = '?' . http_build_query(['premiacao_id' => $filtroPremiacao, 'fase_id' => $fId]);
            ?>
                <a href="<?= $url ?>" class="fase-pill <?= $isActive ? 'active' : '' ?>">
                    <?= $icon ?> <?= h($f['nome']) ?>
                    <?php if ($f['tipo_fase'] === 'final'): ?><span class="dot"></span><?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Botões exportar CSV e XLSX -->
    <?php if ($faseAtiva && !empty($porCat)): ?>
    <div class="ms-auto">
        <label class="invisible d-block" style="font-size:.78rem;">.</label>
        <div class="d-flex gap-2">
            <a href="<?= h($csvUrl) ?>" class="btn-csv">
                <i class="bi bi-file-earmark-spreadsheet-fill"></i>
                Exportar CSV
            </a>
            <a href="export_xlsx.php?premiacao_id=<?= $filtroPremiacao ?>&fase_id=<?= $filtroFase ?>" class="btn-csv">
                <i class="bi bi-file-earmark-excel-fill"></i>
                Exportar XLSX
            </a>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- ═══ CONTEÚDO ══════════════════════════════════════════════════════════════ -->
<?php if (empty($faseAtiva) || empty($porCat)): ?>

    <div class="empty-state">
        <div class="icon">🏅</div>
        <p class="fw-semibold text-muted mb-1 fs-5">Nenhum classificado encontrado</p>
        <p class="text-muted" style="font-size:.875rem;">
            <?php if (empty($todasFases)): ?>
                Não há fases apuradas para esta edição ainda.<br>
                Acesse <a href="premiacao_periodos.php?premiacao_id=<?= $filtroPremiacao ?>" style="color:#97A327;">Períodos</a> e recalcule o status após o encerramento das votações.
            <?php else: ?>
                Selecione uma fase acima para visualizar os classificados.
            <?php endif; ?>
        </p>
    </div>

<?php else:
    $isFinal = $faseAtiva['tipo_fase'] === 'final';
?>

    <?php foreach ($porCat as $catNome => $catDados): ?>
        <div class="cat-block">

            <!-- Cabeçalho da categoria -->
            <div class="cat-header">
                <div class="cat-icon">🏅</div>
                <div class="flex-grow-1 min-width-0">
                    <strong style="font-size:.95rem;color:#1E3425;"><?= h($catNome) ?></strong>
                </div>
                <span class="badge rounded-pill" style="background:#F2F2F2;color:#1E3425;border:1px solid #e0e0e0;font-size:.75rem;">
                    <?= count($catDados['itens']) ?> classificado<?= count($catDados['itens']) !== 1 ? 's' : '' ?>
                </span>
                <?php if ($isFinal): ?>
                    <span class="badge rounded-pill ms-1" style="background:#CDDE00;color:#1E3425;border:1px solid #dfe980;font-size:.75rem;">🏆 Final</span>
                <?php endif; ?>
            </div>

            <!-- Linhas de classificados -->
            <div class="cat-body">
                <?php foreach ($catDados['itens'] as $item):
                    $pos      = (int)$item['posicao'];
                    $isPub    = (int)$item['publicado'] === 1;
                    $vPop     = $isFinal ? (int)$item['pub_voto_popular']  : (int)$item['votos_pop'];
                    $vTec     = $isFinal ? (int)$item['pub_voto_tecnica']  : (int)$item['votos_tec'];
                    $vJuri    = $isFinal ? (int)$item['pub_voto_juri']     : (int)$item['votos_juri'];
                    $vTot     = (int)$item['pub_total_votos'];
                    $rowClass = ($pos === 1 && $isFinal) ? 'row-gold' : '';
                    $posClass = match($pos) { 1 => 'pos-1', 2 => 'pos-2', 3 => 'pos-3', default => 'pos-n' };
                    $medal    = match($pos) { 1 => '🥇', 2 => '🥈', 3 => '🥉', default => $pos . 'º' };

                    $origemBadge = match($item['origem']) {
                        'popular'     => '<span class="badge rounded-pill badge-popular" style="font-size:.72rem;">👥 Popular</span>',
                        'tecnica'     => '<span class="badge rounded-pill badge-tecnica" style="font-size:.72rem;">🔬 Técnica</span>',
                        'ambos'       => '<span class="badge rounded-pill badge-ambos"   style="font-size:.72rem;">✅ Ambos</span>',
                        'juri'        => '<span class="badge rounded-pill badge-juri"    style="font-size:.72rem;">⚖️ Júri</span>',
                        'complemento' => '<span class="badge rounded-pill badge-complemento" style="font-size:.72rem;">➕ Complemento</span>',
                        default       => '<span class="badge rounded-pill badge-complemento" style="font-size:.72rem;">' . h($item['origem']) . '</span>',
                    };
                ?>
                    <div class="class-row <?= $rowClass ?>">

                        <div class="pos-col">
                            <div class="pos-badge <?= $posClass ?>"><?= $medal ?></div>
                        </div>

                        <div class="neg-col">
                            <div class="neg-name"><?= h($item['nome_fantasia']) ?></div>
                            <div class="neg-sub">
                                <i class="bi bi-person me-1"></i><?= h($item['empreendedor_nome']) ?>
                                <?php if ($item['municipio']): ?>
                                    &nbsp;·&nbsp;<i class="bi bi-geo-alt me-1"></i><?= h($item['municipio']) ?>/<?= h($item['estado']) ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="votos-col">
                            <div class="voto-item">
                                <div class="vn"><?= number_format($vPop) ?></div>
                                <div class="vl">Popular</div>
                            </div>
                            <div class="voto-item">
                                <div class="vn"><?= number_format($vTec) ?></div>
                                <div class="vl">Técnico</div>
                            </div>
                            <div class="voto-item">
                                <div class="vn"><?= number_format($vJuri) ?></div>
                                <div class="vl">Júri</div>
                            </div>
                            <?php if ($isFinal): ?>
                            <div class="voto-item">
                                <div class="vn destaque"><?= $isPub ? number_format($vTot) : '—' ?></div>
                                <div class="vl">Total</div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="orig-col"><?= $origemBadge ?></div>

                        <?php if ($isFinal): ?>
                        <div class="status-col">
                            <?php if ($isPub): ?>
                                <span class="badge rounded-pill badge-publicado" style="font-size:.72rem;">
                                    <i class="bi bi-check-circle-fill me-1"></i>Publicado
                                </span>
                            <?php else: ?>
                                <span class="badge rounded-pill badge-nao-publicado" style="font-size:.72rem;">Não publicado</span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <div class="date-col"><?= dataBr($item['apurado_em']) ?></div>

                    </div>
                <?php endforeach; ?>
            </div>

        </div>
    <?php endforeach; ?>

<?php endif; ?>

</div><!-- /container-fluid -->

<script>
function aplicarFiltroEdicao(premiacaoId) {
    const url = new URL(window.location.href);
    url.searchParams.set('premiacao_id', premiacaoId);
    url.searchParams.delete('fase_id');
    window.location.href = url.toString();
}
</script>

<?php require_once $appBase . '/views/admin/footer.php'; ?>