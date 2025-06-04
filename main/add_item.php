<?php
session_start();  // Start the session to access session variables

// Redirect if user is not logged in or is not a lender
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'lender') {
    header("Location: login.php");
    exit;
}

include __DIR__ . '/../database/db.php';  // Ensure the path to db.php is correct
include("../main/partials/header-1.php");

$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['name'], $_POST['description'], $_POST['status'])) {
        // These are the form values
        $name = $_POST['name'];
        $description = $_POST['description'];
        $status = $_POST['status'];
        $available = 'available';
        $owner_id = $_SESSION['user_id'];

        // Optional image upload code
        $image_path = "";
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $upload_dir = "uploads/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir);
            }

            $file_name = uniqid() . "_" . $_FILES['image']['name'];
            $file_tmp = $_FILES['image']['tmp_name'];
            $image_path = $upload_dir . $file_name;

            move_uploaded_file($file_tmp, $image_path);
        }

        // Prepare and execute the insert query
        $sql = "INSERT INTO items (name, description, status, image, user_id) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $name, $description, $status, $image_path, $owner_id);

        if ($stmt->execute()) {
            $message = "✅ تم إضافة الأداة بنجاح!";
        } else {
            $message = "❌ حدث خطأ أثناء الإضافة: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $message = "❌ بعض الحقول مفقودة.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة أداة | نظام الإعارة الطبية</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #7209b7;
            --success: #06d6a0;
            --danger: #ef476f;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --border-radius: 16px;
            --shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            background: linear-gradient(135deg, #f0f4f8 0%, #e6f0fa 100%);
            color: var(--dark);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0;
        }

        .form-header {
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: white;
            padding: 30px;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .form-header::before {
            content: "";
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .form-header::after {
            content: "";
            position: absolute;
            bottom: -80px;
            left: -30px;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.07);
            border-radius: 50%;
        }

        .form-header h1 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 2;
        }

        .form-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }

        .form-card {
            background: white;
            border-radius: 0 0 var(--border-radius) var(--border-radius);
            padding: 40px;
            box-shadow: var(--shadow);
            position: relative;
            z-index: 10;
        }

        .form-group {
            margin-bottom: 28px;
            position: relative;
        }

        .form-group label {
            display: block;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 12px;
            color: var(--primary);
            display: flex;
            align-items: center;
        }

        .form-group label i {
            margin-left: 10px;
            font-size: 1.2rem;
        }

        .input-with-icon {
            position: relative;
        }

        .input-with-icon i {
            position: absolute;
            top: 50%;
            left: 18px;
            transform: translateY(-50%);
            color: var(--gray);
            font-size: 1.2rem;
        }

        input, select, textarea {
            width: 100%;
            padding: 16px 20px 16px 55px;
            border: 2px solid var(--light-gray);
            border-radius: 12px;
            font-size: 1.1rem;
            font-family: 'Tajawal', sans-serif;
            transition: var(--transition);
            background: #fafbff;
        }

        input:focus, select:focus, textarea:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.15);
            background: white;
        }

        textarea {
            min-height: 140px;
            resize: vertical;
            padding: 20px;
        }

        .file-upload {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border: 2px dashed var(--light-gray);
            border-radius: 12px;
            padding: 40px 20px;
            background: #fafbff;
            cursor: pointer;
            transition: var(--transition);
        }

        .file-upload:hover {
            border-color: var(--primary);
            background: rgba(67, 97, 238, 0.03);
        }

        .file-upload i {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 20px;
        }

        .file-upload p {
            color: var(--gray);
            font-size: 1.1rem;
            text-align: center;
            margin-bottom: 15px;
        }

        .file-upload span {
            display: inline-block;
            padding: 10px 25px;
            background: var(--primary);
            color: white;
            border-radius: 8px;
            font-weight: 600;
            transition: var(--transition);
        }

        .file-upload:hover span {
            background: var(--primary-dark);
        }

        #file-name {
            margin-top: 15px;
            font-size: 0.95rem;
            color: var(--gray);
            text-align: center;
        }

        button[type="submit"] {
            display: block;
            width: 100%;
            padding: 18px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: white;
            font-size: 1.3rem;
            font-weight: 700;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 20px;
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.3);
            position: relative;
            overflow: hidden;
        }

        button[type="submit"]::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }

        button[type="submit"]:hover::before {
            left: 100%;
        }

        button[type="submit"]:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(67, 97, 238, 0.4);
        }

        .message {
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            font-weight: 600;
            font-size: 1.1rem;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .message.success {
            background: rgba(6, 214, 160, 0.15);
            color: #057a55;
            border: 2px solid rgba(6, 214, 160, 0.3);
        }

        .message.error {
            background: rgba(239, 71, 111, 0.15);
            color: #b91c1c;
            border: 2px solid rgba(239, 71, 111, 0.3);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 25px;
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
        }

        .back-link:hover {
            color: var(--secondary);
            transform: translateX(-5px);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                margin: 20px auto;
            }
            
            .form-header {
                padding: 25px 20px;
            }
            
            .form-header h1 {
                font-size: 1.8rem;
            }
            
            .form-card {
                padding: 30px 20px;
            }
            
            input, select, textarea {
                padding: 14px 18px 14px 50px;
                font-size: 1rem;
            }
            
            button[type="submit"] {
                font-size: 1.1rem;
                padding: 16px;
            }
        }

        @media (max-width: 480px) {
            .form-header h1 {
                font-size: 1.6rem;
            }
            
            .form-header p {
                font-size: 1rem;
            }
            
            .form-group label {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-header">
            <h1><i class="fas fa-plus-circle"></i> إضافة أداة جديدة</h1>
            <p>أضف أداتك الطبية للمشاركة في نظام الإعارة ومساعدة المحتاجين</p>
        </div>
        
        <div class="form-card">
            <?php if ($message != ""): ?>
                <div class="message <?= strpos($message, '✅') !== false ? 'success' : 'error' ?>">
                    <i class="fas <?= strpos($message, '✅') !== false ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" id="item-form">
                <div class="form-group">
                    <label for="name"><i class="fas fa-toolbox"></i> اسم الأداة</label>
                    <div class="input-with-icon">
                        <i class="fas fa-stethoscope"></i>
                        <input type="text" name="name" id="name" placeholder="أدخل اسم الأداة" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description"><i class="fas fa-align-left"></i> الوصف</label>
                    <textarea name="description" id="description" placeholder="وصف مفصل للأداة ومواصفاتها..." required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="status"><i class="fas fa-clipboard-check"></i> حالة الأداة</label>
                    <div class="input-with-icon">
                        <i class="fas fa-battery-full"></i>
                        <select name="status" id="status" required>
                            <option value="" disabled selected>اختر حالة الأداة</option>
                            <option value="new">جديدة</option>
                            <option value="good">جيدة جداً</option>
                            <option value="used">مستخدمة قليلاً</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-camera"></i> صورة الأداة (اختياري)</label>
                    <div class="file-upload" id="upload-area">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>اسحب وأسقط الصورة هنا أو انقر للاختيار</p>
                        <span>اختر صورة</span>
                        <input type="file" name="image" id="image" accept="image/*" hidden>
                        <div id="file-name"></div>
                    </div>
                </div>
                
                <button type="submit">
                    <i class="fas fa-plus-circle"></i> إضافة الأداة
                </button>
            </form>
            
            <a href="dashboard.php" class="back-link">
                <i class="fas fa-arrow-right"></i> العودة إلى لوحة التحكم
            </a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // File upload functionality
            const uploadArea = document.getElementById('upload-area');
            const fileInput = document.getElementById('image');
            const fileName = document.getElementById('file-name');
            
            uploadArea.addEventListener('click', () => {
                fileInput.click();
            });
            
            fileInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    fileName.textContent = 'تم اختيار: ' + this.files[0].name;
                    uploadArea.style.borderColor = '#4361ee';
                    uploadArea.style.backgroundColor = 'rgba(67, 97, 238, 0.05)';
                } else {
                    fileName.textContent = '';
                }
            });
            
            // Drag and drop functionality
            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.style.borderColor = '#4361ee';
                uploadArea.style.backgroundColor = 'rgba(67, 97, 238, 0.1)';
            });
            
            uploadArea.addEventListener('dragleave', () => {
                uploadArea.style.borderColor = '#e9ecef';
                uploadArea.style.backgroundColor = '#fafbff';
            });
            
            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                if (e.dataTransfer.files.length) {
                    fileInput.files = e.dataTransfer.files;
                    fileName.textContent = 'تم اختيار: ' + e.dataTransfer.files[0].name;
                    uploadArea.style.borderColor = '#4361ee';
                    uploadArea.style.backgroundColor = 'rgba(67, 97, 238, 0.05)';
                }
            });
            
            // Form validation
            const form = document.getElementById('item-form');
            form.addEventListener('submit', function(e) {
                let valid = true;
                
                // Check required fields
                const requiredFields = form.querySelectorAll('[required]');
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        valid = false;
                        field.style.borderColor = '#ef476f';
                        setTimeout(() => {
                            field.style.borderColor = '#e9ecef';
                        }, 2000);
                    }
                });
                
                if (!valid) {
                    e.preventDefault();
                    alert('يرجى ملء جميع الحقول المطلوبة');
                }
            });
        });
    </script>
</body>
</html>