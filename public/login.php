<?php
session_start();
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // 1. Try to Login as Consultant (Staff)
    $stmt = $pdo->prepare("SELECT * FROM consultants WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $consultant = $stmt->fetch();

    if ($consultant && password_verify($password, $consultant['password_hash'])) {
        // Login Success - Consultant
        $_SESSION['user_id'] = $consultant['id'];
        $_SESSION['user_type'] = 'consultant';
        $_SESSION['full_name'] = $consultant['full_name'];

        header("Location: consultant-dashboard.html");
        exit();
    }

    // 2. Try to Login as Customer
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $customer = $stmt->fetch();

    if ($customer && password_verify($password, $customer['password_hash'])) {
        // Login Success - Customer
        $_SESSION['user_id'] = $customer['id'];
        $_SESSION['user_type'] = 'customer';
        $_SESSION['full_name'] = $customer['full_name'];

        header("Location: customer-dashboard.html");
        exit();
    }

    // Login Failed
    header("Location: login.html?error=invalid_credentials");
    exit();
}
?>