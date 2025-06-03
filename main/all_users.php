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
$filter_role = htmlspecialchars(trim($_GET['role'] ?? ''));
$search_term = htmlspecialchars(trim($_GET['search'] ?? ''));
$page          = max(1, (int)($_GET['page'] ?? 1));
$perPage       = 10;
$offset        = ($page - 1) * $perPage;

// Build main query
$sql = "
    SELECT
        u.user_id,
        u.first_name,
        u.second_name,
        u.last_name,
        u.email,
        u.phone,
        u.role,
        u.document,
        u.created_at,
        u.city_id,
        u.location,
        u.street
    FROM users AS u
    WHERE 1=1
";
$params = [];
$types  = '';

if ($filter_role !== '') {
    $sql      .= " AND u.role = ?";
    $params[]  = $filter_role;
    $types    .= 's';
}
if ($search_term !== '') {
    $sql      .= " AND (u.first_name LIKE ? OR u.second_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
    $like      = "%{$search_term}%";
    $params[]  = $like;
    $params[]  = $like;
    $params[]  = $like;
    $params[]  = $like;
    $types    .= 'ssss';
}

$sql .= " ORDER BY u.created_at DESC LIMIT ?, ?";
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
    FROM users AS u
    WHERE 1=1
";
$countParams = [];
$countTypes  = '';
if ($filter_role !== '') {
    $countSql     .= " AND u.role = ?";
    $countParams[] = $filter_role;
    $countTypes   .= 's';
}
if ($search_term !== '') {
    $countSql     .= " AND (u.first_name LIKE ? OR u.second_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
    $countParams[] = $like;
    $countParams[] = $like;
    $countParams[] = $like;
    $countParams[] = $like;
    $countTypes   .= 'ssss';
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
    <title>👥 جميع المستخدمين</title>
    <link rel="stylesheet" href="style.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --light-gray: #ecf0f1;
            --dark-gray: #7f8c8d;
            --border-radius: 10px;
            --shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: var(--light-gray);
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
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }
        .filter-form input,
        .filter-form select {
            padding: 10px 15px;
            border: 1px solid var(--dark-gray);
            border-radius: var(--border-radius);
            font-size: 16px;
            flex: 1 1 200px;
        }
        .filter-form button {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: background 0.3s;
        }
        .filter-form button:hover {
            background-color: #2980b9;
        }
        .user-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        .user-card {
            background: white;
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            transition: transform 0.2s;
        }
        .user-card:hover {
            transform: translateY(-3px);
        }
        .user-card h3 {
            margin: 0 0 10px;
            font-size: 1.2em;
        }
        .user-card p {
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
            background: var(--secondary-color);
            color: white;
        }
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
            border-radius: var(--border-radius);
            transition: all 0.3s;
        }
        .pagination a:hover {
            background: var(--secondary-color);
            color: white;
        }
        @media (max-width: 768px) {
            .filter-form { flex-direction: column; }
            .filter-form input,
            .filter-form select,
            .filter-form button { width: 100%; flex: none; }
            .user-grid { grid-template-columns: 1fr; }
        }
        .btn {
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background-color: #3498db;
            color: white;
        }

        .btn-warning {
            background-color: #f39c12;
            color: white;
        }

        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }

        .btn:hover {
            opacity: 0.8;
        }


    </style>
</head>
<body>

<?php 
    include "./partials/sidebar.php";
?>
    
<div class="container">
    <h2>👥 جميع المستخدمين</h2>
    <a href="create_user.php" class="btn btn-primary">إضافة مستخدم جديد</a>

    <form class="filter-form" method="GET" action="">
        <input type="text" name="search" placeholder="🔍 ابحث باسم أو بريد" value="<?= htmlspecialchars($search_term) ?>">
        <select name="role">
            <option value="">-- الدور --</option>
            <?php foreach (['user','admin','lender'] as $role): ?>
                <option value="<?= $role ?>" <?= $filter_role === $role ? 'selected' : '' ?>><?= $role ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">🔎 تصفية</button>
    </form>

    <?php if ($result->num_rows > 0): ?>
        <div class="user-grid">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="user-card">
    <h3><?= htmlspecialchars("{$row['first_name']} {$row['second_name']} {$row['last_name']}") ?></h3>
    <p><strong>البريد:</strong> <?= htmlspecialchars($row['email']) ?></p>
    <p><strong>الهاتف:</strong> <?= htmlspecialchars($row['phone']) ?></p>
    <p><strong>العنوان:</strong> <?= htmlspecialchars("{$row['street']}, {$row['location']}") ?></p>
    <p><strong>المدينة ID:</strong> <?= htmlspecialchars($row['city_id']) ?></p>
    
    <!-- Check if document exists, then display either a link or embedded PDF -->
    <?php if (!empty($row['document'])): ?>
    <p><a href="<?= '/MEDICAL-SYSTEM/main' . htmlspecialchars($row['document']) ?>" target="_blank">📎 المستند</a></p>
<?php endif; ?>

    <p><strong>تاريخ التسجيل:</strong> <?= date('d/m/Y', strtotime($row['created_at'])) ?></p>
    <span class="status"><?= htmlspecialchars($row['role']) ?></span>

    <!-- Edit and Delete Buttons -->
    <div>
        <a href="edit_user.php?user_id=<?= $row['user_id'] ?>" class="btn btn-warning">تعديل</a>
        <a href="delete_user.php?user_id=<?= $row['user_id'] ?>" class="btn btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا المستخدم؟')">حذف</a>
    </div>
</div>
            <?php endwhile; ?>
        </div>

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
        <p>🚫 لا يوجد مستخدمون مطابقون للبحث.</p>
    <?php endif; ?>
</div>
</body>
</html>
