<?php
$host = getenv('HOST');
$username = getenv('USERNAME');
$password = getenv('PASSWORD');

$conn = oci_connect($username, $password, $host);

if (!$conn) {
    $e = oci_error();
    die("Connection failed: " . $e['message']);
}
?> 