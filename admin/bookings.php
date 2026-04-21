<?php
include 'auth_check.php';
include '../config/db.php';

$allowedStatuses = ['pending', 'confirmed', 'completed', 'cancelled'];

$status = $_GET['status'] ?? 'all';
$filter_date = $_GET['filter_date'] ?? '';

$sql = "
    SELECT 
        a.appointment_id,
        a.appointment_date,
        a.start_time,
        a.end_time,
        a.status,
        a.confirmed_duration_minutes,
        c.full_name AS customer_name,
        p.pet_name,
        s.service_name
    FROM appointments a
    JOIN customers c ON a.customer_id = c.customer_id
    JOIN pets p ON a.pet_id = p.pet_id
    JOIN appointment_services aps ON a.appointment_id = aps.appointment_id
    JOIN services s ON aps.service_id = s.service_id
    WHERE 1=1
";

$params = [];

if (in_array($status, $allowedStatuses)) {
    $sql .= " AND a.status = ? ";
    $params[] = $status;
}

if ($filter_date !== '') {
    $sql .= " AND a.appointment_date = ? ";
    $params[] = $filter_date;
}

$sql .= " ORDER BY a.appointment_date DESC, a.start_time DESC ";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Bright Kennel</title>
<link rel="stylesheet" href="../assets/css/style.css?v=70">
</head>
<body>
    <div class="page-wrapper">
        <div class="booking-container admin-dashboard-shell">

            <div class="admin-header-card">
                <div class="admin-header-left">
                    <p class="admin-mini-text">Booking Management</p>
                    <h1>Manage Bookings</h1>
                    <p class="admin-subtitle">Filter bookings by status and date, then open each record to confirm or update it.</p>
                </div>
                <div class="admin-header-right">
                    <a href="dashboard.php" class="admin-link-btn">Dashboard</a>
                </div>
            </div>

            <div class="admin-menu stylish-filter-menu">
                <a href="bookings.php?status=all&filter_date=<?php echo urlencode($filter_date); ?>" class="admin-link-btn <?php echo $status === 'all' ? 'active-filter' : ''; ?>">All</a>
                <a href="bookings.php?status=pending&filter_date=<?php echo urlencode($filter_date); ?>" class="admin-link-btn <?php echo $status === 'pending' ? 'active-filter' : ''; ?>">Pending</a>
                <a href="bookings.php?status=confirmed&filter_date=<?php echo urlencode($filter_date); ?>" class="admin-link-btn <?php echo $status === 'confirmed' ? 'active-filter' : ''; ?>">Confirmed</a>
                <a href="bookings.php?status=completed&filter_date=<?php echo urlencode($filter_date); ?>" class="admin-link-btn <?php echo $status === 'completed' ? 'active-filter' : ''; ?>">Completed</a>
                <a href="bookings.php?status=cancelled&filter_date=<?php echo urlencode($filter_date); ?>" class="admin-link-btn <?php echo $status === 'cancelled' ? 'active-filter' : ''; ?>">Cancelled</a>
            </div>

            <form method="GET" class="filter-form modern-filter-form">
                <div class="filter-row">
                    <div class="form-group">
                        <label for="status">Booking Status</label>
                        <select name="status" id="status">
                            <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>All</option>
                            <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="confirmed" <?php echo $status === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="filter_date">Appointment Date</label>
                        <input type="date" name="filter_date" id="filter_date" value="<?php echo htmlspecialchars($filter_date); ?>">
                    </div>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="admin-link-btn">Apply Filters</button>
                    <a href="bookings.php" class="admin-link-btn clear-btn">Clear</a>
                </div>
            </form>

            <div class="table-wrapper modern-table-wrapper desktop-bookings-table">
                <table class="admin-table modern-admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Owner</th>
                            <th>Pet</th>
                            <th>Service</th>
                            <th>Date</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($bookings) > 0): ?>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td>#<?php echo $booking['appointment_id']; ?></td>
                                    <td><?php echo htmlspecialchars($booking['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['pet_name']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['service_name']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['appointment_date']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['start_time']); ?></td>
                                    <td><?php echo $booking['end_time'] ? htmlspecialchars($booking['end_time']) : '-'; ?></td>
                                    <td><?php echo $booking['confirmed_duration_minutes'] ? $booking['confirmed_duration_minutes'] . ' min' : '-'; ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo htmlspecialchars($booking['status']); ?>">
                                            <?php echo htmlspecialchars(ucfirst($booking['status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="view_booking.php?id=<?php echo $booking['appointment_id']; ?>" class="small-btn">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" style="text-align:center;">No bookings found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="mobile-booking-cards">
                <?php if (count($bookings) > 0): ?>
                    <?php foreach ($bookings as $booking): ?>
                        <div class="mobile-booking-card">
                            <div class="mobile-booking-top">
                                <h3>#<?php echo $booking['appointment_id']; ?> - <?php echo htmlspecialchars($booking['customer_name']); ?></h3>
                                <span class="status-badge status-<?php echo htmlspecialchars($booking['status']); ?>">
                                    <?php echo htmlspecialchars(ucfirst($booking['status'])); ?>
                                </span>
                            </div>

                            <div class="mobile-booking-body">
                                <p><strong>Pet:</strong> <?php echo htmlspecialchars($booking['pet_name']); ?></p>
                                <p><strong>Service:</strong> <?php echo htmlspecialchars($booking['service_name']); ?></p>
                                <p><strong>Date:</strong> <?php echo htmlspecialchars($booking['appointment_date']); ?></p>
                                <p><strong>Start:</strong> <?php echo htmlspecialchars($booking['start_time']); ?></p>
                                <p><strong>End:</strong> <?php echo $booking['end_time'] ? htmlspecialchars($booking['end_time']) : '-'; ?></p>
                                <p><strong>Duration:</strong> <?php echo $booking['confirmed_duration_minutes'] ? $booking['confirmed_duration_minutes'] . ' min' : '-'; ?></p>
                            </div>

                            <div class="mobile-booking-actions">
                                <a href="view_booking.php?id=<?php echo $booking['appointment_id']; ?>" class="small-btn">View Booking</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="mobile-booking-card">
                        <p>No bookings found.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</body>
</html>