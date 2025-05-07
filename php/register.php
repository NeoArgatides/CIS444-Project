<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm-password']);
    $username = trim($_POST['username'] ?? ''); // Nouveau champ (nécessaire)
    $errors = [];

    // Validation
    if (empty($username)) {
        $errors[] = "Username is required";
    }
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    // Check if email or username already exists
    $stmt = $DBConnect->prepare("SELECT * FROM Users WHERE Email = ? OR Username = ?");
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $errors[] = "Username or email already exists";
    }

    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $DBConnect->prepare("INSERT INTO Users (Username, Email, PasswordHash, Role) VALUES (?, ?, ?, 'User')");
        $stmt->bind_param("sss", $username, $email, $hashed_password);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Registration successful! Please login.";
            header("Location: /login.php");
            exit();
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
    }

    $_SESSION['register_errors'] = $errors;
    $_SESSION['register_old'] = ['email' => $email, 'username' => $username];
    header("Location: /signup.php");
    exit();
}
?>
