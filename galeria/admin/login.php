<?php require __DIR__.'/../config.php';
if ($_SERVER['REQUEST_METHOD']==='POST'){
  $u = $_POST['u'] ?? '';
  $p = $_POST['p'] ?? '';
  if ($u===ADMIN_USER && password_verify($p, ADMIN_PASS_HASH)){
    $_SESSION['auth']=true;
    header('Location: '.BASE_URL.'/admin/dashboard.php'); exit;
  }
  $err = 'Usuário ou senha inválidos';
}
?>
<!doctype html><html lang="pt-br"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login - Galeria</title>
<style>
body{display:flex;align-items:center;justify-content:center;height:100vh;font-family:system-ui}
.box{width:340px;border:1px solid #e5e7eb;border-radius:12px;padding:20px}
label{display:block;margin:8px 0 4px}
input{width:100%;padding:10px;border:1px solid #d1d5db;border-radius:8px}
button{margin-top:12px;width:100%;padding:10px;border:0;border-radius:8px;background:#111;color:#fff}
.msg{color:#b91c1c;margin-bottom:8px}
</style></head><body>
<div class="box">
  <h2 style="margin-top:0">Painel da Galeria</h2>
  <?php if(!empty($err)):?><div class="msg"><?= htmlspecialchars($err) ?></div><?php endif;?>
  <form method="post" autocomplete="off">
    <label>Usuário</label><input name="u" required>
    <label>Senha</label><input name="p" type="password" required>
    <button>Entrar</button>
  </form>
</div>
</body></html>
