<?php
$DB_HOST     = "localhost";
$DB_USERNAME = "root";
$DB_PASSWORD = "";
$DB_NAME     = "medical-system";

// Create connection
$conn = new mysqli($DB_HOST, $DB_USERNAME, $DB_PASSWORD, $DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

