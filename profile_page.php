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

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: bejelentkezes.php");
    exit();
}

// Fetch user details
$stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle email update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_email'])) {
    $new_email = $_POST['new_email'];
    
    // Basic email validation
    if (filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
        $stmt->execute([$new_email, $_SESSION['user_id']]);
        $success_message = "Email sikeresen frissítve!";
        $user['email'] = $new_email;
    } else {
        $error_message = "Érvénytelen email cím!";
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate password change
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $stored_password = $stmt->fetchColumn();
    
    if (password_verify($current_password, $stored_password)) {
        if ($new_password === $confirm_password) {
            // Hash the new password
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
        <section class="profile-card">
            <h1>Felhasználói Profil</h1>
            
            <!-- User Info Section -->
            <div class="user-info">
                <h2>Felhasználói Adatok</h2>
                <p><strong>Felhasználónév:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            </div>

            <!-- Email Update Section -->
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

            <!-- Password Change Section -->
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
        </section>
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