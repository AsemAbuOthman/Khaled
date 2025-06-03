<?php
session_start();
include __DIR__ . '/../database/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$search = trim($_GET['search'] ?? '');
$perPage = 8;
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$where = "reserve_status = 'available'";
$params = [];
$paramTypes = "";

if ($search !== '') {
    $where .= " AND (name LIKE ? OR description LIKE ?)";
    $searchParam = "%$search%";
    $params[] = &$searchParam;
    $params[] = &$searchParam;
    $paramTypes .= "ss";
}

// Total count
$countQuery = "SELECT COUNT(*) as total FROM items WHERE $where";
$countStmt = $conn->prepare($countQuery);
if ($paramTypes) {
    $countStmt->bind_param($paramTypes, ...$params);
}
$countStmt->execute();
$totalItems = $countStmt->get_result()->fetch_assoc()['total'];
$countStmt->close();
$totalPages = ceil($totalItems / $perPage);

// Items
$dataQuery = "SELECT * FROM items WHERE $where ORDER BY created_at DESC LIMIT ? OFFSET ?";
$dataStmt = $conn->prepare($dataQuery);
$paramTypesData = $paramTypes . "ii";
$params[] = &$perPage;
$params[] = &$offset;
$dataStmt->bind_param($paramTypesData, ...$params);
$dataStmt->execute();
$result = $dataStmt->get_result();

$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = [
        'id' => $row['item_id'],
        'name' => $row['name'],
        'description' => mb_strimwidth(strip_tags($row['description']), 0, 150, '...'),
        'image' => (!empty($row['image']) && file_exists($row['image'])) ? $row['image'] : 'default-image.jpg',
        'status' => $row['reserve_status']
    ];
}

echo json_encode([
    'items' => $items,
    'totalPages' => $totalPages,
    'currentPage' => $page
]);
