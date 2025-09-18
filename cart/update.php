<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_id = $_POST['cart_id'];
    $quantity = $_POST['quantity'];
    $user_id = 1; // For now, we'll use a fixed user ID

    // Get current cart item
    $checkQuery = "SELECT c.*, p.stock 
                  FROM cart c 
                  JOIN products p ON c.product_id = p.id 
                  WHERE c.id = :cart_id AND c.user_id = :user_id";
    $checkStmt = oci_parse($conn, $checkQuery);
    oci_bind_by_name($checkStmt, ':cart_id', $cart_id);
    oci_bind_by_name($checkStmt, ':user_id', $user_id);
    oci_execute($checkStmt);
    $cartItem = oci_fetch_assoc($checkStmt);
    oci_free_statement($checkStmt);

    if (!$cartItem) {
        header('Location: view.php?error=Cart item not found');
        exit;
    }

    if ($cartItem['STOCK'] < $quantity) {
        header('Location: view.php?error=Not enough stock available');
        exit;
    }

    // Update quantity
    $updateQuery = "UPDATE cart SET quantity = :quantity 
                   WHERE id = :cart_id AND user_id = :user_id";
    $updateStmt = oci_parse($conn, $updateQuery);
    oci_bind_by_name($updateStmt, ':quantity', $quantity);
    oci_bind_by_name($updateStmt, ':cart_id', $cart_id);
    oci_bind_by_name($updateStmt, ':user_id', $user_id);
    oci_execute($updateStmt);
    oci_free_statement($updateStmt);

    header('Location: view.php?success=Cart updated');
} else {
    header('Location: view.php');
}
oci_close($conn);
?>
