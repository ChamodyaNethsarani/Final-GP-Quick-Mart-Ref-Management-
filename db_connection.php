<?php
// Database connection details
$servername = "localhost";
$username = "root";
$password = "1234";
$dbname = "quickmart";

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
