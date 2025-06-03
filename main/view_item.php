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
  echo "<div class='alert success'>" . htmlspecialchars($_SESSION['success']) . "</div>";
  unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
  echo "<div class='alert error'>" . htmlspecialchars($_SESSION['error']) . "</div>";
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
    echo "<p class='alert'>الأداة غير موجودة.</p>"; exit;
}

// Pagination & filter
$filter    = in_array($_GET['status'] ?? '', ['pending','accepted','rejected']) 
               ? $_GET['status'] 
               : '';
$page      = max(1, intval($_GET['page'] ?? 1));
$perPage   = 6;
$offset    = ($page -1)*$perPage;

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
  SELECT r.*, u.first_name, u.last_name, r.documents_path
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
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>تفاصيل الأداة</title>
  <link rel="stylesheet" href="style.css">
  <style>
    /* Improved CSS */
    :root {
      --primary: #4CAF50;
      --secondary: #2196F3;
      --danger: #f44336;
      --gray: #607D8B;
    }
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
    }


    .container {
      max-width: 1200px;
      margin: 20px auto;
      padding: 0 15px;
    }

    .card {
      background: white;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      padding: 20px;
      margin-bottom: 20px;
    }

    .item-details .image-wrapper {
      max-width: 400px;
      margin: 0 auto 20px;
    }

    .item-details img {
      width: 100%;
      height: auto;
      border-radius: 4px;
    }

    .info-group p {
      margin: 10px 0;
      font-size: 16px;
    }

    .label {
      font-weight: bold;
      color: var(--gray);
      min-width: 80px;
      display: inline-block;
    }

    .status {
      padding: 4px 8px;
      border-radius: 4px;
      font-weight: bold;
    }

    .status.available { background: #E8F5E9; color: #2E7D32; }
    .status.unavailable { background: #FFEBEE; color: #C62828; }

    .reservations-table {
      width: 100%;
      border-collapse: collapse;
      margin: 20px 0;
      display: table;
    }

    .reservations-table th,
    .reservations-table td {
      padding: 12px;
      text-align: right;
      border-bottom: 1px solid #ddd;
    }

    .reservations-table th {
      background: #f5f5f5;
    }

    .status-badge {
      padding: 4px 8px;
      border-radius: 12px;
      font-size: 14px;
    }

    .status-badge.pending { background: #FFF3E0; color: #EF6C00; }
    .status-badge.accepted { background: #E8F5E9; color: #2E7D32; }
    .status-badge.rejected { background: #FFEBEE; color: #C62828; }

    .btn {
      display: inline-flex;
      align-items: center;
      padding: 8px 16px;
      border-radius: 4px;
      text-decoration: none;
      font-weight: 500;
      transition: all 0.3s;
      border: 1px solid transparent;
    }

    .btn.outline {
      border-color: var(--primary);
      color: var(--primary);
    }

    .btn.small {
      padding: 4px 8px;
      font-size: 14px;
    }

    .btn.accept { background: #E8F5E9; color: #2E7D32; }
    .btn.reject { background: #FFEBEE; color: #C62828; }

    .btn:hover {
      filter: brightness(90%);
    }

    .pagination {
      margin-top: 20px;
      display: flex;
      gap: 8px;
      justify-content: center;
    }

    .pagination a {
      padding: 8px 12px;
      border-radius: 4px;
      background: #f5f5f5;
      color: #333;
      text-decoration: none;
    }

    .pagination a.current {
      background: var(--primary);
      color: white;
    }

    /* Mobile Cards */
    .reservation-card {
      display: none;
      background: #fff;
      border-radius: 8px;
      padding: 15px;
      margin: 10px 0;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .reservation-card div {
      margin: 6px 0;
      display: flex;
      justify-content: space-between;
    }

    @media (max-width: 768px) {
      .reservations-table { display: none; }
      .reservation-card { display: block; }
    }
    .alert {
    padding: 15px;
    margin: 20px 0;
    border-radius: 5px;
    font-weight: bold;
}

.alert.success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert.error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
  </style>
</head>
<body>
  <div class="container">

    <!-- Item Details -->
    <section class="card item-details">
      <?php if (!empty($item['image']) && file_exists($item['image'])): ?>
        <div class="image-wrapper">
          <img src="<?= htmlspecialchars($item['image']) ?>" alt="صورة الأداة">
        </div>
      <?php endif; ?>
      <h2><?= htmlspecialchars($item['name']) ?></h2>
      <div class="info-group">
        <p><span class="label">الوصف:</span> <?= htmlspecialchars($item['description']) ?></p>
        <p><span class="label">الحالة:</span> <?= htmlspecialchars($item['status']) ?></p>
        <p>
          <span class="label">التوفر:</span>
          <?php if ($item['reserve_status']==='available'): ?>
            <span class="status available">متاحة</span>
          <?php else: ?>
            <span class="status unavailable">محجوزة</span>
          <?php endif; ?>
        </p>
        <p><span class="label">المالك:</span> <?= htmlspecialchars($item['owner_name']) ?></p>
      </div>
      <div class="actions">
        <a href="my_items.php" class="btn">🔙 رجوع</a>
        <?php if ($_SESSION['user_id']===$item['user_id'] || $_SESSION['role']==='admin'): ?>
          <a href="edit_item.php?item_id=<?= $item_id ?>" class="btn outline">✏️ تعديل</a>
        <?php endif; ?>
      </div>
    </section>

    <!-- Reservations -->
    <section class="card reservations">
      <h3>طلبات الحجز</h3>

      <!-- Filter -->
      <div class="filter-bar">
        <form method="get">
          <input type="hidden" name="item_id" value="<?= $item_id ?>">
          <select name="status" onchange="this.form.submit()" class="btn">
            <option value="">كل الحالات</option>
            <?php foreach($statusMap as $key=>$lab): ?>
              <option 
                value="<?= $key ?>"
                <?= $filter === $key ? 'selected' : '' ?>>
                <?= $lab ?>
              </option>
            <?php endforeach; ?>
          </select>
        </form>
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
              <th>إجراءات</th>
            </tr>
          </thead>
          <tbody>
            <?php while($r = $resResult->fetch_assoc()): ?>
              <?php 
                $statusKey = $r['status'];
                $arabic    = $statusMap[$statusKey] ?? $statusKey;
              ?>
              <tr>
                <td><?= htmlspecialchars($r['first_name'].' '.$r['last_name']) ?></td>
                <td><?= date('Y/m/d', strtotime($r['start_date'])) ?></td>
                <td><?= date('Y/m/d', strtotime($r['end_date'])) ?></td>
                <td>
                  <?php if ($r['documents_path']): ?>
                    <a href="<?= $r['documents_path'] ?>" target="_blank">عرض المرفق</a>
                  <?php else: ?>
                    -
                  <?php endif; ?>
                </td>
                <td>
                  <span class="status-badge <?= $statusKey ?>">
                    <?= $arabic ?>
                  </span>
                </td>
                <td class="reservation-actions">
                  <?php if ($statusKey === 'pending'): ?>
                    <form action="accept_reservation.php" method="post" class="btn-form">
                      <input type="hidden" name="reservation_id" value="<?= $r['reservation_id'] ?>">
                      <button class="btn small accept">قبول</button>
                    </form>
                    <form action="reject_reservation.php" method="post" class="btn-form">
                      <input type="hidden" name="reservation_id" value="<?= $r['reservation_id'] ?>">
                      <button class="btn small reject">رفض</button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>

        <!-- Cards for mobile -->
        <?php
          $resResult->data_seek(0); // Reset pointer
          while($r = $resResult->fetch_assoc()):
            $statusKey = $r['status'];
            $arabic    = $statusMap[$statusKey] ?? $statusKey;
        ?>
          <div class="reservation-card">
            <div><label>الاسم:</label><?= htmlspecialchars($r['first_name'].' '.$r['last_name']) ?></div>
            <div><label>بداية:</label><?= date('Y/m/d', strtotime($r['start_date'])) ?></div>
            <div><label>نهاية:</label><?= date('Y/m/d', strtotime($r['end_date'])) ?></div>
            <div><label>المرفقات:</label>
              <?php if ($r['documents_path']): ?>
                <a href="<?= $r['documents_path'] ?>" target="_blank">عرض</a>
              <?php else: ?>
                -
              <?php endif; ?>
            </div>
            <div>
              <label>الحالة:</label>
              <span class="status-badge <?= $statusKey ?>">
                <?= $arabic ?>
              </span>
            </div>
            <?php if ($statusKey === 'pending'): ?>
              <div class="mobile-actions">
                <form action="accept_reservation.php" method="post">
                  <input type="hidden" name="reservation_id" value="<?= $r['reservation_id'] ?>">
                  <button class="btn small accept">قبول</button>
                </form>
                <form action="reject_reservation.php" method="post">
                  <input type="hidden" name="reservation_id" value="<?= $r['reservation_id'] ?>">
                  <button class="btn small reject">رفض</button>
                </form>
              </div>
            <?php endif; ?>
          </div>
        <?php endwhile; ?>

        <!-- Pagination -->
        <?php $pages = ceil($total/$perPage); if ($pages>1): ?>
          <div class="pagination">
            <?php for($p=1;$p<=$pages;$p++): ?>
              <a 
                href="?item_id=<?= $item_id ?>&status=<?= $filter ?>&page=<?= $p ?>"
                class="<?= $p===$page?'current':'' ?>">
                <?= $p ?>
              </a>
            <?php endfor; ?>
          </div>
        <?php endif; ?>

      <?php else: ?>
        <p class="empty">لا يوجد طلبات حتى الآن.</p>
      <?php endif; ?>
    </section>
  </div>
</body>
</html>