<?php
include __DIR__ . '../../database/db.php';

$cities = [];
$sql = "SELECT * FROM cities";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cities[] = $row;
    }
}

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء حساب جديد</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            display: flex;
            width: 100%;
            max-width: 900px;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }
        
        .left-panel {
            flex: 1;
            background: linear-gradient(135deg, #2575fc 0%, #6a11cb 100%);
            color: white;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        
        .left-panel h2 {
            font-size: 2.2rem;
            margin-bottom: 20px;
        }
        
        .left-panel p {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 30px;
            max-width: 350px;
        }
        
        .left-panel .features {
            text-align: right;
            width: 100%;
            max-width: 300px;
        }
        
        .feature {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 10px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
        
        .feature i {
            font-size: 1.5rem;
            margin-left: 15px;
            flex-shrink: 0;
        }
        
        .feature div {
            flex-grow: 1;
        }
        
        .feature h3 {
            font-size: 1.1rem;
            margin-bottom: 5px;
        }
        
        .right-panel {
            flex: 1;
            padding: 40px;
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-header h2 {
            color: #2575fc;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .form-header p {
            color: #666;
        }
        
        .form-container {
            width: 100%;
        }
        
        .input-group {
            margin-bottom: 20px;
        }
        
        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #444;
            font-weight: 500;
        }
        
        .input-group input,
        .input-group select {
            width: 100%;
            padding: 14px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .input-group input:focus,
        .input-group select:focus {
            border-color: #2575fc;
            box-shadow: 0 0 0 3px rgba(37, 117, 252, 0.2);
            outline: none;
        }
        
        .row {
            display: flex;
            gap: 20px;
        }
        
        .row .input-group {
            flex: 1;
        }
        
        .password-container {
            position: relative;
        }
        
        .password-container input {
            padding-right: 45px;
        }
        
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 42px;
            cursor: pointer;
            color: #777;
        }
        
        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .error-message i {
            margin-left: 10px;
            font-size: 1.2rem;
        }
        
        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(to right, #2575fc, #6a11cb);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 14px rgba(37, 117, 252, 0.3);
        }
        
        .login-link {
            text-align: center;
            margin-top: 25px;
            color: #666;
        }
        
        .login-link a {
            color: #2575fc;
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            
            .left-panel {
                padding: 30px 20px;
            }
            
            .row {
                flex-direction: column;
                gap: 0;
            }
        }
        
        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
        }
        
        .logo-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #2575fc, #6a11cb);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 15px;
        }
        
        .logo-icon i {
            color: white;
            font-size: 1.8rem;
        }
        
        .logo-text {
            font-size: 1.8rem;
            font-weight: 700;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <div class="logo-text">تأجير</div>
            </div>
            <h2>انضم إلى مجتمعنا</h2>
            <p>سجل الآن للوصول إلى آلاف العناصر المتاحة للإعارة ولتبدأ في مشاركة ما تملك مع الآخرين</p>
            
            <div class="features">
                <div class="feature">
                    <i class="fas fa-shield-alt"></i>
                    <div>
                        <h3>حماية وأمان</h3>
                        <p>بياناتك محمية دائماً معنا</p>
                    </div>
                </div>
                <div class="feature">
                    <i class="fas fa-exchange-alt"></i>
                    <div>
                        <h3>تبادل سهل</h3>
                        <p>عملية إعارة بسيطة وسريعة</p>
                    </div>
                </div>
                <div class="feature">
                    <i class="fas fa-headset"></i>
                    <div>
                        <h3>دعم فني</h3>
                        <p>فريق دعم متاح على مدار الساعة</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="right-panel">
            <div class="form-header">
                <h2>إنشاء حساب جديد</h2>
                <p>املأ النموذج أدناه للتسجيل في النظام</p>
            </div>
            
            <div class="form-container">
                <?php if (!empty($_SESSION['signup_error'])): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <div><?= $_SESSION['signup_error']; unset($_SESSION['signup_error']); ?></div>
                    </div>
                <?php endif; ?>
                
                <form action="signup.php" method="POST">
                    <div class="row">
                        <div class="input-group">
                            <label for="firstName">الاسم الأول:</label>
                            <input type="text" name="FirstName" id="firstName" placeholder="أدخل اسمك الأول" required>
                        </div>
                        <div class="input-group">
                            <label for="midName">اسم الأب:</label>
                            <input type="text" name="MidName" id="midName" placeholder="أدخل اسم والدك">
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-group">
                            <label for="lastName">اسم العائلة:</label>
                            <input type="text" name="LastName" id="lastName" placeholder="أدخل اسم العائلة" required>
                        </div>
                        <div class="input-group">
                            <label for="email">البريد الإلكتروني:</label>
                            <input type="email" name="email" id="email" placeholder="example@domain.com" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-group">
                            <label for="phone">رقم الهاتف:</label>
                            <input type="text" name="phone" id="phone" placeholder="05XXXXXXXX" required>
                        </div>
                        <div class="input-group">
                            <label for="Role">نوع المستخدم:</label>
                            <select name="Role" id="Role">
                                <option value="user">مستفيد</option>
                                <option value="lender">معير</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-group">
                            <label for="city_id">المدينة:</label>
                            <select name="city_id" id="city_id" required>
                                <option value="">-- اختر المدينة --</option>
                                <?php if (!empty($cities)): ?>
                                    <?php foreach ($cities as $city): ?>
                                        <option value="<?= htmlspecialchars($city['city_id']) ?>">
                                            <?= htmlspecialchars($city['city_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="">لا توجد مدن متاحة</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="input-group">
                            <label for="password">كلمة المرور:</label>
                            <div class="password-container">
                                <input type="password" name="password" id="password" placeholder="كلمة مرور قوية" required>
                                <span class="toggle-password" onclick="togglePassword()">
                                    <i class="far fa-eye"></i>
                                </span>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn">إنشاء الحساب</button>
                </form>
                
                <div class="login-link">
                    <p>لديك حساب بالفعل؟ <a href="login_page.php">تسجيل الدخول</a></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const icon = document.querySelector('.toggle-password i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // Simple form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const phone = document.getElementById('phone').value;
            
            if (password.length < 6) {
                alert('كلمة المرور يجب أن تكون 6 أحرف على الأقل');
                e.preventDefault();
            }
            
            if (!/^07\d{8}$/.test(phone)) {
                alert('رقم الهاتف يجب أن يبدأ بـ 05 ويحتوي على 10 أرقام');
                e.preventDefault();
            }
        });
    </script>
    
    <?php
    // Close the database connection after rendering HTML
    if (isset($conn)) {
        $conn->close();
    }
    ?>
</body>
</html>