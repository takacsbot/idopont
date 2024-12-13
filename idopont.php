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
        <select id="service-select" onchange="updateAvailableTimes()">
            <option value="">Válassz szolgáltatást</option>
            <option value="life-coaching">Life Coaching</option>
            <option value="business-coaching">Business Coaching</option>
            <option value="workshop">Stresszkezelés és Reziliencia Workshop</option>
            <option value="career">Karriertervezés és Énmárka Építés</option>
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
        const availableDates = {
            0: [5, 12, 19, 26],
            1: [3, 10, 17, 24],
            2: [1, 8, 15, 22],
            3: [5, 12, 19, 26],
            4: [3, 10, 17, 24],
            5: [1, 8, 15, 22],
            6: [6, 13, 20, 27],
            7: [3, 10, 17, 24],
            8: [7, 14, 21, 28],
            9: [5, 12, 19, 26],
            10: [2, 9, 16, 23],
            11: [7, 14, 21, 28]
        };

        const availableTimes = {
            "consultation": ["09:00", "10:00", "11:00", "14:00", "15:00"],
            "therapy": ["09:30", "10:30", "11:30", "13:30", "14:30"],
            "coaching": ["10:00", "11:00", "12:00", "15:00", "16:00"]
        };

        function updateCalendar() {
            const calendar = document.getElementById("calendar");
            calendar.innerHTML = "";

            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            const monthYear = document.getElementById("month-year");
            monthYear.textContent = currentDate.toLocaleString("hu-HU", { month: "long", year: "numeric" });

            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();

            for (let i = 0; i < firstDay; i++) {
                const emptyDay = document.createElement("div");
                emptyDay.classList.add("day");
                calendar.appendChild(emptyDay);
            }

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
            const today = new Date();
            if (currentDate > today) {
                currentDate.setMonth(currentDate.getMonth() - 1);
                updateCalendar();
            }
        }

        function nextMonth() {
            const maxDate = new Date();
            maxDate.setMonth(maxDate.getMonth() + 3);
            
            if (currentDate < maxDate) {
                currentDate.setMonth(currentDate.getMonth() + 1);
                updateCalendar();
            }
        }

        function selectDate(day) {
            const service = document.getElementById("service-select").value;
            if (service) {
                document.getElementById("time-select").style.display = "block";
                updateAvailableTimes();
                alert(`Kiválasztott dátum: ${currentDate.toLocaleString("hu-HU", { year: 'numeric', month: 'long' })} ${day}.`);
            } else {
                alert("Kérjük, válasszon szolgáltatást az időpontok megjelenítéséhez!");
            }
        }

        function updateAvailableTimes() {
            const service = document.getElementById("service-select").value;
            const timeOptions = document.getElementById("time-options");
            timeOptions.innerHTML = "";

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
            updateCalendar();
        });
</script>

</body>
</html>
