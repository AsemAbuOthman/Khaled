<div class="sidebar">
    <div class="sidebar-header">
    <h2><i class="fas fa-cogs"></i> <span>لوحة التحكم</span></h2>
    </div>
    <div class="nav-links">
    <a href="dashboard.php" class="nav-item ">
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
    </div>
</div>

<style>
    /* Sidebar */
    .sidebar {
        width: 260px;
        background: #1e293b; /* --sidebar */
        color: #ffffff;       /* --white */
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
        color: #06d6a0; /* --success */
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
        background: #334155;  /* --sidebar-hover */
        color: #ffffff;
        border-right: 4px solid #4361ee;  /* --primary */
    }

    .nav-item i {
        width: 24px;
        text-align: center;
    }

    /* Admin Profile */
    .admin-profile {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .admin-avatar {
        width: 45px;
        height: 45px;
        background: linear-gradient(135deg, #4361ee, #7209b7); /* --primary, --secondary */
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-weight: bold;
        font-size: 1.2rem;
    }

    .admin-info h4 {
        font-size: 1.1rem;
        color: #1e293b; /* --dark */
    }

    .admin-info p {
        font-size: 0.9rem;
        color: #64748b; /* --gray */
    }

    .logout-btn {
        background: #ef476f; /* --danger */
        color: #ffffff;
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
</style>