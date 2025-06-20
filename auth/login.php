<?php
ob_start();
session_start();
include __DIR__ . '/../database/db.php'; // Adjust path as needed

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // ✅ Include 'email' in SELECT fields
    $stmt = $conn->prepare("SELECT user_id, first_name, second_name, last_name, email, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result(); 

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, hash: $user['password'])) {

            // ✅ Store all user data in session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['second_name'] = $user['second_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['email'] = $user['email']; // Now this works
            $_SESSION['role'] = $user['role'];

            // ✅ Redirect based on role
            switch ($user['role']) {
                case 'admin':
                    header("Location: ../main/dashboard.php"); exit();
                case 'lender':
                    header("Location: ../main/lender_dashboard.php"); exit();
                case 'user':
                    header("Location: ../main/home.php"); exit();
            }

        } else {
            $_SESSION['login_error'] = "كلمة المرور غير صحيحة.";
            header("Location: login_page.php"); exit();
        }

    } else {
        $_SESSION['login_error'] = "البريد الإلكتروني غير مسجل.";
        header("Location: login_page.php"); exit();
    }

    $stmt->close();
    $conn->close();
}
ob_end_flush();
?>
