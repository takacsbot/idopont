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
            
            /* Dark mode változók */
            --bg-color: #F6F6F6;
            --container-bg: rgba(255, 255, 255, 0.95);
            --input-bg: #f5f5f5;
            --text-color: #2D3436;
            --input-label: #666;
        }

        [data-theme="dark"] {
            --bg-color: #1a1a1a;
            --container-bg: rgba(45, 45, 45, 0.95);
            --input-bg: #333;
            --text-color: #ffffff;
            --input-label: #aaa;
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
            overflow: hidden;
            color: var(--text-color);
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

        .input-group label {
            color: var(--input-label);
        }

        /* Dark mode kapcsoló stílusa */
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
        :root {
            --primary: #FF6B6B;
            --secondary: #4ECDC4;
            --dark: #2D3436;
            --light: #F7F7F7;
            --gradient: linear-gradient(135deg, #FF6B6B, #FFA07A);
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
            background: linear-gradient(135deg, #F6F6F6 0%, #FFFFFF 100%);
            position: relative;
            overflow: hidden;
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
            background: rgba(255, 255, 255, 0.95);
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 1;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
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
        }

        .form-box h2 {
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
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
            background-color: #f5f5f5;
            color: var(--dark);
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .input-group input:focus {
            border-color: var(--primary);
            background-color: white;
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.1);
        }

        .input-group label {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            font-size: 14px;
            color: #666;
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
            background: white;
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
            color: #666;
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
            background: #f5f5f5;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .social-icon:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
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
    <!-- Dark mode kapcsoló -->
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
                    <div class="social-icon">
                        <img src="/api/placeholder/24/24" alt="Google">
                    </div>
                    <div class="social-icon">
                        <img src="/api/placeholder/24/24" alt="Facebook">
                    </div>
                    <div class="social-icon">
                        <img src="/api/placeholder/24/24" alt="Apple">
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

        // Dark mode funkcionalitás
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

        // Téma betöltése a localStorage-ból
        document.addEventListener('DOMContentLoaded', () => {
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