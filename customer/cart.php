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

// order price
$sql = "SELECT ROUND(SUM(`product_price` * `quantity`), 2) AS `order_price`
FROM `cart` 
JOIN `product` USING(`product_id`)
JOIN `product_category` USING(`product_id`)
JOIN `category` USING(`category_id`)
WHERE `customer_id` = $cid";
$order_price = mysqli_fetch_assoc(mysqli_query($conn, $sql))["order_price"];
if ( $order_price == NULL ) $order_price = $delivery_fee = 0.00;
else if ( $order_price <= 10.00 ) $delivery_fee = 1.00;
else $delivery_fee = sprintf("%.2f", 1 + ($order_price - 10) / 10);

if ($_SERVER["REQUEST_METHOD"] == "POST"){
  $address = $_POST["address"];
  $address = $conn->real_escape_string($address);
  if ( $order_price == 0 ){
    $_SESSION["error_message"] = "You cannot place an empty order!";
  }else if ( $customer["balance"] - $order_price - $delivery_fee < 0 ){
    $_SESSION["error_message"] = "You balance is not enough to pay this order!";
  }else{
    // cusotmer pay for the order
    $sql = "UPDATE `customer` 
    SET `balance` = `balance` - $order_price - $delivery_fee
    WHERE `customer_id` = $cid";
    mysqli_query($conn, $sql);

    // update order
    $sql = "INSERT INTO `order`(`customer_id`, `order_time`, `order_address`, `delivery_status`) VALUES
    ($cid, (SELECT NOW() AS CurrentDateTime), '$address', 'Pending')";
    mysqli_query($conn, $sql);

    $oid = $conn->insert_id;
    
    // update transaction
    $sql = "INSERT INTO `transaction` VALUES";
    $cnt = 1;
    foreach ( $cart_result as $product ) {
      $pid = $product["product_id"];
      $single_price = $product["product_price"];
      $number = $product["quantity"];
      if ( $cnt > 1 ) $sql .= ",";
      $sql .= "($oid, $pid, $single_price, $number)";
      $cnt++;
    }
    mysqli_query($conn, $sql);

    // empty the cart
    $sql = "DELETE FROM `cart` WHERE `customer_id` = $cid";
    mysqli_query($conn, $sql);

    header("Location: customer-orders.php");
    exit();
  }
  header("Location: cart.php");
  exit();
}

?>

<!DOCTYPE html>

<html>

<head>
  <link rel="stylesheet" href="../style/main.css">
  <link rel="stylesheet" href="../style/product.css">
  <link rel="stylesheet" href="../style/cart.css">
  <title>Cart - Campus Takeout</title>
</head>

<body>
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
  <div class="side-bar">
    <div class="side-bar-content">
      <h3 class="side-bar-title">Categories</h3>
      <a class="side-bar-item" href="../products.php?category=All">
        All
      </a>
      <?php
      foreach ($category_result as $category) {
        $category_name = $category["category_name"];
        echo"
      <a class='side-bar-item' href='../products.php?category=$category_name'>
        $category_name
      </a>";
      }
      ?>
    </div>
  </div>
  <main class="main-body-with-side-bar">
    <h2 class="title">Your Cart</h2>

    <hr />

    <div class="product-list-container">

      <?php
      foreach( $cart_result as $product ) {
        $image = $product["image"];
        $pid = $product["product_id"];
        $pname = $product["product_name"];
        $category = $product["category_name"];
        $description = $product["description"];
        $number = $product["quantity"];
        $single_price = $product["product_price"];


        echo"
        <div class='product-card' id='product-card-114514'>
          <a href='../product-detail.php?product_id=$pid'>
            <img class='product-card-img' src='data:image/png;base64,".base64_encode($image)."' alt='$pname' />
          </a>
          <div class='product-card-info'>
            <h2 class='product-card-title'>
              $pname
            </h2>
            <a class='product-card-category'>
              $category
            </a>
            <p class='product-card-desc'>
              $description
            </p>
            <div class='product-card-footer'>
              <p class='product-card-price'>
                <span style='font-size: 10pt;'>".$number."x $".$single_price."=</span>
                <span style='font-size: 14pt;'>$".$number*$single_price."</span>
              </p>
              <span>x".$number."</span>
            </div>
          </div>
        </div>
        ";
      }
      ?>

    </div>

    <hr />
    
    <form action="cart.php" method="post" onsubmit="confirm('Are you sure to pay and place this order?')">
    <div class="order-box">
      <h2 class="small-title">
        Place Order
      </h2>
      <div class="lines-content order-box-content">
        <p>
          <label>Items:</label>
          <span class="price">$<?php echo $order_price; ?></span>
        </p>
        <p>
          <label>Delivery Fee:</label>
          <span class="price">$<?php echo $delivery_fee; ?></span>
        </p>
        <p>
          <label>Total:</label>
          <span class="price">$<?php echo $order_price + $delivery_fee; ?></span>
        </p>
        <p>
          <label>Address:</label>
          <input class="order-address-box input-box" type="text" name="address" required>
        </p>
        <p>
          <button class="button" type="submit">Place Order</button>
        </p>
      </div>
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

</hr>