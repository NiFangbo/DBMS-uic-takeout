<?php
session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'customer') {
  header('Location: ../login.php');
  exit();
}

include '../db.php';

// customer information
$cid = $_SESSION["user_id"];
$sql = "SELECT * FROM `customer` WHERE `customer_id` = $cid";
$result = mysqli_query($conn, $sql);
$customer = mysqli_fetch_assoc($result);

// category type
$sql = "SELECT * FROM `category`";
$category_result = mysqli_query($conn, $sql);

// cart information
$sql = "SELECT *
FROM `cart` 
JOIN `product` USING(`product_id`)
JOIN `product_category` USING(`product_id`)
JOIN `category` USING(`category_id`)
WHERE `product`.`status` = 'Available' AND `customer_id` = $cid";
$cart_result = mysqli_query($conn, $sql);

if ($_SERVER["REQUEST_METHOD"] == "POST"){
  if ( isset($_POST["update_info"]) ){
    $nickname = $_POST["nickname"];
    $nickname = $conn->real_escape_string($nickname);

    $sql = "UPDATE `customer` SET `nickname` = '$nickname' WHERE `customer_id` = $cid";

    if ($conn->query($sql) === TRUE) {
      $_SESSION['normal_message'] = "Successfully update the nickname!";
    } else {
      $_SESSION['error_message'] = "Error: " . $sql . "<br>" . $conn->error;
    }
  }

  if (isset($_POST["reset_password"])) {
      $oldpassword = $_POST['curpw'];
      $oldpassword = $conn->real_escape_string($oldpassword);
      $newpassword = $_POST['newpw'];
      $newpassword = $conn->real_escape_string($newpassword);
      $repassword = $_POST['repw'];
      $repassword = $conn->real_escape_string($repassword);

      if ($newpassword != $repassword){
        $_SESSION['error_message'] = "The passwords entered twice are different.";
      }else{
        $sql = "SELECT * FROM `user` WHERE `user_id` = $cid";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $password = $row["password"];
        $password = $conn->real_escape_string($password);

        if ($oldpassword != $password) {
            $_SESSION['error_message'] = "Current password is incorrect.";
        } else {
            // change the password
            $sql = "UPDATE `user` SET `password` = $newpassword WHERE `user_id` = $cid";
            $conn->query($sql);
              session_destroy();
              header("Location: ../login.php?reset=success");
              exit();
        }
      }
  }

  if (isset($_POST["reset_phone"])) {
    $phone = $_POST['phone'];
    $phone = $conn->real_escape_string($phone);
    $sql = "UPDATE `customer` SET `phone` = '$phone' WHERE `customer_id` = $cid";
    $conn->query($sql);
    $_SESSION['normal_message'] = "Successfully update the phone!";
  }

  header("Location: customer-home.php");
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

<body class="uic-background">
  <header>
    <ul class="header-left">
      <li>
        <a href="../products.php?category=All">
          <p class="header-title">Campus Takeout</p>
        </a>
      </li>
    </ul>
    <ul class="header-right">
      <li>
        <a class="header-account" href="customer-home.php"><?php if ( $customer["nickname"] != NULL ) echo $customer["nickname"]; else echo $_SESSION["username"]; ?></a>
      </li>
      <li>
        <a href="cart.php">Cart (<?php echo $cart_result->num_rows; ?>)</a>
      </li>
      <li>
        <a href="customer-orders.php">Orders</a>
      </li>
    </ul>
  </header>
  <main class="main-body">
    <div class="body-card">
      <div class="user-body">
        <h2 class="bold title">
          Account Information
        </h2>
        
        <form action="customer-home.php" method="post">
        <div class="lines-content user-info-block">
          <p>
            <label>Nickname:</label>
            <input id="user-nickname-input" class="input-box" type="text" value="<?php echo $customer["nickname"]; ?>" maxlength="32" name="nickname">
            <input class="button" type="submit" value="Update" name="update_info">
          </p>
          <p>
            <label>Username:</label>
            <span><?php echo $_SESSION["username"]; ?></span>
          </p>
          <p>
            <label>Identity:</label>
            <span><?php echo $customer["identity"]; ?></span>
          </p>
          <p>
            <label>Phone Number:</label>
            <span><?php echo $customer["phone"]; ?></span>
          </p>
          <p>
            <label>Balance:</label>
            <span class="price">$<?php echo $customer["balance"]; ?></span>
          </p>
        </div>
        </form>
        
        <hr />

        <form action="customer-home.php" method="post">
        <div class="lines-content user-info-block">
          <h3 class="small-title">
            Change Password
          </h3>
          <p>
            <label>Current Password:</label>
            <input class="input-box" type="password" value="" name="curpw" required>
          </p>
          <p>
            <label>New Password:</label>
            <input class="input-box" type="password" value="" name="newpw" required>
          </p>
          <p>
            <label>Repeat New Password:</label>
            <input class="input-box" type="password" value="" name="repw" required>
          </p>
          <p>
          <input class="button" type="submit" value="Change Password" name="reset_password">
          </p>
        </div>
        </form>

        <hr />

        <form action="customer-home.php" method="post">
        <div class="lines-content user-info-block">
          <h3 class="small-title">
            Change Phone Number
          </h3>
          <p>
            <label>New Phone Number:</label>
            <input class="input-box" type="text" value="" name="phone">
          </p>
          <p>
            <input class="button" type="submit" value="Change Phone Number" name="reset_phone">
          </p>
        </div>
        </form>

        <hr />

        <div class="user-info-footer">
          <button class="button-danger button" onclick="window.location.assign('../logout.php')">Logout</button>
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