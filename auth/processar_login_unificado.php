<?php
// /auth/processar_login_unificado.php
// Processa login unificado: empreendedor, parceiro e sociedade civil.
// Detecta automaticamente o tipo de usuário consultando as três tabelas.
declare(strict_types=1);
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../app/helpers/functions.php';

$config = require __DIR__ . '/../app/config/db.php';
try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};port={$config['port']};charset={$config['charset']}",
        $config['user'],
        $config['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    error_log('Login unificado — DB error: ' . $e->getMessage());
    $_SESSION['login_error'] = 'Erro interno ao conectar ao banco de dados. Tente novamente.';
    header('Location: /login.php');
    exit;
}

$login    = trim($_POST['login']    ?? '');
$senha    = $_POST['senha']         ?? '';
$redirect = trim($_POST['redirect'] ?? '');
$redirect = ($redirect && str_starts_with($redirect, '/')) ? $redirect : '';

if ($login === '' || $senha === '') {
    $_SESSION['login_error'] = 'Informe seu e-mail (ou CPF/CNPJ) e a senha.';
    header('Location: /login.php' . ($redirect ? '?redirect=' . urlencode($redirect) : ''));
    exit;
}

// Normaliza para buscas numéricas (CPF/CNPJ sem formatação)
$soNumeros = preg_replace('/[^0-9]/', '', $login);

// ─────────────────────────────────────────────────────────────────────────────
// 1. Empreendedor  (email)
// ─────────────────────────────────────────────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT id, nome, email, senha_hash
    FROM empreendedores
    WHERE email = ?
    LIMIT 1
");
$stmt->execute([sanitize_email($login)]);
$empreendedor = $stmt->fetch(PDO::FETCH_ASSOC);

if ($empreendedor && password_verify($senha, $empreendedor['senha_hash'])) {
    session_regenerate_id(true);
    $_SESSION['user_id']            = (int)$empreendedor['id'];
    $_SESSION['user_role']          = 'empreendedor';
    $_SESSION['empreendedor_id']    = (int)$empreendedor['id'];
    $_SESSION['empreendedor_nome']  = $empreendedor['nome'];
    $_SESSION['empreendedor_email'] = $empreendedor['email'];
    $_SESSION['logged_at']          = time();

    $pdo->prepare("
        UPDATE empreendedores
        SET status = CASE WHEN primeiro_acesso_pendente = 1 THEN 'ativo' ELSE status END,
            primeiro_acesso_pendente = 0,
            ultimo_login = NOW()
        WHERE id = ?
    ")->execute([$empreendedor['id']]);

    header('Location: ' . ($redirect ?: '/empreendedores/dashboard.php'));
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// 2. Parceiro  (email_login OU cnpj)
// ─────────────────────────────────────────────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT id, nome_fantasia, senha_hash, etapa_atual
    FROM parceiros
    WHERE email_login = ? OR cnpj = ?
    LIMIT 1
");
$stmt->execute([$login, $soNumeros]);
$parceiro = $stmt->fetch(PDO::FETCH_ASSOC);

if ($parceiro && password_verify($senha, $parceiro['senha_hash'])) {
    session_regenerate_id(true);
    $_SESSION['parceiro_id']   = (int)$parceiro['id'];
    $_SESSION['parceiro_nome'] = $parceiro['nome_fantasia'];

    if ($redirect) {
        header('Location: ' . $redirect);
        exit;
    }

    // Redireciona pela etapa do cadastro
    $etapa = (int)$parceiro['etapa_atual'];
    $destinos = [
        1 => '/parceiros/etapa1_dados.php',
        2 => '/parceiros/etapa2_tipo.php',
        3 => '/parceiros/etapa3_combinado.php',
        4 => '/parceiros/etapa4_interesses.php',
        5 => '/parceiros/etapa5_plataforma.php',
        6 => '/parceiros/etapa6_juridico.php',
    ];
    header('Location: ' . ($destinos[$etapa] ?? '/parceiros/dashboard.php'));
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// 3. Sociedade Civil  (email OU cpf)
// ─────────────────────────────────────────────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT id, nome, email, senha_hash
    FROM sociedade_civil
    WHERE email = ? OR cpf = ?
    LIMIT 1
");
$stmt->execute([$login, $soNumeros]);
$sociedade = $stmt->fetch(PDO::FETCH_ASSOC);

if ($sociedade && password_verify($senha, $sociedade['senha_hash'])) {
    session_regenerate_id(false);
    $_SESSION['logado']        = true;
    $_SESSION['usuario_id']    = (int)$sociedade['id'];
    $_SESSION['usuario_nome']  = $sociedade['nome'];
    $_SESSION['usuario_email'] = $sociedade['email'];
    $_SESSION['usuario_tipo']  = 'sociedade_civil';

    header('Location: ' . ($redirect ?: '/sociedade_civil/minha_conta.php'));
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// Nenhum usuário encontrado ou senha incorreta
// ─────────────────────────────────────────────────────────────────────────────
$_SESSION['login_error'] = 'E-mail, CPF, CNPJ ou senha inválidos. Verifique seus dados e tente novamente.';
header('Location: /login.php' . ($redirect ? '?redirect=' . urlencode($redirect) : ''));
exit;
