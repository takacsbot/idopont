<?php
session_start();

require_once 'db_config.php';
require_once 'functions.php';

// Check if user is admin
$user = isLoggedIn($pdo);
if (!$user || !isInstructor($user)) {
    header('Location: bejelentkezes.php');
    exit();
}

// Handle service addition
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    // Handle AJAX requests
    if (isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                case 'update_settings':
                    if (!isset(
                        $_POST['min_advance_hours'],
                        $_POST['max_advance_days'],
                        $_POST['work_day_start'],
                        $_POST['work_day_end']
                    )) {
                        throw new Exception('Missing settings parameters');
                    }
                    $settings = [
                        'min_advance_hours' => $_POST['min_advance_hours'],
                        'max_advance_days' => $_POST['max_advance_days'],
                        'work_day_start' => $_POST['work_day_start'],
                        'work_day_end' => $_POST['work_day_end'],
                        'email_notifications' => isset($_POST['email_notifications']),
                        'sms_notifications' => isset($_POST['sms_notifications'])
                    ];
                    $success = updateSettings($pdo, $settings);
                    echo json_encode(['success' => $success, 'message' => 'Settings updated successfully']);
                    break;

                case 'edit':
                    if (!isset($_POST['id'], $_POST['name'], $_POST['duration'], $_POST['price'])) {
                        throw new Exception('Missing parameters');
                    }
                    $success = editService($pdo, $_POST['id'], $_POST['name'], $_POST['duration'], $_POST['price']);
                    echo json_encode(['success' => $success, 'message' => 'Service updated successfully']);
                    break;

                case 'delete':
                    if (!isset($_POST['id'])) {
                        throw new Exception('Missing ID');
                    }
                    $success = deleteService($pdo, $_POST['id']);
                    echo json_encode(['success' => $success, 'message' => 'Service deleted successfully']);
                    break;

                case 'confirm':
                    if (!isset($_POST['bookingId'])) {
                        throw new Exception('Missing booking ID');
                    }
                    $success = updateBookingStatus($pdo, $_POST['bookingId'], 'confirmed');
                    echo json_encode(['success' => $success, 'message' => 'Booking confirmed successfully']);
                    break;

                case 'cancel':
                    if (!isset($_POST['bookingId'])) {
                        throw new Exception('Missing booking ID');
                    }
                    $success = updateBookingStatus($pdo, $_POST['bookingId'], 'cancelled');
                    echo json_encode(['success' => $success, 'message' => 'Booking cancelled successfully']);
                    break;

                case 'get':
                    if (!isset($_POST['id'])) {
                        throw new Exception('Missing ID');
                    }
                    $service = getService($pdo, $_POST['id']);
                    if ($service) {
                        echo json_encode(['success' => true, 'data' => $service]);
                    } else {
                        throw new Exception('Service not found');
                    }
                    break;

                default:
                    throw new Exception('Invalid action');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit();
    }

    // Handle regular form submissions
    if (isset($_POST['name'], $_POST['duration'], $_POST['price'])) {
        // Adding service
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $duration = filter_input(INPUT_POST, 'duration', FILTER_SANITIZE_NUMBER_INT);
        $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_INT);

        if (addService($pdo, $name, $duration, $price)) {
            $_SESSION['success'] = "Szolgáltatás sikeresen hozzáadva!";
        } else {
            $_SESSION['error'] = "Hiba történt a szolgáltatás hozzáadásakor.";
        }
        header('Location: #services');
        exit();
    } elseif (isset($_POST['service_id'], $_POST['date'], $_POST['start_time'], $_POST['end_time'])) {
        // Adding timeslot
        $service_id = filter_input(INPUT_POST, 'service_id', FILTER_SANITIZE_NUMBER_INT);
        $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
        $start_time = filter_input(INPUT_POST, 'start_time', FILTER_SANITIZE_STRING);
        $end_time = filter_input(INPUT_POST, 'end_time', FILTER_SANITIZE_STRING);

        if (addTimeSlot($pdo, $service_id, $date, $start_time, $end_time)) {
            $_SESSION['success'] = "Időpont sikeresen hozzáadva!";
        } else {
            $_SESSION['error'] = "Hiba történt az időpont hozzáadásakor.";
        }
        header('Location: #timeslots');
        exit();
    }
}

// Display any messages
if (isset($_SESSION['success'])) {
    echo "<div class='alert alert-success'>" . $_SESSION['success'] . "</div>";
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    echo "<div class='alert alert-error'>" . $_SESSION['error'] . "</div>";
    unset($_SESSION['error']);
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
            <button class="theme-switch" onclick="toggleTheme()">
        <span class="mode-text">☀️</span>
    </button>
        </nav>
        

        <div class="admin-content">
            <section id="services">
                <h2>Szolgáltatások kezelése</h2>
                <form method="POST">
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
                        <?php foreach (getServices($pdo) as $service): ?>
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
                <form method="POST">
                    <select name="service_id" required>
                        <?php foreach (getServices($pdo) as $service): ?>
                            <option value="<?= $service['id'] ?>"><?= htmlspecialchars($service['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="date" name="date" required>
                    <input type="time" name="start_time" required>
                    <input type="time" name="end_time" required>
                    <button type="submit">Időpont hozzáadása</button>
                </form>

                <div class="calendar-view">
                    <!-- Calendar view implementation -->
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
                        <?php foreach (getBookings($pdo) as $booking): ?>
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
                <form method="POST" id="settingsForm">
                    <input type="hidden" name="action" value="update_settings">
                    <?php $settings = getSettings($pdo); ?>
                    <div class="setting-group">
                        <h3>Foglalási szabályok</h3>
                        <label>
                            Minimum előzetes foglalási idő (óra):
                            <input type="number" name="min_advance_hours" value="<?= htmlspecialchars($settings['min_advance_hours']) ?>">
                        </label>
                        <label>
                            Maximum előre foglalható napok száma:
                            <input type="number" name="max_advance_days" value="<?= htmlspecialchars($settings['max_advance_days']) ?>">
                        </label>
                    </div>

                    <div class="setting-group">
                        <h3>Munkaidő beállítások</h3>
                        <label>
                            Napi kezdés:
                            <input type="time" name="work_day_start" value="<?= htmlspecialchars($settings['work_day_start']) ?>">
                        </label>
                        <label>
                            Napi befejezés:
                            <input type="time" name="work_day_end" value="<?= htmlspecialchars($settings['work_day_end']) ?>">
                        </label>
                    </div>

                    <div class="setting-group">
                        <h3>Értesítések</h3>
                        <label>
                            <input type="checkbox" name="email_notifications"
                                <?= $settings['email_notifications'] === 'true' ? 'checked' : '' ?>>
                            Email értesítések küldése
                        </label>
                        <label>
                            <input type="checkbox" name="sms_notifications"
                                <?= $settings['sms_notifications'] === 'true' ? 'checked' : '' ?>>
                            SMS értesítések küldése
                        </label>
                    </div>

                    <button type="submit">Beállítások mentése</button>
                </form>
            </section>
        </div>
    </div>

    <script>
        function confirmBooking(bookingId) {
            if (confirm('Biztosan jóváhagyja ezt a foglalást?')) {
                fetch('foglalas.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=confirm&bookingId=${bookingId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            location.reload();
                        } else {
                            alert('Hiba történt: ' + (data.error || 'Ismeretlen hiba'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Hiba történt a kérés feldolgozása során');
                    });
            }
        }

        function cancelBooking(bookingId) {
            if (confirm('Biztosan lemondja ezt a foglalást?')) {
                fetch('foglalas.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=cancel&bookingId=${bookingId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            location.reload();
                        } else {
                            alert('Hiba történt: ' + (data.error || 'Ismeretlen hiba'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Hiba történt a kérés feldolgozása során');
                    });
            }
        }

        function editService(serviceId) {
            fetch('foglalas.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=get&id=${serviceId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const service = data.data;
                        const modalHtml = `
                        <div id="editServiceModal" class="modal">
                            <div class="modal-content">
                                <h3>Szolgáltatás szerkesztése</h3>
                                <form id="editServiceForm">
                                    <input type="hidden" name="id" value="${service.id}">
                                    <input type="text" name="name" value="${service.name}" placeholder="Szolgáltatás neve" required>
                                    <input type="number" name="duration" value="${service.duration}" placeholder="Időtartam (perc)" required>
                                    <input type="number" name="price" value="${service.price}" placeholder="Ár" required>
                                    <div class="button-group">
                                        <button type="submit">Mentés</button>
                                        <button type="button" onclick="closeModal()">Mégse</button>
                                    </div>
                                </form>
                            </div>
                        </div>`;

                        document.body.insertAdjacentHTML('beforeend', modalHtml);

                        document.getElementById('editServiceForm').addEventListener('submit', function(e) {
                            e.preventDefault();
                            const formData = new FormData(this);
                            formData.append('action', 'edit');

                            fetch('foglalas.php', {
                                    method: 'POST',
                                    body: new URLSearchParams(formData)
                                })
                                .then(response => response.json())
                                .then(result => {
                                    if (result.success) {
                                        alert(result.message);
                                        location.reload();
                                    } else {
                                        alert('Hiba történt: ' + (result.error || 'Ismeretlen hiba'));
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    alert('Hiba történt a kérés feldolgozása során');
                                });
                        });
                    } else {
                        alert('Hiba történt: ' + (data.error || 'Ismeretlen hiba'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Hiba történt a kérés feldolgozása során');
                });
        }

        function deleteService(serviceId) {
            if (confirm('Biztosan törli ezt a szolgáltatást?')) {
                fetch('foglalas.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=delete&id=${serviceId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            location.reload();
                        } else {
                            alert('Hiba történt: ' + (data.error || 'Ismeretlen hiba'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Hiba történt a kérés feldolgozása során');
                    });
            }
        }

        function closeModal() {
            const modal = document.getElementById('editServiceModal');
            if (modal) {
                modal.remove();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            initializeCalendar();
        });

        function initializeCalendar() {
        }

        document.getElementById('settingsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            fetch('foglalas.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams(new FormData(this))
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Settings updated successfully');
                    } else {
                        alert('Error: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to update settings');
                });
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