<?php
session_start();
require 'db_connect.php';  // your PDO connection

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? 'student';

if ($role === 'teacher') {
    // Teachers must exist in DB
    $stmt = $pdo->prepare("SELECT * FROM staff WHERE email=? AND password=?");
    $stmt->execute([$email, $password]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user'] = $user['name'];
        $_SESSION['role'] = 'teacher';
        header("Location: dashboard.php");
        exit;
    } else {
        echo "Invalid teacher credentials.";
    }
} else {
    // Students can be anyone
    $_SESSION['user'] = $email;
    $_SESSION['role'] = 'student';
    header("Location: dashboard.php");
    exit;
}
?>
