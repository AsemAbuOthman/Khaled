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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>تعديل الأداة</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #4361ee;
      --primary-dark: #3a56d4;
      --accent: #4cc9f0;
      --success: #4ade80;
      --danger: #f43f5e;
      --warning: #f59e0b;
      --gray-100: #f8f9fa;
      --gray-200: #e9ecef;
      --gray-300: #dee2e6;
      --gray-700: #495057;
      --gray-900: #212529;
      --radius: 12px;
      --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      --transition: all 0.3s ease;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      background: linear-gradient(135deg, #f5f7ff 0%, #eef2ff 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
      color: var(--gray-900);
      line-height: 1.6;
    }

    .edit-container {
      max-width: 800px;
      width: 100%;
      background: white;
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      overflow: hidden;
      transition: var(--transition);
    }

    .edit-container:hover {
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    }

    .header {
      background: linear-gradient(90deg, var(--primary), var(--accent));
      color: white;
      padding: 30px;
      text-align: center;
      position: relative;
    }

    .header h2 {
      font-size: 2rem;
      margin-bottom: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 15px;
    }

    .header p {
      font-size: 1.1rem;
      opacity: 0.9;
      max-width: 600px;
      margin: 0 auto;
    }

    .form-content {
      padding: 40px;
    }

    .message {
      padding: 15px 20px;
      border-radius: var(--radius);
      margin-bottom: 25px;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 12px;
      box-shadow: var(--shadow);
    }

    .message.success {
      background-color: rgba(74, 222, 128, 0.15);
      color: #15803d;
      border-left: 4px solid var(--success);
    }

    .message.error {
      background-color: rgba(244, 63, 94, 0.15);
      color: var(--danger);
      border-left: 4px solid var(--danger);
    }

    .image-section {
      text-align: center;
      margin-bottom: 30px;
    }

    .preview-container {
      position: relative;
      margin: 0 auto 20px;
      max-width: 100%;
      height: 300px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: var(--radius);
      overflow: hidden;
      box-shadow: var(--shadow);
      background: var(--gray-100);
    }

    .preview {
      max-width: 100%;
      max-height: 100%;
      object-fit: contain;
      transition: var(--transition);
      display: block;
    }

    .preview-placeholder {
      font-size: 3rem;
      color: var(--gray-300);
    }

    .form-group {
      margin-bottom: 25px;
      position: relative;
    }

    .form-group label {
      display: block;
      margin-bottom: 10px;
      font-weight: 600;
      color: var(--gray-700);
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .form-control {
      width: 100%;
      padding: 14px 18px;
      border-radius: 8px;
      border: 2px solid var(--gray-200);
      font-size: 16px;
      transition: var(--transition);
      background: var(--gray-100);
    }

    .form-control:focus {
      border-color: var(--primary);
      outline: none;
      background: white;
      box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
    }

    textarea.form-control {
      min-height: 120px;
      resize: vertical;
    }

    select.form-control {
      appearance: none;
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
      background-repeat: no-repeat;
      background-position: left 15px center;
      background-size: 16px 12px;
      padding-right: 15px;
    }

    .file-upload {
      position: relative;
      display: inline-block;
      width: 100%;
    }

    .file-upload-label {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 14px 18px;
      border-radius: 8px;
      border: 2px solid var(--gray-200);
      background: var(--gray-100);
      font-size: 16px;
      cursor: pointer;
      transition: var(--transition);
    }

    .file-upload-label:hover {
      background: var(--gray-200);
    }

    .file-upload-label span {
      color: var(--gray-700);
    }

    .file-upload-label i {
      color: var(--primary);
    }

    .file-input {
      position: absolute;
      width: 100%;
      height: 100%;
      top: 0;
      right: 0;
      opacity: 0;
      cursor: pointer;
    }

    .info-text {
      font-size: 14px;
      color: var(--gray-700);
      margin-top: 8px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 14px 28px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
      transition: var(--transition);
      border: none;
      cursor: pointer;
      gap: 10px;
      font-size: 16px;
      width: 100%;
      margin-top: 10px;
    }

    .btn-primary {
      background: var(--primary);
      color: white;
      position: relative;
    }

    .btn-primary:hover {
      background: var(--primary-dark);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
    }

    .btn-primary.loading {
      color: transparent;
      pointer-events: none;
    }

    .btn-primary.loading::after {
      content: "";
      position: absolute;
      width: 20px;
      height: 20px;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      margin: auto;
      border: 3px solid transparent;
      border-top-color: white;
      border-radius: 50%;
      animation: button-loading-spinner 1s ease infinite;
    }

    @keyframes button-loading-spinner {
      from {
        transform: rotate(0turn);
      }
      to {
        transform: rotate(1turn);
      }
    }

    .back-container {
      text-align: center;
      margin-top: 25px;
    }

    .btn-outline {
      background: transparent;
      color: var(--primary);
      border: 2px solid var(--primary);
    }

    .btn-outline:hover {
      background: rgba(67, 97, 238, 0.1);
      transform: translateY(-2px);
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }

    .validation-error {
      color: var(--danger);
      font-size: 14px;
      margin-top: 5px;
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .form-control.error {
      border-color: var(--danger);
    }

    @media (max-width: 768px) {
      .form-row {
        grid-template-columns: 1fr;
        gap: 15px;
      }
      
      .form-content {
        padding: 25px 20px;
      }
      
      .header {
        padding: 20px 15px;
      }
      
      .header h2 {
        font-size: 1.7rem;
      }
      
      .preview-container {
        height: 250px;
      }
    }

    @media (max-width: 480px) {
      .header h2 {
        font-size: 1.5rem;
      }
      
      .header p {
        font-size: 1rem;
      }
      
      .form-content {
        padding: 20px 15px;
      }
    }
  </style>
</head>
<body>
  <div class="edit-container">
    <div class="header">
      <h2><i class="fas fa-edit"></i> تعديل الأداة</h2>
      <p>قم بتحديث معلومات الأداة الخاصة بك للحفاظ على دقة المعلومات</p>
    </div>
    
    <div class="form-content">
      <?php if ($message): ?>
        <div class="message <?= strpos($message, '✅') !== false ? 'success' : 'error' ?>">
          <i class="fas <?= strpos($message, '✅') !== false ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
          <?= htmlspecialchars($message) ?>
        </div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data" id="item-form">
        <div class="image-section">
          <div class="preview-container">
            <?php if (!empty($item['image']) && file_exists($item['image'])): ?>
              <img
                src="<?= htmlspecialchars($item['image']) ?>"
                alt="صورة الأداة"
                class="preview"
                id="image-preview"
              >
            <?php else: ?>
              <i class="fas fa-image preview-placeholder" id="image-placeholder"></i>
            <?php endif; ?>
          </div>
          
          <div class="file-upload">
            <label class="file-upload-label">
              <span id="file-name">تغيير الصورة (اختياري)</span>
              <i class="fas fa-cloud-upload-alt"></i>
            </label>
            <input type="file" id="image" name="image" class="file-input" accept="image/*">
          </div>
          <p class="info-text"><i class="fas fa-info-circle"></i> يُسمح بملفات الصور فقط (JPEG, PNG) بحجم أقصى 2MB</p>
          <div class="validation-error" id="image-error" style="display: none;"></div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label for="name"><i class="fas fa-tag"></i> اسم الأداة</label>
            <input
              type="text"
              id="name"
              name="name"
              class="form-control"
              value="<?= htmlspecialchars($item['name']) ?>"
              placeholder="أدخل اسم الأداة"
              required
            >
            <div class="validation-error" id="name-error" style="display: none;">
              <i class="fas fa-exclamation-circle"></i>
              <span>اسم الأداة يجب أن يكون على الأقل 3 أحرف</span>
            </div>
          </div>
          
          <div class="form-group">
            <label for="status"><i class="fas fa-star"></i> حالة الأداة</label>
            <select id="status" name="status" class="form-control" required>
              <option value="new" <?= $item['status']=='new' ? 'selected' : '' ?>>جديدة</option>
              <option value="good" <?= $item['status']=='good' ? 'selected' : '' ?>>جيدة جداً</option>
              <option value="used" <?= $item['status']=='used' ? 'selected' : '' ?>>مستخدمة قليلاً</option>
            </select>
          </div>
        </div>
        
        <div class="form-group">
          <label for="description"><i class="fas fa-align-left"></i> الوصف</label>
          <textarea 
            id="description" 
            name="description" 
            class="form-control" 
            placeholder="أدخل وصفاً مفصلاً للأداة"
            required
          ><?= htmlspecialchars($item['description']) ?></textarea>
          <div class="validation-error" id="description-error" style="display: none;">
            <i class="fas fa-exclamation-circle"></i>
            <span>الوصف يجب أن يكون على الأقل 10 أحرف</span>
          </div>
        </div>
        
        <button type="submit" class="btn btn-primary" id="submit-btn">
          <i class="fas fa-save"></i>
          حفظ التعديلات
        </button>
      </form>
      
      <div class="back-container">
        <a href="view_item.php?item_id=<?= $item_id ?>" class="btn btn-outline">
          <i class="fas fa-arrow-right"></i>
          رجوع إلى صفحة الأداة
        </a>
      </div>
    </div>
  </div>

  <script>
    // File name display and preview
    document.getElementById('image').addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        // Reset error
        document.getElementById('image-error').style.display = 'none';
        
        // Validate file
        const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!validTypes.includes(file.type)) {
          document.getElementById('image-error').innerHTML = `
            <i class="fas fa-exclamation-circle"></i>
            <span>نوع الملف غير مدعوم. يرجى تحميل صورة (JPEG, PNG)</span>
          `;
          document.getElementById('image-error').style.display = 'flex';
          return;
        }
        
        // Check file size (max 2MB)
        if (file.size > 2 * 1024 * 1024) {
          document.getElementById('image-error').innerHTML = `
            <i class="fas fa-exclamation-circle"></i>
            <span>حجم الملف كبير جداً. الحد الأقصى 2MB</span>
          `;
          document.getElementById('image-error').style.display = 'flex';
          return;
        }
        
        // Update file name
        document.getElementById('file-name').textContent = file.name;
        
        // Preview image
        const reader = new FileReader();
        reader.onload = function(e) {
          // Create image if not exists
          let previewImg = document.getElementById('image-preview');
          if (!previewImg) {
            const placeholder = document.getElementById('image-placeholder');
            if (placeholder) placeholder.style.display = 'none';
            
            previewImg = document.createElement('img');
            previewImg.id = 'image-preview';
            previewImg.className = 'preview';
            previewImg.alt = 'صورة الأداة';
            document.querySelector('.preview-container').appendChild(previewImg);
          }
          
          previewImg.src = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    });
    
    // Add animation on load
    document.addEventListener('DOMContentLoaded', function() {
      const container = document.querySelector('.edit-container');
      container.style.opacity = '0';
      container.style.transform = 'translateY(20px)';
      
      setTimeout(() => {
        container.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        container.style.opacity = '1';
        container.style.transform = 'translateY(0)';
      }, 100);
    });
    
    // Form validation
    document.getElementById('item-form').addEventListener('submit', function(e) {
      const name = document.getElementById('name').value.trim();
      const description = document.getElementById('description').value.trim();
      let isValid = true;
      
      // Reset errors
      document.querySelectorAll('.validation-error').forEach(el => {
        el.style.display = 'none';
      });
      document.querySelectorAll('.form-control').forEach(el => {
        el.classList.remove('error');
      });
      
      // Validate name
      if (name.length < 3) {
        document.getElementById('name-error').style.display = 'flex';
        document.getElementById('name').classList.add('error');
        isValid = false;
      }
      
      // Validate description
      if (description.length < 10) {
        document.getElementById('description-error').style.display = 'flex';
        document.getElementById('description').classList.add('error');
        isValid = false;
      }
      
      // If not valid, prevent form submission
      if (!isValid) {
        e.preventDefault();
        return;
      }
      
      // Show loading state
      const submitBtn = document.getElementById('submit-btn');
      submitBtn.classList.add('loading');
    });
    
    // Real-time validation
    document.getElementById('name').addEventListener('input', function() {
      if (this.value.trim().length >= 3) {
        this.classList.remove('error');
        document.getElementById('name-error').style.display = 'none';
      } else {
        this.classList.add('error');
        document.getElementById('name-error').style.display = 'flex';
      }
    });
    
    document.getElementById('description').addEventListener('input', function() {
      if (this.value.trim().length >= 10) {
        this.classList.remove('error');
        document.getElementById('description-error').style.display = 'none';
      } else {
        this.classList.add('error');
        document.getElementById('description-error').style.display = 'flex';
      }
    });
  </script>
</body>
</html>