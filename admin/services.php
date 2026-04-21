<?php
include 'auth_check.php';
include '../config/db.php';

$stmt = $pdo->query("
    SELECT service_id, service_name, description, duration_minutes, price, is_active
    FROM services
    ORDER BY service_name ASC
");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

$successMessage = '';

if (isset($_GET['success'])) {
    if ($_GET['success'] === 'added') {
        $successMessage = "Service added successfully!";
    } elseif ($_GET['success'] === 'updated') {
        $successMessage = "Service updated successfully!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Services - Bright Kennel</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=90">
</head>
<body>
<div class="page-wrapper">
    <div class="booking-container admin-dashboard-shell">

        <div class="admin-header-card">
            <div class="admin-header-left">
                <p class="admin-mini-text">Service Management</p>
                <h1>Manage Services</h1>
                <p class="admin-subtitle">Add, edit, and manage salon services.</p>
            </div>
            <div class="admin-header-right">
                <a href="dashboard.php" class="admin-link-btn">Dashboard</a>
                <a href="add_service.php" class="admin-link-btn">Add Service</a>
            </div>
        </div>

        <?php if ($successMessage !== ''): ?>
            <div class="success-message"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>

        <div class="table-wrapper modern-table-wrapper">
            <table class="admin-table modern-admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Service</th>
                        <th>Description</th>
                        <th>Duration</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($services): ?>
                        <?php foreach ($services as $service): ?>
                            <tr>
                                <td>#<?php echo $service['service_id']; ?></td>
                                <td><?php echo htmlspecialchars($service['service_name']); ?></td>
                                <td><?php echo htmlspecialchars($service['description'] ?? '-'); ?></td>
                                <td><?php echo (int)$service['duration_minutes']; ?> min</td>
                                <td>Rs. <?php echo number_format($service['price'], 2); ?></td>
                                <td>
                                    <?php if ($service['is_active']): ?>
                                        <span class="status-badge status-confirmed">Active</span>
                                    <?php else: ?>
                                        <span class="status-badge status-cancelled">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="edit_service.php?id=<?php echo $service['service_id']; ?>" class="small-btn">Edit</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align:center;">No services found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>
</body>
</html>