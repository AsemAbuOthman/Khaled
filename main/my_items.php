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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>أدواتي | لوحة التحكم</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #4361ee;
      --primary-dark: #3a56d4;
      --secondary: #7209b7;
      --success: #06d6a0;
      --danger: #ef476f;
      --light: #f8f9fa;
      --dark: #212529;
      --gray: #6c757d;
      --light-gray: #e9ecef;
      --border-radius: 12px;
      --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      --transition: all 0.3s ease;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Tajawal', sans-serif;
      background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
      color: var(--dark);
      min-height: 100vh;
      padding-bottom: 40px;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
    }

    /* Header Styles */
    .dashboard-header {
      background: linear-gradient(to right, var(--primary), var(--secondary));
      color: white;
      padding: 25px 0;
      border-radius: 0 0 var(--border-radius) var(--border-radius);
      box-shadow: var(--shadow);
      margin-bottom: 40px;
    }

    .header-content {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
    }

    .page-title {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .page-title i {
      font-size: 1.8rem;
      background: rgba(255, 255, 255, 0.2);
      width: 60px;
      height: 60px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .page-title h1 {
      font-size: 1.8rem;
      font-weight: 700;
    }

    .page-title p {
      opacity: 0.9;
      margin-top: 5px;
      font-size: 1rem;
    }

    .stats-card {
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(10px);
      border-radius: var(--border-radius);
      padding: 18px 25px;
      text-align: center;
      min-width: 220px;
    }

    .stats-card .number {
      font-size: 2.2rem;
      font-weight: 700;
      margin-bottom: 5px;
    }

    .stats-card .label {
      font-size: 0.95rem;
      opacity: 0.85;
    }

    /* Action Bar */
    .action-bar {
      background: white;
      border-radius: var(--border-radius);
      padding: 20px;
      margin-bottom: 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 15px;
      box-shadow: var(--shadow);
    }

    .search-box {
      display: flex;
      align-items: center;
      background: var(--light);
      border-radius: 50px;
      padding: 10px 20px;
      flex: 1;
      max-width: 400px;
    }

    .search-box input {
      background: transparent;
      border: none;
      padding: 8px 15px;
      width: 100%;
      font-family: 'Tajawal', sans-serif;
      font-size: 1rem;
    }

    .search-box input:focus {
      outline: none;
    }

    .btn-add {
      background: var(--primary);
      color: white;
      border: none;
      border-radius: 50px;
      padding: 12px 25px;
      font-family: 'Tajawal', sans-serif;
      font-weight: 700;
      font-size: 1rem;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 10px;
      transition: var(--transition);
      box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
    }

    .btn-add:hover {
      background: var(--primary-dark);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
    }

    /* Items Grid */
    .items-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 25px;
      margin-bottom: 40px;
    }

    .item-card {
      background: white;
      border-radius: var(--border-radius);
      overflow: hidden;
      box-shadow: var(--shadow);
      transition: var(--transition);
      position: relative;
      display: flex;
      flex-direction: column;
    }

    .item-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
    }

    .card-header {
      position: relative;
      height: 180px;
      overflow: hidden;
    }

    .card-header img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: var(--transition);
    }

    .item-card:hover .card-header img {
      transform: scale(1.05);
    }

    .status-badge {
      position: absolute;
      top: 15px;
      left: 15px;
      padding: 6px 15px;
      border-radius: 50px;
      font-size: 0.85rem;
      font-weight: 700;
      background: var(--success);
      color: white;
      box-shadow: 0 3px 10px rgba(6, 214, 160, 0.3);
    }

    .status-badge.unavailable {
      background: var(--danger);
      box-shadow: 0 3px 10px rgba(239, 71, 111, 0.3);
    }

    .card-body {
      padding: 20px;
      flex-grow: 1;
    }

    .item-name {
      color: var(--primary);
      margin-bottom: 10px;
      font-size: 1.3rem;
      font-weight: 700;
    }

    .item-desc {
      color: var(--gray);
      line-height: 1.6;
      margin-bottom: 15px;
      font-size: 0.95rem;
    }

    .meta-info {
      display: flex;
      justify-content: space-between;
      color: var(--gray);
      font-size: 0.9rem;
      margin-top: 15px;
      padding-top: 15px;
      border-top: 1px solid var(--light-gray);
    }

    .card-footer {
      padding: 0 20px 20px;
      display: flex;
      gap: 10px;
    }

    .card-btn {
      flex: 1;
      text-align: center;
      padding: 10px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
      font-size: 0.9rem;
      transition: var(--transition);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }

    .card-btn.view {
      background: var(--light);
      color: var(--primary);
    }

    .card-btn.view:hover {
      background: rgba(67, 97, 238, 0.1);
    }

    .card-btn.edit {
      background: rgba(255, 193, 7, 0.1);
      color: #ffc107;
    }

    .card-btn.edit:hover {
      background: rgba(255, 193, 7, 0.2);
    }

    .card-btn.delete {
      background: rgba(239, 71, 111, 0.1);
      color: var(--danger);
    }

    .card-btn.delete:hover {
      background: rgba(239, 71, 111, 0.2);
    }

    /* Empty State */
    .empty-state {
      text-align: center;
      padding: 60px 20px;
      background: white;
      border-radius: var(--border-radius);
      box-shadow: var(--shadow);
      max-width: 600px;
      margin: 40px auto;
    }

    .empty-state i {
      font-size: 5rem;
      color: var(--light-gray);
      margin-bottom: 20px;
    }

    .empty-state h3 {
      font-size: 1.8rem;
      color: var(--gray);
      margin-bottom: 15px;
    }

    .empty-state p {
      color: var(--gray);
      margin-bottom: 25px;
      font-size: 1.1rem;
      line-height: 1.6;
    }

    /* Pagination */
    .pagination {
      display: flex;
      justify-content: center;
      margin: 30px 0;
      gap: 8px;
    }

    .pagination a {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      border-radius: 10px;
      text-decoration: none;
      font-weight: 600;
      background: white;
      color: var(--gray);
      box-shadow: var(--shadow);
      transition: var(--transition);
    }

    .pagination a:hover, .pagination a.active {
      background: var(--primary);
      color: white;
      transform: translateY(-3px);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .header-content {
        flex-direction: column;
        align-items: flex-start;
        gap: 25px;
      }
      
      .stats-card {
        min-width: auto;
        width: 100%;
      }
      
      .action-bar {
        flex-direction: column;
        align-items: stretch;
      }
      
      .search-box {
        max-width: 100%;
      }
      
      .items-grid {
        grid-template-columns: repeat(auto-fill, minmax(100%, 1fr));
      }
    }
  </style>
</head>
<body>
  <div class="dashboard-header">
    <div class="container">
      <div class="header-content">
        <div class="page-title">
          <i class="fas fa-toolbox"></i>
          <div>
            <h1>أدواتي</h1>
            <p>إدارة الأدوات الطبية المعارة</p>
          </div>
        </div>
        <div class="stats-card">
          <div class="number"><?= $totalItems ?></div>
          <div class="label">أداة مضافة</div>
        </div>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="action-bar">
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="ابحث عن أداة...">
      </div>
      <button class="btn-add">
        <i class="fas fa-plus"></i> أضف أداة جديدة
      </button>
    </div>

    <?php if ($result->num_rows > 0): ?>
      <div class="items-grid">
        <?php while ($item = $result->fetch_assoc()): 
          $statusClass = $item['reserve_status'] === 'available' ? '' : 'unavailable';
          $statusText = $item['reserve_status'] === 'available' ? 'متاح' : 'غير متاح';
        ?>
          <div class="item-card">
            <div class="card-header">
              <?php if (!empty($item['image']) && file_exists($item['image'])): ?>
                <img src="<?= htmlspecialchars($item['image']) ?>" alt="صورة الأداة">
              <?php else: ?>
                <div style="background: #e0e7ff; height: 100%; display: flex; align-items: center; justify-content: center;">
                  <i class="fas fa-tools" style="font-size: 3rem; color: #a5b4fc;"></i>
                </div>
              <?php endif; ?>
              <div class="status-badge <?= $statusClass ?>"><?= $statusText ?></div>
            </div>
            
            <div class="card-body">
              <h3 class="item-name"><?= htmlspecialchars($item['name']) ?></h3>
              <p class="item-desc"><?= htmlspecialchars(mb_substr($item['description'], 0, 80)) . (mb_strlen($item['description']) > 80 ? '...' : '') ?></p>
              
              <div class="meta-info">
                <span><i class="far fa-calendar-alt"></i> <?= date('Y/m/d', strtotime($item['created_at'])) ?></span>
                <span><i class="far fa-eye"></i> 24 مشاهدات</span>
              </div>
            </div>
            
            <div class="card-footer">
              <a href="/Medical-System/main/view_item.php?item_id=<?= $item['item_id'] ?>" class="card-btn view">
                <i class="fas fa-eye"></i> عرض
              </a>
              <a href="/Medical-System/main/edit_item.php?item_id=<?= $item['item_id'] ?>" class="card-btn edit">
                <i class="fas fa-edit"></i> تعديل
              </a>
              <a href="delete_item.php?item_id=<?= $item['item_id'] ?>" class="card-btn delete" onclick="return confirm('هل أنت متأكد من حذف الأداة؟');">
                <i class="fas fa-trash-alt"></i> حذف
              </a>
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
      <div class="empty-state">
        <i class="fas fa-toolbox"></i>
        <h3>لا توجد أدوات مضافة بعد</h3>
        <p>لم تقم بإضافة أي أدوات طبية حتى الآن. يمكنك البدء بإضافة أول أداة بالنقر على الزر "أضف أداة جديدة" في الأعلى.</p>
        <button class="btn-add">
          <i class="fas fa-plus"></i> أضف أداة جديدة
        </button>
      </div>
    <?php endif; ?>
  </div>

  <script>
    // Add active class to pagination links
    document.addEventListener('DOMContentLoaded', function() {
      // Add animation to cards on load
      const cards = document.querySelectorAll('.item-card');
      cards.forEach((card, index) => {
        setTimeout(() => {
          card.style.opacity = 1;
          card.style.transform = 'translateY(0)';
        }, index * 100);
      });
      
      // Add event listener to add button
      document.querySelectorAll('.btn-add').forEach(button => {
        button.addEventListener('click', function() {
          alert('سيتم توجيهك إلى صفحة إضافة أداة جديدة');
          // In real implementation: window.location.href = 'add_item.php';
        });
      });
    });
  </script>
</body>
</html>

<?php $conn->close(); ?>