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

function isLoggedIn($pdo) {
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
            return true;
        }
    }
    return false;
}

if (!isLoggedIn($pdo)) {
    header("Location: bejelentkezes.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Időpontfoglalás</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Roboto', sans-serif;
        }

        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #232526, #414345);
            color: #f8f9fa;
            padding: 20px;
        }

        header {
            text-align: center;
            margin-bottom: 30px;
        }

        header h1 {
            font-size: 32px;
            color: #ff7f50;
            font-weight: 700;
        }

        .container {
            width: 100%;
            max-width: 800px;
            background-color: #2e2e2e;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.3);
        }

        .container h2 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 20px;
            color: #f8f9fa;
        }

        .service-select, .time-select {
            margin-bottom: 20px;
            width: 100%;
        }

        .service-select select, .time-select select {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 8px;
            background-color: #3c3f41;
            color: #f8f9fa;
            font-size: 16px;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .calendar-header h3 {
            margin: 0;
            font-size: 20px;
            color: #ff7f50;
        }

        .calendar-nav {
            font-size: 24px;
            cursor: pointer;
            color: #ff7f50;
        }

        .calendar {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
            margin-bottom: 30px;
        }

        .day {
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            background-color: #3c3f41;
            color: #f8f9fa;
            cursor: pointer;
        }

        .day.available {
            background-color: #ff7f50; /* Elérhető napok színe */
        }

        .day.unavailable {
            background-color: #6c757d;
            cursor: not-allowed;
        }

        .appointment-form button {
            padding: 15px;
            border: none;
            border-radius: 8px;
            background: #ff7f50;
            color: #fff;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.3s;
        }

        .appointment-form button:hover {
            background: #ff5a1d;
        }

        .time-select {
            display: none; /* Alapértelmezett rejtett */
        }

        .time-select.active {
            display: block; /* Megjelenítés, ha aktív */
        }
    </style>
</head>
<body>

<header>
    <h1>Időpontfoglalás</h1>
</header>

<div class="container">
    <h2>Foglalási adatok:</h2>
    <div class="service-select">
        <select id="service-select" onchange="updateAvailableTimes()">
            <option value="">Válassz szolgáltatást</option>
            <option value="consultation">Konzultáció</option>
            <option value="therapy">Terápia</option>
            <option value="coaching">Coaching</option>
        </select>
    </div>

    <div class="calendar-header">
        <div class="calendar-nav" onclick="prevMonth()">&lt;</div>
        <h3 id="month-year"></h3>
        <div class="calendar-nav" onclick="nextMonth()">&gt;</div>
    </div>

    <div class="calendar" id="calendar"></div>

    <div class="time-select" id="time-select">
        <h3>Szabad időpontok:</h3>
        <select id="time-options"></select>
    </div>

    <div class="appointment-form">
        <button type="submit" onclick="bookAppointment()">Időpont foglalása</button>
    </div>
</div>

<script>
    let currentDate = new Date();
    const availableDates = {
        0: [5, 12, 19, 26], // Január
        1: [3, 10, 17, 24], // Február
        2: [1, 8, 15, 22], // Március
        3: [5, 12, 19, 26], // Április
        4: [3, 10, 17, 24], // Május
        5: [1, 8, 15, 22], // Június
        6: [6, 13, 20, 27], // Július
        7: [3, 10, 17, 24], // Augusztus
        8: [7, 14, 21, 28], // Szeptember
        9: [5, 12, 19, 26], // Október
        10: [2, 9, 16, 23], // November
        11: [7, 14, 21, 28]  // December
    };

    const availableTimes = {
        "consultation": ["09:00", "10:00", "11:00", "14:00", "15:00"],
        "therapy": ["09:30", "10:30", "11:30", "13:30", "14:30"],
        "coaching": ["10:00", "11:00", "12:00", "15:00", "16:00"]
    };

    function updateCalendar() {
        const calendar = document.getElementById("calendar");
        calendar.innerHTML = ""; // Tisztítás

        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
        const monthYear = document.getElementById("month-year");
        monthYear.textContent = currentDate.toLocaleString("hu-HU", { month: "long", year: "numeric" });

        const daysInMonth = new Date(year, month + 1, 0).getDate();

        for (let day = 1; day <= daysInMonth; day++) {
            const dayDiv = document.createElement("div");
            dayDiv.classList.add("day");

            if (availableDates[month] && availableDates[month].includes(day)) {
                dayDiv.classList.add("available");
                dayDiv.addEventListener("click", () => selectDate(day));
            } else {
                dayDiv.classList.add("unavailable");
            }

            dayDiv.textContent = day;
            calendar.appendChild(dayDiv);
        }
    }

    function prevMonth() {
        if (currentDate.getMonth() > new Date().getMonth() || currentDate.getFullYear() > new Date().getFullYear()) {
            currentDate.setMonth(currentDate.getMonth() - 1);
            updateCalendar();
        }
    }

    function nextMonth() {
        if (currentDate.getMonth() < 11) {
            currentDate.setMonth(currentDate.getMonth() + 1);
            updateCalendar();
        }
    }

    function selectDate(day) {
        const service = document.getElementById("service-select").value;
        if (service) {
            document.getElementById("time-select").classList.add("active");
            updateAvailableTimes();
            alert("Kiválasztott dátum: " + day + ". nap");
        } else {
            alert("Kérjük, válasszon szolgáltatást az időpontok megjelenítéséhez!");
        }
    }

    function updateAvailableTimes() {
        const service = document.getElementById("service-select").value;
        const timeOptions = document.getElementById("time-options");
        timeOptions.innerHTML = ""; // Tisztítás

        if (service) {
            const times = availableTimes[service] || [];
            times.forEach(time => {
                const option = document.createElement("option");
                option.value = time;
                option.textContent = time;
                timeOptions.appendChild(option);
            });
        }
    }

    function bookAppointment() {
        const service = document.getElementById("service-select").value;
        const time = document.getElementById("time-options").value;

        if (!service) {
            alert("Kérjük, válasszon szolgáltatást!");
            return;
        }
        if (!time) {
            alert("Kérjük, válasszon időpontot!");
            return;
        }
        alert("Időpont foglalása sikeres: " + service + " - " + time);
    }

    document.addEventListener("DOMContentLoaded", updateCalendar);
</script>

</body>
</html>
