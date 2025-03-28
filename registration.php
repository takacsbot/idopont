<?php
require_once './php_backend/db_config.php';
require_once './php_backend/functions.php';
require_once 'vendor/autoload.php';
$message = '';

function getGoogleClient()
{
    $client = new Google_Client();
    $client->setClientId('524001933732-mm6de3bm2bqmg57rjg5ar12t2dpaiths.apps.googleusercontent.com');
    $client->setClientSecret('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');
    $client->setRedirectUri('http://localhost:8000/auth/google/callback.php');
    $client->addScope('email');
    $client->addScope('profile');
    return $client;
}


function handleGoogleCallback($code)
{
    global $pdo;
    
    $client = getGoogleClient();
    $token = $client->fetchAccessTokenWithAuthCode($code);
    $client->setAccessToken($token);
    
    $oauth2 = new Google_Service_Oauth2($client);
    $userInfo = $oauth2->userinfo->get();

    $email = $userInfo->email;
    $name = $userInfo->name;
    $google_id = $userInfo->id;

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR oauth_id = ?");
    $stmt->execute([$email, $google_id]);
    $user = $stmt->fetch();

    if (!$user) {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, oauth_provider, oauth_id) VALUES (?, ?, 'google', ?)");
        $stmt->execute([$name, $email, $google_id]);
        $user_id = $pdo->lastInsertId();
    } else {
        $user_id = $user['id'];
    }

    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $name;
    $_SESSION['email'] = $email;

    $token = bin2hex(random_bytes(32));
    $expires = time() + 86400;
    
    $stmt = $pdo->prepare("INSERT INTO auth_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $token, date('Y-m-d H:i:s', $expires)]);
    
    setcookie('auth_token', $token, [
        'expires' => $expires,
        'path' => '/',
        'httponly' => true,
        'secure' => true,
        'samesite' => 'Lax'
    ]);
    
    $_SESSION['auth_token'] = $token;

    header('Location: index.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    if (empty($username) || empty($email) || empty($password)) {
        $message = "A mezők kitöltése kötelező.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Helytelen e-mail formátum.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);

        if ($stmt->rowCount() > 0) {
            $message = "Ez a felhasználó név vagy e-mail már használatban van.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");

            try {
                $stmt->execute([$username, $email, $hashed_password]);
                sendEmail($email, 'Regisztráció', 'Sikeres regisztráció!');
                $message = "Regisztráció sikeres!";
                
            } catch (PDOException $e) {
                $message = "Regisztráció sikertelen: " . $e->getMessage();
            }
        }
    }
}

if (isset($_GET['code'])) {
    handleGoogleCallback($_GET['code']);
}
?>

<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regisztráció - Firestarter Akadémia</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />
    <link rel="stylesheet" href="css/registration.css" />

</head>

<body>

    <button class="theme-switch" onclick="toggleTheme()">
        <span class="mode-text">☀️ Light Mode</span>
    </button>

    <div class="background-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="container" data-aos="fade-up">
        <div class="form-box">
            <h2>Regisztráció</h2>
            <?php
            if (!empty($message)) {
                echo "<p style='color: " . (strpos($message, 'successful') !== false ? 'green' : 'red') . ";'>$message</p>";
            }
            ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="input-group">
                    <input type="text" name="username" placeholder=" " required>
                    <label for="username">Felhasználónév</label>
                </div>
                <div class="input-group">
                    <input type="email" name="email" placeholder=" " required>
                    <label for="email">Email cím</label>
                </div>
                <div class="input-group">
                    <input type="password" name="password" placeholder=" " required>
                    <label for="password">Jelszó</label>
                </div>
                <button type="submit" class="button">Regisztráció</button>
            </form>

            <div class="social-login">
                <p>Vagy regisztrálj</p>
                <div class="social-icons">
                    <div class="social-icon google">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="welcome-box">
            <div class="welcome-text">
                <h2>Üdvözöljük!</h2>
                <p>Ha már van fiókja, jelentkezzen be és fedezze fel szolgáltatásainkat!</p>
                <a href="../login.php" class="login-link">Bejelentkezés</a>
            </div>
        </div>
    </div>

    <script>
        AOS.init({
            duration: 1000,
            once: true
        });
        document.querySelector('.social-icon.google').addEventListener('click', function() {
            window.location.href = '<?php echo getGoogleClient()->createAuthUrl(); ?>';
        });

        function toggleTheme() {
            const body = document.body;
            const button = document.querySelector('.theme-switch');
            const modeText = button.querySelector('.mode-text');

            if (body.getAttribute('data-theme') === 'dark') {
                body.removeAttribute('data-theme');
                modeText.textContent = '☀️';
                localStorage.setItem('theme', 'light');
            } else {
                body.setAttribute('data-theme', 'dark');
                modeText.textContent = '🌙';
                localStorage.setItem('theme', 'dark');
            }
        }


        window.addEventListener('DOMContentLoaded', () => {
            const savedTheme = localStorage.getItem('theme');
            const button = document.querySelector('.theme-switch');
            const modeText = button.querySelector('.mode-text');

            if (savedTheme === 'dark') {
                document.body.setAttribute('data-theme', 'dark');
                modeText.textContent = '🌙';
            }
        });
    </script>
</body>

</html>