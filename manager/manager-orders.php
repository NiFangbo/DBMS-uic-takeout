<?php
session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'manager') {
  header('Location: ../login.php');
  exit();
}

$bg = microtime(true);
include '../db.php';

// order information
$sql = "SELECT * FROM `order` JOIN
(SELECT `order_id`, ROUND(SUM(`single_price` * `quantity`), 2) AS `order_price` FROM `order` 
JOIN `transaction` USING(`order_id`) 
JOIN `product` USING(`product_id`) 
GROUP BY `order_id`) AS `temp`
ON `order`.order_id = `temp`.order_id
ORDER BY `order_time` DESC";
$order_result = mysqli_query($conn, $sql);
$page = 1;

if ($_SERVER['REQUEST_METHOD'] == "POST"){
  $sql = "SELECT * FROM `order` JOIN
  (SELECT `order_id`, ROUND(SUM(`single_price` * `quantity`), 2) AS `order_price` FROM `order` 
  JOIN `transaction` USING(`order_id`) 
  JOIN `product` USING(`product_id`) 
  GROUP BY `order_id`) AS `temp`
  ON `order`.order_id = `temp`.order_id
  WHERE";

  // search with id
  if ($_POST["order_id"] != ""){
    $order_id = $_POST["order_id"];
    $sql .= " `order`.order_id = $order_id AND";
  }
  if ($_POST["customer_id"] != ""){
    $customer_id = $_POST["customer_id"];
    $sql .= " `customer_id` = $customer_id AND";
  }
  if ($_POST["deliveryman_id"] != ""){
    $deliveryman_id = $_POST["deliveryman_id"];
    $sql .= " `deliveryman_id` = $deliveryman_id AND";
  }
  
  // search with time
  if ($_POST["start_time"] != "" && $_POST["end_time"] != ""){
    $start_time = $_POST["start_time"]." 00:00:00";
    $end_time = $_POST["end_time"]." 23:59:59";
    $sql .= " `order_time` >= '$start_time' AND `order_time` <= '$end_time' AND";
  }

  // search with status
  $status = $_POST["status"];
  if ( $status != "All" ) $sql .= " `delivery_status` = '$status' AND";
  $sql .= " TRUE";

  // sort by time
  if ( $_POST["sorting"] == "Newest" ) $sql .= " ORDER BY `order_time` DESC";
  else $sql .= " ORDER BY `order_time` ASC";
  
  $order_result = $conn->query($sql);
  $page = $_POST["page"];
}
$ed = microtime(true);
$search_time = sprintf("%.3lf",$ed - $bg);
?>

<!DOCTYPE html>

<html>

<head>
  <link rel="stylesheet" href="../style/main.css">
  <link rel="stylesheet" href="../style/order-list.css">
  <link rel="stylesheet" href="../style/filter.css">
  <link rel="stylesheet" href="../style/manage.css">
  <title>Search Orders - Campus Takeout</title>
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
    <h2 class="title">Search Orders</h2>

    <hr />

    <form action="manager-orders.php" method="post">
    <div class="filter-container">
      <div class="filter-list">
        <div class="filter-list-item">
          <label>Order ID: </label>
          <input id="filter-id" style="width: 6em;" class="input-box" type="number" min="1" value="" name="order_id"/>
        </div>
        <div class="filter-list-item">
          <label>Customer ID: </label>
          <input id="filter-id" style="width: 6em;" class="input-box" type="number" min="1" value="" name="customer_id"/>
        </div>
        <div class="filter-list-item">
          <label>Delivery Person ID: </label>
          <input id="filter-id" style="width: 6em;" class="input-box" type="number" min="1" value="" name="deliveryman_id"/>
        </div>
      </div> 
      <div class="filter-list">
        <div class="filter-list-item">
          <label>Order Time: </label>
          <input id="filter-date-start" class="input-box" type="date" value="" name="start_time"/>
          <span>-</span>
          <input id="filter-date-end" class="input-box" type="date" value="" name="end_time"/>
        </div>
        <div class="filter-list-item">
          <label>Order Status: </label>
          <select class="input-box" id="filter-status" name="status">
            <option value="All">All</option>
            <option value="Cancelled">Cancelled</option>
            <option value="Completed">Completed</option>
            <option value="Delivering">Delivering</option>
            <option value="Pending">Pending</option>
          </select>
        </div>
        <div class="filter-list-item">
          <label>Sorting: </label>
          <select class="input-box" id="filter-status" name="sorting">
            <option value="Newest">Newest</option>
            <option value="Oldest">Oldest</option>
          </select>
        </div>
      </div>
      <div class="filter-list">
        <div class="filter-list-item">
          <label>Page: </label>
          <input id="filter-id" style="width: 4em;" class="input-box" type="number" min="1" step="1" value="<?php echo $page ?>" name="page"/>
        </div>
        <div class="filter-list-item">
          <button class="button" type="submit">Search</button>
        </div>
        <div class="filter-list-item">
          <span><?php echo $order_result->num_rows; ?> Results (<?php echo (int)($order_result->num_rows / 10) + ($order_result->num_rows % 10 > 0); ?> Page) -- Searched in <?php echo $search_time; ?> Second</span>
        </div>
      </div>
    </div>
    </form>

    <hr />

    <div class='order-list-container'>

    <?php
    // print order card
    $cnt = 0;
    foreach ($order_result as $order){
      $cnt++;
      if ( $cnt > ($page - 1) * 10 && $cnt <= $page * 10 ){
        $order_id = $order["order_id"];
        $customer_id = $order["customer_id"];
        $order_time = $order["order_time"];
        $address = $order["order_address"];
        $deliveryman_id = $order["deliveryman_id"];
        $check_time = $order["check_time"];
        if ( $check_time == "0000-00-00 00:00:00" ) $check_time = "";
        $status = $order["delivery_status"];
        $complete_time = $order["complete_time"];
        if ( $complete_time == "0000-00-00 00:00:00" ) $complete_time = "";
        $order_price = $order["order_price"];
        if ( $order_price <= 10.00 ) $delivery_fee = 1.00;
        else $delivery_fee = sprintf("%.2f", 1 + ($order_price - 10) / 10);

        $sql = "SELECT `product_name`, `quantity`, `single_price` 
        FROM `transaction` JOIN `product` USING(`product_id`) 
        WHERE `order_id` = $order_id";
        $product_result = $conn->query($sql);

        echo"
          <div class='order-card'>
            <a href='manager-order-detail.php?order_id=$order_id'>
              <h2 class='small-title'>Order #$order_id</h2>
            </a>
            <p class='order-card-status'>$status</p>

          <div class='order-card-product-list'>
            <ul>
        ";

          // print product card
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
            </p>
            <p>
              <label>Complete Time: </label>
              <span>$complete_time</span>
            </p>
            <p>
              <label>Address:</label>
              <span>$address</span>
            </p>
            <p>
              <label>Delivery Fee:</label>
              <span class='price'>$".$delivery_fee."</span>
            </p>
            <p>
              <label>Pay Amount:</label>
              <span class='price'>$".$order_price+$delivery_fee."</span>
            </p>
          </div>
        </div>
        ";
      }
    }
    ?>

    </div>

  </main>
</body>

</html>