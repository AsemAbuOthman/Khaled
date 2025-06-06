<?php
session_start();  // Start the session to access session variables

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include __DIR__ . '/../database/db.php';  // Database connection

$user_id = $_SESSION['user_id'];
$message = "";
$success = false;

// Fetch user data
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['update_profile'])) {
        // Update profile information
        $first_name = $_POST['first_name'];
        $second_name = $_POST['second_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $role = $_POST['role'];
        $city_id = $_POST['city_id'];
        $location = $_POST['location'];
        $street = $_POST['street'];
        $locationUrl = $_POST['locationUrl'];
        
        // Handle document upload
        $document_path = $user['document'];
        if (isset($_FILES['document']) && $_FILES['document']['error'] === 0) {
            $upload_dir = "documents/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir);
            }
            
            $file_name = uniqid() . "_" . $_FILES['document']['name'];
            $file_tmp = $_FILES['document']['tmp_name'];
            $document_path = $upload_dir . $file_name;
            
            move_uploaded_file($file_tmp, $document_path);
        }
        
        // Update query with locationUrl
        $sql = "UPDATE users SET 
                first_name = ?, 
                second_name = ?, 
                last_name = ?, 
                email = ?, 
                phone = ?, 
                role = ?, 
                city_id = ?, 
                location = ?, 
                street = ?,
                document = ?,
                locationUrl = ?
                WHERE user_id = ?";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssissssi", 
            $first_name, 
            $second_name, 
            $last_name, 
            $email, 
            $phone, 
            $role, 
            $city_id, 
            $location, 
            $street,
            $document_path,
            $locationUrl,
            $user_id
        );
        
        if ($stmt->execute()) {
            $message = "✅ تم تحديث الملف الشخصي بنجاح!";
            $success = true;
            // Refresh user data
            $sql = "SELECT * FROM users WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
        } else {
            $message = "❌ حدث خطأ أثناء تحديث الملف الشخصي: " . $stmt->error;
        }
    } elseif (isset($_POST['delete_account'])) {
        // Delete account
        $sql = "DELETE FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            session_destroy();
            header("Location: login.php?message=تم حذف حسابك بنجاح");
            exit;
        } else {
            $message = "❌ حدث خطأ أثناء حذف الحساب: " . $stmt->error;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ملفي الشخصي | نظام الإعارة الطبية</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #7209b7;
            --success: #06d6a0;
            --danger: #ef476f;
            --warning: #ffd166;
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
            max-width: 1000px;
            margin: 40px auto;
            padding: 0;
        }

        .profile-header {
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: white;
            padding: 40px 30px;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .profile-header::before {
            content: "";
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .profile-header::after {
            content: "";
            position: absolute;
            bottom: -80px;
            left: -30px;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.07);
            border-radius: 50%;
        }

        .profile-header h1 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 2;
        }

        .profile-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }

        .profile-card {
            background: white;
            border-radius: 0 0 var(--border-radius) var(--border-radius);
            padding: 40px;
            box-shadow: var(--shadow);
            position: relative;
            z-index: 10;
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
        }

        .profile-sidebar {
            flex: 1;
            min-width: 250px;
            background: var(--light);
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--shadow);
            text-align: center;
        }

        .profile-content {
            flex: 3;
            min-width: 300px;
        }

        .avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #4361ee, #7209b7);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: white;
        }

        .user-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--primary);
        }

        .user-role {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            background: var(--primary);
            color: white;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }

        .user-role.lender {
            background: var(--secondary);
        }

        .user-role.admin {
            background: var(--danger);
        }

        .user-stats {
            display: flex;
            justify-content: space-around;
            margin: 25px 0;
            padding: 15px 0;
            border-top: 1px solid var(--light-gray);
            border-bottom: 1px solid var(--light-gray);
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--gray);
        }

        .sidebar-actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 20px;
        }

        .action-btn {
            padding: 12px;
            border-radius: 8px;
            background: white;
            border: 2px solid var(--light-gray);
            color: var(--dark);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .action-btn:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .action-btn.delete:hover {
            background: var(--danger);
            border-color: var(--danger);
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light-gray);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-col {
            flex: 1;
            min-width: 250px;
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

        .file-upload {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border: 2px dashed var(--light-gray);
            border-radius: 12px;
            padding: 25px;
            background: #fafbff;
            cursor: pointer;
            transition: var(--transition);
        }

        .file-upload:hover {
            border-color: var(--primary);
            background: rgba(67, 97, 238, 0.03);
        }

        .file-upload i {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 15px;
        }

        .file-upload p {
            color: var(--gray);
            font-size: 1.1rem;
            text-align: center;
            margin-bottom: 15px;
        }

        .file-upload span {
            display: inline-block;
            padding: 8px 20px;
            background: var(--primary);
            color: white;
            border-radius: 8px;
            font-weight: 600;
            transition: var(--transition);
        }

        .file-upload:hover span {
            background: var(--primary-dark);
        }

        #document-name {
            margin-top: 15px;
            font-size: 0.95rem;
            color: var(--gray);
            text-align: center;
        }

        .document-preview {
            margin-top: 15px;
            padding: 15px;
            background: var(--light);
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .document-preview i {
            font-size: 2rem;
            color: var(--primary);
        }

        .map-preview {
            margin-top: 15px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--light-gray);
            height: 200px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }

        .map-preview i {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 15px;
        }

        .map-preview p {
            color: var(--gray);
            font-size: 1.1rem;
        }

        button[type="submit"] {
            display: inline-block;
            padding: 15px 35px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: white;
            font-size: 1.2rem;
            font-weight: 700;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 10px;
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

        /* Delete confirmation modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            border-radius: var(--border-radius);
            padding: 30px;
            max-width: 500px;
            width: 90%;
            box-shadow: var(--shadow);
            text-align: center;
        }

        .modal-title {
            font-size: 1.8rem;
            color: var(--danger);
            margin-bottom: 20px;
        }

        .modal-text {
            font-size: 1.1rem;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .modal-actions {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .modal-btn {
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: var(--transition);
        }

        .modal-btn.confirm {
            background: var(--danger);
            color: white;
        }

        .modal-btn.confirm:hover {
            background: #d32f2f;
        }

        .modal-btn.cancel {
            background: var(--light-gray);
            color: var(--dark);
        }

        .modal-btn.cancel:hover {
            background: #d1d1d1;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                margin: 20px auto;
            }
            
            .profile-header {
                padding: 30px 20px;
            }
            
            .profile-header h1 {
                font-size: 1.8rem;
            }
            
            .profile-card {
                padding: 30px 20px;
                flex-direction: column;
            }
            
            input, select, textarea {
                padding: 14px 18px 14px 50px;
                font-size: 1rem;
            }
            
            button[type="submit"] {
                font-size: 1.1rem;
                padding: 14px 25px;
            }
        }

        @media (max-width: 480px) {
            .profile-header h1 {
                font-size: 1.6rem;
            }
            
            .profile-header p {
                font-size: 1rem;
            }
            
            .form-group label {
                font-size: 1rem;
            }
            
            .avatar {
                width: 120px;
                height: 120px;
                font-size: 3rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="profile-header">
            <h1><i class="fas fa-user-circle"></i> ملفي الشخصي</h1>
            <p>إدارة معلومات حسابك وتحديث بياناتك الشخصية</p>
        </div>
        
        <div class="profile-card">
            <div class="profile-sidebar">
                <div class="avatar">
                    <?php 
                    $initials = substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1);
                    echo $initials;
                    ?>
                </div>
                <h2 class="user-name"><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></h2>
                <div class="user-role <?php echo $user['role']; ?>"><?php echo $user['role'] == 'lender' ? 'مقرض' : ($user['role'] == 'admin' ? 'مدير' : 'مستخدم'); ?></div>
                
                <div class="user-stats">
                    <div class="stat-item">
                        <div class="stat-value">12</div>
                        <div class="stat-label">الإعارات</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">8</div>
                        <div class="stat-label">التقييمات</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">4.8</div>
                        <div class="stat-label">التقييم</div>
                    </div>
                </div>
                
                <p><i class="fas fa-envelope"></i> <?php echo $user['email']; ?></p>
                <p><i class="fas fa-phone"></i> <?php echo $user['phone'] ? $user['phone'] : 'لم يتم إضافة رقم هاتف'; ?></p>
                
                <div class="sidebar-actions">
                    <button class="action-btn" onclick="document.getElementById('deleteModal').style.display='flex'">
                        <i class="fas fa-trash-alt"></i> حذف الحساب
                    </button>
                    <a href="dashboard.php" class="action-btn">
                        <i class="fas fa-arrow-right"></i> العودة للوحة التحكم
                    </a>
                </div>
            </div>
            
            <div class="profile-content">
                <?php if ($message != ""): ?>
                    <div class="message <?php echo $success ? 'success' : 'error'; ?>">
                        <i class="fas <?php echo $success ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <h2 class="section-title"><i class="fas fa-user-edit"></i> تحديث الملف الشخصي</h2>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="first_name"><i class="fas fa-user"></i> الاسم الأول</label>
                                <input type="text" name="first_name" id="first_name" 
                                    value="<?php echo $user['first_name']; ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-col">
                            <div class="form-group">
                                <label for="second_name"><i class="fas fa-user"></i> اسم الأب</label>
                                <input type="text" name="second_name" id="second_name" 
                                    value="<?php echo $user['second_name']; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="last_name"><i class="fas fa-user"></i> اسم العائلة</label>
                                <input type="text" name="last_name" id="last_name" 
                                    value="<?php echo $user['last_name']; ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-col">
                            <div class="form-group">
                                <label for="email"><i class="fas fa-envelope"></i> البريد الإلكتروني</label>
                                <input type="email" name="email" id="email" 
                                    value="<?php echo $user['email']; ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="phone"><i class="fas fa-phone"></i> رقم الهاتف</label>
                                <input type="tel" name="phone" id="phone" 
                                    value="<?php echo $user['phone']; ?>">
                            </div>
                        </div>
                        
                        <div class="form-col">
                            <div class="form-group">
                                <label for="role"><i class="fas fa-user-tag"></i> الدور</label>
                                <select name="role" id="role" required>
                                    <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>مستخدم</option>
                                    <option value="lender" <?php echo $user['role'] == 'lender' ? 'selected' : ''; ?>>مقرض</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="city_id"><i class="fas fa-city"></i> المدينة</label>
                                <select name="city_id" id="city_id" required>
                                    <option value="1" <?php echo $user['city_id'] == 1 ? 'selected' : ''; ?>>الرياض</option>
                                    <option value="2" <?php echo $user['city_id'] == 2 ? 'selected' : ''; ?>>جدة</option>
                                    <option value="3" <?php echo $user['city_id'] == 3 ? 'selected' : ''; ?>>مكة المكرمة</option>
                                    <option value="4" <?php echo $user['city_id'] == 4 ? 'selected' : ''; ?>>المدينة المنورة</option>
                                    <option value="5" <?php echo $user['city_id'] == 5 ? 'selected' : ''; ?>>الدمام</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-col">
                            <div class="form-group">
                                <label for="location"><i class="fas fa-map-marker-alt"></i> المنطقة</label>
                                <input type="text" name="location" id="location" 
                                    value="<?php echo $user['location']; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="street"><i class="fas fa-road"></i> الشارع</label>
                        <input type="text" name="street" id="street" 
                            value="<?php echo $user['street']; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="locationUrl"><i class="fas fa-map-marked-alt"></i> رابط الموقع الجغرافي</label>
                        <input type="url" name="locationUrl" id="locationUrl"
                        placeholder="https://maps.google.com/?q=..." />
                        <div id="map" style="width: 100%; height: 400px; margin-top: 10px;"></div>
                        
                        <?php if (!empty($user['locationUrl'])): ?>
                            <div class="map-preview">
                                <i class="fas fa-map-marker-alt"></i>
                                <p>رابط موقعك الجغرافي: <a href="<?php echo $user['locationUrl']; ?>" target="_blank">عرض على الخريطة</a></p>
                            </div>
                        <?php else: ?>
                            <div class="map-preview">
                                <i class="fas fa-map-marked-alt"></i>
                                <p>لم يتم إضافة رابط الموقع الجغرافي بعد</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-file-alt"></i> وثيقة الهوية (اختياري)</label>
                        <div class="file-upload" id="document-upload">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>اسحب وأسقط الملف هنا أو انقر للاختيار</p>
                            <span>اختر ملف</span>
                            <input type="file" name="document" id="document" accept=".pdf,.doc,.docx,.jpg,.png" hidden>
                            <div id="document-name"></div>
                        </div>
                        
                        <?php if ($user['document']): ?>
                            <div class="document-preview">
                                <i class="fas fa-file-pdf"></i>
                                <span>تم رفع ملف: <?php echo basename($user['document']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" name="update_profile">
                        <i class="fas fa-save"></i> حفظ التغييرات
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteModal">
        <div class="modal-content">
            <h2 class="modal-title"><i class="fas fa-exclamation-triangle"></i> تأكيد حذف الحساب</h2>
            <p class="modal-text">
                هل أنت متأكد من رغبتك في حذف حسابك؟ هذا الإجراء لا يمكن التراجع عنه وسيؤدي إلى إزالة جميع بياناتك بشكل دائم من النظام.
            </p>
            <div class="modal-actions">
                <form method="POST" style="display:inline;">
                    <button type="submit" name="delete_account" class="modal-btn confirm">نعم، احذف الحساب</button>
                </form>
                <button class="modal-btn cancel" onclick="document.getElementById('deleteModal').style.display='none'">إلغاء</button>
            </div>
        </div>
    </div>

    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDJplAnffqQ83dYpipHCiZ7zJ9CZgc6dL8"></script>
    
    <script>
        function initMap() {
            const defaultLocation = { lat: 30.0444, lng: 31.2357 }; // Cairo as default
            const map = new google.maps.Map(document.getElementById("map"), {
            zoom: 10,
            center: defaultLocation,
            });

            let marker = new google.maps.Marker({
            map,
            position: defaultLocation,
            draggable: true,
            });

            // Update input on marker drag
            marker.addListener("dragend", function (event) {
            updateLocation(event.latLng);
            });

            // Update input on map click
            map.addListener("click", function (event) {
            marker.setPosition(event.latLng);
            updateLocation(event.latLng);
            });

            function updateLocation(latlng) {
            const lat = latlng.lat();
            const lng = latlng.lng();
            document.getElementById("locationUrl").value = `https://maps.google.com/?q=${lat},${lng}`;
            }
        }

        // Wait for the page to fully load, then initialize the map
        window.addEventListener("load", initMap);
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Document upload functionality
            const documentUpload = document.getElementById('document-upload');
            const documentInput = document.getElementById('document');
            const documentName = document.getElementById('document-name');
            
            documentUpload.addEventListener('click', () => {
                documentInput.click();
            });
            
            documentInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    documentName.textContent = 'تم اختيار: ' + this.files[0].name;
                    documentUpload.style.borderColor = '#4361ee';
                    documentUpload.style.backgroundColor = 'rgba(67, 97, 238, 0.05)';
                } else {
                    documentName.textContent = '';
                }
            });
            
            // Drag and drop functionality
            documentUpload.addEventListener('dragover', (e) => {
                e.preventDefault();
                documentUpload.style.borderColor = '#4361ee';
                documentUpload.style.backgroundColor = 'rgba(67, 97, 238, 0.1)';
            });
            
            documentUpload.addEventListener('dragleave', () => {
                documentUpload.style.borderColor = '#e9ecef';
                documentUpload.style.backgroundColor = '#fafbff';
            });
            
            documentUpload.addEventListener('drop', (e) => {
                e.preventDefault();
                if (e.dataTransfer.files.length) {
                    documentInput.files = e.dataTransfer.files;
                    documentName.textContent = 'تم اختيار: ' + e.dataTransfer.files[0].name;
                    documentUpload.style.borderColor = '#4361ee';
                    documentUpload.style.backgroundColor = 'rgba(67, 97, 238, 0.05)';
                }
            });
            
            // Close modal if clicked outside
            window.addEventListener('click', (e) => {
                const modal = document.getElementById('deleteModal');
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>