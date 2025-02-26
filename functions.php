<?php
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

function isInstructor($user = null) {
    return $user && isset($user['is_instructor']) && $user['is_instructor'] == 1;
}

function isAdmin($user = null) {
    return $user && isset($user['is_admin']) && $user['is_admin'] == 1;
}

function getServices($pdo) {
    $stmt = $pdo->query("SELECT * FROM services ORDER BY name");
    return $stmt->fetchAll();
}

function addService($pdo, $name, $duration, $price) {
    try {
        $stmt = $pdo->prepare("INSERT INTO services (name, duration, price) 
                              VALUES (?, ?, ?)");
        return $stmt->execute([$name, $duration, $price]);
    } catch (PDOException $e) {
        error_log("Hiba a szolgáltatás hozzáadásakor: " . $e->getMessage());
        return false;
    }
}

function getService($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function editService($pdo, $id, $name, $duration, $price) {
    $stmt = $pdo->prepare("UPDATE services 
                          SET name = ?, duration = ?, price = ? 
                          WHERE id = ?");
    return $stmt->execute([$name, $duration, $price, $id]);
}

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

function getAvailableTimeSlots($pdo, $service_id, $date) {
    $stmt = $pdo->prepare("SELECT * FROM time_slots 
                          WHERE service_id = ? 
                          AND date = ? 
                          AND is_available = 1
                          ORDER BY start_time");
    $stmt->execute([$service_id, $date]);
    return $stmt->fetchAll();
}

function addTimeSlot($pdo, $service_id, $date, $start_time, $end_time) {
    try {
        $formatted_date = date('Y-m-d', strtotime($date));
        $formatted_start = date('H:i:s', strtotime($start_time));
        $formatted_end = date('H:i:s', strtotime($end_time));
        
        if ($formatted_start >= $formatted_end) {
            throw new Exception('A kezdő időpont nem lehet későbbi vagy egyenlő a befejező időpontnál');
        }

        $stmt = $pdo->prepare("INSERT INTO time_slots (service_id, date, start_time, end_time, is_available) 
                              VALUES (?, ?, ?, ?, 1)");
        return $stmt->execute([$service_id, $formatted_date, $formatted_start, $formatted_end]);
    } catch (Exception $e) {
        error_log("Hiba az időpont hozzáadásakor: " . $e->getMessage());
        return false;
    }
}

function getBookings($pdo) {
    $stmt = $pdo->query("
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
        ORDER BY t.date DESC, t.start_time ASC
    ");
    return $stmt->fetchAll();
}

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

function updateBookingStatus($pdo, $bookingId, $status) {
    $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    return $stmt->execute([$status, $bookingId]);
}

function sendEmail($to, $subject, $message) {

}

function updateSettings($pdo, $settings) {
    try {
        foreach ($settings as $key => $value) {
            if ($key === 'email_notifications' || $key === 'sms_notifications') {
                $value = $value ? 'true' : 'false';
            }
            $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
            $stmt->execute([$value, $key]);
        }
        return true;
    } catch (Exception $e) {
        throw new Exception('A beállítások frissítése nem történt meg: ' . $e->getMessage());
    }
}

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
            'work_day_end' => '17:00',
            'email_notifications' => 'true',
            'sms_notifications' => 'false'
        ];
        
        foreach ($settings as $key => $value) {
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
            $stmt->execute([$key, $value]);
        }
    }
    
    return $settings;
}

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
