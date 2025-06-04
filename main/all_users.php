<?php
session_start();
require_once __DIR__ . '/../database/db.php';

// Redirect if user is not logged in or not admin
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Sanitize inputs
$filter_role = htmlspecialchars(trim($_GET['role'] ?? ''));
$search_term = htmlspecialchars(trim($_GET['search'] ?? ''));
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

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
$types = '';

if ($filter_role !== '') {
    $sql .= " AND u.role = ?";
    $params[] = $filter_role;
    $types .= 's';
}
if ($search_term !== '') {
    $sql .= " AND (u.first_name LIKE ? OR u.second_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
    $like = "%{$search_term}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= 'ssss';
}

$sql .= " ORDER BY u.created_at DESC LIMIT ?, ?";
$params[] = $offset;
$params[] = $perPage;
$types .= 'ii';

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
$countTypes = '';
if ($filter_role !== '') {
    $countSql .= " AND u.role = ?";
    $countParams[] = $filter_role;
    $countTypes .= 's';
}
if ($search_term !== '') {
    $countSql .= " AND (u.first_name LIKE ? OR u.second_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
    $countParams[] = $like;
    $countParams[] = $like;
    $countParams[] = $like;
    $countParams[] = $like;
    $countTypes .= 'ssss';
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
    <title>👥 إدارة المستخدمين</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #3f37c9;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --success: #4cc9f0;
            --warning: #fca311;
            --danger: #e63946;
            --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
            --border-radius: 12px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f5f7ff;
            color: var(--dark);
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }

        h1 {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .actions {
            display: flex;
            gap: 15px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            border: none;
            outline: none;
        }

        .btn i {
            font-size: 1.1rem;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(67, 97, 238, 0.4);
        }

        .btn-outline {
            background: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-outline:hover {
            background: rgba(67, 97, 238, 0.1);
        }

        .filter-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: var(--card-shadow);
        }

        .filter-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            color: var(--primary);
            font-size: 1.2rem;
            font-weight: 600;
        }

        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--gray);
        }

        .form-control {
            padding: 14px 16px;
            border: 1px solid var(--light-gray);
            border-radius: 8px;
            font-size: 1rem;
            font-family: 'Tajawal', sans-serif;
            transition: var(--transition);
            background: white;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
            outline: none;
        }

        .submit-btn {
            align-self: flex-end;
            background: var(--primary);
            color: white;
            border: none;
            padding: 14px 24px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .submit-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
        }

        .user-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .user-card {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            position: relative;
            border-top: 4px solid var(--primary);
        }

        .user-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            padding: 20px;
            border-bottom: 1px solid var(--light-gray);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-name {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0;
        }

        .user-role {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .role-admin {
            background: rgba(67, 97, 238, 0.15);
            color: var(--primary);
        }

        .role-user {
            background: rgba(76, 201, 240, 0.15);
            color: #0d96b6;
        }

        .role-lender {
            background: rgba(252, 163, 17, 0.15);
            color: #d18a0a;
        }

        .card-body {
            padding: 20px;
        }

        .user-detail {
            display: flex;
            align-items: flex-start;
            margin-bottom: 16px;
            gap: 12px;
        }

        .detail-icon {
            width: 36px;
            height: 36px;
            background: rgba(67, 97, 238, 0.1);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            flex-shrink: 0;
        }

        .detail-content h4 {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--gray);
            margin-bottom: 4px;
        }

        .detail-content p {
            font-size: 1rem;
            font-weight: 500;
            color: var(--dark);
            word-break: break-all;
        }

        .document-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: rgba(76, 201, 240, 0.1);
            border-radius: 8px;
            color: #0d96b6;
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            margin-top: 8px;
        }

        .document-link:hover {
            background: rgba(76, 201, 240, 0.2);
        }

        .card-footer {
            padding: 15px 20px;
            background: var(--light);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid var(--light-gray);
        }

        .user-date {
            font-size: 0.9rem;
            color: var(--gray);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-actions {
            display: flex;
            gap: 12px;
        }

        .action-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            color: white;
            font-size: 1rem;
            border: none;
        }

        .edit-btn {
            background: var(--warning);
        }

        .delete-btn {
            background: var(--danger);
        }

        .action-btn:hover {
            transform: scale(1.1);
        }

        .no-results {
            grid-column: 1 / -1;
            text-align: center;
            padding: 50px 20px;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
        }

        .no-results-icon {
            font-size: 4rem;
            color: var(--light-gray);
            margin-bottom: 20px;
        }

        .no-results h3 {
            font-size: 1.5rem;
            color: var(--gray);
            margin-bottom: 10px;
        }

        .no-results p {
            color: var(--gray);
            max-width: 500px;
            margin: 0 auto;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .page-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: white;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
        }

        .page-link:hover, .page-link.active {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
        }

        .page-link.disabled {
            opacity: 0.5;
            pointer-events: none;
        }

        .page-info {
            padding: 10px 20px;
            background: white;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            font-weight: 500;
            color: var(--gray);
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .actions {
                width: 100%;
                justify-content: center;
            }
            
            .filter-form {
                grid-template-columns: 1fr;
            }
            
            .user-grid {
                grid-template-columns: 1fr;
            }
            
            .submit-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<?php 
    include "./partials/sidebar.php";
?>

<div class="container">
    <div class="header">
        <h1>
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            إدارة المستخدمين
        </h1>
        <div class="actions">
            <a href="create_user.php" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                إضافة مستخدم جديد
            </a>
            <a href="#" class="btn btn-outline">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                تصدير البيانات
            </a>
        </div>
    </div>

    <div class="filter-card">
        <div class="filter-header">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
            </svg>
            تصفية النتائج
        </div>
        <form class="filter-form" method="GET" action="">
            <div class="form-group">
                <label for="search">بحث</label>
                <input type="text" id="search" name="search" class="form-control" placeholder="ابحث باسم أو بريد إلكتروني..." value="<?= htmlspecialchars($search_term) ?>">
            </div>
            <div class="form-group">
                <label for="role">الدور</label>
                <select id="role" name="role" class="form-control">
                    <option value="">كل الأدوار</option>
                    <?php foreach (['user' => 'مستخدم', 'admin' => 'مدير', 'lender' => 'مقرض'] as $role => $label): ?>
                        <option value="<?= $role ?>" <?= $filter_role === $role ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="submit-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                تطبيق التصفية
            </button>
        </form>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <div class="user-grid">
            <?php while ($row = $result->fetch_assoc()): 
                $roleClass = 'role-' . $row['role'];
                $roleLabel = [
                    'admin' => 'مدير',
                    'user' => 'مستخدم',
                    'lender' => 'مقرض'
                ][$row['role']] ?? $row['role'];
            ?>
                <div class="user-card">
                    <div class="card-header">
                        <h3 class="user-name"><?= htmlspecialchars("{$row['first_name']} {$row['last_name']}") ?></h3>
                        <span class="user-role <?= $roleClass ?>"><?= $roleLabel ?></span>
                    </div>
                    <div class="card-body">
                        <div class="user-detail">
                            <div class="detail-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                            </div>
                            <div class="detail-content">
                                <h4>البريد الإلكتروني</h4>
                                <p><?= htmlspecialchars($row['email']) ?></p>
                            </div>
                        </div>
                        
                        <div class="user-detail">
                            <div class="detail-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                </svg>
                            </div>
                            <div class="detail-content">
                                <h4>الهاتف</h4>
                                <p><?= htmlspecialchars($row['phone']) ?: '--' ?></p>
                            </div>
                        </div>
                        
                        <div class="user-detail">
                            <div class="detail-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                            </div>
                            <div class="detail-content">
                                <h4>العنوان</h4>
                                <p>
                                    <?= htmlspecialchars($row['street'] ? "{$row['street']}, " : '') ?>
                                    <?= htmlspecialchars($row['location'] ? "{$row['location']}, " : '') ?>
                                    <?= htmlspecialchars($row['city_id'] ? "المدينة {$row['city_id']}" : '') ?>
                                </p>
                            </div>
                        </div>
                        
                        <?php if (!empty($row['document'])): ?>
                        <div class="user-detail">
                            <div class="detail-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                    <polyline points="10 9 9 9 8 9"></polyline>
                                </svg>
                            </div>
                            <div class="detail-content">
                                <h4>المستندات</h4>
                                <a href="<?= '/MEDICAL-SYSTEM/main' . htmlspecialchars($row['document']) ?>" target="_blank" class="document-link">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                        <polyline points="15 3 21 3 21 9"></polyline>
                                        <line x1="10" y1="14" x2="21" y2="3"></line>
                                    </svg>
                                    عرض المستند
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer">
                        <div class="user-date">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            <?= date('d/m/Y', strtotime($row['created_at'])) ?>
                        </div>
                        <div class="card-actions">
                            <a href="edit_user.php?user_id=<?= $row['user_id'] ?>" class="action-btn edit-btn" title="تعديل">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </a>
                            <a href="delete_user.php?user_id=<?= $row['user_id'] ?>" class="action-btn delete-btn" title="حذف" onclick="return confirm('هل أنت متأكد من حذف هذا المستخدم؟')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    <line x1="10" y1="11" x2="10" y2="17"></line>
                                    <line x1="14" y1="11" x2="14" y2="17"></line>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>" class="page-link" title="الصفحة الأولى">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="11 17 6 12 11 7"></polyline>
                        <polyline points="18 17 13 12 18 7"></polyline>
                    </svg>
                </a>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="page-link" title="السابق">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                </a>
            <?php else: ?>
                <span class="page-link disabled">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="11 17 6 12 11 7"></polyline>
                        <polyline points="18 17 13 12 18 7"></polyline>
                    </svg>
                </span>
                <span class="page-link disabled">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                </span>
            <?php endif; ?>

            <div class="page-info">
                صفحة <?= $page ?> من <?= $totalPages ?>
            </div>

            <?php if ($page < $totalPages): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="page-link" title="التالي">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $totalPages])) ?>" class="page-link" title="الصفحة الأخيرة">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="13 17 18 12 13 7"></polyline>
                        <polyline points="6 17 11 12 6 7"></polyline>
                    </svg>
                </a>
            <?php else: ?>
                <span class="page-link disabled">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </span>
                <span class="page-link disabled">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="13 17 18 12 13 7"></polyline>
                        <polyline points="6 17 11 12 6 7"></polyline>
                    </svg>
                </span>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="no-results">
            <div class="no-results-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            <h3>لم يتم العثور على مستخدمين</h3>
            <p>لا توجد نتائج مطابقة لمعايير البحث الخاصة بك. حاول تغيير عوامل التصفية الخاصة بك أو ابحث عن مصطلح مختلف.</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>