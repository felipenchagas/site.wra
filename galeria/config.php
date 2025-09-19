<?php
// DEBUG TEMPORÁRIO (retire depois)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// DB
define('DB_HOST', 'localhost');
define('DB_NAME', 'wraind36_galeriawra');
define('DB_USER', 'wraind36_user');
define('DB_PASS', 'ZscEwAztaQYxP2W');
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_errno) { die("Erro DB: ".$mysqli->connect_error); }
$mysqli->set_charset('utf8mb4');

session_start();

define('BASE_URL', '/galeria');

// Login
define('ADMIN_USER', 'wra');
define('ADMIN_PASS_HASH', password_hash('WRA@2025', PASSWORD_DEFAULT));

// Upload
define('UPLOAD_DIR', __DIR__ . '/fotos/');
define('UPLOAD_URL', BASE_URL . '/fotos/');
define('MAX_MB', 8);
$ALLOWED = ['image/jpeg'=>'.jpg','image/png'=>'.png','image/webp'=>'.webp'];

// Garante a pasta de upload
if (!is_dir(UPLOAD_DIR)) { @mkdir(UPLOAD_DIR, 0775, true); }
