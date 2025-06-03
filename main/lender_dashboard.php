<?php
session_start();
include("../main/partials/header-1.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'lender') {
    header("Location: login.php");
    exit;
}

include __DIR__ . '../../database/db.php'; 

$users_count = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
$requests_count = $conn->query("SELECT COUNT(*) FROM reservations")->fetch_row()[0];
$items_count = $conn->query("SELECT COUNT(*) FROM items")->fetch_row()[0];

$recent_users = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

$recent_requests = $conn->query("SELECT r.user_id, u.first_name, i.item_id, i.name AS item_name, r.status 
                                FROM reservations r
                                JOIN users u ON r.user_id = u.user_id
                                JOIN items i ON r.item_id = i.item_id
                                ORDER BY r.created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>


<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>لوحة تحكم المدير</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    :root {
    --primary: #5b6bf0;
    --primary-light: #7d8df5;
    --primary-dark: #4a57d6;
    --secondary: #8c68cd;
    --accent: #4fd1c5;
    --success: #48bb78;
    --warning: #ed8936;
    --danger: #e53e3e;
    --dark: #2d3748;
    --darker: #1a202c;
    --light: #f8fafc;
    --gray: #a0aec0;
    --gray-light: #e2e8f0;
    --gray-dark: #718096;
    --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    --transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    
    * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    }
    
    body {
    background: linear-gradient(135deg, #f5f7ff 0%, #f0f4ff 100%);
    font-family: 'Tajawal', sans-serif;
    color: var(--dark);
    min-height: 100vh;
    line-height: 1.6;
    }
    
    .admin-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 1.5rem;
    }
    
    .dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--gray-light);
    }
    
    .header-left h1 {
    font-size: 2.2rem;
    font-weight: 800;
    background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    margin-bottom: 0.5rem;
    }
    
    .header-left p {
    font-size: 1.1rem;
    color: var(--gray-dark);
    max-width: 600px;
    }
    
    .header-right {
    display: flex;
    align-items: center;
    gap: 1rem;
    }
    
    .user-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background: white;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    box-shadow: var(--card-shadow);
    }
    
    .user-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 1.2rem;
    }
    
    .user-text h3 {
    font-size: 1rem;
    font-weight: 600;
    color: var(--dark);
    }
    
    .user-text p {
    font-size: 0.85rem;
    color: var(--gray-dark);
    }
    
    .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2.5rem;
    }
    
    .stat-card {
    background: white;
    border-radius: 16px;
    padding: 1.75rem;
    box-shadow: var(--card-shadow);
    transition: var(--transition);
    position: relative;
    overflow: hidden;
    }
    
    .stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    }
    
    .stat-card::before {
    content: "";
    position: absolute;
    top: 0;
    right: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, var(--primary) 0%, var(--accent) 100%);
    }
    
    .stat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    }
    
    .stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    }
    
    .users-card .stat-icon {
    background: rgba(91, 107, 240, 0.12);
    color: var(--primary);
    }
    
    .requests-card .stat-icon {
    background: rgba(237, 137, 54, 0.12);
    color: var(--warning);
    }
    
    .items-card .stat-icon {
    background: rgba(79, 209, 197, 0.12);
    color: var(--accent);
    }
    
    .stat-title {
    font-size: 1.1rem;
    color: var(--gray-dark);
    font-weight: 500;
    }
    
    .stat-value {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--darker);
    margin-bottom: 0.25rem;
    }
    
    .stat-trend {
    display: flex;
    align-items: center;
    font-size: 0.9rem;
    color: var(--success);
    font-weight: 500;
    }
    
    .stat-trend.down {
    color: var(--danger);
    }
    
    .stat-link {
    display: inline-flex;
    align-items: center;
    margin-top: 1.5rem;
    padding: 0.6rem 1.25rem;
    background: var(--light);
    color: var(--primary);
    border-radius: 10px;
    font-weight: 600;
    text-decoration: none;
    transition: var(--transition);
    gap: 0.5rem;
    }
    
    .stat-link:hover {
    background: var(--primary);
    color: white;
    }
    
    .dashboard-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 2.5rem;
    }
    
    @media (max-width: 1100px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
    }
    
    .panel {
    background: white;
    border-radius: 16px;
    padding: 1.75rem;
    box-shadow: var(--card-shadow);
    }
    
    .panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--gray-light);
    }
    
    .panel-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--darker);
    }
    
    .panel-link {
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    transition: var(--transition);
    }
    
    .panel-link:hover {
    color: var(--primary-dark);
    }
    
    .recent-list {
    list-style: none;
    }
    
    .recent-item {
    display: flex;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid var(--gray-light);
    }
    
    .recent-item:last-child {
    border-bottom: none;
    }
    
    .recent-icon {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-left: 1rem;
    flex-shrink: 0;
    }
    
    .users-list .recent-icon {
    background: rgba(91, 107, 240, 0.1);
    color: var(--primary);
    }
    
    .requests-list .recent-icon {
    background: rgba(237, 137, 54, 0.1);
    color: var(--warning);
    }
    
    .recent-content {
    flex: 1;
    }
    
    .recent-title {
    font-weight: 600;
    color: var(--darker);
    margin-bottom: 0.25rem;
    }
    
    .recent-subtitle {
    font-size: 0.9rem;
    color: var(--gray-dark);
    }
    
    .recent-time {
    font-size: 0.85rem;
    color: var(--gray);
    font-weight: 500;
    }
    
    .status-badge {
    padding: 0.3rem 0.8rem;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
    text-align: center;
    display: inline-block;
    }
    
    .status-pending {
    background: rgba(237, 137, 54, 0.1);
    color: var(--warning);
    }
    
    .status-approved {
    background: rgba(72, 187, 120, 0.1);
    color: var(--success);
    }
    
    .status-rejected {
    background: rgba(229, 62, 62, 0.1);
    color: var(--danger);
    }
    
    .summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    }
    
    .summary-card {
    background: white;
    border-radius: 16px;
    padding: 1.75rem;
    box-shadow: var(--card-shadow);
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    transition: var(--transition);
    }
    
    .summary-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    }
    
    .summary-icon {
    width: 80px;
    height: 80px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    margin-bottom: 1.5rem;
    }
    
    .summary-users .summary-icon {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: white;
    }
    
    .summary-requests .summary-icon {
    background: linear-gradient(135deg, var(--warning) 0%, #f6ad55 100%);
    color: white;
    }
    
    .summary-items .summary-icon {
    background: linear-gradient(135deg, var(--accent) 0%, #81e6d9 100%);
    color: white;
    }
    
    .summary-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--darker);
    margin-bottom: 0.5rem;
    }
    
    .summary-text {
    color: var(--gray-dark);
    margin-bottom: 1.5rem;
    max-width: 300px;
    }
    
    .summary-link {
    display: inline-flex;
    align-items: center;
    padding: 0.7rem 1.5rem;
    background: var(--light);
    color: var(--primary);
    border-radius: 50px;
    font-weight: 600;
    text-decoration: none;
    transition: var(--transition);
    gap: 0.5rem;
    }
    
    .summary-link:hover {
    background: var(--primary);
    color: white;
    }
    
    @media (max-width: 768px) {
    .dashboard-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1.5rem;
    }
    
    .header-right {
        width: 100%;
        justify-content: space-between;
    }
    
    .summary-cards {
        grid-template-columns: 1fr;
    }
    }
</style>
</head>
<body>

<div class="admin-container">
<!-- Dashboard Header -->
<div class="dashboard-header">
    <div class="header-left">
    <h1>لوحة تحكم المدير</h1>
    <p>مرحبًا بك في مركز التحكم الخاص بالنظام، يمكنك إدارة كافة الجوانب من هنا</p>
    </div>
    <div class="header-right">
    <div class="user-info">
        <div class="user-avatar">
        <?php 
            $name = $_SESSION['username'] ?? 'المدير';
            $initials = mb_substr($name, 0, 1, 'UTF-8');
            echo $initials;
        ?>
        </div>
        <div class="user-text">
        <h3><?php echo $name; ?></h3>
        <p>مدير النظام</p>
        </div>
    </div>
    <div class="notification">
        <button style="background: white; border: none; width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: var(--card-shadow);">
        <i class="fas fa-bell" style="color: var(--primary); font-size: 1.2rem;"></i>
        </button>
    </div>
    </div>
</div>

<!-- Stats Overview -->
<div class="stats-grid">
    <div class="stat-card users-card">
    <div class="stat-header">
        <div>
        <div class="stat-title">إجمالي المستخدمين</div>
        <div class="stat-value"><?= $users_count ?></div>
        <div class="stat-trend">
            <i class="fas fa-arrow-up"></i> 12% زيادة هذا الشهر
        </div>
        </div>
        <div class="stat-icon">
        <i class="fas fa-users"></i>
        </div>
    </div>
    <a href="all_users.php" class="stat-link">
        عرض المستخدمين
        <i class="fas fa-arrow-left"></i>
    </a>
    </div>
    
    <div class="stat-card requests-card">
    <div class="stat-header">
        <div>
        <div class="stat-title">إجمالي الطلبات</div>
        <div class="stat-value"><?= $requests_count ?></div>
        <div class="stat-trend down">
            <i class="fas fa-arrow-down"></i> 3% انخفاض هذا الشهر
        </div>
        </div>
        <div class="stat-icon">
        <i class="fas fa-clipboard-list"></i>
        </div>
    </div>
    <a href="all_requests.php" class="stat-link">
        عرض الطلبات
        <i class="fas fa-arrow-left"></i>
    </a>
    </div>
    
    <div class="stat-card items-card">
    <div class="stat-header">
        <div>
        <div class="stat-title">إجمالي الأدوات</div>
        <div class="stat-value"><?= $items_count ?></div>
        <div class="stat-trend">
            <i class="fas fa-arrow-up"></i> 8% زيادة هذا الشهر
        </div>
        </div>
        <div class="stat-icon">
        <i class="fas fa-tools"></i>
        </div>
    </div>
    <a href="all_items.php" class="stat-link">
        عرض الأدوات
        <i class="fas fa-arrow-left"></i>
    </a>
    </div>
</div>

<!-- Recent Activity -->
<div class="dashboard-grid">
    <div class="panel">
    <div class="panel-header">
        <h3 class="panel-title">المستخدمون الجدد</h3>
        <a href="all_users.php" class="panel-link">
        عرض الكل
        <i class="fas fa-arrow-left"></i>
        </a>
    </div>
    <ul class="recent-list users-list">
        <?php foreach ($recent_users as $user): ?>
        <li class="recent-item">
            <div class="recent-icon">
            <i class="fas fa-user"></i>
            </div>
            <div class="recent-content">
            <div class="recent-title"><?= $user['first_name'] ?></div>
            <div class="recent-subtitle"><?= $user['email'] ?></div>
            </div>
            <div class="recent-time">
            <?= date('Y-m-d', strtotime($user['created_at'])) ?>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>
    </div>
    
    <div class="panel">
    <div class="panel-header">
        <h3 class="panel-title">الطلبات الحديثة</h3>
        <a href="all_requests.php" class="panel-link">
        عرض الكل
        <i class="fas fa-arrow-left"></i>
        </a>
    </div>
    <ul class="recent-list requests-list">
        <?php foreach ($recent_requests as $request): 
        $status_class = 'status-pending';
        if ($request['status'] == 'approved') $status_class = 'status-approved';
        if ($request['status'] == 'rejected') $status_class = 'status-rejected';
        ?>
        <li class="recent-item">
            <div class="recent-icon">
            <i class="fas fa-file-alt"></i>
            </div>
            <div class="recent-content">
            <div class="recent-title">طلب #<?= $request['item_id'] ?> - <?= $request['item_name'] ?></div>
            <div class="recent-subtitle">بواسطة: <?= $request['item_name'] ?></div>
            </div>
            <div class="status-badge <?= $status_class ?>">
            <?php 
                if ($request['status'] == 'pending') echo 'قيد الانتظار';
                elseif ($request['status'] == 'approved') echo 'موافق عليه';
                else echo 'مرفوض';
            ?>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>
    </div>
</div>

<!-- Summary Cards -->
<div class="summary-cards">
    <div class="summary-card summary-users">
    <div class="summary-icon">
        <i class="fas fa-users"></i>
    </div>
    <h3 class="summary-title">إدارة المستخدمين</h3>
    <p class="summary-text">عرض جميع المستخدمين، إضافة مستخدمين جدد، تعديل الصلاحيات وإدارة الحسابات</p>
    <a href="all_users.php" class="summary-link">
        الانتقال للإدارة
        <i class="fas fa-arrow-left"></i>
    </a>
    </div>
    
    <div class="summary-card summary-requests">
    <div class="summary-icon">
        <i class="fas fa-clipboard-check"></i>
    </div>
    <h3 class="summary-title">إدارة الطلبات</h3>
    <p class="summary-text">مراجعة الطلبات الجديدة، متابعة حالة الطلبات، الموافقة أو الرفض وتعديل التفاصيل</p>
    <a href="all_requests.php" class="summary-link">
        الانتقال للإدارة
        <i class="fas fa-arrow-left"></i>
    </a>
    </div>
    
    <div class="summary-card summary-items">
    <div class="summary-icon">
        <i class="fas fa-toolbox"></i>
    </div>
    <h3 class="summary-title">إدارة الأدوات</h3>
    <p class="summary-text">إضافة أدوات جديدة، تعديل الأدوات الحالية، إدارة التصنيفات وتحديث المخزون</p>
    <a href="all_items.php" class="summary-link">
        الانتقال للإدارة
        <i class="fas fa-arrow-left"></i>
    </a>
    </div>
</div>
</div>

</body>
</html>