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
    header("Location: /login.php");
    exit;
}

$parceiro_id = $_SESSION['parceiro_id'];
$mensagem    = '';
$tipo_msg    = '';

// ============================================================
// PROCESSAMENTO DO FORMULÁRIO
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $ods               = $_POST['ods']               ?? [];
    $eixos             = $_POST['eixos']             ?? [];
    $maturidade        = $_POST['maturidade']        ?? [];
    $setores           = $_POST['setores']           ?? [];
    $perfil_impacto    = $_POST['perfil_impacto']    ?? [];
    $perfil_iniciativa = $_POST['perfil_iniciativa'] ?? [];
    $alcance           = trim($_POST['alcance']      ?? '');

    $perfil_impacto_outro       = trim($_POST['perfil_impacto_outro']       ?? '');
    $perfil_iniciativa_outro    = trim($_POST['perfil_iniciativa_outro']    ?? '');
    $setor_outro_setor_primario   = trim($_POST['setor_outro_setor_primário']   ?? '');
    $setor_outro_setor_secundario = trim($_POST['setor_outro_setor_secundário'] ?? '');
    $setor_outro_setor_terciario  = trim($_POST['setor_outro_setor_terciário']  ?? '');

    $eixos_json             = json_encode($eixos,             JSON_UNESCAPED_UNICODE);
    $maturidade_json        = json_encode($maturidade,        JSON_UNESCAPED_UNICODE);
    $setores_json           = json_encode($setores,           JSON_UNESCAPED_UNICODE);
    $perfil_impacto_json    = json_encode($perfil_impacto,    JSON_UNESCAPED_UNICODE);
    $perfil_iniciativa_json = json_encode($perfil_iniciativa, JSON_UNESCAPED_UNICODE);

    try {
        $pdo->beginTransaction();

        $sql_int = "
            INSERT INTO parceiro_interesses (
                parceiro_id,
                eixos_interesse,
                maturidade_negocios,
                setores_interesse,
                perfil_impacto,
                perfil_iniciativa,
                perfil_impacto_outro,
                perfil_iniciativa_outro,
                setor_outro_setor_primario,
                setor_outro_setor_secundario,
                setor_outro_setor_terciario,
                alcance_impacto
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                eixos_interesse              = VALUES(eixos_interesse),
                maturidade_negocios          = VALUES(maturidade_negocios),
                setores_interesse            = VALUES(setores_interesse),
                perfil_impacto               = VALUES(perfil_impacto),
                perfil_iniciativa            = VALUES(perfil_iniciativa),
                perfil_impacto_outro         = VALUES(perfil_impacto_outro),
                perfil_iniciativa_outro      = VALUES(perfil_iniciativa_outro),
                setor_outro_setor_primario   = VALUES(setor_outro_setor_primario),
                setor_outro_setor_secundario = VALUES(setor_outro_setor_secundario),
                setor_outro_setor_terciario  = VALUES(setor_outro_setor_terciario),
                alcance_impacto              = VALUES(alcance_impacto)
        ";

        $pdo->prepare($sql_int)->execute([
            $parceiro_id,
            $eixos_json,
            $maturidade_json,
            $setores_json,
            $perfil_impacto_json,
            $perfil_iniciativa_json,
            $perfil_impacto_outro,
            $perfil_iniciativa_outro,
            $setor_outro_setor_primario,
            $setor_outro_setor_secundario,
            $setor_outro_setor_terciario,
            $alcance,
        ]);

        $pdo->prepare("DELETE FROM parceiro_ods WHERE parceiro_id = ?")->execute([$parceiro_id]);

        if (!empty($ods)) {
            $stmt_ods = $pdo->prepare("INSERT INTO parceiro_ods (parceiro_id, ods_id) VALUES (?, ?)");
            foreach ($ods as $ods_id) {
                $stmt_ods->execute([$parceiro_id, (int)$ods_id]);
            }
        }

        $pdo->commit();
        $mensagem = "Preferências e interesses atualizados com sucesso! Seu radar de conexões foi ajustado.";
        $tipo_msg = "success";

    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Erro ao atualizar interesses do parceiro: " . $e->getMessage());
        $mensagem = "Erro ao salvar seus interesses. Tente novamente mais tarde.";
        $tipo_msg = "danger";
    }
}

// ============================================================
// BUSCA DADOS ATUAIS
// ============================================================
$stmt_ods = $pdo->prepare("SELECT ods_id FROM parceiro_ods WHERE parceiro_id = ?");
$stmt_ods->execute([$parceiro_id]);
$ods_salvas = $stmt_ods->fetchAll(PDO::FETCH_COLUMN) ?: [];

$todas_ods = $pdo->query("SELECT * FROM ods ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

$stmt_int = $pdo->prepare("SELECT * FROM parceiro_interesses WHERE parceiro_id = ?");
$stmt_int->execute([$parceiro_id]);
$interesses = $stmt_int->fetch(PDO::FETCH_ASSOC) ?: [];

$eixos_salvos            = !empty($interesses['eixos_interesse'])       ? json_decode($interesses['eixos_interesse'], true)       : [];
$maturidade_salva        = !empty($interesses['maturidade_negocios'])   ? json_decode($interesses['maturidade_negocios'], true)   : [];
$setores_salvos          = !empty($interesses['setores_interesse'])     ? json_decode($interesses['setores_interesse'], true)     : [];
$perfil_impacto_salvo    = !empty($interesses['perfil_impacto'])        ? json_decode($interesses['perfil_impacto'], true)        : [];
$perfil_iniciativa_salvo = !empty($interesses['perfil_iniciativa'])     ? json_decode($interesses['perfil_iniciativa'], true)     : [];

if (!is_array($eixos_salvos))            $eixos_salvos = [];
if (!is_array($maturidade_salva))        $maturidade_salva = [];
if (!is_array($setores_salvos))          $setores_salvos = [];
if (!is_array($perfil_impacto_salvo))    $perfil_impacto_salvo = [];
if (!is_array($perfil_iniciativa_salvo)) $perfil_iniciativa_salvo = [];

$alcance                    = $interesses['alcance_impacto']              ?? '';
$perfil_impacto_outro       = $interesses['perfil_impacto_outro']         ?? '';
$perfil_iniciativa_outro    = $interesses['perfil_iniciativa_outro']      ?? '';
$setor_outro_primario       = $interesses['setor_outro_setor_primario']   ?? '';
$setor_outro_secundario     = $interesses['setor_outro_setor_secundario'] ?? '';
$setor_outro_terciario      = $interesses['setor_outro_setor_terciario']  ?? '';

$pageTitle = "Editar Interesses e Perfil de Impacto";
include __DIR__ . '/../app/views/public/header_public.php';
?>

<div class="container py-5">
    <div class="row">
        <!-- SIDEBAR -->
        <div class="col-lg-3 col-md-4 mb-4 mb-md-0">
            <?php include __DIR__ . '/../app/views/parceiros/sidebar.php'; ?>
        </div>

        <!-- CONTEÚDA PRINCIPAL -->
        <div class="col-lg-9 col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <div>
                    <h2 class="fw-bold mb-1">Meus Interesses e Perfil de Impacto</h2>
                    <p class="text-muted mb-0">Ajuste o foco do seu radar na Rede de Impacto.</p>
                </div>
                <a href="dashboard.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i> Voltar ao Painel
                </a>
            </div>

            <?php if (!empty($mensagem)): ?>
                <div class="alert alert-<?= $tipo_msg ?> alert-dismissible fade show" role="alert">
                    <i class="bi <?= $tipo_msg === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?> me-2"></i>
                    <?= htmlspecialchars($mensagem) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="">

                <!-- ===== BLOCO 1 – EIXOS TEMÁTICOS ===== -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="bi bi-grid-3x3-gap"></i> Eixos Temáticos de Interesse
                    </div>
                    <p class="form-section-desc">Quais temas mais despertam seu interesse?</p>

                    <?php
                    $eixos_lista = [
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
                    ?>
                    <div class="row g-3">
                        <?php foreach ($eixos_lista as $titulo => $descricao):
                            $checked = in_array($titulo, $eixos_salvos) ? 'checked' : '';
                        ?>
                            <div class="col-12">
                                <label class="match-card match-card-check">
                                    <input class="visually-hidden match-check" type="checkbox" name="eixos[]" value="<?= htmlspecialchars($titulo) ?>" <?= $checked ?>>
                                    <div class="match-card-inner">
                                        <div class="match-card-content">
                                            <div class="match-card-title"><?= htmlspecialchars($titulo) ?></div>
                                            <div class="match-card-desc"><?= htmlspecialchars($descricao) ?></div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- ===== BLOCO 2 – ODS ===== -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="bi bi-bullseye"></i> ODS de Interesse
                    </div>
                    <p class="form-section-desc">
                        Quais Objetivos de Desenvolvimento Sustentável você mais se identifica ou gostaria de acompanhar?
                    </p>
                    <div class="row g-3">
                        <?php foreach ($todas_ods as $ods):
                            $checked = in_array($ods['id'], $ods_salvas) ? 'checked' : '';
                        ?>
                            <div class="col-12 col-md-6">
                                <label class="match-card match-card-check">
                                    <input class="visually-hidden match-check" type="checkbox" name="ods[]" value="<?= $ods['id'] ?>" <?= $checked ?>>
                                    <div class="match-card-inner">
                                        <?php if (!empty($ods['icone_url'])): ?>
                                            <div class="match-card-icon">
                                                <img src="<?= htmlspecialchars($ods['icone_url']) ?>" alt="ODS <?= $ods['n_ods'] ?>">
                                            </div>
                                        <?php endif; ?>
                                        <div class="match-card-content">
                                            <div class="match-card-title">
                                                ODS <?= $ods['n_ods'] ?> – <?= htmlspecialchars($ods['nome']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- ===== BLOCO 3 – MATURIDADE ===== -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="bi bi-bar-chart-line"></i> Maturidade dos Negócios
                    </div>
                    <p class="form-section-desc">
                        Você prefere acompanhar negócios em qual estágio de maturidade?
                    </p>
                    <?php
                    $maturidades = [
                        'Ideação'     => 'Ideação (começando agora)',
                        'Validação'   => 'Validação (modelo sendo testado)',
                        'Crescimento' => 'Crescimento (já operando e expandindo)',
                        'Escala'      => 'Escala (impacto consolidado e ampliando alcance)',
                    ];
                    ?>
                    <div class="row g-3">
                        <?php foreach ($maturidades as $val => $label):
                            $checked = in_array($val, $maturidade_salva) ? 'checked' : '';
                        ?>
                            <div class="col-md-6">
                                <label class="match-card match-card-check">
                                    <input class="visually-hidden match-check" type="checkbox" name="maturidade[]" value="<?= htmlspecialchars($val) ?>" <?= $checked ?>>
                                    <div class="match-card-inner">
                                        <div class="match-card-content">
                                            <div class="match-card-title"><?= htmlspecialchars($label) ?></div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- ===== BLOCO 4 – SETORES POR GRUPO ===== -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="bi bi-briefcase"></i> Setores / Indústrias de Interesse
                    </div>
                    <p class="form-section-desc">Há algum setor específico que você gostaria de acompanhar?</p>

                    <?php
                    $setores_grupos = [
                        'Setor Primário' => [
                            'desc'  => 'Atividades relacionadas à extração e produção de recursos naturais.',
                            'outro' => $setor_outro_primario,
                            'key'   => 'setor_outro_setor_primário',
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
                            'desc'  => 'Atividades ligadas à transformação industrial e construção civil.',
                            'outro' => $setor_outro_secundario,
                            'key'   => 'setor_outro_setor_secundário',
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
                            'desc'  => 'Atividades de comércio, serviços e distribuição.',
                            'outro' => $setor_outro_terciario,
                            'key'   => 'setor_outro_setor_terciário',
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
                        $slug = md5($grupo_nome);
                    ?>
                    <div class="mb-4">
                        <p class="fw-semibold mb-0"><?= htmlspecialchars($grupo_nome) ?></p>
                        <p class="text-muted mb-3" style="font-size: 0.875rem;"><?= htmlspecialchars($grupo['desc']) ?></p>

                        <div class="row g-2">
                            <?php foreach ($grupo['itens'] as $item):
                                $checked = in_array($item, $setores_salvos) ? 'checked' : '';
                            ?>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="setores[]"
                                            value="<?= htmlspecialchars($item) ?>"
                                            id="set_<?= md5($item) ?>"
                                            <?= $checked ?>
                                        >
                                        <label class="form-check-label" for="set_<?= md5($item) ?>">
                                            <?= htmlspecialchars($item) ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <!-- Outro por grupo -->
                            <div class="col-12 mt-1">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="chk_<?= $slug ?>" <?= !empty($grupo['outro']) ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-semibold" for="chk_<?= $slug ?>">Outro</label>
                                </div>
                                <div id="div_<?= $slug ?>" style="<?= empty($grupo['outro']) ? 'display:none;' : '' ?> margin-top: 0.5rem;">
                                    <input
                                        type="text"
                                        class="form-control form-control-sm"
                                        name="<?= htmlspecialchars($grupo['key']) ?>"
                                        id="inp_<?= $slug ?>"
                                        value="<?= htmlspecialchars($grupo['outro']) ?>"
                                        placeholder="Especifique..."
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- ===== BLOCO 5 – PERFIL DE IMPACTO ===== -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="bi bi-stars"></i> Perfil de Impacto que Mais te Inspira
                    </div>

                    <!-- Área de Impacto -->
                    <p class="form-section-desc mb-3">
                        <strong>Área de Impacto</strong> — Selecione o tipo de impacto que mais se conecta com sua visão, atuação ou interesse.
                    </p>
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
                    ?>
                    <div class="row g-2 mb-3">
                        <?php foreach ($areas_impacto as $area):
                            $checked = in_array($area, $perfil_impacto_salvo) ? 'checked' : '';
                        ?>
                            <div class="col-md-6">
                                <label class="match-card match-card-check">
                                    <input class="visually-hidden match-check" type="checkbox" name="perfil_impacto[]" value="<?= htmlspecialchars($area) ?>" <?= $checked ?>>
                                    <div class="match-card-inner">
                                        <div class="match-card-content">
                                            <div class="match-card-title"><?= htmlspecialchars($area) ?></div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        <?php endforeach; ?>

                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="chk_pi_outro" <?= !empty($perfil_impacto_outro) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="chk_pi_outro">Outro (campo aberto)</label>
                            </div>
                            <div id="div_pi_outro" style="<?= empty($perfil_impacto_outro) ? 'display:none;' : '' ?> margin-top:0.5rem;">
                                <input type="text" class="form-control form-control-sm" name="perfil_impacto_outro" id="inp_pi_outro"
                                    value="<?= htmlspecialchars($perfil_impacto_outro) ?>" placeholder="Descreva a área de impacto...">
                            </div>
                        </div>
                    </div>

                    <!-- Perfil da Iniciativa -->
                    <p class="form-section-desc mb-3">
                        <strong>Perfil da Iniciativa</strong> — Selecione os perfis de negócios, projetos ou lideranças que mais te inspiram.
                    </p>
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
                    ?>
                    <div class="row g-2">
                        <?php foreach ($perfis_iniciativa as $perfil):
                            $checked = in_array($perfil, $perfil_iniciativa_salvo) ? 'checked' : '';
                        ?>
                            <div class="col-md-6">
                                <label class="match-card match-card-check">
                                    <input class="visually-hidden match-check" type="checkbox" name="perfil_iniciativa[]" value="<?= htmlspecialchars($perfil) ?>" <?= $checked ?>>
                                    <div class="match-card-inner">
                                        <div class="match-card-content">
                                            <div class="match-card-title"><?= htmlspecialchars($perfil) ?></div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        <?php endforeach; ?>

                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="chk_pini_outro" <?= !empty($perfil_iniciativa_outro) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="chk_pini_outro">Outro (campo aberto)</label>
                            </div>
                            <div id="div_pini_outro" style="<?= empty($perfil_iniciativa_outro) ? 'display:none;' : '' ?> margin-top:0.5rem;">
                                <input type="text" class="form-control form-control-sm" name="perfil_iniciativa_outro" id="inp_pini_outro"
                                    value="<?= htmlspecialchars($perfil_iniciativa_outro) ?>" placeholder="Descreva o perfil de iniciativa...">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===== BLOCO 6 – ALCANCE ===== -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="bi bi-globe-americas"></i> Alcance do Impacto
                    </div>
                    <p class="form-section-desc">
                        Você prefere apoiar causas locais, nacionais, globais ou atuar em todos os níveis?
                    </p>
                    <div class="row g-3">
                        <?php
                        $alcance_opcoes = ['local' => 'Local', 'nacional' => 'Nacional', 'global' => 'Global', 'todos' => 'Todos os níveis'];
                        foreach ($alcance_opcoes as $val => $label):
                        ?>
                            <div class="col-md-3 col-6">
                                <label class="match-card match-card-radio match-card-center">
                                    <input class="visually-hidden match-radio" type="radio" name="alcance" value="<?= $val ?>" <?= ($alcance === $val) ? 'checked' : '' ?> required>
                                    <div class="match-card-inner">
                                        <div class="match-card-title"><?= $label ?></div>
                                    </div>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- AÇÕES -->
                <div class="match-form-actions">
                    <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold">
                        <i class="bi bi-floppy me-2"></i> Salvar Alterações
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // Match cards: radio
    document.querySelectorAll('.match-radio').forEach(function (radio) {
        const activate = function (r) {
            const name = r.getAttribute('name');
            document.querySelectorAll('.match-radio[name="' + name + '"]').forEach(function (item) {
                const card = item.closest('.match-card');
                if (card) card.classList.remove('selected');
            });
            const card = r.closest('.match-card');
            if (card) card.classList.add('selected');
        };
        radio.addEventListener('change', function () { activate(this); });
        if (radio.checked) activate(radio);
    });

    // Match cards: checkbox
    document.querySelectorAll('.match-check').forEach(function (check) {
        check.addEventListener('change', function () {
            const card = this.closest('.match-card');
            if (card) card.classList.toggle('selected', this.checked);
        });
        if (check.checked) {
            const card = check.closest('.match-card');
            if (card) card.classList.add('selected');
        }
    });

    // Toggle genérico: todos os pares chk_X / div_X / inp_X
    document.querySelectorAll('[id^="chk_"]').forEach(function (chk) {
        const suffix = chk.id.replace('chk_', '');
        const div    = document.getElementById('div_' + suffix);
        const inp    = document.getElementById('inp_' + suffix);
        if (!div) return;
        chk.addEventListener('change', function () {
            div.style.display = this.checked ? 'block' : 'none';
            if (!this.checked && inp) inp.value = '';
            if (this.checked && inp) inp.focus();
        });
    });
});
</script>

<?php include __DIR__ . '/../app/views/public/footer_public.php'; ?>
