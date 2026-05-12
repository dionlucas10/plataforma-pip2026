<?php
session_start();

$config = require __DIR__ . '/../app/config/db.php';
$pdo = new PDO(
    "mysql:host={$config['host']};dbname={$config['dbname']};port={$config['port']};charset={$config['charset']}",
    $config['user'],
    $config['pass'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

if (!isset($_SESSION['parceiro_id'])) {
    header("Location: login.php?msg=login_necessario");
    exit;
}

$parceiro_id = $_SESSION['parceiro_id'];

// ODS salvas
$stmt_ods = $pdo->prepare("SELECT ods_id FROM parceiro_ods WHERE parceiro_id = ?");
$stmt_ods->execute([$parceiro_id]);
$ods_salvas = $stmt_ods->fetchAll(PDO::FETCH_COLUMN) ?: [];

// Todas as ODS do banco
$todas_ods = $pdo->query("SELECT * FROM ods ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

// Interesses
$stmt_int = $pdo->prepare("SELECT * FROM parceiro_interesses WHERE parceiro_id = ?");
$stmt_int->execute([$parceiro_id]);
$interesses = $stmt_int->fetch(PDO::FETCH_ASSOC) ?: [];

// Decodifica JSONs
$eixos_salvos       = !empty($interesses['eixos_interesse'])    ? json_decode($interesses['eixos_interesse'], true)    : [];
$maturidade_salva   = !empty($interesses['maturidade_negocios'])? json_decode($interesses['maturidade_negocios'], true): [];
$setores_salvos     = !empty($interesses['setores_interesse'])  ? json_decode($interesses['setores_interesse'], true)  : [];
$perfil_impacto_salvo  = !empty($interesses['perfil_impacto'])  ? json_decode($interesses['perfil_impacto'], true)     : [];
$perfil_iniciativa_salvo = !empty($interesses['perfil_iniciativa']) ? json_decode($interesses['perfil_iniciativa'], true) : [];

if (!is_array($eixos_salvos))            $eixos_salvos = [];
if (!is_array($maturidade_salva))        $maturidade_salva = [];
if (!is_array($setores_salvos))          $setores_salvos = [];
if (!is_array($perfil_impacto_salvo))    $perfil_impacto_salvo = [];
if (!is_array($perfil_iniciativa_salvo)) $perfil_iniciativa_salvo = [];

$alcance               = $interesses['alcance_impacto']        ?? '';
$perfil_impacto_outro  = $interesses['perfil_impacto_outro']   ?? '';
$perfil_iniciativa_outro = $interesses['perfil_iniciativa_outro'] ?? '';

include __DIR__ . '/../app/views/public/header_public.php';
?>

<div class="container py-5 parceiro-step-shell">
    <div class="parceiro-step-top mb-4 mb-lg-5">
        <div class="parceiro-step-progress-card">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                <div>
                    <span class="parceiro-step-kicker">Etapa 4 de 6</span>
                    <h1 class="parceiro-step-title mb-1">Perfil de Impacto</h1>
                    <p class="parceiro-step-subtitle mb-0">
                        Mapeie os temas, perfis e formatos de conexão que mais combinam com os interesses da sua organização.
                    </p>
                </div>
                <div class="parceiro-step-indicator">66%</div>
            </div>
            <div class="progress parceiro-step-progress" role="progressbar" aria-valuenow="66" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar bg-primary" style="width: 66%;"></div>
            </div>
        </div>
    </div>

    <div class="row g-4 align-items-start">
        <div class="col-lg-4">
            <aside class="parceiro-step-aside">
                <div class="parceiro-step-aside-card">
                    <div class="parceiro-step-aside-title">
                        <i class="bi bi-bullseye"></i>
                        Objetivo desta etapa
                    </div>
                    <ul class="parceiro-step-aside-list">
                        <li>Entender os temas e ODS com os quais sua organização mais se conecta.</li>
                        <li>Mapear estágio, setor, perfil e alcance dos negócios de interesse.</li>
                        <li>Melhorar o matchmaking com oportunidades e conexões futuras na plataforma.</li>
                    </ul>
                </div>
                <div class="parceiro-step-aside-card parceiro-step-aside-highlight">
                    <div class="parceiro-step-aside-title">
                        <i class="bi bi-diagram-3-fill"></i>
                        Matchmaking mais assertivo
                    </div>
                    <p class="mb-0">
                        Quanto mais precisa for sua seleção, melhores serão os cruzamentos com negócios, programas e oportunidades compatíveis.
                    </p>
                </div>
            </aside>
        </div>

        <div class="col-lg-8">
            <div class="parceiro-step-card">
                <div class="parceiro-step-card-header">
                    <div>
                        <h2 class="parceiro-step-card-title mb-1">Mapeamento de interesses</h2>
                        <p class="parceiro-step-card-subtitle mb-0">
                            Essas respostas ajudam a conectar sua organização com negócios de impacto e oportunidades mais adequadas ao seu perfil.
                        </p>
                    </div>
                </div>

                <div class="parceiro-step-card-body">
                    <?php if (isset($_SESSION['erro_etapa4'])): ?>
                        <div class="alert alert-danger d-flex align-items-start gap-2 parceiro-step-alert">
                            <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                            <div><?= htmlspecialchars($_SESSION['erro_etapa4']) ?></div>
                        </div>
                        <?php unset($_SESSION['erro_etapa4']); ?>
                    <?php endif; ?>

                    <form method="POST" action="processar_etapa4.php">
                        <input type="hidden" name="from" value="<?= htmlspecialchars($_GET['from'] ?? '') ?>">

                        <!-- ===== BLOCO 1 – EIXOS TEMÁTICOS ===== -->
                        <section class="parceiro-step-section">
                            <div class="parceiro-step-section-head">
                                <h3 class="parceiro-step-section-title">Eixos Temáticos de Interesse</h3>
                                <p class="parceiro-step-section-text">
                                    Quais temas mais despertam seu interesse?
                                </p>
                            </div>

                            <div class="row g-3">
                                <?php
                                $eixos = [
                                    'Cidadania, Direitos Humanos e Sociedade' =>
                                        'Promoção da inclusão, diversidade, equidade, participação cidadã e acesso a direitos fundamentais.',
                                    'Cidades, Mobilidade, Serviços e Infraestrutura Urbana' =>
                                        'Soluções para melhorar mobilidade, habitação, saneamento, infraestrutura urbana e qualidade de vida nas cidades.',
                                    'Educação, Cultura, Economia Criativa e Tecnologia da Informação' =>
                                        'Projetos que ampliam o acesso à educação, cultura, criatividade e tecnologias digitais.',
                                    'Saúde' =>
                                        'Soluções que promovem saúde, bem-estar, prevenção, tratamentos acessíveis e gestão eficiente da área da saúde.',
                                    'Finanças' =>
                                        'Iniciativas de inclusão financeira, acesso a crédito, educação financeira e inovação em serviços financeiros.',
                                    'Biodiversidade, Bioeconomia, Tecnologias Verdes e Indústria Sustentável' =>
                                        'Negócios que conservam a natureza, usam recursos de forma sustentável e promovem tecnologias verdes.',
                                ];
                                foreach ($eixos as $titulo => $descricao):
                                    $checked = in_array($titulo, $eixos_salvos) ? 'checked' : '';
                                ?>
                                    <div class="col-12">
                                        <div class="parceiro-choice-card">
                                            <div class="form-check parceiro-choice-check">
                                                <input
                                                    class="form-check-input parceiro-choice-input"
                                                    type="checkbox"
                                                    name="eixos[]"
                                                    value="<?= htmlspecialchars($titulo) ?>"
                                                    id="eixo_<?= md5($titulo) ?>"
                                                    <?= $checked ?>
                                                >
                                                <label class="form-check-label parceiro-choice-label" for="eixo_<?= md5($titulo) ?>">
                                                    <span class="parceiro-choice-title"><?= htmlspecialchars($titulo) ?></span>
                                                    <span class="parceiro-choice-desc"><?= htmlspecialchars($descricao) ?></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </section>

                        <!-- ===== BLOCO 2 – ODS (PRESERVADO) ===== -->
                        <section class="parceiro-step-section">
                            <div class="parceiro-step-section-head">
                                <h3 class="parceiro-step-section-title">ODS de Interesse</h3>
                                <p class="parceiro-step-section-text">
                                    Quais Objetivos de Desenvolvimento Sustentável você mais se identifica ou gostaria de acompanhar?
                                </p>
                            </div>

                            <div class="row g-3">
                                <?php foreach ($todas_ods as $ods):
                                    $checked = in_array($ods['id'], $ods_salvas) ? 'checked' : '';
                                ?>
                                    <div class="col-md-6">
                                        <div class="parceiro-choice-card parceiro-choice-card-ods">
                                            <div class="form-check parceiro-choice-check parceiro-choice-check-ods">
                                                <input
                                                    class="form-check-input parceiro-choice-input"
                                                    type="checkbox"
                                                    name="ods[]"
                                                    value="<?= $ods['id'] ?>"
                                                    id="ods_<?= $ods['id'] ?>"
                                                    <?= $checked ?>
                                                >
                                                <label class="form-check-label parceiro-choice-label parceiro-choice-label-ods" for="ods_<?= $ods['id'] ?>">
                                                    <?php if (!empty($ods['icone_url'])): ?>
                                                        <img
                                                            src="<?= htmlspecialchars($ods['icone_url']) ?>"
                                                            alt="ODS <?= $ods['n_ods'] ?>"
                                                            class="parceiro-ods-icon"
                                                        >
                                                    <?php endif; ?>
                                                    <span class="parceiro-choice-title parceiro-choice-title-ods">
                                                        ODS <?= $ods['n_ods'] ?> – <?= htmlspecialchars($ods['nome']) ?>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </section>

                        <!-- ===== BLOCO 3 – MATURIDADE ===== -->
                        <section class="parceiro-step-section">
                            <div class="parceiro-step-section-head">
                                <h3 class="parceiro-step-section-title">Maturidade dos Negócios</h3>
                                <p class="parceiro-step-section-text">
                                    Você prefere acompanhar negócios em qual estágio de maturidade?
                                </p>
                            </div>

                            <div class="row g-3">
                                <?php
                                $maturidades = [
                                    'Ideação'     => 'Ideação (começando agora)',
                                    'Validação'   => 'Validação (modelo sendo testado)',
                                    'Crescimento' => 'Crescimento (já operando e expandindo)',
                                    'Escala'      => 'Escala (impacto consolidado e ampliando alcance)',
                                ];
                                foreach ($maturidades as $val => $label):
                                    $checked = in_array($val, $maturidade_salva) ? 'checked' : '';
                                ?>
                                    <div class="col-md-6">
                                        <div class="parceiro-choice-card">
                                            <div class="form-check parceiro-choice-check">
                                                <input
                                                    class="form-check-input parceiro-choice-input"
                                                    type="checkbox"
                                                    name="maturidade[]"
                                                    value="<?= htmlspecialchars($val) ?>"
                                                    id="mat_<?= md5($val) ?>"
                                                    <?= $checked ?>
                                                >
                                                <label class="form-check-label parceiro-choice-label" for="mat_<?= md5($val) ?>">
                                                    <span class="parceiro-choice-title"><?= htmlspecialchars($label) ?></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </section>

                        <!-- ===== BLOCO 4 – SETORES / INDÚSTRIAS ===== -->
                        <section class="parceiro-step-section">
                            <div class="parceiro-step-section-head">
                                <h3 class="parceiro-step-section-title">Setores / Indústrias de Interesse</h3>
                                <p class="parceiro-step-section-text">
                                    Há algum setor específico que você gostaria de acompanhar?
                                </p>
                            </div>

                            <?php
                            $setores_grupos = [
                                'Setor Primário' => [
                                    'desc' => 'Atividades relacionadas à extração e produção de recursos naturais.',
                                    'itens' => [
                                        'Agricultura (soja, milho, café, cana-de-açúcar, hortaliças etc.)',
                                        'Pecuária (gado de corte, leite, aves, suínos)',
                                        'Pesca (artesanal e industrial)',
                                        'Silvicultura (reflorestamento, produção de madeira e celulose)',
                                        'Extração vegetal (castanha, borracha, óleos)',
                                        'Mineração (ferro, ouro, bauxita, nióbio, petróleo bruto)',
                                    ],
                                ],
                                'Setor Secundário' => [
                                    'desc' => 'Atividades ligadas à transformação industrial e construção civil.',
                                    'itens' => [
                                        'Indústrias alimentícias e bebidas',
                                        'Indústria têxtil e de vestuário',
                                        'Indústria automobilística',
                                        'Indústria química e petroquímica',
                                        'Indústria farmacêutica',
                                        'Indústria de papel e celulose',
                                        'Indústria de cimento e construção civil',
                                        'Siderurgia e metalurgia',
                                        'Indústria de eletroeletrônicos e tecnologia',
                                        'Geração e distribuição de energia',
                                    ],
                                ],
                                'Setor Terciário' => [
                                    'desc' => 'Atividades de comércio, serviços e distribuição.',
                                    'itens' => [
                                        'Comércio varejista e atacadista',
                                        'Transporte e logística (rodoviário, ferroviário, aéreo, marítimo)',
                                        'Serviços financeiros (bancos, fintechs, cooperativas de crédito)',
                                        'Educação (escolas, universidades, cursos técnicos)',
                                        'Saúde (hospitais, clínicas, laboratórios)',
                                        'Turismo, hotelaria e eventos',
                                        'Tecnologia e serviços digitais (startups, plataformas, TI)',
                                        'Serviços jurídicos e contábeis',
                                        'Comunicação e marketing',
                                        'Administração pública e serviços sociais',
                                        'Serviços de limpeza, segurança e manutenção',
                                        'Entretenimento e cultura (cinema, música, teatro)',
                                    ],
                                ],
                            ];

                            foreach ($setores_grupos as $grupo_nome => $grupo):
                                $grupo_slug = md5($grupo_nome);
                            ?>
                            <div class="parceiro-step-toggle-box mb-4">
                                <div class="d-flex align-items-start gap-3 mb-1">
                                    <div>
                                        <p class="fw-semibold mb-0"><?= htmlspecialchars($grupo_nome) ?></p>
                                        <p class="text-muted mb-0" style="font-size: var(--text-sm);"><?= htmlspecialchars($grupo['desc']) ?></p>
                                    </div>
                                </div>

                                <div class="row g-2 mt-2">
                                    <?php foreach ($grupo['itens'] as $item):
                                        $checked = in_array($item, $setores_salvos) ? 'checked' : '';
                                    ?>
                                        <div class="col-md-6">
                                            <div class="parceiro-choice-card parceiro-choice-card-soft">
                                                <div class="form-check parceiro-choice-check">
                                                    <input
                                                        class="form-check-input parceiro-choice-input"
                                                        type="checkbox"
                                                        name="setores[]"
                                                        value="<?= htmlspecialchars($item) ?>"
                                                        id="set_<?= md5($item) ?>"
                                                        <?= $checked ?>
                                                    >
                                                    <label class="form-check-label parceiro-choice-label" for="set_<?= md5($item) ?>">
                                                        <span class="parceiro-choice-title"><?= htmlspecialchars($item) ?></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                    <!-- Outro por grupo -->
                                    <?php
                                    $outro_key = 'setor_outro_' . strtolower(str_replace(' ', '_', $grupo_nome));
                                    $outro_val = $interesses[$outro_key] ?? '';
                                    ?>
                                    <div class="col-12">
                                        <div class="parceiro-step-toggle-box">
                                            <div class="form-check m-0">
                                                <input class="form-check-input" type="checkbox" id="check_<?= $grupo_slug ?>" <?= !empty($outro_val) ? 'checked' : '' ?>>
                                                <label class="form-check-label fw-semibold" for="check_<?= $grupo_slug ?>">Outro</label>
                                            </div>
                                            <div class="mt-2" id="div_<?= $grupo_slug ?>" style="<?= empty($outro_val) ? 'display:none;' : '' ?>">
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    name="<?= $outro_key ?>"
                                                    id="input_<?= $grupo_slug ?>"
                                                    value="<?= htmlspecialchars($outro_val) ?>"
                                                    placeholder="Especifique..."
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </section>

                        <!-- ===== BLOCO 5 – PERFIL DE IMPACTO ===== -->
                        <section class="parceiro-step-section">
                            <div class="parceiro-step-section-head">
                                <h3 class="parceiro-step-section-title">Perfil de Impacto que Mais te Inspira</h3>
                            </div>

                            <!-- Área de Impacto -->
                            <div class="mb-4">
                                <p class="parceiro-step-section-text mb-3">
                                    <strong>Área de Impacto</strong> — Selecione o tipo de impacto que mais se conecta com sua visão, atuação ou interesse.
                                </p>
                                <div class="row g-3">
                                    <?php
                                    $areas_impacto = [
                                        'Social',
                                        'Ambiental',
                                        'Social e Ambiental',
                                        'Inovação e Tecnologia para Impacto',
                                        'Desenvolvimento Comunitário e Territorial',
                                        'Educação e Inclusão',
                                        'Economia Regenerativa e Sustentabilidade',
                                    ];
                                    foreach ($areas_impacto as $area):
                                        $checked = in_array($area, $perfil_impacto_salvo) ? 'checked' : '';
                                    ?>
                                        <div class="col-md-6">
                                            <div class="parceiro-choice-card parceiro-choice-card-soft">
                                                <div class="form-check parceiro-choice-check">
                                                    <input
                                                        class="form-check-input parceiro-choice-input"
                                                        type="checkbox"
                                                        name="perfil_impacto[]"
                                                        value="<?= htmlspecialchars($area) ?>"
                                                        id="pi_<?= md5($area) ?>"
                                                        <?= $checked ?>
                                                    >
                                                    <label class="form-check-label parceiro-choice-label" for="pi_<?= md5($area) ?>">
                                                        <span class="parceiro-choice-title"><?= htmlspecialchars($area) ?></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                    <!-- Outro – Área de Impacto -->
                                    <div class="col-12">
                                        <div class="parceiro-step-toggle-box">
                                            <div class="form-check m-0">
                                                <input class="form-check-input" type="checkbox" id="check_pi_outro" <?= !empty($perfil_impacto_outro) ? 'checked' : '' ?>>
                                                <label class="form-check-label fw-semibold" for="check_pi_outro">Outro (campo aberto)</label>
                                            </div>
                                            <div class="mt-2" id="div_pi_outro" style="<?= empty($perfil_impacto_outro) ? 'display:none;' : '' ?>">
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    name="perfil_impacto_outro"
                                                    id="input_pi_outro"
                                                    value="<?= htmlspecialchars($perfil_impacto_outro) ?>"
                                                    placeholder="Descreva a área de impacto..."
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Perfil da Iniciativa -->
                            <div>
                                <p class="parceiro-step-section-text mb-3">
                                    <strong>Perfil da Iniciativa</strong> — Selecione os perfis de negócios, projetos ou lideranças que mais te inspiram.
                                </p>
                                <div class="row g-3">
                                    <?php
                                    $perfis_iniciativa = [
                                        'Negócios de base comunitária',
                                        'Lideranças femininas',
                                        'Lideranças jovens',
                                        'Iniciativas locais e regionais',
                                        'Iniciativas de alcance nacional ou global',
                                        'Empreendedorismo periférico e inclusivo',
                                        'Soluções inovadoras e escaláveis',
                                    ];
                                    foreach ($perfis_iniciativa as $perfil):
                                        $checked = in_array($perfil, $perfil_iniciativa_salvo) ? 'checked' : '';
                                    ?>
                                        <div class="col-md-6">
                                            <div class="parceiro-choice-card parceiro-choice-card-soft">
                                                <div class="form-check parceiro-choice-check">
                                                    <input
                                                        class="form-check-input parceiro-choice-input"
                                                        type="checkbox"
                                                        name="perfil_iniciativa[]"
                                                        value="<?= htmlspecialchars($perfil) ?>"
                                                        id="pini_<?= md5($perfil) ?>"
                                                        <?= $checked ?>
                                                    >
                                                    <label class="form-check-label parceiro-choice-label" for="pini_<?= md5($perfil) ?>">
                                                        <span class="parceiro-choice-title"><?= htmlspecialchars($perfil) ?></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                    <!-- Outro – Perfil da Iniciativa -->
                                    <div class="col-12">
                                        <div class="parceiro-step-toggle-box">
                                            <div class="form-check m-0">
                                                <input class="form-check-input" type="checkbox" id="check_pini_outro" <?= !empty($perfil_iniciativa_outro) ? 'checked' : '' ?>>
                                                <label class="form-check-label fw-semibold" for="check_pini_outro">Outro (campo aberto)</label>
                                            </div>
                                            <div class="mt-2" id="div_pini_outro" style="<?= empty($perfil_iniciativa_outro) ? 'display:none;' : '' ?>">
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    name="perfil_iniciativa_outro"
                                                    id="input_pini_outro"
                                                    value="<?= htmlspecialchars($perfil_iniciativa_outro) ?>"
                                                    placeholder="Descreva o perfil de iniciativa..."
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- ===== BLOCO 6 – ALCANCE (PRESERVADO) ===== -->
                        <section class="parceiro-step-section">
                            <div class="parceiro-step-section-head">
                                <h3 class="parceiro-step-section-title">Alcance do impacto</h3>
                                <p class="parceiro-step-section-text">
                                    Você prefere apoiar causas locais, nacionais, globais ou atuar em todos os níveis?
                                </p>
                            </div>

                            <div class="row g-3">
                                <?php
                                $alcance_opcoes = ['local' => 'Local', 'nacional' => 'Nacional', 'global' => 'Global', 'todos' => 'Todos os níveis'];
                                foreach ($alcance_opcoes as $val => $label):
                                ?>
                                    <div class="col-md-6 col-xl-3">
                                        <label class="parceiro-radio-card parceiro-radio-card-center parceiro-radio-card-compact">
                                            <input
                                                class="form-check-input parceiro-radio-input"
                                                type="radio"
                                                name="alcance"
                                                value="<?= $val ?>"
                                                id="alc_<?= $val ?>"
                                                <?= ($alcance === $val) ? 'checked' : '' ?>
                                                required
                                            >
                                            <span class="parceiro-radio-content">
                                                <span class="parceiro-radio-title"><?= $label ?></span>
                                            </span>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </section>

                        <div class="parceiro-step-actions">
                            <?php if (($_GET['from'] ?? '') === 'confirmacao'): ?>
                                <button type="submit" name="acao" value="confirmacao" class="btn btn-outline-primary">
                                    Salvar e voltar à revisão
                                </button>
                            <?php endif; ?>

                            <a href="etapa3_combinado.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Voltar
                            </a>

                            <button type="submit" class="btn-reg-submit">
                                Salvar e avançar
                                <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    // Toggle genérico: ao clicar no checkbox, mostra/oculta o campo
    function bindToggle(checkId, divId, inputId) {
        const chk = document.getElementById(checkId);
        const div = document.getElementById(divId);
        const inp = inputId ? document.getElementById(inputId) : null;
        if (!chk || !div) return;
        chk.addEventListener("change", function () {
            div.style.display = this.checked ? "block" : "none";
            if (!this.checked && inp) inp.value = "";
            if (this.checked && inp) inp.focus();
        });
    }

    // Setores – "Outro" por grupo
    ["setor_primário", "setor_secundário", "setor_terciário"].forEach(function(grupo) {
        const slug = btoa(unescape(encodeURIComponent(grupo))).replace(/[^a-z0-9]/gi,'').substr(0,32);
    });

    // Percorre todos os pares check/div gerados pelo PHP via md5
    document.querySelectorAll('[id^="check_"]').forEach(function(chk) {
        const suffix = chk.id.replace('check_', '');
        const div    = document.getElementById('div_' + suffix);
        const inp    = document.getElementById('input_' + suffix);
        if (!div) return;
        chk.addEventListener("change", function () {
            div.style.display = this.checked ? "block" : "none";
            if (!this.checked && inp) inp.value = "";
            if (this.checked && inp) inp.focus();
        });
    });
});
</script>

<?php include __DIR__ . '/../app/views/public/footer_public.php'; ?>
