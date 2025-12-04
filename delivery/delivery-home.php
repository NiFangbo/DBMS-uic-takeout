<?php
session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'deliveryman') {
  header('Location: ../login.php');
  exit();
}

include '../db.php';

// deliveryman information
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM `deliveryman` WHERE `deliveryman_id` = '$user_id'";
$deliveryman_result = $conn->query($sql);
$deliveryman = $deliveryman_result->fetch_assoc();


if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // reset password
  if (isset($_POST["reset_password"])) {
    $oldpassword = $_POST['oldpassword'];
    $oldpassword = $conn->real_escape_string($oldpassword);
    $newpassword = $_POST['newpassword'];
    $newpassword = $conn->real_escape_string($newpassword);
    $repassword = $_POST['repassword'];
    $repassword = $conn->real_escape_string($repassword);

    if ($newpassword != $repassword){
      $_SESSION['error_message'] = "The passwords entered twice are different.";
    }else{
      $sql = "SELECT * FROM `user` WHERE `user_id` = $user_id";
      $result = $conn->query($sql);
      $row = $result->fetch_assoc();
      $password = $row["password"];
      $password = $conn->real_escape_string($password);

      if ($oldpassword != $password ) {
        $_SESSION['error_message'] = "Current password is incorrect.";
      } else {
        // change the password
        $sql = "UPDATE `user` SET `password` = '$newpassword' WHERE `user_id` = $user_id";
        if ($conn->query($sql) === TRUE) {
          session_destroy();
          header("Location: ../login.php?reset=success");
          exit();
        } else {
          $_SESSION['error_message'] = "Error: " . $sql . "<br>" . $conn->error;
        }
      }
    }
  }

  if (isset($_POST["reset_phone"])) {
    $phone = $_POST['phone'];
    $phone = $conn->real_escape_string($phone);
    $sql = "UPDATE `deliveryman` SET `phone` = '$phone' WHERE `deliveryman_id` = $user_id";
    if ($conn->query($sql) === TRUE) {
      $_SESSION['normal_message'] = "Successfully Update the phone!";
    } else {
      $_SESSION['error_message'] = "Error: " . $sql . "<br>" . $conn->error;
    }
  }

  header("Location: delivery-home.php");
  exit();
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

<body class="uic-background delivery-page">
  <header>
    <ul class="header-left">
      <li>
        <a href="delivery-pending.php">
          <p class="header-title">
            Campus Takeout
          </p>
        </a>
      </li>
    </ul>
    <ul class="header-right">
      <li>
        <a class="header-account" href="delivery-home.php"><?php echo $_SESSION['username']?> (Delivery Person)</a>
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
            <span>Professional Delivery Person</span>
          </p>
          <p>
            <label>Phone Number:</label>
            <span><?php echo $deliveryman['phone']?></span>
          </p>
          <p>
            <label>Balance:</label>
            <span class="price">$<?php echo $deliveryman['balance']?></span>
          </p>
        </div>
        
        <hr />

        <form action="delivery-home.php" method="post">
        <div class="lines-content user-info-block">
          <h3 class="small-title">
            Change Password
          </h3>
          <p>
            <label>Current Password:</label>
            <input class="input-box" type="password" value="" name="oldpassword" required>
          </p>
          <p>
            <label>New Password:</label>
            <input class="input-box" type="password" value="" name="newpassword" required>
          </p>
          <p>
            <label>Repeat New Password:</label>
            <input class="input-box" type="password" value="" name="repassword" required>
          </p>
          <p>
            <input class="button" type="submit" value="Change Password" name="reset_password">
          </p>
        </div>
        </form>

        <hr />
        
        <form action="delivery-home.php" method="post">
        <div class="lines-content user-info-block">
          <h3 class="small-title">
            Change Phone Number
          </h3>
          <p>
            <label>New Phone Number:</label>
            <input class="input-box" type="tel" value="" name="phone" required>
          </p>
          <p>
          <input class="button" type="submit" value="Change Phone Number" name="reset_phone">
          </p>
        </div>
        </form>

        <hr />

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

        <div class="user-info-footer">
          <button class="button-danger button" onclick="window.location.href='../logout.php'">Logout</button>
        </div>
      </div>
    </div>

  </main>
</body>

</html>