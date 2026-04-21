<?php
include 'auth_check.php';
include '../config/db.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT service_id, service_name, description, duration_minutes, price, is_active
    FROM services
    WHERE service_id = ?
    LIMIT 1
");
$stmt->execute([$id]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$service) {
    die("Service not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Service - Bright Kennel</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=90">
</head>
<body>
<div class="page-wrapper">
    <div class="booking-container" style="max-width:700px;">
        <div class="admin-header-card">
            <div class="admin-header-left">
                <p class="admin-mini-text">Service Management</p>
                <h1>Edit Service</h1>
            </div>
            <div class="admin-header-right">
                <a href="services.php" class="admin-link-btn">Back</a>
            </div>
        </div>

        <form method="POST" action="update_service.php" class="booking-form">
            <input type="hidden" name="service_id" value="<?php echo $service['service_id']; ?>">

            <div class="form-group">
                <label>Service Name</label>
                <input type="text" name="service_name" value="<?php echo htmlspecialchars($service['service_name']); ?>" required>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description"><?php echo htmlspecialchars($service['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label>Duration (minutes)</label>
                <input type="number" name="duration_minutes" min="1" value="<?php echo (int)$service['duration_minutes']; ?>" required>
            </div>

            <div class="form-group">
                <label>Price</label>
                <input type="number" step="0.01" name="price" min="0" value="<?php echo $service['price']; ?>" required>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="is_active">
                    <option value="1" <?php echo $service['is_active'] ? 'selected' : ''; ?>>Active</option>
                    <option value="0" <?php echo !$service['is_active'] ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <button type="submit" class="book-btn">Update Service</button>
        </form>
    </div>
</div>
</body>
</html>