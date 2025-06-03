<?php
function getNavLinks() {
    if (isset($_SESSION['role'])) {
        switch ($_SESSION['role']) {
            case 'user':
                return [
                    ['url' => '/Medical-System/main/home.php', 'text' => 'الرئيسية', 'icon' => 'fa-home'],
                    ['url' => '/Medical-System/main/new_request.php', 'text' => 'طلب أداة', 'icon' => 'fa-plus-circle'],
                    ['url' => '/Medical-System/main/my_requests.php', 'text' => 'طلباتي', 'icon' => 'fa-list'],
                    ['url' => '/Medical-System/auth/logout.php', 'text' => 'تسجيل الخروج', 'icon' => 'fa-sign-out-alt', 'class' => 'logout']
                ];
            case 'lender':
                return [
                    ['url' => '/Medical-System/main/lender_dashboard.php', 'text' => 'الرئيسية', 'icon' => 'fa-home'],
                    ['url' => '/Medical-System/main/add_item.php', 'text' => 'إضافة أداة', 'icon' => 'fa-plus'],
                    ['url' => '/Medical-System/main/my_items.php', 'text' => 'أدواتي', 'icon' => 'fa-tools'],
                    ['url' => '/Medical-System/auth/logout.php', 'text' => 'تسجيل الخروج', 'icon' => 'fa-sign-out-alt', 'class' => 'logout']
                ];
            case 'admin':
                return [
                    ['url' => '/Medical-System/main/dashboard.php', 'text' => 'لوحة التحكم', 'icon' => 'fa-tachometer-alt'],
                    ['url' => '/Medical-System/main/all_users.php', 'text' => 'المستخدمين', 'icon' => 'fa-users'],
                    ['url' => '/Medical-System/main/all_requests.php', 'text' => 'الطلبات', 'icon' => 'fa-clipboard-list'],
                    ['url' => '/Medical-System/auth/logout.php', 'text' => 'تسجيل الخروج', 'icon' => 'fa-sign-out-alt', 'class' => 'logout']
                ];
            default:
                return [];
        }
    } else {
        return [
            ['url' => '/Medical-System/main/about.php', 'text' => 'من نحن', 'icon' => 'fa-info-circle'],
            ['url' => '/Medical-System/auth/login_page.php', 'text' => 'تسجيل الدخول', 'icon' => 'fa-sign-in-alt'],
            ['url' => '/Medical-System/auth/signup_page.php', 'text' => 'إنشاء حساب', 'icon' => 'fa-user-plus']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعارة طبية</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6a11cb;
            --secondary-color: #2575fc;
            --hover-color: #4a00e0;
            --text-color: #ffffff;
            --logout-color: #ff4757;
            --logout-hover: #ff6b81;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Tajawal', sans-serif;
        }

        .main-header {
      background: linear-gradient(to right, var(--primary), var(--secondary));
            color: var(--text-color);
            padding: 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: var(--shadow);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            height: 70px;
        }

        .logo {
            display: flex;
            align-items: center;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-color);
            text-decoration: none;
        }

        .logo-icon {
            margin-left: 10px;
            font-size: 1.8rem;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .nav-links a {
            color: var(--text-color);
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1rem;
            font-weight: 500;
            transition: var(--transition);
        }

        .nav-links a i {
            font-size: 1.1rem;
        }

        .nav-links a:not(.logout):hover {
            background-color: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }

        .logout {
            background-color: var(--logout-color);
            padding: 10px 20px;
        }

        .logout:hover {
            background-color: var(--logout-hover);
            transform: translateY(-2px);
        }

        .hamburger {
            display: none;
            cursor: pointer;
            font-size: 1.5rem;
        }

        /* Mobile Styles */
        @media (max-width: 992px) {
            .header-content {
                padding: 0 15px;
            }
            
            .nav-links {
                position: fixed;
                top: 70px;
                right: -100%;
                width: 80%;
                max-width: 300px;
                height: calc(100vh - 70px);
                background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
                flex-direction: column;
                align-items: flex-start;
                padding: 20px;
                gap: 10px;
                transition: var(--transition);
                box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
            }

            .nav-links.active {
                right: 0;
            }

            .nav-links a {
                width: 100%;
                padding: 15px;
                border-radius: 5px;
                justify-content: flex-start;
            }

            .hamburger {
                display: block;
            }
        }

        /* Animation for mobile menu */
        @keyframes slideIn {
            from {
                transform: translateX(100%);
            }
            to {
                transform: translateX(0);
            }
        }

        .nav-links.active {
            animation: slideIn 0.3s ease-out;
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="header-content">
            <a href="/Medical-System" class="logo">
                <span>إعارة طبية</span>
                <i class="fas fa-hand-holding-medical logo-icon"></i>
            </a>

            <div class="hamburger" id="hamburger">
                <i class="fas fa-bars"></i>
            </div>

            <nav class="nav-links" id="navLinks">
                <?php
                $navLinks = getNavLinks();
                foreach ($navLinks as $link) {
                    $class = isset($link['class']) ? 'class="' . $link['class'] . '"' : '';
                    echo '<a href="' . $link['url'] . '" ' . $class . '>';
                    echo '<i class="fas ' . $link['icon'] . '"></i>';
                    echo '<span>' . $link['text'] . '</span>';
                    echo '</a>';
                }
                ?>
            </nav>
        </div>
    </header>

    <script>
        // Mobile menu toggle
        const hamburger = document.getElementById('hamburger');
        const navLinks = document.getElementById('navLinks');

        hamburger.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            
            // Toggle hamburger icon
            const icon = hamburger.querySelector('i');
            if (navLinks.classList.contains('active')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!navLinks.contains(e.target) && e.target !== hamburger && !hamburger.contains(e.target)) {
                navLinks.classList.remove('active');
                const icon = hamburger.querySelector('i');
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
    </script>
</body>
</html>