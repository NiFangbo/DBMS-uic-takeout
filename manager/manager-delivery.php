<?php
session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'manager') {
  header('Location: ../login.php');
  exit();
}

$bg = microtime(true);
include '../db.php';

// deliveryman information
$sql = "SELECT * FROM `deliveryman`";
$result = $conn->query($sql);
$page = 1;

if ($_SERVER["REQUEST_METHOD"] == "POST"){
  $sql = "SELECT * FROM `deliveryman` WHERE";

  // search with id
  if ($_POST["deliveryman_id"] != ""){
    $did = $_POST["deliveryman_id"];
    $sql .= " `deliveryman_id` = $did AND";
  }

  // search with username
  if ($_POST["username"] != ""){
    $username = $_POST["username"];
    $username = $conn->real_escape_string($username);
    $sql .= " `deliveryman_name` LIKE '%$username%' AND";
  }

  // search with phone
  if ($_POST["phone"] != ""){
    $phone = $_POST["phone"];
    $phone = $conn->real_escape_string($phone);
    $sql .= " `phone` = '$phone' AND";
  }
  
  // search with identity
  $identity = $_POST["identity"];
  if ( $identity != "All" ) $sql .= " `identity` = '$identity' AND"; 
  
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
  <link rel="stylesheet" href="../style/order-list.css">
  <link rel="stylesheet" href="../style/filter.css">
  <link rel="stylesheet" href="../style/manage.css">
  <title>Search Customers - Campus Takeout</title>
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
    <h2 class="title">Search Delivery Persons</h2>

    <hr />

    <form action="manager-delivery.php" method="post">
    <div class="filter-container">
      <div class="filter-list">
        <div class="filter-list-item">
          <label>ID: </label>
          <input id="filter-id" style="width: 10em;" class="input-box" type="number" placeholder="Deliveryman ID" min="1" value="" name="deliveryman_id"/>
        </div>
        <div class="filter-list-item">
          <label>Username: </label>
          <input id="filter-id" style="width: 10em;" class="input-box" type="text" placeholder="Username" value="" name="username"/>
        </div>
        <div class="filter-list-item">
          <label>Tel: </label>
          <input id="filter-id" style="width: 10em;" class="input-box" type="tel" placeholder="Phone Number" value="" name="phone"/>
        </div>
        <div class="filter-list-item">
          <label>Identity: </label>
          <select class="input-box" id="filter-status" name="identity">
            <option value="All">All</option>
            <option value="Part-Time Student">Part-Time Student</option>
            <option value="Part-Time Staff">Part-Time Staff</option>
            <option value="Delivery Person">Delivery Person</option>
          </select>
        </div>
      </div>
      <div class="filter-list">
        <div class="filter-list-item">
          <label>Page:</label>
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

    <hr />

    <table class="table">
      <tbody>
        <tr>
          <th>User ID</th>
          <th>Username</th>
          <th>Tel</th>
          <th>Identity</th>
          <th>Balance</th>
        </tr>
        
        <?php
        // print deliveryman information
        $cnt = 0;
        foreach ($result as $row){
          $cnt++;
          if ( $cnt > ($page - 1) * 10 && $cnt <= $page * 10 ){
            $did = $row["deliveryman_id"];
            $dname = $row["deliveryman_name"];
            $phone = $row["phone"];
            $identity = $row["identity"];
            if ( $identity == NULL ) $identity = "Unknown";
            $balance = $row["balance"];

            echo"
            <tr>
            <td><a href='manager-delivery-detail.php?deliveryman_id=$did'>$did</a></td>
            <td>$dname</td>
            <td>$phone</td>
            <td>$identity</td>
            <td>$".$balance."</td>
            </tr>
            ";
          }
        }
        ?>

      </tbody>
    </table>

  </main>
</body>

</html>