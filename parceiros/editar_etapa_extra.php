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
    header('Location: /login.php');
    exit;
}

$parceiro_id = (int) $_SESSION['parceiro_id'];

// Só exibe se etapa extra foi preenchida
$stmt_check = $pdo->prepare("SELECT etapa_extra_concluida FROM parceiros WHERE id = ? LIMIT 1");
$stmt_check->execute([$parceiro_id]);
$check = $stmt_check->fetch(PDO::FETCH_ASSOC);

if (!$check || empty($check['etapa_extra_concluida'])) {
    header('Location: dashboard.php');
    exit;
}

// Busca dados já salvos
$stmt = $pdo->prepare("SELECT * FROM parceiro_etapa_extra WHERE parceiro_id = ? LIMIT 1");
$stmt->execute([$parceiro_id]);
$dados = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

$objetivo_parceria  = !empty($dados['objetivo_parceria'])  ? json_decode($dados['objetivo_parceria'],  true) : [];
$objetivo_outro     = $dados['objetivo_outro']     ?? '';
$modalidade         = !empty($dados['modalidade'])         ? json_decode($dados['modalidade'],         true) : [];
$modalidade_outro   = $dados['modalidade_outro']   ?? '';
$faixa_apoio        = $dados['faixa_apoio']        ?? '';
$interesse_proposta = !empty($dados['interesse_proposta']) ? json_decode($dados['interesse_proposta'], true) : [];
$observacoes        = $dados['observacoes']        ?? '';
$declara_interesse  = $dados['declara_interesse']  ?? 0;

if (!is_array($objetivo_parceria))  $objetivo_parceria  = [];
if (!is_array($modalidade))         $modalidade         = [];
if (!is_array($interesse_proposta)) $interesse_proposta = [];

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

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Editar — Patrocinadores &amp; Investidores</h2>
                    <p class="text-muted mb-0">Atualize as informações do seu perfil de patrocínio e investimento.</p>
                </div>
                <a href="dashboard.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Voltar ao Painel
                </a>
            </div>

            <?php if (isset($_SESSION['sucesso_editar_etapa_extra'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?= htmlspecialchars($_SESSION['sucesso_editar_etapa_extra']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
                <?php unset($_SESSION['sucesso_editar_etapa_extra']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['erro_editar_etapa_extra'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= htmlspecialchars($_SESSION['erro_editar_etapa_extra']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
                <?php unset($_SESSION['erro_editar_etapa_extra']); ?>
            <?php endif; ?>

            <form method="POST" action="processar_editar_etapa_extra.php">
                <div class="card shadow-sm border-0 rounded-3 mb-4">
                    <div class="card-body p-4 p-md-5">

                        <!-- BLOCO 1 — OBJETIVO DA PARCERIA -->
                        <h5 class="fw-bold mb-3 border-bottom pb-2 text-primary">
                            <i class="bi bi-bullseye me-2"></i>Objetivo da Parceria
                        </h5>
                        <p class="text-muted small mb-3">Qual o principal interesse da sua organização ao se tornar parceira da Impactos Positivos?</p>

                        <?php
                        $objetivos = [
                            'Fortalecimento institucional e posicionamento de marca',
                            'Apoio a iniciativas de impacto socioambiental',
                            'Conexão com negócios e soluções inovadoras',
                            'ESG, sustentabilidade e reputação',
                            'Relacionamento com ecossistema e lideranças',
                            'Investimento em negócios de impacto',
                            'Desenvolvimento de projetos e programas especiais',
                        ];
                        ?>
                        <div class="row g-2 mb-3">
                            <?php foreach ($objetivos as $obj):
                                $checked = in_array($obj, $objetivo_parceria) ? 'checked' : '';
                            ?>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="objetivo_parceria[]" id="obj_<?= md5($obj) ?>" value="<?= htmlspecialchars($obj) ?>" <?= $checked ?>>
                                        <label class="form-check-label" for="obj_<?= md5($obj) ?>"><?= htmlspecialchars($obj) ?></label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div class="col-12 mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="chk_obj_outro" <?= !empty($objetivo_outro) ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-semibold" for="chk_obj_outro">Outro</label>
                                </div>
                                <div id="div_obj_outro" style="<?= empty($objetivo_outro) ? 'display:none;' : '' ?> margin-top:0.5rem;">
                                    <input type="text" class="form-control form-control-sm" name="objetivo_outro" id="inp_obj_outro"
                                        value="<?= htmlspecialchars($objetivo_outro) ?>" placeholder="Descreva o objetivo...">
                                </div>
                            </div>
                        </div>

                        <!-- BLOCO 2 — MODALIDADE -->
                        <h5 class="fw-bold mt-5 mb-3 border-bottom pb-2 text-primary">
                            <i class="bi bi-diagram-3 me-2"></i>Modalidade de Interesse
                        </h5>
                        <p class="text-muted small mb-3">De que forma sua organização prefere apoiar ou se envolver com o ecossistema?</p>

                        <?php
                        $modalidades = [
                            'Patrocínio institucional',
                            'Patrocínio de eventos, premiações ou campanhas',
                            'Apoio a programas especiais e aceleração',
                            'Investimento em ecossistema ou negócios de impacto',
                            'Apoio via permuta, serviços ou produtos',
                            'Cocriação de iniciativas estratégicas',
                        ];
                        ?>
                        <div class="row g-2 mb-3">
                            <?php foreach ($modalidades as $mod):
                                $checked = in_array($mod, $modalidade) ? 'checked' : '';
                            ?>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="modalidade[]" id="mod_<?= md5($mod) ?>" value="<?= htmlspecialchars($mod) ?>" <?= $checked ?>>
                                        <label class="form-check-label" for="mod_<?= md5($mod) ?>"><?= htmlspecialchars($mod) ?></label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div class="col-12 mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="chk_mod_outro" <?= !empty($modalidade_outro) ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-semibold" for="chk_mod_outro">Outro</label>
                                </div>
                                <div id="div_mod_outro" style="<?= empty($modalidade_outro) ? 'display:none;' : '' ?> margin-top:0.5rem;">
                                    <input type="text" class="form-control form-control-sm" name="modalidade_outro" id="inp_mod_outro"
                                        value="<?= htmlspecialchars($modalidade_outro) ?>" placeholder="Descreva a modalidade...">
                                </div>
                            </div>
                        </div>

                        <!-- BLOCO 3 — FAIXA DE APOIO -->
                        <h5 class="fw-bold mt-5 mb-3 border-bottom pb-2 text-primary">
                            <i class="bi bi-currency-dollar me-2"></i>Faixa Estimada de Apoio ou Investimento
                        </h5>
                        <p class="text-muted small mb-3">Isso nos ajuda a criar uma proposta compatível com o porte e os objetivos da sua organização.</p>

                        <?php
                        $faixas = [
                            'Ate 10k'             => 'Até R$ 10 mil',
                            '10k a 50k'           => 'R$ 10 mil a R$ 50 mil',
                            '50k a 100k'          => 'R$ 50 mil a R$ 100 mil',
                            'Acima de 100k'       => 'Acima de R$ 100 mil',
                            'Alinhar diretamente' => 'Prefiro alinhar diretamente com a equipe',
                        ];
                        ?>
                        <div class="row g-2 mb-3">
                            <?php foreach ($faixas as $val => $label): ?>
                                <div class="col-md-4 col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="faixa_apoio" id="faixa_<?= md5($val) ?>" value="<?= htmlspecialchars($val) ?>" <?= ($faixa_apoio === $val) ? 'checked' : '' ?> required>
                                        <label class="form-check-label" for="faixa_<?= md5($val) ?>"><?= htmlspecialchars($label) ?></label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- BLOCO 4 — INTERESSE EM PROPOSTA -->
                        <h5 class="fw-bold mt-5 mb-3 border-bottom pb-2 text-primary">
                            <i class="bi bi-envelope-open me-2"></i>Interesse em Proposta Personalizada
                        </h5>
                        <p class="text-muted small mb-3">Como você gostaria de avançar com a Impactos Positivos?</p>

                        <?php
                        $propostas = [
                            'Sim, desejo receber uma proposta institucional',
                            'Sim, desejo agendar uma conversa estratégica',
                            'Já recebi uma proposta da Impactos Positivos e concordo em seguir com os próximos passos relacionados à parceria, patrocínio ou investimento.',
                            'Não neste momento',
                        ];
                        ?>
                        <div class="row g-2 mb-3">
                            <?php foreach ($propostas as $prop):
                                $checked = in_array($prop, $interesse_proposta) ? 'checked' : '';
                            ?>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="interesse_proposta[]" id="prop_<?= md5($prop) ?>" value="<?= htmlspecialchars($prop) ?>" <?= $checked ?>>
                                        <label class="form-check-label" for="prop_<?= md5($prop) ?>"><?= htmlspecialchars($prop) ?></label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- BLOCO 5 — OBSERVAÇÕES -->
                        <h5 class="fw-bold mt-5 mb-3 border-bottom pb-2 text-primary">
                            <i class="bi bi-chat-text me-2"></i>Observações e Objetivos
                        </h5>
                        <textarea
                            name="observacoes"
                            id="observacoes"
                            class="form-control"
                            rows="5"
                            maxlength="3000"
                            placeholder="Descreva livremente seus objetivos, metas e expectativas com essa parceria..."
                        ><?= htmlspecialchars($observacoes) ?></textarea>
                        <div class="form-text mt-1">Máximo de 3.000 caracteres.</div>

                        <!-- BLOCO 6 — DECLARAÇÃO -->
                        <div class="mt-4 p-4 rounded-3 border" style="background: var(--bs-success-bg-subtle, #d1e7dd);">
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="declara_interesse"
                                    id="declara_interesse"
                                    value="1"
                                    <?= $declara_interesse ? 'checked' : '' ?>
                                    required
                                >
                                <label class="form-check-label fw-semibold" for="declara_interesse">
                                    Declaro interesse em avançar nas conversas relacionadas a patrocínio, investimento ou parceria estratégica com a Impactos Positivos.
                                </label>
                            </div>
                        </div>

                        <!-- AÇÕES -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-5 pt-3 border-top">
                            <a href="dashboard.php" class="btn btn-outline-secondary me-md-2">
                                <i class="bi bi-x-lg me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold">
                                <i class="bi bi-floppy me-2"></i>Salvar Alterações
                            </button>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Toggle genérico: pares chk_X / div_X / inp_X
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

    // Contador de caracteres textarea
    const ta = document.getElementById('observacoes');
    if (ta) {
        const hint = ta.nextElementSibling;
        ta.addEventListener('input', function () {
            const restante = 3000 - this.value.length;
            if (hint) hint.textContent = restante + ' caracteres restantes.';
        });
    }
});
</script>

<?php include __DIR__ . '/../app/views/public/footer_public.php'; ?>