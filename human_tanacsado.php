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

function isLoggedIn($pdo) {
    $token = $_COOKIE['auth_token'] ?? $_SESSION['auth_token'] ?? null;
    
    if ($token) {
        $stmt = $pdo->prepare("SELECT u.* FROM users u 
                               JOIN auth_tokens a ON u.id = a.user_id 
                               WHERE a.token = ? AND a.expires_at > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['auth_token'] = $token;
            return true;
        }
    }
    return false;
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Firestarter Akadémia</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        :root {
            --primary: #FF6B6B;
            --secondary: #4ECDC4;
            --dark: #2D3436;
            --light: #F7F7F7;
            --gradient: linear-gradient(135deg, #FF6B6B, #FFA07A);
            

            --bg-color: #F7F7F7;
            --text-color: #2D3436;
            --card-bg: white;
            --header-bg: rgba(255, 255, 255, 0.95);
            --testimonial-bg: rgba(255, 255, 255, 0.05);
            --nav-link-color: #2D3436;
            --card-shadow: rgba(0, 0, 0, 0.1);
            --testimonials-bg: #2D3436;
            --hero-bg: linear-gradient(135deg, #F6F6F6 0%, #FFFFFF 100%);
            --footer-bg: #F7F7F7;
            --footer-text: #2D3436;
        }

        [data-theme="dark"] {
            --bg-color: #1a1a1a;
            --text-color: #ffffff;
            --card-bg: #2d2d2d;
            --header-bg: rgba(45, 45, 45, 0.95);
            --testimonial-bg: rgba(255, 255, 255, 0.1);
            --nav-link-color: #ffffff;
            --card-shadow: rgba(0, 0, 0, 0.3);
            --testimonials-bg: #000000;
            --hero-bg: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            --footer-bg: #2d2d2d;
            --footer-text: #ffffff;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            line-height: 1.6;
            transition: all 0.3s ease;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
        }

        /* Theme switch gomb */
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

        header {
            position: fixed;
            width: 100%;
            z-index: 1000;
            transition: background 0.3s ease;
        }

        header.scrolled {
            background: var(--header-bg);
            box-shadow: 0 2px 20px var(--card-shadow);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 5%;
            max-width: 1400px;
            margin: 0 auto;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            transition: transform 0.3s ease;
        }

        .logo:hover {
            transform: scale(1.05);
        }

        nav {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        nav a {
            color: var(--nav-link-color);
            text-decoration: none;
            font-weight: 500;
            position: relative;
            padding: 0.5rem 0;
            transition: color 0.3s ease;
        }

        nav a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--gradient);
            transition: width 0.3s ease;
        }

        nav a:hover::after {
            width: 100%;
        }

        .login-button {
            background: var(--gradient);
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
        }

        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 5rem 5%;
            background: var(--hero-bg);
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: var(--gradient);
            opacity: 0.1;
            border-radius: 50%;
            transform: scale(1.5);
        }

        .hero-content {
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
            position: relative;
            z-index: 1;
        }

        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            line-height: 1.2;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            color: var(--text-color);
        }

        .services {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            padding: 5rem 5%;
            max-width: 1400px;
            margin: 0 auto;
        }

        .service-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--gradient);
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px var(--card-shadow);
        }

        .service-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }

        .service-card h3 {
            color: var(--text-color);
            margin-bottom: 1rem;
        }

        .service-card p {
            color: var(--text-color);
            margin-bottom: 1.5rem;
        }

        .testimonials {
            background: var(--testimonials-bg);
            color: white;
            padding: 5rem 5%;
        }

        .testimonial-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .testimonial-card {
            background: var(--testimonial-bg);
            padding: 2rem;
            border-radius: 20px;
            position: relative;
        }

        .testimonial-card::before {
            content: '"';
            position: absolute;
            top: -20px;
            left: 20px;
            font-size: 5rem;
            color: var(--primary);
            opacity: 0.3;
        }

        footer {
            background: var(--footer-bg);
            color: var(--footer-text);
            text-align: center;
            padding: 2rem;
            margin-top: 2rem;
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }

            nav {
                flex-direction: column;
                gap: 1rem;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .theme-switch {
                top: 10px;
                right: 10px;
                padding: 8px 15px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <button class="theme-switch" onclick="toggleTheme()">
        <span class="mode-text">☀️ Light Mode</span>
    </button>

    <header>
        <div class="header-content">
            <div class="logo">Firestarter Akadémia</div>
            <nav>
                <a href="#kezdolap">Kezdőlap</a>
                <a href="#jelentkezes">Jelentkezés</a>
                <a href="#kepzesekrol">Képzésekről</a>
                <a href="#rolunk">Rólunk</a>
                <a href="#login" class="login-button">Belépés</a>
            </nav>
        </div>
    </header>

    <section class="hero">
        <div class="hero-content" data-aos="fade-up">
            <h1>Alakítsd át az életed velünk</h1>
            <p>Szakértő segítség az önfejlesztésben, karrierépítésben és kapcsolataid fejlesztésében</p>
            <div class="contact-info">
                <p>Kapcsolat: +36 70 631 3311</p>
            </div>
        </div>
    </section>

    <section class="services">
        <div class="service-card" data-aos="fade-up" data-aos-delay="100">
            <img src="life-coaching.jpg" alt="Life Coaching">
            <h3>LIFE COACHING</h3>
            <p>Fedezd fel önmagad és valósítsd meg céljaidat szakértő támogatással</p>
            <a href="#" class="login-button">Részletek</a>
        </div>

        <div class="service-card" data-aos="fade-up" data-aos-delay="200">
            <img src="business-coaching.jpg" alt="Business Coaching">
            <h3>BUSINESS COACHING</h3>
            <p>Fejleszd vezetői készségeidet és vidd sikerre vállalkozásod</p>
            <a href="#" class="login-button">Részletek</a>
        </div>

        <div class="service-card" data-aos="fade-up" data-aos-delay="300">
            <img src="mediation.jpg" alt="Mediáció">
            <h3>MEDIÁCIÓ</h3>
            <p>Oldd meg konfliktusaidat professzionális segítséggel</p>
            <a href="#" class="login-button">Részletek</a>
        </div>

        <div class="service-card" data-aos="fade-up" data-aos-delay="400">
            <img src="training-1.jpg" alt="Tréningek">
            <h3>TRÉNINGEK</h3>
            <p>Csoportos fejlődési lehetőségek inspiráló környezetben</p>
            <a href="#" class="login-button">Részletek</a>
        </div>
    </section>

    <section class="testimonials">
        <div class="testimonial-grid">
            <div class="testimonial-card" data-aos="fade-right">
                <p>„A coaching foglalkozások valódi megvilágosodást jelentettek számomra! Az életem új irányt vett, és sokkal magabiztosabb vagyok."</p>
                <p>– Kata, Life Coaching ügyfél</p>
            </div>
            <div class="testimonial-card" data-aos="fade-left">
                <p>„A Firestarter Akadémia üzleti coaching programja lehetővé tette, hogy fejlesszem vezetői készségeimet és megvalósítsam karrierálmaimat."</p>
                <p>– Péter, Business Coaching ügyfél</p>
            </div>
        </div>
    </section>

    <footer>
        <p>&copy; 2024 Firestarter Akadémia - Minden jog fenntartva</p>
    </footer>



    <script>
        AOS.init({
            duration: 1000,
            once: true
        });

        window.addEventListener('scroll', () => {
            const header = document.querySelector('header');
            if (window.scrollY > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
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