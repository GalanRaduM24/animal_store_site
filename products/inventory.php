<?php 
require_once '../config/database.php';

// Handle stock update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $productId = $_POST['product_id'];
    $newStock = $_POST['new_stock'];
    
    $updateQuery = "UPDATE products SET stock = :stock WHERE id = :id";
    $updateStmt = oci_parse($conn, $updateQuery);
    oci_bind_by_name($updateStmt, ':stock', $newStock);
    oci_bind_by_name($updateStmt, ':id', $productId);
    
    if (oci_execute($updateStmt)) {
        $success = "Stock updated successfully!";
    } else {
        $error = "Failed to update stock.";
    }
    oci_free_statement($updateStmt);
}

// Get departments
$deptQuery = "SELECT * FROM departments ORDER BY name";
$deptStmt = oci_parse($conn, $deptQuery);
oci_execute($deptStmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../components/deposit_navbar.php'; ?>

    <div class="container mt-4">
        <div class="header">
            <h2>Inventory Management</h2>
            <div class="controls">
                <select id="departmentFilter" class="form-select me-2">
                    <option value="">All Departments</option>
                    <?php
                    while ($dept = oci_fetch_assoc($deptStmt)) {
                        echo '<option value="' . htmlspecialchars($dept['ID']) . '">' . 
                             htmlspecialchars($dept['NAME']) . '</option>';
                    }
                    ?>
                </select>
                <input type="text" id="searchInput" class="form-control me-2" placeholder="Search products...">
                <a href="add_product.php" class="btn btn-success">Add New Product</a>
            </div>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success mt-3"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger mt-3"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php
        // Reset the department statement for reuse
        oci_free_statement($deptStmt);
        $deptStmt = oci_parse($conn, $deptQuery);
        oci_execute($deptStmt);

        while ($dept = oci_fetch_assoc($deptStmt)):
        ?>
            <div class="department-section mt-4" data-department-id="<?php echo htmlspecialchars($dept['ID']); ?>">
                <div class="card">
                    <div class="card-header" onclick="toggleDepartment(this)">
                        <h3 class="mb-0"><?php echo htmlspecialchars($dept['NAME']); ?></h3>
                    </div>
                    
                    <div class="card-body">
                        <?php
                        $query = "SELECT * FROM products WHERE department_id = :dept_id ORDER BY name";
                        $stmt = oci_parse($conn, $query);
                        oci_bind_by_name($stmt, ':dept_id', $dept['ID']);
                        oci_execute($stmt);
                        
                        $hasProducts = false;
                        echo '<div class="table-responsive">';
                        echo '<table class="table">';
                        echo '<thead><tr><th>Product</th><th>Current Stock</th><th>Action</th></tr></thead>';
                        echo '<tbody>';
                        
                        while ($row = oci_fetch_assoc($stmt)) {
                            $hasProducts = true;
                            $stockClass = $row['STOCK'] < 10 ? 'text-danger fw-bold' : '';
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($row['NAME']) . '</td>';
                            echo '<td class="' . $stockClass . '">' . htmlspecialchars($row['STOCK']) . '</td>';
                            echo '<td>';
                            echo '<form method="POST" class="d-flex gap-2">';
                            echo '<input type="hidden" name="product_id" value="' . htmlspecialchars($row['ID']) . '">';
                            echo '<input type="number" name="new_stock" class="form-control form-control-sm" style="width: 100px;" min="0" value="' . htmlspecialchars($row['STOCK']) . '" required>';
                            echo '<button type="submit" name="update_stock" class="btn btn-primary btn-sm">Update</button>';
                            echo '</form>';
                            echo '</td>';
                            echo '</tr>';
                        }
                        
                        echo '</tbody></table></div>';
                        
                        if (!$hasProducts) {
                            echo '<div class="alert alert-info mb-0">No products in this department</div>';
                        }
                        
                        oci_free_statement($stmt);
                        ?>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to toggle department visibility
        function toggleDepartment(header) {
            const cardBody = header.nextElementSibling;
            if (cardBody.style.display === 'none') {
                cardBody.style.display = 'block';
            } else {
                cardBody.style.display = 'none';
            }
        }

        // Show all departments by default
        document.addEventListener('DOMContentLoaded', function() {
            const sections = document.getElementsByClassName('department-section');
            for (let section of sections) {
                section.querySelector('.card-body').style.display = 'block';
            }
        });

        // Department filter functionality
        document.getElementById('departmentFilter').addEventListener('change', function() {
            const selectedDept = this.value;
            const sections = document.getElementsByClassName('department-section');
            
            for (let section of sections) {
                if (selectedDept === '' || section.dataset.departmentId === selectedDept) {
                    section.style.display = 'block';
                } else {
                    section.style.display = 'none';
                }
            }
        });

        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const sections = document.getElementsByClassName('department-section');
            
            for (let section of sections) {
                const rows = section.getElementsByTagName('tr');
                let hasVisibleRows = false;
                
                for (let i = 1; i < rows.length; i++) {
                    const productName = rows[i].getElementsByTagName('td')[0].textContent.toLowerCase();
                    
                    if (productName.includes(searchText)) {
                        rows[i].style.display = '';
                        hasVisibleRows = true;
                    } else {
                        rows[i].style.display = 'none';
                    }
                }
                
                // Show/hide the department section based on whether it has visible products
                section.style.display = hasVisibleRows ? 'block' : 'none';
            }
        });
    </script>
</body>
</html>

<?php
oci_free_statement($deptStmt);
oci_close($conn);
?> 