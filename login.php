<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$config = require __DIR__ . '/app/config/db.php';
$pdo = new PDO(
    "mysql:host={$config['host']};dbname={$config['dbname']};port={$config['port']};charset={$config['charset']}",
    $config['user'],
    $config['pass'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
$pageTitle = 'Login — Impactos Positivos';
$redirect  = trim($_GET['redirect'] ?? '');
$redirect  = ($redirect && str_starts_with($redirect, '/')) ? $redirect : '';
include __DIR__ . '/app/views/public/header_public.php';
?>

<div class="container-fluid px-3 px-md-4 px-lg-5">
    <div class="ip-login-wrap">

    <!-- ══════════════ COLUNA ESQUERDA — LOGIN ══════════════ -->
    <div class="ip-login-col">

        <div class="ip-brand-badge">
        <i class="bi bi-person-circle"></i>
        <span>Login do usuário</span>
        </div>

        <h1>Bem-vindo de volta!</h1>
        <p class="ip-sub">Entre com suas credenciais para acessar a plataforma.</p>

        <!-- Alerta de erro -->
        <?php if (!empty($_SESSION['login_error'])): ?>
        <div class="ip-alert-error" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span><?= htmlspecialchars($_SESSION['login_error'], ENT_QUOTES, 'UTF-8') ?></span>
            <button type="button" onclick="this.closest('.ip-alert-error').remove()" aria-label="Fechar">
            <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <?php unset($_SESSION['login_error']); ?>
        <?php endif; ?>

        <!-- Formulário único -->
        <form method="POST" action="/auth/processar_login_unificado.php" novalidate>
        <?php if ($redirect): ?>
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect, ENT_QUOTES) ?>">
        <?php endif; ?>

        <div class="ip-field">
            <label for="login">E-mail, CPF ou CNPJ</label>
            <div class="input-wrap">
            <input
                type="text"
                id="login"
                name="login"
                placeholder="seu@email.com ou CPF/CNPJ"
                autocomplete="username"
                required
            >
            </div>
            <p class="ip-hint">Empreendedor: E-mail &nbsp;·&nbsp; Parceiro: E-mail ou CNPJ &nbsp;·&nbsp; Sociedade Civil: E-mail ou CPF</p>
        </div>

        <div class="ip-field">
            <label for="senha">Senha</label>
            <div class="input-wrap">
            <input
                type="password"
                id="senha"
                name="senha"
                placeholder="Sua senha"
                autocomplete="current-password"
                required
            >
            <button class="ip-eye toggle-password" type="button" data-target="senha" aria-label="Mostrar/ocultar senha">
                <i class="bi bi-eye"></i>
            </button>
            </div>
        </div>

        <div class="ip-forgot">
            <a href="/auth/forgot_password_form.php">Esqueci minha senha</a>
        </div>

        <button type="submit" class="ip-btn-login">
            <i class="bi bi-box-arrow-in-right"></i>
            Entrar na plataforma
        </button>
        </form>

        <!-- Divisor visível apenas em mobile -->
        <div class="ip-divider-mobile">
        <span></span><p>Ainda não tem conta?</p><span></span>
        </div>

    </div>
    <!-- fim coluna login -->


    <!-- ══════════════ COLUNA DIREITA — CADASTRO ══════════════ -->
    <div class="ip-register-col">

        <div class="ip-reg-header">
        <p class="ip-eyebrow">Ainda não tem conta?</p>
        <h2>Escolha seu perfil<br>e cadastre-se</h2>
        <p>Selecione o tipo de conta que melhor representa você ou sua organização para começar.</p>
        </div>

        <div class="ip-profile-cards">

        <a href="/empreendedores/register.php" class="ip-profile-card negocio">
            <div class="ip-card-icon">
            <i class="bi bi-rocket-takeoff-fill"></i>
            </div>
            <div class="ip-card-text">
            <strong>Negócio de Impacto</strong>
            <span>Empresas e startups com propósito social ou ambiental</span>
            </div>
            <i class="bi bi-arrow-right ip-card-arrow"></i>
        </a>

        <a href="/parceiros/cadastro.php" class="ip-profile-card parceiro">
            <div class="ip-card-icon">
            <i class="bi bi-diagram-3-fill"></i>
            </div>
            <div class="ip-card-text">
            <strong>Parceiro</strong>
            <span>Organizações, fundações e investidores de impacto</span>
            </div>
            <i class="bi bi-arrow-right ip-card-arrow"></i>
        </a>

        <a href="cadastro.php" class="ip-profile-card sociedade">
            <div class="ip-card-icon">
            <i class="bi bi-people-fill"></i>
            </div>
            <div class="ip-card-text">
            <strong>Sociedade Civil</strong>
            <span>Pessoas físicas engajadas em transformação social</span>
            </div>
            <i class="bi bi-arrow-right ip-card-arrow"></i>
        </a>

        </div>

    </div>
    <!-- fim coluna cadastro -->

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.toggle-password').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var input = document.getElementById(this.getAttribute('data-target'));
      var icon  = this.querySelector('i');
      if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
      } else {
        input.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
      }
    });
  });
});
</script>

<?php include __DIR__ . '/app/views/public/footer_public.php'; ?>