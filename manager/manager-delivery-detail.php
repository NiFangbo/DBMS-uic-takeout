<?php
session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'manager') {
  header('Location: ../login.php');
  exit();
}

include '../db.php';

$did = $_GET['deliveryman_id'] ?? '';

// deliveryman information
$sql = "SELECT * FROM `deliveryman` WHERE `deliveryman_id` = $did";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_array($result);

$username = $row["deliveryman_name"];
$phone = $row["phone"];
$identity = $row["identity"];
$balance = $row["balance"];

if ($_SERVER["REQUEST_METHOD"] == "POST"){
  // update information
  if (isset($_POST["update_info"])){
    $phone = $_POST["phone"];
    $phone = $conn->real_escape_string($phone);
    $identity = $_POST["identity"];

    $sql = "UPDATE `deliveryman` SET `phone` = '$phone', `identity` = '$identity' WHERE `deliveryman_id` = $did";
    mysqli_query($conn, $sql);

    $_SESSION['normal_message'] = "Successfully update the information!";
  }

    // delete account
    if (isset($_POST["delete_account"])){
      $sql = "DELETE FROM `user` WHERE `user_id` = $did";
      mysqli_query($conn, $sql);
      
      header("Location: manager-delivery.php");
      exit();
    }

  // change balance
  if (isset($_POST["change_balance"])){
    $amount = $_POST["amount"];

    $sql = "UPDATE `deliveryman` SET `balance` = $balance + $amount WHERE `deliveryman_id` = $did";
    mysqli_query($conn, $sql);

    $_SESSION['normal_message'] = "Successfully change the balance!";
  }

  // reset password
  if (isset($_POST["reset_pw"])){
    $password = $_POST["password"];
    $password = $conn->real_escape_string($password);
    $repassword = $_POST["repassword"];
    $repassword = $conn->real_escape_string($repassword);

    if ($password == $repassword ){
      $sql = "UPDATE `user` SET `password` = '$password' WHERE `user_id` = $did";
      mysqli_query($conn, $sql);
      $_SESSION['normal_message'] = "Successfully reset password!";
    } else {
      $_SESSION['error_message'] = "The passwords entered twice are different.";
    }
  }

  // redirect
  header("Location:manager-delivery-detail.php?deliveryman_id=$did");
  exit();
}
?>

<!DOCTYPE html>

<html>

<head>
  <link rel="stylesheet" href="../style/main.css">
  <link rel="stylesheet" href="../style/product.css">
  <link rel="stylesheet" href="../style/cart.css">
  <link rel="stylesheet" href="../style/filter.css">
  <link rel="stylesheet" href="../style/manage.css">
  <title>Delivery Person #1 - Campus Takeout</title>
</head>

<body class="manager-page">
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
        <a class="header-account" href="manager-home.php"><?php echo $_SESSION["username"]; ?> (Manager)</a>
      </li>
    </ul>
  </header>

  <div class="side-bar">
    <div class="side-bar-content">
      <h3 class="side-bar-title">Query</h3>
      <a class="side-bar-item" href="manager-products.php">
        Products
      </a>
      <a class="side-bar-item" href="manager-orders.php">
        Orders
      </a>
      <a class="side-bar-item" href="manager-customers.php">
        Customers
      </a>
      <a class="side-bar-item" href="manager-delivery.php">
        Delivery Persons
      </a>
      <a class="side-bar-item" href="manager-comments.php">
        Comments
      </a>
      <h3 class="side-bar-title">Add</h3>
      <a class="side-bar-item" href="manager-add-product.php">
        Add Product
      </a>
      <a class="side-bar-item" href="manager-add-user.php">
        Add User
      </a>
    </div>
  </div>

  <div class="main-body-with-side-bar">
    <h2 class="title">Delivery Person #<?php echo $did; ?></h2>
    <?php
      if (isset($_GET["insert_time"])){
        echo "<p>new deliveryman inserted in ".$_GET["insert_time"]." seconds.</p>";
      }
    ?>

    <hr />

    <form action="manager-delivery-detail.php?deliveryman_id=<?php echo $did; ?>" method="post">
    <div class="lines-content manage-content">
      <h class="small-title">Account Info</h>
      <p>
        <label>Username:</label>
        <span><?php echo $username; ?></span>
      </p>
      <p>
        <label>Phone Number:</label>
        <input id="input-phone" class="input-box" type="tel" value="<?php echo $phone; ?>" name="phone">
      </p>
      <p>
        <label>Identity: </label>
        <select class="input-box" id="filter-status" name="identity">
          <option value="Part-Time Student">Part-Time Student</option>
          <option value="Part-Time Staff" <?php if ($identity == "Part-Time Staff") echo "selected"; ?>>Part-Time Staff</option>
          <option value="Delivery Person" <?php if ($identity == "Delivery Person") echo "selected"; ?>>Delivery Person</option>
        </select>
      </p>
      <p>
        <label>Balance:</label>
        <span><?php echo $balance; ?></span>
      </p>
      <p style="text-align: right;">
        <input class="button-danger button" type="submit" value="Delete Account" name="delete_account" onclick="return confirm('Are you sure to delete this account? If you perform this action, all the information related with the account will be deleted!')">
        <input class="button" type="submit" value="Update Account Info" name="update_info">
      </p>
    </div>
    </form>

    <hr />

    <form action="manager-delivery-detail.php?deliveryman_id=<?php echo $did; ?>" method="post">
    <div class="lines-content manage-content">
      <h class="small-title">Top Up / Deduct</h>
      <p>
        <label>Amount:</label>
        <input id="input-balance-delta" class="input-box" type="number" name="amount" required/>
      </p>
      <p style="text-align: right;">
        <input class="button" type="submit" value="Top Up / Deduct" name="change_balance">
      </p>
    </div>
    </form>

    <hr />

    <form action="manager-delivery-detail.php?deliveryman_id=<?php echo $did; ?>" method="post">
    <div class="lines-content manage-content">
      <h class="small-title">Reset Password</h>
      <p>
        <label>Password:</label>
        <input id="input-password" class="input-box" type="text" name="password"/>
      </p>
      <p>
        <label>Retype Password:</label>
        <input id="input-password-retype" class="input-box" type="text" name="repassword" required/>
      </p>
      <p style="text-align: right;">
        <input class="button" type="submit" value="Reset password" name="reset_pw" required/>
      </p>
    </div>
    </form>

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