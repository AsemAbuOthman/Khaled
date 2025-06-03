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
    <title>إضافة أداة</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(to bottom, #eaf6ff, #ffffff);
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 700px;
            margin: 40px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #0077cc;
            margin-bottom: 30px;
            font-size: 1.8em;
        }

        label {
            display: block;
            font-size: 1.1em;
            margin-bottom: 10px;
            color: #444;
        }

        input, select, textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1em;
            transition: border-color 0.3s ease;
        }

        input:focus, select:focus, textarea:focus {
            border-color: #0077cc;
            outline: none;
        }

        textarea {
            height: 100px;
            resize: vertical;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #0077cc;
            color: white;
            font-size: 1.2em;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #005fa3;
        }

        .message {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
            font-size: 1.2em;
        }

        .success {
            color: #2e7d32;
        }

        .error {
            color: #c62828;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            h2 {
                font-size: 1.5em;
            }
        }

    </style>
</head>
<body>

<div class="container">
    <h2>إضافة أداة جديدة</h2>

    <?php if ($message != ""): ?>
        <p class="message <?= strpos($message, '✅') !== false ? 'success' : 'error' ?>"> <?= $message ?> </p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label for="name">اسم الأداة:</label>
        <input type="text" name="name" required>

        <label for="description">الوصف:</label>
        <textarea name="description" required></textarea>

        <label for="status">الحالة:</label>
        <select name="status" required>
            <option value="new">جديدة</option>
            <option value="good">جيدة جداً</option>
            <option value="used">مستخدمة قليلاً</option>
        </select>

        <label for="image">صورة الأداة (اختياري):</label>
        <input type="file" name="image" accept="image/*">

        <button type="submit">إضافة الأداة</button>
    </form>
</div>

</body>
</html>
