<?php
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $department = $_POST['department'];
    $subject = $_POST['subject'];
    $room = $_POST['room'];
    $experience = $_POST['experience'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $imgPath = null;

    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir);
        $fileName = time().'_'.basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $targetDir.$fileName);
        $imgPath = $targetDir.$fileName;
    }

    $sql = "INSERT INTO staff (name,department,subject,room,experience,email,phone,image_path)
            VALUES (?,?,?,?,?,?,?,?)";
    $pdo->prepare($sql)->execute([$name,$department,$subject,$room,$experience,$email,$phone,$imgPath]);

    header("Location: campus-directory.php");
    exit;
}
