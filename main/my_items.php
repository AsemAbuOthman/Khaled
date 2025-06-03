<?php
session_start();

include("../main/partials/header-1.php");
include __DIR__ . '/../database/db.php'; 

// Redirect if user is not logged in or is not a lender
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'lender') {
    header("Location: login.php");
    exit;
}

$lender_id = $_SESSION['user_id'];

// Pagination setup
$perPage = 8;
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

// Count total items for this lender
$countResult = $conn->query("SELECT COUNT(*) as total FROM items WHERE user_id = $lender_id");
$totalItems = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalItems / $perPage);

// Fetch paginated items
$sql = "SELECT * FROM items WHERE user_id = $lender_id ORDER BY created_at DESC LIMIT $perPage OFFSET $offset";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>أدواتي</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body {
      background: linear-gradient(to bottom, #f0f8ff, #ffffff);
      font-family: 'Tajawal', sans-serif;
      margin: 0;
      padding: 0;
    }
    .container {
      max-width: 1000px;
      margin: 40px auto;
      padding: 20px;
    }
    .items-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 20px;
      margin-top: 30px;
    }

    .item-card {
      background-color: #fff;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 0 10px rgba(0,0,0,0.06);
      transition: 0.3s ease;
      text-align: center;
    }

    .item-card:hover {
      transform: translateY(-4px);
    }

    .item-card img {
      max-width: 100%;
      max-height: 160px;
      border-radius: 8px;
      margin-bottom: 12px;
    }

    .item-card h3 {
      color: #0077cc;
      margin-bottom: 8px;
    }

    .item-card p {
      color: #555;
      margin: 6px 0;
    }

    .item-card .status {
      font-weight: bold;
      color: green;
    }

    .item-card .unavailable {
      font-weight: bold;
      color: red;
    }

    .item-card .actions {
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 12px;
    }

    .item-card a {
      padding: 8px 14px;
      border-radius: 6px;
      background-color: #0077cc;
      color: white;
      text-decoration: none;
      font-size: 0.95em;
      font-weight: bold;
    }

    .item-card a:hover {
      background-color: #005fa3;
    }

    .item-card .delete-btn {
      background-color: #dc3545;
    }

    .item-card .delete-btn:hover {
      background-color: #b02a37;
    }

    .pagination {
      text-align: center;
      margin: 30px 0;
    }

    .pagination a {
      margin: 0 5px;
      text-decoration: none;
      padding: 6px 12px;
      border-radius: 4px;
      border: 1px solid #0077cc;
      color: #0077cc;
    }

    .pagination a.active {
      background-color: #0077cc;
      color: #fff;
    }
  </style>
</head>
<body>

<div class="container">
  <h2>🧰 أدواتي</h2>

  <?php if ($result->num_rows > 0): ?>
    <div class="items-grid">
      <?php while ($item = $result->fetch_assoc()): ?>
        <div class="item-card">
          <?php if (!empty($item['image']) && file_exists($item['image'])): ?>
            <img src="<?= htmlspecialchars($item['image']) ?>" alt="صورة الأداة">
          <?php endif; ?>
          <h3><?= htmlspecialchars($item['name']) ?></h3>
          <p><?= htmlspecialchars($item['description']) ?></p>
          <p class="<?= $item['reserve_status'] === 'available' ? 'status' : 'unavailable' ?>">
            <?= $item['reserve_status'] === 'available' ? 'متاح' : 'غير متاحة' ?>
          </p>
          <div class="actions">
            <a href="/Medical-System/main/view_item.php?item_id=<?= $item['item_id'] ?>">🔍 عرض</a>
            <a href="/Medical-System/main/edit_item.php?item_id=<?= $item['item_id'] ?>">✏️ تعديل</a>
            <a class="delete-btn" href="delete_item.php?item_id=<?= $item['item_id'] ?>" onclick="return confirm('هل أنت متأكد من حذف الأداة؟');">🗑️ حذف</a>
          </div>
        </div>
      <?php endwhile; ?>
    </div>

    <?php if ($totalPages > 1): ?>
      <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <a href="?page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>

  <?php else: ?>
    <p style="text-align: center; color: #666;">🔕 لا توجد أدوات مضافة بعد.</p>
  <?php endif; ?>
</div>

</body>
</html>

<?php $conn->close(); ?>
