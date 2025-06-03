<?php
session_start();
include("../main/partials/header-1.php");
include __DIR__ . '/../database/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php"); exit;
}

$user_id     = $_SESSION['user_id'];
$perPage     = 8;
$page        = max(1, intval($_GET['page'] ?? 1));
$offset      = ($page - 1) * $perPage;
$filter      = in_array($_GET['status'] ?? '', ['pending','accepted','rejected'])
                ? $_GET['status']
                : '';
$status_map  = ['pending'=>'معلق','accepted'=>'مقبول','rejected'=>'مرفوض'];

// 1) Count total (with optional filter)
$count_sql = "SELECT COUNT(*) FROM reservations WHERE user_id = ?"
           . ($filter ? " AND status = ?" : "");
$count_stmt = $conn->prepare($count_sql);
if ($filter) {
    $count_stmt->bind_param("is",$user_id,$filter);
} else {
    $count_stmt->bind_param("i",$user_id);
}
$count_stmt->execute();
$total = $count_stmt->get_result()->fetch_row()[0];

// 2) Fetch page of requests
$sql = "
  SELECT r.*, i.name AS item_name, i.image
  FROM reservations r
  JOIN items      i ON r.item_id = i.item_id
  WHERE r.user_id = ?" 
  . ($filter ? " AND r.status = ?" : "") . "
  ORDER BY r.created_at DESC
  LIMIT ? OFFSET ?
";
$stmt = $conn->prepare($sql);

if ($filter) {
    $stmt->bind_param("isii", $user_id, $filter, $perPage, $offset);
} else {
    $stmt->bind_param("iii", $user_id, $perPage, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
$conn->close();

// Helper: Arabic “ago” formatting
function arabic_ago($dt) {
    $diff = time() - strtotime($dt);
    if ($diff < 60) return "الآن";
    if ($diff < 3600) return floor($diff/60)." دقيقة مضت";
    if ($diff < 86400) return floor($diff/3600)." ساعة مضت";
    return floor($diff/86400)." يوم مضى";
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>طلباتي</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body {
      background: linear-gradient(to bottom, #f0f8ff, #ffffff);
      font-family: 'Tajawal', sans-serif;
      margin: 0; padding: 0;
    }
    .container {
      max-width: 900px;
      margin: 40px auto;
      padding: 20px;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 0 12px rgba(0, 0, 0, 0.07);
    }
    h2 {
      text-align: center;
      color: #0077cc;
      margin-bottom: 30px;
    }
    .request-card {
      border: 1px solid #ccc;
      padding: 20px;
      border-radius: 10px;
      margin-bottom: 20px;
      background: #fdfdfd;
    }
    .request-card p {
      margin: 6px 0;
    }
    .request-card .status {
      font-weight: bold;
      padding: 4px 8px;
      border-radius: 4px;
      display: inline-block;
    }
    .status.pending  { background:#fff3cd; color:#856404; }  /* معلق */
    .status.accepted { background:#d4edda; color:#155724; }  /* مقبول */
    .status.rejected { background:#f8d7da; color:#721c24; }  /* مرفوض */

    .request-card a.document-link {
      display: inline-block;
      margin-top: 10px;
      color: #0077cc;
      text-decoration: none;
      font-weight: bold;
    }
    .request-card a.document-link:hover {
      text-decoration: underline;
    }

    .btn-new {
      display: inline-block;
      margin: 20px auto 0;
      padding: 10px 20px;
      background-color: #0077cc;
      color: #fff;
      text-decoration: none;
      border-radius: 6px;
      font-weight: bold;
      transition: background-color 0.3s;
    }
    .btn-new:hover {
      background-color: #005fa3;
    }
    .requests-filter {
  text-align: center;
  margin-bottom: 20px;
}
.requests-filter select {
  padding: 8px 12px;
  border-radius: 4px;
  border: 1px solid #ccc;
  font-size: 1rem;
}
.requests-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px,1fr));
  gap: 20px;
}
.request-card {
  background: #ffffff;
  border: 1px solid #ddd;
  border-radius: 10px;
  padding: 16px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.05);
  display: flex;
  flex-direction: column;
}
.request-card img.thumb {
  width: 100%;
  max-height: 140px;
  object-fit: cover;
  border-radius: 6px;
  margin-bottom: 12px;
}
.request-card .meta {
  font-size: 0.9rem;
  color: #555;
  margin-bottom: 8px;
}
.request-card .status {
  margin-bottom: 12px;
}
  </style>
</head>
<body>
  <div class="container">
    <h2>📑 طلباتي السابقة</h2>

    <div class="requests-filter">
      <form method="get">
        <select name="status" onchange="this.form.submit()">
          <option value="">— كافة الحالات —</option>
          <?php foreach($status_map as $key=>$label): ?>
            <option 
              value="<?= $key ?>" 
              <?= $key === $filter ? 'selected' : '' ?>>
               <?= $label ?>
            </option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>

    <?php if ($total): ?>
      <div class="requests-grid">
      <?php while($row = $result->fetch_assoc()): ?>
        <?php
          $status_key   = $row['status'];
          $status_label = $status_map[$status_key] ?? $status_key;
          $img          = $row['image'] && file_exists(__DIR__.'/'.$row['image'])
                          ? $row['image']
                          : 'placeholder.jpg'; // fallback
          $docPath      = $row['documents_path'];
          $docExists    = $docPath && file_exists(__DIR__.'/'.$docPath);
        ?>
        <div class="request-card">
          <img src="<?= htmlspecialchars($img) ?>" alt="" class="thumb">
          <div class="meta"><strong>أداة:</strong> <?= htmlspecialchars($row['item_name']) ?></div>
          <div class="meta">
            <strong>الفترة:</strong>
            <?= htmlspecialchars($row['start_date']) ?> →
            <?= htmlspecialchars($row['end_date']) ?>
          </div>
          <div class="meta">
            <strong>مقدم:</strong> <?= arabic_ago($row['created_at']) ?>
          </div>
          <div class="status <?= $status_key ?>">
            <?= htmlspecialchars($status_label) ?>
          </div>

          <?php if ($docExists): ?>
            <?php $size = round(filesize(__DIR__.'/'.$docPath)/1024,1); ?>
            <a 
              href="<?= htmlspecialchars($docPath) ?>" 
              target="_blank" 
              class="document-link">
              📎 عرض الوثيقة (<?= $size ?> KB)
            </a>
          <?php endif; ?>
        </div>
      <?php endwhile; ?>
      </div>

      <!-- pagination -->
      <?php $pages = ceil($total/$perPage); ?>
      <?php if ($pages>1): ?>
        <div class="pagination" style="text-align:center;margin:30px 0;">
          <?php for($p=1;$p<=$pages;$p++): ?>
            <a 
              href="?status=<?= $filter ?>&page=<?= $p ?>"
              style="margin:0 5px;
                     text-decoration:none;
                     color:<?= $p===$page?'#fff':'#0077cc' ?>;
                     background:<?= $p===$page?'#0077cc':'transparent' ?>;
                     padding:6px 10px;
                     border-radius:4px;"
            ><?= $p ?></a>
          <?php endfor; ?>
        </div>
      <?php endif; ?>

    <?php else: ?>
      <p style="text-align:center; color:#888;">🚫 لم تقم بتقديم أي طلبات بعد.</p>
      <p style="text-align:center;">
        <a href="new_request.php" class="btn-new">➕ تقديم طلب جديد</a>
      </p>
    <?php endif; ?>

  </div>
</body>
</html>