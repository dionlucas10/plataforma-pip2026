<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once __DIR__ . '/../app/helpers/auth.php';
require_admin_login();

// /public/blog/arquivos.php
$config = require __DIR__ . '/../app/config/db.php';
$pdo = new PDO(
    "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}",
    $config['user'], $config['pass'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

if (session_status() === PHP_SESSION_NONE) session_start();

$pageTitle = 'Blog — Publicações';

// ── Ações rápidas ─────────────────────────────────────────────────────────────
$msg = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);

    if ($action === 'delete' && $id) {
        $pdo->prepare("DELETE FROM blog_posts WHERE id = ?")->execute([$id]);
        $msg = 'Post excluído.'; $msgType = 'success';
    }
    if ($action === 'toggle_status' && $id) {
        $post = $pdo->prepare("SELECT status FROM blog_posts WHERE id = ?");
        $post->execute([$id]);
        $current = $post->fetchColumn();
        $new = $current === 'publicado' ? 'rascunho' : 'publicado';
        $pub = $new === 'publicado' ? date('Y-m-d H:i:s') : null;
        $pdo->prepare("UPDATE blog_posts SET status=?, publicado_em=? WHERE id=?")->execute([$new, $pub, $id]);
        $msg = 'Status alterado.'; $msgType = 'success';
    }
}

// ── Filtros ───────────────────────────────────────────────────────────────────
$filtroStatus = $_GET['status'] ?? '';
$filtroTipo   = $_GET['tipo']   ?? '';
$filtroBusca  = trim($_GET['q'] ?? '');
$paginaAtual  = max(1, (int)($_GET['p'] ?? 1));
$porPagina    = 15;
$offset       = ($paginaAtual - 1) * $porPagina;

$where  = ['1=1'];
$params = [];

if ($filtroStatus) { $where[] = 'bp.status = ?';         $params[] = $filtroStatus; }
if ($filtroTipo)   { $where[] = 'bp.tipo = ?';           $params[] = $filtroTipo; }
if ($filtroBusca)  { $where[] = 'bp.titulo LIKE ?';      $params[] = "%$filtroBusca%"; }

$whereSQL = implode(' AND ', $where);

$total = $pdo->prepare("SELECT COUNT(*) FROM blog_posts bp WHERE $whereSQL");
$total->execute($params);
$totalPosts = (int)$total->fetchColumn();
$totalPaginas = (int)ceil($totalPosts / $porPagina);

$stmt = $pdo->prepare("
    SELECT bp.*,
           u.name AS autor_nome,
           (SELECT COUNT(*) FROM blog_posts_revisoes r WHERE r.post_id = bp.id AND r.status = 'pendente') AS revisoes_pendentes
    FROM blog_posts bp
    LEFT JOIN users u ON u.id = bp.autor_id
    WHERE $whereSQL
    ORDER BY bp.created_at DESC
    LIMIT $porPagina OFFSET $offset
");
$stmt->execute($params);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Contadores para abas
$contadores = $pdo->query("
    SELECT status, COUNT(*) as total FROM blog_posts GROUP BY status
")->fetchAll(PDO::FETCH_KEY_PAIR);

include __DIR__ . '/../../app/views/admin/header.php';
?>

<div class="container-fluid px-4 py-4">

  <nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="/admin/dashboard.php">Dashboard</a></li>
      <li class="breadcrumb-item active">Blog</li>
    </ol>
  </nav>

  <div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h4 mb-0"><i class="bi bi-newspaper me-2"></i>Blog — Publicações</h1>
    <div class="d-flex gap-2">
      <a href="/blog/blog_categorias.php" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-folder2-open me-1"></i>Categorias
      </a>
      <a href="/blog/blog_post_form.php" class="btn btn-sm btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Nova publicação
      </a>
    </div>
  </div>

  <?php if ($msg): ?>
    <div class="alert alert-<?= $msgType ?> alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($msg) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Abas de status -->
  <ul class="nav nav-tabs mb-4">
    <?php
    $abas = [
      ''                     => 'Todos',
      'publicado'            => 'Publicados',
      'rascunho'             => 'Rascunhos',
      'aguardando_moderacao' => 'Aguard. Moderação',
      'rejeitado'            => 'Rejeitados',
    ];
    foreach ($abas as $val => $label):
      $cnt = $val ? ($contadores[$val] ?? 0) : array_sum($contadores);
    ?>
      <li class="nav-item">
        <a class="nav-link <?= $filtroStatus === $val ? 'active' : '' ?>"
           href="?status=<?= $val ?>&tipo=<?= urlencode($filtroTipo) ?>&q=<?= urlencode($filtroBusca) ?>">
          <?= $label ?>
          <?php if ($cnt > 0): ?>
            <span class="badge <?= $filtroStatus === $val ? 'bg-primary' : 'bg-secondary' ?> ms-1"><?= $cnt ?></span>
          <?php endif; ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>

  <!-- Filtros -->
  <form method="GET" class="row g-2 mb-4">
    <input type="hidden" name="status" value="<?= htmlspecialchars($filtroStatus) ?>">
    <div class="col-md-5">
      <input type="text" name="q" class="form-control form-control-sm"
             placeholder="Buscar por título..." value="<?= htmlspecialchars($filtroBusca) ?>">
    </div>
    <div class="col-md-3">
      <select name="tipo" class="form-select form-select-sm">
        <option value="">Todos os tipos</option>
        <option value="publicacao" <?= $filtroTipo === 'publicacao' ? 'selected' : '' ?>>Publicação</option>
        <option value="podcast"    <?= $filtroTipo === 'podcast'    ? 'selected' : '' ?>>Podcast</option>
        <option value="video"      <?= $filtroTipo === 'video'      ? 'selected' : '' ?>>Vídeo</option>
      </select>
    </div>
    <div class="col-auto">
      <button type="submit" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-search me-1"></i>Filtrar
      </button>
      <a href="/blog/arquivos.php" class="btn btn-sm btn-outline-secondary ms-1">Limpar</a>
    </div>
  </form>

  <!-- Tabela de posts -->
  <div class="card shadow-sm">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Título</th>
            <th class="text-center">Tipo</th>
            <th>Autor</th>
            <th class="text-center">Status</th>
            <th class="text-center">Data</th>
            <th class="text-center">Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($posts)): ?>
            <tr>
              <td colspan="6" class="text-center text-muted py-5">
                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                Nenhuma publicação encontrada.
              </td>
            </tr>
          <?php endif; ?>
          <?php foreach ($posts as $post): ?>
            <tr>
              <td>
                <div class="fw-semibold"><?= htmlspecialchars($post['titulo']) ?></div>
                <div class="text-muted small">/blog/<?= htmlspecialchars($post['slug']) ?></div>
                <?php if ($post['revisoes_pendentes'] > 0): ?>
                  <span class="badge bg-warning text-dark mt-1">
                    <i class="bi bi-clock me-1"></i><?= $post['revisoes_pendentes'] ?> revisão pendente
                  </span>
                <?php endif; ?>
              </td>
              <td class="text-center">
                <?php
                $tipos = [
                  'publicacao' => ['icon' => 'file-text', 'class' => 'bg-primary'],
                  'podcast'    => ['icon' => 'mic-fill',  'class' => 'bg-info text-dark'],
                  'video'      => ['icon' => 'play-circle-fill', 'class' => 'bg-danger'],
                ];
                $t = $tipos[$post['tipo']] ?? ['icon' => 'file', 'class' => 'bg-secondary'];
                ?>
                <span class="badge <?= $t['class'] ?>">
                  <i class="bi bi-<?= $t['icon'] ?> me-1"></i>
                  <?= ucfirst($post['tipo']) ?>
                </span>
              </td>
              <td class="text-muted small"><?= htmlspecialchars($post['autor_nome'] ?? '—') ?></td>
              <td class="text-center">
                <?php
                $badges = [
                  'publicado'            => 'success',
                  'rascunho'             => 'secondary',
                  'aguardando_moderacao' => 'warning text-dark',
                  'rejeitado'            => 'danger',
                ];
                $labels = [
                  'publicado'            => 'Publicado',
                  'rascunho'             => 'Rascunho',
                  'aguardando_moderacao' => 'Moderação',
                  'rejeitado'            => 'Rejeitado',
                ];
                $bc = $badges[$post['status']] ?? 'secondary';
                $bl = $labels[$post['status']] ?? $post['status'];
                ?>
                <span class="badge bg-<?= $bc ?>"><?= $bl ?></span>
              </td>
              <td class="text-center text-muted small">
                <?= $post['publicado_em']
                    ? date('d/m/Y H:i', strtotime($post['publicado_em']))
                    : date('d/m/Y', strtotime($post['created_at'])) ?>
              </td>
              <td class="text-center">
                <div class="d-flex justify-content-center gap-1">
                  <a href="/blog/blog_post_form.php?id=<?= $post['id'] ?>"
                     class="btn btn-sm btn-outline-primary" title="Editar">
                    <i class="bi bi-pencil"></i>
                  </a>
                  <form method="POST" class="d-inline">
                    <input type="hidden" name="action" value="toggle_status">
                    <input type="hidden" name="id" value="<?= $post['id'] ?>">
                    <button type="submit"
                            class="btn btn-sm <?= $post['status'] === 'publicado' ? 'btn-outline-warning' : 'btn-outline-success' ?>"
                            title="<?= $post['status'] === 'publicado' ? 'Despublicar' : 'Publicar' ?>">
                      <i class="bi bi-<?= $post['status'] === 'publicado' ? 'eye-slash' : 'eye' ?>"></i>
                    </button>
                  </form>
                  <form method="POST" class="d-inline"
                        onsubmit="return confirm('Excluir este post permanentemente?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $post['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir">
                      <i class="bi bi-trash"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Paginação -->
    <?php if ($totalPaginas > 1): ?>
      <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">
          Mostrando <?= min($offset + 1, $totalPosts) ?>–<?= min($offset + $porPagina, $totalPosts) ?> de <?= $totalPosts ?>
        </small>
        <nav>
          <ul class="pagination pagination-sm mb-0">
            <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
              <li class="page-item <?= $p === $paginaAtual ? 'active' : '' ?>">
                <a class="page-link"
                   href="?p=<?= $p ?>&status=<?= urlencode($filtroStatus) ?>&tipo=<?= urlencode($filtroTipo) ?>&q=<?= urlencode($filtroBusca) ?>">
                  <?= $p ?>
                </a>
              </li>
            <?php endfor; ?>
          </ul>
        </nav>
      </div>
    <?php endif; ?>
  </div>

</div>

<?php include __DIR__ . '/../../app/views/admin/footer.php'; ?>
