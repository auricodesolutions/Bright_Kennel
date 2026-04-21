<?php
include 'config/db.php';

$services = $pdo->query("SELECT * FROM services WHERE is_active = 1 ORDER BY service_name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bright Kennel Booking</title>
    <link rel="stylesheet" href="assets/css/style.css?v=70">
</head>
<body>

<div class="page-wrapper">
    <div class="booking-container">

        <div class="brand-box">
            <img src="assets/images/logo.png" alt="Bright Kennel Logo" class="site-logo">
            <h1><span class="dark-text">Bright</span> <span class="gold-text">Kennel</span></h1>
            <p class="tagline">Where Pets Glow</p>
        </div>

        <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
            <div class="success-message">
                Your appointment has been booked successfully!
            </div>
        <?php endif; ?>

        <form id="bookingForm" class="booking-form" method="POST" action="save_booking.php" enctype="multipart/form-data">

            <div class="section-title">
                <h2>Owner Details</h2>
            </div>

            <div class="form-group">
                <label for="full_name">Owner Name</label>
                <input type="text" name="full_name" id="full_name" placeholder="Enter owner name" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" name="phone" id="phone" placeholder="Enter phone number" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" placeholder="Enter email address">
            </div>

            <div class="section-title">
                <h2>Pet Details</h2>
            </div>

            <div class="form-group">
                <label for="pet_name">Pet Name</label>
                <input type="text" name="pet_name" id="pet_name" placeholder="Enter pet name" required>
            </div>

            <div class="form-group">
                <label for="breed">Breed</label>
                <input type="text" name="breed" id="breed" placeholder="Enter breed">
            </div>

            <div class="form-group">
                <label for="size">Pet Size</label>
                <select name="size" id="size" required>
                    <option value="">Select Pet Size</option>
                    <option value="Small">Small</option>
                    <option value="Medium">Medium</option>
                    <option value="Large">Large</option>
                </select>
            </div>

            <div class="section-title">
                <h2>Booking Details</h2>
            </div>

            <div class="form-group">
                <label for="service_id">Select Service</label>
                <select name="service_id" id="service_id" required>
                    <option value="">Select a Service</option>
                    <?php foreach ($services as $service): ?>
                        <option value="<?= $service['service_id']; ?>">
                            <?= htmlspecialchars($service['service_name']); ?> - Rs. <?= number_format($service['price'], 2); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="appointment_date">Appointment Date</label>
                <input type="date" name="appointment_date" id="appointment_date" required>
            </div>

            <div class="form-group">
                <label>Select Time</label>
                <div id="timeSlots" class="time-slots-wrapper">
                    <p class="empty-slot-text">Please select a date to view time slots.</p>
                </div>
            </div>
            
            <input type="hidden" name="start_time" id="start_time">

            <div class="form-group">
                <label for="booking_note">Note</label>
                <textarea name="booking_note" id="booking_note" placeholder="Enter booking note (optional)"></textarea>
            </div>

            <div class="form-group">
                <label for="attachment">Attachment (Optional)</label>
                <input type="file" name="attachment" id="attachment" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
            </div>

            <div class="slot-note">
                Available times are shown every 15 minutes from 9:00 AM to 6:00 PM.
            </div>

            <button type="submit" class="book-btn">Book Appointment</button>

        </form>
    </div>
</div>

<script src="assets/js/booking.js?v=20"></script>
</body>
</html>