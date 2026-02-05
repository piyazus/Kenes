<?php
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize input
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $employee_code = trim($_POST['employee_code']);
    $department = trim($_POST['department']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic Validation
    if ($password !== $confirm_password) {
        die("Error: Passwords do not match.");
    }

    // Hash Password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Prepare SQL statement
        $sql = "INSERT INTO consultants (full_name, email, employee_code, department, password_hash) 
                VALUES (:full_name, :email, :employee_code, :department, :password_hash)";

        $stmt = $pdo->prepare($sql);

        // Execute
        $stmt->execute([
            ':full_name' => $full_name,
            ':email' => $email,
            ':employee_code' => $employee_code,
            ':department' => $department,
            ':password_hash' => $password_hash
        ]);

        // Redirect to login page on success
        header("Location: login.html?status=consultant_success");
        exit();

    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            die("Error: Email or Employee Code already exists.");
        }
        die("Error: " . $e->getMessage());
    }
}
?>