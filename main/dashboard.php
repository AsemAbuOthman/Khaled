<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

include __DIR__ . '/../database/db.php'; 

// Fetch statistics
$users_count = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
$requests_count = $conn->query("SELECT COUNT(*) FROM reservations")->fetch_row()[0];
$items_count = $conn->query("SELECT COUNT(*) FROM items")->fetch_row()[0];

// Additional statistics
$active_users = $conn->query("SELECT COUNT(DISTINCT user_id) FROM reservations")->fetch_row()[0];
$pending_requests = $conn->query("SELECT COUNT(*) FROM reservations WHERE status = 'pending'")->fetch_row()[0];
$accepted_requests = $conn->query("SELECT COUNT(*) FROM reservations WHERE status = 'accepted'")->fetch_row()[0];


$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>لوحة تحكم المدير</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #4361ee;
      --primary-dark: #3a56d4;
      --secondary: #7209b7;
      --success: #06d6a0;
      --warning: #ffd166;
      --danger: #ef476f;
      --dark: #1e293b;
      --light: #f8fafc;
      --gray: #64748b;
      --light-gray: #e2e8f0;
      --white: #ffffff;
      --sidebar: #1e293b;
      --sidebar-hover: #334155;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Tajawal', sans-serif;
    }
    
    body {
      background-color: #f1f5f9;
      color: var(--dark);
      display: flex;
      min-height: 100vh;
    }
    
    /* Sidebar */
    .sidebar {
      width: 260px;
      background: var(--sidebar);
      color: var(--white);
      padding: 20px 0;
      transition: all 0.3s ease;
      position: fixed;
      height: 100vh;
      overflow-y: auto;
      z-index: 100;
    }
    
    .sidebar-header {
      padding: 0 20px 20px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .sidebar-header h2 {
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: 1.4rem;
    }
    
    .sidebar-header i {
      color: var(--success);
    }
    
    .nav-links {
      margin-top: 20px;
    }
    
    .nav-item {
      padding: 12px 20px;
      display: flex;
      align-items: center;
      gap: 12px;
      color: rgba(255, 255, 255, 0.8);
      text-decoration: none;
      transition: all 0.3s;
      font-size: 1.05rem;
    }
    
    .nav-item:hover, .nav-item.active {
      background: var(--sidebar-hover);
      color: var(--white);
      border-right: 4px solid var(--primary);
    }
    
    .nav-item i {
      width: 24px;
      text-align: center;
    }
    
    /* Main Content */
    .main-content {
      flex: 1;
      margin-right: 260px;
      padding: 20px;
    }
    
    /* Topbar */
    .topbar {
      background: var(--white);
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 30px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .topbar-left h1 {
      font-size: 1.8rem;
      color: var(--dark);
      margin-bottom: 5px;
    }
    
    .topbar-left p {
      color: var(--gray);
      font-size: 1rem;
    }
    
    .topbar-right {
      display: flex;
      gap: 15px;
      align-items: center;
    }
    
    .admin-profile {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    
    .admin-avatar {
      width: 45px;
      height: 45px;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--white);
      font-weight: bold;
      font-size: 1.2rem;
    }
    
    .admin-info h4 {
      font-size: 1.1rem;
      color: var(--dark);
    }
    
    .admin-info p {
      font-size: 0.9rem;
      color: var(--gray);
    }
    
    .logout-btn {
      background: var(--danger);
      color: var(--white);
      border: none;
      padding: 10px 20px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: all 0.3s;
    }
    
    .logout-btn:hover {
      background: #d93a5e;
      transform: translateY(-2px);
    }
    
    /* Dashboard Cards */
    .dashboard-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 25px;
      margin-bottom: 40px;
    }
    
    .dashboard-card {
      background: var(--white);
      border-radius: 16px;
      padding: 25px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
      position: relative;
      overflow: hidden;
      transition: all 0.3s ease;
    }
    
    .dashboard-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }
    
    .card-icon {
      width: 60px;
      height: 60px;
      border-radius: 15px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.8rem;
      color: var(--white);
    }
    
    .card-users .card-icon { background: linear-gradient(135deg, var(--primary), #4895ef); }
    .card-requests .card-icon { background: linear-gradient(135deg, var(--secondary), #9d4edd); }
    .card-items .card-icon { background: linear-gradient(135deg, #06d6a0, #0db39e); }
    .card-active .card-icon { background: linear-gradient(135deg, #ffd166, #ffb703); }
    
    .card-title {
      font-size: 1.1rem;
      color: var(--gray);
      margin-bottom: 5px;
    }
    
    .card-value {
      font-size: 2.2rem;
      font-weight: 700;
      color: var(--dark);
      margin-bottom: 15px;
    }
    
    .card-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-top: 1px solid var(--light-gray);
      padding-top: 15px;
    }
    
    .card-link {
      color: var(--primary);
      text-decoration: none;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: all 0.3s;
    }
    
    .card-link:hover {
      color: var(--primary-dark);
      gap: 12px;
    }
    
    /* Charts Section */
    .charts-section {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
      gap: 25px;
      margin-bottom: 40px;
    }
    
    .chart-container {
      background: var(--white);
      border-radius: 16px;
      padding: 25px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    
    .chart-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
    }
    
    .chart-header h2 {
      font-size: 1.4rem;
      color: var(--dark);
    }
    
    .chart-content {
      height: 300px;
      display: flex;
      align-items: flex-end;
      gap: 20px;
      padding: 20px 0;
    }
    
    .chart-bar {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      height: 100%;
    }
    
    .bar-value {
      background: linear-gradient(to top, var(--primary), #4895ef);
      width: 50px;
      border-radius: 10px 10px 0 0;
      margin-bottom: 10px;
      transition: height 0.8s ease;
    }
    
    .bar-label {
      font-weight: 600;
      color: var(--gray);
    }
    
    /* Recent Activity */
    .activity-container {
      background: var(--white);
      border-radius: 16px;
      padding: 25px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    
    .activity-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
    }
    
    .activity-header h2 {
      font-size: 1.4rem;
      color: var(--dark);
    }
    
    .activity-list {
      list-style: none;
    }
    
    .activity-item {
      display: flex;
      padding: 15px 0;
      border-bottom: 1px solid var(--light-gray);
    }
    
    .activity-item:last-child {
      border-bottom: none;
    }
    
    .activity-icon {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: rgba(67, 97, 238, 0.1);
      display: flex;
      align-items: center;
      justify-content: center;
      margin-left: 15px;
      color: var(--primary);
      font-size: 1.1rem;
    }
    
    .activity-content {
      flex: 1;
    }
    
    .activity-content h4 {
      font-size: 1.1rem;
      color: var(--dark);
      margin-bottom: 5px;
    }
    
    .activity-content p {
      color: var(--gray);
      font-size: 0.95rem;
    }
    
    .activity-time {
      color: var(--gray);
      font-size: 0.9rem;
    }
    
    /* Responsive Styles */
    @media (max-width: 1200px) {
      .charts-section {
        grid-template-columns: 1fr;
      }
    }
    
    @media (max-width: 992px) {
      .sidebar {
        width: 70px;
        overflow: visible;
      }
      
      .sidebar-header h2 span, .nav-item span {
        display: none;
      }
      
      .main-content {
        margin-right: 70px;
      }
    }
    
    @media (max-width: 768px) {
      .topbar {
        flex-direction: column;
        gap: 20px;
        text-align: center;
      }
      
      .admin-profile {
        justify-content: center;
      }
      
      .chart-content {
        flex-direction: column;
        height: auto;
        align-items: stretch;
      }
      
      .chart-bar {
        flex-direction: row;
        align-items: center;
        gap: 15px;
      }
      
      .bar-value {
        width: 100%;
        height: 30px;
        border-radius: 10px;
      }
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <!-- <div class="sidebar">
    <div class="sidebar-header">
      <h2><i class="fas fa-cogs"></i> <span>لوحة التحكم</span></h2>
    </div>
    <div class="nav-links">
      <a href="#" class="nav-item active">
        <i class="fas fa-tachometer-alt"></i>
        <span>الرئيسية</span>
      </a>
      <a href="all_users.php" class="nav-item">
        <i class="fas fa-users"></i>
        <span>المستخدمين</span>
      </a>
      <a href="all_requests.php" class="nav-item">
        <i class="fas fa-clipboard-list"></i>
        <span>الطلبات</span>
      </a>
      <a href="all_items.php" class="nav-item">
        <i class="fas fa-tools"></i>
        <span>الأدوات</span>
      </a>
      <a href="#" class="nav-item">
        <i class="fas fa-chart-bar"></i>
        <span>الإحصائيات</span>
      </a>
      <a href="#" class="nav-item">
        <i class="fas fa-cog"></i>
        <span>الإعدادات</span>
      </a>
    </div>
  </div> -->
  
  <?php 
    include "./partials/sidebar.php";
  ?>



  <!-- Main Content -->
  <div class="main-content">
    <!-- Topbar -->
    <div class="topbar">
      <div class="topbar-left">
        <h1>لوحة تحكم المدير</h1>
        <p>مرحبًا بك في نظام إدارة منصة الإعارة الطبية</p>
      </div>
      <div class="topbar-right">
        <div class="admin-profile">
          <div class="admin-avatar">م</div>
          <div class="admin-info">
            <h4>المدير العام</h4>
            <p>مدير النظام</p>
          </div>
        </div>
        <button class="logout-btn">
          <i class="fas fa-sign-out-alt"></i>
          تسجيل الخروج
        </button>
      </div>
    </div>

    <!-- Dashboard Cards -->
    <div class="dashboard-cards">
      <div class="dashboard-card card-users">
        <div class="card-header">
          <div>
            <div class="card-title">المستخدمين</div>
            <div class="card-value"><?= $users_count ?></div>
          </div>
          <div class="card-icon">
            <i class="fas fa-users"></i>
          </div>
        </div>
        <div class="card-footer">
          <span>إجمالي المستخدمين المسجلين</span>
          <a href="all_users.php" class="card-link">
            عرض المستخدمين
            <i class="fas fa-arrow-left"></i>
          </a>
        </div>
      </div>
      
      <div class="dashboard-card card-requests">
        <div class="card-header">
          <div>
            <div class="card-title">الطلبات</div>
            <div class="card-value"><?= $requests_count ?></div>
          </div>
          <div class="card-icon">
            <i class="fas fa-clipboard-list"></i>
          </div>
        </div>
        <div class="card-footer">
          <div>
            <span style="color: var(--secondary); font-weight: 600;"><?= $accepted_requests ?> مقبولة</span> | 
            <span style="color: var(--warning); font-weight: 600;"><?= $pending_requests ?> قيد الانتظار</span>
          </div>
          <a href="all_requests.php" class="card-link">
            عرض الطلبات
            <i class="fas fa-arrow-left"></i>
          </a>
        </div>
      </div>
      
      <div class="dashboard-card card-items">
        <div class="card-header">
          <div>
            <div class="card-title">الأدوات</div>
            <div class="card-value"><?= $items_count ?></div>
          </div>
          <div class="card-icon">
            <i class="fas fa-tools"></i>
          </div>
        </div>
        <div class="card-footer">
          <span>الأدوات المتاحة في النظام</span>
          <a href="all_items.php" class="card-link">
            عرض الأدوات
            <i class="fas fa-arrow-left"></i>
          </a>
        </div>
      </div>
      
      <div class="dashboard-card card-active">
        <div class="card-header">
          <div>
            <div class="card-title">المستخدمين النشطين</div>
            <div class="card-value"><?= $active_users ?></div>
          </div>
          <div class="card-icon">
            <i class="fas fa-user-check"></i>
          </div>
        </div>
        <div class="card-footer">
          <span>المستخدمين الذين قاموا بحجوزات</span>
          <a href="all_users.php" class="card-link">
            عرض النشطين
            <i class="fas fa-arrow-left"></i>
          </a>
        </div>
      </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-section">
      <div class="chart-container">
        <div class="chart-header">
          <h2>إحصائيات الطلبات</h2>
        </div>
        <div class="chart-content" id="requests-chart">
          <!-- Chart will be generated by JavaScript -->
        </div>
      </div>
      
      <div class="chart-container">
        <div class="chart-header">
          <h2>توزيع المستخدمين</h2>
        </div>
        <div class="chart-content" id="users-chart">
          <!-- Chart will be generated by JavaScript -->
        </div>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="activity-container">
      <div class="activity-header">
        <h2>النشاط الأخير</h2>
        <a href="#" class="card-link">
          عرض الكل
          <i class="fas fa-arrow-left"></i>
        </a>
      </div>
     
    </div>
  </div>

  <script>
    // Requests Chart
    const requestsData = [
      { label: 'طلبات جديدة', value: <?= $pending_requests ?> },
      { label: 'طلبات مقبولة', value: <?= $accepted_requests ?> },
      { label: 'طلبات مكتملة', value: <?= $requests_count - $pending_requests - $accepted_requests ?> }
    ];
    
    const maxRequestValue = Math.max(...requestsData.map(item => item.value), 10) || 10;
    
    const requestsChart = document.getElementById('requests-chart');
    requestsChart.innerHTML = '';
    
    requestsData.forEach(item => {
      const barHeight = (item.value / maxRequestValue) * 100;
      
      const barElement = document.createElement('div');
      barElement.className = 'chart-bar';
      barElement.innerHTML = `
        <div class="bar-value" style="height: ${barHeight}%"></div>
        <div class="bar-label">${item.label}</div>
        <div class="bar-number">${item.value}</div>
      `;
      
      requestsChart.appendChild(barElement);
    });
    
    // Users Chart
    const usersData = [
      { label: 'المستفيدين', value: <?= $users_count - $active_users ?> },
      { label: 'المستخدمين النشطين', value: <?= $active_users ?> },
      { label: 'المعيرين', value: <?= $items_count ?> }
    ];
    
    const maxUserValue = Math.max(...usersData.map(item => item.value), 10) || 10;
    
    const usersChart = document.getElementById('users-chart');
    usersChart.innerHTML = '';
    
    usersData.forEach(item => {
      const barHeight = (item.value / maxUserValue) * 100;
      
      const barElement = document.createElement('div');
      barElement.className = 'chart-bar';
      barElement.innerHTML = `
        <div class="bar-value" style="height: ${barHeight}%; background: linear-gradient(to top, #7209b7, #9d4edd);"></div>
        <div class="bar-label">${item.label}</div>
        <div class="bar-number">${item.value}</div>
      `;
      
      usersChart.appendChild(barElement);
    });
    
    // Animate chart bars
    setTimeout(() => {
      const bars = document.querySelectorAll('.bar-value');
      bars.forEach(bar => {
        const height = bar.style.height;
        bar.style.height = '0';
        setTimeout(() => {
          bar.style.height = height;
        }, 300);
      });
    }, 500);
    
    // Logout functionality
    document.querySelector('.logout-btn').addEventListener('click', function() {
      window.location.href = '../auth/logout.php';
    });
  </script>
</body>
</html>