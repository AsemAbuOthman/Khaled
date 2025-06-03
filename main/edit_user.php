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

// Fetch user data for editing
$user_id = filter_input(INPUT_GET, 'user_id', FILTER_SANITIZE_NUMBER_INT);
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle form submission to update user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs manually
    $first_name = trim(htmlspecialchars($_POST['first_name'] ?? ''));
    $second_name = trim(htmlspecialchars($_POST['second_name'] ?? ''));
    $last_name = trim(htmlspecialchars($_POST['last_name'] ?? ''));
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $phone = trim(htmlspecialchars($_POST['phone'] ?? ''));
    $role = trim(htmlspecialchars($_POST['role'] ?? ''));
    $city_id = filter_var($_POST['city_id'] ?? '', FILTER_VALIDATE_INT);
    $location = trim(htmlspecialchars($_POST['location'] ?? ''));
    $street = trim(htmlspecialchars($_POST['street'] ?? ''));

    // Length validations (adjust max lengths to your DB schema)
    if (strlen($first_name) > 128 || strlen($second_name) > 128 || strlen($last_name) > 128) {
        echo "Name fields cannot exceed 128 characters.";
        exit;
    }

   if (strlen($location) > 100) {
    echo "Location is too long. Maximum 100 characters allowed.";
    exit;
}


    if (strlen($street) > 255) {
        echo "Street is too long. Maximum 255 characters allowed.";
        exit;
    }

    // Retain current document if not updated
    $document = $user['document'];

    // Handle the document upload
    if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['document']['tmp_name'];
        $fileName = $_FILES['document']['name'];
        $fileType = $_FILES['document']['type'];

        $allowedFileTypes = ['application/pdf'];
        if (in_array($fileType, $allowedFileTypes)) {
            $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
            $newFileName = uniqid('doc_') . '.' . $fileExtension;
            $uploadDir = __DIR__ . '/uploads/documents/';
            
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $uploadFilePath = $uploadDir . $newFileName;
            if (move_uploaded_file($fileTmpPath, $uploadFilePath)) {
                $document = '/uploads/documents/' . $newFileName;
            } else {
                echo "Error uploading the document!";
                exit;
            }
        } else {
            echo "Only PDF files are allowed!";
            exit;
        }
    }

    // Prepare SQL to update data
    $sql = "UPDATE users SET first_name = ?, second_name = ?, last_name = ?, email = ?, phone = ?, role = ?, document = ?, city_id = ?, location = ?, street = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssssssssi', $first_name, $second_name, $last_name, $email, $phone, $role, $document, $city_id, $location, $street, $user_id);
    
    if ($stmt->execute()) {
        header('Location: all_users.php');
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}
ob_end_flush();

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تعديل المستخدم</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Reuse the same styles as the create page */
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
        <h2>تعديل المستخدم</h2>
        <!-- Back Button -->
        <a href="all_users.php" class="back-button">رجوع إلى جميع المستخدمين</a>

        <form method="POST" action="" enctype="multipart/form-data">
            <label>الاسم الأول:</label><input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required><br>
            <label>الاسم الثاني:</label><input type="text" name="second_name" value="<?= htmlspecialchars($user['second_name']) ?>" required><br>
            <label>الاسم الأخير:</label><input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required><br>
            <label>البريد الإلكتروني:</label><input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br>
            <label>رقم الهاتف:</label><input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required><br>
            <label>الدور:</label>
            <select name="role">
                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>مستخدم</option>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>مشرف</option>
                <option value="lender" <?= $user['role'] === 'lender' ? 'selected' : '' ?>>مُقرض</option>
            </select><br>
            <label>المستند (PDF):</label><input type="file" name="document" accept="application/pdf"><br>
            
            <!-- Cities Dropdown -->
            <label>المدينة:</label>
            <select name="city_id" required>
                <option value="">-- اختر المدينة --</option>
                <?php while ($row = $cities->fetch_assoc()): ?>
                    <option value="<?= $row['city_id'] ?>" <?= $user['city_id'] == $row['city_id'] ? 'selected' : '' ?>><?= htmlspecialchars($row['city_name']) ?></option>
                <?php endwhile; ?>
            </select><br>

            <label>الموقع:</label><input type="text" name="location" value="<?= htmlspecialchars($user['location']) ?>" required><br>
            <label>الشارع:</label><input type="text" name="street" value="<?= htmlspecialchars($user['street']) ?>" required><br>
            <button type="submit">تعديل المستخدم</button>
        </form>
    </div>
</body>
</html>
