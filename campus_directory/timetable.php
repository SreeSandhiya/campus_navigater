<?php
session_start();
require 'db_connect.php';   // uses the same PDO connection as your directory page

// check role
$is_teacher = (isset($_SESSION['role']) && $_SESSION['role'] === 'teacher');

// fetch staff list (adjust table name if needed)
try {
    // If you have a separate "staff" table
    $stmt = $pdo->query("SELECT id, name, department, image_path FROM staff");
    // Or, if staff are in "users" table: 
    // $stmt = $pdo->prepare("SELECT id, name, department, image_path FROM users WHERE role = 'teacher'");
    // $stmt->execute();

    $staffs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $staffs = [];
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Lecturer Timetable</title>
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

#searchStaff {
  width:90%; padding:5px; margin-bottom:12px; border:1px solid #ccc; border-radius:4px;
}
.staff-item {
  display:flex; align-items:center;
  padding:8px; margin-bottom:10px;
  border:1px solid #eee; border-radius:8px; cursor:pointer;
  transition:background .2s;
}
.staff-item:hover { background:#f5f5f5; }
.staff-item img {
  width:40px; height:40px; border-radius:50%; margin-right:10px; object-fit:cover;
}

/* Main content */
.main { flex:1; padding:20px; overflow:auto; background:#fff; }
#timetableTable { border-collapse:collapse; width:100%; margin-top:10px; }
#timetableTable th, #timetableTable td {
  border:1px solid #ccc; padding:8px; text-align:center; min-width:100px;
}
.editable { background:#fff8e1; }
button {
  margin:10px 6px 10px 0;
  padding:8px 14px; border:none; border-radius:5px;
  background:#163d8c; color:#fff; cursor:pointer;
}
button:hover { background:#1d4ed8; }
 .logout-btn {
    position: fixed;
    top: 5px;
    right: 14px;
    background: #e74c3c;
    color: #fff;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    font-weight: bold;
    cursor: pointer;
    z-index: 9999;
}
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

  <!-- Staff Sidebar -->
  <div class="sidebar">
    <h2>Lecturers</h2>
    <input type="text" id="searchStaff" placeholder="Search...">
    <div id="staffList">
      <?php if (!empty($staffs)): ?>
        <?php foreach($staffs as $s): ?>
          <?php
            $img = (!empty($s['image_path']) && file_exists(__DIR__.'/'.$s['image_path']))
              ? $s['image_path']
              : 'uploads/default-staff.png';
          ?>
          <div class="staff-item" data-id="<?= $s['id'] ?>">
            <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($s['name']) ?>">
            <div>
              <b><?= htmlspecialchars($s['name']) ?></b><br>
              <small><?= htmlspecialchars($s['department']) ?></small>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="padding:10px; color:#777;">No staff found.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Main -->
  <div class="main">
    <h2 id="staffTitle">Select a lecturer</h2>
    <?php if ($is_teacher): ?>
      <button id="editBtn">Edit</button>
      <button id="saveBtn" style="display:none;">Save</button>
    <?php endif; ?>
    <div id="tableContainer"></div>
  </div>
</div>

<script>
// ---------- Search filter ----------
document.getElementById('searchStaff').addEventListener('keyup', function(){
  const q = this.value.toLowerCase();
  document.querySelectorAll('.staff-item').forEach(item=>{
    item.style.display = item.textContent.toLowerCase().includes(q)?'':'none';
  });
});

let editing = false, currentId = null;

// ---------- Load timetable on staff click ----------
document.querySelectorAll('.staff-item').forEach(item=>{
  item.addEventListener('click', ()=>{
    currentId = item.dataset.id;
    fetchTimetable(currentId);
    const name = item.querySelector('b').innerText;
    document.getElementById('staffTitle').innerText = name + " - Timetable";
  });
});

function fetchTimetable(staffId){
  fetch('timetable_fetch.php?staff_id='+staffId)
    .then(r=>r.text())
    .then(html=>{
      document.getElementById('tableContainer').innerHTML = html;
      if (isTeacher) {
        document.getElementById('editBtn').style.display='inline-block';
      }
      document.getElementById('saveBtn').style.display='none';
      editing = false;
    });
}

const isTeacher = <?php echo $is_teacher ? 'true' : 'false'; ?>;

if (isTeacher) {
    document.getElementById('editBtn').onclick = ()=>{
      editing = true;
      document.getElementById('saveBtn').style.display='inline-block';
      document.querySelectorAll('#timetableTable td[data-slot]').forEach(td=>{
        td.contentEditable = true;
        td.classList.add('editable');
      });
    };

    document.getElementById('saveBtn').onclick = ()=>{
      const updates = [];
      document.querySelectorAll('#timetableTable td[data-slot]').forEach(td=>{
        updates.push({
          slot: td.dataset.slot,
          day: td.dataset.day,
          value: td.innerText.trim()
        });
      });
      fetch('timetable_save.php', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({staff_id:currentId, data:updates})
      })
      .then(r=>r.text())
      .then(msg=>{
        alert(msg);
        fetchTimetable(currentId);
      });
    };
}
</script>
</body>
</html>
