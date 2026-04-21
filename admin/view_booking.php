<?php
include 'auth_check.php';
include '../config/db.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT 
        a.*,
        c.full_name AS customer_name,
        c.phone,
        c.email,
        p.pet_name,
        p.breed,
        p.size,
        s.service_name
    FROM appointments a
    JOIN customers c ON a.customer_id = c.customer_id
    JOIN pets p ON a.pet_id = p.pet_id
    JOIN appointment_services aps ON a.appointment_id = aps.appointment_id
    JOIN services s ON aps.service_id = s.service_id
    WHERE a.appointment_id = ?
    LIMIT 1
");
$stmt->execute([$id]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    die("Booking not found.");
}

$successMessage = '';
$errorMessage = '';

if (isset($_GET['success'])) {
    if ($_GET['success'] === 'confirmed') {
        $successMessage = "Booking confirmed successfully!";
    } elseif ($_GET['success'] === 'completed') {
        $successMessage = "Booking marked as completed successfully!";
    } elseif ($_GET['success'] === 'cancelled') {
        $successMessage = "Booking cancelled successfully!";
    } elseif ($_GET['success'] === 'payment_updated') {
        $successMessage = "Payment summary updated successfully!";
    }
}

if (isset($_GET['error'])) {
    if ($_GET['error'] === 'overlap') {
        $errorMessage = "This confirmed time overlaps with another confirmed booking.";
    } elseif ($_GET['error'] === 'completed_locked') {
        $errorMessage = "Completed bookings cannot be changed.";
    } elseif ($_GET['error'] === 'invalid_advance') {
        $errorMessage = "Advance amount cannot be greater than total amount.";
    }
}

$isCompleted = ($booking['status'] === 'completed');

$totalAmount = (float)$booking['total_price'];
$advanceAmount = (float)($booking['advance_amount'] ?? 0);
$balanceAmount = $totalAmount - $advanceAmount;
if ($balanceAmount < 0) {
    $balanceAmount = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Booking - Bright Kennel</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=83">
</head>
<body>
    <div class="page-wrapper">
        <div class="booking-container admin-dashboard-shell" style="max-width:950px;">

            <div class="admin-header-card">
                <div class="admin-header-left">
                    <p class="admin-mini-text">Booking Details</p>
                    <h1>Booking #<?php echo $booking['appointment_id']; ?></h1>
                    <p class="admin-subtitle">Review booking details, update payment summary, and manage status.</p>
                </div>
                <div class="admin-header-right">
                    <a href="bookings.php" class="admin-link-btn">Back to Bookings</a>
                </div>
            </div>

            <?php if ($successMessage !== ''): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($successMessage); ?>
                </div>
            <?php endif; ?>

            <?php if ($errorMessage !== ''): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($errorMessage); ?>
                </div>
            <?php endif; ?>

            <div class="details-grid">
                <div class="admin-details details-card">
                    <h3 class="details-card-title">Owner Information</h3>
                    <p><strong>Owner:</strong> <?php echo htmlspecialchars($booking['customer_name']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($booking['phone']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($booking['email'] ?? '-'); ?></p>
                </div>

                <div class="admin-details details-card">
                    <h3 class="details-card-title">Pet Information</h3>
                    <p><strong>Pet:</strong> <?php echo htmlspecialchars($booking['pet_name']); ?></p>
                    <p><strong>Breed:</strong> <?php echo htmlspecialchars($booking['breed'] ?? '-'); ?></p>
                    <p><strong>Size:</strong> <?php echo htmlspecialchars($booking['size']); ?></p>
                </div>

                <div class="admin-details details-card">
                    <h3 class="details-card-title">Appointment Information</h3>
                    <p><strong>Service:</strong> <?php echo htmlspecialchars($booking['service_name']); ?></p>
                    <p><strong>Date:</strong> <?php echo htmlspecialchars($booking['appointment_date']); ?></p>
                    <p><strong>Start Time:</strong> <?php echo htmlspecialchars($booking['start_time']); ?></p>
                    <p><strong>End Time:</strong> <?php echo $booking['end_time'] ? htmlspecialchars($booking['end_time']) : '-'; ?></p>
                </div>

                <div class="admin-details details-card">
                    <h3 class="details-card-title">Booking Status</h3>
                    <p>
                        <strong>Status:</strong>
                        <span class="status-badge status-<?php echo htmlspecialchars($booking['status']); ?>">
                            <?php echo htmlspecialchars(ucfirst($booking['status'])); ?>
                        </span>
                    </p>
                    <p><strong>Duration:</strong> <?php echo $booking['confirmed_duration_minutes'] ? htmlspecialchars($booking['confirmed_duration_minutes']) . ' minutes' : '-'; ?></p>
                    <p><strong>Note:</strong> <?php echo htmlspecialchars($booking['booking_note'] ?? '-'); ?></p>
                    <p><strong>Attachment:</strong>
<?php if (!empty($booking['attachment'])): ?>
    <a href="../uploads/<?php echo htmlspecialchars($booking['attachment']); ?>" target="_blank">View File</a>
<?php else: ?>
    -
<?php endif; ?>
</p>
                    
                </div>
            </div>

            <div class="admin-action-card" style="margin-bottom: 24px;">
                <h3 class="details-card-title">Payment Summary</h3>

                <div class="details-grid">
                    <div class="admin-details details-card">
                        <p><strong>Total:</strong> Rs. <?php echo number_format($totalAmount, 2); ?></p>
                        <p><strong>Advance:</strong> Rs. <?php echo number_format($advanceAmount, 2); ?></p>
                        <p><strong>Balance:</strong> Rs. <?php echo number_format($balanceAmount, 2); ?></p>
                    </div>

                    <div class="admin-details details-card">
                        <form method="POST" action="update_booking.php" class="booking-form">
                            <input type="hidden" name="appointment_id" value="<?php echo $booking['appointment_id']; ?>">
                            <input type="hidden" name="action" value="update_payment">

                            <div class="form-group">
                                <label for="advance_amount">Advance Amount (Rs.)</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    max="<?php echo htmlspecialchars($totalAmount); ?>"
                                    name="advance_amount"
                                    id="advance_amount"
                                    value="<?php echo htmlspecialchars(number_format($advanceAmount, 2, '.', '')); ?>"
                                >
                            </div>

                            <button type="submit" class="book-btn">Update Payment</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="admin-action-card">
                <h3 class="details-card-title">Update Booking</h3>

                <?php if ($isCompleted): ?>
                    <div class="success-message">
                        This booking is already completed. No further actions can be made.
                    </div>
                <?php endif; ?>

                <form method="POST" action="update_booking.php">
                    <input type="hidden" name="appointment_id" value="<?php echo $booking['appointment_id']; ?>">

                    <div class="form-group">
                        <label for="duration_minutes">Confirm Duration</label>
                        <select name="duration_minutes" id="duration_minutes" <?php echo $isCompleted ? 'disabled' : ''; ?>>
                            <option value="15" <?php echo ((int)$booking['confirmed_duration_minutes'] === 15 ? 'selected' : ''); ?>>15 minutes</option>
                            <option value="30" <?php echo ((int)$booking['confirmed_duration_minutes'] === 30 ? 'selected' : ''); ?>>30 minutes</option>
                            <option value="45" <?php echo ((int)$booking['confirmed_duration_minutes'] === 45 ? 'selected' : ''); ?>>45 minutes</option>
                            <option value="60" <?php echo ((int)$booking['confirmed_duration_minutes'] === 60 ? 'selected' : ''); ?>>1 hour</option>
                            <option value="90" <?php echo ((int)$booking['confirmed_duration_minutes'] === 90 ? 'selected' : ''); ?>>1 hour 30 minutes</option>
                            <option value="120" <?php echo ((int)$booking['confirmed_duration_minutes'] === 120 ? 'selected' : ''); ?>>2 hours</option>
                        </select>
                    </div>

                    <div class="admin-actions-row">
                        <button
                            type="submit"
                            name="action"
                            value="confirm"
                            class="book-btn admin-action-btn <?php echo $isCompleted ? 'disabled-btn' : ''; ?>"
                            <?php echo $isCompleted ? 'disabled' : ''; ?>>
                            Confirm
                        </button>

                        <button
                            type="submit"
                            name="action"
                            value="complete"
                            class="book-btn admin-action-btn <?php echo $isCompleted ? 'disabled-btn' : ''; ?>"
                            <?php echo $isCompleted ? 'disabled' : ''; ?>>
                            Complete
                        </button>

                        <button
                            type="submit"
                            name="action"
                            value="cancel"
                            class="book-btn admin-action-btn cancel-btn <?php echo $isCompleted ? 'disabled-btn' : ''; ?>"
                            <?php echo $isCompleted ? 'disabled' : ''; ?>>
                            Cancel
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</body>
</html>