<?php
session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'customer') {
    header('Location: login.php');
    exit();
}

include 'db.php';

$pid = $_GET["product_id"] ?? '';

// customer information
$cid = $_SESSION["user_id"];
$sql = "SELECT * FROM `customer` WHERE `customer_id` = $cid";
$result = mysqli_query($conn, $sql);
$customer = mysqli_fetch_assoc($result);

// cart information
$sql = "SELECT * FROM `cart` 
JOIN `product` USING(`product_id`)
JOIN `product_category` USING(`product_id`)
JOIN `category` USING(`category_id`)
WHERE `product`.`status` = 'Available' AND `customer_id` = $cid";
$cart_result = mysqli_query($conn, $sql);

// product information
$sql = "SELECT * FROM `product`
JOIN `product_category` USING(`product_id`)
JOIN `category` USING(`category_id`)
WHERE `product_id` = $pid";
$product_result = mysqli_query($conn, $sql);
$product = mysqli_fetch_assoc($product_result);

// product quantity in cart
$sql = "SELECT * FROM `cart`
JOIN `product` USING(`product_id`)
WHERE `product`.`status` = 'Available' AND `customer_id` = $cid AND `product_id` = $pid";
$result = mysqli_query($conn, $sql);
if ( $result->num_rows > 0 ) {
  $quantity = $result->fetch_assoc()["quantity"];
}else $quantity = 0;

// comment information
$sql = "SELECT * FROM `comment`
JOIN `comment_product` USING(`comment_id`)
JOIN `order` USING (`order_id`)
JOIN `customer` USING(`customer_id`)
WHERE `comment`.`status` = 'Shown' AND `product_id` = $pid
ORDER BY `comment_date` DESC";
$comment_result = mysqli_query($conn, $sql);

if ($_SERVER["REQUEST_METHOD"] == "POST"){
  $newquantity = $_POST["quantity"];
  if ( $quantity > 0 && $newquantity > 0 ){
    $sql = "UPDATE `cart`
    SET `quantity` = $newquantity
    WHERE `customer_id` = $cid AND `product_id` = $pid";
  }else if ($quantity == 0 && $newquantity > 0 ){
    $sql = "INSERT INTO `cart` VALUES
    ($cid, $pid, $newquantity)";
  }else if ( $quantity > 0 && $newquantity == 0 ){
    $sql = "DELETE FROM `cart` WHERE `customer_id` = $cid AND `product_id` = $pid";
  }
  mysqli_query($conn, $sql);

  header("Location: customer/cart.php");
  exit();
}

?>

<!DOCTYPE html>

<html>

<head>
  <link rel="stylesheet" href="style/main.css">
  <link rel="stylesheet" href="style/product.css">
  <title>product detail - Campus Takeout</title>
  <script>

  </script>
</head>

<body class="uic-background">
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
  <main class="main-body">

    <form action="product-detail.php?product_id=<?php echo $pid; ?>" method="post">
    <div class="body-card">
      <div class="product-header">
        <?php
        echo"
        <img class='product-img' src='data:image;base64,". base64_encode($product["image"]) ."' alt='". $product["product_name"] ."'/>
        ";
        ?>
        <div class="product-header-content">
          <h2 class="product-header-title">
            <?php echo $product["product_name"]; ?>
          </h2>
          <p class="product-header-desc">
            <?php echo $product["description"]; ?>
          </p>
          <a class="product-header-category" href="products.php?category=<?php echo $product["category_name"]?>">
            <?php echo $product["category_name"]; ?>
          </a>
          <p class="product-header-price">
            <span>$<?php echo $product["product_price"] ?></span>
          </p>
          <div class="product-header-ending">
            <div class="product-quantity-box">
              <p >Quantity: </p>
              <input class="product-quantity-input input-box" type="number" value="<?php echo $quantity; ?>" min="0" name="quantity"/>
            </div> 
            <button class="add-to-cart-button button" type="submit">Add to Cart</button>
          </div>
        </div>
      </div>
    </div>
    </form>


    <div class="body-card">
      <div class="comments-card-container">
        <h2 class="comments-card-title">
          Comments
        </h2>
        <div class="comments-list">

          <?php
          foreach ( $comment_result as $comment ){
            if ( $comment["nickname"] == NULL ) $nickname = $comment["customer_name"];
            else $nickname = $comment["nickname"];
            switch ($comment["star_level"]){
              case 1: $star = "★☆☆☆☆"; break;
              case 2: $star = "★★☆☆☆"; break;
              case 3: $star = "★★★☆☆"; break;
              case 4: $star = "★★★★☆"; break;
              case 5: $star = "★★★★★"; break;
            }
            $comment_date = $comment["comment_date"];
            $content = $comment["content"];

          
            echo"
            <div class='comment-box'>
              <div class='comment-header'>
                <a class='comment-nickname'>$nickname</a>
                <span class='comment-star'>
                  $star
                </span>
                <span class='comment-time'>
                  $comment_date
                </span>
              </div>
              <p class='comment-content'>
                $content
              </p>
            </div>";
          }
          ?>
          
          <div class="comment-footer"></div>
        </div>
      </div>
    </div>

  </main>
</body>

</html>