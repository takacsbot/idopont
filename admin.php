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
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['auth_token'] = $token;
            return $user;
        }
    }
    return false;
}

function isAdmin($user)
{

    return $user && isset($user['is_admin']) && $user['is_admin'] == 1;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_action'])) {
    $loggedInUser = isLoggedIn($pdo);
    
    if (!isAdmin($loggedInUser)) {
        die("Unauthorized access");
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
    }


    header("Location: admin.php");
    exit();
}


function getUsersList($pdo)
{
    $stmt = $pdo->query("SELECT id, username, email, is_admin FROM users ORDER BY id");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <?php
    $loggedInUser = isLoggedIn($pdo);
    
    if (!$loggedInUser || !isAdmin($loggedInUser)) {
        echo "<div class='admin-panel'>Hozzáférés megtagadva. Adminisztrátori jogosultság szükséges.</div>";
        exit();
    }
    ?>

    <div class="admin-panel">
        <h1>Adminisztrátori Felület</h1>
        <table class="user-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Felhasználónév</th>
                    <th>Email</th>
                    <th>Admin</th>
                    <th>Műveletek</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $users = getUsersList($pdo);
                foreach ($users as $user): 
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo $user['is_admin'] ? 'Igen' : 'Nem'; ?></td>
                    <td>
                        <div class="admin-actions">
                            <form method="post" onsubmit="return confirm('Biztosan törölni szeretné a felhasználót?');">
                                <input type="hidden" name="admin_action" value="delete_user">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" class="login-button">Törlés</button>
                            </form>
                            <button onclick="showPasswordReset(<?php echo $user['id']; ?>)" class="login-button">Jelszó módosítás</button>
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
    </script>
</body>
</html>