<?php
session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'manager') {
  header('Location: ../login.php');
  exit();
}

$bg = microtime(true);
include '../db.php';

// comment information
$sql = "SELECT * FROM `comment`
JOIN `comment_product` USING(`comment_id`)
JOIN `order` USING(`order_id`)
JOIN `customer` USING(`customer_id`)
JOIN  `user` ON `user`.`user_id` = `customer`.`customer_id`
JOIN `deliveryman` USING(`deliveryman_id`)
ORDER BY `comment_date` DESC";
$result = mysqli_query($conn, $sql);
$page = 1;

if ($_SERVER["REQUEST_METHOD"] == "POST"){
  $sql = "SELECT * FROM `comment`
  JOIN `comment_product` USING(`comment_id`)
  JOIN `order` USING(`order_id`)
  JOIN `customer` USING(`customer_id`)
  JOIN  `user` ON `user`.`user_id` = `customer`.`customer_id`
  JOIN `deliveryman` USING(`deliveryman_id`)
  WHERE";

  // search with id
  if ( $_POST["comment_id"] != "" ){
    $comment_id = $_POST["comment_id"];
    $sql .= " `comment_id` = $comment_id AND";
  }
  if ( $_POST["order_id"] != "" ){
    $order_id = $_POST["order_id"];
    $sql .= " `order_id` = $order_id AND";
  }

  // saerch with star level
  if ( $_POST["low_star"] != "" && $_POST["high_star"] != "" ){
    $low_star = $_POST["low_star"];
    $high_star = $_POST["high_star"];
    $sql .= " `star_level` >= $low_star AND `star_level` <= $high_star AND";
  }

  // search with status
  $status = $_POST["status"];
  if ( $status != "All" ) $sql .= " `status` = '$status' AND";
  $sql .= " TRUE";

  // sort by time
  if ( $_POST["sorting"] == "Newest" ) $sql .= " ORDER BY `comment_date` DESC";
  else $sql .= " ORDER BY `comment_date` ASC";
  
  $result = mysqli_query($conn, $sql);
  $page = $_POST["page"];
}
$ed = microtime(true);
$search_time = sprintf("%.3lf",$ed - $bg);
?>

<!DOCTYPE html>

<html>

<head>
  <link rel="stylesheet" href="../style/main.css">
  <link rel="stylesheet" href="../style/product.css">
  <link rel="stylesheet" href="../style/cart.css">
  <link rel="stylesheet" href="../style/filter.css">
  <link rel="stylesheet" href="../style/manage.css">
  <title>Search Comments - Campus Takeout</title>
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
        <a class="header-account" href="manager-home.php"><?php echo $_SESSION["username"] ?> (Manager)</a>
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
    <h2 class="title">Search Comments</h2>
    
    <hr />

    <form action="manager-comments.php" method="post">
    <div class="filter-container">
      <div class="filter-list">
        <div class="filter-list-item">
          <label>Comment ID: </label>
          <input id="filter-id" style="width: 6em;" class="input-box" type="number" min="1" value="" name="comment_id"/>
        </div>
				<div class="filter-list-item">
          <label>Order ID: </label>
          <input id="filter-order-id" style="width: 6em;" class="input-box" type="number" min="1" value="" name="order_id"/>
        </div>
      </div>
      <div class="filter-list">
        <div class="filter-list-item">
          <label>Stars Range: </label>
          <input id="filter-star-lm" class="input-box" type="number" min="1" max="5" step="1" value="" name="low_star"/>
          <span>-</span>
          <input id="filter-star-um" class="input-box" type="number" min="1" max="5" step="1" value="" name="high_star"/>
        </div>
        <div class="filter-list-item">
          <label>Status: </label>
          <select class="input-box" id="filter-status" name="status">
            <option value="All">All</option>
            <option value="Shown">Shown</option>
            <option value="Hidden">Hidden</option>
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
            <span><?php echo $result->num_rows; ?> Results (<?php echo (int)($result->num_rows / 10) + ($result->num_rows % 10 > 0); ?> Page) -- Searched in <?php echo $search_time; ?> Second</span>
          </div>
      </div>
    </div>
    </form>

    <hr/>
    
    <div class="manager-comment-list">

      <?php
      // print comments
      $cnt = 0;
      foreach ($result as $row){
        $cnt++;
        if ( $cnt > ($page - 1) * 10 && $cnt <= $page * 10 ){
          $comment_id = $row["comment_id"];
          $order_id = $row["order_id"];
          $customer_id = $row["customer_id"];
          $customer_name = $row["customer_name"];
          $deliveryman_id = $row["deliveryman_id"];
          $deliveryman_name = $row["deliveryman_name"];
          $comment_date = $row["comment_date"];
          $status = $row["status"];
          $star_level = $row["star_level"];
          $content = $row["content"];

          echo"
          <div class='manager-comment-card'>
            <a href='manager-comment-detail.php?comment_id=$comment_id'>
              <h2 class='small-title'>Comment #$comment_id</h2>
            </a>
            <div class='lines-content manager-comment-card-info'>
              <p>
                <label>Order ID:</label>
                <a href='manager-order-detail.php?order_id=$order_id'>#$order_id</a>
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
                <label>Comment Date:</label>
                <span>$comment_date</span>
              </p>
              <p>
                <label>Comment Status:</label>
                <span>$status</span>
              </p>
              <p>
                <label>Star Rating:</label>
                <span>$star_level Star</span>
              </p>
              <hr/>
              <p>
                $content
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