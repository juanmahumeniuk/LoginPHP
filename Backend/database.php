<?php
require_once 'config.php';

// Create connection
// Correct order: mysqli_connect(host, username, password, database)
$conn = mysqli_connect($servername, $username, $password, $dbname, $port);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Perform database operations here (e.g., queries, inserts, updates)

// Close connection
// mysqli_close($conn);
?>