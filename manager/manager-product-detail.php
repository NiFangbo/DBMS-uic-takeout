<?php
session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'manager') {
  header('Location: ../login.php');
  exit();
}

include '../db.php';

$pid = $_GET["product_id"] ?? '';

// product information
$sql = "SELECT * FROM `product` JOIN `product_category` USING(`product_id`) WHERE `product_id` = $pid";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == "POST"){
    // update information
    if (isset($_POST["upload_info"])){
      $pname = $_POST["product_name"];
      $pname = $conn->real_escape_string($pname);
      $price = $_POST["price"];
      $description = $_POST["description"];
      $description = $conn->real_escape_string($description);
      $category_id = $_POST["category_id"];
      $status = $_POST["status"];
      
      $sql = "UPDATE `product`
      SET `product_name` = '$pname', `product_price` = $price, `description` = '$description', `status` = '$status'
      WHERE `product_id` = $pid";
      $conn->query($sql);

      $sql = "UPDATE `product_category` SET `category_id` = $category_id WHERE `product_id` = $pid";
      $conn->query($sql);

      $_SESSION['normal_message'] = "Successfully update the information!";
    }

    // upload image
    if (isset($_POST["upload_img"])){
      $image_data = file_get_contents($_FILES["image"]["tmp_name"]);
      $image_data = $conn->real_escape_string($image_data);
      
      $sql = "UPDATE `product` SET `image` = '$image_data' WHERE `product_id` = $pid";
      $conn->query($sql);

      $_SESSION['normal_message'] = "Successfully update the image!";
    }

    // redirect
    header("Location:manager-product-detail.php?product_id=$pid");
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
  <title>Product #1337 - Campus Takeout</title>
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
    <h2 class="title">Product #<?php echo $pid ?></h2>
    <?php
      if (isset($_GET["insert_time"])){
        echo "<p>new product inserted in ".$_GET["insert_time"]." seconds.</p>";
      }
    ?>

    <hr />

    <form action="manager-product-detail.php?product_id=<?php echo $pid ?>" method="post">
    <input type="hidden" type="hidden" name="upload_info">
    <div class="lines-content manage-content">
      <p>
        <label>Product Name:</label>
        <input class="input-box" style="width: 20em;" type="text" value="<?php echo $row["product_name"]?>" name="product_name" required>
      </p>
      <p>
        <label>Category:</label>
        <select class="input-box" id="filter-status" name="category_id">
            <?php
            // category information
            $sql = "SELECT * FROM `category`";
            $category_res = mysqli_query($conn, $sql);
            
            foreach ($category_res as $category_row) {
              if ( $row["category_id"] == $category_row["category_id"])
                echo "<option value='".$category_row["category_id"]."' selected>".$category_row["category_name"]."</option>";
              else 
                echo "<option value='".$category_row["category_id"]."'>".$category_row["category_name"]."</option>";
            }
            ?>
          </select>
      </p>
      <p>
        <label class="line-multiline-label">Description:</label>
        <textarea class="textarea" name="description" required><?php echo $row["description"]?> </textarea>
      </p>
      <p>
        <label>Price:</label>
        <input class="input-box" style="width: 6em;" type="number" value="<?php echo $row["product_price"]?>" step="0.01" name="price" required>
      </p>
      <p>
        <label>Status:</label>
        <select class="input-box" id="filter-status" name="status">
          <?php $status = $row["status"]; ?>
          <option value="Available">Available</option>
          <option value="Unavailable" <?php if ($status == 'Unavailable') echo 'selected'; ?>>Unavailable</option>
        </select>   
      </p>
      <p style="text-align: right;">
        <button class="button" type="submit">Update Product Info</button>
      </p>
    </div>
    </form>

    <hr />

    <form action="manager-product-detail.php?product_id=<?php echo $pid ?>" method="post" enctype="multipart/form-data">
    <input type="hidden" type="hidden" name="upload_img">
    <div class="lines-content manage-content">
      <p>
        <label>Product Image:</label>
        <img class="product-detail-img" src="data:image;base64,<?php echo base64_encode($row["image"]) ?>" alt="<?php echo $row["product_name"] ?>"/>
      </p>
      <p>
        <label style="height: 1.5em; line-height: 1.5em;">Upload New:</label>
        <input id="new-image-upload" type="file" accept="image/*" name="image" required/>
      </p>
      <p style="text-align: right;">
        <button class="button" type="submit">Update Product Image</button>
      </p>
    </div>
    </form>

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