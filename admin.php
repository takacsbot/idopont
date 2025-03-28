<?php
session_start();
require_once './php_backend/functions.php';
require_once './php_backend/db_config.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_action'])) {
    $loggedInUser = isLoggedIn($pdo);
    
    if (!isAdmin($loggedInUser)) {
        exit('Nincs Admin jogosultság.');
    }

    switch ($_POST['admin_action']) {
        case 'delete_user':
            if (isset($_POST['user_id'])) {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$_POST['user_id']]);
            }
            break;

        case 'reset_password':
            if (isset($_POST['user_id']) && isset($_POST['new_password'])) {
                $hashedPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $_POST['user_id']]);
            }
            break;

        case 'toggle_instructor':
            if (isset($_POST['user_id'])) {
                $stmt = $pdo->prepare("SELECT is_instructor FROM users WHERE id = ?");
                $stmt->execute([$_POST['user_id']]);
                $current = $stmt->fetchColumn();
                
                $stmt = $pdo->prepare("UPDATE users SET is_instructor = ? WHERE id = ?");
                $stmt->execute([!$current, $_POST['user_id']]);
            }
            break;
    }


    header("Location: admin.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Firestarter Akadémia</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="./css/admin.css">
    
</head>
<body>
    <header>
        <div class="header-content">
            <a href="./index.php" class="logo">Firestarter Akadémia</a>
            <nav>
                <a href="./index.php">Főoldal</a>
                <a href="./logout.php" class="logout-button">Kijelentkezés</a>
                <button class="theme-switch" onclick="toggleTheme()">
                    <span class="mode-text">☀️</span>
                </button>
            </nav>
        </div>
    </header>

    <?php
    $loggedInUser = isLoggedIn($pdo);
    
    if (!$loggedInUser || !isAdmin($loggedInUser)) {
        echo "<div class='admin-panel'>Hozzáférés megtagadva. Adminisztrátori jogosultság szükséges.</div>";
        exit();
    }
    ?>

    <div class="admin-panel">
        <h1>Adminisztrátori Felület</h1>
        
        <div class="search-container">
            <input 
                type="text" 
                id="userSearch" 
                placeholder="Keresés felhasználónév vagy email alapján..."
                class="search-input"
            >
        </div>

        <table class="user-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Felhasználónév</th>
                    <th>Email</th>
                    <th>Admin</th>
                    <th>Oktató</th>
                    <th>Műveletek</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $users = getUsersList($pdo);
                foreach ($users as $user): 
                ?>
                <tr>
                    <td data-label="ID"><?php echo htmlspecialchars($user['id']); ?></td>
                    <td data-label="Felhasználónév"><?php echo htmlspecialchars($user['username']); ?></td>
                    <td data-label="Email"><?php echo htmlspecialchars($user['email']); ?></td>
                    <td data-label="Admin"><?php echo $user['is_admin'] ? 'Igen' : 'Nem'; ?></td>
                    <td data-label="Oktató"><?php echo $user['is_instructor'] ? 'Igen' : 'Nem'; ?></td>
                    <td data-label="Műveletek">
                        <div class="admin-actions">
                            <form method="post" onsubmit="return confirm('Biztosan törölni szeretné a felhasználót?');">
                                <input type="hidden" name="admin_action" value="delete_user">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" class="login-button">Törlés</button>
                            </form>
                            <button onclick="showPasswordReset(<?php echo $user['id']; ?>)" class="login-button">Jelszó módosítás</button>
                            <form method="post">
                                <input type="hidden" name="admin_action" value="toggle_instructor">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" class="login-button instructor-toggle">
                                    <?php echo $user['is_instructor'] ? 'Oktató jogkör elvétele' : 'Oktató jogkör adása'; ?>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>


    <div id="passwordResetModal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); background:var(--card-bg); padding:2rem; border-radius:20px; box-shadow:0 0 20px rgba(0,0,0,0.2);">
        <h2>Jelszó módosítás</h2>
        <form method="post" id="passwordResetForm">
            <input type="hidden" name="admin_action" value="reset_password">
            <input type="hidden" name="user_id" id="resetUserId">
            <label for="new_password">Új jelszó:</label>
            <input type="password" name="new_password" required>
            <div class="admin-actions">
                <button type="submit" class="login-button">Mentés</button>
                <button type="button" class="login-button" onclick="closePasswordReset()">Mégsem</button>
            </div>
        </form>
    </div>

    <script>
        function showPasswordReset(userId) {
            document.getElementById('resetUserId').value = userId;
            document.getElementById('passwordResetModal').style.display = 'block';
        }

        function closePasswordReset() {
            document.getElementById('passwordResetModal').style.display = 'none';
        }

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

        document.getElementById('userSearch').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('.user-table tbody tr');
            
            rows.forEach(row => {
                const username = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const email = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                
                if (username.includes(searchTerm) || email.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>