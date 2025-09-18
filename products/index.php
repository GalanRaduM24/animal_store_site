<?php
require_once '../config/database.php';

// Sanitize and validate department filter
$department_id = isset($_GET['department_id']) && is_numeric($_GET['department_id']) ? $_GET['department_id'] : null;

// Prepare product query
if ($department_id) {
    $query = "SELECT p.*, d.name AS department_name 
              FROM products p 
              JOIN departments d ON p.department_id = d.id 
              WHERE p.department_id = :department_id 
              ORDER BY p.name";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':department_id', $department_id);
} else {
    $query = "SELECT p.*, d.name AS department_name 
              FROM products p 
              JOIN departments d ON p.department_id = d.id 
              ORDER BY p.name";
    $stmt = oci_parse($conn, $query);
}

oci_execute($stmt);

// Fetch departments for filter dropdown
$deptQuery = "SELECT * FROM departments ORDER BY name";
$deptStmt = oci_parse($conn, $deptQuery);
oci_execute($deptStmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Products - Virtual Store</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../components/store_navbar.php'; ?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col">
            <h2>Products</h2>
        </div>
        <div class="col-auto">
            <form method="GET" class="d-flex">
                <select name="department_id" class="form-select me-2" onchange="this.form.submit()">
                    <option value="">All Departments</option>
                    <?php while ($dept = oci_fetch_assoc($deptStmt)): ?>
                        <?php 
                            $selected = ($department_id == $dept['ID']) ? 'selected' : '';
                            $deptId = htmlspecialchars($dept['ID']);
                            $deptName = htmlspecialchars($dept['NAME']);
                        ?>
                        <option value="<?php echo $deptId; ?>" <?php echo $selected; ?>>
                            <?php echo $deptName; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </form>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php 
        $hasProducts = false;
        while ($product = oci_fetch_assoc($stmt)): 
            $hasProducts = true;
        ?>
            <div class="col">
                <div class="card h-100">
                    <?php if ($product['IMAGE_PATH']): ?>
                        <img src="../images/<?php echo htmlspecialchars($product['IMAGE_PATH']); ?>" 
                             class="card-img-top" alt="<?php echo htmlspecialchars($product['NAME']); ?>">
                    <?php else: ?>
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                            <span class="text-muted">No image available</span>
                        </div>
                    <?php endif; ?>

                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($product['NAME']); ?></h5>
                        <p class="card-text text-muted"><?php echo htmlspecialchars($product['DEPARTMENT_NAME']); ?></p>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($product['DESCRIPTION'])); ?></p>
                        <p class="card-text">
                            <strong>Price:</strong> $<?php echo number_format($product['PRICE'], 2); ?><br>
                            <strong>Stock:</strong> <?php echo (int)$product['STOCK']; ?> units
                        </p>

                        <form action="../cart/add.php" method="POST" class="mt-3">
                            <input type="hidden" name="product_id" value="<?php echo (int)$product['ID']; ?>">
                            <div class="input-group mb-3">
                                <input type="number" name="quantity" class="form-control" value="1" min="1" max="<?php echo (int)$product['STOCK']; ?>">
                                <button type="submit" class="btn btn-primary">Add to Cart</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>

        <?php if (!$hasProducts): ?>
            <div class="col">
                <div class="alert alert-info w-100">
                    No products found for this department.
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
oci_free_statement($stmt);
oci_free_statement($deptStmt);
oci_close($conn);
?>
