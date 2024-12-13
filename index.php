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
    <link rel="stylesheet" href="css/index.css"/>
    
</head>
<body>



    <header>
        <div class="header-content">
            <div class="logo">Firestarter Akadémia</div>

            <nav>
                <a href="./kepzeseink.html">Képzésekről</a>
                <a href="./rolunk.html">Rólunk</a>
                <?php if (!isLoggedIn($pdo)) {
                    echo '<a class="login-button" href="./bejelentkezes.php">Belépés/Regisztráció</a>';
                } else {
                    echo $_SESSION['username'];
                }?>
                <button class="theme-switch" onclick="toggleTheme()">
                    <span class="mode-text">☀️</span>
                </button>
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
            <a href="./kepzeseink.html#life-coaching" class="login-button">Részletek</a>
            <a href="./idopont.php#life-coaching" class="login-button">Jelentkezés</a>
        </div>

        <div class="service-card" data-aos="fade-up" data-aos-delay="200">
            <img src="business-coaching.jpg" alt="Business Coaching">
            <h3>BUSINESS COACHING</h3>
            <p>Fejleszd vezetői készségeidet és vidd sikerre vállalkozásod</p>
            <a href="./kepzeseink.html#business-coaching" class="login-button">Részletek</a>
            <a href="./idopont.php#business-coaching" class="login-button">Jelentkezés</a>
        </div>

        <div class="service-card" data-aos="fade-up" data-aos-delay="300">
            <img src="mediation.jpg" alt="Mediáció">
            <h3>Stresszkezelés és Reziliencia Workshop</h3>
            <p>Oldd meg konfliktusaidat professzionális segítséggel</p>
            <a href="./kepzeseink.html#workshop" class="login-button">Részletek</a>
            <a href="./idopont.php#workshop" class="login-button">Jelentkezés</a>
        </div>

        <div class="service-card" data-aos="fade-up" data-aos-delay="400">
            <img src="training-1.jpg" alt="Tréningek">
            <h3>Karriertervezés és Énmárka Építés</h3>
            <p>Csoportos fejlődési lehetőségek inspiráló környezetben</p>
            <a href="./kepzeseink.html#career" class="login-button">Részletek</a>
            <a href="./idopont.php#career" class="login-button">Jelentkezés</a>
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