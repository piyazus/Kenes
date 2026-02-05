<?php
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $iin = trim($_POST['iin']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $address = trim($_POST['address']);


    if ($password !== $confirm_password) {
        die("Error: Passwords do not match.");
    }


    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    try {

        $sql = "INSERT INTO customers (full_name, email, iin_number, phone_number, password_hash, address) 
                VALUES (:full_name, :email, :iin, :phone, :password_hash, :address)";

        $stmt = $pdo->prepare($sql);


        $stmt->execute([
            ':full_name' => $full_name,
            ':email' => $email,
            ':iin' => $iin,
            ':phone' => $phone,
            ':password_hash' => $password_hash,
            ':address' => $address
        ]);


        header("Location: login.html?status=success");
        exit();

    } catch (PDOException $e) {

        if ($e->getCode() == 23000) {
            die("Error: Email or IIN already exists.");
        }
        die("Error: " . $e->getMessage());
    }
}
?>