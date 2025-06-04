<?php
session_start();
include __DIR__ . '/../database/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php"); 
    exit;
}

$user_id     = $_SESSION['user_id'];
$perPage     = 8;
$page        = max(1, intval($_GET['page'] ?? 1));
$offset      = ($page - 1) * $perPage;
$filter      = in_array($_GET['status'] ?? '', ['pending','accepted','rejected'])
                ? $_GET['status']
                : '';
$status_map  = [
    'pending' => ['label' => 'معلق', 'color' => '#ff9800', 'icon' => '⏳'],
    'accepted' => ['label' => 'مقبول', 'color' => '#4caf50', 'icon' => '✅'],
    'rejected' => ['label' => 'مرفوض', 'color' => '#f44336', 'icon' => '❌']
];

// Count total (with optional filter)
$count_sql = "SELECT COUNT(*) FROM reservations WHERE user_id = ?"
           . ($filter ? " AND status = ?" : "");
$count_stmt = $conn->prepare($count_sql);
if ($filter) {
    $count_stmt->bind_param("is",$user_id,$filter);
} else {
    $count_stmt->bind_param("i",$user_id);
}
$count_stmt->execute();
$total = $count_stmt->get_result()->fetch_row()[0];

// Fetch page of requests
$sql = "
  SELECT r.*, i.name AS item_name, i.image
  FROM reservations r
  JOIN items      i ON r.item_id = i.item_id
  WHERE r.user_id = ?" 
  . ($filter ? " AND r.status = ?" : "") . "
  ORDER BY r.created_at DESC
  LIMIT ? OFFSET ?
";
$stmt = $conn->prepare($sql);

if ($filter) {
    $stmt->bind_param("isii", $user_id, $filter, $perPage, $offset);
} else {
    $stmt->bind_param("iii", $user_id, $perPage, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
$conn->close();

// Helper: Arabic “ago” formatting
function arabic_ago($dt) {
    $diff = time() - strtotime($dt);
    if ($diff < 60) return "الآن";
    if ($diff < 3600) return floor($diff/60)." دقيقة مضت";
    if ($diff < 86400) return floor($diff/3600)." ساعة مضت";
    return floor($diff/86400)." يوم مضى";
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>طلباتي</title>
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
      max-width: 1200px;
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
    
    .requests-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      flex-wrap: wrap;
      gap: 20px;
    }
    
    .requests-filter {
      display: flex;
      align-items: center;
      gap: 15px;
    }
    
    .filter-label {
      font-weight: 600;
      color: var(--dark);
    }
    
    .filter-select {
      padding: 12px 20px;
      border-radius: 10px;
      border: 2px solid var(--border);
      font-family: 'Tajawal', sans-serif;
      font-size: 16px;
      background: white;
      min-width: 180px;
      transition: var(--transition);
    }
    
    .filter-select:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
    }
    
    .btn-new-request {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      padding: 12px 24px;
      background: linear-gradient(to right, var(--primary), var(--secondary));
      color: white;
      text-decoration: none;
      border-radius: 10px;
      font-weight: 600;
      transition: var(--transition);
      box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
    }
    
    .btn-new-request:hover {
      background: linear-gradient(to right, var(--primary-dark), #651fa3);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
    }
    
    .requests-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 25px;
      margin-bottom: 40px;
    }
    
    .request-card {
      background: white;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: var(--shadow);
      transition: var(--transition);
      display: flex;
      flex-direction: column;
    }
    
    .request-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    }
    
    .card-image {
      height: 180px;
      overflow: hidden;
      position: relative;
    }
    
    .card-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: var(--transition);
    }
    
    .request-card:hover .card-image img {
      transform: scale(1.05);
    }
    
    .card-status {
      position: absolute;
      top: 15px;
      left: 15px;
      padding: 6px 15px;
      border-radius: 20px;
      font-weight: 600;
      font-size: 14px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }
    
    .card-content {
      padding: 20px;
      flex-grow: 1;
      display: flex;
      flex-direction: column;
    }
    
    .card-title {
      font-size: 18px;
      font-weight: 700;
      margin-bottom: 12px;
      color: var(--dark);
    }
    
    .card-meta {
      display: flex;
      flex-direction: column;
      gap: 10px;
      margin-bottom: 15px;
    }
    
    .meta-item {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 14px;
      color: var(--gray);
    }
    
    .meta-icon {
      color: var(--primary);
      width: 20px;
      text-align: center;
    }
    
    .date-range {
      display: flex;
      gap: 10px;
      align-items: center;
      font-size: 15px;
      margin-top: 5px;
    }
    
    .date-item {
      padding: 6px 12px;
      border-radius: 8px;
      background: var(--light);
      flex-grow: 1;
      text-align: center;
    }
    
    .date-arrow {
      color: var(--gray);
    }
    
    .document-link {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      margin-top: 15px;
      padding: 10px 15px;
      background: var(--light);
      border-radius: 8px;
      text-decoration: none;
      color: var(--primary);
      font-weight: 600;
      transition: var(--transition);
    }
    
    .document-link:hover {
      background: rgba(67, 97, 238, 0.1);
    }
    
    .no-requests {
      text-align: center;
      padding: 60px 20px;
      background: white;
      border-radius: 16px;
      box-shadow: var(--shadow);
      margin: 20px 0;
    }
    
    .no-requests i {
      font-size: 80px;
      color: var(--light-gray);
      margin-bottom: 20px;
    }
    
    .no-requests h3 {
      font-size: 24px;
      color: var(--dark);
      margin-bottom: 15px;
    }
    
    .no-requests p {
      font-size: 18px;
      color: var(--gray);
      max-width: 600px;
      margin: 0 auto 25px;
      line-height: 1.6;
    }
    
    .pagination {
      display: flex;
      justify-content: center;
      gap: 8px;
      margin: 40px 0 20px;
      flex-wrap: wrap;
    }
    
    .page-item {
      display: inline-block;
    }
    
    .page-link {
      display: block;
      padding: 10px 18px;
      border-radius: 10px;
      text-decoration: none;
      font-weight: 600;
      transition: var(--transition);
      border: 2px solid transparent;
    }
    
    .page-link.number {
      min-width: 45px;
      text-align: center;
    }
    
    .page-link:hover:not(.active) {
      background: var(--light);
      border-color: var(--border);
    }
    
    .page-link.active {
      background: var(--primary);
      color: white;
      border-color: var(--primary);
    }
    
    .footer {
      text-align: center;
      margin-top: 60px;
      padding: 20px;
      color: var(--gray);
      font-size: 15px;
      border-top: 1px solid var(--border);
    }
    
    @media (max-width: 768px) {
      .requests-header {
        flex-direction: column;
        align-items: stretch;
      }
      
      .requests-filter {
        justify-content: space-between;
      }
      
      .requests-grid {
        grid-template-columns: 1fr;
      }
      
      .page-title {
        font-size: 26px;
      }
      
      .page-description {
        font-size: 16px;
      }
    }
    
    @media (max-width: 480px) {
      .container {
        padding: 10px;
      }
      
      .page-title {
        font-size: 24px;
      }
      
      .filter-select {
        width: 100%;
      }
      
      .requests-filter {
        flex-direction: column;
        align-items: stretch;
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
      <h1 class="page-title">طلباتي</h1>
      <p class="page-description">مراجعة وتتبع حالة طلبات الاستعارة السابقة</p>
    </header>

    <div class="requests-header">
      <div class="requests-filter">
        <span class="filter-label">فلتر حسب الحالة:</span>
        <form method="get" class="filter-form">
          <select name="status" class="filter-select" onchange="this.form.submit()">
            <option value="">عرض الكل</option>
            <?php foreach($status_map as $key => $status): ?>
              <option value="<?= $key ?>" <?= $key === $filter ? 'selected' : '' ?>>
                <?= $status['icon'] ?> <?= $status['label'] ?>
              </option>
            <?php endforeach; ?>
          </select>
        </form>
      </div>
      
      <a href="new_request.php" class="btn-new-request">
        <i class="fas fa-plus"></i> طلب جديد
      </a>
    </div>

    <?php if ($total): ?>
      <div class="requests-grid">
        <?php while($row = $result->fetch_assoc()): ?>
          <?php
            $status_key = $row['status'];
            $status = $status_map[$status_key] ?? ['label' => $status_key, 'color' => '#6c757d', 'icon' => '❓'];
            $img = $row['image'] && file_exists(__DIR__.'/'.$row['image'])
                    ? $row['image']
                    : 'https://via.placeholder.com/400x300/f0f8ff/4361ee?text=No+Image';
            $docPath = $row['documents_path'];
            $docExists = $docPath && file_exists(__DIR__.'/'.$docPath);
          ?>
          <div class="request-card">
            <div class="card-image">
              <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($row['item_name']) ?>">
              <div class="card-status" style="background: <?= $status['color'] ?>; color: white;">
                <?= $status['icon'] ?> <?= $status['label'] ?>
              </div>
            </div>
            
            <div class="card-content">
              <h3 class="card-title"><?= htmlspecialchars($row['item_name']) ?></h3>
              
              <div class="card-meta">
                <div class="meta-item">
                  <span class="meta-icon"><i class="fas fa-clock"></i></span>
                  <span>تم تقديم الطلب: <?= arabic_ago($row['created_at']) ?></span>
                </div>
              </div>
              
              <div class="date-range">
                <div class="date-item">
                  <i class="fas fa-calendar-check"></i> <?= htmlspecialchars($row['start_date']) ?>
                </div>
                <span class="date-arrow"><i class="fas fa-arrow-left"></i></span>
                <div class="date-item">
                  <i class="fas fa-calendar-times"></i> <?= htmlspecialchars($row['end_date']) ?>
                </div>
              </div>
              
              <?php if ($docExists): ?>
                <?php $size = round(filesize(__DIR__.'/'.$docPath)/1024, 1); ?>
                <a href="<?= htmlspecialchars($docPath) ?>" target="_blank" class="document-link">
                  <i class="fas fa-file-pdf"></i> عرض المستند (<?= $size ?> ك.ب)
                </a>
              <?php endif; ?>
            </div>
          </div>
        <?php endwhile; ?>
      </div>

      <!-- Pagination -->
      <?php $pages = ceil($total/$perPage); ?>
      <?php if ($pages > 1): ?>
        <div class="pagination">
          <?php for($p = 1; $p <= $pages; $p++): ?>
            <?php if ($p == $page): ?>
              <span class="page-item">
                <span class="page-link number active"><?= $p ?></span>
              </span>
            <?php else: ?>
              <a class="page-item" href="?status=<?= $filter ?>&page=<?= $p ?>">
                <span class="page-link number"><?= $p ?></span>
              </a>
            <?php endif; ?>
          <?php endfor; ?>
        </div>
      <?php endif; ?>

    <?php else: ?>
      <div class="no-requests">
        <i class="fas fa-file-alt"></i>
        <h3>لا توجد طلبات</h3>
        <p>لم تقم بتقديم أي طلبات استعارة بعد. يمكنك البدء بتقديم طلب جديد باستخدام الزر أدناه.</p>
        <a href="new_request.php" class="btn-new-request">
          <i class="fas fa-plus"></i> تقديم طلب جديد
        </a>
      </div>
    <?php endif; ?>

    <div class="footer">
      <p>© 2023 نظام إدارة الأدوات. جميع الحقوق محفوظة</p>
    </div>
  </div>
  
  <script>
    // Highlight active filter in dropdown
    document.addEventListener('DOMContentLoaded', function() {
      const filterSelect = document.querySelector('.filter-select');
      const options = filterSelect.options;
      
      for (let i = 0; i < options.length; i++) {
        if (options[i].selected) {
          filterSelect.style.backgroundColor = options[i].value ? 
            getComputedStyle(document.documentElement).getPropertyValue('--light') : '';
          break;
        }
      }
      
      // Add event listener to update background on change
      filterSelect.addEventListener('change', function() {
        this.style.backgroundColor = this.value ? 
          getComputedStyle(document.documentElement).getPropertyValue('--light') : '';
      });
    });
  </script>
</body>
</html>