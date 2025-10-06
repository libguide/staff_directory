
<?php require_once __DIR__.'/auth.php'; require_once __DIR__.'/_layout_top.php'; $pdo = pdo();

$ok=''; $errors=[]; $report=[];

function norm_key($s){ $s=strtolower(trim($s)); return preg_replace('/[^a-z0-9]/','',$s); }
function has_data($row){ if(!is_array($row))return false; foreach($row as $v){ if(trim((string)$v)!=='') return true; } return false; }
function first_cell_trim_bom($s){ if(substr($s,0,3)==="\xEF\xBB\xBF") return substr($s,3); return $s; }

$alias_map = [
  'name'         => ['name','staffname','fullname'],
  'designation'  => ['designation','desig','title','jobtitle','position'],
  'department'   => ['department','dept','deptname','departmentname','section'],
  'email'        => ['email','emailid','e-mail','mailid'],
  'intercom'     => ['intercom','mobile','mobileno','mobilenumber','phone','phone1','primaryphone','contactno','cell'],
  'directnumber' => ['directnumber','direct','officephone','officephoneno','officephonenumber','officeno','landline','telephone','telephoneoffice','phone2','altphone'],
  'address'      => ['address','addr','postaladdress','location'],
  'bloodgroup'   => ['bloodgroup','blood','bloodgrp','bgroup'],
  'category'     => ['category','type','staffcategory','role']
];

function locate_columns_with_header($first_row, $alias_map){
  $present = [];
  foreach ($first_row as $i=>$h){
    $nk = norm_key($h);
    foreach ($alias_map as $canonical=>$aliases){
      foreach ($aliases as $a){ if ($nk===norm_key($a)){ $present[$canonical]=$i; break 2; } }
    }
  }
  return $present;
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
  check_csrf();
  if (!isset($_FILES['csv']) || $_FILES['csv']['error']!==UPLOAD_ERR_OK) { $errors[]='Upload failed.'; }
  else {
    $tmp = $_FILES['csv']['tmp_name'];
    $fh = fopen($tmp, 'r'); if (!$fh) { $errors[]='Cannot open uploaded file.'; }
    else {
      $first = fgetcsv($fh);
      if ($first===false || !has_data($first)) { $errors[]='Empty file.'; }
      else {
        if (isset($first[0])) $first[0] = first_cell_trim_bom($first[0]);
        $present = locate_columns_with_header($first, $alias_map);
        $has_header = count($present) > 0;

        $ins = $pdo->prepare("INSERT INTO staff(name,designation,dept_id,category_id,email,intercom,direct_number,address,blood_group)
                              VALUES (?,?,?,?,?,?,?,?,?)
                              ON DUPLICATE KEY UPDATE
                                name=VALUES(name),
                                designation=VALUES(designation),
                                dept_id=VALUES(dept_id),
                                category_id=VALUES(category_id),
                                email=VALUES(email),
                                intercom=VALUES(intercom),
                                direct_number=VALUES(direct_number),
                                address=VALUES(address),
                                blood_group=VALUES(blood_group)");

        $pdo->beginTransaction(); $count=0; $rownum=1;
        try {
          $rows = [];
          if ($has_header) { while(($r=fgetcsv($fh))!==false){ if(has_data($r)) $rows[]=$r; } }
          else { $rows[]=$first; while(($r=fgetcsv($fh))!==false){ if(has_data($r)) $rows[]=$r; } }

          foreach($rows as $r){
            $rownum++;
            $name=$designation=$department=$intercom=$direct=$address='';
            $email=null; $blood=null; $category='Staff';

            if ($has_header){
              $get=function($key) use($present,$r){ if(!isset($present[$key]))return ''; $idx=$present[$key]; return isset($r[$idx])?trim($r[$idx]):''; };
              $name        = $get('name');
              $designation = $get('designation');
              $department  = $get('department');
              $email_raw   = $get('email');
              $intercom    = $get('intercom');
              $direct      = $get('directnumber');
              $address     = $get('address');
              $blood_raw   = $get('bloodgroup');
              $category    = $get('category') ?: 'Staff';
            } else {
              $c = count($r);
              if ($c >= 8){
                [$name,$designation,$department,$email_raw,$intercom,$direct,$address,$blood_raw] = array_map('trim', array_slice($r,0,8));
              } elseif ($c >= 7){
                [$name,$designation,$department,$email_raw,$intercom,$address,$blood_raw] = array_map('trim', array_slice($r,0,7));
                $direct='';
              } else {
                $report[] = "Row $rownum: skipped (too few columns)";
                continue;
              }
              $category = 'Staff';
            }

            if ($department==='') { $department='Unassigned'; }
            if ($intercom==='') { $report[]="Row $rownum: Intercom missing → skipped"; continue; }

            $dept_id = get_or_create_department($department);
            $category_id = get_or_create_category($category ?: 'Staff');

            $email = (filter_var($email_raw ?? '', FILTER_VALIDATE_EMAIL)) ? $email_raw : null;
            $blood = ($blood_raw !== '') ? $blood_raw : null;

            try {
              $ins->execute([$name,$designation,$dept_id,$category_id,$email,$intercom,$direct,$address,$blood]);
              $count++;
            } catch (Exception $e) {
              $report[] = "Row $rownum error: ".$e->getMessage();
            }
          }

          $pdo->commit(); $ok="Imported $count rows.";
        } catch (Exception $e) { $pdo->rollBack(); $errors[] = 'Import failed: '.$e->getMessage(); }
      }
      fclose($fh);
    }
  }
}
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="mb-0">Import CSV</h3>
  <a class="btn btn-outline-secondary" href="dashboard.php">Back</a>
</div>
<?php if ($ok): ?><div class="alert alert-success"><?= htmlspecialchars($ok) ?></div><?php endif; ?>
<?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?></ul></div><?php endif; ?>
<?php if ($report): ?><div class="alert alert-warning"><ul class="mb-0"><?php foreach ($report as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?></ul></div><?php endif; ?>

<div class="card">
  <div class="card-body">
    <form method="post" enctype="multipart/form-data">
      <?php csrf_field(); ?>
      <div class="mb-3">
        <label class="form-label">CSV File</label>
        <input type="file" class="form-control" name="csv" accept=".csv" required>
        <div class="form-text">
          Header/table optional. Recognized aliases: Intercom (Mobile), DirectNumber (Office Phone), Category.
          Email & Blood Group are optional. Empty Department → "Unassigned". Intercom is required.
        </div>
      </div>
      <button class="btn btn-primary">Upload & Import</button>
      <a class="btn btn-outline-secondary" href="../sample.csv" download>Download sample.csv</a>
    </form>
  </div>
</div>
<?php require_once __DIR__.'/_layout_bottom.php'; ?>
