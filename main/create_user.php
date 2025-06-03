<?php
ob_start();
session_start();
include("../main/partials/header-1.php");
require_once __DIR__ . '/../database/db.php';

// Redirect if user is not logged in or not admin
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Fetch cities from the database
$sql = "SELECT city_id, city_name FROM cities ORDER BY city_name";
$stmt = $conn->prepare($sql);
$stmt->execute();
$cities = $stmt->get_result();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name   = trim($_POST['first_name'] ?? '');
    $second_name  = trim($_POST['second_name'] ?? '');
    $last_name    = trim($_POST['last_name'] ?? '');
    $email        = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password     = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone        = trim($_POST['phone'] ?? '');
    $role         = trim($_POST['role'] ?? '');
    $city_id      = filter_input(INPUT_POST, 'city_id', FILTER_SANITIZE_NUMBER_INT);
    $location     = trim($_POST['location'] ?? '');
    $street       = trim($_POST['street'] ?? '');

    // Initialize document URL
    $document = '';
    if (!empty($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['document']['tmp_name'];
        $fileName = $_FILES['document']['name'];
        $fileSize = $_FILES['document']['size'];
        $fileType = mime_content_type($fileTmpPath);

        $allowedFileTypes = ['application/pdf'];
        if (in_array($fileType, $allowedFileTypes)) {
            $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
            $newFileName = uniqid('doc_') . '.' . $fileExtension;
            $uploadDir = __DIR__ . '/uploads/documents/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $uploadFilePath = $uploadDir . $newFileName;
            if (move_uploaded_file($fileTmpPath, $uploadFilePath)) {
                $document = '/uploads/documents/' . $newFileName;
            } else {
                exit('Error uploading the document.');
            }
        } else {
            exit('Only PDF files are allowed.');
        }
    }

// Check if user already exists by email
$checkSql = "SELECT user_id FROM users WHERE email = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    echo "<p style='color: red;'>A user with this email already exists.</p>";
} else {
    // Insert user data into the database
    $sql = "INSERT INTO users (first_name, second_name, last_name, email, password, phone, role, document, city_id, location, street, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssssssiss',
        $first_name,
        $second_name,
        $last_name,
        $email,
        $password,
        $phone,
        $role,
        $document,
        $city_id,
        $location,
        $street
    );

    if ($stmt->execute()) {
        header('Location: all_users.php');
        exit;
    } else {
        echo "<p style='color: red;'>Database Error: " . htmlspecialchars($stmt->error) . "</p>";
    }
}
}
ob_end_flush();

?>


<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إضافة مستخدم جديد</title>
    <link rel="stylesheet" href="style.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --light-gray: #ecf0f1;
            --dark-gray: #7f8c8d;
            --border-radius: 10px;
            --shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: var(--light-gray);
            margin: 0;
            padding: 0;
            color: var(--primary-color);
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 2em;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            background: white;
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }
        input, select, button {
            padding: 10px 15px;
            border-radius: var(--border-radius);
            font-size: 16px;
            border: 1px solid var(--dark-gray);
        }
        button {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #2980b9;
        }
        .back-button {
            background-color: #7f8c8d;
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: var(--border-radius);
            display: inline-block;
            margin-bottom: 20px;
        }
        .back-button:hover {
            background-color: #95a5a6;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>إضافة مستخدم جديد</h2>
        
        <!-- Back Button -->
        <a href="all_users.php" class="back-button">رجوع إلى جميع المستخدمين</a>

        <form method="POST" action="" enctype="multipart/form-data">
            <label>الاسم الأول:</label><input type="text" name="first_name" required><br>
            <label>الاسم الثاني:</label><input type="text" name="second_name" required><br>
            <label>الاسم الأخير:</label><input type="text" name="last_name" required><br>
            <label>البريد الإلكتروني:</label><input type="email" name="email" required><br>
            <label>كلمة المرور:</label><input type="password" name="password" required><br>
            <label>رقم الهاتف:</label><input type="text" name="phone" required><br>
            <label>الدور:</label>
            <select name="role">
                <option value="user">مستخدم</option>
                <option value="admin">مشرف</option>
                <option value="lender">مُقرض</option>
            </select><br>
            <label>المستند (PDF):</label><input type="file" name="document" accept="application/pdf"><br>
            
            <!-- Cities Dropdown -->
            <label>المدينة:</label>
            <select name="city_id" required>
                <option value="">-- اختر المدينة --</option>
                <?php while ($row = $cities->fetch_assoc()): ?>
                    <option value="<?= $row['city_id'] ?>"><?= htmlspecialchars($row['city_name']) ?></option>
                <?php endwhile; ?>
            </select><br>

            <label>الموقع:</label><input type="text" name="location" required><br>
            <label>الشارع:</label><input type="text" name="street" required><br>
            <button type="submit">إضافة مستخدم</button>
        </form>
    </div>
</body>
</html>
