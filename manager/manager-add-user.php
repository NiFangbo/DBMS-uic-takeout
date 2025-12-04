<?php
session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'manager') {
  header('Location: ../login.php');
  exit();
}

$bg = microtime(true);
include '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST"){
  if (isset($_POST["add_customer"])){
    $username = $_POST["username"];
    $username = $conn->real_escape_string($username);
    $nickname = $_POST["nickname"];
    $nickname = $conn->real_escape_string($nickname);
    $phone = $_POST["phone"];
    $phone = $conn->real_escape_string($phone);
    $identity = $_POST["identity"];
    if ($identity == "Unknown") $identity = NULL;
    $balance = $_POST["balance"];
    $pw = $_POST["pw"];
    $pw = $conn->real_escape_string($pw);
    $repw = $_POST["repw"];
    $repw = $conn->real_escape_string($repw);

    if ($pw != $repw){
      $_SESSION['error_message'] = "The passwords entered twice are different. Please reset.";
    }else{
      // check if username exists
      $sql = "SELECT * FROM `user` WHERE `username` = '$username'";
      $result = $conn->query($sql);

      if ($result->num_rows > 0) {
        $_SESSION['error_message'] = "Username already exists.";
      } else  {
        $sql = "INSERT INTO `user`(`user_type`, `username`, `password`)
        VALUES('customer', '$username', '$pw')";
        $conn->query($sql);
        $cid = $conn->insert_id;

        $sql = "UPDATE `customer` 
        SET `nickname` = '$nickname', `phone` = '$phone', `identity` = '$identity', `balance` = $balance
        WHERE `customer_id` = $cid";
        $conn->query($sql);
        $ed = microtime(true);
        $insert_time = sprintf("%.3lf",$ed - $bg);
        
        header("Location:manager-customer-detail.php?customer_id=$cid&insert_time=$insert_time");
        exit();
      }
    }
  }
  
  if (isset($_POST["add_deliveryman"])){
    $username = $_POST["username"];
    $username = $conn->real_escape_string($username);
    $phone = $_POST["phone"];
    $phone = $conn->real_escape_string($phone);
    $identity = $_POST["identity"];
    $balance = $_POST["balance"];
    $pw = $_POST["pw"];
    $pw = $conn->real_escape_string($pw);
    $repw = $_POST["repw"];
    $repw = $conn->real_escape_string($repw);

    if ($pw != $repw){
      $_SESSION['error_message'] = "The passwords entered twice are different. Please reset.";
    }else{
      // check if username exists
      $sql = "SELECT * FROM `user` WHERE `username` = '$username'";
      $result = $conn->query($sql);

      if ($result->num_rows > 0) {
        $_SESSION['error_message'] = "Username already exists.";
      } else  {
        $sql = "INSERT INTO `user`(`user_type`, `username`, `password`)
        VALUES('deliveryman', '$username', '$pw')";
        $conn->query($sql);
        $did = $conn->insert_id;

        $sql = "UPDATE `deliveryman` 
        SET `phone` = '$phone', `identity` = '$identity', `balance` = $balance
        WHERE `deliveryman_id` = $did";
        $conn->query($sql);
        $ed = microtime(true);
        $insert_time = sprintf("%.3lf",$ed - $bg);

        
        header("Location:manager-delivery-detail.php?deliveryman_id=$did&insert_time=$insert_time");
        exit();
      }
    }
  }
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
  <title>Add User - Campus Takeout</title>
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
    <h2 class="title">Add User</h2>

    <hr />
    
    <form action="manager-add-user.php" method="post">
    <div class="lines-content manage-content">
      <h3 class="small-title">Add Customer</h3>
      <p>
        <label>Username:</label>
        <input id="input-c-username" class="input-box" type="text" name="username" required>
      </p>
      <p>
        <label>Nickname:</label>
        <input id="input-c-nickname" class="input-box" type="text" name="nickname">
      </p>
      <p>
        <label>Phone Number:</label>
        <input id="input-c-phone" class="input-box" type="tel" name="phone">
      </p>
      <p>
        <label>Identity:</label>
        <select id="input-c-identity" class="input-box" name="identity">
          <option value="Unknown">Unknown</option>
          <option value="Student">Student</option>
          <option value="Staff">Staff</option>
        </select>
      </p>
      <p>
        <label>Balance:</label>
        <input id="input-c-balance-delta" class="input-box" type="number" step="0.01" name="balance" required/>
      </p>
      <p>
        <label>Password:</label>
        <input id="input-c-password" class="input-box" type="text" name="pw" required/>
      </p>
      <p>
        <label>Retype Password:</label>
        <input id="input-c-password-retype" class="input-box" type="text" name="repw" required/>
      </p>
      <p style="text-align: right;">
        <input class="button" type="submit" value="Add Customer" name="add_customer">
      </p>
    </div>
    </form>
    
    <hr />

    <form action="manager-add-user.php" method="post">
    <div class="lines-content manage-content">
      <h3 class="small-title">Add Delivery Person</h3>
      <p>
        <label>Username:</label>
        <input id="input-d-username" class="input-box" type="text" name="username" required>
      </p>
      <p>
        <label>Phone Number:</label>
        <input id="input-d-phone" class="input-box" type="tel" name="phone" required>
      </p>
      <p>
        <label>Identity:</label>
        <select id="input-d-identity" class="input-box" name="identity">
          <option value="part-time-student">Part-Time Student</option>
          <option value="part-time-staff">Part-Time Staff</option>
          <option value="delivery-person" selected>Delivery Person</option>
        </select>
      </p>
      <p>
        <label>Balance:</label>
        <input id="input-d-balance-delta" class="input-box" type="number" step="0.01" name="balance" required/>
      </p>
      <p>
        <label>Password:</label>
        <input id="input-d-password" class="input-box" type="text" name="pw" required/>
      </p>
      <p>
        <label>Retype Password:</label>
        <input id="input-d-password-retype" class="input-box" type="text" name="repw" required/>
      </p>
      <p style="text-align: right;">
        <input class="button" type="submit" value="Add Delivery Person" name="add_deliveryman">
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