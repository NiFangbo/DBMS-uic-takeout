<?php
session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'deliveryman') {
    header('Location: ../login.php');
    exit();
}

$bg = microtime(true);
include '../db.php';

$did = $_SESSION["user_id"];

$sql = "SELECT * FROM `order` JOIN
(SELECT `order_id`, ROUND(SUM(`single_price` * `quantity`), 2) AS `order_price` FROM `order` 
JOIN `transaction` USING(`order_id`) 
JOIN `product` USING(`product_id`) 
GROUP BY `order_id`) AS `temp`
ON `order`.order_id = `temp`.order_id
WHERE `delivery_status` = 'Pending'
ORDER BY `order_time` DESC";
$result = mysqli_query($conn, $sql);
$page = 1;

if ($_SERVER["REQUEST_METHOD"] == "POST"){
  if (isset($_POST["search_order"])){
    $sql = "SELECT * FROM `order` JOIN
    (SELECT `order_id`, ROUND(SUM(`single_price` * `quantity`), 2) AS `order_price` FROM `order` 
    JOIN `transaction` USING(`order_id`) 
    JOIN `product` USING(`product_id`) 
    GROUP BY `order_id`) AS `temp`
    ON `order`.order_id = `temp`.order_id
    WHERE `delivery_status` = 'Pending' AND";

    if ( $_POST["oid"] != "" ){
      $oid = $_POST["oid"];
      $sql .= " `order`.`order_id` = $oid AND";
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
    $page = $_POST["page"];
  }

  if (isset($_POST["take_order"])){
    $oid = $_POST["order_id"];

    $sql = "UPDATE `order` SET 
    `deliveryman_id` = $did, 
    `check_time` = (SELECT NOW() AS CurrentDateTime), 
    `delivery_status` = 'Delivering'
    WHERE `order_id` = $oid";

    mysqli_query($conn, $sql);

    $_SESSION["normal_message"] = "Take the order successfully!";
    
    header("Location: delivery-pending.php");
    exit();
  }
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
  <title>Pending Orders - Campus Takeout</title>
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
    <h2 class="title">Pending Orders</h2>

    <hr />

    <form action="delivery-pending.php" method="post">
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
      </div>
      <div class="filter-list">
      <div class="filter-list-item">
        <label>Page: </label>
          <input id="filter-id" style="width: 4em;" class="input-box" type="number" min="1" max="<?php echo (int)($result->num_rows / 10) + 1; ?>" step="1" value="<?php echo $page ?>" name="page"/>
        </div>
        <div class="filter-list-item">
          <input class="button" type="submit" value="Search" name="search_order">
        </div>
        <div class="filter-list-item">
          <span><?php echo $result->num_rows; ?> Results (<?php echo (int)($result->num_rows / 10) + ($result->num_rows % 10 > 0); ?> Page) -- Searched in <?php echo $search_time; ?> Second</span>
        </div>
      </div>
    </div>
    </form>

    <hr />

    <div class="order-list-container">

      <?php
      $cnt = 0;
      foreach ($result as $row){
        $cnt++;
        if ( $cnt > ($page - 1) * 10 && $cnt <= $page * 10 ){
          $oid = $row["order_id"];
          $order_time = $row["order_time"];
          $address = $row["order_address"];
          $order_price = $row["order_price"];
          if ( $order_price <= 10.00 ) $delivery_fee = 1.00;
          else $delivery_fee = sprintf("%.2f", 1 + ($order_price - 10) / 10);

          $sql = "SELECT `product_name`, `quantity`, `single_price` 
          FROM `transaction` JOIN `product` USING(`product_id`) 
          WHERE `order_id` = $oid";
          $product_result = $conn->query($sql);

          echo"
          <div class='order-card'>
            <h2 class='small-title'>Order #$oid</h2>
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
                <label>Address:</label>
                <span>$address</span>
              </p>
              <p>
                <label>Delivery Fee:</label>
                <span class='price'>$".$delivery_fee."</span>
              </p>
            </div>

            <hr />
            
            <form action='delivery-pending.php' method='post'>
            <input type='hidden' name='order_id' value='$oid'>
            <div class='order-card-footer'>
              <input class='button' type='submit' value='Take Order' name='take_order'>
            </div>
            </form>
          </div>
          ";
        }
      }
      ?>

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