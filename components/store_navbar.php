<!-- components/store_navbar.php -->
<style>
  body {
    margin: 0;
    font-family: Arial, sans-serif;
  }

  .navbar {
    background-color: #4CAF50;
    overflow: hidden;
    position: sticky;
    top: 0;
    z-index: 1000;
  }

  .navbar a {
    float: left;
    display: block;
    color: white;
    text-align: center;
    padding: 14px 20px;
    text-decoration: none;
    transition: background-color 0.3s;
  }

  .navbar a:hover {
    background-color: #45a049;
  }

  .navbar .right {
    float: right;
  }

  .cart-count {
    background-color: #ff4444;
    color: white;
    border-radius: 50%;
    padding: 2px 6px;
    font-size: 12px;
    margin-left: 5px;
  }
</style>

<div class="navbar">
  <a href="/virtual_store/index.php">Home</a>
  <a href="/virtual_store/departments/index.php">Departments</a>
  <a href="/virtual_store/cart/view.php" class="right">
    Cart
    <?php
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        $itemCount = array_sum($_SESSION['cart']);
        echo "<span class='cart-count'>$itemCount</span>";
    }
    ?>
  </a>
</div> 