
<?php require_once __DIR__.'/auth.php'; require_once __DIR__.'/_layout_top.php'; $pdo = pdo(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="mb-0">Dashboard</h3>
  <div>
    <a href="create.php" class="btn btn-primary">Add Staff</a>
    <a href="import.php" class="btn btn-outline-secondary">Import CSV</a>
  </div>
</div>
<?php
$q   = trim($_GET['q'] ?? '');
$cat = (int)($_GET['category_id'] ?? 0);

$perPage = max(1, (int)($_GET['per'] ?? 25));
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$params = [];
$sql = "FROM staff s JOIN departments d ON s.dept_id=d.id JOIN categories c ON s.category_id=c.id";
if ($q !== '' || $cat>0) $sql .= " WHERE 1";
if ($q !== '') { $sql .= " AND (s.name LIKE :q OR s.email LIKE :q OR s.intercom LIKE :q OR d.name LIKE :q OR c.name LIKE :q)"; $params[':q'] = "%$q%"; }
if ($cat>0)  { $sql .= " AND s.category_id=:cat"; $params[':cat']=$cat; }

$countSql = "SELECT COUNT(*) ".$sql;
$st = $pdo->prepare($countSql); $st->execute($params); $total = (int)$st->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));

$listSql = "SELECT s.*, d.name AS department, c.name AS category ".$sql." ORDER BY s.name ASC LIMIT :lim OFFSET :off";
$st = $pdo->prepare($listSql);
foreach($params as $k=>$v) $st->bindValue($k,$v);
$st->bindValue(':lim',$perPage,PDO::PARAM_INT);
$st->bindValue(':off',$offset,PDO::PARAM_INT);
$st->execute();
$rows = $st->fetchAll();

$categories = get_categories();
?>
<form class="row g-2 mb-3">
  <div class="col-md-4"><input class="form-control" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Search staff"></div>
  <div class="col-md-3">
    <select class="form-select" name="category_id">
      <option value="0">All Categories</option>
      <?php foreach ($categories as $c): ?>
      <option value="<?= (int)$c['id'] ?>" <?= $cat===(int)$c['id']?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-2">
    <input type="number" class="form-control" name="per" value="<?= (int)$perPage ?>" min="1" max="200" placeholder="Per page">
  </div>
  <div class="col-md-1"><button class="btn btn-outline-primary w-100">Go</button></div>
  <div class="col-md-2"><a href="dashboard.php" class="btn btn-outline-secondary w-100">Reset</a></div>
</form>

<div class="table-responsive shadow-sm">
  <table class="table table-striped table-hover bg-white align-middle">
    <thead class="table-light"><tr>
      <th>Name</th><th>Designation</th><th>Department</th><th>Category</th><th>Email</th><th>Intercom</th><th>Direct Number</th><th>Blood Group</th><th>Actions</th>
    </tr></thead>
    <tbody>
      <?php if (!$rows): ?><tr><td colspan="9" class="text-center text-muted py-4">No entries.</td></tr><?php endif; ?>
      <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['name']) ?></td>
        <td><?= htmlspecialchars($r['designation']) ?></td>
        <td><?= htmlspecialchars($r['department']) ?></td>
        <td><?= htmlspecialchars($r['category']) ?></td>
        <td><?= $r['email'] ? htmlspecialchars($r['email']) : '—' ?></td>
        <td><?= htmlspecialchars($r['intercom']) ?></td>
        <td><?= htmlspecialchars($r['direct_number']) ?></td>
        <td><?= $r['blood_group'] ? htmlspecialchars($r['blood_group']) : '—' ?></td>
        <td>
          <a class="btn btn-sm btn-outline-secondary" href="edit.php?id=<?= (int)$r['id'] ?>">Edit</a>
          <a class="btn btn-sm btn-outline-danger" href="delete.php?id=<?= (int)$r['id'] ?>" onclick="return confirm('Delete this record?')">Delete</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php if ($pages > 1): ?>
<nav class="mt-3">
  <ul class="pagination">
    <?php $qs = $_GET; for ($p=1; $p<=$pages; $p++): $qs['page']=$p; $link='?'.http_build_query($qs); ?>
      <li class="page-item <?= $p===(int)($_GET['page'] ?? 1)?'active':'' ?>"><a class="page-link" href="<?= htmlspecialchars($link) ?>"><?= $p ?></a></li>
    <?php endfor; ?>
  </ul>
</nav>
<?php endif; ?>

<?php require_once __DIR__.'/_layout_bottom.php'; ?>
