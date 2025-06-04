<?php

session_start();
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
    <title>📋 لوحة تحكم الطلبات</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #23cba7;
            --accent: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --success: #2ecc71;
            --warning: #f1c40f;
            --danger: #e74c3c;
            --border-radius: 12px;
            --shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
            --card-bg: #ffffff;
            --sidebar-bg: #1c1f26;
            --sidebar-text: #a0aec0;
            --sidebar-active: #2d3748;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f0f2f5;
            color: #333;
            line-height: 1.6;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            height: 100vh;
            position: fixed;
            transition: var(--transition);
            z-index: 100;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid #2d3748;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-header h2 {
            color: white;
            font-size: 1.3rem;
            margin: 0;
        }

        .sidebar-menu {
            padding: 15px 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 14px 20px;
            color: var(--sidebar-text);
            text-decoration: none;
            transition: var(--transition);
            gap: 12px;
            font-size: 1rem;
        }

        .sidebar-menu a:hover {
            background: var(--sidebar-active);
            color: white;
        }

        .sidebar-menu a.active {
            background: var(--primary);
            color: white;
        }

        .sidebar-menu a i {
            width: 24px;
            text-align: center;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-right: 260px;
            padding: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--light-gray);
        }

        .page-title {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .page-title h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0;
        }

        .page-title i {
            background: var(--primary);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        /* Dashboard Cards */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .dashboard-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--shadow);
            display: flex;
            flex-direction: column;
        }

        .card-title {
            font-size: 1rem;
            color: var(--gray);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-value {
            font-size: 1rem;
            font-weight: 700;
            color: var(--dark);
        }

        .card-info {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid var(--light-gray);
            color: var(--gray);
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Filter Section */
        .filter-section {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--dark);
        }

        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-size: 0.9rem;
            margin-bottom: 8px;
            color: var(--gray);
        }

        .form-control {
            padding: 12px 15px;
            border: 1px solid var(--light-gray);
            border-radius: 8px;
            font-family: inherit;
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-family: inherit;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            align-self: flex-end;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        /* Request Cards */
        .request-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }

        .request-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: var(--transition);
        }

        .request-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            padding: 20px;
            border-bottom: 1px solid var(--light-gray);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-user {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar-sm {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .user-details h3 {
            font-size: 1.1rem;
            margin: 0;
            color: var(--dark);
        }

        .user-details p {
            font-size: 0.85rem;
            color: var(--gray);
            margin: 0;
        }

        .status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status.pending {
            background: rgba(241, 196, 15, 0.15);
            color: #c29d0b;
        }

        .status.accepted {
            background: rgba(46, 204, 113, 0.15);
            color: #27ae60;
        }

        .status.rejected {
            background: rgba(231, 76, 60, 0.15);
            color: #c0392b;
        }

        .card-body {
            padding: 20px;
        }

        .card-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--light-gray);
        }

        .card-row:last-child {
            border-bottom: none;
        }

        .card-label {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .card-value {
            font-weight: 500;
            color: var(--dark);
            text-align: left;
        }

        .card-actions {
            padding: 15px 20px;
            border-top: 1px solid var(--light-gray);
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .btn-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--light-gray);
            color: var(--gray);
            transition: var(--transition);
            border: none;
            cursor: pointer;
        }

        .btn-icon:hover {
            background: var(--primary);
            color: white;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 40px;
        }

        .pagination a, .pagination span {
            padding: 10px 16px;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pagination a {
            background: white;
            color: var(--primary);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .pagination a:hover {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 8px rgba(67, 97, 238, 0.3);
        }

        .pagination span {
            background: var(--primary);
            color: white;
        }

        .pagination i {
            font-size: 0.9rem;
        }

        /* Empty State */
        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 50px 20px;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--light-gray);
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            color: var(--gray);
            margin-bottom: 15px;
        }

        .empty-state p {
            color: var(--gray);
            max-width: 500px;
            margin: 0 auto;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
            }
            .sidebar-header h2, .sidebar-menu a span {
                display: none;
            }
            .sidebar-menu a {
                justify-content: center;
            }
            .main-content {
                margin-right: 80px;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 20px 15px;
            }
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
            }
            .filter-form {
                grid-template-columns: 1fr;
            }
            .request-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="container">

        <?php 
          include '../main/partials/sidebar.php';
        ?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <div class="page-title">
                    <i class="fas fa-clipboard-list"></i>
                    <h1>لوحة تحكم الطلبات</h1>
                </div>
                <div class="user-info">
                    <div class="user-avatar">أد</div>
                    <div>
                        <h3>المشرف أحمد</h3>
                        <p>مسؤول النظام</p>
                    </div>
                </div>
            </div>

            <!-- Dashboard Cards -->
            <div class="dashboard-cards">
                <div class="dashboard-card">
                    <div class="card-title">
                        <i class="fas fa-clock"></i>
                        <span>الطلبات المعلقة</span>
                    </div>
                    <div class="card-value">24</div>
                    <div class="card-info">
                        <i class="fas fa-arrow-up"></i>
                        <span>زيادة 12% عن الشهر الماضي</span>
                    </div>
                </div>
                <div class="dashboard-card">
                    <div class="card-title">
                        <i class="fas fa-check-circle"></i>
                        <span>الطلبات المقبولة</span>
                    </div>
                    <div class="card-value">142</div>
                    <div class="card-info">
                        <i class="fas fa-arrow-up"></i>
                        <span>زيادة 8% عن الشهر الماضي</span>
                    </div>
                </div>
                <div class="dashboard-card">
                    <div class="card-title">
                        <i class="fas fa-times-circle"></i>
                        <span>الطلبات المرفوضة</span>
                    </div>
                    <div class="card-value">18</div>
                    <div class="card-info">
                        <i class="fas fa-arrow-down"></i>
                        <span>انخفاض 5% عن الشهر الماضي</span>
                    </div>
                </div>
                <div class="dashboard-card">
                    <div class="card-title">
                        <i class="fas fa-users"></i>
                        <span>المستخدمون النشطون</span>
                    </div>
                    <div class="card-value">63</div>
                    <div class="card-info">
                        <i class="fas fa-arrow-up"></i>
                        <span>زيادة 15% عن الشهر الماضي</span>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <div class="section-title">
                    <i class="fas fa-filter"></i>
                    <span>تصفية الطلبات</span>
                </div>
                <form class="filter-form" method="GET" action="">
                    <div class="form-group">
                        <label for="user">البحث بالمستفيد</label>
                        <input type="text" id="user" name="user" class="form-control" placeholder="اسم المستفيد..." value="<?= htmlspecialchars($search_user) ?>">
                    </div>
                    <div class="form-group">
                        <label for="status">حالة الطلب</label>
                        <select id="status" name="status" class="form-control">
                            <option value="">جميع الحالات</option>
                            <option value="pending" <?= $filter_status === 'pending' ? 'selected' : '' ?>>معلق</option>
                            <option value="accepted" <?= $filter_status === 'accepted' ? 'selected' : '' ?>>مقبول</option>
                            <option value="rejected" <?= $filter_status === 'rejected' ? 'selected' : '' ?>>مرفوض</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                            تطبيق التصفية
                        </button>
                    </div>
                </form>
            </div>

            <!-- Requests Section -->
            <div class="section-title">
                <i class="fas fa-list"></i>
                <span>جميع الطلبات</span>
            </div>

            <?php if ($result->num_rows > 0): ?>
                <div class="request-grid">
                    <?php while ($row = $result->fetch_assoc()): 
                        $user_initials = mb_substr($row['first_name'], 0, 1) . mb_substr($row['last_name'], 0, 1);
                        $status_class = '';
                        $status_text = '';
                        switch ($row['status']) {
                            case 'pending':
                                $status_class = 'pending';
                                $status_text = 'معلق';
                                break;
                            case 'accepted':
                                $status_class = 'accepted';
                                $status_text = 'مقبول';
                                break;
                            case 'rejected':
                                $status_class = 'rejected';
                                $status_text = 'مرفوض';
                                break;
                        }
                    ?>
                    <div class="request-card">
                        <div class="card-header">
                            <div class="card-user">
                                <div class="user-avatar-sm"><?= $user_initials ?></div>
                                <div class="user-details">
                                    <h3><?= htmlspecialchars("{$row['first_name']} {$row['last_name']}") ?></h3>
                                    <p>#<?= htmlspecialchars($row['reservation_id']) ?></p>
                                </div>
                            </div>
                            <span class="status <?= $status_class ?>"><?= $status_text ?></span>
                        </div>
                        <div class="card-body">
                            <div class="card-row">
                                <span class="card-label">الأداة</span>
                                <span class="card-value"><?= htmlspecialchars($row['item_name'] ?: 'غير محددة') ?></span>
                            </div>
                            <div class="card-row">
                                <span class="card-label">تاريخ البدء</span>
                                <span class="card-value"><?= date('d/m/Y', strtotime($row['start_date'])) ?></span>
                            </div>
                            <div class="card-row">
                                <span class="card-label">تاريخ الانتهاء</span>
                                <span class="card-value"><?= date('d/m/Y', strtotime($row['end_date'])) ?></span>
                            </div>
                        </div>
                        <div class="card-actions">
                            <?php if (!empty($row['documents_path'])): ?>
                                <a href="<?= htmlspecialchars($row['documents_path']) ?>" target="_blank" class="btn-icon" title="عرض الوثيقة">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            <?php endif; ?>
                            <button class="btn-icon" title="تعديل الطلب">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon" title="حذف الطلب">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>

                <!-- Pagination -->
                <nav class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                            <i class="fas fa-arrow-right"></i>
                            السابق
                        </a>
                    <?php endif; ?>
                    
                    <span>صفحة <?= $page ?> من <?= $totalPages ?></span>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                            التالي
                            <i class="fas fa-arrow-left"></i>
                        </a>
                    <?php endif; ?>
                </nav>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>لا توجد طلبات</h3>
                    <p>لم يتم العثور على أي طلبات تطابق معايير البحث الخاصة بك. حاول تغيير عوامل التصفية أو إنشاء طلب جديد.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Add active class to clicked sidebar items
        document.querySelectorAll('.sidebar-menu a').forEach(link => {
            link.addEventListener('click', function() {
                document.querySelectorAll('.sidebar-menu a').forEach(item => {
                    item.classList.remove('active');
                });
                this.classList.add('active');
            });
        });

        // Add hover effects to cards
        document.querySelectorAll('.request-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>