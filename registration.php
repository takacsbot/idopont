<?php
$host = 'localhost';
$dbname = 'timetable_db';
$username = 'root';
$password = '';
require_once 'functions.php';
require_once 'vendor/autoload.php';

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


try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$message = '';

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
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M21.8055 10.0415H21V10H12V14H17.6515C16.827 16.3285 14.6115 18 12 18C8.6865 18 6 15.3135 6 12C6 8.6865 8.6865 6 12 6C13.5295 6 14.921 6.577 15.9805 7.5195L18.809 4.691C17.023 3.0265 14.634 2 12 2C6.4775 2 2 6.4775 2 12C2 17.5225 6.4775 22 12 22C17.5225 22 22 17.5225 22 12C22 11.3295 21.931 10.675 21.8055 10.0415Z" fill="#FFC107" />
                            <path d="M3.15295 7.3455L6.43845 9.755C7.32745 7.554 9.48045 6 12 6C13.5295 6 14.921 6.577 15.9805 7.5195L18.809 4.691C17.023 3.0265 14.634 2 12 2C8.15895 2 4.82795 4.1685 3.15295 7.3455Z" fill="#FF3D00" />
                            <path d="M12 22C14.583 22 16.93 21.0115 18.7045 19.404L15.6095 16.785C14.5717 17.5742 13.3037 18.0011 12 18C9.39903 18 7.19053 16.3415 6.35853 14.027L3.09753 16.5395C4.75253 19.778 8.11353 22 12 22Z" fill="#4CAF50" />
                            <path d="M21.8055 10.0415H21V10H12V14H17.6515C17.2571 15.1082 16.5467 16.0766 15.608 16.7855L15.6095 16.785L18.7045 19.404C18.4855 19.6025 22 17 22 12C22 11.3295 21.931 10.675 21.8055 10.0415Z" fill="#1976D2" />
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