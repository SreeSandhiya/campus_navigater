<?php
session_start();
/* ======== DATABASE CONNECTION ======== */
$host="localhost";
$port="3307";
$dbname="campus_directory";
$user="root";
$pass="";
try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    die("DB Connection failed: ".$e->getMessage());
}

/* ======== ADD STAFF ======== */
if(isset($_POST['action']) && $_POST['action']==='add' && $_SESSION['role']==='teacher'){
    $imagePath='';
    if(!empty($_FILES['image']['name'])){
        $targetDir="uploads/";
        if(!is_dir($targetDir)) mkdir($targetDir);
        $imagePath=$targetDir.basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'],$imagePath);
    }
    $sql="INSERT INTO staff(name,department,subject,room,experience,email,phone,image_path)
          VALUES(?,?,?,?,?,?,?,?)";
    $pdo->prepare($sql)->execute([
        $_POST['name'],$_POST['department'],$_POST['subject'],
        $_POST['room'],$_POST['experience'],$_POST['email'],$_POST['phone'],$imagePath
    ]);
    header("Location: campus-directory.php"); exit;
}

/* ======== EDIT STAFF ======== */
if(isset($_POST['action']) && $_POST['action']==='edit' && $_SESSION['role']==='teacher'){
    $id=(int)$_POST['id'];
    $imageSql=''; $params=[];
    if(!empty($_FILES['image']['name'])){
        $targetDir="uploads/";
        if(!is_dir($targetDir)) mkdir($targetDir);
        $imagePath=$targetDir.basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'],$imagePath);
        $imageSql=", image_path=?";
        $params[]=$imagePath;
    }
    $params=array_merge([
        $_POST['name'],$_POST['department'],$_POST['subject'],
        $_POST['room'],$_POST['experience'],$_POST['email'],$_POST['phone']
    ],$params,[$id]);
    $sql="UPDATE staff SET name=?,department=?,subject=?,room=?,experience=?,email=?,phone=? $imageSql WHERE id=?";
    $pdo->prepare($sql)->execute($params);
    header("Location: campus-directory.php"); exit;
}

/* ======== DELETE STAFF ======== */
if(isset($_GET['delete']) && $_SESSION['role']==='teacher'){
    $id=(int)$_GET['delete'];
    $pdo->prepare("DELETE FROM staff WHERE id=?")->execute([$id]);
    header("Location: campus-directory.php"); exit;
}

/* ======== FETCH STAFF ======== */
$stmt=$pdo->query("SELECT * FROM staff ORDER BY department,name");
$rows=$stmt->fetchAll(PDO::FETCH_ASSOC);
$directory=[];
foreach($rows as $r) $directory[$r['department']][]=$r;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Campus Directory</title>
<style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      display: flex;
      height: 100vh;
      flex-direction: column;
    }

    /* Topbar */
    .topbar {
      height: 50px;
      background: #fff;
      border-bottom: 1px solid #ddd;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 20px;
      font-size: 16px;
      font-weight: bold;
    }
    .topbar .user {
      font-size: 14px;
      font-weight: normal;
      color: #333;
      height: 100px;
    }
.topbar .logo {
  display: flex;
  align-items: center;
  font-size: 15px;
  font-weight: bold;
}

.topbar .logo img {
  height: 28px;   /* adjust size */
  margin-right: 8px;
}

    /* Main layout: sidebar + content */
    .main-wrapper {
      display: flex;
      flex: 1;
    }

    /* Sidebar */
.sidebar {
  width: 220px;
  background: #fff;
  border-right: 1px solid #ddd;
  padding: 20px 0;
  font-family: Arial, sans-serif;
  height: 100vh;   /* full height */
}

/* Sidebar title */
.sidebar h2 {
  font-size: 14px;
  font-weight: bold;
  padding: 0 20px;
  margin-bottom: 15px;
  color: #555;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Sidebar list */
.sidebar ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

/* Sidebar items */
.sidebar li {
  margin: 5px 0;
}

.sidebar li a {
  display: block;
  padding: 12px 20px;
  text-decoration: none;
  color: #333;
  font-size: 14px;
  border-radius: 8px;
  transition: all 0.3s;
}

/* Hover effect */
.sidebar li a:hover {
  background: #f0f4ff;
  color: #163d8c;
}

/* Active page */
.sidebar li.active a {
  background: #163d8c;
  color: #fff;
  font-weight: bold;
}

.sidebar li a:hover { background: #f0f4ff; color: #163d8c; }
.sidebar li.active a { background: #163d8c; color: #fff; font-weight: bold; }
.main { flex:1; padding:20px; overflow-y:auto; }
.search-bar{display:flex; gap:10px; margin-bottom:15px;}
.search-bar input{ flex:1; max-width:600px; padding:8px; border:1px solid #ccc; border-radius:5px; }
.action-btn{ padding:6px 12px; border:none; border-radius:5px; background:#163d8c; color:#fff; cursor:pointer; }
.action-btn:hover{background:#0d2b66;}
.department{margin-bottom:30px;}
.department h3{ margin-bottom:10px; color:#163d8c; border-bottom:1px solid #ccc; padding-bottom:4px; }
.staff-list { display:flex; gap:15px; overflow-x:auto; padding-bottom:10px; scrollbar-width:thin; }
.staff-list::-webkit-scrollbar { height:8px; }
.staff-list::-webkit-scrollbar-thumb { background:#bbb; border-radius:4px; }
.staff-card { flex:0 0 auto; width:180px; background:#fff; border:1px solid #ddd; border-radius:10px; padding:15px; text-align:center; box-shadow:0 2px 5px rgba(0,0,0,0.05); }
.staff-card img { width:120px; height:120px; object-fit:cover; border-radius:50%; margin-bottom:20px; }
.staff-card b { color:#163d8c; display: block; margin-top: 5px; }
.card-btn{ margin-top:6px; padding:5px 10px; border:none; border-radius:5px; font-size:12px; cursor:pointer; }
.edit-btn{background:#1976d2; color:#fff;}
.edit-btn:hover{background:#125a9c;}
.delete-btn{background:#c62828; color:#fff;}
.delete-btn:hover{background:#a01414;}
.modal{ display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; }
.modal-content{ background:#fff; padding:20px; border-radius:10px; max-width:400px; width:90%; }
.modal-content label{display:block; margin-top:10px;}
.modal-content input{width:100%; padding:6px; margin-top:3px; border:1px solid #ccc; border-radius:5px;}
.logout-btn { position: fixed; top: 15px; right: 20px; background: #e74c3c; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-weight: bold; cursor: pointer; z-index: 9999; }
.logout-btn:hover { background: #c0392b; }
</style>
</head>
<body>
<!-- Top Bar -->
<div class="topbar">
  <div class="logo">
    <img src="images/location.png" alt="Logo">
    <span>Campus Navigator</span>
  </div>
  <div class="user">Student User ⌄</div>
</div>

<div class="main-wrapper">
  <!-- Sidebar -->
  <div class="sidebar">
      <h2>Campus Navigator</h2>
      
       <ul>
  <li><a href="campus-map.html">Campus Map</a></li>
  <li><a href="timetable.php">Timetable</a></li>
  <li><a href="campus-directory.php">Directory</a></li>
</ul>

    
      <form action="logout.php" method="post" style="position:relative;">
   <button type="submit" class="logout-btn">Logout</button>
</form>
    </div>
  <div class="main">
    <h2>Campus Directory</h2>
    <div class="search-bar">
      <input type="text" id="searchInput" placeholder="Search by name, subject, or department..." onkeyup="filterStaff()">
      <?php if ($_SESSION['role']==='teacher'): ?>
        <button class="action-btn" onclick="openAddModal()">Add Staff</button>
      <?php endif; ?>
    </div>

    <div id="directory">
      <?php foreach ($directory as $dept=>$staffList): ?>
        <div class="department">
          <h3><?= htmlspecialchars($dept) ?> Department</h3>
          <div class="staff-list">
            <?php foreach ($staffList as $p):
              $img = '';
              if ($p['image_path']) {
                if (file_exists(__DIR__ . "/{$p['image_path']}")) $img=$p['image_path'];
                elseif (file_exists(__DIR__ . "/uploads/{$p['image_path']}")) $img="uploads/{$p['image_path']}";
              }
            ?>
              <div class="staff-card">
                <img src="<?= $img ?: 'uploads/default-staff.png' ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                <b><?= htmlspecialchars($p['name']) ?></b><br>
                Subject: <?= htmlspecialchars($p['subject']) ?><br>
                Room: <?= htmlspecialchars($p['room']) ?><br>
                Exp: <?= htmlspecialchars($p['experience']) ?> yrs<br>
                <a href="mailto:<?= htmlspecialchars($p['email']) ?>"><?= htmlspecialchars($p['email']) ?></a><br>
                <a href="tel:<?= htmlspecialchars($p['phone']) ?>"><?= htmlspecialchars($p['phone']) ?></a><br>
                <?php if ($_SESSION['role']==='teacher'): ?>
                  <button class="card-btn edit-btn"
                    onclick="openEditModal(<?= $p['id'] ?>,'<?= htmlspecialchars($p['name']) ?>','<?= htmlspecialchars($p['department']) ?>','<?= htmlspecialchars($p['subject']) ?>','<?= htmlspecialchars($p['room']) ?>','<?= htmlspecialchars($p['experience']) ?>','<?= htmlspecialchars($p['email']) ?>','<?= htmlspecialchars($p['phone']) ?>')">Edit</button>
                  <button class="card-btn delete-btn"
                    onclick="confirmDelete(<?= $p['id'] ?>,'<?= htmlspecialchars($p['name']) ?>')">Delete</button>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<?php if ($_SESSION['role']==='teacher'): ?>
<!-- Modal only for teachers -->
<div id="staffModal" class="modal">
  <div class="modal-content">
    <h3 id="modalTitle">Add Staff</h3>
    <form id="staffForm" method="post" enctype="multipart/form-data">
      <input type="hidden" name="action" value="add" id="formAction">
      <input type="hidden" name="id" id="staffId">
      <label>Name *<input name="name" id="name" required></label>
      <label>Department *<input name="department" id="department" required></label>
      <label>Subject *<input name="subject" id="subject" required></label>
      <label>Room *<input name="room" id="room" required></label>
      <label>Experience (years)<input name="experience" id="experience"></label>
      <label>Email *<input name="email" id="email" type="email" required></label>
      <label>Phone *<input name="phone" id="phone" required></label>
      <label>Image<input type="file" name="image" id="image"></label>
      <button class="action-btn" type="submit">Save</button>
      <button class="action-btn" type="button" onclick="closeModal()">Close</button>
    </form>
  </div>
</div>
<?php endif; ?>

<script>
function filterStaff(){
  const q=document.getElementById("searchInput").value.toLowerCase();
  document.querySelectorAll('.department').forEach(d=>{
    let match=false;
    d.querySelectorAll('.staff-card').forEach(c=>{
      const show=c.textContent.toLowerCase().includes(q);
      c.style.display=show?'':'none';
      if(show) match=true;
    });
    d.style.display=match?'':'none';
  });
}
function confirmDelete(id,name){
  if(confirm("Delete "+name+"?")){
    window.location='campus-directory.php?delete='+id;
  }
}
function openAddModal(){
  document.getElementById('modalTitle').textContent="Add Staff";
  document.getElementById('formAction').value="add";
  document.getElementById('staffForm').reset();
  document.getElementById('staffModal').style.display='flex';
}
function openEditModal(id,name,dept,subj,room,exp,email,phone){
  document.getElementById('modalTitle').textContent="Edit Staff";
  document.getElementById('formAction').value="edit";
  document.getElementById('staffId').value=id;
  document.getElementById('name').value=name;
  document.getElementById('department').value=dept;
  document.getElementById('subject').value=subj;
  document.getElementById('room').value=room;
  document.getElementById('experience').value=exp;
  document.getElementById('email').value=email;
  document.getElementById('phone').value=phone;
  document.getElementById('staffModal').style.display='flex';
}
function closeModal(){
  document.getElementById('staffModal').style.display='none';
}
</script>
</body>
</html>
