<?php
session_start();

include "../main/partials/header-1.php";

include __DIR__ . '/../database/db.php'; 

// Redirect if user is not logged in or is not a beneficiary
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: login_page.php");
    exit;
}


$user_id = $_SESSION['user_id'];

// Get user's first name for personalization
$user_stmt = $conn->prepare("SELECT first_name FROM users WHERE user_id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();
$first_name = $user_data['first_name'] ?? 'المستفيد';

// Get counts
$requests_count = $conn->prepare("SELECT COUNT(*) FROM reservations WHERE user_id = ?");
$requests_count->bind_param("i", $user_id);
$requests_count->execute();
$requests_count_result = $requests_count->get_result()->fetch_row()[0];

$accepted_requests = $conn->prepare("SELECT COUNT(*) FROM reservations WHERE user_id = ? AND status = 'accepted'");
$accepted_requests->bind_param("i", $user_id);
$accepted_requests->execute();
$accepted_requests_result = $accepted_requests->get_result()->fetch_row()[0];

$available_items = $conn->prepare("SELECT COUNT(*) FROM items WHERE reserve_status = 1");
$available_items->execute();
$available_items_result = $available_items->get_result()->fetch_row()[0];

$pending_requests = $requests_count_result - $accepted_requests_result;

$conn->close();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>لوحة تحكم المستفيد</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #6a11cb;
      --primary-light: #8a4cff;
      --secondary: #2575fc;
      --success: #10b981;
      --warning: #f59e0b;
      --info: #3b82f6;
      --dark: #1e293b;
      --light: #f8fafc;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Tajawal', sans-serif;
    }
    
    body {
      background: linear-gradient(135deg, #f0f8ff 0%, #e6f7ff 100%);
      min-height: 100vh;
      padding: 0;
      margin: 0;
      color: var(--dark);
    }
    
    .dashboard-container {
      max-width: 1400px;
      margin: 0 auto;
      padding: 20px;
    }
    
    .dashboard-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      padding: 30px 20px;
      background: linear-gradient(to right, var(--primary), var(--secondary));
      border-radius: 20px;
      color: white;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      margin-bottom: 40px;
      position: relative;
      overflow: hidden;
    }
    
    .dashboard-header::before {
      content: "";
      position: absolute;
      top: -50px;
      right: -50px;
      width: 200px;
      height: 200px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
    }
    
    .dashboard-header::after {
      content: "";
      position: absolute;
      bottom: -30px;
      left: -30px;
      width: 150px;
      height: 150px;
      background: rgba(255, 255, 255, 0.07);
      border-radius: 50%;
    }
    
    .welcome-section h1 {
      font-size: 2.4rem;
      margin-bottom: 10px;
      font-weight: 700;
    }
    
    .welcome-section p {
      font-size: 1.2rem;
      opacity: 0.9;
      max-width: 600px;
    }
    
    .date-section {
      text-align: left;
      background: rgba(255, 255, 255, 0.2);
      padding: 15px 25px;
      border-radius: 15px;
      backdrop-filter: blur(10px);
      z-index: 1;
    }
    
    .date-section .day {
      font-size: 1.8rem;
      font-weight: bold;
      margin-bottom: 5px;
    }
    
    .date-section .date {
      font-size: 1.1rem;
    }
    
    .dashboard-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 25px;
      margin-bottom: 40px;
    }
    
    .dashboard-card {
      background: white;
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
      border: 1px solid rgba(0, 0, 0, 0.03);
    }
    
    .dashboard-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    }
    
    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
    }
    
    .card-icon {
      width: 60px;
      height: 60px;
      border-radius: 15px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.8rem;
      color: white;
    }
    
    .card-blue .card-icon { background: linear-gradient(to right, var(--primary), var(--primary-light)); }
    .card-green .card-icon { background: linear-gradient(to right, var(--success), #34d399); }
    .card-orange .card-icon { background: linear-gradient(to right, var(--warning), #fbbf24); }
    .card-purple .card-icon { background: linear-gradient(to right, #8b5cf6, #a78bfa); }
    
    .card-title {
      font-size: 1.3rem;
      font-weight: 600;
      color: var(--dark);
    }
    
    .card-value {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 15px;
      color: var(--dark);
    }
    
    .card-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-top: 1px solid rgba(0, 0, 0, 0.05);
      padding-top: 20px;
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
      color: var(--primary-light);
      gap: 12px;
    }
    
    .chart-container {
      background: white;
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
      margin-bottom: 40px;
    }
    
    .chart-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
    }
    
    .chart-header h2 {
      font-size: 1.6rem;
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
      background: linear-gradient(to top, var(--primary), var(--secondary));
      width: 50px;
      border-radius: 10px 10px 0 0;
      margin-bottom: 10px;
      transition: height 0.8s ease;
    }
    
    .bar-label {
      font-weight: 600;
      color: var(--dark);
    }
    
    .quick-actions {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 40px;
    }
    
    .action-card {
      background: white;
      border-radius: 15px;
      padding: 25px;
      text-align: center;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      transition: all 0.3s;
      border: 1px solid rgba(0, 0, 0, 0.03);
    }
    
    .action-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
      background: linear-gradient(to right, var(--primary), var(--secondary));
      color: white;
    }
    
    .action-card:hover .action-icon,
    .action-card:hover .action-title,
    .action-card:hover .action-description {
      color: white;
    }
    
    .action-icon {
      font-size: 2.5rem;
      margin-bottom: 15px;
      color: var(--primary);
    }
    
    .action-title {
      font-size: 1.2rem;
      font-weight: 600;
      margin-bottom: 10px;
      color: var(--dark);
    }
    
    .action-description {
      font-size: 0.9rem;
      color: #64748b;
      margin-bottom: 15px;
    }
    
    .action-btn {
      display: inline-block;
      padding: 8px 20px;
      background: rgba(106, 17, 203, 0.1);
      color: var(--primary);
      border-radius: 30px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s;
    }
    
    .action-card:hover .action-btn {
      background: rgba(255, 255, 255, 0.2);
      color: white;
    }
    
    @media (max-width: 768px) {
      .dashboard-header {
        flex-direction: column;
        text-align: center;
        gap: 20px;
      }
      
      .date-section {
        text-align: center;
      }
      
      .welcome-section {
        text-align: center;
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
  <div class="dashboard-container">
    <div class="dashboard-header">
      <div class="welcome-section">
        <h1>مرحبًا <?= htmlspecialchars($first_name) ?>! 👋</h1>
        <p>هذه لوحة تحكمك حيث يمكنك متابعة طلباتك واستعراض الأدوات المتاحة للاستعارة</p>
      </div>
      <div class="date-section">
        <div class="day" id="current-day">الاثنين</div>
        <div class="date" id="current-date">٢ يونيو ٢٠٢٥</div>
      </div>
    </div>
    
    <div class="dashboard-cards">
      <div class="dashboard-card card-blue">
        <div class="card-header">
          <div class="card-title">طلباتك</div>
          <div class="card-icon">
            <i class="fas fa-file-alt"></i>
          </div>
        </div>
        <div class="card-value"><?= $requests_count_result ?></div>
        <div class="card-footer">
          <a href="/Medical-System/main/my_requests.php" class="card-link">
            عرض الطلبات
            <i class="fas fa-arrow-left"></i>
          </a>
        </div>
      </div>
      
      <div class="dashboard-card card-green">
        <div class="card-header">
          <div class="card-title">طلبات مقبولة</div>
          <div class="card-icon">
            <i class="fas fa-check-circle"></i>
          </div>
        </div>
        <div class="card-value"><?= $accepted_requests_result ?></div>
        <div class="card-footer">
          <span>تم الموافقة عليها</span>
        </div>
      </div>
      
      <div class="dashboard-card card-orange">
        <div class="card-header">
          <div class="card-title">أدوات متاحة</div>
          <div class="card-icon">
            <i class="fas fa-tools"></i>
          </div>
        </div>
        <div class="card-value"><?= $available_items_result ?></div>
        <div class="card-footer">
          <a href="/Medical-System/main/available_items.php" class="card-link">
            تصفح الأدوات
            <i class="fas fa-arrow-left"></i>
          </a>
        </div>
      </div>
      
      <div class="dashboard-card card-purple">
        <div class="card-header">
          <div class="card-title">طلبات قيد الانتظار</div>
          <div class="card-icon">
            <i class="fas fa-clock"></i>
          </div>
        </div>
        <div class="card-value"><?= $pending_requests ?></div>
        <div class="card-footer">
          <span>في انتظار الموافقة</span>
        </div>
      </div>
    </div>
    
    <div class="chart-container">
      <div class="chart-header">
        <h2>إحصائيات الطلبات</h2>
      </div>
      <div class="chart-content" id="requests-chart">
        <!-- Chart will be generated by JavaScript -->
      </div>
    </div>
    
    <div class="quick-actions">
      <div class="action-card">
        <div class="action-icon">
          <i class="fas fa-search"></i>
        </div>
        <div class="action-title">استعرض الأدوات</div>
        <div class="action-description">تصفح جميع الأدوات المتاحة للاستعارة</div>
        <a href="/Medical-System/main/available_items.php" class="action-btn">استعرض الآن</a>
      </div>
      
      <div class="action-card">
        <div class="action-icon">
          <i class="fas fa-plus-circle"></i>
        </div>
        <div class="action-title">تقديم طلب جديد</div>
        <div class="action-description">قدم طلبًا جديدًا لاستعارة أداة</div>
        <a href="/Medical-System/main/request_item.php" class="action-btn">قدم طلبًا</a>
      </div>
      
      <div class="action-card">
        <div class="action-icon">
          <i class="fas fa-history"></i>
        </div>
        <div class="action-title">سجل الطلبات</div>
        <div class="action-description">راجع جميع طلباتك السابقة والحالية</div>
        <a href="/Medical-System/main/my_requests.php" class="action-btn">عرض السجل</a>
      </div>
      
      <div class="action-card">
        <div class="action-icon">
          <i class="fas fa-user-cog"></i>
        </div>
        <div class="action-title">إعدادات الحساب</div>
        <div class="action-description">عدّل معلومات حسابك الشخصية</div>
        <a href="/Medical-System/main/profile.php" class="action-btn">الإعدادات</a>
      </div>
    </div>
  </div>

  <script>
    // Set current date in Arabic
    const days = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
    const months = ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
    
    const today = new Date();
    document.getElementById('current-day').textContent = days[today.getDay()];
    document.getElementById('current-date').textContent = `${today.getDate()} ${months[today.getMonth()]} ${today.getFullYear()}`;
    
    // Generate chart
    const chartData = [
      { label: 'طلباتك', value: <?= $requests_count_result ?> },
      { label: 'مقبولة', value: <?= $accepted_requests_result ?> },
      { label: 'قيد الانتظار', value: <?= $pending_requests ?> },
      { label: 'أدوات متاحة', value: <?= $available_items_result ?> }
    ];
    
    const maxValue = Math.max(...chartData.map(item => item.value), 10) || 10;
    
    const chartContainer = document.getElementById('requests-chart');
    chartContainer.innerHTML = '';
    
    chartData.forEach(item => {
      const barHeight = (item.value / maxValue) * 100;
      
      const barElement = document.createElement('div');
      barElement.className = 'chart-bar';
      barElement.innerHTML = `
        <div class="bar-value" style="height: ${barHeight}%"></div>
        <div class="bar-label">${item.label}</div>
        <div class="bar-number">${item.value}</div>
      `;
      
      chartContainer.appendChild(barElement);
    });
    
    // Animate chart bars after a delay
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
  </script>
</body>
</html>