<?php
require_once './php_backend/db_config.php';
require_once './php_backend/functions.php';
session_start();
$user = isLoggedIn($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && 
    stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        $stmt = $pdo->prepare("SELECT is_available FROM time_slots WHERE id = ? AND service_id = ?");
        $stmt->execute([$data['time_slot_id'], $data['service_id']]);
        $timeSlot = $stmt->fetch(PDO::FETCH_ASSOC);


        $success = createBooking($pdo, $user['id'], $data['service_id'], $data['time_slot_id']);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Foglalás sikeres!'
        ]);
        exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $service_id = isset($_GET['service_id']) ? (int)$_GET['service_id'] : 0;
    $time_slot_id = isset($_GET['time_slot_id']) ? (int)$_GET['time_slot_id'] : 0;
    $sql = "SELECT s.*, ts.date, ts.start_time, ts.end_time 
            FROM services s
            JOIN time_slots ts ON ts.service_id = s.id
            WHERE s.id = ? AND ts.id = ? AND ts.is_available = 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$service_id, $time_slot_id]); 
    $booking_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking_data) {
        header("Location: idopont.php");
        exit();
    }
}

$appointment_date = date('Y.m.d', strtotime($booking_data['date'])) . '. ' . 
                   date('H:i', strtotime($booking_data['start_time'])) . ' - ' . 
                   date('H:i', strtotime($booking_data['end_time']));
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Fizetés - Firestarter Akadémia</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />
    <link rel="stylesheet" href="css/idopont.css"/>
    <link rel="stylesheet" href="css/payment_page.css"/>
</head>
<body class="bg">
    <button class="theme-switch" onclick="toggleTheme()">
        <span class="mode-text">☀️</span>
    </button>

    <div class="container">
        <header>
            <h1>Fizetés</h1>
            <p>Fizetési adatok megadása</p>
        </header>
        
        <div class="summary">
            <h3>Rendelés összesítő</h3>
            <div class="summary-item">
                <span>Szolgáltatás:</span>
                <span id="service-name"><?php echo htmlspecialchars($booking_data['name']); ?></span>
            </div>
            <div class="summary-item">
                <span>Időpont:</span>
                <span id="appointment-date"><?php echo htmlspecialchars($appointment_date); ?></span>
            </div>
            <div class="summary-item summary-total">
                <span>Fizetendő összeg:</span>
                <span id="total-amount"><?php echo number_format($booking_data['price'], 0, ',', '.') . ' Ft'; ?></span>
            </div>
        </div>
        
        <h2>Fizetési mód</h2>
        <div class="payment-methods">
            <div class="payment-method active" onclick="selectPaymentMethod(this, 'card')">
                <h4>Bankkártya</h4>
            </div>
            <div class="payment-method" onclick="selectPaymentMethod(this, 'transfer')">
                <h4>Átutalás</h4>
            </div>
        </div>
        
        <form id="payment-form" onsubmit="submitPayment(event)">
            <div id="card-payment-section">
                <h2>Kártyaadatok</h2>
                <div class="form-group">
                    <label for="card-holder">Kártyabirtokos neve</label>
                    <input type="text" id="card-holder" class="form-control" required>
                </div>
                
                <div class="form-group card-details">
                    <div class="card-number">
                        <label for="card-number">Kártyaszám</label>
                        <input type="text" id="card-number" class="form-control" placeholder="1234 5678 9012 3456" required>
                    </div>
                    
                    <div class="card-expiry">
                        <label for="card-expiry">Lejárat</label>
                        <input type="text" id="card-expiry" class="form-control" placeholder="HH/ÉÉ" required>
                    </div>
                    
                    <div class="card-cvc">
                        <label for="card-cvc">CVC</label>
                        <input type="text" id="card-cvc" class="form-control" placeholder="123" required>
                    </div>
                </div>
            </div>
            
            <div id="transfer-payment-section" style="display: none;">
                <h2>Átutalási információk</h2>
                <div class="form-group">
                    <p>Kérjük, utalja az összeget az alábbi számlaszámra:</p>
                    <p><strong>Számlatulajdonos:</strong> Firestarter Akadémia Kft.</p>
                    <p><strong>Számlaszám:</strong> 12345678-12345678-12345678</p>
                    <p><strong>Közlemény:</strong> <span id="payment-reference">FIR-2025-001</span></p>
                </div>
            </div>
            
            <h2>Számlázási adatok</h2>
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="billing-name">Név</label>
                        <input type="text" id="billing-name" class="form-control" required>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label for="billing-email">Email cím</label>
                        <input type="email" id="billing-email" class="form-control" required>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="billing-address">Cím</label>
                <input type="text" id="billing-address" class="form-control" required>
            </div>
            
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="billing-city">Város</label>
                        <input type="text" id="billing-city" class="form-control" required>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label for="billing-zip">Irányítószám</label>
                        <input type="text" id="billing-zip" class="form-control" required>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="billing-country">Ország</label>
                <select id="billing-country" class="form-control" required>
                    <option value="HU">Magyarország</option>
                </select>
            </div>
            
            <button type="submit">Fizetés</button>
        </form>
    </div>
    
    <script>
        let paymentMethod = 'card';
        
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
        
        function selectPaymentMethod(element, method) {
            document.querySelectorAll('.payment-method').forEach(el => {
                el.classList.remove('active');
            });
            
            element.classList.add('active');
            paymentMethod = method;
            
            if (method === 'card') {
                document.getElementById('card-payment-section').style.display = 'block';
                document.getElementById('transfer-payment-section').style.display = 'none';
            } else {
                document.getElementById('card-payment-section').style.display = 'none';
                document.getElementById('transfer-payment-section').style.display = 'block';
            }
        }
        
        function submitPayment(event) {
            event.preventDefault();
            const formData = {
                payment_method: paymentMethod,
                service_id: <?php echo $service_id; ?>,
                time_slot_id: <?php echo $time_slot_id; ?>,
                billing_name: document.getElementById('billing-name').value,
                billing_email: document.getElementById('billing-email').value,
                billing_address: document.getElementById('billing-address').value,
                billing_city: document.getElementById('billing-city').value,
                billing_zip: document.getElementById('billing-zip').value
            };


            fetch('payment_page.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => {
                return response.text();  
            })
            .then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    throw new Error('');
                }
            })
            .then(data => {
                if (data.success) {
                    alert("Foglalás és fizetés sikeres!");
                    window.location.href = 'profile_page.php?page=courses';
                } else {
                    alert("Hiba történt a foglalás során: " + (data.message || "Ismeretlen hiba"));
                }
            })
            .catch(error => {
                alert("Hiba történt a kérés feldolgozása során: " + error.message);
            });
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