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

function isAdmin() {
    return isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'admin';
}


function getServices($pdo) {
    $stmt = $pdo->query("SELECT * FROM services ORDER BY name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addService($pdo, $name, $duration, $price) {
    $stmt = $pdo->prepare("INSERT INTO services (name, duration, price) VALUES (?, ?, ?)");
    return $stmt->execute([$name, $duration, $price]);
}


function addTimeSlot($pdo, $service_id, $date, $start_time, $end_time) {
    $stmt = $pdo->prepare("INSERT INTO time_slots (service_id, date, start_time, end_time, is_available) 
                          VALUES (?, ?, ?, ?, 1)");
    return $stmt->execute([$service_id, $date, $start_time, $end_time]);
}

function getTimeSlots($pdo, $start_date, $end_date) {
    $stmt = $pdo->prepare("SELECT ts.*, s.name as service_name 
                          FROM time_slots ts
                          JOIN services s ON ts.service_id = s.id
                          WHERE ts.date BETWEEN ? AND ?
                          ORDER BY ts.date, ts.start_time");
    $stmt->execute([$start_date, $end_date]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function getBookings($pdo) {
    $stmt = $pdo->query("SELECT b.*, u.username as client_name, s.name as service_name
                         FROM bookings b
                         JOIN users u ON b.user_id = u.id
                         JOIN services s ON b.service_id = s.id
                         ORDER BY b.date DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - Időpontfoglalás</title>
    <link rel="stylesheet" href="foglalas.css">
</head>
<body>
    <div class="admin-container">
        <nav class="admin-nav">
            <ul>
                <li><a href="#services">Szolgáltatások</a></li>
                <li><a href="#timeslots">Időpontok</a></li>
                <li><a href="#bookings">Foglalások</a></li>
                <li><a href="#settings">Beállítások</a></li>
            </ul>
        </nav>

        <div class="admin-content">

            <section id="services">
                <h2>Szolgáltatások kezelése</h2>
                <form method="POST" action="add_service.php">
                    <input type="text" name="name" placeholder="Szolgáltatás neve" required>
                    <input type="number" name="duration" placeholder="Időtartam (perc)" required>
                    <input type="number" name="price" placeholder="Ár" required>
                    <button type="submit">Hozzáadás</button>
                </form>
                
                <table class="services-table">
                    <thead>
                        <tr>
                            <th>Név</th>
                            <th>Időtartam</th>
                            <th>Ár</th>
                            <th>Műveletek</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach(getServices($pdo) as $service): ?>
                        <tr>
                            <td><?= htmlspecialchars($service['name']) ?></td>
                            <td><?= $service['duration'] ?> perc</td>
                            <td><?= number_format($service['price'], 0, ',', ' ') ?> Ft</td>
                            <td>
                                <button onclick="editService(<?= $service['id'] ?>)">Szerkesztés</button>
                                <button onclick="deleteService(<?= $service['id'] ?>)">Törlés</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>


            <section id="timeslots">
                <h2>Időpontok kezelése</h2>
                <form method="POST" action="add_timeslot.php">
                    <select name="service_id" required>
                        <?php foreach(getServices($pdo) as $service): ?>
                            <option value="<?= $service['id'] ?>"><?= htmlspecialchars($service['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="date" name="date" required>
                    <input type="time" name="start_time" required>
                    <input type="time" name="end_time" required>
                    <button type="submit">Időpont hozzáadása</button>
                </form>

                <div class="calendar-view">
                </div>
            </section>

            <section id="bookings">
                <h2>Foglalások</h2>
                <table class="bookings-table">
                    <thead>
                        <tr>
                            <th>Dátum</th>
                            <th>Kliens</th>
                            <th>Szolgáltatás</th>
                            <th>Státusz</th>
                            <th>Műveletek</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach(getBookings($pdo) as $booking): ?>
                        <tr>
                            <td><?= $booking['date'] ?></td>
                            <td><?= htmlspecialchars($booking['client_name']) ?></td>
                            <td><?= htmlspecialchars($booking['service_name']) ?></td>
                            <td><?= $booking['status'] ?></td>
                            <td>
                                <button onclick="confirmBooking(<?= $booking['id'] ?>)">Jóváhagyás</button>
                                <button onclick="cancelBooking(<?= $booking['id'] ?>)">Lemondás</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

            <section id="settings">
                <h2>Rendszer beállítások</h2>
                <form method="POST" action="update_settings.php">
                    <div class="setting-group">
                        <h3>Foglalási szabályok</h3>
                        <label>
                            Minimum előzetes foglalási idő (óra):
                            <input type="number" name="min_advance_hours" value="24">
                        </label>
                        <label>
                            Maximum előre foglalható napok száma:
                            <input type="number" name="max_advance_days" value="60">
                        </label>
                    </div>

                    <div class="setting-group">
                        <h3>Munkaidő beállítások</h3>
                        <label>
                            Napi kezdés:
                            <input type="time" name="work_day_start" value="09:00">
                        </label>
                        <label>
                            Napi befejezés:
                            <input type="time" name="work_day_end" value="17:00">
                        </label>
                    </div>

                    <div class="setting-group">
                        <h3>Értesítések</h3>
                        <label>
                            <input type="checkbox" name="email_notifications" checked>
                            Email értesítések küldése
                        </label>
                        <label>
                            <input type="checkbox" name="sms_notifications">
                            SMS értesítések küldése
                        </label>
                    </div>

                    <button type="submit">Beállítások mentése</button>
                </form>
            </section>
        </div>
    </div>

    <script>
        function editService(serviceId) {
        }

        function deleteService(serviceId) {
            if (confirm('Biztosan törli ezt a szolgáltatást?')) {
            }
        }

        function confirmBooking(bookingId) {
        }

        function cancelBooking(bookingId) {
        }

        document.addEventListener('DOMContentLoaded', function() {
            initializeCalendar();
        });

        function initializeCalendar() {
   
        }
    </script>
</body>
</html>