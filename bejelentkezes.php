
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
            
            header("Location: idopont.php");
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
    <style>
        :root {
            --primary: #FF6B6B;
            --secondary: #4ECDC4;
            --dark: #2D3436;
            --light: #F7F7F7;
            --gradient: linear-gradient(135deg, #FF6B6B, #FFA07A);
            
            --bg-color: #F6F6F6;
            --container-bg: rgba(255, 255, 255, 0.95);
            --input-bg: #f5f5f5;
            --text-color: #2D3436;
            --input-label: #666;
            --social-icon-bg: #f5f5f5;
            --social-text: #666;
        }

        [data-theme="dark"] {
            --bg-color: #1a1a1a;
            --container-bg: rgba(45, 45, 45, 0.95);
            --input-bg: #333;
            --text-color: #ffffff;
            --input-label: #aaa;
            --social-icon-bg: #333;
            --social-text: #fff;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--bg-color) 0%, var(--bg-color) 100%);
            position: relative;
            overflow-x: hidden;
            color: var(--text-color);
            transition: all 0.3s ease;
        }

        .background-shapes {
            position: fixed;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            background: var(--gradient);
            opacity: 0.1;
            animation: float 20s infinite ease-in-out;
        }

        .shape:nth-child(1) {
            width: 500px;
            height: 500px;
            top: -250px;
            right: -250px;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 400px;
            height: 400px;
            bottom: -200px;
            left: -200px;
            animation-delay: -5s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) scale(1);
            }
            50% {
                transform: translateY(-20px) scale(1.05);
            }
        }

        .container {
            width: 400px;
            background: var(--container-bg);
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 1;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            transition: all 0.3s ease;
            padding: 40px;
        }

        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--gradient);
        }

        h2 {
            margin-bottom: 30px;
            font-weight: 700;
            font-size: 24px;
            position: relative;
            color: var(--text-color);
            text-align: center;
        }

        .input-group {
            position: relative;
            margin-bottom: 25px;
        }

        .input-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid transparent;
            border-radius: 12px;
            outline: none;
            background-color: var(--input-bg);
            color: var(--text-color);
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .input-group input:focus {
            border-color: var(--primary);
            background-color: var(--container-bg);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.1);
        }

        .input-group label {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            font-size: 14px;
            color: var(--input-label);
            transition: all 0.3s ease;
            pointer-events: none;
            padding: 0 5px;
        }

        .input-group input:focus ~ label,
        .input-group input:not(:placeholder-shown) ~ label {
            top: 0;
            left: 10px;
            transform: translateY(-50%);
            font-size: 12px;
            background: var(--container-bg);
            color: var(--primary);
            font-weight: 500;
        }

        .forgot-password {
            text-align: right;
            margin-bottom: 20px;
        }

        .forgot-password a {
            color: var(--primary);
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        .button {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 12px;
            background: var(--gradient);
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
        }

        .social-login {
            margin-top: 30px;
            text-align: center;
        }

        .social-login p {
            color: var(--social-text);
            font-size: 14px;
            margin-bottom: 15px;
        }

        .social-icons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .social-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--social-icon-bg);
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 10px;
        }

        .social-icon:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .social-icon svg {
            width: 24px;
            height: 24px;
        }

        .social-icon.google {
            background: white;
        }

        .social-icon.facebook {
            background: #1877f2;
        }

        .social-icon.apple {
            background: #000;
        }

        .social-icon.facebook svg,
        .social-icon.apple svg {
            fill: white;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: var(--text-color);
        }

        .register-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        .theme-switch {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: var(--gradient);
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .theme-switch:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
        }

        @media (max-width: 768px) {
            .container {
                width: 90%;
                margin: 20px;
            }
        }
    </style>
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
                <a href="#">Elfelejtett jelszó?</a>
            </div>
            <button type="submit" class="button">Bejelentkezés</button>
        </form>

        <div class="social-login">
            <p>Vagy jelentkezz be</p>
            <div class="social-icons">
                <div class="social-icon google">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                </div>
                <div class="social-icon facebook">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                </div>
                <div class="social-icon apple">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
                        <path d="M318.7 268.7c-.2-36.7 16.4-64.4 50-84.8-18.8-26.9-47.2-41.7-84.7-44.6-35.5-2.8-74.3 20.7-88.5 20.7-15 0-49.4-19.7-76.4-19.7C63.3 141.2 4 184.8 4 273.5q0 39.3 14.4 81.2c12.8 36.7 59 126.7 107.2 125.2 25.2-.6 43-17.9 75.8-17.9 31.8 0 48.3 17.9 76.4 17.9 48.6-.7 90.4-82.5 102.6-119.3-65.2-30.7-61.7-90-61.7-91.9zm-56.6-164.2c27.3-32.4 24.8-61.9 24-72.5-24.1 1.4-52 16.4-67.9 34.9-17.5 19.8-27.8 44.3-25.6 71.9 26.1 2 49.9-11.4 69.5-34.3z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="register-link">
            <p>Még nincs fiókod? <a href="../regisztracio.php">Regisztrálj most!</a></p>
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
        


    </script>
</body>
</html>
