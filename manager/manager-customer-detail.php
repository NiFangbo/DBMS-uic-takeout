<?php
session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'manager') {
  header('Location: ../login.php');
  exit();
}

include '../db.php';

$cid = $_GET['customer_id'] ?? '';

// customer information
$sql = "SELECT * FROM `customer` WHERE `customer_id` = $cid";
$result = mysqli_query($conn, $sql);
$row = $result->fetch_assoc();
$username = $row["customer_name"];
$nickname = $row["nickname"];
$phone = $row["phone"];
$identity = $row["identity"];
$balance = $row["balance"];

if ($_SERVER["REQUEST_METHOD"] == "POST"){
  // update information
  if (isset($_POST["update_info"])){
    $nickname = $_POST["nickname"];
    $nickname = $conn->real_escape_string($nickname);
    $phone = $_POST["phone"];
    $phone = $conn->real_escape_string($phone);
    $identity = $_POST["identity"];
    if ( $identity == 'Unknown' ) $identity = NULL;

    $sql = "UPDATE `customer` SET `nickname` = '$nickname', `phone` = '$phone', `identity` = '$identity' WHERE `customer_id` = $cid";
    mysqli_query($conn, $sql);

    $_SESSION['normal_message'] = "Successfully update the information!";
  }

  // delete account
  if (isset($_POST["delete_account"])){
    $sql = "DELETE FROM `user` WHERE `user_id` = $cid";
    mysqli_query($conn, $sql);
    
    header("Location: manager-customers.php");
    exit();
  }

  // change balance
  if (isset( $_POST["change_balance"])){
    $amount = $_POST["amount"];

    $sql = "UPDATE `customer` SET `balance` = $balance + $amount WHERE `customer_id` = $cid";
    mysqli_query($conn, $sql);

    $_SESSION['normal_message'] = "Successfully change the balance!";
  }

  // reset password
  if (isset( $_POST["reset_pw"])){
    $password = $_POST["password"];
    $password = $conn->real_escape_string($password);
    $repassword = $_POST["repassword"];
    $repassword = $conn->real_escape_string($repassword);
    
    if ($password == $repassword){
      $sql = "UPDATE `user` SET `password` = '$password' WHERE `user_id` = $cid";
      mysqli_query($conn, $sql);
      $_SESSION['normal_message'] = "Successfully reset password!";
    }else{
      $_SESSION['error_message'] = "The passwords entered twice are different. Please reset.";
    }
  }
  
  // redirect
  header("Location:manager-customer-detail.php?customer_id=$cid");
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
  <title>Customer #1037 - Campus Takeout</title>
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

  <main class="main-body-with-side-bar">
    <h2 class="title">Customer #<?php echo $cid; ?></h2>
    <?php
      if (isset($_GET["insert_time"])){
        echo "<p>new customer inserted in ".$_GET["insert_time"]." seconds.</p>";
      }
    ?>

    <hr />

    <form action="manager-customer-detail.php?customer_id=<?php echo $cid; ?>" method="post">
    <div class="lines-content manage-content">
      <h class="small-title">Account Info</h>
      <p>
        <label>Username:</label>
        <span><?php echo $username ?></span>
      </p>
      <p>
        <label>Nickname:</label>
        <input id="input-nickname" class="input-box" type="text" value="<?php echo $nickname ?>" name="nickname">
      </p>
      <p>
        <label>Phone Number:</label>
        <input id="input-phone" class="input-box" type="tel" value="<?php echo $phone ?>" name="phone">
      </p>
      <p>
        <label>Identity: </label>
        <select class="input-box" id="filter-status" name="identity">
          <option value="Unknown">Unknown</option>
          <option value="Student" <?php if ($identity == 'Student') echo 'selected'; ?>>Student</option>
          <option value="Staff" <?php if ($identity == 'Staff') echo 'selected'; ?>>Staff</option>
        </select>
      </p>
      <p>
        <label>Balance:</label>
        <span><?php echo $balance ?></span>
      </p>
      <p style="text-align: right;">
        <input class="button-danger button" type="submit" value="Delete Account" name="delete_account" onclick="return confirm('Are you sure to delete this account? If you perform this action, all the information related with the account will be deleted!')">
        <input class="button" type="submit" value="Update Account Info" name="update_info">
      </p>
    </div>
    </form>

    <hr />

    <form action="manager-customer-detail.php?customer_id=<?php echo $cid; ?>", method="post">
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

    <form action="manager-customer-detail.php?customer_id=<?php echo $cid; ?>", method="post">
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