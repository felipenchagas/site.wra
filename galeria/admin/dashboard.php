<?php require __DIR__.'/../config.php';
if (empty($_SESSION['auth'])) { header('Location: '.BASE_URL.'/admin/login.php'); exit; }

function flash($key,$val=null){
  if($val!==null){ $_SESSION['flash'][$key]=$val; return; }
  if(isset($_SESSION['flash'][$key])){ $v=$_SESSION['flash'][$key]; unset($_SESSION['flash'][$key]); return $v; }
  return '';
}

/* UPLOAD (múltiplo) c/ legendas individuais */
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['acao']??'')==='upload_multi'){
  $ALLOWED = ['image/jpeg'=>'.jpg','image/png'=>'.png','image/webp'=>'.webp'];

  if (empty($_FILES['imgs']) || !is_array($_FILES['imgs']['name'])){
    flash('err','Nenhum arquivo selecionado.'); header('Location: '.BASE_URL.'/admin/dashboard.php'); exit;
  }

  $titleAll = trim($_POST['title'] ?? '');     // legenda “global” (fallback)
  $titles   = $_POST['titles'] ?? [];          // legendas por arquivo (titles[0], titles[1]...)
  $ok = 0; $fail = [];

  $names = $_FILES['imgs']['name'];
  $types = $_FILES['imgs']['type'];
  $tmp   = $_FILES['imgs']['tmp_name'];
  $errs  = $_FILES['imgs']['error'];
  $sizes = $_FILES['imgs']['size'];

  for($i=0; $i<count($names); $i++){
    if ($errs[$i] !== UPLOAD_ERR_OK){ $fail[] = $names[$i].' (erro '.$errs[$i].')'; continue; }
    if ($sizes[$i] > MAX_MB*1024*1024){ $fail[] = $names[$i].' (maior que '.MAX_MB.'MB)'; continue; }
/* checagem extra: garante que o arquivo é uma imagem válida */
$info = @getimagesize($tmp[$i]);
if ($info === false) {
    $fail[] = $names[$i].' (não é uma imagem válida)'; 
    continue;
}
    $mime = $types[$i] ?: (@mime_content_type($tmp[$i]) ?: '');
    if (!isset($ALLOWED[$mime])) {
      $extGuess = strtolower(pathinfo($names[$i], PATHINFO_EXTENSION));
      $extMap = ['jpg'=>'.jpg','jpeg'=>'.jpg','png'=>'.png','webp'=>'.webp'];
      if (!isset($extMap[$extGuess])) { $fail[] = $names[$i].' (tipo não permitido)'; continue; }
      $ext = $extMap[$extGuess];
    } else {
      $ext = $ALLOWED[$mime];
    }

    $new = bin2hex(random_bytes(8)).$ext;
    if (!is_dir(UPLOAD_DIR)) @mkdir(UPLOAD_DIR,0775,true);
    if (!is_writable(UPLOAD_DIR)) { $fail[] = $names[$i].' (sem permissão em /galeria/fotos)'; continue; }

    if (move_uploaded_file($tmp[$i], UPLOAD_DIR.$new)){
      $title = trim($titles[$i] ?? $titleAll);   // legenda individual OU global
      $stmt = $mysqli->prepare("INSERT INTO images(filename,title) VALUES(?,?)");
      if($stmt){ $stmt->bind_param('ss',$new,$title); $stmt->execute(); $ok++; }
      else { $fail[] = $names[$i].' (DB: '.$mysqli->error.')'; }
    } else {
      $fail[] = $names[$i].' (falha ao salvar)';
    }
  }

  if ($ok && empty($fail))      flash('ok', "✅ $ok imagem(ns) enviada(s)!");
  elseif ($ok && $fail)         flash('ok', "⚠️ $ok enviada(s); falharam: ".implode(', ', $fail));
  else                          flash('err','❌ Falha em todas: '.implode(', ', $fail));

  header('Location: '.BASE_URL.'/admin/dashboard.php'); exit;
}


/* EXCLUIR */
if (isset($_GET['del'])){
  $id = intval($_GET['del']);
  $q = $mysqli->prepare("SELECT filename FROM images WHERE id=?");
  if($q){
    $q->bind_param('i',$id); $q->execute(); $q->bind_result($fn);
    if ($q->fetch()){ @unlink(UPLOAD_DIR.$fn); }
  }
  $mysqli->query("DELETE FROM images WHERE id=".$id);
  flash('ok','Imagem excluída.');
  header('Location: '.BASE_URL.'/admin/dashboard.php'); exit;
}

/* EDITAR */
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['acao']??'')==='edit'){
  $id = intval($_POST['id'] ?? 0);
  $title = trim($_POST['title'] ?? '');
  $s = $mysqli->prepare("UPDATE images SET title=? WHERE id=?");
  if($s){ $s->bind_param('si',$title,$id); $s->execute(); flash('ok','Descrição atualizada.'); }
  else { flash('err','Erro DB (prepare): '.$mysqli->error); }
  header('Location: '.BASE_URL.'/admin/dashboard.php'); exit;
}

/* LISTA */
$res = $mysqli->query("SELECT id, filename, title, created_at FROM images ORDER BY created_at DESC");
$listaErro = $res ? '' : $mysqli->error;
?>
<!doctype html><html lang="pt-br"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Painel - Galeria</title>
<style>
:root{--b:#111;--g:#f5f5f5;--bd:#e5e7eb}
*{box-sizing:border-box}
body{font-family:system-ui,Segoe UI,Roboto,Arial;background:#fafafa;margin:0}
.container{max-width:1100px;margin:24px auto;padding:0 16px}
.top{display:flex;gap:10px;align-items:center;justify-content:space-between;margin-bottom:16px}
.btn{padding:9px 14px;border-radius:10px;border:1px solid var(--b);text-decoration:none;color:var(--b);background:#fff}
.box{background:#fff;border:1px solid var(--bd);border-radius:14px;padding:16px;margin-bottom:16px}
h2,h3{margin:0 0 10px}
.row{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
input[type=file],input[type=text]{padding:10px;border:1px solid #d1d5db;border-radius:10px;background:#fff}
input[type=text]{min-width:260px;flex:1}
button{padding:10px 14px;border:0;border-radius:10px;background:#111;color:#fff;cursor:pointer}
.note{padding:10px 12px;border-radius:10px;margin-bottom:12px;background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}
.err{padding:10px 12px;border-radius:10px;margin-bottom:12px;background:#fef2f2;color:#991b1b;border:1px solid #fecaca}

.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(360px,1fr));gap:12px}

.card{
  display:grid;
  grid-template-columns:120px 1fr;
  gap:12px;
  align-items:start;         /* <— evita “descer” o input */
  border:1px solid var(--bd);
  border-radius:12px;
  padding:12px;
  background:#fff
}
.card img{width:120px;height:80px;object-fit:cover;border-radius:8px}
.card .right{display:flex;flex-direction:column;gap:8px}
.card form.inline{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
.card form.inline input[type=text]{flex:1;min-width:200px}
.card .actions{display:flex;gap:8px;justify-content:flex-end;flex-wrap:wrap}
.small{color:#6b7280;font-size:12px}
.captions{margin-top:10px;display:grid;grid-template-columns:1fr;gap:8px}
.cap-row{
  display:grid;
  grid-template-columns:minmax(140px,35%) 1fr;
  gap:10px; align-items:center;
  background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:10px
}
.cap-name{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:#374151;font-size:13px}
.cap-row input[type=text]{padding:10px;border:1px solid #d1d5db;border-radius:10px;width:100%}
.cap-name{display:flex;align-items:center;gap:8px;min-width:0}
.cap-name img{flex:0 0 auto}
.cap-name{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}

</style>

</head><body>
<div class="container">
  <div class="top">
    <h2>Painel da Galeria</h2>
    <div>
      <a class="btn" href="<?= BASE_URL ?>/" target="_blank">Ver galeria</a>
      <a class="btn" href="<?= BASE_URL ?>/admin/logout.php">Sair</a>
    </div>
  </div>

  <?php if($m=flash('ok')): ?><div class="note"><?= htmlspecialchars($m) ?></div><?php endif; ?>
  <?php if($m=flash('err')): ?><div class="err"><?= htmlspecialchars($m) ?></div><?php endif; ?>

<div class="box">
  <h3>Adicionar imagem</h3>
  <form method="post" enctype="multipart/form-data" class="row" id="upForm" style="flex-direction:column; gap:10px">
    <input type="hidden" name="acao" value="upload_multi">

    <div class="row">
      <input type="file" name="imgs[]" id="imgs" accept="image/*" multiple required onchange="renderCaptions(this)">
      <input type="text" name="title" id="titleGlobal" placeholder="Descrição (opcional, aplica a todas)">
    </div>

    <!-- ⚠️ AGORA DENTRO DO FORM -->
    <div id="captions" class="captions"></div>

    <div class="row">
      <button>Enviar</button>
      <div id="legend-hint" class="small" style="margin-left:8px">
        Você pode selecionar várias imagens segurando Ctrl/Shift. Permitidos: JPG, PNG, WEBP. Máx <?= MAX_MB ?>MB por arquivo.
      </div>
    </div>
  </form>
</div>

  <?php if($listaErro): ?>
    <div class="err">Erro ao listar imagens: <?= htmlspecialchars($listaErro) ?></div>
  <?php else: ?>
    <div class="grid">
      <?php while($row=$res->fetch_assoc()): ?>
<div class="card">
  <img src="<?= UPLOAD_URL . rawurlencode($row['filename']) ?>" alt="">
  <div class="right">
    <form method="post" class="inline">
      <input type="hidden" name="acao" value="edit">
      <input type="hidden" name="id" value="<?= $row['id'] ?>">
      <input type="text" name="title" value="<?= htmlspecialchars($row['title']) ?>" placeholder="Descrição">
      <button>Salvar</button>
    </form>
    <div class="actions">
      <a class="btn" href="<?= UPLOAD_URL . rawurlencode($row['filename']) ?>" target="_blank">Abrir</a>
      <a class="btn" href="?del=<?= $row['id'] ?>" onclick="return confirm('Excluir a imagem?')">Excluir</a>
    </div>
  </div>
</div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
  
</div>
<script>
document.getElementById('upForm').addEventListener('submit', (e)=>{
  const btn = e.target.querySelector('button[type="submit"]') || e.target.querySelector('button');
  if (btn){ btn.disabled = true; btn.textContent = 'Enviando...'; }
});
</script>

<script>
let previewURLs = [];

function renderCaptions(input){
  const box  = document.getElementById('captions');
  const hint = document.getElementById('legend-hint');

  // revoke URLs antigos
  previewURLs.forEach(u=>URL.revokeObjectURL(u));
  previewURLs = [];

  box.innerHTML = '';
  const files = input.files || [];
  if(!files.length){ if(hint) hint.style.display='block'; return; }
  if(hint) hint.style.display='none';

  for(let i=0;i<files.length;i++){
    const row = document.createElement('div');
    row.className = 'cap-row';
    const url = URL.createObjectURL(files[i]);
    previewURLs.push(url);
    row.innerHTML = `
      <span class="cap-name" title="${files[i].name}">
        <img src="${url}" style="width:44px;height:32px;object-fit:cover;border-radius:6px;margin-right:8px;vertical-align:middle">
        ${files[i].name}
      </span>
      <input type="text" name="titles[]" placeholder="Legenda para este arquivo">
    `;
    box.appendChild(row);
  }

  // foca no primeiro campo de legenda
  const first = box.querySelector('input[name="titles[]"]');
  if(first) first.focus();
}

// opcional: revogar quando enviar
document.getElementById('upForm').addEventListener('submit', ()=>{
  previewURLs.forEach(u=>URL.revokeObjectURL(u));
  previewURLs = [];
});


/* preenche legendas vazias com a global ao enviar */
document.getElementById('upForm').addEventListener('submit', function(e){
  const global = (document.getElementById('titleGlobal')?.value || '').trim();
  if(!global) return; // nada a copiar
  document.querySelectorAll('#captions input[name="titles[]"]').forEach(inp=>{
    if(!inp.value.trim()) inp.value = global;
  });
});

/* botão para copiar manualmente */
(function(){
  const btn = document.createElement('button');
  btn.type = 'button';
  btn.textContent = 'Copiar descrição global p/ todas';
  btn.style.marginLeft = 'auto';
  btn.onclick = function(){
    const global = (document.getElementById('titleGlobal')?.value || '').trim();
    document.querySelectorAll('#captions input[name="titles[]"]').forEach(inp=>{
      inp.value = global;
    });
  };
  const rows = document.querySelectorAll('.box form .row');
  if(rows.length>=2) rows[1].appendChild(btn);
})();
</script>


</body>
</html>
