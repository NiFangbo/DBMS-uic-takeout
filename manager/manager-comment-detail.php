<?php
session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'manager') {
  header('Location: ../login.php');
  exit();
}

include '../db.php';

// comment information
$comment_id = $_GET['comment_id'] ?? '';
$sql = "SELECT * FROM `comment`
JOIN `comment_product` USING(`comment_id`)
JOIN `order` USING(`order_id`)
JOIN `customer` USING(`customer_id`)
JOIN `deliveryman` USING(`deliveryman_id`)
WHERE `comment_id` = $comment_id";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_array($result);

$order_id = $row["order_id"];
$customer_id = $row["customer_id"];
$customer_name = $row["customer_name"];
$deliveryman_id = $row["deliveryman_id"];
$deliveryman_name = $row["deliveryman_name"];
$status = $row["status"];
$star_level = $row["star_level"];
$content = $row["content"];

// product information in comment
$sql = "SELECT * FROM `comment_product`
JOIN `product` USING(`product_id`)
WHERE `comment_id` = $comment_id";
$product_result = mysqli_query($conn, $sql);

if ($_SERVER["REQUEST_METHOD"] == "POST"){
  // update status
  if ( isset($_POST["show"]) ) $status="Shown";
  if ( isset($_POST["hide"]) ) $status= "Hidden";
  $sql = "UPDATE `comment` SET `status` = '$status' WHERE `comment_id` = $comment_id";
  mysqli_query($conn, $sql);

  if ( isset($_POST["delete_comment"]) ){
    $sql = "DELETE FROM `comment` WHERE `comment_id` = $comment_id";
    mysqli_query($conn, $sql);
    header("Location: manager-comments.php");
    exit();
  }

  $_SESSION['normal_message'] = "Successfully update the status!";
}

?>

<!DOCTYPE html>

<html>

<head>
  <link rel="stylesheet" href="../style/main.css">
  <link rel="stylesheet" href="../style/product.css">
  <link rel="stylesheet" href="../style/cart.css">
  <link rel="stylesheet" href="../style/manage.css">
  <title>Comment #237 - Campus Takeout</title>
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
    <h2 class="title">Comment #<?php echo $comment_id; ?></h2>

    <hr />

    <div class="order-box">
      <h2 class="small-title">
        Comment Information
      </h2>

      <div class='lines-content order-box-content'>
        
        <?php
        echo"
        <p>
          <label>Order ID:</label>
          <a href='manager-order-detail.php?order_id=$order_id'>#$order_id</a>
        </p>
        <p>
          <label>Comment Product:</label>";

        foreach ($product_result as $product) {
          $pid = $product["product_id"];
          $pname = $product["product_name"];
          echo "<a href='manager-product-detail.php?product_id=$pid'>$pname (#$pid)  </a>";
        }
        
        echo"
        </p>
        <p>
          <label>Customer:</label>
          <a href='manager-customer-detail.php?customer_id=$customer_id'>$customer_name (#$customer_id)</a>
        </p>
        <p>
          <label>Delivery Person:</label>
          <a href='manager-delivery-detail.php?deliveryman_id=$deliveryman_id'>$deliveryman_name (#$deliveryman_id)</a>
        </p>
        <p>
          <label>Comment Status:</label>
          <span>$status</span>
        </p>
        <p>
          <label>Star Rating:</label>
          <span>$star_level Star</span>
        </p>
        <hr />
        <p>
          $content
        </p>
        ";
        ?>

        <hr />
        <form action="manager-comment-detail.php?comment_id=<?php echo $comment_id ?>" method="post">
        <p style="text-align: right;">
          <input class="button" type="submit" value="Show Comment" name="show">
          <input class="button" type="submit" value="Hide Comment" name="hide">
          <input class="button-danger button" type="submit" value="Delete Comment" name="delete_comment" onclick="return confirm('Are you sure to delete this comment?')">
        </p>
        </form>
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