<?php
require 'db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);
$staff_id = (int)$data['staff_id'];
$entries  = $data['data'];

// delete old timetable for that staff
$pdo->prepare("DELETE FROM timetable WHERE staff_id=?")->execute([$staff_id]);

// insert updated rows
$insert = $pdo->prepare("INSERT INTO timetable (staff_id,day,time_slot,subject) VALUES (?,?,?,?)");
foreach($entries as $e){
    if(!empty($e['value'])){
        $insert->execute([$staff_id, $e['day'], $e['slot'], $e['value']]);
    }
}

echo "Timetable saved successfully";
