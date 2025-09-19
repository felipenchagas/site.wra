<?php
require __DIR__.'/_bootstrap.php';

function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$res = $mysqli->query("SELECT title,slug,meta_description,cover_image
                       FROM posts
                       WHERE status='published'
                       ORDER BY COALESCE(published_at, created_at) DESC");
?>
<!doctype html><html lang="pt-br"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Blog – WRA</title>
<style>
body{font-family:system-ui,Segoe UI,Roboto,Arial;margin:0;background:#fafafa}
.container{max-width:1100px;margin:24px auto;padding:0 16px}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px}
.card{background:#fff;border:1px solid #eee;border-radius:12px;overflow:hidden}
.card img{width:100%;height:160px;object-fit:cover;display:block}
.card a{display:block;padding:12px 14px;text-decoration:none;color:#111}
.card p{color:#555;font-size:14px;margin:6px 0 0}
</style>
</head><body>
<div class="container">
  <h1>Blog</h1>
  <div class="grid">
    <?php if($res && $res->num_rows): while($p=$res->fetch_assoc()): ?>
      <div class="card">
        <?php if($p['cover_image']): ?>
          <img src="<?= BLOG_UPLOAD_URL.e($p['cover_image']) ?>" alt="<?= e($p['title']) ?>" loading="lazy">
        <?php endif; ?>
        <a href="<?= BLOG_URL ?>view.php?slug=<?= e($p['slug']) ?>">
          <strong><?= e($p['title']) ?></strong>
          <p><?= e($p['meta_description'] ?: '') ?></p>
        </a>
      </div>
    <?php endwhile; else: ?>
      <p>Nenhum post publicado ainda.</p>
    <?php endif; ?>
  </div>
</div>
</body></html>
