
<?php
define('APP_NAME', 'Staff Directory');
define('DB_HOST', 'localhost');
define('DB_NAME', 'staff_directory');
define('DB_USER', 'root');
define('DB_PASS', '');

define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'Admin@123'); // CHANGE THIS
define('SESSION_NAME', 'staff_admin_session');

if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
function csrf_field(){ $t = htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); echo '<input type="hidden" name="csrf_token" value="'.$t.'">'; }
function check_csrf(){ if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) { http_response_code(400); die('Invalid CSRF token.'); } }

function pdo(){
  static $pdo;
  if (!$pdo) {
    $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
  }
  return $pdo;
}

function get_departments(){
  $pdo = pdo();
  return $pdo->query("SELECT id, name FROM departments ORDER BY name")->fetchAll();
}
function get_or_create_department($name){
  $name = trim($name);
  if ($name==='') return null;
  $pdo = pdo();
  $s = $pdo->prepare("SELECT id FROM departments WHERE name=?");
  $s->execute([$name]);
  $id = $s->fetchColumn();
  if ($id) return (int)$id;
  $i = $pdo->prepare("INSERT INTO departments(name) VALUES (?)");
  $i->execute([$name]);
  return (int)$pdo->lastInsertId();
}

function get_categories(){
  $pdo = pdo();
  return $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();
}
function get_or_create_category($name){
  $name = trim($name);
  if ($name==='') return null;
  $pdo = pdo();
  $s = $pdo->prepare("SELECT id FROM categories WHERE name=?");
  $s->execute([$name]);
  $id = $s->fetchColumn();
  if ($id) return (int)$id;
  $i = $pdo->prepare("INSERT INTO categories(name) VALUES (?)");
  $i->execute([$name]);
  return (int)$pdo->lastInsertId();
}
?>
