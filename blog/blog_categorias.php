<?php
// /public/blog/blog_categorias.php
require_once __DIR__ . '/../../app/config/db.php';
require_once __DIR__ . '/../../app/auth.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Apenas admin/superadmin
$role = $_SESSION['user_role'] ?? '';
if (!in_array($role, ['admin', 'superadmin'], true)) {
    http_response_code(403);
    die('Acesso negado.');
}

$pageTitle = 'Blog — Categorias';
$msg = '';
$msgType = '';

// ── CRUD Actions ─────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'edit') {
        $nome      = trim($_POST['nome'] ?? '');
        $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        $ordem     = (int)($_POST['ordem'] ?? 0);
        $descricao = trim($_POST['descricao'] ?? '');

        if ($nome === '') {
            $msg = 'O nome da categoria é obrigatório.';
            $msgType = 'danger';
        } else {
            // Gera slug
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $nome));
            $slug = trim($slug, '-');

            if ($action === 'create') {
                $stmt = $pdo->prepare("INSERT INTO blog_categorias (nome, slug, parent_id, ordem, descricao) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$nome, $slug, $parent_id, $ordem, $descricao ?: null]);
                $msg = 'Categoria criada com sucesso.';
                $msgType = 'success';
            } else {
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("UPDATE blog_categorias SET nome=?, slug=?, parent_id=?, ordem=?, descricao=? WHERE id=?");
                $stmt->execute([$nome, $slug, $parent_id, $ordem, $descricao ?: null, $id]);
                $msg = 'Categoria atualizada com sucesso.';
                $msgType = 'success';
            }
        }
    }

    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        // Verifica se tem posts vinculados
        $count = $pdo->prepare("SELECT COUNT(*) FROM blog_post_categorias WHERE categoria_id = ?");
        $count->execute([$id]);
        if ($count->fetchColumn() > 0) {
            $msg = 'Não é possível excluir: há posts vinculados a esta categoria.';
            $msgType = 'warning';
        } else {
            $pdo->prepare("DELETE FROM blog_categorias WHERE id = ?")->execute([$id]);
            $msg = 'Categoria excluída.';
            $msgType = 'success';
        }
    }

    if ($action === 'toggle') {
        $id = (int)$_POST['id'];
        $pdo->prepare("UPDATE blog_categorias SET ativo = NOT ativo WHERE id = ?")->execute([$id]);
        $msg = 'Status alterado.';
        $msgType = 'success';
    }
}

// ── Busca categorias ─────────────────────────────────────────────────────────
$categorias = $pdo->query("
    SELECT c.*, p.nome AS parent_nome,
           (SELECT COUNT(*) FROM blog_post_categorias bpc WHERE bpc.categoria_id = c.id) AS total_posts
    FROM blog_categorias c
    LEFT JOIN blog_categorias p ON p.id = c.parent_id
    ORDER BY COALESCE(c.parent_id, c.id), c.ordem, c.nome
")->fetchAll(PDO::FETCH_ASSOC);

// Categorias raiz para select
$raizes = array_filter($categorias, fn($c) => $c['parent_id'] === null);

// Categoria em edição
$editing = null;
if (!empty($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    foreach ($categorias as $c) {
        if ($c['id'] === $editId) { $editing = $c; break; }
    }
}

include __DIR__ . '/../../app/views/admin/header.php';
?>

<div class="container-fluid px-4 py-4">

  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="/admin/dashboard.php">Dashboard</a></li>
      <li class="breadcrumb-item"><a href="/blog/arquivos.php">Blog</a></li>
      <li class="breadcrumb-item active">Categorias</li>
    </ol>
  </nav>

  <div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h4 mb-0"><i class="bi bi-folder2-open me-2"></i>Categorias do Blog</h1>
    <a href="/blog/arquivos.php" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-arrow-left me-1"></i>Voltar ao Blog
    </a>
  </div>

  <?php if ($msg): ?>
    <div class="alert alert-<?= $msgType ?> alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($msg) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <div class="row g-4">

    <!-- Formulário -->
    <div class="col-lg-4">
      <div class="card shadow-sm">
        <div class="card-header">
          <h5 class="mb-0">
            <i class="bi bi-<?= $editing ? 'pencil' : 'plus-circle' ?> me-2"></i>
            <?= $editing ? 'Editar Categoria' : 'Nova Categoria' ?>
          </h5>
        </div>
        <div class="card-body">
          <form method="POST">
            <input type="hidden" name="action" value="<?= $editing ? 'edit' : 'create' ?>">
            <?php if ($editing): ?>
              <input type="hidden" name="id" value="<?= $editing['id'] ?>">
            <?php endif; ?>

            <div class="mb-3">
              <label class="form-label fw-semibold">Nome <span class="text-danger">*</span></label>
              <input type="text" name="nome" class="form-control"
                     value="<?= htmlspecialchars($editing['nome'] ?? '') ?>"
                     placeholder="Ex: Notícias" required>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Categoria pai</label>
              <select name="parent_id" class="form-select">
                <option value="">— Nenhuma (categoria raiz) —</option>
                <?php foreach ($raizes as $r):
                  if ($editing && $r['id'] === $editing['id']) continue; ?>
                  <option value="<?= $r['id'] ?>"
                    <?= ($editing['parent_id'] ?? null) == $r['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($r['nome']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Descrição</label>
              <textarea name="descricao" class="form-control" rows="2"
                        placeholder="Descrição breve (opcional)"><?= htmlspecialchars($editing['descricao'] ?? '') ?></textarea>
            </div>

            <div class="mb-4">
              <label class="form-label fw-semibold">Ordem</label>
              <input type="number" name="ordem" class="form-control" min="0"
                     value="<?= $editing['ordem'] ?? 0 ?>">
              <div class="form-text">Menor número aparece primeiro.</div>
            </div>

            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg me-1"></i>
                <?= $editing ? 'Salvar alterações' : 'Criar categoria' ?>
              </button>
              <?php if ($editing): ?>
                <a href="/blog/blog_categorias.php" class="btn btn-outline-secondary">Cancelar</a>
              <?php endif; ?>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Listagem -->
    <div class="col-lg-8">
      <div class="card shadow-sm">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Categorias cadastradas</h5>
          <span class="badge bg-secondary"><?= count($categorias) ?></span>
        </div>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Nome</th>
                <th>Pai</th>
                <th class="text-center">Posts</th>
                <th class="text-center">Ordem</th>
                <th class="text-center">Status</th>
                <th class="text-center">Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($categorias)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Nenhuma categoria cadastrada.</td></tr>
              <?php endif; ?>
              <?php foreach ($categorias as $cat): ?>
                <tr class="<?= !$cat['ativo'] ? 'table-secondary opacity-75' : '' ?>">
                  <td>
                    <?php if ($cat['parent_id']): ?>
                      <span class="text-muted ms-3">↳</span>
                    <?php endif; ?>
                    <strong><?= htmlspecialchars($cat['nome']) ?></strong>
                    <div class="text-muted small">/<?= htmlspecialchars($cat['slug']) ?></div>
                  </td>
                  <td class="text-muted small"><?= $cat['parent_nome'] ? htmlspecialchars($cat['parent_nome']) : '—' ?></td>
                  <td class="text-center">
                    <span class="badge bg-light text-dark border"><?= $cat['total_posts'] ?></span>
                  </td>
                  <td class="text-center"><?= $cat['ordem'] ?></td>
                  <td class="text-center">
                    <form method="POST" class="d-inline">
                      <input type="hidden" name="action" value="toggle">
                      <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                      <button type="submit" class="btn btn-sm <?= $cat['ativo'] ? 'btn-success' : 'btn-outline-secondary' ?>">
                        <i class="bi bi-<?= $cat['ativo'] ? 'check-circle-fill' : 'x-circle' ?>"></i>
                        <?= $cat['ativo'] ? 'Ativo' : 'Inativo' ?>
                      </button>
                    </form>
                  </td>
                  <td class="text-center">
                    <div class="d-flex justify-content-center gap-1">
                      <a href="?edit=<?= $cat['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                        <i class="bi bi-pencil"></i>
                      </a>
                      <?php if ($cat['total_posts'] == 0): ?>
                        <form method="POST" onsubmit="return confirm('Excluir categoria \'<?= addslashes($cat['nome']) ?>\'?')">
                          <input type="hidden" name="action" value="delete">
                          <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                          <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir">
                            <i class="bi bi-trash"></i>
                          </button>
                        </form>
                      <?php else: ?>
                        <button class="btn btn-sm btn-outline-danger" disabled title="Possui posts vinculados">
                          <i class="bi bi-trash"></i>
                        </button>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../app/views/admin/footer.php'; ?>
