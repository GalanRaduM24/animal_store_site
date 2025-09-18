<?php
// Include the connection file
include 'config/database.php';

if ($conn) {
    echo "Connection to Oracle database successful!";

    // Display tables & procedures
    $query = "SELECT object_name, object_type FROM user_objects WHERE object_type IN ('TABLE', 'PROCEDURE')";
    $stid = oci_parse($conn, $query);
    oci_execute($stid);

    echo "<h2>Tables & Procedures:</h2>";
    echo "<ul>";
    while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
        echo "<li>" . htmlspecialchars($row['OBJECT_NAME']) . " (" . htmlspecialchars($row['OBJECT_TYPE']) . ")</li>";
    }
    echo "</ul>";

    oci_free_statement($stid);

    // Show procedure and function details
    $procFuncQuery = "SELECT object_name, object_type, status, created, last_ddl_time 
                      FROM user_objects 
                      WHERE object_type IN ('PROCEDURE', 'FUNCTION', 'TRIGGER')";
    $stid = oci_parse($conn, $procFuncQuery);
    oci_execute($stid);

    echo "<h2>Procedures, Functions & Triggers Details:</h2>";
    echo "<table border='1'>";
    echo "<tr><th>Name</th><th>Type</th><th>Status</th><th>Created</th><th>Last DDL Time</th></tr>";
    while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['OBJECT_NAME']) . "</td>";
        echo "<td>" . htmlspecialchars($row['OBJECT_TYPE']) . "</td>";
        echo "<td>" . htmlspecialchars($row['STATUS']) . "</td>";
        echo "<td>" . htmlspecialchars($row['CREATED']) . "</td>";
        echo "<td>" . htmlspecialchars($row['LAST_DDL_TIME']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    oci_free_statement($stid);

        // Show sequence details
    $seqQuery = "SELECT sequence_name, min_value, max_value, increment_by, last_number 
                 FROM user_sequences";
    $stid = oci_parse($conn, $seqQuery);
    oci_execute($stid);

    echo "<h2>Sequences Details:</h2>";
    echo "<table border='1'>";
    echo "<tr><th>Name</th><th>Min Value</th><th>Max Value</th><th>Increment By</th><th>Last Number</th></tr>";
    while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['SEQUENCE_NAME']) . "</td>";
        echo "<td>" . htmlspecialchars($row['MIN_VALUE']) . "</td>";
        echo "<td>" . htmlspecialchars($row['MAX_VALUE']) . "</td>";
        echo "<td>" . htmlspecialchars($row['INCREMENT_BY']) . "</td>";
        echo "<td>" . htmlspecialchars($row['LAST_NUMBER']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    oci_free_statement($stid);

    // Show contents of (departments, product, cart) tables
    $tables = ['departments', 'products', 'cart'];
    foreach ($tables as $table) {
        $query = "SELECT * FROM $table";
        $stid = oci_parse($conn, $query);
        oci_execute($stid);

        echo "<h2>Contents of $table:</h2>";
        echo "<table border='1'>";
        echo "<tr>";

        // Fetch column names
        $numCols = oci_num_fields($stid);
        for ($i = 1; $i <= $numCols; $i++) {
            $colName = oci_field_name($stid, $i);
            echo "<th>" . htmlspecialchars($colName) . "</th>";
        }
        echo "</tr>";

        // Fetch rows
        while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            echo "<tr>";
            foreach ($row as $item) {
                echo "<td>" . htmlspecialchars($item) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";

        oci_free_statement($stid);
    }

} else {
    $e = oci_error();
    echo "Connection failed: " . $e['message'];
}
?>
