<?php
session_start();
require 'db_connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    // user table-ல் verify பண்ணனும்
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
    $stmt->execute([$email, $role]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // session set
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        // redirect
        header("Location:campus-map.html");
        exit;
    } else {
        $error = "Invalid email, password, or role!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Campus Navigator – Login</title>
  <style>
    body { margin: 0; font-family: Arial, sans-serif; background: #f4f4f9; }
    .container { display: flex; justify-content: center; align-items: center; min-height: 100vh; }
    .login-box { background: #fff; padding: 30px 25px; border-radius: 10px; width: 320px; box-shadow: 0 0 12px rgba(0,0,0,0.2); text-align: center; }
    .switch-wrapper { margin: 15px auto 25px; width: 220px; background: #e0e0e0; border-radius: 30px; display: flex; position: relative; overflow: hidden; cursor: pointer; }
    .switch-slider { position: absolute; top: 0; left: 0; width: 50%; height: 100%; background: #4a148c; border-radius: 30px; transition: left 0.3s; }
    .switch-option { flex: 1; padding: 10px 0; font-weight: bold; z-index: 1; color: #4a148c; user-select: none; text-align: center; }
    .switch-option.active { color: #fff; }
    label { display: block; text-align: left; margin: 10px 0 5px; font-weight: bold; }
    input[type=email], input[type=password] { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px; }
    button.btn { background: #4a148c; color: #fff; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-size: 1rem; }
    .error { color: red; margin-bottom: 10px; }
  </style>
</head>
<body>
  <div class="container"> 
    <div class="login-box"> 
      <div class="logo"> 
        <img src="images/location.png" alt="Location Icon" width="70" height="70"> 
      </div> 
      <h2 class="title">Campus Navigator</h2> 
      <p class="subtitle" id="subtitle">Sign in as Student</p>

      <div class="switch-wrapper" onclick="toggleSwitch()"> 
        <div id="slider" class="switch-slider"></div> 
        <div id="optStudent" class="switch-option active" onclick="setRole('teacher')">Student</div> 
        <div id="optTeacher" class="switch-option" onclick="setRole('student')">Teacher</div> 
      </div> 

      <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <form method="post" action="">
        <label for="email">Email Address</label> 
        <input type="email" name="email" id="email" required placeholder="Enter your email"> 
        <label for="password">Password</label> 
        <input type="password" name="password" id="password" required placeholder="Enter your password"> 
        <input type="hidden" name="role" id="role" value="student"> 
        <button type="submit" class="btn">Login</button> 
       </form> 
        <p style="margin-top:15px;">Already have an account? <a href="signup.php">Signin here</a></p>   
    </div> 
  </div> 

  <script>
    function setRole(role) { 
      document.getElementById('role').value = role; 
      document.getElementById('subtitle').innerText = 'Sign in as ' + (role === 'teacher' ? 'Teacher' : 'Student'); 
      const slider = document.getElementById('slider'); 
      slider.style.left = role === 'student' ? '0%' : '50%'; 
      document.getElementById('optStudent').classList.toggle('active', role === 'student'); 
      document.getElementById('optTeacher').classList.toggle('active', role === 'teacher'); 
    } 
    function toggleSwitch() { 
      const current = document.getElementById('role').value; 
      setRole(current === 'student' ? 'teacher' : 'student'); 
    } 
  </script>
</body>
</html>
