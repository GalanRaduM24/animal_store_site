<?php
require_once '../config/database.php';
session_start();
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Get cart items
$query = "SELECT c.*, p.name, p.price, p.image_path 
          FROM cart c 
          JOIN products p ON c.product_id = p.id 
          " . ($user_id ? "WHERE c.user_id = :user_id" : "") . "
          ORDER BY p.name";
$stmt = oci_parse($conn, $query);
if ($user_id) {
    oci_bind_by_name($stmt, ":user_id", $user_id);
}
oci_execute($stmt);

// Fetch items
$items = [];
while ($item = oci_fetch_assoc($stmt)) {
    $items[] = $item;
}

// Calculate total using calculate_cart_total function if user_id is available
if ($user_id) {
    $total_stmt = oci_parse($conn, "BEGIN :total := calculate_cart_total(:user_id); END;");
    oci_bind_by_name($total_stmt, ":user_id", $user_id);
    oci_bind_by_name($total_stmt, ":total", $total, 32);
    oci_execute($total_stmt);
} else {
    // fallback: calculate total in PHP
    $total = 0;
    foreach ($items as $item) {
        $total += $item['PRICE'] * $item['QUANTITY'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Virtual Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../components/store_navbar.php'; ?>

    <div class="container mt-4">
        <h2>Shopping Cart</h2>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($items)): ?>
            <div class="alert alert-info">
                Your cart is empty. <a href="../products/index.php">Continue shopping</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <?php if ($item['IMAGE_PATH']): ?>
                                        <img src="../images/<?php echo htmlspecialchars($item['IMAGE_PATH']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['NAME']); ?>"
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($item['NAME']); ?>
                                </td>
                                <td>$<?php echo number_format($item['PRICE'], 2); ?></td>
                                <td>
                                    <form action="update.php" method="POST" class="d-flex align-items-center">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['ID']; ?>">
                                        <input type="number" name="quantity" value="<?php echo $item['QUANTITY']; ?>" 
                                               min="1" class="form-control form-control-sm" style="width: 70px;">
                                        <button type="submit" class="btn btn-sm btn-outline-primary ms-2">Update</button>
                                    </form>
                                </td>
                                <td>$<?php echo number_format($item['PRICE'] * $item['QUANTITY'], 2); ?></td>
                                <td>
                                    <form action="remove.php" method="POST" class="d-inline">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['ID']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                            <td><strong>$<?php echo number_format($total, 2); ?></strong></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="../products/index.php" class="btn btn-secondary">Continue Shopping</a>
                <form action="purchase.php" method="POST">
                    <button type="submit" class="btn btn-primary">Purchase Items</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
oci_free_statement($stmt);
oci_close($conn);
?>
