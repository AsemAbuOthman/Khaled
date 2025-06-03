<?php
session_start();
// Check for login errors
$login_error = '';
if (!empty($_SESSION['login_error'])) {
    $login_error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول</title>
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
            display: flex;
            flex-direction: column;
            justify-content: center;
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
            margin-bottom: 25px;
        }
        
        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #444;
            font-weight: 500;
        }
        
        .input-group input {
            width: 100%;
            padding: 14px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .input-group input:focus {
            border-color: #2575fc;
            box-shadow: 0 0 0 3px rgba(37, 117, 252, 0.2);
            outline: none;
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
        
        .login-links {
            display: flex;
            justify-content: space-between;
            margin-top: 25px;
            color: #666;
        }
        
        .login-links a {
            color: #2575fc;
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-links a:hover {
            text-decoration: underline;
        }
        
        .social-login {
            margin-top: 30px;
            text-align: center;
        }
        
        .social-login p {
            color: #666;
            margin-bottom: 15px;
            position: relative;
        }
        
        .social-login p::before,
        .social-login p::after {
            content: "";
            position: absolute;
            top: 50%;
            width: 30%;
            height: 1px;
            background: #ddd;
        }
        
        .social-login p::before {
            left: 0;
        }
        
        .social-login p::after {
            right: 0;
        }
        
        .social-icons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        
        .social-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f5f7fa;
            color: #555;
            font-size: 1.3rem;
            transition: all 0.3s;
        }
        
        .social-icon:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .facebook { color: #1877f2; }
        .twitter { color: #1da1f2; }
        .google { color: #ea4335; }
        
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            
            .left-panel {
                padding: 30px 20px;
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
            <h2>مرحبًا بعودتك</h2>
            <p>سجل دخولك للاستمرار في رحلتك معنا واستكشاف آلاف العناصر المتاحة للإعارة</p>
            
            <div class="features">
                <div class="feature">
                    <i class="fas fa-lock"></i>
                    <div>
                        <h3>حساب آمن</h3>
                        <p>بياناتك محمية بأحدث تقنيات التشفير</p>
                    </div>
                </div>
                <div class="feature">
                    <i class="fas fa-bolt"></i>
                    <div>
                        <h3>دخول سريع</h3>
                        <p>وصل إلى حسابك بخطوات بسيطة</p>
                    </div>
                </div>
                <div class="feature">
                    <i class="fas fa-history"></i>
                    <div>
                        <h3>استئناف النشاط</h3>
                        <p>استكمل من حيث توقفت في تجربتك</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="right-panel">
            <div class="form-header">
                <h2>تسجيل الدخول</h2>
                <p>أدخل بياناتك للوصول إلى حسابك</p>
            </div>
            
            <div class="form-container">
                <?php if (!empty($login_error)): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <div><?= htmlspecialchars($login_error) ?></div>
                    </div>
                <?php endif; ?>
                
                <form action="login.php" method="POST">
                    <div class="input-group">
                        <label for="email">البريد الإلكتروني:</label>
                        <input type="email" name="email" id="email" placeholder="example@domain.com" required>
                    </div>
                    
                    <div class="input-group">
                        <label for="password">كلمة المرور:</label>
                        <div class="password-container">
                            <input type="password" name="password" id="password" placeholder="أدخل كلمة المرور" required>
                            <span class="toggle-password" onclick="togglePassword()">
                                <i class="far fa-eye"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div class="login-links">
                        <div>
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">تذكرني</label>
                        </div>
                        <a href="forgot_password.php">نسيت كلمة المرور؟</a>
                    </div>
                    
                    <button type="submit" class="btn">تسجيل الدخول</button>
                </form>
                
                <div class="social-login">
                    <p>أو سجل الدخول باستخدام</p>
                    <div class="social-icons">
                        <a href="#" class="social-icon facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon google"><i class="fab fa-google"></i></a>
                    </div>
                </div>
                
                <div class="login-links" style="justify-content: center; margin-top: 20px;">
                    <p>ليس لديك حساب؟ <a href="signup_page.php">إنشاء حساب جديد</a></p>
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
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                e.preventDefault();
                alert('الرجاء ملء جميع الحقول المطلوبة');
                return false;
            }
            
            // Simple email validation
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                e.preventDefault();
                alert('الرجاء إدخال بريد إلكتروني صحيح');
                return false;
            }
            
            return true;
        });
    </script>
</body>
</html>