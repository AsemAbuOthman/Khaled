<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit;
}

include __DIR__ . '/../database/db.php';

$user_id = $_SESSION['user_id'];
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

if (empty($excluded_ids)) {
    $items_sql = "SELECT item_id, name FROM items WHERE reserve_status = 'available' ORDER BY name ASC";
    $items_stmt = $conn->prepare($items_sql);
} else {
    $placeholders = implode(',', array_fill(0, count($excluded_ids), '?'));
    $items_sql = "SELECT item_id, name FROM items WHERE reserve_status = 'available' AND item_id NOT IN ($placeholders) ORDER BY name ASC";
    $items_stmt = $conn->prepare($items_sql);
    $types = str_repeat('i', count($excluded_ids));
    $items_stmt->bind_param($types, ...$excluded_ids);
}
$items_stmt->execute();
$items_result = $items_stmt->get_result();
$available_items = $items_result->fetch_all(MYSQLI_ASSOC);
$items_stmt->close();

$message = empty($available_items) ? "❌ لا توجد أدوات متاحة حاليًا." : "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $item_id    = intval($_POST['item_id']);
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
            $document_path = "";
            if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION));
                // PDF ONLY - changed validation
                if ($ext !== 'pdf') {
                    $message = "❌ فقط ملفات PDF مسموح بها.";
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>طلب استعارة أداة</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
  <style>
     :root {
      --primary: #4361ee;
      --primary-dark: #3a56d4;
      --secondary: #7209b7;
      --success: #4caf50;
      --error: #f44336;
      --warning: #ff9800;
      --light: #f8f9fa;
      --dark: #212529;
      --gray: #6c757d;
      --light-gray: #e9ecef;
      --border: #dee2e6;
      --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      --transition: all 0.3s ease;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Tajawal', sans-serif;
      background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
      color: var(--dark);
      min-height: 100vh;
      padding: 20px;
      direction: rtl;
    }
    
    .container {
      max-width: 1000px;
      margin: 0 auto;
      padding: 20px;
    }
    
    header {
      text-align: center;
      margin-bottom: 40px;
      padding: 20px;
    }
    
    .logo {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 15px;
      margin-bottom: 20px;
    }
    
    .logo-icon {
      background: var(--primary);
      width: 60px;
      height: 60px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: var(--shadow);
    }
    
    .logo-icon i {
      font-size: 28px;
      color: white;
    }
    
    .logo-text {
      font-size: 28px;
      font-weight: 700;
      color: var(--primary);
      letter-spacing: -0.5px;
    }
    
    .page-title {
      font-size: 32px;
      font-weight: 700;
      color: var(--dark);
      margin-bottom: 10px;
      position: relative;
      display: inline-block;
    }
    
    .page-title:after {
      content: '';
      position: absolute;
      bottom: -10px;
      right: 0;
      width: 70px;
      height: 4px;
      background: var(--primary);
      border-radius: 2px;
    }
    
    .page-description {
      font-size: 18px;
      color: var(--gray);
      max-width: 600px;
      margin: 25px auto 0;
      line-height: 1.6;
    }
    
    .form-container {
      background: white;
      border-radius: 16px;
      box-shadow: var(--shadow);
      overflow: hidden;
      margin-bottom: 40px;
    }
    
    .form-header {
      background: linear-gradient(to right, var(--primary), var(--secondary));
      color: white;
      padding: 25px 30px;
      display: flex;
      align-items: center;
      gap: 15px;
    }
    
    .form-header i {
      font-size: 24px;
    }
    
    .form-header h2 {
      font-size: 22px;
      font-weight: 600;
    }
    
    .form-content {
      padding: 35px;
    }
    
    .form-row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 25px;
      margin-bottom: 25px;
    }
    
    .form-group {
      margin-bottom: 20px;
    }
    
    .form-label {
      display: block;
      margin-bottom: 10px;
      font-weight: 600;
      color: var(--dark);
      font-size: 16px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    
    .form-label i {
      color: var(--primary);
    }
    
    .form-control {
      width: 100%;
      padding: 14px 18px;
      border: 2px solid var(--border);
      border-radius: 10px;
      font-size: 16px;
      font-family: 'Tajawal', sans-serif;
      transition: var(--transition);
      background: var(--light);
    }
    
    .form-control:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
    }
    
    .file-upload {
      position: relative;
      display: flex;
      flex-direction: column;
    }
    
    .file-input {
      position: absolute;
      width: 0.1px;
      height: 0.1px;
      opacity: 0;
      overflow: hidden;
      z-index: -1;
    }
    
    .file-label {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 14px 20px;
      background: var(--light);
      border: 2px dashed var(--border);
      border-radius: 10px;
      cursor: pointer;
      transition: var(--transition);
      text-align: center;
      gap: 10px;
      color: var(--gray);
    }
    
    .file-label:hover {
      background: var(--light-gray);
      border-color: var(--primary);
    }
    
    .file-label i {
      font-size: 20px;
      color: var(--primary);
    }
    
    .file-name {
      margin-top: 10px;
      font-size: 14px;
      color: var(--gray);
      text-align: center;
    }
    
    .btn-submit {
      display: block;
      width: 100%;
      padding: 16px;
      background: linear-gradient(to right, var(--primary), var(--secondary));
      color: white;
      border: none;
      border-radius: 10px;
      font-size: 18px;
      font-weight: 600;
      cursor: pointer;
      transition: var(--transition);
      font-family: 'Tajawal', sans-serif;
      margin-top: 20px;
      box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
    }
    
    .btn-submit:hover {
      background: linear-gradient(to right, var(--primary-dark), #651fa3);
      box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
      transform: translateY(-2px);
    }
    
    .btn-submit:active {
      transform: translateY(0);
    }
    
    .btn-submit i {
      margin-left: 8px;
    }
    
    .message {
      padding: 20px;
      border-radius: 10px;
      margin-bottom: 30px;
      display: flex;
      align-items: center;
      gap: 15px;
      font-size: 17px;
      font-weight: 500;
    }
    
    .message.success {
      background: rgba(76, 175, 80, 0.15);
      color: var(--success);
      border-left: 4px solid var(--success);
    }
    
    .message.error {
      background: rgba(244, 67, 54, 0.15);
      color: var(--error);
      border-left: 4px solid var(--error);
    }
    
    .no-items {
      text-align: center;
      padding: 40px;
      background: white;
      border-radius: 16px;
      box-shadow: var(--shadow);
    }
    
    .no-items i {
      font-size: 60px;
      color: var(--gray);
      margin-bottom: 20px;
    }
    
    .no-items h3 {
      font-size: 24px;
      color: var(--dark);
      margin-bottom: 15px;
    }
    
    .no-items p {
      font-size: 18px;
      color: var(--gray);
      max-width: 600px;
      margin: 0 auto;
      line-height: 1.6;
    }
    
    .info-box {
      background: var(--light);
      border-radius: 12px;
      padding: 25px;
      margin-top: 30px;
      border-left: 4px solid var(--primary);
    }
    
    .info-box h3 {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 15px;
      color: var(--primary);
    }
    
    .info-box ul {
      padding-right: 20px;
    }
    
    .info-box li {
      margin-bottom: 10px;
      line-height: 1.6;
    }
    
    .info-box li i {
      color: var(--primary);
      margin-left: 8px;
    }
    
    .footer {
      text-align: center;
      margin-top: 40px;
      padding: 20px;
      color: var(--gray);
      font-size: 15px;
      border-top: 1px solid var(--border);
    }
    
    @media (max-width: 768px) {
      .form-row {
        grid-template-columns: 1fr;
      }
      
      .form-content {
        padding: 25px;
      }
      
      .page-title {
        font-size: 26px;
      }
      
      .page-description {
        font-size: 16px;
      }
    }
    
    @media (max-width: 480px) {
      body {
        padding: 10px;
      }
      
      .container {
        padding: 10px;
      }
      
      .form-content {
        padding: 20px 15px;
      }
      
      .form-header {
        padding: 20px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <header>
      <div class="logo">
        <div class="logo-icon">
          <i class="fas fa-tools"></i>
        </div>
        <div class="logo-text">أدواتي</div>
      </div>
      <h1 class="page-title">طلب استعارة أداة</h1>
      <p class="page-description">من هنا يمكنك طلب استعارة الأدوات المتاحة لدينا. اختر الأداة المطلوبة، حدد فترة الاستعارة، وأرفق المستندات اللازمة</p>
    </header>

    <?php if (!empty($message)): ?>
      <div class="message <?= strpos($message, '✅') !== false ? 'success' : 'error' ?>">
        <i class="<?= strpos($message, '✅') !== false ? 'fas fa-check-circle' : 'fas fa-exclamation-circle' ?>"></i>
        <span><?= $message ?></span>
      </div>
    <?php endif; ?>

    <?php if (!empty($available_items)): ?>
      <div class="form-container">
        <div class="form-header">
          <i class="fas fa-file-alt"></i>
          <h2>نموذج طلب الاستعارة</h2>
        </div>
        
        <div class="form-content">
          <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
              <label class="form-label" for="item_id"><i class="fas fa-toolbox"></i>اختر الأداة</label>
              <select class="form-control" name="item_id" id="item_id" required>
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
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label class="form-label" for="start_date"><i class="fas fa-calendar-check"></i>تاريخ البداية</label>
                <input type="date" class="form-control" name="start_date" id="start_date" required>
              </div>
              
              <div class="form-group">
                <label class="form-label" for="end_date"><i class="fas fa-calendar-times"></i>تاريخ النهاية</label>
                <input type="date" class="form-control" name="end_date" id="end_date" required>
              </div>
            </div>
            
            <div class="form-group">
              <label class="form-label"><i class="fas fa-file-pdf"></i>تحميل وثيقة (PDF فقط)</label>
              <div class="file-upload">
                <input type="file" class="file-input" name="document" id="document" accept=".pdf">
                <label class="file-label" for="document">
                  <i class="fas fa-cloud-upload-alt"></i>
                  <span>انقر لرفع ملف PDF</span>
                </label>
                <div class="file-name" id="file-name">لم يتم اختيار ملف</div>
              </div>
            </div>
            
            <button type="submit" class="btn-submit">
              <span>تقديم الطلب</span>
              <i class="fas fa-paper-plane"></i>
            </button>
          </form>
        </div>
      </div>
    <?php else: ?>
      <div class="no-items">
        <i class="fas fa-box-open"></i>
        <h3>لا توجد أدوات متاحة حاليًا</h3>
        <p>عذرًا، جميع الأدوات حاليًا إما مستعارة أو قيد الحجز. يرجى المحاولة مرة أخرى لاحقًا أو التواصل مع الدعم الفني لمزيد من المعلومات.</p>
      </div>
    <?php endif; ?>
    
    <div class="info-box">
      <h3><i class="fas fa-info-circle"></i>معلومات مهمة</h3>
      <ul>
        <li><i class="fas fa-check-circle"></i>يجب أن يكون تاريخ انتهاء الاستعارة بعد تاريخ البداية</li>
        <li><i class="fas fa-check-circle"></i>يجب رفع ملفات بصيغة PDF فقط</li>
        <li><i class="fas fa-check-circle"></i>الحد الأقصى لحجم الملف 5 ميجابايت</li>
        <li><i class="fas fa-check-circle"></i>سيتم مراجعة طلبك من قبل المسؤول خلال 24-48 ساعة</li>
        <li><i class="fas fa-check-circle"></i>يمكنك تتبع حالة طلبك من خلال صفحة طلباتي</li>
      </ul>
    </div>
    
    <div class="footer">
      <p>© 2023 نظام إدارة الأدوات. جميع الحقوق محفوظة</p>
    </div>
  </div>
  
  <script>
    // File name display
    document.getElementById('document').addEventListener('change', function(e) {
      const fileName = document.getElementById('file-name');
      if(this.files.length > 0) {
        fileName.textContent = this.files[0].name;
      } else {
        fileName.textContent = 'لم يتم اختيار ملف';
      }
    });

    // Set min date for date inputs to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('start_date').min = today;
    document.getElementById('end_date').min = today;
  </script>
</body>
</html>