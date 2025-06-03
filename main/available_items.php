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
  <title>الأدوات المتاحة</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body {
      background: linear-gradient(to bottom, #f0f8ff, #ffffff);
      font-family: 'Tajawal', sans-serif;
      margin: 0;
      padding: 0;
    }
    .container {
      max-width: 1100px;
      margin: 40px auto;
      padding: 20px;
    }
    h2 {
      text-align: center;
      color: #0077cc;
      margin-bottom: 20px;
    }
    .search-bar {
      text-align: center;
      margin-bottom: 25px;
    }
    .search-bar input[type="text"] {
      width: 60%;
      padding: 10px 15px;
      font-size: 1rem;
      border: 1px solid #ccc;
      border-radius: 8px;
    }
    .items-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 20px;
    }
    .item-card {
      background-color: #ffffff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
      transition: 0.3s ease;
      text-align: center;
    }
    .item-card:hover {
      transform: translateY(-5px);
    }
    .item-card img {
      max-width: 100%;
      max-height: 180px;
      margin-bottom: 15px;
      border-radius: 6px;
    }
    .item-card h3 {
      color: #0077cc;
      margin-bottom: 10px;
    }
    .item-card p {
      color: #555;
      margin: 5px 0;
    }
    .item-card .request-btn {
      display: inline-block;
      margin-top: 10px;
      padding: 10px 16px;
      background-color: #0077cc;
      color: white;
      border-radius: 6px;
      text-decoration: none;
      font-weight: bold;
      transition: background-color 0.3s ease;
    }
    .item-card .request-btn:hover {
      background-color: #005fa3;
    }
    .no-items {
      text-align: center;
      padding: 40px 0;
      color: #666;
    }
    .pagination {
      text-align: center;
      margin: 30px 0;
    }
    .pagination a {
      margin: 0 5px;
      text-decoration: none;
      padding: 6px 12px;
      border-radius: 4px;
      border: 1px solid #0077cc;
      color: #0077cc;
    }
    .pagination a.active {
      background-color: #0077cc;
      color: #fff;
    }
  </style>
</head>
<div class="container">
  <h2>🧰 الأدوات المتاحة للاستعارة</h2>

  <div class="search-bar">
    <form id="searchForm">
      <input type="text" id="searchInput" placeholder="ابحث باسم أو وصف الأداة...">
    </form>
  </div>

  <div class="items-grid" id="itemsGrid"></div>

  <div class="pagination" id="pagination"></div>
</div>

<script>
  const grid = document.getElementById("itemsGrid");
  const pagination = document.getElementById("pagination");
  const searchInput = document.getElementById("searchInput");

  let currentPage = 1;

  function fetchItems(page = 1, search = '') {
    fetch(`available_items_api.php?page=${page}&search=${encodeURIComponent(search)}`)
      .then(res => res.json())
      .then(data => {
        currentPage = data.currentPage;
        renderItems(data.items);
        renderPagination(data.totalPages);
      });
  }

  function renderItems(items) {
    grid.innerHTML = items.length ? items.map(item => `
      <div class="item-card">
        <img src="${item.image}" alt="صورة الأداة">
        <h3>${item.name}</h3>
        <p><strong>الوصف:</strong> ${item.description}</p>
        <p><strong>الحالة:</strong> ${item.status}</p>
        <a href="/Medical-System/main/new_request.php?item_id=${item.id}" class="request-btn">📝 تقديم طلب استعارة</a>
      </div>
    `).join('') : `<p class="no-items">🚫 لا توجد أدوات متاحة حالياً.</p>`;
  }

  function renderPagination(totalPages) {
    pagination.innerHTML = '';
    for (let i = 1; i <= totalPages; i++) {
      pagination.innerHTML += `
        <a href="#" class="${i === currentPage ? 'active' : ''}" onclick="fetchItems(${i}, searchInput.value); return false;">
          ${i}
        </a>
      `;
    }
  }

  // Initial load
  fetchItems();

  // Search listener
  let searchTimeout;

searchInput.addEventListener("keyup", () => {
  clearTimeout(searchTimeout); // debounce
  searchTimeout = setTimeout(() => {
    fetchItems(1, searchInput.value);
  }, 300); // wait 300ms after typing stops
});
</script>

</html>

<?php $conn->close(); ?>
