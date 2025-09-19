<?php
// usa a MESMA conexão/login da galeria
require $_SERVER['DOCUMENT_ROOT'].'/galeria/config.php';

// URLs/paths ABSOLUTOS do BLOG (não da galeria)
define('BLOG_URL',        rtrim(BASE_URL,'/').'/blog/');          // https://wra.ind.br/blog/
define('BLOG_UPLOAD_URL', BLOG_URL.'uploads/');                   // https://wra.ind.br/blog/uploads/
define('BLOG_DIR',        $_SERVER['DOCUMENT_ROOT'].'/blog/');    // /home/.../public_html/blog/
define('BLOG_UPLOAD_DIR', BLOG_DIR.'uploads/');                   // /home/.../public_html/blog/uploads/

function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
