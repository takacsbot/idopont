<?php
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//////////////////////////////////////////////////////////////////////////////////////////////
//
//Description:
//  Check if user is logged in and set session variables.
//
//Input: 
//  $pdo - PDO object
//
//Output: 
//  $user - user data
//
//////////////////////////////////////////////////////////////////////////////////////////////
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
            return $user;
        }
    }
    return false;
}

//////////////////////////////////////////////////////////////////////////////////////////////
//
//Description:
//  Check if user is an instructor.
//
//Input: 
//  $user - user data
//
//Output: 
//  boolean
//
//////////////////////////////////////////////////////////////////////////////////////////////
function isInstructor($user = null) {
    return $user && isset($user['is_instructor']) && $user['is_instructor'] == 1;
}

//////////////////////////////////////////////////////////////////////////////////////////////
//
//Description:
//  Check if user is an admin.
//
//Input: 
//  $user - user data
//
//Output: 
//  boolean
//
//////////////////////////////////////////////////////////////////////////////////////////////
function isAdmin($user = null) {
    return $user && isset($user['is_admin']) && $user['is_admin'] == 1;
}

//////////////////////////////////////////////////////////////////////////////////////////////
//
//Description:
//  Return services.
//
//Input: 
//  $pdo - PDO object
//  $instructor - instructor data

//
//Output: 
//  $services - services
//
//////////////////////////////////////////////////////////////////////////////////////////////
function getServices($pdo, $instructor = null) {
    if ($instructor) {
        $stmt = $pdo->prepare("SELECT * FROM services WHERE instructor_id = ? ORDER BY name");
        $stmt->execute([$instructor['id']]);
        return $stmt->fetchAll();
    } else {
        $services = $pdo->query("SELECT * FROM services ORDER BY name");
        return $services->fetchAll();
    }
}

//////////////////////////////////////////////////////////////////////////////////////////////
//
//Description:
//  Add a new service to the database.
//
//Input: 
//  $pdo - PDO object
//  $instructor - instructor data
//  $name - service name
//  $duration - service duration
//  $price - service price
//
//Output: 
//  boolean
//
//////////////////////////////////////////////////////////////////////////////////////////////
function addService($pdo, $instructor, $name, $duration, $price, $image) {
    try {
        $allowed_types = ['jpg'];
        $file_extension = strtolower(pathinfo($image["name"], PATHINFO_EXTENSION));
        if (!in_array($file_extension, $allowed_types)) {
            throw new Exception('Engedélyezett fájl típusok: jpg');
        }

        if (!move_uploaded_file($image["tmp_name"], './pictures_from_training_courses/' . $name . '.' . $file_extension)) {
            throw new Exception('Failed to move uploaded file');
        }
                $stmt = $pdo->prepare("INSERT INTO services (name, instructor_id, duration, price) 
                              VALUES (?, ?, ?, ?)");
        return $stmt->execute([$name, $instructor['id'], $duration, $price]);
    } catch (PDOException $e) {
        error_log("Hiba a szolgáltatás hozzáadásakor: " . $e->getMessage());
        return false;
    }
}

//////////////////////////////////////////////////////////////////////////////////////////////
//
//Description:
//  Get service details by ID.
//
//Input: 
//  $pdo - PDO object
//  $id - service ID
//
//Output: 
//  service data array or false if not found.
//
//////////////////////////////////////////////////////////////////////////////////////////////
function getService($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

//////////////////////////////////////////////////////////////////////////////////////////////
//
//Description:
//  Update an existing service.
//
//Input: 
//  $pdo - PDO object
//  $id - service ID
//  $name - service name
//  $duration - service duration
//  $price - service price
//
//Output: 
//  boolean
//
//////////////////////////////////////////////////////////////////////////////////////////////
function editService($pdo, $id, $name, $duration, $price) {
    $stmt = $pdo->prepare("UPDATE services 
                          SET name = ?, duration = ?, price = ? 
                          WHERE id = ?");
    return $stmt->execute([$name, $duration, $price, $id]);
}

//////////////////////////////////////////////////////////////////////////////////////////////
//
//Description:
//  Delete a service if it has no bookings.
//
//Input: 
//  $pdo - PDO object
//  $id - service ID.
//
//Output: 
//  boolean
//  throws Exception if service has bookings
//
//////////////////////////////////////////////////////////////////////////////////////////////
function deleteService($pdo, $id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE service_id = ?");
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('A szolgáltatás nem törölhető, mert már vannak foglalások hozzá');
    }
    $stmt = $pdo->prepare("DELETE FROM time_slots WHERE service_id = ?");
    $stmt->execute([$id]);
    
    $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
    return $stmt->execute([$id]);
}

//////////////////////////////////////////////////////////////////////////////////////////////
//
//Description:
//  Get available time slots for a service on a specific date.
//
//Input: 
//  $pdo - PDO object
//  $service_id - service ID, $date - date string.
//
//Output: 
//  array of available time slots.
//
//////////////////////////////////////////////////////////////////////////////////////////////
function getAvailableTimeSlots($pdo, $service_id, $date) {
    $stmt = $pdo->prepare("SELECT * FROM time_slots 
                          WHERE service_id = ? 
                          AND date = ? 
                          AND is_available = 1
                          ORDER BY start_time");
    $stmt->execute([$service_id, $date]);
    return $stmt->fetchAll();
}

//////////////////////////////////////////////////////////////////////////////////////////////
//
//Description:
//  Add a new time slot for a service.
//
//Input:
//  $pdo - PDO object
//  $service_id - service ID
//  $date - date string
//  $start_time - start time string
//  $end_time - end time string
//
//Output:
//  boolean
//
//////////////////////////////////////////////////////////////////////////////////////////////
function addTimeSlot($pdo, $service_id, $date, $start_time, $end_time) {
    try {
        $formatted_date = date('Y-m-d', strtotime($date));
        $formatted_start = date('H:i:s', strtotime($start_time));
        $formatted_end = date('H:i:s', strtotime($end_time));
        
        $stmt = $pdo->prepare("SELECT duration FROM services WHERE id = ?");
        $stmt->execute([$service_id]);
        $service = $stmt->fetch();
        
        if (!$service) {
            throw new Exception('A szolgáltatás nem található');
        }
        
        $start_datetime = strtotime($formatted_start);
        $end_datetime = strtotime($formatted_end);
        $duration_minutes = ($end_datetime - $start_datetime) / 60;

        if ($duration_minutes != $service['duration']) {
            throw new Exception("Az időpont hossza nem egyezik meg a szolgáltatás időtartamával ({$service['duration']} perc)");
        }
        if ($formatted_start >= $formatted_end) {
            throw new Exception('A kezdő időpont nem lehet későbbi vagy egyenlő a befejező időpontnál');
        }

        $stmt = $pdo->prepare("INSERT INTO time_slots (service_id, date, start_time, end_time, is_available) 
                              VALUES (?, ?, ?, ?, 1)");
        return $stmt->execute([$service_id, $formatted_date, $formatted_start, $formatted_end]);
    } catch (Exception $e) {
        error_log("Hiba az időpont hozzáadásakor: " . $e->getMessage());
        throw $e;
    }
}

//////////////////////////////////////////////////////////////////////////////////////////////
//
//Description:
//  Get all bookings with client and service details.
//
//Input:
//  $pdo - PDO object
//  $instructor - instructor data
//
//Output:
//  array of booking data.
//
//////////////////////////////////////////////////////////////////////////////////////////////
function getBookings($pdo, $instructor = null) {
    $stmt = $pdo->prepare("
    SELECT 
        b.*,
        u.username as client_name,
        s.name as service_name,
        t.start_time,
        t.end_time,
        t.date,
        DATE_FORMAT(t.date, '%Y-%m-%d') as formatted_date,
        DATE_FORMAT(t.start_time, '%H:%i') as formatted_start_time,
        DATE_FORMAT(t.end_time, '%H:%i') as formatted_end_time
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN services s ON b.service_id = s.id
    JOIN time_slots t ON b.time_slot_id = t.id
    WHERE s.instructor_id = ?
    ORDER BY t.date DESC, t.start_time ASC");
    $stmt->execute([$instructor['id']]);
    return $stmt->fetchAll();

}

//////////////////////////////////////////////////////////////////////////////////////////////
//
//Description:
//  Create a new booking and mark time slot as unavailable.
//
//Input:
//  $pdo - PDO object
//  $user_id - user ID
//  $service_id - service ID
//  $time_slot_id - time slot ID
//
//Output:
//  boolean
//
//////////////////////////////////////////////////////////////////////////////////////////////
function createBooking($pdo, $user_id, $service_id, $time_slot_id) {
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("SELECT date, start_time FROM time_slots WHERE id = ?");
        $stmt->execute([$time_slot_id]);
        $timeSlot = $stmt->fetch();
        
        if (!$timeSlot) {
            throw new Exception('Időpont nem található');
        }
        
        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, service_id, time_slot_id, date, status) 
                              VALUES (?, ?, ?, ?, 'pending')");
        $stmt->execute([
            $user_id, 
            $service_id, 
            $time_slot_id,
            $timeSlot['date']
        ]);
        
        $stmt = $pdo->prepare("UPDATE time_slots SET is_available = 0 WHERE id = ?");
        $stmt->execute([$time_slot_id]);
        
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Hiba a foglalás létrehozásakor: " . $e->getMessage());
        return false;
    }
}

//////////////////////////////////////////////////////////////////////////////////////////////
//
//Description:
//  Update a booking's status.
//
//Input: $pdo - PDO object
//  $bookingId - booking ID
//  $status - new status string
//
//Output:
//  boolean
//
//////////////////////////////////////////////////////////////////////////////////////////////
function updateBookingStatus($pdo, $bookingId, $status) {
    $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    return $stmt->execute([$status, $bookingId]);
}

//////////////////////////////////////////////////////////////////////////////////////////////
//
//Description:
//  Send an email.
//
//Input:
//  $to - recipient email
//  $subject - email subject
//  $message - email body
//
//Output:
//  boolean
//
//////////////////////////////////////////////////////////////////////////////////////////////
function sendEmail($to, $subject, $message) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();                                          
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;                             
        $mail->Username   = 'firestarterakademia@gmail.com'; 
        $mail->Password   = 'muvn svge ipdv ykqr';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('firestarterakademia@gmail.com', mb_encode_mimeheader('Firestarter Akadémia', 'UTF-8'));
        $mail->addAddress($to);

        $mail->isHTML(true);
        $htmlMessage = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>' . htmlspecialchars($subject) . '</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333333;
                    margin: 0;
                    padding: 0;
                    background-color: #f4f4f4;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    background-color: #ffffff;
                    border-radius: 5px;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                }
                .header {
                    background-color: #ff5722;
                    color: #ffffff;
                    padding: 20px;
                    text-align: center;
                    border-top-left-radius: 5px;
                    border-top-right-radius: 5px;
                }
                .content {
                    padding: 20px;
                }
                .footer {
                    text-align: center;
                    padding: 10px;
                    font-size: 12px;
                    color: #777777;
                    border-top: 1px solid #dddddd;
                }
                .button {
                    display: inline-block;
                    padding: 10px 20px;
                    background-color: #ff5722;
                    color: #ffffff;
                    text-decoration: none;
                    border-radius: 3px;
                    margin-top: 15px;
                }
                .password-box {
                    background-color: #f0f0f0;
                    border: 1px dashed #cccccc;
                    padding: 10px;
                    margin: 15px 0;
                    font-family: monospace;
                    font-size: 18px;
                    text-align: center;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Firestarter Akadémia</h1>
                </div>
                <div class="content">
                    ' . nl2br($message) . '
                </div>
                <div class="footer">
                    <p>&copy; ' . date('Y') . ' Firestarter Akadémia. Minden jog fenntartva.</p>
                </div>
            </div>
        </body>
        </html>
        ';
        $mail->Subject = mb_encode_mimeheader($subject, 'UTF-8');
        $mail->Body    = nl2br($htmlMessage);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Az emailt nem lehetett elküldeni: {$mail->ErrorInfo}");
        return false;
    }
}

//////////////////////////////////////////////////////////////////////////////////////////////
//
//Description
//  Update application settings.
//
//Input:
//  $pdo - PDO object
//  $settings - array of settings.
//
//Output:
//  boolean
//  throws Exception on failure
//
//////////////////////////////////////////////////////////////////////////////////////////////
function updateSettings($pdo, $settings) {
    try {
        foreach ($settings as $key => $value) {
            $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
            $stmt->execute([$value, $key]);
        }
        return true;
    } catch (Exception $e) {
        throw new Exception('A beállítások frissítése nem történt meg: ' . $e->getMessage());
    }
}

//////////////////////////////////////////////////////////////////////////////////////////////
//
//Description:
//  Get all application settings.
//
//Input:
//  $pdo - PDO object
//
//Output:
//  array of settings
//
//////////////////////////////////////////////////////////////////////////////////////////////
function getSettings($pdo) {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    $rows = $stmt->fetchAll();
    
    $settings = [];
    foreach ($rows as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    
    if (!$settings) {
        $settings = [
            'min_advance_hours' => 24,
            'max_advance_days' => 60,
            'work_day_start' => '09:00',
            'work_day_end' => '17:00'
        ];
        
        foreach ($settings as $key => $value) {
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
            $stmt->execute([$key, $value]);
        }
    }
    
    return $settings;
}

//////////////////////////////////////////////////////////////////////////////////////////////
//
//Description:
//  Get bookings for a specific user.
//
//Input:
//  $pdo - PDO object
//  $user_id - user ID
//
//Output:
//  array of user's booking data
//
//////////////////////////////////////////////////////////////////////////////////////////////
function getUserBookings($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT 
            b.*,
            s.name as service_name,
            t.start_time,
            t.end_time,
            t.date,
            DATE_FORMAT(t.date, '%Y-%m-%d') as formatted_date,
            DATE_FORMAT(t.start_time, '%H:%i') as formatted_start_time,
            DATE_FORMAT(t.end_time, '%H:%i') as formatted_end_time
        FROM bookings b
        JOIN services s ON b.service_id = s.id
        JOIN time_slots t ON b.time_slot_id = t.id
        WHERE b.user_id = ?
        ORDER BY t.date DESC, t.start_time ASC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

//////////////////////////////////////////////////////////////////////////////////////////////
//
//Description:
//  Cancel a booking for a user.
//
//Input:
//  $pdo - PDO object
//  $booking_id - booking ID
//  $user_id - user ID
//
//Output:
//  boolean
//  throws Exception on failure
//
//////////////////////////////////////////////////////////////////////////////////////////////
function cancelBooking($pdo, $booking_id, $user_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ?");
        $stmt->execute([$booking_id, $user_id]);
        $booking = $stmt->fetch();
        
        if (!$booking) {
            throw new Exception('Érvénytelen foglalás');
        }
        
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
        $stmt->execute([$booking_id]);
        
        return true;
    } catch(Exception $e) {
        error_log("Hiba a foglalás lemondásakor: " . $e->getMessage());
        throw $e;
    }
}

//////////////////////////////////////////////////////////////////////////////////////////////
//
//Description:
//  Delete bookings older than one week.
//
//Input:
//  $pdo - PDO object
//
//Output:
//  boolean
//
//////////////////////////////////////////////////////////////////////////////////////////////
function deleteOldBookings($pdo) {
    try {
        $stmt = $pdo->prepare("DELETE FROM bookings WHERE date < DATE_SUB(NOW(), INTERVAL 1 WEEK)");
        $stmt->execute();
        return true;
    } catch(Exception $e) {
        error_log("Hiba a régi foglalások közbe: " . $e->getMessage());
        return false;
    }
}

//////////////////////////////////////////////////////////////////////////////////////////////
//
//Description:
//  Send a booking email.
//
//Input:
//  $to - recipient email
//  $bookingId - booking ID
//  $status - booking status
//  $courseName - course name
//  $courseTime - course time
//
//Output:
//  boolean
//
//////////////////////////////////////////////////////////////////////////////////////////////
function sendBookingEmail($to, $bookingId, $status, $courseName, $courseTime) {
    $subject = $status === 'confirmed' ? 'Foglalás megerősítése' : 'Foglalás lemondása';
    $message = $status === 'confirmed' 
        ? "Kedves Felhasználó,<br><br>A foglalása megerősítve lett.<br>Kurzus neve: $courseName<br>Időpont: $courseTime<br><br>Köszönjük, hogy minket választott!<br><br>Üdvözlettel,<br>Firestarter Akadémia"
        : "Kedves Felhasználó,<br><br>A foglalása lemondásra került.<br>Kurzus neve: $courseName<br>Időpont: $courseTime<br><br>Ha bármilyen kérdése van, kérjük, lépjen kapcsolatba velünk.<br><br>Üdvözlettel,<br>Firestarter Akadémia";

    return sendEmail($to, $subject, $message);
} 
