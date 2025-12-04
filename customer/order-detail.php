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
  // submit the comment to the order and the product in the order
  if (isset( $_POST["submit_comment"])){
    $product_id = $_POST["product_id"];
    $star_level = $_POST["star_level"];
    $content = $_POST["content"];
    $content = $conn->real_escape_string($content);

    if ( $status == "Completed" ){
      $sql = "INSERT INTO `comment`(`content`, `customer_id`, `comment_date`, `star_level`, `status`)
      VALUES ('$content', $cid,  (SELECT NOW() AS CurrentDate), $star_level, 'Shown')";

      $conn->query($sql);
      $comment_id = $conn->insert_id;
      
      $sql = "INSERT INTO `comment_product` VALUES ($comment_id, $oid, $product_id)";
      
      $conn->query($sql);
      $_SESSION['normal_message'] = "Successfully submit the comment!";
    } else {
      $_SESSION["error_message"] = "You cannot make comment when the order is not completed!";
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
  <title>Order #1024 - Campus Takeout</title>
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
    <h2 class="title">Order #<?php echo $oid; ?></h2>

    <hr />

    <div class="product-list-container">

    <?php
    $sql = "SELECT * FROM `transaction` 
    JOIN `product` USING(product_id) 
    JOIN product_category USING(product_id) 
    JOIN category USING(category_id) 
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

    <div class="order-box">
      <h3 class="small-title">
        Order Status
      </h3>
      <div class="lines-content order-box-content">
      
      <?php
      echo"
      <p>
        <label>Status: </label>
        <span>$status</span>
      </p>
      <p>
        <label>Delivery Person:</label>";
        if ( $deliveryman_id != NULL )
          echo "<span>$deliveryman_name</span>";
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
      
    </div>
    </div>

    <hr />

    <div class="order-box">
      <h3 class="small-title">
        Comment
      </h3>
      
      <form action="order-detail.php?order_id=<?php echo $oid; ?>" method="post">
      <div class="lines-content order-box-content">
        <p>
          <label>Product:</label>
          <select class="input-box" name="product_id">
            
            <?php
            // product information in order
            $sql = "SELECT * FROM `transaction` 
            JOIN `product` USING(product_id) 
            WHERE `order_id` = $oid";
            
            $product_result = $conn->query($sql);

            foreach( $product_result as $product ){
              $pid = $product["product_id"];
              $pname = $product["product_name"];
              echo"<option value='$pid'>$pname</option>";
            }
            ?>

          </select>
        </p>
        <p>
          <label>Stars:</label>
          <select class="input-box" name="star_level">
            <option value="5">5 Star</option>
            <option value="4">4 Star</option>
            <option value="3">3 Star</option>
            <option value="2">2 Star</option>
            <option value="1">1 Star</option>
          </select>
        </p>
        <p>
          <label class="line-multiline-label" style="line-height: 2.5em;">Comment:</label>
          <input class="textarea" style="height: 8em;" name="content" required>
        </p>
        <p>
          <input class="button" type="submit" value="Submit Comment" name="submit_comment">
        </p>
      </div>
      </form>

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