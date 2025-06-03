<?php
session_start();

require_once __DIR__ . '/../database/db.php';

// Redirect if user is not logged in or not admin
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
// include("../main/partials/header-1.php");

// Sanitize inputs
$filter_status  = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_SPECIAL_CHARS) ?: '';
$filter_reserve = filter_input(INPUT_GET, 'reserve', FILTER_SANITIZE_SPECIAL_CHARS) ?: '';
$search_name    = filter_input(INPUT_GET, 'name', FILTER_SANITIZE_SPECIAL_CHARS) ?: '';
$page               = max(1, (int)($_GET['page'] ?? 1));
$perPage            = 10;
$offset             = ($page - 1) * $perPage;

// Build main query
$sql = "
    SELECT
        i.item_id,
        i.name,
        i.description,
        i.image,
        i.created_at,
        i.status,
        i.reserve_status,
        u.first_name,
        u.second_name,
        u.last_name
    FROM items AS i
    LEFT JOIN users AS u ON i.user_id = u.user_id
    WHERE 1=1
";
$params = [];
$types  = '';

if ($filter_status !== '') {
    $sql      .= " AND i.status = ?";
    $params[]  = $filter_status;
    $types    .= 's';
}
if ($filter_reserve !== '') {
    $sql      .= " AND i.reserve_status = ?";
    $params[]  = $filter_reserve;
    $types    .= 's';
}
if ($search_name !== '') {
    $sql      .= " AND i.name LIKE ?";
    $params[]  = "%{$search_name}%";
    $types    .= 's';
}

$sql .= " ORDER BY i.created_at DESC LIMIT ?, ?";
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
    FROM items AS i
    LEFT JOIN users AS u ON i.user_id = u.user_id
    WHERE 1=1
";
$countParams = [];
$countTypes  = '';
if ($filter_status !== '') {
    $countSql     .= " AND i.status = ?";
    $countParams[] = $filter_status;
    $countTypes   .= 's';
}
if ($filter_reserve !== '') {
    $countSql     .= " AND i.reserve_status = ?";
    $countParams[] = $filter_reserve;
    $countTypes   .= 's';
}
if ($search_name !== '') {
    $countSql     .= " AND i.name LIKE ?";
    $countParams[] = "%{$search_name}%";
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
    <title>📦 جميع الأدوات</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Reuse the same variables and styling as reservations */
        /* ... include CSS from reservations page here ... */
    </style>
</head>
<body>

<?php 
    include "./partials/sidebar.php";
?>

<div class="container">
    <h2>📦 جميع الأدوات</h2>

    <form class="filter-form" method="GET" action="">
        <input type="text" name="name" placeholder="🔍 ابحث باسم الأداة" value="<?= htmlspecialchars($search_name) ?>">
        <select name="status">
            <option value="">-- الحالة --</option>
            <?php foreach (['new','good','used'] as $status): ?>
                <option value="<?= $status ?>" <?= $filter_status === $status ? 'selected' : '' ?>><?= $status ?></option>
            <?php endforeach; ?>
        </select>
        <select name="reserve">
            <option value="">-- حالة الحجز --</option>
            <?php foreach (['available','reserved'] as $rs): ?>
                <option value="<?= $rs ?>" <?= $filter_reserve === $rs ? 'selected' : '' ?>><?= $rs ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">🔎 تصفية</button>
    </form>

    <?php if ($result->num_rows > 0): ?>
        <div class="request-grid">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="request-card">
                    <?php if (!empty($row['image'])): ?>
                        <img src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>" style="max-width:100%; border-radius:8px; margin-bottom:10px;">
                    <?php endif; ?>
                    <h3><?= htmlspecialchars($row['name']) ?></h3>
                    <p><?= nl2br(htmlspecialchars(substr($row['description'], 0, 100))) ?><?= strlen($row['description']) > 100 ? '...' : '' ?></p>
                    <p><strong>المالك:</strong> <?= htmlspecialchars("{$row['first_name']} {$row['second_name']} {$row['last_name']}") ?></p>
                    <p><strong>تاريخ الإضافة:</strong> <?= date('d/m/Y', strtotime($row['created_at'])) ?></p>
                    <span class="status <?= htmlspecialchars($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></span>
                    <span class="status <?= htmlspecialchars($row['reserve_status']) ?>"><?= htmlspecialchars($row['reserve_status']) ?></span>
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
        <p>🚫 لا توجد أدوات مطابقة للبحث.</p>
    <?php endif; ?>
</div>

</body>
</html>
<style>
:root {
    --primary-color: #2c3e50;
    --secondary-color: #3498db;
    --success-color: #27ae60;
    --warning-color: #f39c12;
    --danger-color: #e74c3c;
    --light-gray: #ecf0f1;
    --dark-gray: #7f8c8d;
}

body {
    font-family: 'Tajawal', sans-serif;
    background-color: #f5f6fa;
    margin: 0;
    padding: 0;
    color: var(--primary-color);
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

h2 {
    color: var(--primary-color);
    text-align: center;
    margin-bottom: 30px;
    font-size: 2em;
}

.filter-form {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 30px;
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.filter-form input,
.filter-form select {
    padding: 10px 15px;
    border: 1px solid var(--light-gray);
    border-radius: 5px;
    font-size: 16px;
    flex: 1 1 200px;
}

.filter-form button {
    background-color: var(--secondary-color);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s;
}

.filter-form button:hover {
    background-color: #2980b9;
}

.request-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.request-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}

.request-card:hover {
    transform: translateY(-3px);
}

.request-card h3 {
    margin: 0 0 10px 0;
    color: var(--primary-color);
    font-size: 1.2em;
}

.request-card p {
    margin: 5px 0;
    font-size: 0.9em;
    color: var(--dark-gray);
}

.status {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 0.8em;
    margin: 5px 3px;
}

.status.new { background: var(--success-color); color: white; }
.status.good { background: #27ae60; color: white; }
.status.used { background: var(--warning-color); color: white; }
.status.available { background: var(--success-color); color: white; }
.status.reserved { background: var(--danger-color); color: white; }

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 15px;
    margin-top: 30px;
}

.pagination a {
    color: var(--secondary-color);
    text-decoration: none;
    padding: 8px 15px;
    border: 1px solid var(--secondary-color);
    border-radius: 5px;
    transition: all 0.3s;
}

.pagination a:hover {
    background: var(--secondary-color);
    color: white;
}

@media (max-width: 768px) {
    .filter-form {
        flex-direction: column;
    }
    
    .filter-form input,
    .filter-form select,
    .filter-form button {
        width: 100%;
        flex: none;
    }
    
    .request-grid {
        grid-template-columns: 1fr;
    }
}
</style>