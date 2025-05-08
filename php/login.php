<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $errors = [];

    if (empty($username) || empty($password)) {
        $errors[] = "Both username and password are required";
    }

    if (empty($errors)) {
        $stmt = $DBConnect->prepare("SELECT * FROM Users WHERE Username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['PasswordHash'])) {
                $stmt_update = $DBConnect->prepare("UPDATE Users SET LastConnection = CURRENT_TIMESTAMP WHERE UserID = ?");
                $stmt_update->bind_param("i", $user['UserID']);
                $stmt_update->execute();

                $_SESSION['user_id'] = $user['UserID'];
                $_SESSION['username'] = $user['Username'];
                $_SESSION['role'] = $user['Role'];

                header("Location: ../index.php");
                exit();
            } else {
                $errors[] = "Invalid password";
            }
        } else {
            $errors[] = "User not found";
        }
    }

    $_SESSION['login_errors'] = $errors;
    $_SESSION['login_old'] = ['username' => $username];
    header("Location: ../login.php");
    exit();
}
?>
