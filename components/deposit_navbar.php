<!-- components/deposit_navbar.php -->
<style>
  body {
    margin: 0;
    font-family: Arial, sans-serif;
  }

  .navbar {
    background-color: #2196F3;
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
    background-color: #1976D2;
  }

  .navbar .right {
    float: right;
  }
</style>

<div class="navbar">
  <a href="/virtual_store/index.php">Home</a>
  <a href="/virtual_store/products/inventory.php">Inventory</a>
  <a href="/virtual_store/products/add_product.php">Add Product</a>
</div> 