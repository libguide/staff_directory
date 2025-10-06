
<?php require_once __DIR__.'/../config.php'; if (session_status()===PHP_SESSION_NONE){session_start();}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  check_csrf();
  $u = trim($_POST['username'] ?? '');
  $p = trim($_POST['password'] ?? '');
  if ($u === ADMIN_USERNAME && $p === ADMIN_PASSWORD) {
    $_SESSION[SESSION_NAME] = ['username' => $u, 'time' => time()];
    header('Location: dashboard.php'); exit;
  } else { $error = 'Invalid username or password.'; }
}
?>
<!doctype html><html lang="en"><head>
<meta charset="utf-8"/><meta name="viewport" content="width=device-width, initial-scale=1"/>
<title>Login - <?= htmlspecialchars(APP_NAME) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="bg-light d-flex align-items-center" style="min-height:100vh">
<div class="container"><div class="row justify-content-center"><div class="col-md-4">
<div class="card shadow"><div class="card-body">
<h4 class="mb-3">Admin Login</h4>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<form method="post"><?php csrf_field(); ?>
  <div class="mb-3"><label class="form-label">Username</label><input class="form-control" name="username" required></div>
  <div class="mb-3"><label class="form-label">Password</label><input type="password" class="form-control" name="password" required></div>
  <button class="btn btn-primary w-100">Login</button>
</form>
</div></div>
<p class="text-center text-muted small mt-3">Default: admin / Admin@123 (change in config.php)</p>
</div></div></div></body></html>
