<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = 1; // For now, we'll use a fixed user ID

    // Get all cart items
    $cartQuery = "SELECT c.*, p.stock, p.name 
                 FROM cart c 
                 JOIN products p ON c.product_id = p.id 
                 WHERE c.user_id = :user_id";
    $cartStmt = oci_parse($conn, $cartQuery);
    oci_bind_by_name($cartStmt, ':user_id', $user_id);
    oci_execute($cartStmt);

    $error = null;
    $items = [];
    while ($item = oci_fetch_assoc($cartStmt)) {
        $items[] = $item;
        if ($item['STOCK'] < $item['QUANTITY']) {
            $error = "Not enough stock available for " . $item['NAME'];
            break;
        }
    }
    oci_free_statement($cartStmt);

    if ($error) {
        header('Location: view.php?error=' . urlencode($error));
        exit;
    }

    // Process each item
    foreach ($items as $item) {
        // Call the process_purchase procedure
        $procQuery = "BEGIN process_purchase(:product_id, :quantity); END;";
        $procStmt = oci_parse($conn, $procQuery);
        oci_bind_by_name($procStmt, ':product_id', $item['PRODUCT_ID']);
        oci_bind_by_name($procStmt, ':quantity', $item['QUANTITY']);
        oci_execute($procStmt);
        oci_free_statement($procStmt);
    }

    // Clear the cart
    $clearQuery = "DELETE FROM cart WHERE user_id = :user_id";
    $clearStmt = oci_parse($conn, $clearQuery);
    oci_bind_by_name($clearStmt, ':user_id', $user_id);
    oci_execute($clearStmt);
    oci_free_statement($clearStmt);

    header('Location: view.php?success=Purchase completed successfully');
} else {
    header('Location: view.php');
}
oci_close($conn);
?> 