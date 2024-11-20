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
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        :root {
            --primary: #FF6B6B;
            --secondary: #4ECDC4;
            --dark: #2D3436;
            --light: #F7F7F7;
            --gradient: linear-gradient(135deg, #FF6B6B, #FFA07A);
            --bg-color: #F7F7F7;
            --text-color: #2D3436;
            --card-bg: white;
            --header-bg: rgba(255, 255, 255, 0.95);
            --card-shadow: rgba(0, 0, 0, 0.1);
            --testimonial-bg: rgba(255, 255, 255, 0.05);
            --nav-link-color: #2D3436;
            --hero-bg: linear-gradient(135deg, #F6F6F6 0%, #FFFFFF 100%);
        }



        [data-theme="dark"] {
            --bg-color: #1a1a1a;
            --text-color: #ffffff;
            --card-bg: #2d2d2d;
            --header-bg: rgba(45, 45, 45, 0.95);
            --card-shadow: rgba(0, 0, 0, 0.3);
            --testimonial-bg: rgba(255, 255, 255, 0.1);
            --nav-link-color: #ffffff;
            --hero-bg: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        .bg {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 5rem 5%;
            background: var(--hero-bg);
            position: relative;
            overflow: hidden;
        }

        .bg::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: var(--gradient);
            opacity: 0.1;
            border-radius: 50%;
            transform: scale(1.5);
        }
   
        .theme-switch {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: var(--gradient);
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .theme-switch:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
        }

        header {
            text-align: center;
            padding: 2rem 2rem 2rem;
            background: var(--card-bg);
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }



        header h1 {
            font-size: 2.5rem;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
        }

        header p {
            color: var(--text-color);
            font-size: 1.2rem;
            position: relative;
            z-index: 1;
        }

        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto 2rem;
            padding: 2rem;
            background: var(--card-bg);
            border-radius: 20px;
            box-shadow: 0 10px 30px var(--card-shadow);
            position: relative;
            overflow: hidden;
        }

        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--gradient);
        }

        .container h2 {
            color: var(--text-color);
            margin-bottom: 2rem;
            font-size: 1.5rem;
        }

        select {
            width: 100%;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border: 2px solid var(--primary);
            border-radius: 10px;
            background: var(--card-bg);
            color: var(--text-color);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        select:focus {
            outline: none;
            box-shadow: 0 0 0 2px var(--primary);
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .calendar-header h3 {
            color: var(--primary);
            font-size: 1.2rem;
        }

        .calendar-nav {
            background: var(--gradient);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .calendar-nav:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
        }

        .calendar {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0.5rem;
            margin-bottom: 2rem;
        }

        .day {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .day.available {
            background: var(--gradient);
            color: white;
            cursor: pointer;
        }

        .day.available:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
        }

        .day.unavailable {
            background: var(--card-bg);
            color: #999;
            cursor: not-allowed;
        }

        button[type="submit"] {
            width: 100%;
            padding: 1rem;
            background: var(--gradient);
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
        }

        @media (max-width: 768px) {
            .container {
                margin: 1rem;
                padding: 1rem;
            }

            .calendar {
                gap: 0.25rem;
            }

            .day {
                font-size: 0.8rem;
            }

            header {
                padding: 4rem 1rem 1rem;
            }

            header h1 {
                font-size: 2rem;
            }
        }
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
