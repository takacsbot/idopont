<?php
session_start();

require_once 'db_config.php';

require_once 'functions.php';

$services = getServices($pdo);
?>

<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Képzéseink - Firestarter Akadémia</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />
    <link rel="stylesheet" href="css/kepzeseink.css" />

</head>

<body>
    <header>
        <div class="header-content">
            <a href="index.php" class="logo-link">
                <div class="logo">Firestarter Akadémia</div>
            </a>
            <nav>
                <a href="../">Kezdőlap</a>
                <button class="theme-switch" onclick="toggleTheme()">
                    <span class="mode-text">☀️</span>
                </button>
            </nav>
        </div>
    </header>

    <section class="hero" data-aos="fade-up">
        <h1 class="section-title">Képzéseink</h1>
        <p class="program-description">
            A Firestarter Akadémia különféle képzéseket kínál, amelyek célja a személyes és szakmai fejlődés támogatása.
            Akár életvezetési tanácsadásra, akár üzleti fejlődési iránymutatásra van szükséged, nálunk megtalálhatod a
            megfelelő programot.
        </p>

        <div class="training-section">
        <?php
            try {
                $delay = 0;
                
                foreach ($services as $service) {
                    echo '<div class="training-card" data-aos="fade-up" data-aos-delay="' . $delay . '" id="' . htmlspecialchars($service['id']) . '">';
                    echo '<h2 class="training-title">' . htmlspecialchars($service['name']) . '</h2>';
                    echo '<p class="training-info">' . htmlspecialchars($service['description']) . '</p>';
                    echo '<p class="training-info"><strong>Ajánlott Időtartam:</strong> ' . htmlspecialchars($service['recommended_time']) . '</p>';
                    echo '<p class="training-info"><strong>Kiknek ajánljuk:</strong> ' . htmlspecialchars($service['recommended_to']) . '</p>';
                    echo '<p class="training-info"><strong>Időtartam:</strong> ' . htmlspecialchars($service['duration']) . ' perc alkalmanként</p>';
                    echo '<p class="training-info"><strong>Ár:</strong> ' . number_format($service['price'], 0, '.', ' ') . ' Ft</p>';
                    echo '</div>';
                    
                    $delay += 100;
                }
                
            } catch(PDOException $e) {
                echo '<p class="error">Sajnáljuk, a szolgáltatások betöltése sikertelen.</p>';
            }
            ?>
        </div>
    </section>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
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
            if (window.location.hash) {
                document.querySelector(window.location.hash).classList.add('pulse');
            }
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