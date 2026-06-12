<?php
// ============================================================
// admin/excluir_parceiro.php
// Exclui permanentemente um parceiro e seus dados relacionados
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

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    die("ID inválido.");
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['msg_erro'] = 'ID de parceiro inválido.';
    header('Location: parceiros.php');
    exit;
}

$stmt = $pdo->prepare("SELECT id, razaos_ocial, nome_fantasia, email_login, rep_nome, rep_email FROM parceiros WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$parceiro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$parceiro) {
    $_SESSION['msg_erro'] = 'Parceiro não encontrado.';
    header('Location: parceiros.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_exclusao'])) {
    try {
        $pdo->beginTransaction();

        // Tabelas sem garantia de cascade explícita no dump
        $pdo->prepare('DELETE FROM parceiro_ods WHERE parceiro_id = ?')->execute([$id]);
        $pdo->prepare('DELETE FROM parceiro_interesses WHERE parceiro_id = ?')->execute([$id]);
        $pdo->prepare('DELETE FROM parceiro_etapa_extra WHERE parceiro_id = ?')->execute([$id]);
        $pdo->prepare('DELETE FROM parceiro_contrato WHERE parceiro_id = ?')->execute([$id]);
        $pdo->prepare('DELETE FROM parceiros_perfil WHERE parceiro_id = ?')->execute([$id]);

        // Registro principal
        $pdo->prepare('DELETE FROM parceiros WHERE id = ?')->execute([$id]);

        $pdo->commit();

        $_SESSION['msg_sucesso'] = 'Parceiro excluído com sucesso.';
        header('Location: parceiros.php');
        exit;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['msg_erro'] = 'Erro ao excluir parceiro: ' . $e->getMessage();
        header('Location: visualizar_parceiro.php?id=' . $id);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir Parceiro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-danger shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">Excluir cadastro de parceiro</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger">
                        <strong>Atenção:</strong> esta ação remove permanentemente o parceiro e os dados relacionados. Não há desfazer.
                    </div>

                    <table class="table table-bordered align-middle">
                        <tbody>
                            <tr>
                                <th width="220">ID</th>
                                <td><?= (int) $parceiro['id'] ?></td>
                            </tr>
                            <tr>
                                <th>Razão social</th>
                                <td><?= htmlspecialchars((string) $parceiro['razaosocial']) ?></td>
                            </tr>
                            <tr>
                                <th>Nome fantasia</th>
                                <td><?= htmlspecialchars((string) $parceiro['nomefantasia']) ?></td>
                            </tr>
                            <tr>
                                <th>E-mail de login</th>
                                <td><?= htmlspecialchars((string) $parceiro['emaillogin']) ?></td>
                            </tr>
                            <tr>
                                <th>Representante</th>
                                <td><?= htmlspecialchars((string) $parceiro['repnome']) ?></td>
                            </tr>
                            <tr>
                                <th>E-mail do representante</th>
                                <td><?= htmlspecialchars((string) $parceiro['repemail']) ?></td>
                            </tr>
                        </tbody>
                    </table>

                    <form method="post" class="d-flex gap-2 flex-wrap">
                        <input type="hidden" name="confirmar_exclusao" value="1">
                        <button type="submit" class="btn btn-danger">Sim, excluir permanentemente</button>
                        <a href="visualizar_parceiro.php?id=<?= (int) $parceiro['id'] ?>" class="btn btn-outline-secondary">Cancelar</a>
                        <a href="parceiros.php" class="btn btn-light border">Voltar à lista</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
