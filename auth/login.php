<?php
ob_start();
session_start();
include __DIR__ . '/../database/db.php'; // Adjust based on directory structure

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare SQL query to retrieve user information based on email
    $stmt = $conn->prepare("SELECT user_id, first_name, second_name, last_name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);  // Bind email parameter
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if a user was found with the provided email
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Check if the provided password matches the hashed password stored in the database
        if (password_verify($password, $user['password'])) {
            // Store user information in the session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['second_name'] = $user['second_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect based on the user's role
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
        // Invalid email
        $_SESSION['login_error'] = "البريد الإلكتروني غير مسجل.";
        header("Location: login_page.php"); exit();
    }

    // Close the statement and database connection
    $stmt->close();
    $conn->close();
}
ob_end_flush();

?>
