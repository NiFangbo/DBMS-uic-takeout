<?php
session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'manager') {
  header('Location: ../login.php');
  exit();
}

include '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // reset password
  $username = $_SESSION['username'];
  $oldpassword = $_POST['oldpassword'];
  $oldpassword = $conn->real_escape_string($oldpassword);
  $newpassword = $_POST['newpassword'];
  $newpassword = $conn->real_escape_string($newpassword);
  $repassword = $_POST['repassword'];
  $repassword = $conn->real_escape_string($repassword);

  if ($newpassword != $repassword){
    $_SESSION['error_message'] = "The passwords entered twice are different.";
  } else {
    $sql = "SELECT * FROM `user` WHERE `username` = '$username'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $password = $row["password"];
    $password = $conn->real_escape_string($password);

    if ($oldpassword != $password) {
      $_SESSION['error_message'] = "Current password is incorrect.";
    } else {
      $sql = "UPDATE `user` SET `password` = $newpassword WHERE `username` = '$username'";
      $conn->query($sql);

      // logout and direct to login page
      session_destroy();
      header("Location: ../login.php?reset=success");
    }
  }
}
?>

<!DOCTYPE html>

<html>

<head>
  <link rel="stylesheet" href="../style/main.css">
  <link rel="stylesheet" href="../style/user.css">
  <title>Account Information - Campus Takeout</title>
  <script>
    
  </script>
</head>

<body class="uic-background manager-page">
  <header>
    <ul class="header-left">
      <li>
        <a href="manager-index.php">
          <p class="header-title">
            Campus Takeout
          </p>
        </a>
      </li>
    </ul>
    <ul class="header-right">
      <li>
        <a class="header-account" href="manager-home.php"><?php echo $_SESSION['username']?> (Manager)</a>
      </li>
    </ul>
  </header>
  <main class="main-body">
    <div class="body-card">
      <div class="user-body">
        <h2 class="bold title">
          Account Information
        </h2>
        <div class="lines-content user-info-block">
          <p>
            <label>Username:</label>
            <span><?php echo $_SESSION['username']?></span>
          </p>
          <p>
            <label>Identity:</label>
            <span>Manager</span>
          </p>
        </div>
        
        <hr />

        <div class="lines-content user-info-block">
          <h3 class="small-title">
            Change Password
          </h3>
          
          <form action="manager-home.php" method="post">
          <p>
            <label>Current Password:</label>
            <input class="input-box" type="password" name="oldpassword" required/>
          </p>
          <p>
            <label>New Password:</label>
            <input class="input-box" type="password" name="newpassword" required/>
          </p>
          <p>
            <label>Repeat New Password:</label>
            <input class="input-box" type="password" name="repassword" required/>
          </p>
          <p>
            <button class="button" type="submit">Change Password</button>
          </p>
          </form>
          
        </div>

        <hr />

        <div class="user-info-footer">
          <button class="button-danger button" onclick="window.location.href='../logout.php'">Logout</button>
        </div>
      </div>
    </div>

    <?php
    if (isset($_SESSION['normal_message'])) {
      echo "<script>alert('" . $_SESSION['normal_message'] . "')</script>";
      unset($_SESSION['normal_message']);
    }
    ?>
    <?php
    if (isset($_SESSION['error_message'])) {
      echo "<script>alert('" . $_SESSION['error_message'] . "')</script>";
      unset($_SESSION['error_message']);
    }
    ?>

  </main>
</body>

</html>