<?php
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize input
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $iin = trim($_POST['iin']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $address = trim($_POST['address']);

    // Basic Validation
    if ($password !== $confirm_password) {
        die("Error: Passwords do not match.");
    }

    // Hash Password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Prepare SQL statement
        $sql = "INSERT INTO customers (full_name, email, iin_number, phone_number, password_hash, address) 
                VALUES (:full_name, :email, :iin, :phone, :password_hash, :address)";

        $stmt = $pdo->prepare($sql);

        // Execute
        $stmt->execute([
            ':full_name' => $full_name,
            ':email' => $email,
            ':iin' => $iin,
            ':phone' => $phone,
            ':password_hash' => $password_hash,
            ':address' => $address
        ]);

        // Redirect to login page on success
        header("Location: login.html?status=success");
        exit();

    } catch (PDOException $e) {
        // Handle duplicate entry errors (e.g. email or IIN already exists)
        if ($e->getCode() == 23000) {
            die("Error: Email or IIN already exists.");
        }
        die("Error: " . $e->getMessage());
    }
}
?>