<?php
ob_start();
session_start();
include __DIR__ . '/../database/db.php';

// Redirect if not logged in or not a lender
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'lender') {
    header("Location: login.php");
    exit;
}

// Validate item_id
if (!isset($_GET['item_id']) || !is_numeric($_GET['item_id'])) {
    header("Location: my_items.php");
    exit;

}

include("../main/partials/header-1.php");

$item_id = intval($_GET['item_id']);

// Fetch item
$sql = "SELECT * FROM items WHERE item_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo "<p style='padding:20px;color:red;'>الأداة غير موجودة.</p>";
    exit;
}
$item = $result->fetch_assoc();

// Permission check
$is_owner = $_SESSION['user_id'] == $item['user_id'];
$is_admin = $_SESSION['role'] == 'admin';
if (!$is_owner && !$is_admin) {
    echo "<p style='padding:20px;color:red;'>ليس لديك صلاحية التعديل.</p>";
    exit;
}

$message = "";
$image_path = $item['image'];

// Handle POST update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name        = trim($_POST['name']);
    $description = trim($_POST['description']);
    $status = $_POST['status'] ?? 'new';
  $allowed_statuses = ['new', 'good', 'used'];
if (!in_array($status, $allowed_statuses, true)) {
    $message = "❌ الحالة غير صالحة.";
}
    // If new image uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        // 2MB max
        if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $message = "❌ حجم الصورة أكبر من 2MB.";
        } else {
            $tmp  = $_FILES['image']['tmp_name'];
            $info = getimagesize($tmp);
            if (!$info) {
                $message = "❌ الملف ليس صورة صالحة.";
            } else {
                // Prepare upload dir
                $upload_dir = __DIR__ . "/uploads/";
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                list($width, $height, $type) = $info;
                $ext       = image_type_to_extension($type, false);
                $filename  = uniqid() . ".$ext";
                $dest_path = $upload_dir . $filename;

                // Move & then maybe resize
                if (move_uploaded_file($tmp, $dest_path)) {
                    // Resize if larger than 1024px
                    if ($width > 1024 || $height > 1024) {
                        resizeImage($dest_path, 1024, 1024);
                    }
                    // Store web-relative path
                    $image_path = "uploads/" . $filename;
                } else {
                    $message = "❌ فشل حفظ الصورة.";
                }
            }
        }
    }

    // If no error, update DB
    if ($message === "") {
        $update = $conn->prepare(
            "UPDATE items 
             SET name=?, description=?, status=?, image=? 
             WHERE item_id=?"
        );
        $update->bind_param("ssssi", $name, $description, $status, $image_path, $item_id);
        if ($update->execute()) {
            header("Location: view_item.php?item_id=$item_id");
            exit;
        } else {
            $message = "❌ حدث خطأ أثناء التحديث.";
        }
    }
}

/**
 * Resize an image file to fit within maxW × maxH, preserving aspect ratio.
 */
function resizeImage($file, $maxW, $maxH) {
    [$w, $h, $type] = getimagesize($file);
    $ratio = min($maxW/$w, $maxH/$h);
    $newW  = (int)($w * $ratio);
    $newH  = (int)($h * $ratio);

    $dst = imagecreatetruecolor($newW, $newH);
    switch ($type) {
        case IMAGETYPE_JPEG:
            $src = imagecreatefromjpeg($file);
            break;
        case IMAGETYPE_PNG:
            $src = imagecreatefrompng($file);
            imagecolortransparent($dst, imagecolorallocatealpha($dst, 0,0,0,127));
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            break;
        default:
            return;
    }

    imagecopyresampled($dst, $src, 0,0, 0,0, $newW,$newH, $w,$h);
    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($dst, $file, 85);
            break;
        case IMAGETYPE_PNG:
            imagepng($dst, $file);
            break;
    }
    imagedestroy($src);
    imagedestroy($dst);
}
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>تعديل الأداة</title>
  <link rel="stylesheet" href="style.css">
  <style>
    :root {
      --border-radius: 8px;
      --primary-color: #0077cc;
      --secondary-color: #005fa3;
    }
    body {
      background: linear-gradient(to bottom, #f0f8ff, #ffffff);
      font-family: 'Tajawal', sans-serif;
      margin: 0; padding: 0;
    }
    .edit-container {
      max-width: 800px;
      margin: 40px auto;
      background: #fff;
      padding: 30px;
      border-radius: var(--border-radius);
      box-shadow: 0 0 15px rgba(0,0,0,0.08);
    }
    .edit-container h2 {
      text-align: center;
      margin-bottom: 20px;
      color: var(--primary-color);
    }
    .edit-container label {
      display: block;
      margin-bottom: 5px;
      font-weight: 600;
      color: #333;
    }
    .edit-container input,
    .edit-container select,
    .edit-container textarea {
      width: 100%;
      padding: 10px;
      margin-bottom: 20px;
      border: 1px solid #ccc;
      border-radius: var(--border-radius);
    }
    .edit-container button {
      background-color: var(--primary-color);
      color: white;
      padding: 12px 24px;
      border: none;
      border-radius: var(--border-radius);
      cursor: pointer;
      font-weight: bold;
    }
    .edit-container button:hover {
      background-color: var(--secondary-color);
    }
    .edit-container .message {
      padding: 10px;
      margin-bottom: 20px;
      border-radius: var(--border-radius);
      font-weight: bold;
    }
    .edit-container .message.success {
      background: #e6ffed;
      color: #2d6a4f;
    }
    .edit-container .message.error {
      background: #ffe6e6;
      color: #9d0208;
    }
    /* Preview styling */
    .preview {
      max-width: 100%;
      max-height: 300px;
      object-fit: contain;
      display: block;
      margin: 0 auto 20px;
      border-radius: var(--border-radius);
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>

  <div class="container edit-container">
    <h2>✏️ تعديل الأداة</h2>

    <?php if ($message): ?>
      <div class="message <?= strpos($message, '✅') !== false ? 'success' : 'error' ?>">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <?php if (!empty($item['image']) && file_exists($item['image'])): ?>
        <img
          src="<?= htmlspecialchars($item['image']) ?>"
          alt="صورة الأداة"
          class="preview"
        >
      <?php endif; ?>

      <label>اسم الأداة:</label>
      <input
        type="text"
        name="name"
        value="<?= htmlspecialchars($item['name']) ?>"
        required
      >

      <label>الوصف:</label>
      <textarea name="description" required><?= htmlspecialchars($item['description']) ?></textarea>

      <label>الحالة:</label>
      <select name="status" required>
        <option value="new"      <?= $item['status']=='new'      ? 'selected' : '' ?>>جديدة</option>
        <option value="good" <?= $item['status']=='good' ? 'selected' : '' ?>>جيدة جداً</option>
        <option value="used" <?= $item['status']=='used' ? 'selected' : '' ?>>مستخدمة قليلاً</option>
      </select>

      <label>تغيير الصورة (اختياري):</label>
      <input type="file" name="image" accept="image/*">

      <button type="submit">💾 حفظ التعديلات</button>
    </form>
  </div>

</body>
</html>
