<?php
session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'manager') {
  header('Location: ../login.php');
  exit();
}

$bg = microtime(true);
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] == "POST"){
  $pname = $_POST["product_name"];
  $pname = $conn->real_escape_string($pname);
  $price = $_POST["price"];
  $description = $_POST["description"];
  $description = $conn->real_escape_string($description);
  $category_id = $_POST["category_id"];
  $status = $_POST["status"];
  $image_data = file_get_contents($_FILES["image"]["tmp_name"]);
  $image_data = $conn->real_escape_string($image_data);
  
  $sql = "INSERT INTO `product`(`product_name`, `product_price`, `description`, `image`, `status`)
  VALUES('$pname', $price, '$description', '$image_data', '$status')";

  mysqli_query($conn, $sql);
  $pid = $conn->insert_id;

  $sql = "INSERT INTO `product_category`
  VALUES($pid, $category_id)";
  mysqli_query($conn, $sql);
  $ed = microtime(true);
  $insert_time = sprintf("%.3lf", $ed - $bg);

  header("Location:manager-product-detail.php?product_id=$pid&insert_time=$insert_time");
  exit();
}

?>

<!DOCTYPE html>

<html>

<head>
  <link rel="stylesheet" href="../style/main.css">
  <link rel="stylesheet" href="../style/product.css">
  <link rel="stylesheet" href="../style/cart.css">
  <link rel="stylesheet" href="../style/filter.css">
  <link rel="stylesheet" href="../style/manage.css">
  <title>Add Product - Campus Takeout</title>
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
    <h2 class="title">Add Product</h2>

    <hr />

    <form action="manager-add-product.php" method="post" enctype="multipart/form-data">
    <div class="lines-content manage-content">
      <p>
        <label style="height: 1.5em; line-height: 1.5em;">Product Image:</label>
        <input id="new-image-upload" type="file" accept="image/*" name="image"/>
      </p>
      <p>
        <label>Product Name:</label>
        <input class="input-box" style="width: 20em;" type="text" name="product_name" required>
      </p>
      <p>
        <label>Category:</label>
        <select class="input-box" id="filter-status" name="category_id">
            <?php
              // seach for the categories
              $sql = "SELECT * FROM `category`";
              $category_res = mysqli_query($conn, $sql);
              foreach ($category_res as $category_row) {
                echo "<option value='". $category_row["category_id"] ."'>". $category_row["category_name"] ."</option>";
              }
            ?>
          </select>
      </p>
      <p>
        <label class="line-multiline-label">Description:</label>
        <input class="textarea" name="description" required>
      </p>
      <p>
        <label>Price:</label>
        <input class="input-box" style="width: 6em;" type="number" step="0.01" name="price" required>
      </p>
      <p>
        <label>Status:</label>
        <select class="input-box" id="filter-status" name="status">
          <option value="Available">Available</option>
          <option value="Unavailable">Unavailable</option>
        </select>   
      </p>
      <p style="text-align: right;">
        <button class="button" type="submit">Add Product</button>
      </p>
    </div>
    </form>
    
  </main>
</body>

</html>