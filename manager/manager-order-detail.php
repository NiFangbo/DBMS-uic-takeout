<?php
session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'manager') {
  header('Location: ../login.php');
  exit();
}

include '../db.php';

$oid = $_GET['order_id'] ?? '';

// order information
$sql = "SELECT * FROM `order` 
JOIN `customer` USING(`customer_id`)
LEFT OUTER JOIN `deliveryman` USING(`deliveryman_id`)
JOIN 
(SELECT `order_id`, ROUND(SUM(`single_price` * `quantity`), 2) AS `order_price` FROM `order` 
JOIN `transaction` USING(`order_id`) 
JOIN `product` USING(`product_id`) 
GROUP BY `order_id`) AS `temp`
ON `order`.order_id = `temp`.order_id
WHERE `order`.order_id = $oid";
$order_result = $conn->query($sql);
$order = $order_result->fetch_assoc();

$customer_id = $order["customer_id"];
$customer_name = $order["customer_name"];
$order_time = $order["order_time"];
$address = $order["order_address"];
$deliveryman_id = $order["deliveryman_id"];
$deliveryman_name = $order["deliveryman_name"];
$check_time = $order["check_time"];
$status = $order["delivery_status"];
$complete_time = $order["complete_time"];
$order_price = $order["order_price"];
if ( $order_price <= 10.00 ) $delivery_fee = 1.00;
else $delivery_fee = sprintf("%.2f", 1 + ($order_price - 10) / 10);

if ($_SERVER["REQUEST_METHOD"] == "POST"){
  // complete order
  if (isset($_POST["complete"])){
    if ( $status == 'Delivering' ){
      // update time
      $sql = "UPDATE `order` 
      SET `delivery_status` = 'Completed', 
      `complete_time` = (SELECT NOW() AS CurrentDateTime) 
      WHERE `order_id` = $oid";
      mysqli_query($conn, $sql);

      // pay delivery fee
      $sql = "UPDATE `deliveryman`
      SET `balance` = `balance` + $delivery_fee
      WHERE `deliveryman_id` = $deliveryman_id";
      mysqli_query($conn, $sql);

      $_SESSION['normal_message'] = "Successfully complete the order!";
    }else{
      $_SESSION['error_message'] = "You cannot complete an order which is $status!";
    }
  }
  
  // cancel order
  if (isset($_POST["cancel"])){
    if ( $status != 'Cancelled' ){
      $sql = "UPDATE `order` 
      SET `delivery_status` = 'Cancelled', 
      `complete_time` = NULL
      WHERE `order_id` = $oid";
      mysqli_query($conn, $sql);
      
      // return delivery fee
      if ( $status == 'Completed' ){
        $sql = "UPDATE `deliveryman`
        SET `balance` = `balance` - $delivery_fee
        WHERE `deliveryman_id` = $deliveryman_id";
        mysqli_query($conn, $sql);
      }

      // return payment
      $sql = "UPDATE `customer`
      SET `balance` = `balance` + $order_price + $delivery_fee
      WHERE `customer_id` = $customer_id";
      mysqli_query($conn, $sql);

      $_SESSION['normal_message'] = "The order is cancelled!";
    }else{
      $_SESSION['error_message'] = "You cannot cancel an order which is $status!";
    }
  }

  // redirect
  header("Location:manager-order-detail.php?order_id=$oid");
  exit();
}

?>

<!DOCTYPE html>

<html>

<head>
  <link rel="stylesheet" href="../style/main.css">
  <link rel="stylesheet" href="../style/product.css">
  <link rel="stylesheet" href="../style/cart.css">
  <link rel="stylesheet" href="../style/manage.css">
  <title>Order #1342 - Campus Takeout</title>
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
    <h2 class="title">Order #<?php echo $oid; ?></h2>

    <hr />

    <div class="product-list-container">

      <?php
      $sql = "SELECT * FROM `transaction` 
      JOIN `product` USING(product_id) 
      JOIN `product_category` USING(product_id) 
      JOIN `category` USING(category_id) 
      WHERE `order_id` = $oid";     
      $product_result = $conn->query($sql);

      foreach ($product_result as $product) {
        $image = $product["image"];
        $pid = $product["product_id"];
        $pname = $product["product_name"];
        $category = $product["category_name"];
        $description = $product["description"];
        $number = $product["quantity"];
        $single_price = $product["single_price"];

        echo"
        <div class='product-card' id='product-card-1919810'>
          <a href='manager-product-detail.php?product_id=$pid'>
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

    <div class="order-box">
      <h2 class="small-title">
        Order Status
      </h2>
      <div class="lines-content order-box-content">
        
      <?php
        echo"
        <p>
          <label>Status: </label>
          <span>$status</span>
        </p>
        <p>
          <label>Customer:</label>
          <a href='manager-customer-detail.php?customer_id=$customer_id'>$customer_name (#$customer_id)</a>
        </p>
        <p>
          <label>Delivery Person:</label>";
          if ( $deliveryman_id != NULL )
            echo "<a href='manager-delivery-detail.php?deliveryman_id=$deliveryman_id'>$deliveryman_name (#$deliveryman_id)</a>";
        echo"
        </p>
        <p>
          <label>Order Time: </label>
          <span>$order_time</span>
        </p>
        <p>
          <label>Check Time: </label>
          <span>$check_time</span>
        </p>
        <p>
          <label>Complete Time: </label>
          <span>$complete_time</span>
        </p>
        <p>
          <label>Items:</label>
          <span class='price'>$".$order_price."</span>
        </p>
        <p>
          <label>Delivery Fee:</label>
          <span class='price'>$".$delivery_fee."</span>
        </p>
        <p>
          <label>Pay Amount:</label>
          <span class='price'>$".$order_price + $delivery_fee."</span>
        </p>
        <p>
          <label>Address: </label>
          <span>$address</span>
        </p>
        ";
      ?>
        
      <form action="manager-order-detail.php?order_id=<?php echo $oid; ?>" method="post">
      <p style="text-align: right;">
      <input class="button" type="submit" name="complete" value="Complete Order" onclick="return confirm('Are you sure to complete this order?')">
      <input class="button-danger button" type="submit" name="cancel" value="Cancel Order" onclick="return confirm('Are you sure to cancel this order?')">
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