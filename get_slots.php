<?php
include 'config/db.php';

header('Content-Type: application/json');

$appointment_date = $_GET['appointment_date'] ?? '';

if ($appointment_date === '') {
    echo json_encode([]);
    exit;
}

$slot_interval = 15;
$open_time = "09:00:00";
$close_time = "18:00:00";

$slots = [];

$start_timestamp = strtotime($appointment_date . ' ' . $open_time);
$close_timestamp = strtotime($appointment_date . ' ' . $close_time);

while ($start_timestamp < $close_timestamp) {
    $slot_start = date("H:i:s", $start_timestamp);
    $slot_label = date("g:i A", $start_timestamp);

    $status = "available";

    $confirmedStmt = $pdo->prepare("
        SELECT appointment_id
        FROM appointments
        WHERE appointment_date = ?
          AND status = 'confirmed'
          AND end_time IS NOT NULL
          AND ? >= start_time
          AND ? < end_time
        LIMIT 1
    ");
    $confirmedStmt->execute([$appointment_date, $slot_start, $slot_start]);

    if ($confirmedStmt->fetch(PDO::FETCH_ASSOC)) {
        $status = "booked";
    }

    $blockedStmt = $pdo->prepare("
        SELECT blocked_id
        FROM blocked_slots
        WHERE blocked_date = ?
          AND ? >= start_time
          AND ? < end_time
        LIMIT 1
    ");
    $blockedStmt->execute([$appointment_date, $slot_start, $slot_start]);

    if ($blockedStmt->fetch(PDO::FETCH_ASSOC)) {
        $status = "blocked";
    }

    $slots[] = [
        "label" => $slot_label,
        "start_time" => $slot_start,
        "status" => $status
    ];

    $start_timestamp += ($slot_interval * 60);
}

echo json_encode($slots);
?>