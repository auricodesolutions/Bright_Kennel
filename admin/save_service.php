<?php
include 'auth_check.php';
include '../config/db.php';

$service_name = trim($_POST['service_name'] ?? '');
$description = trim($_POST['description'] ?? '');
$duration_minutes = (int)($_POST['duration_minutes'] ?? 0);
$price = (float)($_POST['price'] ?? 0);
$is_active = (int)($_POST['is_active'] ?? 1);

if ($service_name === '' || $duration_minutes <= 0 || $price < 0) {
    die("Invalid service data.");
}

$stmt = $pdo->prepare("
    INSERT INTO services (service_name, description, duration_minutes, price, is_active)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->execute([
    $service_name,
    $description !== '' ? $description : null,
    $duration_minutes,
    $price,
    $is_active
]);

header("Location: services.php?success=added");
exit;
?>