
<?php require_once __DIR__.'/auth.php'; require_once __DIR__.'/_layout_top.php'; $pdo = pdo();
$errors=[]; $ok=''; $departments = get_departments(); $categories = get_categories();
if ($_SERVER['REQUEST_METHOD']==='POST') {
  check_csrf();
  $name = trim($_POST['name'] ?? '');
  $designation = trim($_POST['designation'] ?? '');
  $dept_id = (int)($_POST['dept_id'] ?? 0);
  $category_id = (int)($_POST['category_id'] ?? 0);
  $email = trim($_POST['email'] ?? '');
  $intercom = trim($_POST['intercom'] ?? '');
  $direct_number = trim($_POST['direct_number'] ?? '');
  $address = trim($_POST['address'] ?? '');
  $blood = trim($_POST['blood_group'] ?? '');

  if ($name==='') $errors[]='Name required';
  if ($designation==='') $errors[]='Designation required';
  if ($dept_id<=0) $errors[]='Department required';
  if ($category_id<=0) $errors[]='Category required';
  if ($intercom==='') $errors[]='Intercom required';

  $email = $email !== '' ? $email : null;
  $blood = $blood !== '' ? $blood : null;

  if (!$errors) {
    try {
      $st=$pdo->prepare("INSERT INTO staff(name,designation,dept_id,category_id,email,intercom,direct_number,address,blood_group) VALUES (?,?,?,?,?,?,?,?,?)");
      $st->execute([$name,$designation,$dept_id,$category_id,$email,$intercom,$direct_number,$address,$blood]);
      $ok='Staff added successfully';
    } catch (PDOException $e) { $errors[]='DB Error: '.$e->getMessage(); }
  }
}
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="mb-0">Add Staff</h3>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary" href="dashboard.php">Back</a>
    <a class="btn btn-outline-primary" href="departments.php">Manage Departments</a>
    <a class="btn btn-outline-primary" href="categories.php">Manage Categories</a>
  </div>
</div>
<?php if ($ok): ?><div class="alert alert-success"><?= htmlspecialchars($ok) ?></div><?php endif; ?>
<?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?></ul></div><?php endif; ?>
<form method="post"><?php csrf_field(); ?>
  <div class="row g-3">
    <div class="col-md-6"><label class="form-label">Name</label><input class="form-control" name="name" required></div>
    <div class="col-md-6"><label class="form-label">Designation</label><input class="form-control" name="designation" required></div>
    <div class="col-md-6">
      <label class="form-label">Department</label>
      <select class="form-select" name="dept_id" required>
        <option value="">-- Select Department --</option>
        <?php foreach($departments as $d): ?><option value="<?= (int)$d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label">Category</label>
      <select class="form-select" name="category_id" required>
        <?php foreach($categories as $c): ?><option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6"><label class="form-label">Email (optional)</label><input type="email" class="form-control" name="email"></div>
    <div class="col-md-6"><label class="form-label">Intercom</label><input class="form-control" name="intercom" required></div>
    <div class="col-md-6"><label class="form-label">Direct Number</label><input class="form-control" name="direct_number"></div>
    <div class="col-md-6"><label class="form-label">Blood Group (optional)</label><input class="form-control" name="blood_group" placeholder="e.g., A+, O-"></div>
    <div class="col-12"><label class="form-label">Address</label><textarea class="form-control" name="address" rows="2"></textarea></div>
  </div>
  <div class="mt-3"><button class="btn btn-primary">Save</button></div>
</form>
<?php require_once __DIR__.'/_layout_bottom.php'; ?>
