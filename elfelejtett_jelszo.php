<?php
session_start();
require_once 'db_config.php';
require_once 'functions.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    if (empty($email)) {
        $message = "Az email cím megadása kötelező.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $new_password = bin2hex(random_bytes(12));
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->execute([$hashed_password, $email]);
            sendEmail($email, 'Jelszó visszaállítása', "Kedves Felhasználó!
Az Ön fiókjához tartozó jelszó-visszaállítási kérelmet kaptunk.
Az új jelszava: $new_password
Fontos: Ezzel az jelszóval most már be tud jelentkezni a fiókjába. Bejelentkezés után kérjük, látogasson el a 'Profil adatok' menüpontba, ahol beállíthatja az Ön által választott új jelszót.
Ha nem Ön kérte ezt a jelszó-visszaállítást, kérjük, vegye fel a kapcsolatot ügyfélszolgálatunkkal azonnal.

Üdvözlettel,
A Firestarter Akadémia Csapata");
            $message = "Ha létezik fiók ezzel az email címmel, hamarosan kap egy új jelszót.";
        } else {
            $message = "Ezzel az e-mail címmel nem létezik fiók.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elfelejtett jelszó - Firestarter Akadémia</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />
    <link rel="stylesheet" href="css/elfelejtett_jelszo.css" />
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
        <h2>Elfelejtett jelszó</h2>
        <p class="instruction-text">Add meg az email címed, és küldünk egy jelszót.</p>
        <?php
        if (!empty($message)) {
            echo "<p class='message'>$message</p>";
        }
        ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="input-group">
                <input type="email" name="email" placeholder=" " required>
                <label for="email">Email cím</label>
            </div>
            <button type="submit" class="button">Jelszó visszaállítása</button>
        </form>

        <div class="login-link">
            <p>Vissza a <a href="login.php">bejelentkezéshez</a></p>
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
    </script>
</body>

</html>