<?php
session_start();

include(__DIR__ . "/../main/partials/header-1.php");
// Redirect if user is not logged in or is not a beneficiary
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit;
}
include __DIR__ . '/../database/db.php';

$user_id = $_SESSION['user_id'];
// Exclude items already requested by this user with pending/accepted status
$exclude_stmt = $conn->prepare(
    "SELECT item_id FROM reservations WHERE user_id = ? AND status IN ('pending', 'accepted')"
);
$exclude_stmt->bind_param("i", $user_id);
$exclude_stmt->execute();
$exclude_result = $exclude_stmt->get_result();
$excluded_ids = [];
while ($row = $exclude_result->fetch_assoc()) {
    $excluded_ids[] = $row['item_id'];
}
$exclude_stmt->close();

// Build SQL for available items, excluding those
if (empty($excluded_ids)) {
    $items_sql = "SELECT item_id, name FROM items WHERE reserve_status = 'available' ORDER BY name ASC";
    $items_stmt = $conn->prepare($items_sql);
} else {
    // Create a comma-separated list of placeholders based on count
    $placeholders = implode(',', array_fill(0, count($excluded_ids), '?'));
    $items_sql = "SELECT item_id, name FROM items WHERE reserve_status = 'available' AND item_id NOT IN ($placeholders) ORDER BY name ASC";
    $items_stmt = $conn->prepare($items_sql);
    // Bind excluded ids dynamically
    $types = str_repeat('i', count($excluded_ids));
    $items_stmt->bind_param($types, ...$excluded_ids);
}
$items_stmt->execute();
$items_result = $items_stmt->get_result();
$available_items = $items_result->fetch_all(MYSQLI_ASSOC);
$items_stmt->close();

$message = empty($available_items) ? "❌ لا توجد أدوات متاحة حاليًا." : "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $item_id    = intval($_POST['item_id']);
    // Re-check this specific item
    $chk = $conn->prepare(
        "SELECT COUNT(*) FROM reservations WHERE user_id = ? AND item_id = ? AND status IN ('pending', 'accepted')"
    );
    $chk->bind_param("ii", $user_id, $item_id);
    $chk->execute();
    $already = $chk->get_result()->fetch_row()[0];
    $chk->close();

    if ($already > 0) {
        $message = "❌ لقد قدمت طلبًا على هذه الأداة بالفعل.";
    } else {
        $start_date = $_POST['start_date'];
        $end_date   = $_POST['end_date'];
        if (strtotime($start_date) >= strtotime($end_date)) {
            $message = "❌ تاريخ النهاية يجب أن يكون بعد تاريخ البداية.";
        } else {
            // File upload
            $document_path = "";
            if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['pdf','jpg','jpeg','png'])) {
                    $message = "❌ فقط الملفات من نوع PDF أو صورة يمكن رفعها.";
                } else {
                    $upload_dir = __DIR__ . "/uploads/";
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                    $filename = uniqid() . "_" . basename($_FILES['document']['name']);
                    move_uploaded_file($_FILES['document']['tmp_name'], $upload_dir . $filename);
                    $document_path = "uploads/" . $filename;
                }
            }

            if (empty($message)) {
                $ins = $conn->prepare(
                    "INSERT INTO reservations (item_id, user_id, status, start_date, end_date, documents_path) VALUES (?, ?, 'pending', ?, ?, ?)"
                );
                $ins->bind_param("iisss", $item_id, $user_id, $start_date, $end_date, $document_path);
                if ($ins->execute()) {
                    $message = "✅ تم تقديم الطلب بنجاح!";
                    // Remove the requested item from available list
                    foreach ($available_items as $i => $it) {
                        if ($it['item_id'] == $item_id) unset($available_items[$i]);
                    }
                } else {
                    $message = "❌ حدث خطأ أثناء تقديم الطلب.";
                }
                $ins->close();
            }
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>طلب استعارة أداة</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body {
        background: linear-gradient(to bottom, #f0f8ff, #ffffff);
        font-family: 'Tajawal', sans-serif;
        margin: 0;
        padding: 0;
    }
    .form-container {
      max-width: 700px;
      margin: 40px auto;
      background: #ffffff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 0 12px rgba(0, 0, 0, 0.08);
    }
    h2 {
      text-align: center;
      color: #0077cc;
      margin-bottom: 25px;
    }
    label {
      display: block;
      margin-bottom: 5px;
      font-weight: 600;
    }
    input, select {
      width: 100%;
      padding: 10px;
      margin-bottom: 20px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }
    button {
      background-color: #0077cc;
      color: white;
      padding: 12px 24px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: bold;
      transition: background-color 0.3s ease;
    }
    button:hover {
      background-color: #005fa3;
    }
    .message {
      text-align: center;
      margin-bottom: 20px;
      font-weight: bold;
    }
    .success {
      color: #2e7d32;
    }
    .error {
      color: #c62828;
    }
  </style>
</head>
<body>
<div class="container">
  <div class="form-container">
    <h2>📝 تقديم طلب استعارة</h2>
    <?php if (!empty($message)): ?>
      <p class="message <?= strpos($message, '✅') !== false ? 'success' : 'error' ?>"><?= $message ?></p>
    <?php endif; ?>
    <?php if (!empty($available_items)): ?>
      <form method="POST" enctype="multipart/form-data">
      <label>اختر الأداة:</label>
<select name="item_id" required>
  <option value="">-- اختر أداة --</option>
  <?php 
    $selected_id = $_GET['item_id'] ?? null;
    foreach ($available_items as $item): 
      $selected = ($item['item_id'] == $selected_id) ? 'selected' : '';
  ?>
    <option value="<?= $item['item_id'] ?>" <?= $selected ?>>
      <?= htmlspecialchars($item['name']) ?>
    </option>
  <?php endforeach; ?>
</select>

        <label>تاريخ البداية:</label>
        <input type="date" name="start_date" required>
        <label>تاريخ النهاية:</label>
        <input type="date" name="end_date" required>
        <label>تحميل وثيقة (PDF أو صورة):</label>
        <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png">
        <button type="submit">📤 إرسال الطلب</button>
      </form>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
