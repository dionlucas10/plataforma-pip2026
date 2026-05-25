<?php
// /vencedores.php — Página pública com vencedores de todas as edições
// Exibe apenas edições que possuem ao menos 1 inscricao com status = 'vencedora'.
session_start();

$config = require __DIR__ . '/app/config/db.php';
$pdo = new PDO(
    "mysql:host={$config['host']};dbname={$config['dbname']};port={$config['port']};charset={$config['charset']}",
    $config['user'], $config['pass'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

function h(?string $v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

$iconesCat = [
    'Ideação'       => '/assets/images/icons/ideacao.png',
    'Operação'      => '/assets/images/icons/operacao.png',
    'Tração/Escala' => '/assets/images/icons/tracao.png',
    'Dinamizador'   => '/assets/images/icons/dinamizadores.png',
];

// ── Filtro de ano via GET ────────────────────────────────────────────────
$filtroAno = (int)($_GET['ano'] ?? 0);

// ── Edições com vencedores publicados ───────────────────────────────────
// Busca todas as premiações que têm pelo menos 1 inscricao com status 'vencedora',
// mais recente primeiro.
$edicoes = $pdo->query("
    SELECT DISTINCT p.id, p.nome, p.ano, p.slug
    FROM premiacoes p
    INNER JOIN premiacao_inscricoes pi ON pi.premiacao_id = p.id AND pi.status = 'vencedora'
    ORDER BY p.ano DESC
")->fetchAll(PDO::FETCH_ASSOC);

// ── Vencedores por edição (com filtro opcional de ano) ──────────────────
$whereAno = $filtroAno > 0 ? 'AND p.ano = ' . $filtroAno : '';

$stmt = $pdo->prepare("
    SELECT
        p.id   AS premiacao_id,
        p.nome AS premiacao_nome,
        p.ano  AS premiacao_ano,
        n.id   AS negocio_id,
        n.nome_fantasia,
        n.categoria,
        n.municipio,
        n.estado,
        a.logo_negocio,
        a.imagem_destaque,
        a.frase_negocio,
        o.icone_url  AS ods_icone,
        o.nome       AS ods_nome,
        et.nome      AS eixo_nome,
        CONCAT(e.nome, ' ', e.sobrenome) AS empreendedor_nome
    FROM premiacao_inscricoes pi
    INNER JOIN premiacoes p        ON p.id  = pi.premiacao_id
    INNER JOIN negocios n          ON n.id  = pi.negocio_id
    LEFT  JOIN negocio_apresentacao a  ON a.negocio_id  = n.id
    LEFT  JOIN ods o               ON o.id  = n.ods_prioritaria_id
    LEFT  JOIN eixos_tematicos et  ON et.id = n.eixo_principal_id
    LEFT  JOIN empreendedores e    ON e.id  = pi.empreendedor_id
    WHERE pi.status = 'vencedora'
    $whereAno
    ORDER BY p.ano DESC, n.categoria ASC, n.nome_fantasia ASC
");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar por edição → categoria → negócios
$porEdicao = [];
foreach ($rows as $r) {
    $anoKey = (int)$r['premiacao_ano'];
    if (!isset($porEdicao[$anoKey])) {
        $porEdicao[$anoKey] = [
            'id'   => $r['premiacao_id'],
            'nome' => $r['premiacao_nome'],
            'ano'  => $anoKey,
            'categorias' => [],
        ];
    }
    $cat = $r['categoria'] ?: 'Sem categoria';
    if (!isset($porEdicao[$anoKey]['categorias'][$cat])) {
        $porEdicao[$anoKey]['categorias'][$cat] = [];
    }
    $porEdicao[$anoKey]['categorias'][$cat][] = $r;
}

include __DIR__ . '/app/views/public/header_public.php';
?>

<div class="container vitrine-nacional-page">

    <!-- Hero -->
    <div class="vitrine-nacional-hero mb-4">
        <div class="vitrine-nacional-hero-content">
            <span class="vitrine-kicker">Premiação</span>
            <h1 class="vitrine-title mb-2">Vencedores</h1>
            <p class="vitrine-subtitle mb-0">
                Conheça os negócios de impacto positivo premiados em cada edição.
            </p>
        </div>
    </div>

    <?php if (empty($porEdicao)): ?>
        <div class="vitrine-empty">
            <h3>Nenhum vencedor publicado ainda</h3>
            <p>Os resultados das premiações serão divulgados aqui após as cerimônias.</p>
            <a href="/premiacao.php" class="btn btn-outline-primary mt-2">Ver premiação ativa</a>
        </div>
        <?php include __DIR__ . '/app/views/public/footer_public.php'; exit; ?>
    <?php endif; ?>

    <!-- Filtro de edição -->
    <?php if (count($edicoes) > 1): ?>
    <div class="vitrine-toolbar mb-4">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="text-muted small me-2">Filtrar por edição:</span>
            <a href="/vencedores.php"
               class="btn btn-sm <?= $filtroAno === 0 ? 'btn-primary' : 'btn-outline-secondary' ?>">
                Todas
            </a>
            <?php foreach ($edicoes as $ed): ?>
                <a href="/vencedores.php?ano=<?= (int)$ed['ano'] ?>"
                   class="btn btn-sm <?= $filtroAno === (int)$ed['ano'] ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    Edição <?= (int)$ed['ano'] ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Vencedores por edição -->
    <?php foreach ($porEdicao as $anoKey => $edicao): ?>
        <section class="mb-5">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div>
                    <h2 class="h3 mb-0">
                        <i class="bi bi-trophy-fill me-2" style="color:#c8960c;"></i>
                        <?= h($edicao['nome']) ?>
                    </h2>
                    <p class="text-muted mb-0 small">Edição <?= (int)$edicao['ano'] ?></p>
                </div>
            </div>

            <?php foreach ($edicao['categorias'] as $categoria => $negocios): ?>
                <div class="mb-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <?php
                        $iconeCat = $iconesCat[$categoria] ?? null;
                        if ($iconeCat): ?>
                            <img src="<?= h($iconeCat) ?>" alt="<?= h($categoria) ?>" width="24" height="24">
                        <?php endif; ?>
                        <h3 class="h5 mb-0"><?= h($categoria) ?></h3>
                    </div>

                    <div class="row g-4">
                        <?php foreach ($negocios as $n): ?>
                        <div class="col-md-6 col-xl-4">
                            <article class="vitrine-card h-100" style="border:2px solid #f0d060;">

                                <!-- Faixa de vencedor -->
                                <div class="d-flex align-items-center gap-2 px-3 py-2"
                                     style="background:#fff9e6;border-bottom:1px solid #f0d060;">
                                    <i class="bi bi-trophy-fill" style="color:#c8960c;"></i>
                                    <span class="fw-semibold small" style="color:#5a3e00;">Vencedor <?= (int)$edicao['ano'] ?></span>
                                </div>

                                <a href="/negocio.php?id=<?= (int)$n['negocio_id'] ?>" class="vitrine-card-link-area">
                                    <div class="vitrine-card-media <?= empty($n['imagem_destaque']) ? 'sem-capa' : '' ?>">
                                        <?php if (!empty($n['imagem_destaque'])): ?>
                                            <img src="<?= h($n['imagem_destaque']) ?>"
                                                 alt="<?= h($n['nome_fantasia']) ?>"
                                                 class="vitrine-card-cover">
                                        <?php elseif (!empty($n['logo_negocio'])): ?>
                                            <div class="vitrine-card-logo-wrap">
                                                <img src="<?= h($n['logo_negocio']) ?>"
                                                     alt="<?= h($n['nome_fantasia']) ?>"
                                                     class="vitrine-card-logo">
                                            </div>
                                        <?php else: ?>
                                            <div class="vitrine-card-fallback">
                                                <span><?= h(mb_strtoupper(mb_substr($n['nome_fantasia'], 0, 1))) ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="vitrine-card-body">
                                        <div class="vitrine-card-top">
                                            <h4 class="vitrine-card-title"><?= h($n['nome_fantasia']) ?></h4>
                                            <?php if (!empty($n['municipio']) || !empty($n['estado'])): ?>
                                                <p class="vitrine-card-local">
                                                    <i class="bi bi-geo-alt"></i>
                                                    <?= h(trim(($n['municipio'] ?? '') . ' / ' . ($n['estado'] ?? ''), ' /')) ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="vitrine-card-meta">
                                            <?php if (!empty($n['eixo_nome'])): ?>
                                                <span class="vitrine-chip vitrine-chip-eixo"><?= h($n['eixo_nome']) ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($n['ods_icone'])): ?>
                                                <span class="vitrine-ods">
                                                    <img src="<?= h($n['ods_icone']) ?>" alt="<?= h($n['ods_nome'] ?? 'ODS') ?>">
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($n['frase_negocio'])): ?>
                                            <blockquote class="vitrine-card-frase">
                                                <?= h($n['frase_negocio']) ?>
                                            </blockquote>
                                        <?php endif; ?>
                                        <?php if (!empty($n['empreendedor_nome'])): ?>
                                            <p class="small text-muted mt-2 mb-0">
                                                <i class="bi bi-person me-1"></i><?= h($n['empreendedor_nome']) ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </a>

                                <div class="vitrine-card-actions">
                                    <a href="/negocio.php?id=<?= (int)$n['negocio_id'] ?>" class="btn btn-outline-primary">
                                        Conhecer Negócio
                                    </a>
                                </div>

                            </article>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (!$loop ?? true): /* separador entre edições */ ?>
                <hr class="my-4" style="border-color: var(--color-divider, #ddd);">
            <?php endif; ?>
        </section>
    <?php endforeach; ?>

    <!-- CTA: inscrever-se na próxima edição -->
    <div class="text-center py-4">
        <p class="text-muted mb-2">Quer participar da próxima edição?</p>
        <a href="/premiacao.php" class="btn btn-primary">
            <i class="bi bi-rocket-takeoff me-1"></i>
            Ver premiação ativa e inscrever-se
        </a>
    </div>

</div>

<?php include __DIR__ . '/app/views/public/footer_public.php'; ?>