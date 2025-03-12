<?php
session_start();
require_once '../../vendor/autoload.php';

function getGoogleClient() {
    $client = new Google_Client();
    $client->setClientId('524001933732-mm6de3bm2bqmg57rjg5ar12t2dpaiths.apps.googleusercontent.com');
    $client->setClientSecret('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');
    $client->setRedirectUri('http://localhost:8000/auth/google/callback.php');
    $client->addScope('email');
    $client->addScope('profile');
    return $client;
}

try {
    if (isset($_GET['code'])) {
        $client = getGoogleClient();
        
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        
        if (!isset($token['error'])) {
            $client->setAccessToken($token);
            
            $oauth2 = new Google_Service_Oauth2($client);
            $userInfo = $oauth2->userinfo->get();
            
            $host = 'localhost';
            $dbname = 'timetable_db';
            $username = 'root';
            $password = '';
            
            $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR oauth_id = ?");
            $stmt->execute([$userInfo->email, $userInfo->id]);
            $user = $stmt->fetch();
            
            if (!$user) {
                $stmt = $pdo->prepare("INSERT INTO users (username, email, oauth_provider, oauth_id) VALUES (?, ?, 'google', ?)");
                $stmt->execute([$userInfo->name, $userInfo->email, $userInfo->id]);
                $user_id = $pdo->lastInsertId();
            } else {
                $user_id = $user['id'];
            }
            
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $userInfo->name;
            $_SESSION['email'] = $userInfo->email;
            
            $auth_token = bin2hex(random_bytes(32));
            $expires = time() + 86400;
            
            $stmt = $pdo->prepare("INSERT INTO auth_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $auth_token, date('Y-m-d H:i:s', $expires)]);
            
            setcookie('auth_token', $auth_token, [
                'expires' => $expires,
                'path' => '/',
                'httponly' => true,
                'secure' => true,
                'samesite' => 'Lax'
            ]);
            
            $_SESSION['auth_token'] = $auth_token;
            
            header('Location: /index.php');
            exit();
        }
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    header('Location: /login.php?error=google_auth_failed');
    exit();
}

header('Location: /login.php?error=google_auth_failed');
exit(); 