<?php
session_start();
include("../main/partials/header-1.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit;
}

include __DIR__ . '/../database/db.php';

$search = trim($_GET['search'] ?? '');
$perPage = 8;
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$where = "reserve_status = 'available'";
$params = [];
$paramTypes = "";

// Add search condition
if ($search !== '') {
    $where .= " AND (name LIKE ? OR description LIKE ?)";
    $searchParam = "%$search%";
    $params[] = &$searchParam;
    $params[] = &$searchParam;
    $paramTypes .= "ss";
}

// Count query
$countQuery = "SELECT COUNT(*) as total FROM items WHERE $where";
$countStmt = $conn->prepare($countQuery);
if ($paramTypes) {
    $countStmt->bind_param($paramTypes, ...$params);
}
$countStmt->execute();
$totalItems = $countStmt->get_result()->fetch_assoc()['total'];
$countStmt->close();
$totalPages = ceil($totalItems / $perPage);

// Data query
$dataQuery = "SELECT * FROM items WHERE $where ORDER BY created_at DESC LIMIT ? OFFSET ?";
$dataStmt = $conn->prepare($dataQuery);
$paramTypesData = $paramTypes . "ii";
$params[] = &$perPage;
$params[] = &$offset;
$dataStmt->bind_param($paramTypesData, ...$params);
$dataStmt->execute();
$result = $dataStmt->get_result();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>الأدوات المتاحة - نظام إدارة الاستعارة</title>
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #4361ee;
      --primary-dark: #3a56d4;
      --secondary: #4cc9f0;
      --light: #f8f9fa;
      --dark: #212529;
      --gray: #6c757d;
      --light-gray: #e9ecef;
      --success: #4caf50;
      --card-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
      --transition: all 0.3s ease;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      background: linear-gradient(135deg, #f5f7fa 0%, #e4e7f1 100%);
      font-family: 'Tajawal', sans-serif;
      color: var(--dark);
      min-height: 100vh;
      padding-bottom: 40px;
    }
    
    .container {
      max-width: 1200px;
      margin: 40px auto;
      padding: 0 20px;
    }
    
    .header {
      text-align: center;
      margin-bottom: 40px;
      position: relative;
    }
    
    .header h1 {
      color: var(--primary);
      font-size: 2.5rem;
      margin-bottom: 12px;
      font-weight: 700;
      position: relative;
      display: inline-block;
    }
    
    .header h1:after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 80px;
      height: 4px;
      background: var(--secondary);
      border-radius: 2px;
    }
    
    .header p {
      color: var(--gray);
      font-size: 1.1rem;
      max-width: 700px;
      margin: 20px auto 0;
      line-height: 1.6;
    }
    
    .dashboard {
      background: white;
      border-radius: 16px;
      box-shadow: var(--card-shadow);
      overflow: hidden;
      margin-bottom: 30px;
    }
    
    .dashboard-stats {
      display: flex;
      justify-content: space-around;
      background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
      color: white;
      padding: 25px 20px;
    }
    
    .stat-card {
      text-align: center;
      padding: 0 15px;
    }
    
    .stat-card i {
      font-size: 2.5rem;
      margin-bottom: 15px;
      opacity: 0.9;
    }
    
    .stat-card .number {
      font-size: 2.2rem;
      font-weight: 700;
      margin-bottom: 5px;
    }
    
    .stat-card .label {
      font-size: 1.1rem;
      opacity: 0.9;
    }
    
    .search-section {
      padding: 30px;
      background: white;
      border-bottom: 1px solid var(--light-gray);
    }
    
    .search-container {
      max-width: 700px;
      margin: 0 auto;
      position: relative;
    }
    
    .search-container input {
      width: 100%;
      padding: 16px 60px 16px 20px;
      font-size: 1.1rem;
      border: 2px solid var(--light-gray);
      border-radius: 50px;
      outline: none;
      transition: var(--transition);
      font-family: 'Tajawal', sans-serif;
    }
    
    .search-container input:focus {
      border-color: var(--primary);
      box-shadow: 0 5px 15px rgba(67, 97, 238, 0.1);
    }
    
    .search-container button {
      position: absolute;
      top: 50%;
      left: 20px;
      transform: translateY(-50%);
      background: var(--primary);
      color: white;
      border: none;
      border-radius: 50%;
      width: 46px;
      height: 46px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: var(--transition);
    }
    
    .search-container button:hover {
      background: var(--primary-dark);
      transform: translateY(-50%) scale(1.05);
    }
    
    .content-section {
      padding: 30px;
    }
    
    .section-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
    }
    
    .section-title {
      font-size: 1.5rem;
      color: var(--primary);
      font-weight: 600;
    }
    
    .filter-controls {
      display: flex;
      gap: 15px;
    }
    
    .filter-btn {
      background: var(--light);
      border: 1px solid var(--light-gray);
      padding: 10px 20px;
      border-radius: 30px;
      font-family: 'Tajawal', sans-serif;
      cursor: pointer;
      transition: var(--transition);
    }
    
    .filter-btn:hover, .filter-btn.active {
      background: var(--primary);
      color: white;
      border-color: var(--primary);
    }
    
    .items-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 25px;
    }
    
    .item-card {
      background: white;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: var(--card-shadow);
      transition: var(--transition);
      position: relative;
      display: flex;
      flex-direction: column;
      height: 100%;
    }
    
    .item-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
    }
    
    .badge {
      position: absolute;
      top: 15px;
      left: 15px;
      background: var(--success);
      color: white;
      padding: 6px 12px;
      border-radius: 30px;
      font-size: 0.85rem;
      font-weight: 600;
      z-index: 2;
    }
    
    .item-image {
      height: 200px;
      width: 100%;
      overflow: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(45deg, #f0f4ff, #e6eeff);
    }
    
    .item-image img {
      max-width: 80%;
      max-height: 80%;
      object-fit: contain;
      transition: var(--transition);
    }
    
    .item-card:hover .item-image img {
      transform: scale(1.05);
    }
    
    .item-info {
      padding: 20px;
      flex-grow: 1;
      display: flex;
      flex-direction: column;
    }
    
    .item-name {
      font-size: 1.3rem;
      font-weight: 700;
      color: var(--primary);
      margin-bottom: 10px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    
    .item-name i {
      color: var(--secondary);
    }
    
    .item-description {
      color: var(--gray);
      line-height: 1.6;
      margin-bottom: 15px;
      flex-grow: 1;
    }
    
    .item-meta {
      display: flex;
      justify-content: space-between;
      padding-top: 15px;
      border-top: 1px dashed var(--light-gray);
      margin-top: auto;
    }
    
    .item-status {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 0.9rem;
      color: var(--success);
    }
    
    .item-status i {
      font-size: 1.1rem;
    }
    
    .request-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      background: var(--primary);
      color: white;
      padding: 10px 20px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
      transition: var(--transition);
      width: 100%;
      margin-top: 15px;
      border: none;
      cursor: pointer;
      font-family: 'Tajawal', sans-serif;
      font-size: 1rem;
    }
    
    .request-btn:hover {
      background: var(--primary-dark);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
    }
    
    .request-btn i {
      transition: var(--transition);
    }
    
    .request-btn:hover i {
      transform: translateX(3px);
    }
    
    .no-items {
      text-align: center;
      padding: 50px 20px;
      grid-column: 1 / -1;
    }
    
    .no-items i {
      font-size: 4rem;
      color: var(--secondary);
      margin-bottom: 20px;
      opacity: 0.7;
    }
    
    .no-items h3 {
      font-size: 1.8rem;
      color: var(--primary);
      margin-bottom: 15px;
    }
    
    .no-items p {
      color: var(--gray);
      max-width: 600px;
      margin: 0 auto;
      font-size: 1.1rem;
      line-height: 1.7;
    }
    
    .pagination {
      display: flex;
      justify-content: center;
      gap: 8px;
      margin-top: 40px;
    }
    
    .pagination-btn {
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      border: 1px solid var(--light-gray);
      background: white;
      color: var(--dark);
      text-decoration: none;
      font-weight: 600;
      transition: var(--transition);
      cursor: pointer;
    }
    
    .pagination-btn:hover, .pagination-btn.active {
      background: var(--primary);
      color: white;
      border-color: var(--primary);
      transform: translateY(-2px);
    }
    
    .pagination-btn.disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
    
    .loading-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(255, 255, 255, 0.9);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 1000;
      flex-direction: column;
      gap: 20px;
    }
    
    .spinner {
      width: 60px;
      height: 60px;
      border: 5px solid var(--light-gray);
      border-top: 5px solid var(--primary);
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    
    .loading-text {
      font-size: 1.2rem;
      color: var(--primary);
      font-weight: 600;
    }
    
    /* Responsive design */
    @media (max-width: 992px) {
      .dashboard-stats {
        flex-wrap: wrap;
      }
      .stat-card {
        flex: 0 0 50%;
        margin-bottom: 20px;
      }
    }
    
    @media (max-width: 768px) {
      .header h1 {
        font-size: 2rem;
      }
      .stat-card {
        flex: 0 0 100%;
      }
      .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
      }
      .filter-controls {
        width: 100%;
        overflow-x: auto;
        padding-bottom: 10px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1><i class="fas fa-tools"></i> نظام إدارة استعارة الأدوات</h1>
      <p>استعرض الأدوات المتاحة في نظامنا وقدم طلب استعارة بسهولة لأي أداة تحتاجها</p>
    </div>
    
    <div class="dashboard">
      <div class="dashboard-stats">
        <div class="stat-card">
          <i class="fas fa-tools"></i>
          <div class="number"><?php echo $totalItems; ?></div>
          <div class="label">الأدوات المتاحة</div>
        </div>
        <div class="stat-card">
          <i class="fas fa-sync-alt"></i>
          <div class="number">142</div>
          <div class="label">الطلبات النشطة</div>
        </div>
        <div class="stat-card">
          <i class="fas fa-check-circle"></i>
          <div class="number">1,245</div>
          <div class="label">الطلبات المكتملة</div>
        </div>
        <div class="stat-card">
          <i class="fas fa-users"></i>
          <div class="number">346</div>
          <div class="label">المستخدمين النشطين</div>
        </div>
      </div>
      
      <div class="search-section">
        <div class="search-container">
          <input type="text" id="searchInput" placeholder="ابحث عن أداة بالاسم أو الوصف..." value="<?php echo htmlspecialchars($search); ?>">
          <button onclick="fetchItems(1, searchInput.value)">
            <i class="fas fa-search"></i>
          </button>
        </div>
      </div>
      
      <div class="content-section">
        <div class="section-header">
          <div class="section-title">
            <i class="fas fa-box-open"></i> الأدوات المتاحة للاستعارة
          </div>
          <div class="filter-controls">
            <button class="filter-btn active">الكل</button>
            <button class="filter-btn">الأدوات الطبية</button>
            <button class="filter-btn">الأدوات الهندسية</button>
            <button class="filter-btn">الأدوات التعليمية</button>
          </div>
        </div>
        
        <div class="items-grid" id="itemsGrid">
          <!-- Items will be loaded here via JavaScript -->
        </div>
        
        <div class="pagination" id="pagination">
          <!-- Pagination will be loaded here -->
        </div>
      </div>
    </div>
  </div>
  
  <div class="loading-overlay" id="loadingOverlay">
    <div class="spinner"></div>
    <div class="loading-text">جاري تحميل الأدوات...</div>
  </div>

  <script>
    const grid = document.getElementById("itemsGrid");
    const pagination = document.getElementById("pagination");
    const searchInput = document.getElementById("searchInput");
    const loadingOverlay = document.getElementById("loadingOverlay");
    let currentPage = 1;
    let totalPages = 1;

    // Show loading indicator
    function showLoading() {
      loadingOverlay.style.display = 'flex';
    }
    
    // Hide loading indicator
    function hideLoading() {
      loadingOverlay.style.display = 'none';
    }

    // Fetch items from API
    function fetchItems(page = 1, search = '') {
      showLoading();
      fetch(`available_items_api.php?page=${page}&search=${encodeURIComponent(search)}`)
        .then(res => res.json())
        .then(data => {
          currentPage = data.currentPage;
          totalPages = data.totalPages;
          renderItems(data.items);
          renderPagination();
          hideLoading();
        })
        .catch(error => {
          console.error('Error fetching data:', error);
          hideLoading();
          grid.innerHTML = `
            <div class="no-items">
              <i class="fas fa-exclamation-triangle"></i>
              <h3>حدث خطأ أثناء تحميل البيانات</h3>
              <p>تعذر تحميل قائمة الأدوات. يرجى المحاولة مرة أخرى لاحقاً.</p>
              <button class="request-btn" onclick="fetchItems()">
                <i class="fas fa-redo"></i> إعادة المحاولة
              </button>
            </div>
          `;
          pagination.innerHTML = '';
        });
    }

    // Render items in grid
    function renderItems(items) {
      if (items.length === 0) {
        grid.innerHTML = `
          <div class="no-items">
            <i class="fas fa-search"></i>
            <h3>لا توجد أدوات متاحة</h3>
            <p>لم يتم العثور على أي أدوات مطابقة لبحثك. يرجى محاولة استخدام مصطلحات بحث مختلفة.</p>
          </div>
        `;
        return;
      }
      
      grid.innerHTML = items.map(item => `
        <div class="item-card">
          <div class="badge">متاح</div>
          <div class="item-image">
            <img src="${item.image}" alt="${item.name}">
          </div>
          <div class="item-info">
            <h3 class="item-name"><i class="fas fa-cube"></i> ${item.name}</h3>
            <p class="item-description">${item.description}</p>
            <div class="item-meta">
              <div class="item-status">
                <i class="fas fa-check-circle"></i> ${item.status}
              </div>
            </div>
            <a href="/Medical-System/main/new_request.php?item_id=${item.id}" class="request-btn">
              <i class="fas fa-file-alt"></i> تقديم طلب استعارة
            </a>
          </div>
        </div>
      `).join('');
    }

    // Render pagination controls
    function renderPagination() {
      if (totalPages <= 1) {
        pagination.innerHTML = '';
        return;
      }
      
      let paginationHTML = '';
      
      // Previous button
      if (currentPage > 1) {
        paginationHTML += `
          <div class="pagination-btn" onclick="fetchItems(${currentPage - 1}, searchInput.value)">
            <i class="fas fa-chevron-right"></i>
          </div>
        `;
      } else {
        paginationHTML += `
          <div class="pagination-btn disabled">
            <i class="fas fa-chevron-right"></i>
          </div>
        `;
      }
      
      // Page numbers
      const startPage = Math.max(1, currentPage - 2);
      const endPage = Math.min(totalPages, currentPage + 2);
      
      for (let i = startPage; i <= endPage; i++) {
        paginationHTML += `
          <div class="pagination-btn ${i === currentPage ? 'active' : ''}" 
               onclick="fetchItems(${i}, searchInput.value)">
            ${i}
          </div>
        `;
      }
      
      // Next button
      if (currentPage < totalPages) {
        paginationHTML += `
          <div class="pagination-btn" onclick="fetchItems(${currentPage + 1}, searchInput.value)">
            <i class="fas fa-chevron-left"></i>
          </div>
        `;
      } else {
        paginationHTML += `
          <div class="pagination-btn disabled">
            <i class="fas fa-chevron-left"></i>
          </div>
        `;
      }
      
      pagination.innerHTML = paginationHTML;
    }

    // Initial load
    fetchItems(<?php echo $page; ?>, '<?php echo $search; ?>');
    
    // Search listener with debounce
    let searchTimeout;
    searchInput.addEventListener("keyup", () => {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        fetchItems(1, searchInput.value);
      }, 500);
    });
    
    // Also trigger search on Enter key
    searchInput.addEventListener("keypress", (e) => {
      if (e.key === "Enter") {
        clearTimeout(searchTimeout);
        fetchItems(1, searchInput.value);
      }
    });
  </script>
</body>
</html>

<?php 
$dataStmt->close();
$conn->close(); 
?>