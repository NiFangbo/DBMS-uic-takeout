<?php
session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'deliveryman') {
  header('Location: ../login.php');
  exit();
}

include '../db.php';

$status = $_GET["status"] ?? '';
$did = $_SESSION["user_id"];

// order information
$sql = "SELECT * FROM `order` JOIN
(SELECT `order_id`, ROUND(SUM(`single_price` * `quantity`), 2) AS `order_price` FROM `order` 
JOIN `transaction` USING(`order_id`) 
JOIN `product` USING(`product_id`) 
GROUP BY `order_id`) AS `temp`
ON `order`.order_id = `temp`.order_id
WHERE `deliveryman_id` = $did AND `delivery_status` <> 'Pending' AND";
if ( $status != "All" ){
  $sql .= " `delivery_status` = '$status' AND";
}
$sql .= " TRUE ORDER BY `order_time` DESC";
$result = mysqli_query($conn, $sql);


if ($_SERVER["REQUEST_METHOD"] == "POST"){
  if (isset($_POST["search_order"])){
     $sql = "SELECT * FROM `order` JOIN
    (SELECT `order_id`, ROUND(SUM(`single_price` * `quantity`), 2) AS `order_price` FROM `order` 
    JOIN `transaction` USING(`order_id`) 
    JOIN `product` USING(`product_id`) 
    GROUP BY `order_id`) AS `temp`
    ON `order`.order_id = `temp`.order_id
    WHERE `deliveryman_id` = $did AND `delivery_status` <> 'Pending' AND";

    // search with status
    if ( $status != "All" ){
      $sql .= " `delivery_status` = '$status' AND";
    }

    // search with id
    if ( $_POST["oid"] != "" ){
      $oid = $_POST["oid"];
      $sql .= " `order`.`order_id` = $oid AND";
    }

    // search with time
    if ( $_POST["start_time"] != "" && $_POST["end_time"] != "" ){
      $start_time = $_POST["start_time"]." 00:00:00";
      $end_time = $_POST["end_time"]." 23:59:59";
      $sql .= " `order_time` >= '$start_time' && `order_time` <= '$end_time' AND";
    }
    $sql .= " TRUE";

    // sort by time
    if ( $_POST["sorting"] == "Newest" ) $sql .= " ORDER BY `order_time` DESC";
    else $sql .= " ORDER BY `order_time` ASC";

    $result = mysqli_query($conn, $sql);
  }

  // set the complete status and the complete time and pay the money to deliveryman
  if (isset($_POST["complete"])){
    $oid = $_POST["oid"];
    $delivery_fee = $_POST["delivery_fee"];

    $sql = "UPDATE `order` 
    SET `delivery_status` = 'Completed', 
    `complete_time` = (SELECT NOW() AS CurrentDateTime) 
    WHERE `order_id` = $oid";

    mysqli_query($conn, $sql);

    $sql = "UPDATE `deliveryman`
    SET `balance` = `balance` + $delivery_fee
    WHERE `deliveryman_id` = $did";

    mysqli_query($conn, $sql);

    $_SESSION['normal_message'] = "Successfully complete the order!";

    header("Location: delivery-orders.php?status=$status");
    exit();
  }
  
  // cancel the order and return the payment back to the customer
  if (isset($_POST["cancel"])){
    $oid = $_POST["oid"];
    $customer_id = $_POST["customer_id"];
    $order_price = $_POST["order_price"];
    $delivery_fee = $_POST["delivery_fee"];

    $sql = "UPDATE `order` 
    SET `delivery_status` = 'Cancelled', 
    `complete_time` = NULL
    WHERE `order_id` = $oid";

    mysqli_query($conn, $sql);

    // return all the payment back to customer
    $sql = "UPDATE `customer`
    SET `balance` = `balance` + $order_price + $delivery_fee
    WHERE `customer_id` = $customer_id";

    mysqli_query($conn, $sql);

    $_SESSION['normal_message'] = "The order is cancelled!";

    
    header("Location: delivery-orders.php?status=$status");
    exit();
  }
}


?>

<!DOCTYPE html>

<html>

<head>
  <link rel="stylesheet" href="../style/main.css">
  <link rel="stylesheet" href="../style/order-list.css">
  <link rel="stylesheet" href="../style/filter.css">
  <title>Delivery Index - Campus Takeout</title>
</head>

<body class="delivery-page">
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
        <a class="header-account" href="delivery-home.php"><?php echo $_SESSION["username"] ?> (Delivery Person)</a>
      </li>
    </ul>
  </header>
  <div class="side-bar">
    <div class="side-bar-content">
      <h3 class="side-bar-title">Take Orders</h3>
      <a class="side-bar-item" href="delivery-pending.php">
        Pending Orders
      </a>
      <h3 class="side-bar-title">Delivery</h3>
      <a class="side-bar-item" href="delivery-orders.php?status=All">
        All
      </a>
      <a class="side-bar-item" href="delivery-orders.php?status=Delivering">
        Delivering
      </a>
      <a class="side-bar-item" href="delivery-orders.php?status=Completed">
        Completed
      </a>
      <a class="side-bar-item" href="delivery-orders.php?status=Cancelled">
        Cancelled
      </a>
    </div>
  </div>
  <main class="main-body-with-side-bar">
    <h2 class="title">Orders</h2>

    <hr />

    <form action="delivery-orders.php?status=<?php echo $status; ?>" method="post">
    <div class="filter-container">
      <div class="filter-list">
        <div class="filter-list-item">
          <label>ID: </label>
          <input id="filter-id" style="width: 6em;" class="input-box" type="number" placeholder="Order ID" min="1" value="" name="oid"/>
        </div>
        <div class="filter-list-item">
          <label>Order Time: </label>
          <input id="filter-date-start" class="input-box" type="date" value="" name="start_time"/>
          <span>-</span>
          <input id="filter-date-end" class="input-box" type="date" value="" name="end_time"/>
        </div>
        <div class="filter-list-item">
          <label>Sorting: </label>
          <select class="input-box" id="filter-status" name="sorting">
            <option value="Newest">Newest</option>
            <option value="Oldest">Oldest</option>
          </select>
        </div>
        <div class="filter-list-item">
          <input class="button" type="submit" value="Search" name="search_order">
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
        <h2 class='small-title'>Order #$oid</h2>";

      if ( $order_status == "Completed" ){
        echo "<p class='order-card-status-completed order-card-status'>Completed</p>";
      }else if ( $order_status == "Delivering" ){
        echo "<p class='order-card-status-delivering order-card-status'>Delivering</p>";
      }else if ( $order_status == "Cancelled" ){
        echo "<p class='order-card-status-cancelled order-card-status'>Cancelled</p>";
      }

      echo"
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
        </div>";
      
      if ( $order_status == 'Delivering' ){
        echo"
        <hr />
        <form action='delivery-orders.php?status=$status' method='post'>
        <div class='order-card-footer'>
          <input type='hidden' name='oid' value='$oid'>
          <input type='hidden' name='customer_id' value='$customer_id'>
          <input type='hidden' name='order_price' value='$order_price'>
          <input type='hidden' name='delivery_fee' value='$delivery_fee'>
          <input class='button' type='submit' value='Complete' name='complete' onclick='return confirm(&quot;Are you sure to complete this order?&quot;)'/>
          <input class='button-danger button' type='submit' value='Cancel Order' name='cancel' onclick='return confirm(&quot;Are you sure to cancel this order?&quot;)'/>
        </div>
        </form>
        ";
      }

      echo"
      </div>
      ";
    }
    ?>

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