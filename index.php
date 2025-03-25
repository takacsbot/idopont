<?php
session_start();
require_once './php_backend/db_config.php';
require_once './php_backend/functions.php';

$user = isLoggedIn($pdo);
$services = getServices($pdo);
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
                <a href="./news.html" id="hirek">Hírek</a>
                <a href="./kepzeseink.php" id="kepzesek">Képzésekről</a>
                <a href="./rolunk.html" id="rolunk">Rólunk</a>
                <?php if (!$user) {
                    echo '<a class="login-button" href="./login.php">Belépés/Regisztráció</a>';
                } else {
                    echo '<a href="./profile_page.php">' . htmlspecialchars($user['username']) . '</a>';
                } 
                if (isInstructor($user)) {
                    echo '<a href="foglalas.php" class="login-button">Időpontok kezelése</a>';
                }
                if (isAdmin($user)) {
                    echo '<a href="admin.php" class="login-button">Admin Panel</a>';
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
        <?php 
        $delay = 100;
        foreach ($services as $service): 
            $imageUrl = "./pictures_from_training_courses/" . $service['name'];
            $anchorId = strtolower(str_replace(' ', '-', $service['name']));
        ?>
            <div class="service-card" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                <img src="<?php echo htmlspecialchars($imageUrl); ?>.jpg" alt="<?php echo htmlspecialchars($service['name']); ?>">
                <h3><?php echo htmlspecialchars($service['name']); ?></h3>
                <p><?php echo htmlspecialchars($service['description']); ?></p>
                <a href="./kepzeseink.php#<?php echo $service['id']; ?>" class="login-button">Részletek</a>
                <a href="./idopont.php#<?php echo $service['id']; ?>" class="login-button">Jelentkezés</a>
            </div>
        <?php 
            $delay += 100;
        endforeach; 
        ?>
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
        <p>&copy; 2024-2025 Firestarter Akadémia - Minden jog fenntartva</p>
    </footer>

    <script>
        document.body.setAttribute('data-theme', 'dark');
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