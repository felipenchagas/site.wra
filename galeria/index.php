<?php
require __DIR__.'/config.php';

/* ===== DEBUG (remova em produção) ===== */
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

/* ===== BASE_URL / UPLOAD_URL de fallback ===== */
if (!defined('BASE_URL')) {
  $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
  $host  = $_SERVER['HTTP_HOST'] ?? 'localhost';
  define('BASE_URL', $proto . '://' . $host);
}
if (!defined('UPLOAD_URL')) {
  define('UPLOAD_URL', rtrim(BASE_URL, '/') . '/galeria/fotos/');
}

/* ===== Conexão $mysqli garantida ===== */
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
  die('Falha na conexão com o banco. Verifique config.php ($mysqli).');
}
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Galeria - WRA</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="../css/estilo.css"> <!-- se existir -->
<style>
:root{--bd:#e5e7eb;--ink:#111;--muted:#6b7280}
*{box-sizing:border-box}
body{font-family:system-ui,Segoe UI,Roboto,Arial;margin:0;background:#fafafa;color:var(--ink)}
.container{max-width:1200px;margin:24px auto;padding:0 16px}
h1{margin:12px 0 20px;font-size:26px}

/* Menu simples (usa sua estrutura de UL já existente) */
#menu{background:#fff;border-bottom:1px solid var(--bd)}
#menu ul{list-style:none;margin:0;padding:0;display:flex;gap:16px;align-items:center}
#menu>ul{max-width:1200px;margin:0 auto;padding:12px 16px}
#menu a{text-decoration:none;color:var(--ink);padding:8px 10px;border-radius:10px}
#menu a:hover{background:#111;color:#fff}
#menu .dropdown{position:relative}
#menu .dropdown .sub-menu{display:none;position:absolute;left:0;top:100%;background:#fff;border:1px solid var(--bd);border-radius:10px;padding:8px;min-width:220px;z-index:10}
#menu .dropdown:hover .sub-menu{display:block}
#menu .sub-menu li a{display:block}

/* Grid de imagens */
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:18px}
.card{background:#fff;border:1px solid var(--bd);border-radius:12px;overflow:hidden;text-align:center;box-shadow:0 1px 4px rgba(0,0,0,.06);cursor:pointer;transition:transform .18s ease}
.card:hover{transform:scale(1.02)}
.card img{width:100%;height:180px;object-fit:cover;display:block}
.card p{margin:10px;font-size:14px;color:#444}

/* Lightbox */
#lightbox{position:fixed;inset:0;background:rgba(0,0,0,.9);display:none;align-items:center;justify-content:center;z-index:9999}
#lightbox img{max-width:90vw;max-height:90vh;border-radius:8px}
body.noscroll{overflow:hidden}

/* Footer bem simples (usa seu HTML) */
footer{margin-top:40px;background:#fff;border-top:1px solid var(--bd);padding:24px 0}
footer .wrapper{max-width:1200px;margin:0 auto;padding:0 16px}
footer .row{display:flex;flex-wrap:wrap;gap:16px}
footer .p-5{padding:10px}
footer .col-4{flex:1 1 33%}
footer .col-md-6{flex:1 1 300px}
.footer__menu ul{list-style:none;margin:0;padding:0}
.footer__menu a{text-decoration:none;color:var(--ink)}
.address a{display:block;text-decoration:none;color:var(--ink);margin:4px 0}
.address i{margin-right:6px;color:var(--muted)}
@media (max-width:700px){
  #menu>ul{flex-wrap:wrap}
  .card img{height:160px}
}
</style>
</head>
<body>

<!-- ===== MENU ===== -->
<nav id="menu">
  <ul>
    <li><a href="../" title="Página inicial">Home</a></li>
    <li><a href="../empresa" title="Empresa">Empresa</a></li>

    <li class="dropdown">
      <a href="../servicos" title="Serviços">Serviços</a>
      <ul class="sub-menu">
        <li><a href="../equipamentos">Equipamentos</a></li>
        <li><a href="../moegas-balancas">Moegas e Balanças</a></li>
        <li><a href="../misturador">Misturador</a></li>
        <li><a href="../misturador-pas-bateladas">Misturador Pás por Bateladas</a></li>
        <li><a href="../misturador-helicoidal">Misturador Helicoidal</a></li>
        <li><a href="../peneiras-vibratorias-rotativas">Peneiras Vibratórias e Rotativas</a></li>
        <li><a href="../moinhos-destorroado-grumos">Moinhos Destorroado de Grumos</a></li>
      </ul>
    </li>

    <li><a href="https://wra.ind.br/galeria/" title="Obras Realizadas">Obras Realizadas</a></li>
    <li><a href="../contato" title="Contato">Contato</a></li>

    <li class="dropdown">
      <a href="../informacoes" title="Informações">Informações</a>
      <ul class="sub-menu">
        <li><a href="../misturadores">Misturadores</a></li>
        <li><a href="../automacao-industrial">Automação Industrial</a></li>
        <li><a href="../projetos-unidades-fabril">Projetos Unidades Fabril</a></li>
        <li><a href="../dosadores-industrial">Dosadores Industrial</a></li>
        <li><a href="../elevadores-caneca">Elevadores de Caneca</a></li>
        <li><a href="../ensacadeiras-fertilizantes">Ensacadeiras Fertilizantes</a></li>
        <li><a href="../equipamento-processo-mistura-fertilizantes">Equipamento Processo Mistura</a></li>
        <li><a href="../estruturas-metalicas">Estruturas Metálicas</a></li>
        <li><a href="../moegas-descarga-alimentacao">Moegas Descarga e Alimentação</a></li>
        <li><a href="../granuladores-fertilizantes">Granuladores Fertilizantes</a></li>
        <li><a href="../silos-reservatorios">Silos e Reservatórios</a></li>
        <li><a href="../evazadora-big-bag">Evazadora Big Bag</a></li>
      </ul>
    </li>
  </ul>
</nav>

<div class="container">
  <h1>Galeria</h1>

  <div class="grid">
    <?php
      $res = $mysqli->query("SELECT filename, title FROM images ORDER BY created_at DESC");
      if (!$res || $res->num_rows === 0) {
        echo '<p style="grid-column:1/-1;margin:8px 0;">Nenhuma imagem encontrada.</p>';
      } else {
        while ($row = $res->fetch_assoc()) {
          $src = UPLOAD_URL . rawurlencode($row['filename']);
          $alt = htmlspecialchars($row['title'] ?: 'Imagem');
          $cap = $row['title'] ? htmlspecialchars($row['title']) : 'Sem descrição';
          echo '<div class="card" onclick="openLightbox(\'' . $src . '\')">
                  <img src="' . $src . '" alt="' . $alt . '">
                  <p>' . $cap . '</p>
                </div>';
        }
      }
    ?>
  </div>
</div>

<!-- ===== LIGHTBOX ===== -->
<div id="lightbox">
  <img src="" alt="">
</div>

<!-- ===== RODAPÉ ===== -->
<footer>
  <div class="wrapper">
    <div class="row">
      <div class="p-5 col-4 col-md-12">
        <img class="footer__logo" src="../imagens/logo.png" alt="WRA" title="WRA" width="200" loading="lazy">
      </div>

      <div class="p-5 col-4 col-md-6">
        <div class="footer__menu">
          <h3>NAVEGAÇÃO</h3>
          <nav>
            <ul>
              <li><a href="../">Home</a></li>
              <li><a href="../empresa">Empresa</a></li>
              <li><a href="../servicos">Serviços</a></li>
              <li><a href="https://wra.ind.br/galeria/">Obras Realizadas</a></li>
              <li><a href="../contato">Contato</a></li>
              <li><a href="../informacoes">Informações</a></li>
              <li><a href="../mapa-site">Mapa do site</a></li>
            </ul>
          </nav>
        </div>
      </div>

      <div class="p-5 col-4 col-md-6">
        <h3>CONTATO</h3>
        <address class="address">
          <span><i class="fas fa-map-marker-alt"></i> R. Zeila Moura dos Santos n°101, sala 821 - Cristo Rei Curitiba - CEP: 80050-605</span>
          <a title="Clique e ligue" href="tel:4135678187"><i class="fas fa-phone"></i> (41) 3567-8187</a>
          <a href="https://wa.me/5541991452534" target="_blank" title="Whatsapp WRA"><i class="fab fa-whatsapp"></i> (41) 99145-2534</a>
          <a title="Envie um e-mail" href="mailto:contato@wra.ind.br"><i class="fas fa-paper-plane"></i> contato@wra.ind.br</a>
          <a class="btn" href="../contato" title="Envie sua mensagem!">Envie sua mensagem!</a>
        </address>
      </div>
    </div>
  </div>
</footer>

<script>
const lb = document.getElementById('lightbox');
const lbImg = lb.querySelector('img');

function openLightbox(src){
  lbImg.src = src;
  lb.style.display = 'flex';
  document.body.classList.add('noscroll');
}
function closeLightbox(){
  lb.style.display = 'none';
  lbImg.src = '';
  document.body.classList.remove('noscroll');
}
// Fecha clicando fora da imagem
lb.addEventListener('click', (e)=>{ if (e.target === lb) closeLightbox(); });
// Fecha com ESC
document.addEventListener('keydown', (e)=>{ if (e.key === 'Escape') closeLightbox(); });
</script>
</body>
</html>
