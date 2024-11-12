<?php
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
                $message = "Regisztráció sikeres!";
            } catch(PDOException $e) {
                $message = "Regisztráció sikertelen: " . $e->getMessage();
            }
        }
    }
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
            display: flex;
            width: 900px;
            background: var(--container-bg);
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 1;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .form-box {
            width: 60%;
            padding: 40px;
            position: relative;
        }

        .form-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: var(--gradient);
        }

        .welcome-box {
            width: 40%;
            padding: 40px;
            background: var(--gradient);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .welcome-box::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 50%);
            top: -50%;
            left: -50%;
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        h2 {
            margin-bottom: 30px;
            font-weight: 700;
            font-size: 24px;
            position: relative;
            color: var(--text-color);
        }

        .welcome-box h2 {
            color: white;
            margin-bottom: 20px;
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

        .button::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, transparent 60%);
            transform: translate(-50%, -50%) scale(0);
            transition: transform 0.5s ease;
        }

        .button:hover::after {
            transform: translate(-50%, -50%) scale(2);
        }

        .welcome-text {
            position: relative;
            z-index: 1;
        }

        .welcome-text p {
            margin-bottom: 20px;
            font-size: 16px;
            line-height: 1.6;
        }

        .login-link {
            color: white;
            text-decoration: none;
            font-weight: 600;
            padding: 10px 25px;
            border: 2px solid white;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .login-link:hover {
            background: white;
            color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
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

        .social-icon svg {
            width: 24px;
            height: 24px;
            transition: all 0.3s ease;
        }

        .social-icon:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .social-icon.google:hover {
            background: #fff;
            box-shadow: 0 5px 15px rgba(66, 133, 244, 0.3);
        }

        .social-icon.facebook:hover {
            background: #fff;
            box-shadow: 0 5px 15px rgba(59, 89, 152, 0.3);
        }

        .social-icon.apple:hover {
            background: #fff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
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
                flex-direction: column;
            }
            
            .form-box, .welcome-box {
                width: 100%;
            }

            .form-box::before {
                width: 100%;
                height: 5px;
            }
        }
    </style>
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
            <form action="#" autocomplete="off">
                <div class="input-group">
                    <input type="text" id="username" placeholder=" " required>
                    <label for="username">Felhasználónév</label>
                </div>
                <div class="input-group">
                    <input type="email" id="email" placeholder=" " required>
                    <label for="email">Email cím</label>
                </div>
                <div class="input-group">
                    <input type="password" id="password" placeholder=" " required>
                    <label for="password">Jelszó</label>
                </div>
                <button type="submit" class="button">Regisztráció</button>
            </form>

            <div class="social-login">
                <p>Vagy regisztrálj</p>
                <div class="social-icons">
                    <div class="social-icon google">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M21.8055 10.0415H21V10H12V14H17.6515C16.827 16.3285 14.6115 18 12 18C8.6865 18 6 15.3135 6 12C6 8.6865 8.6865 6 12 6C13.5295 6 14.921 6.577 15.9805 7.5195L18.809 4.691C17.023 3.0265 14.634 2 12 2C6.4775 2 2 6.4775 2 12C2 17.5225 6.4775 22 12 22C17.5225 22 22 17.5225 22 12C22 11.3295 21.931 10.675 21.8055 10.0415Z" fill="#FFC107"/>
                            <path d="M3.15295 7.3455L6.43845 9.755C7.32745 7.554 9.48045 6 12 6C13.5295 6 14.921 6.577 15.9805 7.5195L18.809 4.691C17.023 3.0265 14.634 2 12 2C8.15895 2 4.82795 4.1685 3.15295 7.3455Z" fill="#FF3D00"/>
                            <path d="M12 22C14.583 22 16.93 21.0115 18.7045 19.404L15.6095 16.785C14.5717 17.5742 13.3037 18.0011 12 18C9.39903 18 7.19053 16.3415 6.35853 14.027L3.09753 16.5395C4.75253 19.778 8.11353 22 12 22Z" fill="#4CAF50"/>
                            <path d="M21.8055 10.0415H21V10H12V14H17.6515C17.2571 15.1082 16.5467 16.0766 15.608 16.7855L15.6095 16.785L18.7045 19.404C18.4855 19.6025 22 17 22 12C22 11.3295 21.931 10.675 21.8055 10.0415Z" fill="#1976D2"/>
                        </svg>
                    </div>
                    <div class="social-icon facebook">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 12.05C20 7.60001 16.4 4.00001 12 4.00001C7.6 4.00001 4 7.60001 4 12.05C4 16.1 6.9 19.4 10.7 20V14.9H8.7V12.05H10.7V9.80001C10.7 7.30001 11.9 6.20001 14.1 6.20001C15.2 6.20001 16.3 6.40001 16.3 6.40001V8.30001H15C13.7 8.30001 13.3 9.00001 13.3 9.70001V12.05H16.2L15.7 14.9H13.3V20C17.1 19.4 20 16.1 20 12.05Z" fill="#1877F2"/>
                        </svg>
                    </div>
                    <div class="social-icon apple">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M14.94 5.19C15.88 4.03 16.49 2.57 16.33 1C15.03 1.09 13.43 1.95 12.46 3.11C11.57 4.16 10.85 5.63 11.04 7.2C12.48 7.27 14 6.42 14.94 5.19Z" fill="black"/>
                            <path d="M20.3 17.89C20.96 16.82 21.21 16.29 21.83 14.96C19.8 14.09 19.09 11.46 21.11 10.12C20.12 8.89 18.81 8.33 17.5 8.33C16.24 8.33 15.45 8.89 14.43 8.89C13.35 8.89 12.42 8.33 11.24 8.33C9.59 8.33 7.79 9.38 6.79 11.2C5.31 13.89 5.64 19.14 8.16 23C9.06 24.24 10.2 25.63 11.67 25.63C13.06 25.63 13.57 24.82 15.2 24.82C16.83 24.82 17.29 25.63 18.75 25.63C20.22 25.63 21.28 24.36 22.18 23.12C22.8 22.23 23.05 21.8 23.68 20.45C21.48 19.45 20.3 17.89 20.3 17.89Z" fill="black"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="welcome-box">
            <div class="welcome-text">
                <h2>Üdvözöljük!</h2>
                <p>Ha már van fiókja, jelentkezzen be és fedezze fel szolgáltatásainkat!</p>
                <a href="#" class="login-link">Bejelentkezés</a>
            </div>
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
                modeText.textContent = '☀️ Light Mode';
                localStorage.setItem('theme', 'light');
            } else {
                body.setAttribute('data-theme', 'dark');
                modeText.textContent = '🌙 Dark Mode';
                localStorage.setItem('theme', 'dark');
            }
        }


        window.addEventListener('DOMContentLoaded', () => {
            const savedTheme = localStorage.getItem('theme');
            const button = document.querySelector('.theme-switch');
            const modeText = button.querySelector('.mode-text');
            
            if (savedTheme === 'dark') {
                document.body.setAttribute('data-theme', 'dark');
                modeText.textContent = '🌙 Dark Mode';
            }
        });
    </script>
</body>
</html>