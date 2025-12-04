<?php
session_start();

// header for direction
if (isset($_SESSION['user_id'])) {
  if ($_SESSION['user_type'] == 'manager') {
    header("Location: manager/manager-index.php");
  } else if ($_SESSION['user_type'] == 'deliveryman') {
    header("Location: delivery/delivery-pending.php");
  } else{
    header("Location: products.php?category=All");
  }
  exit();
} else {
    header("Location: login.php");
    exit();
}
?>