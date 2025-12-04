<?php
session_start();

// header for direction
if (isset($_SESSION['user_id'])) {
  if ($_SESSION['user_type'] == 'manager') {
    header("Location: manager/manager-index.php");
  } else if ($_SESSION['user_type'] == 'deliveryman') {
    header("Location: delivery/delivery-pending.php");
  } else{
    header("Location: products.php?category=All");
  }
  exit();
}

include 'db.php';

// header from register
$username_registered = $_GET['register'] ?? '';
if ($username_registered) {
  $_SESSION['normal_message'] = "Registration successful! Please login.";
}

// header from reset password
$password_reset = $_GET['reset'] ?? '';
if ($password_reset) {
  $_SESSION['normal_message'] = "Successfully reset password! Please login again.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // login
  $username = $_POST['username'];
  $username = $conn->real_escape_string($username);
  $password = $_POST['password'];
  $password = $conn->real_escape_string($password);

  $sql = "SELECT * FROM `user` WHERE `username` = '$username'";
  $result = $conn->query($sql);

  if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if ($password == $row['password']) {
      $_SESSION['user_id'] = $row['user_id'];
      $_SESSION['username'] = $row['username'];
      $_SESSION['user_type'] = $row['user_type']; 
      if ($_SESSION['user_type'] == 'manager') {
          header("Location: manager/manager-index.php");
      } else if ($_SESSION['user_type'] == 'deliveryman') {
          header("Location: delivery/delivery-pending.php");
      } else{
          header("Location: products.php?category=All");
      }
      exit();
    } else {
      $_SESSION['error_message'] = "Incorrect password.";
    }
  } else {
    $_SESSION['error_message'] = "No user found with that username.";
  }

  // Redirect to display the error message
  header("Location: login.php");
  exit();
}
?>
<!DOCTYPE html>

<html>

<head>
  <link rel="stylesheet" href="style/main.css">
  <link rel="stylesheet" href="style/login.css">
  <title>Login - Campus takeout</title>
  <script>
    
  </script>
</head>

<body class="uic-background">
  <div class="login-page">
    <p class="login-title">
      UIC <br/>
      Campus <br/>
      Takeout
    </p>
    <div class="body-card login-card">
      <h2>Login</h2>
      
      <form action="login.php" method="post">
        <input class="login-input input-box" type="text" name="username" placeholder="Username" required/>
        <input class="login-input input-box" type="password" name="password" placeholder="Password" required/>
        <button class="login-button button" type="submit">Login</button>
      </form>
      
        <?php
        if (isset($_SESSION['normal_message'])) {
            echo "<p class='normal-message'>" . $_SESSION['normal_message'] . "</p>";
            unset($_SESSION['normal_message']);
        }
        ?>
        <?php
        if (isset($_SESSION['error_message'])) {
            echo "<p class='error-message'>" . $_SESSION['error_message'] . "</p>";
            unset($_SESSION['error_message']);
        }
        ?>
      
      <p class="login-bottom-hint">
        Don't have an account?
        <a href="register.php">Register Now.</a>
      </p>
    </div>
  </div>
</body>
  
</html>