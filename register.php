<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // register a new user account
  $username = $_POST['username'];
  $username = $conn->real_escape_string($username);
  $password = $_POST['password'];
  $password = $conn->real_escape_string($password);
  $repassword = $_POST['repassword'];
  $repassword = $conn->real_escape_string($repassword);
  $user_type = $_POST['user_type'];

  if ($password != $repassword){
    $_SESSION['error_message'] = "The passwords entered twice are different. Please reset.";
  } else {
    // check if username exists
    $sql = "SELECT * FROM `user` WHERE `username` = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
      $_SESSION['error_message'] = "Username already exists.";
    } else {
      // insert user and update customer information
      $sql = "INSERT INTO `user`(`username`, `password`, `user_type`) VALUES ('$username', '$password', '$user_type')";
      $conn->query($sql);

      // header to login page and print the successful message
      header("Location: login.php?register=success");
      exit();
    }
  }
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
    <div class="body-card register-card">
      <h2>Register</h2>

      <form action="register.php" method="post">
        <input class="login-input input-box" type="text" name="username" placeholder="Username" required/>
        <input class="login-input input-box" type="password" name="password" placeholder="Password" required/>
        <input class="login-input input-box" type="password" name="repassword" placeholder="Re-enter your password" required/>
        <input type="hidden" name="user_type" value="customer"/>
        <button class="login-button button" type="submit">Register</button>
      </form>

        <?php
        if (isset($_SESSION['error_message'])) {
            echo "<p class='error-message'>" . $_SESSION['error_message'] . "</p>";
            unset($_SESSION['error_message']);
        }
        ?>
        
      <p class="login-bottom-hint">Already have an account? <a href="login.php">Login now!</a></p>

    </div>
  </div>
</body>
  
</html>