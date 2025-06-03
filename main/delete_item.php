<?php
ob_start(); // Start output buffering
session_start();  // Start the session to access session variables

// Redirect if user is not logged in or is not a beneficiary
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'lender') {
    header("Location: login.php");
    exit;
}

include __DIR__ . '/../database/db.php'; 

if (!isset($_GET['item_id'])) {
    header("Location: my_items.php");
    exit;
}

include("../Medical-System/main/partials/header-1.php");

$user_id = $_SESSION['user_id'];

$item_id = intval($_GET['item_id']);
$is_admin = ($_SESSION['role'] == 'admin');


// التحقق من ملكية الأداة
$sql = "SELECT * FROM items WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['msg'] = "❌ لم يتم العثور على الأداة.";
    header("Location: my_items.php");
    exit;
}

$item = $result->fetch_assoc();
$is_owner = ($item['user_id'] == $user_id);

if (!$is_owner && !$is_admin) {
    $_SESSION['msg'] = "🚫 ليس لديك صلاحية لحذف هذه الأداة.";
    header("Location: my_items.php");
    exit;
}

// حذف الصورة من السيرفر (اختياري)
if (!empty($item['image']) && file_exists($item['image'])) {
    unlink($item['image']);
}

// تنفيذ الحذف
$delete_stmt = $conn->prepare("DELETE FROM items WHERE item_id = ?");
$delete_stmt->bind_param("i", $item_id);
$delete_stmt->execute();

$_SESSION['msg'] = "✅ تم حذف الأداة بنجاح.";
header("Location: my_items.php");
exit;
ob_end_flush(); // Send the buffered output

?>
