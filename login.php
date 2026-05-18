<?php
session_start();
$pageTitle = 'Login — Impactos Positivos';
$redirect  = trim($_GET['redirect'] ?? '');
$redirect  = ($redirect && str_starts_with($redirect, '/')) ? $redirect : '';
include __DIR__ . '/app/views/public/header_public.php';
?>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-sm-10 col-md-8 col-lg-6 col-xl-5">

      <!-- Cabeçalho -->
      <div class="text-center mb-4">
        <i class="bi bi-person-circle" style="font-size:2.8rem; color:#1E3425;"></i>
        <h1 class="h4 fw-bold mt-2 mb-1" style="color:#1E3425;">Bem-vindo de volta</h1>
        <p class="text-muted mb-0" style="font-size:.9rem;">
          Empreendedores, Parceiros e Sociedade Civil acessam pela mesma tela.
        </p>
      </div>

      <!-- Alerta de erro -->
      <?php if (!empty($_SESSION['login_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          <?= htmlspecialchars($_SESSION['login_error'], ENT_QUOTES, 'UTF-8') ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
        <?php unset($_SESSION['login_error']); ?>
      <?php endif; ?>

      <!-- Formulário único -->
      <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">

          <form method="POST" action="/auth/processar_login_unificado.php" novalidate>
            <?php if ($redirect): ?>
              <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect, ENT_QUOTES) ?>">
            <?php endif; ?>

            <div class="mb-3">
              <label for="login" class="form-label fw-semibold">E-mail, CPF ou CNPJ</label>
              <input
                type="text"
                id="login"
                name="login"
                class="form-control form-control-lg"
                placeholder="seu@email.com ou CPF/CNPJ"
                autocomplete="username"
                required
              >
              <div class="form-text">
                Empreendedor: e-mail &nbsp;·&nbsp; Parceiro: e-mail ou CNPJ &nbsp;·&nbsp; Sociedade Civil: e-mail ou CPF
              </div>
            </div>

            <div class="mb-4">
              <label for="senha" class="form-label fw-semibold">Senha</label>
              <div class="input-group">
                <input
                  type="password"
                  id="senha"
                  name="senha"
                  class="form-control form-control-lg"
                  placeholder="Sua senha"
                  autocomplete="current-password"
                  required
                >
                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="senha" aria-label="Mostrar/ocultar senha">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
            </div>

            <div class="d-grid">
              <button type="submit" class="btn btn-lg fw-semibold" style="background:#1E3425; color:#fff;">
                <i class="bi bi-box-arrow-in-right me-2"></i>Entrar
              </button>
            </div>
          </form>

          <hr class="my-4">

          <!-- Links de cadastro por perfil -->
          <p class="text-center text-muted mb-2" style="font-size:.82rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em;">Ainda não tem conta?</p>
          <div class="d-flex flex-column gap-2">
            <a href="/empreendedores/cadastro.php" class="btn btn-outline-success btn-sm">
              <i class="bi bi-rocket-takeoff me-2"></i>Cadastrar como Negócio de Impacto
            </a>
            <a href="/parceiros/cadastro.php" class="btn btn-outline-primary btn-sm">
              <i class="bi bi-diagram-3 me-2"></i>Cadastrar como Parceiro
            </a>
            <a href="/sociedade_civil/cadastro.php" class="btn btn-outline-secondary btn-sm">
              <i class="bi bi-people me-2"></i>Cadastrar como Sociedade Civil
            </a>
          </div>

        </div>
      </div>
      <!-- fim card -->

    </div>
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
