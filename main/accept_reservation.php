<?php
session_start();
require_once __DIR__ . '/../database/db.php';

// Check if user is authenticated as lender or admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'lender' && $_SESSION['role'] !== 'admin')) {
    $_SESSION['error'] = "غير مصرح بالوصول.";
    header("Location: login.php");
    exit;
}

// Validate request method and parameters
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['reservation_id'])) {
    $_SESSION['error'] = "طلب غير صالح.";
    header("Location: my_items.php");
    exit;
}

$reservation_id = intval($_POST['reservation_id']);
$user_id = $_SESSION['user_id'];


// Fetch reservation and item details
$sql = "SELECT r.*, i.user_id as item_owner_id 
        FROM reservations r 
        JOIN items i ON r.item_id = i.item_id 
        WHERE r.reservation_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $reservation_id);
$stmt->execute();
$result = $stmt->get_result();
$reservation = $result->fetch_assoc();

if (!$reservation) {
    $_SESSION['error'] = "طلب الحجز غير موجود.";
    header("Location: my_items.php");
    exit;
}

// Validate reservation status
if ($reservation['status'] !== 'pending') {
    $_SESSION['error'] = "لا يمكن قبول طلب حجز غير معلّق.";
    header("Location: view_item.php?item_id=" . $reservation['item_id']);
    exit;
}

// Authorization check
if ($_SESSION['role'] !== 'admin' && $reservation['item_owner_id'] != $user_id) {
    $_SESSION['error'] = "غير مصرح لك بتنفيذ هذا الإجراء.";
    header("Location: view_item.php?item_id=" . $reservation['item_id']);
    exit;
}

// Update reservation and item status
$conn->begin_transaction();
try {
    // Accept reservation
    $updateRes = $conn->prepare("UPDATE reservations SET status = 'accepted' WHERE reservation_id = ?");
    $updateRes->bind_param("i", $reservation_id);
    $updateRes->execute();

    // Reserve item
    $updateItem = $conn->prepare("UPDATE items SET reserve_status = 'reserved' WHERE item_id = ?");
    $updateItem->bind_param("i", $reservation['item_id']);
    $updateItem->execute();

    $conn->commit();
    $_SESSION['success'] = "تم قبول طلب الحجز بنجاح.";
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "حدث خطأ أثناء معالجة الطلب.";
}

$conn->close();
header("Location: view_item.php?item_id=" . $reservation['item_id']);
exit;
?>