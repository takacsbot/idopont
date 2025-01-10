<?php
session_start();
require_once 'db_config.php';
require_once 'functions.php';

$user = isLoggedIn($pdo);
if (!$user) {
    header("Location: bejelentkezes.php");
    exit();
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json; charset=utf-8');

    switch ($_GET['action']) {
        case 'get_dates':
            if (!isset($_GET['year'], $_GET['month'])) {
                http_response_code(400);
                exit(json_encode(['error' => 'Missing parameters']));
            }

            $year = (int)$_GET['year'];
            $month = (int)$_GET['month'];
            $service_id = $_GET['service_id'] ?? null;

            // Get the first and last day of the month
            $start_date = sprintf('%04d-%02d-01', $year, $month);
            $end_date = date('Y-m-t', strtotime($start_date));

            // Query to get days with available time slots
            $sql = "SELECT DISTINCT DAY(date) as day 
                    FROM time_slots 
                    WHERE date BETWEEN ? AND ? 
                    AND is_available = 1";
            $params = [$start_date, $end_date];

            if ($service_id) {
                $sql .= " AND service_id = ?";
                $params[] = $service_id;
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll());
            exit();

        case 'get_timeslots':
            if (!isset($_GET['service_id'], $_GET['date'])) {
                http_response_code(400);
                exit(json_encode(['error' => 'Missing parameters']));
            }

            $slots = getAvailableTimeSlots($pdo, $_GET['service_id'], $_GET['date']);
            echo json_encode($slots);
            exit();
    }
}

// Handle booking creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    
    $data = json_decode(file_get_contents('php://input'), true);
    $service_id = $data['service_id'] ?? null;
    $time_slot_id = $data['time_slot_id'] ?? null;

    if (!$service_id || !$time_slot_id) {
        http_response_code(400);
        exit(json_encode(['success' => false, 'message' => 'Missing parameters']));
    }

    $success = createBooking($pdo, $user['id'], $service_id, $time_slot_id);

    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Failed to create booking']);
    }
    exit();
}

?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Időpontfoglalás - Firestarter Akadémia</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />
    <link rel="stylesheet" href="css/idopont.css"/>

    </style>
</head>
<body class="bg">
    <button class="theme-switch" onclick="toggleTheme()">
        <span class="mode-text">☀️</span>
    </button>


    <div class="container">
        
    <header>
        <h1>Időpontfoglalás</h1>
        <p>Válassz szolgáltatást és időpontot</p>
    </header>
        <h2>Foglalási adatok</h2>
        <select id="service-select" onchange="updateServiceSelection()">
            <option value="">Válassz szolgáltatást</option>
            <?php foreach(getServices($pdo) as $service): ?>
                <option value="<?= $service['id'] ?>"><?= htmlspecialchars($service['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <div class="calendar-header">
            <button class="calendar-nav" onclick="prevMonth()">&#8249;</button>
            <h3 id="month-year"></h3>
            <button class="calendar-nav" onclick="nextMonth()">&#8250;</button>
        </div>

        <div class="calendar" id="calendar"></div>

        <div id="time-select" style="display: none;">
            <h3>Szabad időpontok:</h3>
            <select id="time-options"></select>
        </div>

        <button type="submit" onclick="bookAppointment()">Időpont foglalása</button>
    </div>

    <script>
        let currentDate = new Date();
        
        function getAvailableDatesForMonth(year, month) {
            const service = document.getElementById("service-select").value;
            const url = `idopont.php?action=get_dates&year=${year}&month=${month + 1}${service ? '&service_id=' + service : ''}`;
            
            return fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(dates => {
                    return dates.map(date => parseInt(date.day));
                })
                .catch(error => {
                    console.error('Error fetching dates:', error);
                    return [];
                });
        }

        async function updateCalendar() {
            const calendar = document.getElementById("calendar");
            calendar.innerHTML = "";

            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            const monthYear = document.getElementById("month-year");
            monthYear.textContent = currentDate.toLocaleString("hu-HU", { month: "long", year: "numeric" });

            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();

            // Get available dates from server
            const availableDates = await getAvailableDatesForMonth(year, month);

            // Add empty cells for days before the first day of the month
            for (let i = 0; i < firstDay; i++) {
                const emptyDay = document.createElement("div");
                emptyDay.classList.add("day");
                calendar.appendChild(emptyDay);
            }

            // Create calendar days
            for (let day = 1; day <= daysInMonth; day++) {
                const dayDiv = document.createElement("div");
                dayDiv.classList.add("day");

                // Check if date is in the past
                const dateToCheck = new Date(year, month, day);
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                if (dateToCheck < today) {
                    dayDiv.classList.add("unavailable");
                } else if (availableDates.includes(day)) {
                    dayDiv.classList.add("available");
                    dayDiv.addEventListener("click", () => selectDate(day));
                } else {
                    dayDiv.classList.add("unavailable");
                }

                dayDiv.textContent = day;
                calendar.appendChild(dayDiv);
            }
        }

        async function prevMonth() {
            const today = new Date();
            if (currentDate > today) {
                currentDate.setMonth(currentDate.getMonth() - 1);
                await updateCalendar();
            }
        }

        async function nextMonth() {
            const maxDate = new Date();
            maxDate.setMonth(maxDate.getMonth() + 3);
            
            if (currentDate < maxDate) {
                currentDate.setMonth(currentDate.getMonth() + 1);
                await updateCalendar();
            }
        }

        function selectDate(day) {
            const allDays = document.querySelectorAll('.day');
            allDays.forEach(d => d.classList.remove('selected'));
            
            const selectedDay = Array.from(allDays).find(d => d.textContent == day);
            if (selectedDay) {
                selectedDay.classList.add('selected');
                updateAvailableTimes();
            }
        }

        function updateAvailableTimes() {
            const service = document.getElementById("service-select").value;
            const selectedDate = document.querySelector(".day.selected");
            
            if (!service || !selectedDate) return;
            
            const date = `${currentDate.getFullYear()}-${(currentDate.getMonth() + 1).toString().padStart(2, '0')}-${selectedDate.textContent.padStart(2, '0')}`;
            
            fetch(`idopont.php?action=get_timeslots&service_id=${service}&date=${date}`)
                .then(response => response.json())
                .then(times => {
                    const timeOptions = document.getElementById("time-options");
                    timeOptions.innerHTML = "";
                    
                    times.forEach(slot => {
                        const option = document.createElement("option");
                        option.value = slot.id;
                        option.textContent = `${slot.start_time} - ${slot.end_time}`;
                        timeOptions.appendChild(option);
                    });
                    
                    document.getElementById("time-select").style.display = "block";
                });
        }

        function bookAppointment() {
            const service_id = document.getElementById("service-select").value;
            const time_slot_id = document.getElementById("time-options").value;
            
            if (!service_id || !time_slot_id) {
                alert("Kérjük, válasszon szolgáltatást és időpontot!");
                return;
            }
            
            fetch('idopont.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    service_id: service_id,
                    time_slot_id: time_slot_id
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Időpont foglalása sikeres!");
                    location.reload();
                } else {
                    alert("Hiba történt a foglalás során: " + data.message);
                }
            });
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
        window.addEventListener('DOMContentLoaded', async () => {
            if (window.location.hash) {
                console.log(window.location.hash.split('#')[1])
                document.getElementById('service-select').value = window.location.hash.split('#')[1];
                document.getElementById('service-select').disabled = true;
            }
            const savedTheme = localStorage.getItem('theme');
            const button = document.querySelector('.theme-switch');
            const modeText = button.querySelector('.mode-text');
            
            if (savedTheme === 'dark') {
                document.body.setAttribute('data-theme', 'dark');
                modeText.textContent = '🌙';
            }
            await updateCalendar();
        });

        function updateServiceSelection() {
            const service = document.getElementById("service-select").value;
            updateCalendar(); // This will now fetch dates for the selected service
        }
    </script>

</body>
</html>
