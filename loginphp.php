<?php
session_start();
require_once './php_backend/db_config.php';
require_once './php_backend/functions.php';


$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $message = "A mezők kitöltése kötelező.";
    } else {
        $user = getUserByEmail($pdo, $email);

        if ($user && password_verify($password, $user['password'])) {
            $tokenData = createAuthToken($pdo, $user['id']);
            setAuthSessionAndCookie($user, $tokenData);
            
            header("Location: index.php");
            exit();
        } else {
            $message = "Helytelen e-mail vagy jelszó.";
        }
    }
}
