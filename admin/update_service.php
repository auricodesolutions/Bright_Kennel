<?php
include 'auth_check.php';
include '../config/db.php';

$service_id = (int)($_POST['service_id'] ?? 0);
$service_name = trim($_POST['service_name'] ?? '');
$description = trim($_POST['description'] ?? '');
$duration_minutes = (int)($_POST['duration_minutes'] ?? 0);
$price = (float)($_POST['price'] ?? 0);
$is_active = (int)($_POST['is_active'] ?? 1);

if ($service_id <= 0 || $service_name === '' || $duration_minutes <= 0 || $price < 0) {
    die("Invalid service data.");
}

$stmt = $pdo->prepare("
    UPDATE services
    SET service_name = ?, description = ?, duration_minutes = ?, price = ?, is_active = ?
    WHERE service_id = ?
");
$stmt->execute([
    $service_name,
    $description !== '' ? $description : null,
    $duration_minutes,
    $price,
    $is_active,
    $service_id
]);

header("Location: services.php?success=updated");
exit;
?>