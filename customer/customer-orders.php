<?php
session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'customer') {
  header('Location: ../login.php');
  exit();
}

include '../db.php';

$cid = $_SESSION["user_id"];
$sql = "SELECT * FROM `customer` WHERE `customer_id` = $cid";
$result = mysqli_query($conn, $sql);
$customer = mysqli_fetch_assoc($result);

// order information
$sql = "SELECT * FROM `order` JOIN
(SELECT `order_id`, ROUND(SUM(`single_price` * `quantity`), 2) AS `order_price` FROM `order` 
JOIN `transaction` USING(`order_id`) 
JOIN `product` USING(`product_id`) 
GROUP BY `order_id`) AS `temp`
ON `order`.order_id = `temp`.order_id
WHERE `customer_id` = $cid
ORDER BY `order_time` DESC";
$result = mysqli_query($conn, $sql);

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
    $sql = "SELECT * FROM `order` JOIN
  (SELECT `order_id`, ROUND(SUM(`single_price` * `quantity`), 2) AS `order_price` FROM `order` 
  JOIN `transaction` USING(`order_id`) 
  JOIN `product` USING(`product_id`) 
  GROUP BY `order_id`) AS `temp`
  ON `order`.order_id = `temp`.order_id
  WHERE `customer_id` = $cid AND";

  if ( $_POST["oid"] != "" ){
    $oid = $_POST["oid"];
    $sql .= " `order`.`order_id` = $oid AND";
  }

  if ( $_POST["status"] != "All" ){
    $status = $_POST["status"];
    $sql .= " `delivery_status` = '$status' AND";
  }

  if ( $_POST["start_time"] != "" && $_POST["end_time"] != "" ){
    $start_time = $_POST["start_time"]." 00:00:00";
    $end_time = $_POST["end_time"]." 23:59:59";
    $sql .= " `order_time` >= '$start_time' && `order_time` <= '$end_time' AND";
  }

  $sql .= " TRUE";
  
  if ( $_POST["sorting"] == "Newest" ) $sql .= " ORDER BY `order_time` DESC";
  else $sql .= " ORDER BY `order_time` ASC";
  $result = mysqli_query($conn, $sql);
}

?>

<!DOCTYPE html>

<html>

<head>
  <link rel="stylesheet" href="../style/main.css">
  <link rel="stylesheet" href="../style/order-list.css">
  <link rel="stylesheet" href="../style/filter.css">
  <title>Orders - Campus Takeout</title>
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
    <h2 class="title">Your Orders</h2>
    
    <hr />

    <form action="customer-orders.php" method="post">
    <div class="filter-container">
      <div class="filter-list">
        <div class="filter-list-item">
          <label>ID: </label>
          <input id="filter-id" style="width: 6em;" class="input-box" type="number" min="1" placeholder="Order ID" name="oid"/>
        </div>
        <div class="filter-list-item">
          <label>Order Status: </label>
          <select class="input-box" id="filter-status" name="status">
            <option value="All">All</option>
            <option value="Pending">Pending</option>
            <option value="Delivering">Delivering</option>
            <option value="Completed">Completed</option>
            <option value="Cancelled">Cancelled</option>
          </select>
        </div>
      </div>
      <div class="filter-list">
        <div class="filter-list-item">
          <label>Order Time: </label>
          <input id="filter-date-start" class="input-box" type="date" name="start_time"/>
          <span>-</span>
          <input id="filter-date-end" class="input-box" type="date" name="end_time"/>
        </div>
        <div class="filter-list-item">
          <label>Sorting: </label>
          <select class="input-box" id="filter-status" name="sorting">
            <option value="Newest">Newest</option>
            <option value="Oldest">Oldest</option>
          </select>
        </div>
        <div class="filter-list-item">
          <button class="button">Search</button>
        </div>
        <div class="filter-list-item">
          <span><?php echo $result->num_rows; ?> Results</span>
        </div>
      </div>
    </div>
    </form>

    <hr />
    
    <div class="order-list-container">

    <?php
    foreach( $result as $row ) {

      $oid = $row["order_id"];
      $customer_id = $row["customer_id"];
      $order_time = $row["order_time"];
      $address = $row["order_address"];
      $order_price = $row["order_price"];
      $order_status = $row["delivery_status"];
      $check_time = $row["check_time"];
      $complete_time = $row["complete_time"];
      if ( $order_price <= 10.00 ) $delivery_fee = 1.00;
      else $delivery_fee = sprintf("%.2f", 1 + ($order_price - 10) / 10);

      $sql = "SELECT `product_name`, `quantity`, `single_price` 
      FROM `transaction` JOIN `product` USING(`product_id`) 
      WHERE `order_id` = $oid";
      $product_result = $conn->query($sql);

      echo"
      <div class='order-card'>
        <a href='order-detail.php?order_id=$oid'>
          <h2 class='small-title'>Order #$oid</h2>
        </a>
        <p class='order-card-status'>$order_status</p>
        <div class='order-card-product-list'>
          <ul>";
            
            foreach ($product_result as $product){
              $name = $product["product_name"];
              $number = $product["quantity"];
              $price = $product["single_price"];

              echo"
                <li>
                  <span class='order-card-product-count'>".$number."x</span>
                  <span class='order-card-product-name'>$name</span>
                  <span class='order-card-product-price'>$".$price."</span>
                </li>
              ";
            }

      echo"
              <li style='list-style-type: none;'>
                <span class='order-card-product-count'></span>
                <span class='order-card-product-name'>Total</span>
                <span class='order-card-product-price'>$".$order_price."</span>
              </li> 
          </ul>
        </div>
        <hr />
        <div class='lines-content order-card-info'>
          <p>
            <label>Order Time: </label>
            <span>$order_time</span>
          </p>
          <p>
            <label>Check Time: </label>
            <span>$check_time</span>
          </p>";

      if ( $order_status == "Completed" ){
        echo "<p>
          <label>Complete Time: </label>
          <span>$complete_time</span>
        </p>";
      }

      echo"
          <p>
            <label>Address:</label>
            <span>$address</span>
          </p>
          <p>
            <label>Delivery Fee:</label>
            <span class='price'>$".$delivery_fee."</span>
          </p>
          <p>
            <label>Total:</label>
            <span class='price'>$".$order_price + $delivery_fee."</span>
          </p>
        </div>
      </div>
      ";
    }
    ?>
      
    </div>
  </main>
</body>

</html>