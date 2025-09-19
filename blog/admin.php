<?php
require __DIR__.'/_bootstrap.php';

// usa a MESMA sessão/autenticação da galeria:
if (empty($_SESSION['auth'])) {
  // ajuste o caminho do seu login, se for diferente
  header('Location: '.BASE_URL.'/admin/login.php');
  exit;
}

function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

// publicar/despublicar rapidamente
if (isset($_GET['publish'])) {
  $id = (int)$_GET['publish'];
  $mysqli->query("UPDATE posts SET status='published', published_at=NOW() WHERE id={$id} LIMIT 1");
  header('Location: '.BLOG_URL.'admin.php'); exit;
}
if (isset($_GET['draft'])) {
  $id = (int)$_GET['draft'];
  $mysqli->query("UPDATE posts SET status='draft' WHERE id={$id} LIMIT 1");
  header('Location: '.BLOG_URL.'admin.php'); exit;
}
if (isset($_GET['delete'])) {
  $id = (int)$_GET['delete'];
  $mysqli->query("DELETE FROM posts WHERE id={$id} LIMIT 1");
  header('Location: '.BLOG_URL.'admin.php'); exit;
}

$res = $mysqli->query("SELECT id,title,slug,status,cover_image,created_at,published_at
                       FROM posts ORDER BY created_at DESC");
?>
<!doctype html><html lang="pt-br"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Blog – Admin</title>
<style>
body{font-family:system-ui,Segoe UI,Roboto,Arial;margin:0;background:#fafafa}
.container{max-width:1100px;margin:24px auto;padding:0 16px}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px}
.card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden}
.card .thumb{width:100%;height:180px;object-fit:cover;display:block;background:#f4f4f5}
.card .body{padding:12px 14px}
.actions a{margin-right:8px;display:inline-block;padding:8px 12px;border:1px solid #111;border-radius:10px;text-decoration:none;color:#111}
</style>
</head><body>
<div class="container">
  <h1>Posts</h1>

  <p><a class="actions" href="<?= BLOG_URL ?>edit.php">+ Novo post</a></p>

  <div class="grid">
    <?php while($p=$res->fetch_assoc()): ?>
      <div class="card">
<?php if($p['cover_image']): ?>
  <img class="thumb"
       src="<?= BLOG_UPLOAD_URL . rawurlencode($p['cover_image']) ?>"
       alt="">
<?php else: ?>
  <div class="thumb"></div>
<?php endif; ?>

        <div class="body">
          <strong><?= e($p['title']) ?></strong><br>
          <small>Slug: <?= e($p['slug']) ?> · Status: <?= e($p['status']) ?> · <?= e(date('d/m/Y H:i', strtotime($p['created_at']))) ?></small>
          <div class="actions" style="margin-top:10px">
            <a href="<?= BLOG_URL ?>edit.php?id=<?= (int)$p['id'] ?>">Editar</a>
            <a href="<?= BLOG_URL ?>view.php?slug=<?= e($p['slug']) ?>&preview=1" target="_blank">Ver</a>
            <?php if($p['status']==='published'): ?>
              <a href="<?= BLOG_URL ?>admin.php?draft=<?= (int)$p['id'] ?>">Despublicar</a>
            <?php else: ?>
              <a href="<?= BLOG_URL ?>admin.php?publish=<?= (int)$p['id'] ?>">Publicar</a>
            <?php endif; ?>
            <a href="<?= BLOG_URL ?>admin.php?delete=<?= (int)$p['id'] ?>" onclick="return confirm('Excluir?')">Excluir</a>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</div>
</body></html>
