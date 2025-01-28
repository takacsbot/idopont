<?php

$stmt = $pdo->prepare("
    SELECT 
        oc.id as order_id,
        c.name as course_name,
        oc.start_date,
        oc.status,
        oc.modification_request
    FROM ordered_courses oc
    JOIN courses c ON oc.course_id = c.id
    WHERE oc.user_id = ?
    ORDER BY oc.start_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$ordered_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_modification'])) {
    $order_id = $_POST['order_id'];
    $new_date = $_POST['new_date'];
    $request_type = $_POST['request_type'];

    if ($request_type === 'reschedule') {
        $stmt = $pdo->prepare("
            UPDATE ordered_courses 
            SET modification_request = ?, modification_status = 'pending' 
            WHERE id = ? AND user_id = ?
        ");
        $request_message = "Időpont módosítási kérelem: " . $new_date;
        $stmt->execute([$request_message, $order_id, $_SESSION['user_id']]);
        $modification_success = "Időpont módosítási kérelem elküldve!";
    } elseif ($request_type === 'cancel') {
        $stmt = $pdo->prepare("
            UPDATE ordered_courses 
            SET modification_request = 'Lemondási kérelem', 
                modification_status = 'pending' 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$order_id, $_SESSION['user_id']]);
        $modification_success = "Lemondási kérelem elküldve!";
    }
}
?>

<div class="ordered-courses">
    <h2>Megrendelt Kurzusok</h2>
    <?php if (isset($modification_success)): ?>
        <p class="success-message"><?php echo $modification_success; ?></p>
    <?php endif; ?>

    <?php if (empty($ordered_courses)): ?>
        <p class="no-courses">Még nem rendeltél kurzust.</p>
    <?php else: ?>
        <div class="courses-list">
            <?php foreach ($ordered_courses as $course): ?>
                <div class="course-item">
                    <div class="course-info">
                        <h3><?php echo htmlspecialchars($course['course_name']); ?></h3>
                        <p class="course-date">
                            <strong>Kezdés:</strong>
                            <?php echo date('Y. m. d.', strtotime($course['start_date'])); ?>
                        </p>
                        <p class="course-status">
                            <strong>Státusz:</strong>
                            <span class="status-<?php echo strtolower($course['status']); ?>">
                                <?php
                                switch ($course['status']) {
                                    case 'active':
                                        echo 'Aktív';
                                        break;
                                    case 'pending':
                                        echo 'Függőben';
                                        break;
                                    case 'completed':
                                        echo 'Teljesítve';
                                        break;
                                    default:
                                        echo htmlspecialchars($course['status']);
                                }
                                ?>
                            </span>
                        </p>
                        <?php if ($course['modification_request']): ?>
                            <p class="modification-pending">
                                Függőben lévő kérelem: <?php echo htmlspecialchars($course['modification_request']); ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <?php if ($course['status'] === 'active' && !$course['modification_request']): ?>
                        <div class="course-actions">
                            <button class="modify-button"
                                onclick="showModificationForm('<?php echo $course['order_id']; ?>')">
                                Módosítás/Lemondás
                            </button>
                        </div>

                        <div class="modification-form" id="form-<?php echo $course['order_id']; ?>">
                            <form method="POST">
                                <input type="hidden" name="order_id"
                                    value="<?php echo $course['order_id']; ?>">

                                <select name="request_type" class="request-type-select" required>
                                    <option value="">Válassz műveletet</option>
                                    <option value="reschedule">Időpont módosítás</option>
                                    <option value="cancel">Lemondás</option>
                                </select>

                                <div class="date-input" id="date-input-<?php echo $course['order_id']; ?>">
                                    <input type="date" name="new_date"
                                        min="<?php echo date('Y-m-d'); ?>">
                                </div>

                                <button type="submit" name="submit_modification"
                                    class="submit-modification">
                                    Kérelem beküldése
                                </button>
                            </form>
                        </div>
                        <script>
                            function showModificationForm(orderId) {
                                const form = document.getElementById(`form-${orderId}`);
                                const dateInput = document.getElementById(`date-input-${orderId}`);
                                form.classList.toggle('active');
                                const selectElement = form.querySelector('.request-type-select');
                                selectElement.addEventListener('change', function() {
                                    if (this.value === 'reschedule') {
                                        dateInput.style.display = 'block';
                                        dateInput.querySelector('input').required = true;
                                    } else {
                                        dateInput.style.display = 'none';
                                        dateInput.querySelector('input').required = false;
                                    }
                                });
                            }
                        </script>

                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>