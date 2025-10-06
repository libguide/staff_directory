
<?php require_once __DIR__.'/auth.php'; require_once __DIR__.'/_layout_top.php'; $pdo=pdo();
$errors=[]; $ok='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  check_csrf();
  $name = trim($_POST['add_name'] ?? '');
  if ($name==='') $errors[]='Department name required';
  else { try { $pdo->prepare("INSERT INTO departments(name) VALUES (?)")->execute([$name]); $ok='Department added'; } catch (PDOException $e) { $errors[]='DB Error: '.$e->getMessage(); } }
}
if (isset($_GET['del'])) {
  $id=(int)$_GET['del'];
  try { $pdo->prepare("DELETE FROM departments WHERE id=?")->execute([$id]); $ok='Department deleted'; }
  catch (PDOException $e) { $errors[]='Cannot delete: likely in use by staff.'; }
}
$rows = $pdo->query("SELECT id, name FROM departments ORDER BY name")->fetchAll();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="mb-0">Departments</h3>
  <a class="btn btn-outline-secondary" href="dashboard.php">Back</a>
</div>
<?php if ($ok): ?><div class="alert alert-success"><?= htmlspecialchars($ok) ?></div><?php endif; ?>
<?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?></ul></div><?php endif; ?>

<div class="card mb-3"><div class="card-body">
  <form method="post" class="row g-2 align-items-end"><?php csrf_field(); ?>
    <div class="col-md-6"><label class="form-label">New Department</label><input class="form-control" name="add_name" placeholder="e.g., Computer Science"></div>
    <div class="col-md-2"><button class="btn btn-primary w-100">Add</button></div>
  </form>
</div></div>

<div class="table-responsive shadow-sm">
  <table class="table table-striped table-hover bg-white align-middle">
    <thead class="table-light"><tr><th>#</th><th>Name</th><th>Actions</th></tr></thead>
    <tbody>
      <?php if (!$rows): ?><tr><td colspan="3" class="text-center text-muted py-4">No departments.</td></tr><?php endif; ?>
      <?php foreach($rows as $r): ?>
      <tr>
        <td><?= (int)$r['id'] ?></td>
        <td><?= htmlspecialchars($r['name']) ?></td>
        <td><a class="btn btn-sm btn-outline-danger" href="departments.php?del=<?= (int)$r['id'] ?>" onclick="return confirm('Delete this department? It must not be in use.')">Delete</a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require_once __DIR__.'/_layout_bottom.php'; ?>
