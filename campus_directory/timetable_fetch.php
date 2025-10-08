<?php
require 'db_connect.php';

if (!isset($_GET['staff_id'])) {
    exit('No staff id given');
}

$staff_id = (int)$_GET['staff_id'];

// make sure the table exists (see next section for schema)
$stmt = $pdo->prepare("SELECT * FROM timetable WHERE staff_id = ?");
$stmt->execute([$staff_id]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// build timetable HTML
$days  = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
$slots = ['9:00-10:00','10:00-11:00','11:00-12:00','12:00-1:00','2:00-3:00','3:00-4:00','4:00-5:00'];

echo '<table id="timetableTable">';
echo '<tr><th>Time</th>';
foreach($days as $d) echo "<th>$d</th>";
echo '</tr>';

foreach($slots as $s) {
    echo "<tr><th>$s</th>";
    foreach($days as $d) {
        $value = '';
        foreach($data as $row){
            if($row['day']==$d && $row['time_slot']==$s){
                $value = htmlspecialchars($row['subject']);
                break;
            }
        }
        echo "<td data-day=\"$d\" data-slot=\"$s\">$value</td>";
    }
    echo "</tr>";
}
echo '</table>';
