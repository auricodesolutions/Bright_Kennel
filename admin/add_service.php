<?php include 'auth_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Service - Bright Kennel</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=90">
</head>
<body>
<div class="page-wrapper">
    <div class="booking-container" style="max-width:700px;">
        <div class="admin-header-card">
            <div class="admin-header-left">
                <p class="admin-mini-text">Service Management</p>
                <h1>Add Service</h1>
            </div>
            <div class="admin-header-right">
                <a href="services.php" class="admin-link-btn">Back</a>
            </div>
        </div>

        <form method="POST" action="save_service.php" class="booking-form">
            <div class="form-group">
                <label>Service Name</label>
                <input type="text" name="service_name" required>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description"></textarea>
            </div>

            <div class="form-group">
                <label>Duration (minutes)</label>
                <input type="number" name="duration_minutes" min="1" required>
            </div>

            <div class="form-group">
                <label>Price</label>
                <input type="number" step="0.01" name="price" min="0" required>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="is_active">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>

            <button type="submit" class="book-btn">Save Service</button>
        </form>
    </div>
</div>
</body>
</html>