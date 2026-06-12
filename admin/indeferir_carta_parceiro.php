<?php
// ============================================================
// admin/indeferir_carta_parceiro.php
// Cancela a assinatura da carta/acordo do parceiro, permitindo
// nova edição e nova assinatura
// Baseado no schema real do bd_homo.sql
// ============================================================

declare(strict_types=1);
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../app/helpers/auth.php';

// Apenas superadmin
if (!is_superadmin()) {
    http_response_code(403);
    die("Acesso negado. Apenas superadmin pode excluir empreendedor.");
}

$config = require __DIR__ . '/../app/config/db.php';

$dsn = "mysql:host={$config['host']};dbname={$config['dbname']};port={$config['port']};charset={$config['charset']}";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $config['user'], $config['pass'], $options);
} catch (PDOException $e) {
    die('Erro de conexão: ' . $e->getMessage());
}

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
$parceiro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$parceiro) {
    $_SESSION['msg_erro'] = 'Parceiro não encontrado.';
    header('Location: parceiros.php');
    exit;
}

if (empty($parceiro['contrato_id'])) {
    $_SESSION['msg_erro'] = 'Este parceiro não possui registro em parceirocontrato.';
    header('Location: visualizar_parceiro.php?id=' . $id);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_indeferimento'])) {
    $motivo = trim($_POST['motivo'] ?? '');
    $adminId = $_SESSION['admin_id'] ?? $_SESSION['usuario_id'] ?? 0;

    try {
        $pdo->beginTransaction();

        $stmtContrato = $pdo->prepare(" 
            UPDATE parceiro_contrato
            SET assinatura_digital_url = NULL,
                data_assinatura = NULL,
                motivo_indeferimento = ?,
                indeferido_em = NOW(),
                indeferido_por = ?
            WHERE parceiro_id = ?
        ");
        $stmtContrato->execute([$motivo, $adminId, $id]);

        $stmtParceiro = $pdo->prepare(" 
            UPDATE parceiros
            SET acordo_aceito = 0,
                acordo_data = NULL,
                acordo_ip = NULL,
                atualizado_em = NOW()
            WHERE id = ?
        ");
        $stmtParceiro->execute([$id]);

        $pdo->commit();

        $_SESSION['msg_sucesso'] = 'Carta/acordo indeferido com sucesso. O parceiro poderá editar os dados e assinar novamente.';
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

$assinou = !empty($parceiro['acordo_aceito']) || !empty($parceiro['data_assinatura']) || !empty($parceiro['assinatura_digital_url']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Indeferir Carta/Acordo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-warning shadow-sm">
                <div class="card-header bg-warning">
                    <h4 class="mb-0">Indeferir carta/acordo do parceiro</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        Esta ação cancela a assinatura atual e libera o parceiro para editar os dados e realizar uma nova assinatura.
                    </div>

                    <table class="table table-bordered align-middle">
                        <tbody>
                            <tr>
                                <th width="240">ID</th>
                                <td><?= (int) $parceiro['id'] ?></td>
                            </tr>
                            <tr>
                                <th>Razão social</th>
                                <td><?= htmlspecialchars((string) $parceiro['razao_social']) ?></td>
                            </tr>
                            <tr>
                                <th>Nome fantasia</th>
                                <td><?= htmlspecialchars((string) $parceiro['nome_fantasia']) ?></td>
                            </tr>
                            <tr>
                                <th>E-mail de login</th>
                                <td><?= htmlspecialchars((string) $parceiro['email_login']) ?></td>
                            </tr>
                            <tr>
                                <th>Contrato</th>
                                <td>#<?= (int) $parceiro['contrato_id'] ?></td>
                            </tr>
                            <tr>
                                <th>Status atual</th>
                                <td>
                                    <?php if ($assinou): ?>
                                        <span class="badge text-bg-success">Assinado</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-secondary">Sem assinatura ativa</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Data da assinatura</th>
                                <td><?= !empty($parceiro['data_assinatura']) ? htmlspecialchars((string) $parceiro['dataassinatura']) : '-' ?></td>
                            </tr>
                            <tr>
                                <th>Arquivo da assinatura</th>
                                <td><?= !empty($parceiro['assinatura_digital_url']) ? htmlspecialchars((string) $parceiro['assinatura_digital_url']) : '-' ?></td>
                            </tr>
                        </tbody>
                    </table>

                    <form method="post">
                        <input type="hidden" name="confirmar_indeferimento" value="1">

                        <div class="mb-3">
                            <label for="motivo" class="form-label">Motivo do indeferimento</label>
                            <textarea name="motivo" id="motivo" class="form-control" rows="4" placeholder="Descreva o motivo para registro interno ou para futura comunicação."></textarea>
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <button type="submit" class="btn btn-warning">Confirmar indeferimento</button>
                            <a href="visualizar_parceiro.php?id=<?= (int) $parceiro['id'] ?>" class="btn btn-outline-secondary">Cancelar</a>
                            <a href="parceiros.php" class="btn btn-light border">Voltar à lista</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
