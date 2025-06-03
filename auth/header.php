
<header class="main-header">
  <div class="header-content">
    <div class="logo">📦 إعارة طبية</div>

    <nav class="nav-links">
      <?php if (isset($_SESSION['user_role'])): ?>
        <?php if ($_SESSION['user_role'] == 'beneficiary'): ?>
          <a href="beneficiary_dashboard.php">الرئيسية</a>
          <a href="request_tool.php">طلب أداة</a>
          <a href="my_requests.php">طلباتي</a>
        <?php elseif ($_SESSION['user_role'] == 'lender'): ?>
          <a href="lender_dashboard.php">الرئيسية</a>
          <a href="add_item.php">إضافة أداة</a>
          <a href="my_items.php">أدواتي</a>
        <?php elseif ($_SESSION['user_role'] == 'admin'): ?>
          <a href="admin_dashboard.php">لوحة التحكم</a>
          <a href="users.php">المستخدمين</a>
          <a href="all_requests.php">الطلبات</a>
        <?php endif; ?>
        <a href="about.php">من نحن</a>
        <a href="logout.php" class="logout">تسجيل الخروج</a>
      <?php else: ?>
        <a href="about.php">من نحن</a>
        <a href="login.php">تسجيل الدخول</a>
        <a href="register.php">إنشاء حساب</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<style>
 /* ========== الهيدر ========== */
.main-header {
  background-color: var(--card-bg);
  padding: 15px 30px;
  border-bottom: 1px solid var(--border-color);
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
  position: fixed;
  top: 0;
  width: 100%;
  z-index: 1000;
  transition: background-color 0.3s, border-color 0.3s;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
}

.logo {
  font-size: 1.8rem;
  font-weight: bold;
  color: var(--primary-color);
  white-space: nowrap;
}

/* ========== روابط التنقل ========== */
.nav-links {
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
  align-items: center;
}

.nav-links a {
  padding: 8px 14px;
  border-radius: 8px;
  transition: background-color 0.3s, color 0.3s;
  color: var(--text-color);
  font-weight: 500;
}

.nav-links a:hover {
  background-color: var(--primary-color);
  color: white;
}

.nav-links a.logout {
  background-color: var(--primary-color);
  color: white;
}

.nav-links a.logout:hover {
  background-color: var(--primary-hover);
}

/* ========== تجاوب الهيدر ========== */
@media (max-width: 768px) {
  .header-content {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }

  .nav-links {
    flex-direction: column;
    width: 100%;
  }

  .nav-links a {
    width: 100%;
    text-align: right;
  }
}

</style>