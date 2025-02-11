<?php
// config.php
$servername = "localhost";
$username = "root";  // Default MySQL username
$password = "";      // Default password for localhost
$dbname = "member_system";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
return $conn;

// Ensure you create the database and the table using SQL commands:
// CREATE DATABASE member_system;
// CREATE TABLE user (
//   id INT AUTO_INCREMENT PRIMARY KEY,
//   username VARCHAR(255) NOT NULL UNIQUE,
//   password VARCHAR(255) NOT NULL
// );
?>