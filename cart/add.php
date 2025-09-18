<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $user_id = 1; // For now, we'll use a fixed user ID

    // Check if product exists and has enough stock
    $checkQuery = "SELECT stock FROM products WHERE id = :product_id";
    $checkStmt = oci_parse($conn, $checkQuery);
    oci_bind_by_name($checkStmt, ':product_id', $product_id);
    oci_execute($checkStmt);
    $product = oci_fetch_assoc($checkStmt);
    oci_free_statement($checkStmt);

    if (!$product) {
        header('Location: ../products/index.php?error=Product not found');
        exit;
    }

    if ($product['STOCK'] < $quantity) {
        header('Location: ../products/index.php?error=Not enough stock available');
        exit;
    }

    // Check if product is already in cart
    $checkCartQuery = "SELECT * FROM cart WHERE product_id = :product_id AND user_id = :user_id";
    $checkCartStmt = oci_parse($conn, $checkCartQuery);
    oci_bind_by_name($checkCartStmt, ':product_id', $product_id);
    oci_bind_by_name($checkCartStmt, ':user_id', $user_id);
    oci_execute($checkCartStmt);
    $cartItem = oci_fetch_assoc($checkCartStmt);
    oci_free_statement($checkCartStmt);

    if ($cartItem) {
        // Update quantity if product is already in cart
        $updateQuery = "UPDATE cart SET quantity = quantity + :quantity 
                       WHERE product_id = :product_id AND user_id = :user_id";
        $updateStmt = oci_parse($conn, $updateQuery);
        oci_bind_by_name($updateStmt, ':quantity', $quantity);
        oci_bind_by_name($updateStmt, ':product_id', $product_id);
        oci_bind_by_name($updateStmt, ':user_id', $user_id);
        oci_execute($updateStmt);
        oci_free_statement($updateStmt);
    } else {
        // Add new item to cart
        $insertQuery = "INSERT INTO cart (id, user_id, product_id, quantity) 
                       VALUES (cart_seq.NEXTVAL, :user_id, :product_id, :quantity)";
        $insertStmt = oci_parse($conn, $insertQuery);
        oci_bind_by_name($insertStmt, ':user_id', $user_id);
        oci_bind_by_name($insertStmt, ':product_id', $product_id);
        oci_bind_by_name($insertStmt, ':quantity', $quantity);
        oci_execute($insertStmt);
        oci_free_statement($insertStmt);
    }

    header('Location: view.php?success=Product added to cart');
} else {
    header('Location: ../products/index.php');
}
oci_close($conn);
?>
