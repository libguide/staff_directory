
<?php
require_once __DIR__.'/config.php';
$pdo = pdo();

$search = trim($_GET['q'] ?? '');
$dept   = (int)($_GET['department_id'] ?? 0);
$cat    = (int)($_GET['category_id'] ?? 0);

$perPage = max(1, (int)($_GET['per'] ?? 25));
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$params = [];
$sql = "FROM staff s
        JOIN departments d ON s.dept_id = d.id
        JOIN categories  c ON s.category_id = c.id
        WHERE 1";
if ($search !== '') {
  $sql .= " AND (s.name LIKE :q OR s.email LIKE :q OR s.intercom LIKE :q OR s.designation LIKE :q OR d.name LIKE :q OR c.name LIKE :q)";
  $params[':q'] = "%$search%";
}
if ($dept > 0) { $sql .= " AND s.dept_id = :dept"; $params[':dept']=$dept; }
if ($cat  > 0) { $sql .= " AND s.category_id = :cat"; $params[':cat']=$cat; }

$countSql = "SELECT COUNT(*) ".$sql;
$st = $pdo->prepare($countSql); $st->execute($params); $total = (int)$st->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));

$listSql = "SELECT s.*, d.name AS department, c.name AS category ".$sql." ORDER BY s.name ASC LIMIT :lim OFFSET :off";
$st = $pdo->prepare($listSql);
foreach ($params as $k=>$v) $st->bindValue($k, $v);
$st->bindValue(':lim', $perPage, PDO::PARAM_INT);
$st->bindValue(':off', $offset, PDO::PARAM_INT);
$st->execute();
$rows = $st->fetchAll();

$deptRows = get_departments();
$categories = get_categories();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title><?= htmlspecialchars(APP_NAME) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand" href="./"><?= htmlspecialchars(APP_NAME) ?></a>
    <div class="ms-auto">
      <a class="btn btn-outline-light btn-sm" href="admin/login.php">Admin</a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <div class="card shadow-sm">
    <div class="card-body">
      <form class="row g-2 align-items-end" method="get">
        <div class="col-md-4">
          <label class="form-label">Search (name/email/intercom/designation)</label>
          <input type="text" class="form-control" name="q" value="<?= htmlspecialchars($search) ?>" />
        </div>
        <div class="col-md-3">
          <label class="form-label">Department</label>
          <select class="form-select" name="department_id">
            <option value="0">All</option>
            <?php foreach ($deptRows as $d): ?>
              <option value="<?= (int)$d['id'] ?>" <?= $dept===(int)$d['id']?'selected':'' ?>><?= htmlspecialchars($d['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Category</label>
          <select class="form-select" name="category_id">
            <option value="0">All</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?= (int)$c['id'] ?>" <?= $cat===(int)$c['id']?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Per page</label>
          <input type="number" class="form-control" name="per" value="<?= (int)$perPage ?>" min="1" max="200">
        </div>
        <div class="col-md-2">
          <button class="btn btn-primary w-100">Apply</button>
        </div>
        <div class="col-md-2 text-end">
          <a href="./" class="btn btn-outline-secondary w-100">Reset</a>
        </div>
      </form>
    </div>
  </div>

  <div class="mt-4">
    <div class="table-responsive shadow-sm">
      <table class="table table-striped table-hover align-middle bg-white">
        <thead class="table-light">
          <tr>
            <th>Name</th>
            <th>Designation</th>
            <th>Department</th>
            <th>Category</th>
            <th>Email</th>
            <th>Intercom</th>
            <th>Direct Number</th>
            <th>Blood Group</th>
            <th>Address</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$rows): ?>
            <tr><td colspan="9" class="text-center py-4 text-muted">No staff found.</td></tr>
          <?php endif; ?>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?= htmlspecialchars($r['name']) ?></td>
              <td><?= htmlspecialchars($r['designation']) ?></td>
              <td><?= htmlspecialchars($r['department']) ?></td>
              <td><?= htmlspecialchars($r['category']) ?></td>
              <td><?= $r['email'] ? '<a href="mailto:'.htmlspecialchars($r['email']).'">'.htmlspecialchars($r['email']).'</a>' : '<span class="text-muted">—</span>' ?></td>
              <td><?= htmlspecialchars($r['intercom']) ?></td>
              <td><?= htmlspecialchars($r['direct_number']) ?></td>
              <td><?= $r['blood_group'] ? htmlspecialchars($r['blood_group']) : '<span class="text-muted">—</span>' ?></td>
              <td><?= nl2br(htmlspecialchars($r['address'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if ($pages > 1): ?>
    <nav class="mt-3">
      <ul class="pagination">
        <?php $qs = $_GET;
          for ($p=1; $p<=$pages; $p++):
            $qs['page']=$p; $link='?'.http_build_query($qs); ?>
          <li class="page-item <?= $p===$page?'active':'' ?>">
            <a class="page-link" href="<?= htmlspecialchars($link) ?>"><?= $p ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
