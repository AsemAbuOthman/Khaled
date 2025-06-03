<?php
ob_start();
session_start();
include("../Medical-System/main/partials/header-1.php");
require_once __DIR__ . '/../database/db.php';

// Redirect if user is not logged in or not admin
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$user_id = filter_input(INPUT_GET, 'user_id', FILTER_SANITIZE_NUMBER_INT);

// Prepare SQL to delete user
$sql = "DELETE FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);

if ($stmt->execute()) {
    echo "User deleted successfully!";
    header(header: 'Location: all_users.php');
    exit;
} else {
    echo "Error: " . $stmt->error;
}
ob_end_flush();

?>
