<?php
include '../config/database.php';
include '../components/store_navbar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Departments</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        h2 {
            margin-bottom: 15px;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            margin: 8px 0;
        }

        a {
            text-decoration: none;
            color: #007BFF;
        }

        a:hover {
            text-decoration: underline;
        }

        .error {
            color: red;
        }

        .empty {
            color: #555;
            font-style: italic;
        }
    </style>
</head>
<body>

<h2>Departments</h2>

<ul>
<?php
$query = 'SELECT id, name FROM departments';
$stmt = oci_parse($conn, $query);

if (!$stmt) {
    $e = oci_error($conn);
    echo "<p class='error'>Error parsing query: " . htmlentities($e['message']) . "</p>";
} elseif (!oci_execute($stmt)) {
    $e = oci_error($stmt);
    echo "<p class='error'>Error executing query: " . htmlentities($e['message']) . "</p>";
} else {
    $hasResults = false;
    while ($row = oci_fetch_assoc($stmt)) {
        $hasResults = true;
        $id = htmlspecialchars($row['ID']);
        $name = htmlspecialchars($row['NAME']);
        echo "<li><a href='../products/index.php?department_id={$id}'>{$name}</a></li>";
    }

    if (!$hasResults) {
        echo "<li class='empty'>No departments found.</li>";
    }

    oci_free_statement($stmt);
}

oci_close($conn);
?>
</ul>

</body>
</html>
