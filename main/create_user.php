<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة مستخدم جديد - لوحة التحكم</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #3f37c9;
            --accent: #4cc9f0;
            --success: #4ade80;
            --danger: #f43f5e;
            --warning: #f59e0b;
            --gray-100: #f8f9fa;
            --gray-200: #e9ecef;
            --gray-300: #dee2e6;
            --gray-700: #495057;
            --gray-900: #212529;
            --radius: 12px;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7ff 0%, #eef2ff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: var(--gray-900);
            line-height: 1.6;
        }

        .container {
            max-width: 1000px;
            width: 100%;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: var(--primary-dark);
            font-size: 2.5rem;
            margin-bottom: 10px;
            position: relative;
            display: inline-block;
        }

        .header h1:after {
            content: '';
            position: absolute;
            bottom: -10px;
            right: 50%;
            transform: translateX(50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, var(--accent), var(--primary));
            border-radius: 2px;
        }

        .header p {
            color: var(--gray-700);
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
            margin-top: 20px;
        }

        .form-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .form-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 40px;
            transition: var(--transition);
        }

        .form-card:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        .card-title {
            color: var(--primary);
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--gray-200);
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--gray-700);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-control {
            width: 100%;
            padding: 14px 18px;
            border-radius: 8px;
            border: 2px solid var(--gray-200);
            font-size: 16px;
            transition: var(--transition);
            background: var(--gray-100);
        }

        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            background: white;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: left 15px center;
            background-size: 16px 12px;
            padding-right: 15px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 14px 28px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            gap: 10px;
            font-size: 16px;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            width: 100%;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
        }

        .btn-outline {
            background: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-outline:hover {
            background: rgba(67, 97, 238, 0.1);
            transform: translateY(-2px);
        }

        .back-container {
            text-align: center;
            margin-top: 25px;
        }

        .file-upload {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .file-upload-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 18px;
            border-radius: 8px;
            border: 2px solid var(--gray-200);
            background: var(--gray-100);
            font-size: 16px;
            cursor: pointer;
            transition: var(--transition);
        }

        .file-upload-label:hover {
            background: var(--gray-200);
        }

        .file-upload-label span {
            color: var(--gray-700);
        }

        .file-upload-label i {
            color: var(--primary);
        }

        .file-input {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            right: 0;
            opacity: 0;
            cursor: pointer;
        }

        .info-text {
            font-size: 14px;
            color: var(--gray-700);
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            font-size: 2.5rem;
            color: var(--primary);
            background: linear-gradient(135deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-flex;
            align-items: center;
            gap: 15px;
        }

        @media (max-width: 900px) {
            .form-container {
                grid-template-columns: 1fr;
            }
            
            .form-card {
                padding: 30px;
            }
        }

        @media (max-width: 576px) {
            .form-card {
                padding: 25px 20px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-container">
            <div class="logo">
                <i class="fas fa-user-plus"></i>
                <span>لوحة التحكم</span>
            </div>
        </div>
        
        <div class="header">
            <h1>إضافة مستخدم جديد</h1>
            <p>املأ النموذج أدناه لإضافة مستخدم جديد إلى النظام. تأكد من صحة جميع البيانات قبل الإرسال.</p>
        </div>
        
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-container">
                <!-- Personal Information Card -->
                <div class="form-card">
                    <h2 class="card-title"><i class="fas fa-user-circle"></i> المعلومات الشخصية</h2>
                    
                    <div class="form-group">
                        <label for="first_name"><i class="fas fa-signature"></i> الاسم الأول</label>
                        <input type="text" id="first_name" name="first_name" class="form-control" placeholder="أدخل الاسم الأول" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="second_name"><i class="fas fa-signature"></i> الاسم الثاني</label>
                            <input type="text" id="second_name" name="second_name" class="form-control" placeholder="أدخل الاسم الثاني" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name"><i class="fas fa-signature"></i> الاسم الأخير</label>
                            <input type="text" id="last_name" name="last_name" class="form-control" placeholder="أدخل الاسم الأخير" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i> البريد الإلكتروني</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="example@domain.com" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password"><i class="fas fa-lock"></i> كلمة المرور</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="أنشئ كلمة مرور قوية" required>
                    </div>
                </div>
                
                <!-- Account Details Card -->
                <div class="form-card">
                    <h2 class="card-title"><i class="fas fa-cog"></i> إعدادات الحساب</h2>
                    
                    <div class="form-group">
                        <label for="phone"><i class="fas fa-phone"></i> رقم الهاتف</label>
                        <input type="text" id="phone" name="phone" class="form-control" placeholder="+966 5X XXX XXXX" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="role"><i class="fas fa-user-tag"></i> الدور</label>
                        <select id="role" name="role" class="form-control" required>
                            <option value="">-- اختر دور المستخدم --</option>
                            <option value="user">مستخدم عادي</option>
                            <option value="admin">مشرف النظام</option>
                            <option value="lender">مُقرض أدوات</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="document"><i class="fas fa-file-pdf"></i> المستند (اختياري)</label>
                        <div class="file-upload">
                            <label class="file-upload-label">
                                <span id="file-name">اختر ملف PDF</span>
                                <i class="fas fa-cloud-upload-alt"></i>
                            </label>
                            <input type="file" id="document" name="document" class="file-input" accept="application/pdf">
                        </div>
                        <p class="info-text"><i class="fas fa-info-circle"></i> يجب أن يكون الملف من نوع PDF فقط</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="city_id"><i class="fas fa-city"></i> المدينة</label>
                        <select id="city_id" name="city_id" class="form-control" required>
                            <option value="">-- اختر المدينة --</option>
                            <option value="1">الرياض</option>
                            <option value="2">جدة</option>
                            <option value="3">مكة المكرمة</option>
                            <option value="4">المدينة المنورة</option>
                            <option value="5">الدمام</option>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="location"><i class="fas fa-map-marker-alt"></i> الموقع</label>
                            <input type="text" id="location" name="location" class="form-control" placeholder="أدخل موقعك" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="street"><i class="fas fa-road"></i> الشارع</label>
                            <input type="text" id="street" name="street" class="form-control" placeholder="اسم الشارع" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i>
                        إضافة مستخدم جديد
                    </button>
                </div>
            </div>
        </form>
        
        <div class="back-container">
            <a href="all_users.php" class="btn btn-outline">
                <i class="fas fa-arrow-right"></i>
                رجوع إلى قائمة المستخدمين
            </a>
        </div>
    </div>

    <script>
        // File name display
        document.getElementById('document').addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : 'اختر ملف PDF';
            document.getElementById('file-name').textContent = fileName;
        });
        
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            if (password.length < 8) {
                alert('كلمة المرور يجب أن تكون على الأقل 8 أحرف');
                e.preventDefault();
            }
            
            const phone = document.getElementById('phone').value;
            const saudiPhoneRegex = /^(009665|9665|\+9665|05|5)(5|0|3|6|4|9|1|8|7)([0-9]{7})$/;
            if (!saudiPhoneRegex.test(phone)) {
                alert('يرجى إدخال رقم هاتف سعودي صحيح');
                e.preventDefault();
            }
        });
        
        // Add animation on load
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.form-card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 200 * index);
            });
        });
    </script>
</body>
</html>