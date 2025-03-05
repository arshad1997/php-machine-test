<?php
$host = "localhost";
$dbname = "machine_test_db";
$username = "root"; 
$password = "";

// Create a connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
