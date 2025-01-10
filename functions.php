<?php
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

function isAdmin($user = null) {
    return $user && isset($user['is_admin']) && $user['is_admin'] == 1;
}

function getServices($pdo) {
    $stmt = $pdo->query("SELECT * FROM services ORDER BY name");
    return $stmt->fetchAll();
}

function addService($pdo, $name, $duration, $price) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    
    $stmt = $pdo->prepare("INSERT INTO services (name, duration, price, slug) 
                          VALUES (?, ?, ?, ?)");
    return $stmt->execute([$name, $duration, $price, $slug]);
}

function getService($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function editService($pdo, $id, $name, $duration, $price) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $stmt = $pdo->prepare("UPDATE services SET name = ?, duration = ?, price = ?, slug = ? WHERE id = ?");
    return $stmt->execute([$name, $duration, $price, $slug, $id]);
}

function deleteService($pdo, $id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE service_id = ?");
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Cannot delete service with existing bookings');
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
    $stmt = $pdo->prepare("INSERT INTO time_slots (service_id, date, start_time, end_time, is_available) 
                          VALUES (?, ?, ?, ?, 1)");
    return $stmt->execute([$service_id, $date, $start_time, $end_time]);
}

function getBookings($pdo) {
    $stmt = $pdo->query("SELECT b.*, u.username as client_name, s.name as service_name
                         FROM bookings b
                         JOIN users u ON b.user_id = u.id
                         JOIN services s ON b.service_id = s.id
                         ORDER BY b.date DESC");
    return $stmt->fetchAll();
}

function createBooking($pdo, $user_id, $service_id, $time_slot_id) {
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, service_id, time_slot_id, status) 
                              VALUES (?, ?, ?, 'pending')");
        $stmt->execute([$user_id, $service_id, $time_slot_id]);
        
        $stmt = $pdo->prepare("UPDATE time_slots SET is_available = 0 WHERE id = ?");
        $stmt->execute([$time_slot_id]);
        
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

function updateBookingStatus($pdo, $bookingId, $status) {
    $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    return $stmt->execute([$status, $bookingId]);
}

function sendEmail($to, $subject, $message) {
    $headers = 'From: noreply@example.com' . "\r\n" .
               'Reply-To: noreply@example.com' . "\r\n" .
               'X-Mailer: PHP/' . phpversion();

    return mail($to, $subject, $message, $headers);
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
        throw new Exception('Failed to update settings: ' . $e->getMessage());
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