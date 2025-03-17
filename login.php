<?php
session_start();

$host = 'localhost';
$dbname = 'timetable_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $message = "A mezők kitöltése kötelező.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            $token = bin2hex(random_bytes(32));
            $expires = time() + 86400; 
            
            $stmt = $pdo->prepare("INSERT INTO auth_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$user['id'], $token, date('Y-m-d H:i:s', $expires)]);
            
            setcookie('auth_token', $token, [
                'expires' => $expires,
                'path' => '/',
                'httponly' => true,
                'secure' => true, 
                'samesite' => 'Lax' 
            ]);
            
            $_SESSION['auth_token'] = $token;
            
            header("Location: index.php");
            exit();
        } else {
            $message = "Helytelen e-mail vagy jelszó.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bejelentkezés - Firestarter Akadémia</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />
    <link rel="stylesheet" href="css/login.css"/>
</head>
<body>
    <button class="theme-switch" onclick="toggleTheme()">
        <span class="mode-text">☀️</span>
    </button>

    <div class="background-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="container" data-aos="fade-up">
        <h2>Bejelentkezés</h2>
        <?php
        if (!empty($message)) {
            echo "<p style='color: red;'>$message</p>";
        }
        ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="input-group">
                <input type="email" name="email" placeholder=" " required>
                <label for="email">Email cím</label>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder=" " required>
                <label for="password">Jelszó</label>
            </div>
            <div class="forgot-password">
                <a href="../elfelejtett_jelszo.php">Elfelejtett jelszó?</a>
            </div>
            <button type="submit" class="button">Bejelentkezés</button>
        </form>

        <div class="social-login">
            <p>Vagy jelentkezz be</p>
            <div class="social-icons">
                <div class="social-icon google" onclick="handleGoogleLogin()">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="register-link">
            <p>Még nincs fiókod? <a href="../registration.php">Regisztrálj most!</a></p>
        </div>
    </div>

    <script>
        AOS.init({
            duration: 1000,
            once: true
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

        document.querySelectorAll('.social-icon').forEach(icon => {
            icon.addEventListener('mouseover', function() {
                this.style.transform = 'translateY(-3px)';
            });
            
            icon.addEventListener('mouseout', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        function handleGoogleLogin() {
            window.location.href = '<?php 
                require_once 'vendor/autoload.php';
                
                $client = new Google_Client();
                $client->setClientId('524001933732-mm6de3bm2bqmg57rjg5ar12t2dpaiths.apps.googleusercontent.com');
                $client->setClientSecret('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');
                $client->setRedirectUri('http://localhost:8000/auth/google/callback.php');
                $client->addScope('email');
                $client->addScope('profile');
                
                echo $client->createAuthUrl();
            ?>';
        }
    </script>
</body>
</html>
