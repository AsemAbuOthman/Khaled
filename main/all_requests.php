<?php

session_start();
// include("../main/partials/header-1.php");


require_once __DIR__ . '/../database/db.php';

// Redirect if user is not logged in or not admin
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Sanitize inputs
$filter_status = htmlspecialchars(trim($_GET['status'] ?? ''));
$search_user   = htmlspecialchars(trim($_GET['user'] ?? ''));
$page          = max(1, (int)($_GET['page'] ?? 1));
$perPage       = 10;
$offset        = ($page - 1) * $perPage;

// Build main query
$sql = "
    SELECT
        r.reservation_id,
        r.start_date,
        r.end_date,
        r.status,
        r.documents_path,
        u.first_name,
        u.second_name,
        u.last_name,
        i.name AS item_name
    FROM reservations AS r
    INNER JOIN users AS u ON r.user_id = u.user_id
    LEFT JOIN items AS i ON r.item_id = i.item_id
    WHERE 1=1
";
$params = [];
$types  = '';

if ($filter_status !== '') {
    $sql     .= " AND r.status = ?";
    $params[] = $filter_status;
    $types   .= 's';
}
if ($search_user !== '') {
    $sql     .= " AND CONCAT_WS(' ', u.first_name, u.second_name, u.last_name) LIKE ?";
    $params[] = "%$search_user%";
    $types   .= 's';
}

$sql .= " ORDER BY r.created_at DESC LIMIT ?, ?";
$params[] = $offset;
$params[] = $perPage;
$types   .= 'ii';

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die('Error preparing statement: ' . htmlspecialchars($conn->error));
}
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Count total for pagination
$countSql = "
    SELECT COUNT(*)
    FROM reservations AS r
    INNER JOIN users AS u ON r.user_id = u.user_id
    LEFT JOIN items AS i ON r.item_id = i.item_id
    WHERE 1=1
";
$countParams = [];
$countTypes  = '';
if ($filter_status !== '') {
    $countSql     .= " AND r.status = ?";
    $countParams[] = $filter_status;
    $countTypes   .= 's';
}
if ($search_user !== '') {
    $countSql     .= " AND CONCAT_WS(' ', u.first_name, u.second_name, u.last_name) LIKE ?";
    $countParams[] = "%$search_user%";
    $countTypes   .= 's';
}
$countStmt = $conn->prepare($countSql);
if ($countParams) {
    $countStmt->bind_param($countTypes, ...$countParams);
}
$countStmt->execute();
$total = $countStmt->get_result()->fetch_row()[0];
$totalPages = (int) ceil($total / $perPage);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📋 جميع الطلبات</title>
    <link rel="stylesheet" href="style.css">
    <style>
:root {
  --primary-color: #4a90e2;
  --primary-hover: #357ab8;
  --bg-light: #f9f9fb;
  --bg-dark: #1c1f26;
  --card-bg: #ffffff;
  --card-bg-alt: #292e3b;
  --text-light: #eaecef;
  --text-dark: #1e1e1e;
  --border-radius: 14px;
  --shadow: 0 8px 16px rgba(0, 0, 0, 0.08);
  --transition: 0.3s ease;
  --accent: #23cba7;
  --danger: #e74c3c;
  --success: #2ecc71;
  --warning: #f1c40f;
}

body {
  font-family: "Inter", "Segoe UI", Tahoma, sans-serif;
  background-color: var(--bg-light);
  color: var(--text-dark);
  margin: 0;
  padding: 0;
  line-height: 1.7;
  transition: all var(--transition);
}
body.dark-mode {
  background-color: var(--bg-dark);
  color: var(--text-light);
}

.container {
  max-width: 1200px;
  margin: auto;
  padding: 40px 20px;
}

h2 {
  font-size: 2.2rem;
  font-weight: 600;
  margin-bottom: 30px;
  text-align: center;
  color: var(--primary-color);
}

/* Filter Form */
.filter-form {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  justify-content: flex-end;
  margin-bottom: 35px;
}

.filter-form input[type="text"],
.filter-form select {
  padding: 12px 16px;
  font-size: 1rem;
  border-radius: var(--border-radius);
  border: 1px solid #ccc;
  background-color: #fff;
  transition: border-color var(--transition);
  min-width: 200px;
}
.filter-form input:focus,
.filter-form select:focus {
  border-color: var(--primary-color);
  outline: none;
}

.filter-form button {
  padding: 12px 18px;
  background-color: var(--primary-color);
  color: #fff;
  border: none;
  font-weight: 600;
  border-radius: var(--border-radius);
  cursor: pointer;
  transition: background-color var(--transition), transform var(--transition);
}

.filter-form button:hover {
  background-color: var(--primary-hover);
  transform: scale(1.05);
}

/* Request Cards */
.request-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 24px;
}

.request-card {
  background-color: var(--card-bg);
  padding: 22px;
  border-radius: var(--border-radius);
  box-shadow: var(--shadow);
  transition: all var(--transition);
  display: flex;
  flex-direction: column;
  gap: 10px;
}
body.dark-mode .request-card {
  background-color: var(--card-bg-alt);
}
.request-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
}

.status {
  display: inline-block;
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: bold;
  align-self: flex-start;
  text-transform: capitalize;
}

.status.معلق {
  background-color: var(--warning);
  color: #000;
}

.status.مقبول {
  background-color: var(--success);
  color: #fff;
}

.status.مرفوض {
  background-color: var(--danger);
  color: #fff;
}

.request-card p {
  font-size: 0.95rem;
  margin: 0;
}
.request-card a {
  text-decoration: none;
  color: var(--primary-color);
  font-weight: 500;
  transition: color var(--transition);
}
.request-card a:hover {
  color: var(--primary-hover);
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 20px;
  margin-top: 40px;
}
.pagination a {
  padding: 10px 16px;
  background-color: var(--primary-color);
  color: #fff;
  border-radius: var(--border-radius);
  font-weight: 500;
  text-decoration: none;
  transition: background-color var(--transition), transform var(--transition);
}
.pagination a:hover {
  background-color: var(--primary-hover);
  transform: scale(1.05);
}
.pagination span {
  font-size: 1rem;
  font-weight: 500;
}

/* Responsive */
@media (max-width: 768px) {
  .filter-form {
    flex-direction: column;
    align-items: stretch;
  }
  .filter-form input,
  .filter-form select {
    width: 100%;
  }
}
  
    </style>
</head>

<body>

<?php 
    include "./partials/sidebar.php";
?>

<div class="container">
    <h2>📋 جميع الطلبات</h2>

    <form class="filter-form" method="GET" action="">
        <input type="text" name="user" placeholder="🔍 ابحث باسم المستفيد" value="<?= htmlspecialchars($search_user) ?>">
        <select name="status">
            <option value="">-- الحالة --</option>
            <?php foreach (['pending','accepted','rejected'] as $status): ?>
                <option value="<?= $status ?>" <?= $filter_status === $status ? 'selected' : '' ?>><?= $status ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">🔎 تصفية</button>
    </form>

    <?php if ($result->num_rows > 0): ?>
        <div class="request-grid">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="request-card">
                    <span class="status <?= htmlspecialchars($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></span>
                    <p><strong>المستفيد:</strong> <?= htmlspecialchars("{$row['first_name']} {$row['second_name']} {$row['last_name']}") ?></p>
                    <p><strong>الأداة:</strong> <?= htmlspecialchars($row['item_name'] ?: 'غير محددة') ?></p>
                    <p><strong>التاريخ:</strong> <?= date('d/m/Y', strtotime($row['start_date'])) ?> → <?= date('d/m/Y', strtotime($row['end_date'])) ?></p>
                    <?php if (!empty($row['documents_path'])): ?>
                        <a href="<?= htmlspecialchars($row['documents_path']) ?>" target="_blank">📎 الوثيقة</a>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Pagination -->
        <nav class="pagination">
            <?php if ($page > 1): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">← السابق</a>
            <?php endif; ?>
            <span>صفحة <?= $page ?> من <?= $totalPages ?></span>
            <?php if ($page < $totalPages): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">التالي →</a>
            <?php endif; ?>
        </nav>
    <?php else: ?>
        <p>🚫 لا توجد طلبات مطابقة للبحث.</p>
    <?php endif; ?>
</div>
</body>
</html>
