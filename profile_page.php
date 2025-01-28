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

if (!isset($_SESSION['user_id'])) {
    header("Location: bejelentkezes.php");
    exit();
}

$stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_email'])) {
    $new_email = $_POST['new_email'];
    
    if (filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
        $stmt->execute([$new_email, $_SESSION['user_id']]);
        $success_message = "Email sikeresen frissítve!";
        $user['email'] = $new_email;
    } else {
        $error_message = "Érvénytelen email cím!";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $stored_password = $stmt->fetchColumn();
    
    if (password_verify($current_password, $stored_password)) {
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $_SESSION['user_id']]);
            $password_success_message = "Jelszó sikeresen megváltoztatva!";
        } else {
            $password_error_message = "Az új jelszavak nem egyeznek!";
        }
    } else {
        $password_error_message = "Jelenlegi jelszó nem helyes!";
    }
}
$current_page = isset($_GET['page']) ? $_GET['page'] : 'profile';

?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Felhasználói Profil - Firestarter Akadémia</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/profile_page.css"/>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">Firestarter Akadémia</div>
            <nav>
                <a href="./index.php">Főoldal</a>
                <a href="./kepzeseink.html">Képzések</a>
                <a href="./logout.php" class="logout-button">Kijelentkezés</a>
                <button class="theme-switch" onclick="toggleTheme()">
                    <span class="mode-text">☀️</span>
                </button>
            </nav>
        </div>
    </header>

    <main class="profile-container">
        <div class="profile-wrapper">
            <nav class="profile-nav">
                <a href="?page=profile" class="nav-item <?php echo $current_page === 'profile' ? 'active' : ''; ?>">
                    Profil Adatok
                </a>
                <a href="?page=courses" class="nav-item <?php echo $current_page === 'courses' ? 'active' : ''; ?>">
                    Kurzusaim
                </a>
            </nav>

            <section class="profile-content">
                <?php if ($current_page === 'profile'): ?>
                    <div class="profile-card">
     
                    <h1>Profil Adatok</h1>
                        
                        <div class="user-info">
                            <h2>Felhasználói Adatok</h2>
                            <p><strong>Felhasználónév:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                        </div>

                        <div class="email-update">
                            <h2>Email Módosítása</h2>
                            <?php if (isset($success_message)): ?>
                                <p class="success-message"><?php echo $success_message; ?></p>
                            <?php endif; ?>
                            <?php if (isset($error_message)): ?>
                                <p class="error-message"><?php echo $error_message; ?></p>
                            <?php endif; ?>
                            <form method="POST">
                                <input type="email" name="new_email" placeholder="Új email cím" required>
                                <button type="submit" name="update_email" class="update-button">Email frissítése</button>
                            </form>
                        </div>

                        <div class="password-change">
                            <h2>Jelszó Módosítása</h2>
                            <?php if (isset($password_success_message)): ?>
                                <p class="success-message"><?php echo $password_success_message; ?></p>
                            <?php endif; ?>
                            <?php if (isset($password_error_message)): ?>
                                <p class="error-message"><?php echo $password_error_message; ?></p>
                            <?php endif; ?>
                            <form method="POST">
                                <input type="password" name="current_password" placeholder="Jelenlegi jelszó" required>
                                <input type="password" name="new_password" placeholder="Új jelszó" required>
                                <input type="password" name="confirm_password" placeholder="Új jelszó megerősítése" required>
                                <button type="submit" name="change_password" class="update-button">Jelszó módosítása</button>
                            </form>
                        </div>
                    </div>
                <?php elseif ($current_page === 'courses'): ?>
                    <div class="profile-card">
                        <h1>Kurzusaim</h1>
                        <!-- Itt kezdődik a korábban létrehozott kurzusok szekció -->
                        <div class="ordered-courses">
                            <h2>Megrendelt Kurzusok</h2>
                            <?php if (isset($modification_success)): ?>
                                <p class="success-message"><?php echo $modification_success; ?></p>
                            <?php endif; ?>
                            
                            <?php if (empty($ordered_courses)): ?>
                                <p class="no-courses">Még nem rendeltél kurzust.</p>
                            <?php else: ?>
                                <div class="courses-list">
                                    <!-- ... (a korábban létrehozott kurzus lista kód) ... -->
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Firestarter Akadémia - Minden jog fenntartva</p>
    </footer>
    <script>
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

