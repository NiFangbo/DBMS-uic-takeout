<?php
session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'customer') {
    header('Location: login.php');
    exit();
}

include 'db.php';

$category = $_GET["category"] ?? '';

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

// products in the category
$sql = "SELECT * FROM `product` 
JOIN `product_category` USING(`product_id`) 
JOIN `category` USING(`category_id`)
WHERE `product`.`status` = 'Available'";
if ( $category != "All" ) $sql .= " AND `category_name` = '$category'";
$product_result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>

<html>

<head>
  <link rel="stylesheet" href="style/main.css">
  <link rel="stylesheet" href="style/product.css">
  <title>All Products - Campus Takeout</title>
</head>

<body>
  <header>
    <ul class="header-left">
      <li>
        <a href="products.php?category=All">
          <p class="header-title">Campus Takeout</p>
        </a>
      </li>
    </ul>
    <ul class="header-right">
      <li>
        <a class="header-account" href="customer/customer-home.php"><?php if ( $customer["nickname"] != NULL ) echo $customer["nickname"]; else echo $_SESSION["username"]; ?></a>
      </li>
      <li>
        <a href="customer/cart.php">Cart (<?php echo $cart_result->num_rows; ?>)</a>
      </li>
      <li>
        <a href="customer/customer-orders.php">Orders</a>
      </li>
    </ul>
  </header>
  <div class="side-bar">
    <div class="side-bar-content">
      <h3 class="side-bar-title">Categories</h3>
      <a class="side-bar-item" href="products.php?category=All">
        All
      </a>
      <?php
      foreach ($category_result as $category) {
        $category_name = $category["category_name"];
        echo"
      <a class='side-bar-item' href='products.php?category=$category_name'>
        $category_name
      </a>";
      }
      ?>
    </div>
  </div>
  <main class="main-body-with-side-bar">
    <h2 class="title">Explore Our Goods</h2>
    
    <hr />
    
    <div class="product-list-container">
      
    <?php  
      foreach ($product_result as $row){
        $pid = $row["product_id"];
        echo "
          <div class='product-card'>
            <a href='product-detail.php?product_id=$pid'>
                <img class='product-card-img' src='data:image;base64,". base64_encode($row["image"]) ."' alt='". $row["product_name"] ."'/>
            </a>
            <div class='product-card-info'>
            <h2 class='product-card-title'>".
                $row["product_name"].
            "</h2>
            <a class='product-card-category'>".
                $row["category_name"].
            "</a>
            <p class='product-card-desc'>".
                $row["description"].
            "</p>
              <div class='product-card-footer'>
                <p class='product-card-price'>
                <span style='font-size: 14pt;'>$". $row["product_price"] ."</span>
                </p>
              </div>
            </div>
          </div>";
      }
    ?>

    </div>
  </main>
</body>

</html>