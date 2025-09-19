<?php
require __DIR__.'/_bootstrap.php';

$slug = preg_replace('~[^a-z0-9-]+~','', strtolower($_GET['slug'] ?? '') );

$stmt = $mysqli->prepare("SELECT * FROM posts WHERE slug=? AND status='published' LIMIT 1");
$stmt->bind_param('s',$slug); $stmt->execute();
$res = $stmt->get_result();
$post = $res->fetch_assoc();
if(!$post){ http_response_code(404); echo "Post não encontrado"; exit; }

function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
$canonical = rtrim(BASE_URL,'/').'/blog/'.$post['slug'];
$metaTitle = $post['meta_title'] ?: $post['title'];
$metaDesc  = $post['meta_description'] ?: '';
$coverAbs  = $post['cover_image'] ? (rtrim(BASE_URL,'/').'/blog/uploads/'.$post['cover_image']) : '';
?>
<!doctype html><html lang="pt-br"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($metaTitle) ?></title>
<meta name="description" content="<?= e($metaDesc) ?>">
<?php if($post['meta_keywords']): ?><meta name="keywords" content="<?= e($post['meta_keywords']) ?>"><?php endif; ?>
<link rel="canonical" href="<?= e($canonical) ?>">

<!-- Open Graph -->
<meta property="og:type" content="article">
<meta property="og:title" content="<?= e($metaTitle) ?>">
<meta property="og:description" content="<?= e($metaDesc) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<?php if($coverAbs): ?><meta property="og:image" content="<?= e($coverAbs) ?>"><?php endif; ?>
<meta property="og:site_name" content="WRA">

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($metaTitle) ?>">
<meta name="twitter:description" content="<?= e($metaDesc) ?>">
<?php if($coverAbs): ?><meta name="twitter:image" content="<?= e($coverAbs) ?>"><?php endif; ?>

<?php
// Linhas brutas extras (se quiser meta/manifest etc.)
if ($post['head_extra']) { echo $post['head_extra'], "\n"; }

// Vários blocos JSON-LD: separe no admin com uma linha contendo apenas ---
if ($post['head_jsonld']) {
  $blocks = preg_split("~\R-{3,}\R~", trim($post['head_jsonld']));
  foreach ($blocks as $json) {
    $json = trim($json);
    if ($json==='') continue;
    // imprime "as is" (quem edita é você). Opcional: validar com json_decode.
    echo "<script type=\"application/ld+json\">\n".$json."\n</script>\n";
  }
}
?>
<style>
body{font-family:system-ui,Segoe UI,Roboto,Arial;margin:0;background:#fff;color:#111}
.container{max-width:900px;margin:20px auto;padding:0 16px}
.cover{width:100%;max-height:420px;object-fit:cover;border-radius:12px}
.post h1{margin:12px 0 8px}
.post .meta{color:#666;font-size:14px;margin-bottom:18px}
.post .content img{max-width:100%;height:auto;border-radius:8px}
</style>
</head>
<body>
<div class="container post">
  <h1><?= e($post['title']) ?></h1>
  <div class="meta"><?= e(date('d/m/Y', strtotime($post['published_at'] ?: $post['created_at']))) ?></div>
  <?php if($coverAbs): ?><img class="cover" src="<?= e($coverAbs) ?>" alt=""><?php endif; ?>
  <div class="content"><?= $post['content'] /* conteúdo já vem em HTML */ ?></div>
</div>
</body></html>
