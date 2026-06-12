<?php
// ============================================================
// admin/indeferir_carta_parceiro.php
// Cancela a assinatura da carta/acordo do parceiro, permitindo
// nova edição e nova assinatura. Notifica o parceiro por e-mail.
// ============================================================
declare(strict_types=1);
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/helpers/mail.php';
require_once __DIR__ . '/../app/helpers/render.php';

require_admin_login();

// Apenas superadmin
if (!is_superadmin()) {
    http_response_code(403);
    die("Acesso negado. Apenas superadmin pode indeferir carta/acordo.");
}

$config = require __DIR__ . '/../app/config/db.php';

$pdo = new PDO(
    "mysql:host={$config['host']};dbname={$config['dbname']};port={$config['port']};charset={$config['charset']}",
    $config['user'],
    $config['pass'],
    [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]
);

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['msg_erro'] = 'ID de parceiro inválido.';
    header('Location: parceiros.php');
    exit;
}

$sql = "
    SELECT
        p.id,
        p.razao_social,
        p.nome_fantasia,
        p.email_login,
        p.acordo_aceito,
        p.acordo_data,
        p.acordo_ip,
        pc.id AS contrato_id,
        pc.assinatura_digital_url,
        pc.data_assinatura,
        pc.motivo_indeferimento,
        pc.indeferido_em,
        pc.indeferido_por,
        pc.atualizado_em
    FROM parceiros p
    LEFT JOIN parceiro_contrato pc ON pc.parceiro_id = p.id
    WHERE p.id = ?
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$parceiro = $stmt->fetch();

if (!$parceiro) {
    $_SESSION['msg_erro'] = 'Parceiro não encontrado.';
    header('Location: parceiros.php');
    exit;
}

if (empty($parceiro['contrato_id'])) {
    $_SESSION['msg_erro'] = 'Este parceiro não possui registro em parceiro_contrato.';
    header('Location: visualizar_parceiro.php?id=' . $id);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_indeferimento'])) {
    $motivo  = trim($_POST['motivo'] ?? '');
    $adminId = $_SESSION['admin_id'] ?? $_SESSION['usuario_id'] ?? 0;

    try {
        $pdo->beginTransaction();

        $pdo->prepare("
            UPDATE parceiro_contrato
            SET assinatura_digital_url = NULL,
                data_assinatura        = NULL,
                motivo_indeferimento   = ?,
                indeferido_em          = NOW(),
                indeferido_por         = ?
            WHERE parceiro_id = ?
        ")->execute([$motivo, $adminId, $id]);

        $pdo->prepare("
            UPDATE parceiros
            SET acordo_aceito  = 0,
                acordo_data    = NULL,
                acordo_ip      = NULL,
                atualizado_em  = NOW()
            WHERE id = ?
        ")->execute([$id]);

        $pdo->commit();

        // ----- Envio de e-mail ao parceiro -----
        $emailDestino      = $parceiro['email_login'] ?? '';
        $nome_parceiro     = $parceiro['nome_fantasia'] ?: $parceiro['razao_social'] ?: 'Parceiro';
        $nome_organizacao  = $parceiro['razao_social'] ?: $parceiro['nome_fantasia'] ?: 'sua organização';

        if (!empty($emailDestino)) {
            $link_painel = get_base_url() . '/parceiros/carta-acordo.php';

            // Bloco com o motivo (se informado)
            $bloco_motivo = '';
            if (!empty($motivo)) {
                $motivo_escaped = nl2br(htmlspecialchars($motivo));
                $bloco_motivo = "
                    <div style='background-color:#fff8e1;border-left:4px solid #ffc107;padding:15px;margin:20px 0;border-radius:4px;'>
                        <strong>Motivo informado pela equipe:</strong><br>
                        <span style='color:#555;'>{$motivo_escaped}</span>
                    </div>
                ";
            }

            $subject = 'Carta/Acordo – Indeferimento e solicitação de revisão';

            $bodyHtml = "
                <div style='font-family:Arial,sans-serif;color:#333;line-height:1.6;max-width:600px;margin:0 auto;border:1px solid #eaeaea;border-radius:8px;padding:30px;background-color:#ffffff;'>

                    <div style='text-align:center;margin-bottom:25px;'>
                        <h2 style='color:#dc3545;margin:10px 0 0 0;'>Carta/Acordo Indeferida</h2>
                    </div>

                    <p style='font-size:16px;'>Olá, <strong>{$nome_parceiro}</strong>,</p>

                    <p>A equipe da <strong>Plataforma Impactos Positivos</strong> analisou a carta/acordo de parceria de <strong>{$nome_organizacao}</strong> e identificou a necessidade de revisão antes de prosseguirmos.</p>

                    <div style='background-color:#fff3f3;border-left:4px solid #dc3545;padding:15px;margin:25px 0;border-radius:4px;'>
                        <p style='margin:0 0 6px 0;'><strong>O que isso significa?</strong></p>
                        <ul style='margin:0;padding-left:18px;color:#555;'>
                            <li style='margin-bottom:8px;'>⚠️ <strong>A assinatura atual foi cancelada.</strong></li>
                            <li style='margin-bottom:8px;'>📝 Você pode <strong>editar os dados da carta</strong> e realizar uma nova assinatura.</li>
                            <li style='margin-bottom:8px;'>✅ Após a nova assinatura, a carta será analisada novamente pela equipe.</li>
                        </ul>
                    </div>

                    {$bloco_motivo}

                    <p>Acesse o painel do parceiro para revisar e assinar novamente a carta/acordo:</p>

                    <p style='text-align:center;margin:35px 0;'>
                        <a href='{$link_painel}' style='background-color:#1D4F3A;color:#ffffff;padding:14px 30px;text-decoration:none;border-radius:5px;font-weight:bold;font-size:16px;display:inline-block;'>
                            Acessar Carta/Acordo
                        </a>
                    </p>

                    <div style='background-color:#f0f4ed;border-left:4px solid #CDDE00;padding:15px;margin:20px 0;border-radius:4px;'>
                        <p style='margin:0;font-size:15px;color:#3a5a40;'>
                            Em caso de dúvidas, entre em contato com a equipe. Estamos à disposição para ajudar!
                        </p>
                    </div>

                    <hr style='border:none;border-top:1px solid #eee;margin:30px 0;'>
                    <p style='color:#666;font-size:14px;margin-bottom:5px;'>Atenciosamente,</p>
                    <p style='color:#666;font-size:14px;margin-top:0;'><strong>Equipe Impactos Positivos</strong></p>

                </div>
            ";

            $email_renderizado = render_email_from_db($subject, $bodyHtml);

            $headers  = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=utf-8\r\n";
            $headers .= "From: Plataforma Impactos Positivos <nao-responda@impactospositivos.com>\r\n";

            send_mail(
                $emailDestino,
                $nome_parceiro,
                $email_renderizado['subject'],
                $email_renderizado['bodyHtml'],
                $headers
            );
        }
        // ----- Fim envio de e-mail -----

        $_SESSION['msg_sucesso'] = 'Carta/acordo indeferida com sucesso. O parceiro foi notificado por e-mail e poderá editar os dados e assinar novamente.';
        header('Location: visualizar_parceiro.php?id=' . $id);
        exit;

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['msg_erro'] = 'Erro ao indeferir carta/acordo: ' . $e->getMessage();
        header('Location: visualizar_parceiro.php?id=' . $id);
        exit;
    }
}

$assinou = !empty($parceiro['acordo_aceito'])
        || !empty($parceiro['data_assinatura'])
        || !empty($parceiro['assinatura_digital_url']);

$pageTitle = "Indeferir Carta/Acordo";
include __DIR__ . '/../app/views/admin/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Indeferir Carta/Acordo</h2>
            <p class="text-muted mb-0">
                <?= htmlspecialchars($parceiro['nome_fantasia'] ?? $parceiro['razao_social'] ?? 'Parceiro') ?>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="visualizar_parceiro.php?id=<?= (int) $parceiro['id'] ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
            <a href="parceiros.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-list-ul"></i> Lista de Parceiros
            </a>
        </div>
    </div>

    <div class="card border-0 rounded-4 shadow-sm mb-4">
        <div class="card-header bg-white border-0 pt-3 pb-0 px-4 d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-x-fill text-warning fs-5"></i>
            <h5 class="fw-bold text-warning mb-0">Confirmar Indeferimento da Carta/Acordo</h5>
        </div>
        <div class="card-body px-4 pb-4 pt-3">

            <div class="alert alert-warning d-flex align-items-start gap-2" role="alert">
                <i class="bi bi-info-circle-fill fs-5 mt-1"></i>
                <div>
                    Esta ação <strong>cancela a assinatura atual</strong> e libera o parceiro para editar os dados e realizar uma nova assinatura.
                    O parceiro <strong>será notificado por e-mail</strong> automaticamente.
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6 mb-2">
                    <small class="text-muted d-block">ID</small>
                    <span class="fw-semibold">#<?= (int) $parceiro['id'] ?></span>
                </div>
                <div class="col-md-6 mb-2">
                    <small class="text-muted d-block">Razão Social</small>
                    <span class="fw-semibold"><?= htmlspecialchars((string) ($parceiro['razao_social'] ?? '-')) ?></span>
                </div>
                <div class="col-md-6 mb-2">
                    <small class="text-muted d-block">Nome Fantasia</small>
                    <span class="fw-semibold"><?= htmlspecialchars((string) ($parceiro['nome_fantasia'] ?? '-')) ?></span>
                </div>
                <div class="col-md-6 mb-2">
                    <small class="text-muted d-block">E-mail do Parceiro</small>
                    <span class="fw-semibold"><?= htmlspecialchars((string) ($parceiro['email_login'] ?? '-')) ?></span>
                </div>
                <div class="col-md-3 mb-2">
                    <small class="text-muted d-block">Contrato</small>
                    <span class="fw-semibold">#<?= (int) $parceiro['contrato_id'] ?></span>
                </div>
                <div class="col-md-3 mb-2">
                    <small class="text-muted d-block">Status Atual</small>
                    <?php if ($assinou): ?>
                        <span class="badge text-bg-success">Assinado</span>
                    <?php else: ?>
                        <span class="badge text-bg-secondary">Sem assinatura ativa</span>
                    <?php endif; ?>
                </div>
                <div class="col-md-3 mb-2">
                    <small class="text-muted d-block">Data da Assinatura</small>
                    <span class="fw-semibold">
                        <?= !empty($parceiro['data_assinatura'])
                            ? htmlspecialchars((string) $parceiro['data_assinatura'])
                            : '-' ?>
                    </span>
                </div>
                <div class="col-md-3 mb-2">
                    <small class="text-muted d-block">Arquivo da Assinatura</small>
                    <span class="fw-semibold">
                        <?= !empty($parceiro['assinatura_digital_url'])
                            ? htmlspecialchars((string) $parceiro['assinatura_digital_url'])
                            : '-' ?>
                    </span>
                </div>
            </div>

            <form method="post">
                <input type="hidden" name="confirmar_indeferimento" value="1">

                <div class="mb-4">
                    <label for="motivo" class="form-label fw-semibold">Motivo do indeferimento</label>
                    <textarea name="motivo" id="motivo" class="form-control" rows="4"
                        placeholder="Descreva o motivo. Este texto será incluído no e-mail enviado ao parceiro."></textarea>
                    <div class="form-text">
                        <i class="bi bi-envelope me-1"></i>
                        Campo opcional, mas recomendado — o conteúdo será exibido no e-mail de notificação enviado ao parceiro.
                    </div>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-file-earmark-x"></i> Confirmar Indeferimento e Notificar Parceiro
                    </button>
                    <a href="visualizar_parceiro.php?id=<?= (int) $parceiro['id'] ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </a>
                </div>
            </form>

        </div>
    </div>
</div>

<?php include __DIR__ . '/../app/views/admin/footer.php'; ?>
