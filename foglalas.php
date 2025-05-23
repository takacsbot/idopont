<?php
session_start();

require_once './php_backend/db_config.php';
require_once './php_backend/functions.php';

$user = isLoggedIn($pdo);
if (!$user || !isInstructor($user)) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            header('Content-Type: application/json; charset=utf-8');

            deleteOldBookings($pdo);
            
            switch ($_POST['action']) {
                case 'update_settings':
                    if (!isset(
                        $_POST['min_advance_hours'],
                        $_POST['max_advance_days'],
                        $_POST['work_day_start'],
                        $_POST['work_day_end']
                    )) {
                        throw new Exception('Hiányzó paraméterek.');
                    }
                    $settings = [
                        'min_advance_hours' => $_POST['min_advance_hours'],
                        'max_advance_days' => $_POST['max_advance_days'],
                        'work_day_start' => $_POST['work_day_start'],
                        'work_day_end' => $_POST['work_day_end'],
                    ];
                    $success = updateSettings($pdo, $settings);
                    echo json_encode(['success' => $success, 'message' => 'Beállítások frissítve.']);
                    break;

                case 'edit':
                    if (!isset($_POST['id'], $_POST['name'], $_POST['duration'], $_POST['price'])) {
                        throw new Exception('Hiányzó paraméterek.');
                    }
                    $success = editService($pdo, $_POST['id'], $_POST['name'], $_POST['duration'], $_POST['price'], $_POST['description'], $_POST['recommended_time'], $_POST['recommended_to']);
                    echo json_encode(['success' => $success, 'message' => 'Szolgáltatás frissítve.']);
                    break;

                case 'delete':
                    if (!isset($_POST['id'])) {
                        throw new Exception('Hiányzó azonosító.');
                    }
                    $success = deleteService($pdo, $_POST['id']);
                    echo json_encode(['success' => $success, 'message' => 'Szolgáltatás törölve.']);
                    break;

                case 'confirm':
                    if (!isset($_POST['bookingId'])) {
                        throw new Exception('Hiányzó foglalás azonosító.');
                    }
                    $success = updateBookingStatus($pdo, $_POST['bookingId'], 'confirmed');
                    
                    if ($success) {
                        $stmt = $pdo->prepare("SELECT u.email, s.name AS course_name, t.start_time, t.date FROM bookings b 
                                                JOIN users u ON b.user_id = u.id 
                                                JOIN services s ON b.service_id = s.id 
                                                JOIN time_slots t ON b.time_slot_id = t.id 
                                                WHERE b.id = ?");
                        $stmt->execute([$_POST['bookingId']]);
                        $bookingDetails = $stmt->fetch();

                        if ($bookingDetails) {
                            $courseName = $bookingDetails['course_name'];
                            $courseTime = $bookingDetails['date'] . ' ' . $bookingDetails['start_time'];
                            sendBookingEmail($bookingDetails['email'], $_POST['bookingId'], 'confirmed', $courseName, $courseTime);
                        }
                    }
                    
                    echo json_encode(['success' => $success, 'message' => 'Foglalás jóváhagyva.']);
                    break;

                case 'cancel':
                    if (!isset($_POST['bookingId'])) {
                        throw new Exception('Hiányzó foglalás azonosító.');
                    }
                    $success = updateBookingStatus($pdo, $_POST['bookingId'], 'cancelled');
                    
                    if ($success) {
                        $stmt = $pdo->prepare("SELECT u.email, s.name AS course_name, t.start_time, t.date FROM bookings b 
                                                JOIN users u ON b.user_id = u.id 
                                                JOIN services s ON b.service_id = s.id 
                                                JOIN time_slots t ON b.time_slot_id = t.id 
                                                WHERE b.id = ?");
                        $stmt->execute([$_POST['bookingId']]);
                        $bookingDetails = $stmt->fetch();

                        if ($bookingDetails) {
                            $courseName = $bookingDetails['course_name'];
                            $courseTime = $bookingDetails['date'] . ' ' . $bookingDetails['start_time'];
                            sendBookingEmail($bookingDetails['email'], $_POST['bookingId'], 'cancelled', $courseName, $courseTime);
                        }
                    }
                    
                    echo json_encode(['success' => $success, 'message' => 'Foglalás lemondva.']);
                    break;

                case 'get':
                    if (!isset($_POST['id'])) {
                        throw new Exception('Hiányzó azonosító.');
                    }
                    $service = getService($pdo, $_POST['id']);
                    if ($service) {
                        echo json_encode(['success' => true, 'data' => $service]);
                    } else {
                        throw new Exception('Szolgáltatás nem található.');
                    }
                    break;

                default:
                    throw new Exception('Helytelen művelet.');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit();
    }

    if (isset($_POST['name'], $_POST['duration'], $_POST['price'], $_FILES['image'], $_POST['description'], $_POST['recommended_time'], $_POST['recommended_to'])) {
        $image = $_FILES['image'];
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $duration = filter_input(INPUT_POST, 'duration', FILTER_SANITIZE_NUMBER_INT);
        $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_INT);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
        $recommended_time = filter_input(INPUT_POST, 'recommended_time', FILTER_SANITIZE_STRING);
        $recommended_to = filter_input(INPUT_POST, 'recommended_to', FILTER_SANITIZE_STRING);

        if (addService($pdo, $user, $name, $duration, $price, $image, $description, $recommended_time, $recommended_to)) {
            $_SESSION['success'] = "Szolgáltatás sikeresen hozzáadva!";
        } else {
            $_SESSION['error'] = "Hiba történt a szolgáltatás hozzáadásakor.";
        }
        header('Location: #alert');
        exit();
    } elseif (isset($_POST['service_id'], $_POST['date'], $_POST['start_time'], $_POST['end_time'])) {
        $service_id = filter_input(INPUT_POST, 'service_id', FILTER_SANITIZE_NUMBER_INT);
        $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
        $start_time = filter_input(INPUT_POST, 'start_time', FILTER_SANITIZE_STRING);
        $end_time = filter_input(INPUT_POST, 'end_time', FILTER_SANITIZE_STRING);
    
        $settings = getSettings($pdo);
        $work_start = strtotime("1970-01-01 " . $settings['work_day_start']);
        $work_end = strtotime("1970-01-01 " . $settings['work_day_end']);
        $slot_start = strtotime("1970-01-01 " . $start_time);
        $slot_end = strtotime("1970-01-01 " . $end_time);
    
        if ($slot_start < $work_start || $slot_end > $work_end) {
            $_SESSION['error'] = "Az időpont a munkaidőn kívül esik! (Munkaidő: {$settings['work_day_start']} - {$settings['work_day_end']})";
            header('Location: #alert');
            exit();
        }
    
        try {
            if (addTimeSlot($pdo, $service_id, $date, $start_time, $end_time)) {
                $_SESSION['success'] = "Időpont sikeresen hozzáadva!";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        header('Location: #alert');
        exit();
    }
}
if (isset($_SESSION['success'])) {
    echo "<div class='alert alert-success' id='alert'>" . $_SESSION['success'] . "</div>";
    echo "<script>
        setTimeout(function() {
            document.getElementById('alert').style.opacity = '0';
            setTimeout(function() {
                document.getElementById('alert').remove();
            }, 400);
        }, 3600);
    </script>";
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    echo "<div class='alert alert-error' id='alert'>" . $_SESSION['error'] . "</div>";
    echo "<script>
        setTimeout(function() {
            document.getElementById('alert').style.opacity = '0';
            setTimeout(function() {
                document.getElementById('alert').remove();
            }, 400);
        }, 3600);
    </script>";
    unset($_SESSION['error']);
}

?>

<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <title>Oktatói kezelőfelület - Időpontfoglalás</title>
    <link rel="stylesheet" href="foglalas.css">
</head>

<body>
    <div class="admin-container">
        <nav class="admin-nav">
            <a href="./index.php" class="logo">Firestarter Akadémia</a>
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
                <form method="POST" enctype="multipart/form-data">
                    <input type="text" name="name" placeholder="Szolgáltatás neve" required>
                    <input type="text" name="description" placeholder="Szolgáltatás leírása" required>
                    <input type="text" name="recommended_time" placeholder="Ajánlott időtartam" required>
                    <input type="text" name="recommended_to" placeholder="Kiknek ajánlott" required>
                    <input type="number" name="duration" placeholder="Időtartam (perc)" required>
                    <input type="number" name="price" placeholder="Ár" required>
                    <input type="file" name="image" placeholder="Kép" accept=".jpg" required>
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
                        <?php foreach (getServices($pdo, $user) as $service): ?>
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
                <?php $settings = getSettings($pdo); ?>
                <form method="POST">
                    <select name="service_id" required>
                        <?php foreach (getServices($pdo, $user) as $service): ?>
                            <option value="<?= $service['id'] ?>"><?= htmlspecialchars($service['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="date-input-container">
                        <input type="text" 
                               name="date" 
                               required 
                               placeholder="ÉÉÉÉ-HH-NN"
                               pattern="\d{4}-\d{2}-\d{2}"
                               minlength="10"
                               maxlength="10"
                               value="<?= date('Y-m-d') ?>"
                               oninput="formatDate(this)">
                        <span class="calendar-icon"></span>
                    </div>
                    <input type="time" 
                           name="start_time" 
                           required 
                           step="900" 
                           min="<?= $settings['work_day_start'] ?>" 
                           max="<?= $settings['work_day_end'] ?>">
                    <input type="time" 
                           name="end_time" 
                           required 
                           step="900"
                           min="<?= $settings['work_day_start'] ?>" 
                           max="<?= $settings['work_day_end'] ?>">
                    <button type="submit">Időpont hozzáadása</button>
                </form>
            </section>

            <section id="bookings">
                <h2>Foglalások</h2>
                <table class="bookings-table">
                    <thead>
                        <tr>
                            <th>Dátum</th>
                            <th>Időpont</th>
                            <th>Kliens</th>
                            <th>Szolgáltatás</th>
                            <th>Státusz</th>
                            <th>Műveletek</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (getBookings($pdo, $user) as $booking): ?>
                            <tr>
                                <td><?= htmlspecialchars($booking['formatted_date']) ?></td>
                                <td><?= htmlspecialchars($booking['formatted_start_time']) ?> - 
                                    <?= htmlspecialchars($booking['formatted_end_time']) ?></td>
                                <td><?= htmlspecialchars($booking['client_name']) ?></td>
                                <td><?= htmlspecialchars($booking['service_name']) ?></td>
                                <td>
                                    <?php
                                    $statusText = [
                                        'pending' => 'Függőben',
                                        'confirmed' => 'Jóváhagyva',
                                        'cancelled' => 'Lemondva'
                                    ];
                                    echo $statusText[$booking['status']] ?? $booking['status'];
                                    ?>
                                </td>
                                <td>
                                    <?php if ($booking['status'] === 'pending'): ?>
                                        <button onclick="confirmBooking(<?= $booking['id'] ?>)">Jóváhagyás</button>
                                    <?php endif; ?>
                                    <?php if ($booking['status'] !== 'cancelled'): ?>
                                        <button onclick="cancelBooking(<?= $booking['id'] ?>)">Lemondás</button>
                                    <?php endif; ?>
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
                        alert('Hiba történt a kérés feldolgozása során.');
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
                                    <form id="editServiceForm" enctype="multipart/form-data">
                                    <input type="hidden" name="id" value="${service.id}">
                                    <input type="text" name="name" value="${service.name}" placeholder="Szolgáltatás neve" required>
                                    <input type="text" name="description" value="${service.description}" placeholder="Leírás" required>
                                    <input type="text" name="recommended_time" value="${service.recommended_time}" placeholder="Ajánlott időtartam" required>
                                    <input type="text" name="recommended_to" value="${service.recommended_to}" placeholder="Ajánlott kiknek" required>
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
            
            const dateInputs = document.querySelectorAll('input[type="date"]');
            dateInputs.forEach(input => {
                input.addEventListener('input', function() {
                    formatDate(this);
                });
            });
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
                        alert('Beállítások frissítve.');
                    } else {
                        alert('Error: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Hiba történt a kérés feldolgozása során.');
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

    function formatDate(input) {
        let value = input.value.replace(/\D/g, '');
        
        if (value.length > 8) {
            value = value.slice(0, 8);
        }
        let formattedValue = '';
        if (value.length > 0) {
            formattedValue = value.slice(0, Math.min(4, value.length));
            
            if (value.length > 4) {
                formattedValue += '-' + value.slice(4, Math.min(6, value.length));
            }
            
            if (value.length > 6) {
                formattedValue += '-' + value.slice(6, 8);
            }
        }
        
        input.value = formattedValue;
    }
    </script>
</body>

</html>