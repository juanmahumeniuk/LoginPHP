<?php
require_once 'config.php';

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}


// Perform database operations here (e.g., queries, inserts, updates)

// Close connection
// mysqli_close($conn);
?>