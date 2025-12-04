<?php
session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'manager') {
  header('Location: ../login.php');
  exit();
}

$bg = microtime(true);
include '../db.php';

// product information
$sql = "SELECT * FROM `product` 
JOIN `product_category` USING(`product_id`) 
JOIN `category` USING(`category_id`)";
$result = $conn->query($sql);
$page = 1;

if ($_SERVER['REQUEST_METHOD'] == "POST"){
  $sql = "SELECT * FROM `product` 
  JOIN `product_category` USING(`product_id`) 
  JOIN `category` USING(`category_id`) 
  WHERE";

  // search with id
  if ( $_POST["pid"] != "" ){
    $pid = $_POST["pid"];
    $sql .= " `product_id` = $pid AND";
  }

  // search with name
  if ( $_POST["pname"] != "" ){
    $pname = $_POST["pname"];
    $pname = $conn->real_escape_string($pname);
    $sql .= " `product_name` LIKE '%$pname%' AND";
  }

  // search with category
  $category = $_POST["category"];
  if ( $category != "All" ) $sql .= " `category_name` = '$category' AND";

  $sql .= " TRUE";
  $result = $conn->query($sql);
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
  <title>Search Products - Campus Takeout</title>
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
    <h2 class="title">Search Products</h2>
    
    <hr />
    
    <form action="manager-products.php" method="post">
    <div class="filter-container">
      <div class="filter-list">
        <div class="filter-list-item">
          <label>ID: </label>
          <input id="filter-id" style="width: 8em;" class="input-box" type="number" min="1" placeholder="Product ID" value="" name="pid"/>
        </div>
        <div class="filter-list-item">
          <label>Name: </label>
          <input id="filter-id" style="width: 12em;" class="input-box" type="text" placeholder="Product Name" value="" name="pname"/>
        </div>
        <div class="filter-list-item">
        <label>Category: </label>
        <select class="input-box" id="filter-status" name="category">
          <option value="All">All</option>
          <?php
            // category information
            $sql = "SELECT `category_name` FROM `category`";
            $category_res = mysqli_query($conn, $sql);
            
            foreach ($category_res as $category_row) {
              echo "<option value='". $category_row["category_name"] ."'>". $category_row["category_name"] ."</option>";
            }
          ?>
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
    
    <div class='product-list-container'>

    <?php
    // print product card
    $cnt = 0;
    foreach ($result as $row){
      $cnt++;
      if ( $cnt > ($page - 1) * 10 && $cnt <= $page * 10 ){
        $pid = $row["product_id"];
        echo"
        <div class='product-card'>
          <a href='manager-product-detail.php?product_id=$pid'>
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
    }
    ?>

    </div>

  </main>
</body>

</html>