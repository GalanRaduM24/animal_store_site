<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_id = $_POST['cart_id'];
    $user_id = 1; // For now, we'll use a fixed user ID

    // Delete cart item
    $deleteQuery = "DELETE FROM cart WHERE id = :cart_id AND user_id = :user_id";
    $deleteStmt = oci_parse($conn, $deleteQuery);
    oci_bind_by_name($deleteStmt, ':cart_id', $cart_id);
    oci_bind_by_name($deleteStmt, ':user_id', $user_id);
    oci_execute($deleteStmt);
    oci_free_statement($deleteStmt);

    header('Location: view.php?success=Item removed from cart');
} else {
    header('Location: view.php?error=Invalid request');
}
oci_close($conn);
?> 