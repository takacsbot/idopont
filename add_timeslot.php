<?php
session_start();
$host = 'localhost';
$dbname = 'timetable_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8mb4");
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Adatbázis kapcsolódási hiba: " . $e->getMessage());
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = filter_input(INPUT_POST, 'service_id', FILTER_SANITIZE_NUMBER_INT);
    $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
    $start_time = filter_input(INPUT_POST, 'start_time', FILTER_SANITIZE_STRING);
    $end_time = filter_input(INPUT_POST, 'end_time', FILTER_SANITIZE_STRING);

    if (!strtotime($date)) {
        $_SESSION['error'] = "Érvénytelen dátum formátum!";
        header('Location: index.php#timeslots');
        exit();
    }

    if (!strtotime($start_time) || !strtotime($end_time)) {
        $_SESSION['error'] = "Érvénytelen időpont formátum!";
        header('Location: index.php#timeslots');
        exit();
    }

    if (strtotime($start_time) >= strtotime($end_time)) {
        $_SESSION['error'] = "A kezdő időpontnak korábbinak kell lennie, mint a záró időpontnak!";
        header('Location: index.php#timeslots');
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM time_slots 
                              WHERE date = ? 
                              AND ((start_time BETWEEN ? AND ?) 
                              OR (end_time BETWEEN ? AND ?))");
        $stmt->execute([$date, $start_time, $end_time, $start_time, $end_time]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $_SESSION['error'] = "A megadott időintervallum ütközik egy már létező időponttal!";
            header('Location: index.php#timeslots');
            exit();
        }

        $stmt = $pdo->prepare("INSERT INTO time_slots (service_id, date, start_time, end_time, is_available) 
                              VALUES (?, ?, ?, ?, 1)");
        $stmt->execute([$service_id, $date, $start_time, $end_time]);
        
        $_SESSION['success'] = "Időpont sikeresen hozzáadva!";
        
    } catch (PDOException $e) {
        $_SESSION['error'] = "Hiba történt: " . $e->getMessage();
    }
    
    header('Location: index.php#timeslots');
    exit();
}