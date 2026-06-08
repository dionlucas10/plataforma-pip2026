<?php
// /public/blog/blog_post_form.php
require_once __DIR__ . '/../../app/config/db.php';
require_once __DIR__ . '/../../app/auth.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$role   = $_SESSION['user_role'] ?? '';
$userId = (int)($_SESSION['user_id'] ?? 0);

if (!in_array($role, ['admin', 'superadmin'], true)) {
    http_response_code(403); die('Acesso negado.');
}

$postId   = !empty($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit   = $postId > 0;
$post     = null;
$msg      = '';
$msgType  = '';

// ── Carrega post para edição ──────────────────────────────────────────────────
if ($isEdit) {
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
    $stmt->execute([$postId]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$post) { header('Location: /blog/arquivos.php'); exit; }

    // Categorias e tags do post
    $postCats = $pdo->prepare("SELECT categoria_id FROM blog_post_categorias WHERE post_id = ?");
    $postCats->execute([$postId]);
    $postCategorias = $postCats->fetchAll(PDO::FETCH_COLUMN);

    $postTagsStmt = $pdo->prepare("SELECT t.nome FROM blog_tags t JOIN blog_post_tags bpt ON bpt.tag_id = t.id WHERE bpt.post_id = ?");
    $postTagsStmt->execute([$postId]);
    $postTags = implode(', ', $postTagsStmt->fetchAll(PDO::FETCH_COLUMN));
}

// ── Salva post ────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo        = trim($_POST['titulo'] ?? '');
    $tipo          = $_POST['tipo'] ?? 'publicacao';
    $resumo        = trim($_POST['resumo'] ?? '');
    $conteudo      = $_POST['conteudo'] ?? '';
    $midia_url     = trim($_POST['midia_url'] ?? '');
    $capa_url      = trim($_POST['capa_url'] ?? '');
    $meta_titulo   = trim($_POST['meta_titulo'] ?? '');
    $meta_desc     = trim($_POST['meta_descricao'] ?? '');
    $status        = $_POST['status'] ?? 'rascunho';
    $categoriasSel = $_POST['categorias'] ?? [];
    $tagsRaw       = trim($_POST['tags'] ?? '');

    // Autor: admin pode escolher, outros usam o próprio
    $autorId = in_array($role, ['admin', 'superadmin']) && !empty($_POST['autor_id'])
               ? (int)$_POST['autor_id']
               : $userId;

    if ($titulo === '') {
        $msg = 'O título é obrigatório.'; $msgType = 'danger';
    } else {
        // Gera slug único
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $titulo));
        $slug = trim($slug, '-');
        $slugBase = $slug;
        $i = 1;
        while (true) {
            $check = $pdo->prepare("SELECT id FROM blog_posts WHERE slug = ? AND id != ?");
            $check->execute([$slug, $postId]);
            if (!$check->fetch()) break;
            $slug = $slugBase . '-' . $i++;
        }

        $pubEm = $status === 'publicado' ? date('Y-m-d H:i:s') : null;

        if ($isEdit) {
            // Admin edita direto; outros criam revisão pendente
            if (in_array($role, ['admin', 'superadmin'])) {
                $pdo->prepare("
                    UPDATE blog_posts SET titulo=?, slug=?, tipo=?, resumo=?, conteudo=?,
                    midia_url=?, capa_url=?, meta_titulo=?, meta_descricao=?,
                    status=?, publicado_em=COALESCE(publicado_em, ?), autor_id=?, updated_at=NOW()
                    WHERE id=?
                ")->execute([$titulo, $slug, $tipo, $resumo ?: null, $conteudo ?: null,
                             $midia_url ?: null, $capa_url ?: null, $meta_titulo ?: null,
                             $meta_desc ?: null, $status, $pubEm, $autorId, $postId]);
                $msg = 'Post atualizado com sucesso.'; $msgType = 'success';
            }
            // Sincroniza categorias
            $pdo->prepare("DELETE FROM blog_post_categorias WHERE post_id = ?")->execute([$postId]);
            foreach ($categoriasSel as $cid) {
                $pdo->prepare("INSERT IGNORE INTO blog_post_categorias (post_id, categoria_id) VALUES (?,?)")->execute([$postId, (int)$cid]);
            }
            $currentPostId = $postId;
        } else {
            $pdo->prepare("
                INSERT INTO blog_posts (titulo, slug, tipo, resumo, conteudo, midia_url, capa_url,
                meta_titulo, meta_descricao, status, publicado_em, autor_id)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
            ")->execute([$titulo, $slug, $tipo, $resumo ?: null, $conteudo ?: null,
                         $midia_url ?: null, $capa_url ?: null, $meta_titulo ?: null,
                         $meta_desc ?: null, $status, $pubEm, $autorId]);
            $currentPostId = (int)$pdo->lastInsertId();

            foreach ($categoriasSel as $cid) {
                $pdo->prepare("INSERT IGNORE INTO blog_post_categorias (post_id, categoria_id) VALUES (?,?)")->execute([$currentPostId, (int)$cid]);
            }
            $msg = 'Post criado com sucesso.'; $msgType = 'success';
            header("Location: /blog/blog_post_form.php?id=$currentPostId&msg=created");
            exit;
        }

        // Tags: cria se não existir e vincula
        $pdo->prepare("DELETE FROM blog_post_tags WHERE post_id = ?")->execute([$currentPostId]);
        if ($tagsRaw) {
            $tagNomes = array_unique(array_map('trim', explode(',', $tagsRaw)));
            foreach ($tagNomes as $tn) {
                if (!$tn) continue;
                $slugTag = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $tn));
                $slugTag = trim($slugTag, '-');
                $pdo->prepare("INSERT IGNORE INTO blog_tags (nome, slug) VALUES (?,?)")->execute([$tn, $slugTag]);
                $tagId = $pdo->query("SELECT id FROM blog_tags WHERE slug = " . $pdo->quote($slugTag))->fetchColumn();
                $pdo->prepare("INSERT IGNORE INTO blog_post_tags (post_id, tag_id) VALUES (?,?)")->execute([$currentPostId, $tagId]);
            }
        }
    }

    // Recarrega dados após salvar
    if ($isEdit) {
        $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
        $stmt->execute([$postId]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        $postCats->execute([$postId]);
        $postCategorias = $pdo->prepare("SELECT categoria_id FROM blog_post_categorias WHERE post_id = ?");
        $postCategorias->execute([$postId]);
        $postCategorias = $postCategorias->fetchAll(PDO::FETCH_COLUMN);
        $postTagsStmt->execute([$postId]);
        $postTags = implode(', ', $postTagsStmt->fetchAll(PDO::FETCH_COLUMN));
    }
}

// Dados para o formulário
$categorias = $pdo->query("SELECT * FROM blog_categorias WHERE ativo=1 ORDER BY ordem, nome")->fetchAll(PDO::FETCH_ASSOC);
$admins     = $pdo->query("SELECT id, name FROM users WHERE role IN ('admin','superadmin') ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$postCategorias = $postCategorias ?? [];
$postTags = $postTags ?? '';

$pageTitle = $isEdit ? 'Editar Post' : 'Novo Post';

include __DIR__ . '/../../app/views/admin/header.php';
?>

<!-- TinyMCE -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
  selector: '#conteudo',
  language: 'pt_BR',
  height: 450,
  plugins: 'link image lists table code media',
  toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter | bullist numlist | link image media | code',
  menubar: false,
  branding: false
});
</script>

<div class="container-fluid px-4 py-4">

  <nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="/admin/dashboard.php">Dashboard</a></li>
      <li class="breadcrumb-item"><a href="/blog/arquivos.php">Blog</a></li>
      <li class="breadcrumb-item active"><?= $isEdit ? 'Editar Post' : 'Novo Post' ?></li>
    </ol>
  </nav>

  <div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h4 mb-0">
      <i class="bi bi-<?= $isEdit ? 'pencil-square' : 'plus-circle' ?> me-2"></i>
      <?= $isEdit ? 'Editar Post' : 'Novo Post' ?>
    </h1>
    <a href="/blog/arquivos.php" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-arrow-left me-1"></i>Voltar
    </a>
  </div>

  <?php if ($msg): ?>
    <div class="alert alert-<?= $msgType ?> alert-dismissible fade show">
      <?= htmlspecialchars($msg) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <form method="POST" id="formPost">
    <div class="row g-4">

      <!-- Coluna principal -->
      <div class="col-lg-8">

        <!-- Tipo de postagem -->
        <div class="card shadow-sm mb-4">
          <div class="card-body">
            <label class="form-label fw-semibold">Tipo de postagem</label>
            <div class="d-flex gap-3">
              <?php foreach (['publicacao' => ['Publicação','file-text'], 'podcast' => ['Podcast','mic-fill'], 'video' => ['Vídeo','play-circle-fill']] as $val => [$label, $icon]): ?>
                <div class="form-check form-check-inline border rounded px-3 py-2 flex-fill text-center tipo-card">
                  <input class="form-check-input d-none" type="radio" name="tipo"
                         id="tipo_<?= $val ?>" value="<?= $val ?>"
                         <?= ($post['tipo'] ?? 'publicacao') === $val ? 'checked' : '' ?>>
                  <label class="form-check-label w-100" for="tipo_<?= $val ?>" style="cursor:pointer">
                    <i class="bi bi-<?= $icon ?> fs-4 d-block mb-1"></i>
                    <?= $label ?>
                  </label>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- Título -->
        <div class="card shadow-sm mb-4">
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label fw-semibold">Título <span class="text-danger">*</span></label>
              <input type="text" name="titulo" id="titulo" class="form-control form-control-lg"
                     value="<?= htmlspecialchars($post['titulo'] ?? '') ?>"
                     placeholder="Título do post" required>
              <div class="form-text">
                Slug gerado: <code id="slugPreview"><?= htmlspecialchars($post['slug'] ?? '') ?></code>
              </div>
            </div>
            <div class="mb-0">
              <label class="form-label fw-semibold">Resumo</label>
              <textarea name="resumo" class="form-control" rows="2"
                        placeholder="Resumo breve para listagem e SEO (máx. 500 caracteres)"
                        maxlength="500"><?= htmlspecialchars($post['resumo'] ?? '') ?></textarea>
            </div>
          </div>
        </div>

        <!-- Conteúdo (publicacao) -->
        <div class="card shadow-sm mb-4" id="blocoConteudo">
          <div class="card-header"><h5 class="mb-0"><i class="bi bi-text-paragraph me-2"></i>Conteúdo</h5></div>
          <div class="card-body">
            <textarea name="conteudo" id="conteudo"><?= htmlspecialchars($post['conteudo'] ?? '') ?></textarea>
          </div>
        </div>

        <!-- URL de mídia (podcast/video) -->
        <div class="card shadow-sm mb-4 d-none" id="blocoMidia">
          <div class="card-header"><h5 class="mb-0"><i class="bi bi-link-45deg me-2"></i>URL da Mídia</h5></div>
          <div class="card-body">
            <input type="url" name="midia_url" class="form-control"
                   value="<?= htmlspecialchars($post['midia_url'] ?? '') ?>"
                   placeholder="https://open.spotify.com/... ou https://youtube.com/...">
            <div class="form-text" id="midiaHelp"></div>
          </div>
        </div>

        <!-- SEO -->
        <div class="card shadow-sm mb-4">
          <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-search me-2"></i>SEO</h5>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label fw-semibold">Meta título</label>
              <input type="text" name="meta_titulo" class="form-control" maxlength="70"
                     value="<?= htmlspecialchars($post['meta_titulo'] ?? '') ?>"
                     placeholder="Deixe em branco para usar o título do post">
            </div>
            <div class="mb-0">
              <label class="form-label fw-semibold">Meta descrição</label>
              <textarea name="meta_descricao" class="form-control" rows="2" maxlength="160"
                        placeholder="Máx. 160 caracteres"><?= htmlspecialchars($post['meta_descricao'] ?? '') ?></textarea>
            </div>
          </div>
        </div>

      </div><!-- /col-lg-8 -->

      <!-- Coluna lateral -->
      <div class="col-lg-4">

        <!-- Publicação -->
        <div class="card shadow-sm mb-4">
          <div class="card-header"><h5 class="mb-0"><i class="bi bi-send me-2"></i>Publicação</h5></div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label fw-semibold">Status</label>
              <select name="status" class="form-select">
                <option value="rascunho"             <?= ($post['status'] ?? '') === 'rascunho'             ? 'selected' : '' ?>>Rascunho</option>
                <option value="publicado"            <?= ($post['status'] ?? '') === 'publicado'            ? 'selected' : '' ?>>Publicado</option>
                <option value="aguardando_moderacao" <?= ($post['status'] ?? '') === 'aguardando_moderacao' ? 'selected' : '' ?>>Aguard. Moderação</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Autor</label>
              <select name="autor_id" class="form-select">
                <?php foreach ($admins as $a): ?>
                  <option value="<?= $a['id'] ?>"
                    <?= ($post['autor_id'] ?? $userId) == $a['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($a['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Imagem de capa</label>
              <input type="url" name="capa_url" class="form-control"
                     value="<?= htmlspecialchars($post['capa_url'] ?? '') ?>"
                     placeholder="https://...">
              <?php if (!empty($post['capa_url'])): ?>
                <img src="<?= htmlspecialchars($post['capa_url']) ?>" class="img-fluid rounded mt-2" style="max-height:120px">
              <?php endif; ?>
            </div>

            <div class="d-grid gap-2">
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg me-1"></i>
                <?= $isEdit ? 'Salvar alterações' : 'Criar post' ?>
              </button>
              <a href="/blog/arquivos.php" class="btn btn-outline-secondary">Cancelar</a>
            </div>
          </div>
        </div>

        <!-- Categorias -->
        <div class="card shadow-sm mb-4">
          <div class="card-header"><h5 class="mb-0"><i class="bi bi-folder2 me-2"></i>Categorias</h5></div>
          <div class="card-body" style="max-height:220px; overflow-y:auto">
            <?php if (empty($categorias)): ?>
              <p class="text-muted small mb-0">
                Nenhuma categoria. <a href="/blog/blog_categorias.php">Criar categoria</a>
              </p>
            <?php endif; ?>
            <?php foreach ($categorias as $cat): ?>
              <div class="form-check">
                <input class="form-check-input" type="checkbox"
                       name="categorias[]" value="<?= $cat['id'] ?>"
                       id="cat<?= $cat['id'] ?>"
                       <?= in_array($cat['id'], $postCategorias) ? 'checked' : '' ?>>
                <label class="form-check-label" for="cat<?= $cat['id'] ?>">
                  <?= $cat['parent_id'] ? '&nbsp;&nbsp;↳ ' : '' ?>
                  <?= htmlspecialchars($cat['nome']) ?>
                </label>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Tags -->
        <div class="card shadow-sm mb-4">
          <div class="card-header"><h5 class="mb-0"><i class="bi bi-tags me-2"></i>Tags</h5></div>
          <div class="card-body">
            <input type="text" name="tags" class="form-control"
                   value="<?= htmlspecialchars($postTags) ?>"
                   placeholder="empreendedorismo, inovação, startup">
            <div class="form-text">Separe as tags por vírgula.</div>
          </div>
        </div>

      </div><!-- /col-lg-4 -->
    </div><!-- /row -->
  </form>
</div>

<script>
// Slug preview
document.getElementById('titulo').addEventListener('input', function () {
  const slug = this.value.toLowerCase()
    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-z0-9\s-]/g, '').trim()
    .replace(/\s+/g, '-');
  document.getElementById('slugPreview').textContent = slug;
});

// Alterna blocos por tipo
const tipoRadios = document.querySelectorAll('input[name="tipo"]');
const blocoConteudo = document.getElementById('blocoConteudo');
const blocoMidia    = document.getElementById('blocoMidia');
const midiaHelp     = document.getElementById('midiaHelp');
const helps = {
  podcast: 'Cole o link do Spotify, Anchor, Soundcloud, etc.',
  video:   'Cole o link do YouTube, Vimeo, etc.',
};

function atualizaBlocos() {
  const tipo = document.querySelector('input[name="tipo"]:checked')?.value;
  const isMidia = tipo === 'podcast' || tipo === 'video';
  blocoConteudo.classList.toggle('d-none', isMidia);
  blocoMidia.classList.toggle('d-none', !isMidia);
  if (helps[tipo]) midiaHelp.textContent = helps[tipo];
  // Destaca card selecionado
  document.querySelectorAll('.tipo-card').forEach(c => c.classList.remove('border-primary', 'bg-light'));
  document.querySelector(`label[for="tipo_${tipo}"]`)?.closest('.tipo-card')?.classList.add('border-primary', 'bg-light');
}

tipoRadios.forEach(r => r.addEventListener('change', atualizaBlocos));
atualizaBlocos();
</script>

<?php include __DIR__ . '/../../app/views/admin/footer.php'; ?>
