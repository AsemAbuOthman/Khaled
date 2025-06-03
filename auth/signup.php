<?php
ob_start();
session_start();
include __DIR__ . '/../database/db.php';


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $FirstName = $_POST['FirstName'];
    $MidName = $_POST['MidName'];
    $LastName = $_POST['LastName'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $Role = $_POST['Role'];
    $CityId = $_POST['city_id'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Ensure unique email
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['signup_error'] = "هذا البريد الإلكتروني مستخدم بالفعل.";
        header("Location: signup_page.php"); 
        exit();
    }

    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (first_name, second_name, last_name, email, phone, role, password, city_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $FirstName, $MidName, $LastName, $email, $phone, $Role, $password, $CityId);

    if ($stmt->execute()) {
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['role'] = $Role;
        header("Location: ../main/home.php");
        exit();
    } else {
        $_SESSION['signup_error'] = "حدث خطأ أثناء التسجيل: " . $stmt->error;
        header("Location: signup_page.php"); 
        exit();
    }

    $stmt->close();
}

// Don't close connection here - needed for HTML rendering
// $conn->close();

ob_end_flush();
?>