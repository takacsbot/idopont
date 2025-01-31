<?php
session_start();

$host = 'localhost';
$dbname = 'timetable_db';
$username = 'root';
$password = '';


try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

function isLoggedIn($pdo)
{
    $token = $_COOKIE['auth_token'] ?? $_SESSION['auth_token'] ?? null;

    if ($token) {
        $stmt = $pdo->prepare("SELECT u.* FROM users u 
                               JOIN auth_tokens a ON u.id = a.user_id 
                               WHERE a.token = ? AND a.expires_at > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['is_admin'] = $user['is_admin'];
            $_SESSION['is_instructor'] = $user['is_instructor'];
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
    <link rel="stylesheet" href="css/index.css" />

</head>

<body>

    <header>
        <div class="header-content">
            <div class="logo">Firestarter Akadémia</div>

            <nav>
                <a href="./kepzeseink.html">Képzésekről</a>
                <a href="./rolunk.html">Rólunk</a>
                <?php if (!isLoggedIn($pdo)) {
                    echo '<a class="login-button" href="./login.php">Belépés/Regisztráció</a>';
                } else {
                    echo '<a href="./profile_page.php">' . $_SESSION['username'] . '</a>';
                } 
                if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
                    echo '<a href="admin.php" class="login-button">Admin Panel</a>';
                }
                if (isset($_SESSION['is_instructor']) && $_SESSION['is_instructor'] == 1) {
                    echo '<a href="foglalas.php" class="login-button">Időpontok kezelése</a>';
                }
                ?>

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
            <img src="./pictures_from_training_courses/life-coaching.jpg" alt="Life Coaching">
            <h3>Life Coaching</h3>
            <p>Fedezd fel önmagad és valósítsd meg céljaidat szakértő támogatással</p>
            <a href="./kepzeseink.html#life-coaching" class="login-button">Részletek</a>
            <a href="./idopont.php#1" class="login-button">Jelentkezés</a>
        </div>

        <div class="service-card" data-aos="fade-up" data-aos-delay="200">
            <img src="./pictures_from_training_courses/business-coaching.jpg" alt="Business Coaching">
            <h3>Business Coaching</h3>
            <p>Fejleszd vezetői készségeidet és vidd sikerre vállalkozásod</p>
            <a href="./kepzeseink.html#business-coaching" class="login-button">Részletek</a>
            <a href="./idopont.php#2" class="login-button">Jelentkezés</a>
        </div>

        <div class="service-card" data-aos="fade-up" data-aos-delay="300">
            <img src="./pictures_from_training_courses/mediation.jpg" alt="Mediáció">
            <h3>Stresszkezelés és Reziliencia Workshop</h3>
            <p>Oldd meg konfliktusaidat professzionális segítséggel</p>
            <a href="./kepzeseink.html#workshop" class="login-button">Részletek</a>
            <a href="./idopont.php#3" class="login-button">Jelentkezés</a>
        </div>

        <div class="service-card" data-aos="fade-up" data-aos-delay="400">
            <img src="./pictures_from_training_courses/training-1.jpg" alt="Tréningek">
            <h3>Karriertervezés és Énmárka Építés</h3>
            <p>Csoportos fejlődési lehetőségek inspiráló környezetben</p>
            <a href="./kepzeseink.html#career" class="login-button">Részletek</a>
            <a href="./idopont.php#5" class="login-button">Jelentkezés</a>
        </div>

        <div class="service-card" data-aos="fade-up" data-aos-delay="500">
            <img src="./pictures_from_training_courses/effective communication and conflict management.jpg" alt="Kommunikáció">
            <h3>Hatékony Kommunikáció és Konfliktuskezelés</h3>
            <p>Sajátítsd el a konstruktív kommunikációs technikákat a jobb kapcsolatokért</p>
            <a href="./kepzeseink.html#communication" class="login-button">Részletek</a>
            <a href="./idopont.php#6" class="login-button">Jelentkezés</a>
        </div>

        <div class="service-card" data-aos="fade-up" data-aos-delay="600">
            <img src="./pictures_from_training_courses/Mindfulness.jpg" alt="Mindfulness">
            <h3>Mindfulness és Produktivitás Program</h3>
            <p>Növeld koncentrációdat és hatékonyságodat tudatos jelenlét gyakorlásával</p>
            <a href="./kepzeseink.html#mindfulness" class="login-button">Részletek</a>
            <a href="./idopont.php#7" class="login-button">Jelentkezés</a>
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

        let lastScroll = 0;
        const header = document.querySelector('header');
        const scrollThreshold = 50;

        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            if (currentScroll === 0) {
                header.classList.remove('hidden');
            }     else if (currentScroll > lastScroll && currentScroll > scrollThreshold) {
                header.classList.add('hidden');
            }

            lastScroll = currentScroll;
        });

        window.addEventListener('scroll', () => {
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