<?php
include 'auth_check.php';
include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: bookings.php");
    exit;
}

$appointment_id = (int)($_POST['appointment_id'] ?? 0);
$action = $_POST['action'] ?? '';
$duration_minutes = (int)($_POST['duration_minutes'] ?? 15);

if ($appointment_id <= 0 || $action === '') {
    header("Location: bookings.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM appointments WHERE appointment_id = ? LIMIT 1");
$stmt->execute([$appointment_id]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    header("Location: bookings.php");
    exit;
}

/* Update payment summary */
if ($action === 'update_payment') {
    $advance_amount = (float)($_POST['advance_amount'] ?? 0);
    $total_price = (float)$booking['total_price'];

    if ($advance_amount < 0 || $advance_amount > $total_price) {
        header("Location: view_booking.php?id=" . $appointment_id . "&error=invalid_advance");
        exit;
    }

    $paymentStmt = $pdo->prepare("
        UPDATE appointments
        SET advance_amount = ?
        WHERE appointment_id = ?
    ");
    $paymentStmt->execute([$advance_amount, $appointment_id]);

    header("Location: view_booking.php?id=" . $appointment_id . "&success=payment_updated");
    exit;
}

/* Do not allow status changes after completed */
if ($booking['status'] === 'completed') {
    header("Location: view_booking.php?id=" . $appointment_id . "&error=completed_locked");
    exit;
}

$message = '';

if ($action === 'confirm') {
    $startDateTime = strtotime($booking['appointment_date'] . ' ' . $booking['start_time']);
    $endDateTime = $startDateTime + ($duration_minutes * 60);
    $end_time = date("H:i:s", $endDateTime);

    $checkStmt = $pdo->prepare("
        SELECT appointment_id
        FROM appointments
        WHERE appointment_id != ?
          AND appointment_date = ?
          AND status = 'confirmed'
          AND (? < end_time AND ? > start_time)
        LIMIT 1
    ");
    $checkStmt->execute([
        $appointment_id,
        $booking['appointment_date'],
        $booking['start_time'],
        $end_time
    ]);

    if ($checkStmt->fetch()) {
        header("Location: view_booking.php?id=" . $appointment_id . "&error=overlap");
        exit;
    }

    $updateStmt = $pdo->prepare("
        UPDATE appointments
        SET status = 'confirmed',
            confirmed_duration_minutes = ?,
            end_time = ?
        WHERE appointment_id = ?
    ");
    $updateStmt->execute([$duration_minutes, $end_time, $appointment_id]);

    $message = "confirmed";
}

if ($action === 'complete') {
    $updateStmt = $pdo->prepare("
        UPDATE appointments
        SET status = 'completed'
        WHERE appointment_id = ?
    ");
    $updateStmt->execute([$appointment_id]);

    $message = "completed";
}

if ($action === 'cancel') {
    $updateStmt = $pdo->prepare("
        UPDATE appointments
        SET status = 'cancelled'
        WHERE appointment_id = ?
    ");
    $updateStmt->execute([$appointment_id]);

    $message = "cancelled";
}

header("Location: view_booking.php?id=" . $appointment_id . "&success=" . $message);
exit;
?>