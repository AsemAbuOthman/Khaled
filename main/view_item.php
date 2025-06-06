<?php
session_start();
include("../main/partials/header-1.php");
include __DIR__ . '/../database/db.php';

// Auth check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'lender') {
    header("Location: login.php");
    exit;
}

$lender_id = $_SESSION['user_id'];
$item_id   = intval($_GET['item_id'] ?? 0);

// Display messages
if (isset($_SESSION['success'])) {
    $success_message = htmlspecialchars($_SESSION['success']);
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    $error_message = htmlspecialchars($_SESSION['error']);
    unset($_SESSION['error']);
}

// Fetch item + owner
$sql = "
  SELECT i.*, CONCAT(u.first_name, ' ', u.last_name) AS owner_name
  FROM items i
  JOIN users u ON i.user_id=u.user_id
  WHERE i.item_id=?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();
if (!$item) {
    echo "<p class='alert'>الأداة غير موجودة.</p>"; 
    exit;
}

// Pagination & filter
$filter    = in_array($_GET['status'] ?? '', ['pending','accepted','rejected']) 
               ? $_GET['status'] 
               : '';
$page      = max(1, intval($_GET['page'] ?? 1));
$perPage   = 6;
$offset    = ($page - 1) * $perPage;

// Count total reservations
$countSql  = "SELECT COUNT(*) FROM reservations WHERE item_id=?" 
           . ($filter ? " AND status=?" : "");
$countStmt = $conn->prepare($countSql);
if ($filter) {
    $countStmt->bind_param("is", $item_id, $filter);
} else {
    $countStmt->bind_param("i", $item_id);
}
$countStmt->execute();
$total = $countStmt->get_result()->fetch_row()[0];

// Fetch page of reservations (with borrower's name + any document)
$resSql = "
  SELECT r.*, u.first_name, u.last_name, u.email, u.phone, u.locationUrl as address, r.documents_path
  FROM reservations r
  JOIN users u ON r.user_id=u.user_id
  WHERE r.item_id=?" 
  . ($filter ? " AND r.status=?" : "") . "
  ORDER BY r.created_at DESC
  LIMIT ? OFFSET ?
";
$resStmt = $conn->prepare($resSql);
if ($filter) {
    $resStmt->bind_param("isii", $item_id, $filter, $perPage, $offset);
} else {
    $resStmt->bind_param("iii",  $item_id, $perPage, $offset);
}
$resStmt->execute();
$resResult = $resStmt->get_result();

$conn->close();

// Map DB → Arabic
$statusMap = [
  'pending'  => 'معلق',
  'accepted' => 'مقبول',
  'rejected' => 'مرفوض'
];

// Relative time helper
function arabic_ago($date) {
    $diff = time() - strtotime($date);
    if ($diff < 60)   return "الآن";
    if ($diff < 3600) return floor($diff/60) . " دقيقة مضت";
    if ($diff < 86400) return floor($diff/3600) . " ساعة مضت";
    return floor($diff/86400) . " يوم مضى";
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>تفاصيل الأداة</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #4361ee;
      --primary-dark: #3a56d4;
      --secondary: #3f37c9;
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
      background-color: #f5f7ff;
      color: var(--gray-900);
      line-height: 1.6;
    }

    .container {
      max-width: 1200px;
      margin: 30px auto;
      padding: 0 20px;
    }

    .card {
      background: white;
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: 30px;
      margin-bottom: 30px;
      transition: var(--transition);
    }

    .card:hover {
      box-shadow: 0 6px 25px rgba(0, 0, 0, 0.12);
    }

    h2, h3 {
      color: var(--primary-dark);
      margin-bottom: 20px;
      position: relative;
      padding-bottom: 10px;
    }

    h2:after, h3:after {
      content: '';
      position: absolute;
      bottom: 0;
      right: 0;
      width: 60px;
      height: 3px;
      background: linear-gradient(90deg, var(--accent), var(--primary));
      border-radius: 3px;
    }

    .item-details {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 30px;
    }

    .image-wrapper {
      border-radius: var(--radius);
      overflow: hidden;
      box-shadow: var(--shadow);
      height: 300px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--gray-100);
    }

    .image-wrapper img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      transition: transform 0.5s ease;
    }

    .image-wrapper:hover img {
      transform: scale(1.05);
    }

    .info-group {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .info-item {
      display: flex;
      padding: 15px 0;
      border-bottom: 1px solid var(--gray-200);
    }

    .info-item:last-child {
      border-bottom: none;
    }

    .label {
      font-weight: 600;
      color: var(--primary-dark);
      width: 120px;
      flex-shrink: 0;
    }

    .value {
      flex-grow: 1;
    }

    .status {
      display: inline-block;
      padding: 6px 14px;
      border-radius: 20px;
      font-weight: 600;
      font-size: 14px;
    }

    .status.available {
      background: rgba(76, 201, 240, 0.15);
      color: #0ea5e9;
    }

    .status.unavailable {
      background: rgba(244, 63, 94, 0.15);
      color: var(--danger);
    }

    .actions {
      display: flex;
      gap: 15px;
      margin-top: 20px;
      flex-wrap: wrap;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 12px 24px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
      transition: var(--transition);
      border: none;
      cursor: pointer;
      gap: 8px;
      font-size: 15px;
    }

    .btn-primary {
      background: var(--primary);
      color: white;
    }

    .btn-primary:hover {
      background: var(--primary-dark);
      transform: translateY(-2px);
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

    .btn-success {
      background: var(--success);
      color: white;
    }

    .btn-danger {
      background: var(--danger);
      color: white;
    }

    .btn-small {
      padding: 8px 16px;
      font-size: 14px;
    }

    .filter-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
      flex-wrap: wrap;
      gap: 15px;
    }

    .filter-controls {
      display: flex;
      gap: 15px;
      align-items: center;
    }

    .filter-select {
      padding: 12px 20px;
      border-radius: 8px;
      border: 2px solid var(--gray-200);
      background: white;
      font-size: 15px;
      font-weight: 500;
      color: var(--gray-700);
      transition: var(--transition);
      cursor: pointer;
    }

    .filter-select:focus {
      border-color: var(--primary);
      outline: none;
    }

    .reservations-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    .reservations-table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      margin: 20px 0;
      display: table;
    }

    .reservations-table th {
      background: var(--primary);
      color: white;
      font-weight: 600;
      text-align: right;
      padding: 16px 20px;
    }

    .reservations-table th:first-child {
      border-top-right-radius: var(--radius);
    }

    .reservations-table th:last-child {
      border-top-left-radius: var(--radius);
    }

    .reservations-table td {
      padding: 16px 20px;
      text-align: right;
      border-bottom: 1px solid var(--gray-200);
      background: white;
    }

    .reservations-table tr:last-child td {
      border-bottom: none;
    }

    .reservations-table tr:hover td {
      background: rgba(67, 97, 238, 0.03);
    }

    .status-badge {
      padding: 6px 14px;
      border-radius: 20px;
      font-size: 14px;
      font-weight: 600;
      display: inline-block;
    }

    .status-badge.pending {
      background: rgba(245, 158, 11, 0.15);
      color: var(--warning);
    }

    .status-badge.accepted {
      background: rgba(74, 222, 128, 0.15);
      color: var(--success);
    }

    .status-badge.rejected {
      background: rgba(244, 63, 94, 0.15);
      color: var(--danger);
    }

    .document-link {
      color: var(--primary);
      text-decoration: none;
      font-weight: 500;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      transition: var(--transition);
    }

    .document-link:hover {
      color: var(--secondary);
      text-decoration: underline;
    }

    .reservation-actions {
      display: flex;
      gap: 10px;
    }

    .btn-form {
      margin: 0;
    }

    .empty-state {
      text-align: center;
      padding: 40px 20px;
      color: var(--gray-700);
    }

    .empty-state i {
      font-size: 48px;
      color: var(--gray-300);
      margin-bottom: 15px;
    }

    .empty-state p {
      font-size: 18px;
    }

    .pagination {
      display: flex;
      justify-content: center;
      gap: 8px;
      margin-top: 30px;
    }

    .pagination a {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      border-radius: 8px;
      background: white;
      color: var(--gray-700);
      text-decoration: none;
      font-weight: 600;
      box-shadow: var(--shadow);
      transition: var(--transition);
    }

    .pagination a:hover, .pagination a.current {
      background: var(--primary);
      color: white;
    }

    .reservation-card {
      display: none;
      background: white;
      border-radius: var(--radius);
      padding: 20px;
      margin: 15px 0;
      box-shadow: var(--shadow);
    }

    .card-row {
      display: flex;
      justify-content: space-between;
      padding: 12px 0;
      border-bottom: 1px solid var(--gray-200);
    }

    .card-row:last-child {
      border-bottom: none;
    }

    .mobile-actions {
      display: flex;
      gap: 10px;
      margin-top: 10px;
    }

    .alert {
      padding: 16px 20px;
      margin: 20px 0;
      border-radius: 8px;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 12px;
      box-shadow: var(--shadow);
    }

    .alert.success {
      background-color: rgba(74, 222, 128, 0.15);
      color: #15803d;
      border-left: 4px solid var(--success);
    }

    .alert.error {
      background-color: rgba(244, 63, 94, 0.15);
      color: var(--danger);
      border-left: 4px solid var(--danger);
    }
    
    /* Borrower Profile Modal */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      justify-content: center;
      align-items: center;
    }
    
    .modal-content {
      background: white;
      border-radius: var(--radius);
      box-shadow: 0 5px 30px rgba(0,0,0,0.3);
      width: 90%;
      max-width: 500px;
      max-height: 90vh;
      overflow-y: auto;
      animation: modalOpen 0.3s ease;
    }
    
    @keyframes modalOpen {
      from { opacity: 0; transform: translateY(-30px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .modal-header {
      padding: 20px;
      background: var(--primary);
      color: white;
      border-top-left-radius: var(--radius);
      border-top-right-radius: var(--radius);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .modal-header h3 {
      color: white;
      margin: 0;
      padding: 0;
    }
    
    .modal-header h3:after {
      display: none;
    }
    
    .close-btn {
      background: transparent;
      border: none;
      color: white;
      font-size: 24px;
      cursor: pointer;
      padding: 0 10px;
    }
    
    .modal-body {
      padding: 25px;
    }
    
    .profile-info {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }
    
    .profile-row {
      display: flex;
      padding-bottom: 15px;
      border-bottom: 1px solid var(--gray-200);
    }
    
    .profile-row:last-child {
      border-bottom: none;
      padding-bottom: 0;
    }
    
    .profile-label {
      width: 120px;
      flex-shrink: 0;
      font-weight: 600;
      color: var(--primary-dark);
    }
    
    .profile-value {
      flex-grow: 1;
    }
    
    .borrower-link {
      color: var(--primary);
      text-decoration: none;
      font-weight: 500;
      cursor: pointer;
      transition: var(--transition);
    }
    
    .borrower-link:hover {
      color: var(--secondary);
      text-decoration: underline;
    }

    @media (max-width: 900px) {
      .item-details {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 768px) {
      .reservations-table { 
        display: none; 
      }
      .reservation-card { 
        display: block; 
      }
      .filter-bar {
        flex-direction: column;
        align-items: flex-start;
      }
      .card {
        padding: 20px;
      }
    }

    @media (max-width: 480px) {
      .actions {
        flex-direction: column;
      }
      .btn {
        width: 100%;
        justify-content: center;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <!-- Success/Error Messages -->
    <?php if (!empty($success_message)): ?>
      <div class="alert success">
        <i class="fas fa-check-circle"></i>
        <div><?= $success_message ?></div>
      </div>
    <?php endif; ?>
    
    <?php if (!empty($error_message)): ?>
      <div class="alert error">
        <i class="fas fa-exclamation-circle"></i>
        <div><?= $error_message ?></div>
      </div>
    <?php endif; ?>

    <!-- Item Details -->
    <section class="card item-details">
      <?php if (!empty($item['image']) && file_exists($item['image'])): ?>
        <div class="image-wrapper">
          <img src="<?= htmlspecialchars($item['image']) ?>" alt="صورة الأداة">
        </div>
      <?php else: ?>
        <div class="image-wrapper" style="background: #f0f4ff; display: flex; align-items: center; justify-content: center;">
          <i class="fas fa-image" style="font-size: 3rem; color: #a0aec0;"></i>
        </div>
      <?php endif; ?>
      
      <div class="info-content">
        <h2><?= htmlspecialchars($item['name']) ?></h2>
        <div class="info-group">
          <div class="info-item">
            <span class="label">الوصف:</span>
            <span class="value"><?= htmlspecialchars($item['description']) ?></span>
          </div>
          <div class="info-item">
            <span class="label">الحالة:</span>
            <span class="value"><?= htmlspecialchars($item['status']) ?></span>
          </div>
          <div class="info-item">
            <span class="label">التوفر:</span>
            <span class="value">
              <?php if ($item['reserve_status'] === 'available'): ?>
                <span class="status available">متاحة</span>
              <?php else: ?>
                <span class="status unavailable">محجوزة</span>
              <?php endif; ?>
            </span>
          </div>
          <div class="info-item">
            <span class="label">المالك:</span>
            <span class="value"><?= htmlspecialchars($item['owner_name']) ?></span>
          </div>
        </div>
        
        <div class="actions">
          <a href="my_items.php" class="btn btn-primary">
            <i class="fas fa-arrow-right"></i>
            رجوع إلى قائمة الأدوات
          </a>
          <?php if ($_SESSION['user_id'] === $item['user_id'] || $_SESSION['role'] === 'admin'): ?>
            <a href="edit_item.php?item_id=<?= $item_id ?>" class="btn btn-outline">
              <i class="fas fa-edit"></i>
              تعديل الأداة
            </a>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- Reservations -->
    <section class="card reservations">
      <div class="reservations-header">
        <h3>طلبات الحجز</h3>
        <div class="filter-controls">
          <span>عدد الطلبات: <strong><?= $total ?></strong></span>
          <form method="get" class="filter-form">
            <input type="hidden" name="item_id" value="<?= $item_id ?>">
            <select name="status" onchange="this.form.submit()" class="filter-select">
              <option value="">كل الحالات</option>
              <?php foreach($statusMap as $key => $lab): ?>
                <option value="<?= $key ?>" <?= $filter === $key ? 'selected' : '' ?>>
                  <?= $lab ?>
                </option>
              <?php endforeach; ?>
            </select>
          </form>
        </div>
      </div>

      <?php if ($total > 0): ?>
        <!-- Table for desktop -->
        <table class="reservations-table">
          <thead>
            <tr>
              <th>الاسم</th>
              <th>بداية الحجز</th>
              <th>نهاية الحجز</th>
              <th>المرفقات</th>
              <th>الحالة</th>
              <th>الإجراءات</th>
            </tr>
          </thead>
          <tbody>
            <?php while($r = $resResult->fetch_assoc()): ?>
              <?php 
                $statusKey = $r['status'];
                $arabic    = $statusMap[$statusKey] ?? $statusKey;
              ?>
              <tr>
                <td>
                  <a class="borrower-link" onclick="showBorrowerProfile(
                    '<?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?>',
                    '<?= htmlspecialchars($r['email']) ?>',
                    '<?= htmlspecialchars($r['phone']) ?>',
                    '<?= htmlspecialchars($r['address']) ?>'
                  )">
                    <?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?>
                    <i class="fas fa-user-circle ml-2"></i>
                  </a>
                </td>
                <td><?= date('Y/m/d', strtotime($r['start_date'])) ?></td>
                <td><?= date('Y/m/d', strtotime($r['end_date'])) ?></td>
                <td>
                  <?php if ($r['documents_path']): ?>
                    <a href="<?= $r['documents_path'] ?>" target="_blank" class="document-link">
                      <i class="fas fa-file-pdf"></i>
                      عرض المرفق
                    </a>
                  <?php else: ?>
                    -
                  <?php endif; ?>
                </td>
                <td>
                  <span class="status-badge <?= $statusKey ?>">
                    <i class="fas fa-<?= $statusKey === 'pending' ? 'clock' : ($statusKey === 'accepted' ? 'check-circle' : 'times-circle') ?>"></i>
                    <?= $arabic ?>
                  </span>
                </td>
                <td class="reservation-actions">
                  <?php if ($statusKey === 'pending'): ?>
                    <form action="accept_reservation.php" method="post" class="btn-form">
                      <input type="hidden" name="reservation_id" value="<?= $r['reservation_id'] ?>">
                      <button class="btn btn-success btn-small">
                        <i class="fas fa-check"></i>
                        قبول
                      </button>
                    </form>
                    <form action="reject_reservation.php" method="post" class="btn-form">
                      <input type="hidden" name="reservation_id" value="<?= $r['reservation_id'] ?>">
                      <button class="btn btn-danger btn-small">
                        <i class="fas fa-times"></i>
                        رفض
                      </button>
                    </form>
                  <?php else: ?>
                    <span class="text-muted">تمت المعالجة</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>

        <!-- Cards for mobile -->
        <?php
          $resResult->data_seek(0);
          while($r = $resResult->fetch_assoc()):
            $statusKey = $r['status'];
            $arabic    = $statusMap[$statusKey] ?? $statusKey;
        ?>
          <div class="reservation-card">
            <div class="card-row">
              <span>الاسم:</span>
              <span>
                <a class="borrower-link" onclick="showBorrowerProfile(
                  '<?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?>',
                  '<?= htmlspecialchars($r['email']) ?>',
                  '<?= htmlspecialchars($r['phone']) ?>',
                  '<?= htmlspecialchars($r['address']) ?>'
                )">
                  <?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?>
                  <i class="fas fa-user-circle ml-2"></i>
                </a>
              </span>
            </div>
            <div class="card-row">
              <span>بداية الحجز:</span>
              <span><?= date('Y/m/d', strtotime($r['start_date'])) ?></span>
            </div>
            <div class="card-row">
              <span>نهاية الحجز:</span>
              <span><?= date('Y/m/d', strtotime($r['end_date'])) ?></span>
            </div>
            <div class="card-row">
              <span>المرفقات:</span>
              <span>
                <?php if ($r['documents_path']): ?>
                  <a href="<?= $r['documents_path'] ?>" target="_blank" class="document-link">عرض المرفق</a>
                <?php else: ?>
                  -
                <?php endif; ?>
              </span>
            </div>
            <div class="card-row">
              <span>الحالة:</span>
              <span class="status-badge <?= $statusKey ?>">
                <i class="fas fa-<?= $statusKey === 'pending' ? 'clock' : ($statusKey === 'accepted' ? 'check-circle' : 'times-circle') ?>"></i>
                <?= $arabic ?>
              </span>
            </div>
            <?php if ($statusKey === 'pending'): ?>
              <div class="mobile-actions">
                <form action="accept_reservation.php" method="post">
                  <input type="hidden" name="reservation_id" value="<?= $r['reservation_id'] ?>">
                  <button class="btn btn-success btn-small">
                    <i class="fas fa-check"></i>
                    قبول
                  </button>
                </form>
                <form action="reject_reservation.php" method="post">
                  <input type="hidden" name="reservation_id" value="<?= $r['reservation_id'] ?>">
                  <button class="btn btn-danger btn-small">
                    <i class="fas fa-times"></i>
                    رفض
                  </button>
                </form>
              </div>
            <?php endif; ?>
          </div>
        <?php endwhile; ?>

        <!-- Pagination -->
        <?php 
          $pages = ceil($total / $perPage); 
          if ($pages > 1): 
        ?>
          <div class="pagination">
            <?php for($p = 1; $p <= $pages; $p++): ?>
              <a 
                href="?item_id=<?= $item_id ?>&status=<?= $filter ?>&page=<?= $p ?>"
                class="<?= $p === $page ? 'current' : '' ?>"
              >
                <?= $p ?>
              </a>
            <?php endfor; ?>
          </div>
        <?php endif; ?>

      <?php else: ?>
        <div class="empty-state">
          <i class="fas fa-inbox"></i>
          <p>لا يوجد طلبات حتى الآن</p>
        </div>
      <?php endif; ?>
    </section>
  </div>
  
  <!-- Borrower Profile Modal -->
  <div id="borrowerModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>تفاصيل المستعير</h3>
        <button class="close-btn" onclick="closeModal()">&times;</button>
      </div>
      <div class="modal-body">
        <div class="profile-info">
          <div class="profile-row">
            <span class="profile-label">الاسم:</span>
            <span class="profile-value" id="borrowerName"></span>
          </div>
          <div class="profile-row">
            <span class="profile-label">البريد الإلكتروني:</span>
            <span class="profile-value" id="borrowerEmail"></span>
          </div>
          <div class="profile-row">
            <span class="profile-label">رقم الهاتف:</span>
            <span class="profile-value" id="borrowerPhone"></span>
          </div>
          <div class="profile-row">
            <span class="profile-label">العنوان:</span>
            <span class="profile-value" id="borrowerAddress"></span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Add animation to cards on load
    document.addEventListener('DOMContentLoaded', function() {
      const cards = document.querySelectorAll('.card');
      cards.forEach((card, index) => {
        setTimeout(() => {
          card.style.opacity = '1';
          card.style.transform = 'translateY(0)';
        }, 150 * index);
      });
    });
    
    // Borrower profile modal functions
    function showBorrowerProfile(name, email, phone, realAddress) {
      document.getElementById('borrowerName').textContent = name;
      document.getElementById('borrowerEmail').textContent = email || 'غير متوفر';
      document.getElementById('borrowerPhone').textContent = phone || 'غير متوفر';
      document.getElementById('borrowerAddress').innerHTML =
        realAddress
          ? `<iframe
              width="100%"
              height="300"
              style="border:0; border-radius: 8px;"
              loading="lazy"
              allowfullscreen
              referrerpolicy="no-referrer-when-downgrade"
              src="${realAddress}&output=embed">
            </iframe>`
          : 'غير متوفر';

      document.getElementById('borrowerModal').style.display = 'flex';
    }
    
    function closeModal() {
      document.getElementById('borrowerModal').style.display = 'none';
    }
    
    // Close modal when clicking outside content
    window.onclick = function(event) {
      const modal = document.getElementById('borrowerModal');
      if (event.target === modal) {
        closeModal();
      }
    }
  </script>
</body>
</html>