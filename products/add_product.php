<?php
require_once '../config/database.php';

// Get departments for dropdown
$deptQuery = "SELECT * FROM departments ORDER BY name";
$deptStmt = oci_parse($conn, $deptQuery);
oci_execute($deptStmt);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $departmentId = $_POST['department_id'];
    
    // Handle image upload
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../images/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $imagePath =  $fileName;
        }
    }
    // Insert product
    $query = "INSERT INTO products (id, name, description, price, stock, department_id, image_path) 
              VALUES (product_seq.NEXTVAL, :name, :description, :price, :stock, :department_id, :image_path)";

    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':name', $name);
    oci_bind_by_name($stmt, ':description', $description);
    oci_bind_by_name($stmt, ':price', $price);
    oci_bind_by_name($stmt, ':stock', $stock);
    oci_bind_by_name($stmt, ':department_id', $departmentId);
    oci_bind_by_name($stmt, ':image_path', $imagePath);
    
    if (oci_execute($stmt)) {
        header('Location: inventory.php?success=1');
        exit;
    } else {
        $error = "Failed to add product.";
    }
    oci_free_statement($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../components/deposit_navbar.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h2 class="mb-0">Add New Product</h2>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="name" class="form-label">Product Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="price" class="form-label">Price</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                            </div>

                            <div class="mb-3">
                                <label for="stock" class="form-label">Initial Stock</label>
                                <input type="number" class="form-control" id="stock" name="stock" min="0" required>
                            </div>

                            <div class="mb-3">
                                <label for="department_id" class="form-label">Department</label>
                                <select class="form-select" id="department_id" name="department_id" required>
                                    <option value="">Select Department</option>
                                    <?php
                                    while ($dept = oci_fetch_assoc($deptStmt)) {
                                        echo '<option value="' . htmlspecialchars($dept['ID']) . '">' . 
                                             htmlspecialchars($dept['NAME']) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="image" class="form-label">Product Image</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="inventory.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Add Product</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
oci_free_statement($deptStmt);
oci_close($conn);
?> 