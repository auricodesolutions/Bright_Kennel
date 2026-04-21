<?php
include 'auth_check.php';
include '../config/db.php';

$pendingCount = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'pending'")->fetchColumn();
$confirmedCount = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'confirmed'")->fetchColumn();
$completedCount = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'completed'")->fetchColumn();
$cancelledCount = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'cancelled'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Bright Kennel</title>
<link rel="stylesheet" href="../assets/css/style.css?v=70">
</head>
<body>
    <div class="page-wrapper">
        <div class="booking-container admin-dashboard-shell">

            <div class="admin-header-card">
                <div class="admin-header-left">
                    <p class="admin-mini-text">Admin Panel</p>
                    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></h1>
                    <p class="admin-subtitle">Manage bookings, confirmations, and customer appointments easily.</p>
                </div>
                <div class="admin-header-right">
                    <a href="bookings.php" class="admin-link-btn">Manage Bookings</a>
                    <a href="services.php" class="admin-link-btn">Manage Services</a>
                    <a href="blocked_slots.php" class="admin-link-btn">Blocked Times</a>
                    <a href="logout.php" class="admin-link-btn danger-outline-btn">Logout</a>
                </div>
            </div>

            <div class="admin-cards modern-admin-cards">
                <div class="admin-card admin-card-pending">
                    <span class="admin-card-label">Pending</span>
                    <p><?php echo $pendingCount; ?></p>
                </div>
                <div class="admin-card admin-card-confirmed">
                    <span class="admin-card-label">Confirmed</span>
                    <p><?php echo $confirmedCount; ?></p>
                </div>
                <div class="admin-card admin-card-completed">
                    <span class="admin-card-label">Completed</span>
                    <p><?php echo $completedCount; ?></p>
                </div>
                <div class="admin-card admin-card-cancelled">
                    <span class="admin-card-label">Cancelled</span>
                    <p><?php echo $cancelledCount; ?></p>
                </div>
            </div>

        </div>
    </div>
</body>
</html>