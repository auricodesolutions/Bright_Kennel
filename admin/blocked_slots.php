<?php
include 'auth_check.php';
include '../config/db.php';

$successMessage = '';
$errorMessage = '';

$editMode = false;
$editSlot = null;

/* -----------------------------
   HANDLE DELETE
------------------------------ */
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];

    if ($delete_id > 0) {
        $deleteStmt = $pdo->prepare("DELETE FROM blocked_slots WHERE blocked_id = ?");
        $deleteStmt->execute([$delete_id]);

        header("Location: blocked_slots.php?success=deleted");
        exit;
    }
}

/* -----------------------------
   HANDLE ADD / UPDATE
------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $blocked_id = (int)($_POST['blocked_id'] ?? 0);
    $blocked_date = trim($_POST['blocked_date'] ?? '');
    $start_time = trim($_POST['start_time'] ?? '');
    $end_time = trim($_POST['end_time'] ?? '');
    $reason = trim($_POST['reason'] ?? '');

    if ($blocked_date === '' || $start_time === '' || $end_time === '') {
        $errorMessage = "Please fill all required fields.";
    } elseif ($start_time >= $end_time) {
        $errorMessage = "End time must be later than start time.";
    } else {
        if ($blocked_id > 0) {
            /* Check overlap excluding current record */
            $checkStmt = $pdo->prepare("
                SELECT blocked_id
                FROM blocked_slots
                WHERE blocked_date = ?
                  AND blocked_id != ?
                  AND (? < end_time AND ? > start_time)
                LIMIT 1
            ");
            $checkStmt->execute([$blocked_date, $blocked_id, $start_time, $end_time]);

            if ($checkStmt->fetch(PDO::FETCH_ASSOC)) {
                $errorMessage = "This blocked time overlaps with an existing blocked slot.";
            } else {
                $updateStmt = $pdo->prepare("
                    UPDATE blocked_slots
                    SET blocked_date = ?, start_time = ?, end_time = ?, reason = ?
                    WHERE blocked_id = ?
                ");
                $updateStmt->execute([
                    $blocked_date,
                    $start_time,
                    $end_time,
                    $reason !== '' ? $reason : null,
                    $blocked_id
                ]);

                header("Location: blocked_slots.php?success=updated");
                exit;
            }
        } else {
            /* Check overlap for new record */
            $checkStmt = $pdo->prepare("
                SELECT blocked_id
                FROM blocked_slots
                WHERE blocked_date = ?
                  AND (? < end_time AND ? > start_time)
                LIMIT 1
            ");
            $checkStmt->execute([$blocked_date, $start_time, $end_time]);

            if ($checkStmt->fetch(PDO::FETCH_ASSOC)) {
                $errorMessage = "This blocked time overlaps with an existing blocked slot.";
            } else {
                $insertStmt = $pdo->prepare("
                    INSERT INTO blocked_slots (blocked_date, start_time, end_time, reason)
                    VALUES (?, ?, ?, ?)
                ");
                $insertStmt->execute([
                    $blocked_date,
                    $start_time,
                    $end_time,
                    $reason !== '' ? $reason : null
                ]);

                header("Location: blocked_slots.php?success=added");
                exit;
            }
        }
    }
}

/* -----------------------------
   HANDLE EDIT LOAD
------------------------------ */
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];

    if ($edit_id > 0) {
        $editStmt = $pdo->prepare("
            SELECT blocked_id, blocked_date, start_time, end_time, reason
            FROM blocked_slots
            WHERE blocked_id = ?
            LIMIT 1
        ");
        $editStmt->execute([$edit_id]);
        $editSlot = $editStmt->fetch(PDO::FETCH_ASSOC);

        if ($editSlot) {
            $editMode = true;
        }
    }
}

/* -----------------------------
   SUCCESS MESSAGES
------------------------------ */
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'added') {
        $successMessage = "Blocked slot added successfully!";
    } elseif ($_GET['success'] === 'updated') {
        $successMessage = "Blocked slot updated successfully!";
    } elseif ($_GET['success'] === 'deleted') {
        $successMessage = "Blocked slot deleted successfully!";
    }
}

/* -----------------------------
   FETCH ALL BLOCKED SLOTS
------------------------------ */
$listStmt = $pdo->query("
    SELECT blocked_id, blocked_date, start_time, end_time, reason
    FROM blocked_slots
    ORDER BY blocked_date DESC, start_time ASC
");
$blockedSlots = $listStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blocked Slots - Bright Kennel</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=95">
    <script>
        function confirmDelete(id) {
            if (confirm("Are you sure you want to delete this blocked slot?")) {
                window.location.href = "blocked_slots.php?delete=" + id;
            }
        }
    </script>
</head>
<body>
<div class="page-wrapper">
    <div class="booking-container admin-dashboard-shell">

        <div class="admin-header-card">
            <div class="admin-header-left">
                <p class="admin-mini-text">Schedule Management</p>
                <h1>Blocked Dates & Times</h1>
                <p class="admin-subtitle">Add, edit, and delete blocked times like lunch breaks, holidays, and closed periods.</p>
            </div>
            <div class="admin-header-right">
                <a href="dashboard.php" class="admin-link-btn">Dashboard</a>
            </div>
        </div>

        <?php if ($successMessage !== ''): ?>
            <div class="success-message"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>

        <?php if ($errorMessage !== ''): ?>
            <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <div class="admin-action-card" style="margin-bottom: 24px;">
            <h3 class="details-card-title">
                <?php echo $editMode ? 'Edit Blocked Slot' : 'Add Blocked Slot'; ?>
            </h3>

            <form method="POST" class="booking-form">
                <input type="hidden" name="blocked_id" value="<?php echo $editMode ? (int)$editSlot['blocked_id'] : 0; ?>">

                <div class="details-grid">
                    <div class="form-group">
                        <label for="blocked_date">Blocked Date</label>
                        <input
                            type="date"
                            name="blocked_date"
                            id="blocked_date"
                            value="<?php echo $editMode ? htmlspecialchars($editSlot['blocked_date']) : ''; ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="start_time">Start Time</label>
                        <input
                            type="time"
                            name="start_time"
                            id="start_time"
                            value="<?php echo $editMode ? htmlspecialchars(substr($editSlot['start_time'], 0, 5)) : ''; ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="end_time">End Time</label>
                        <input
                            type="time"
                            name="end_time"
                            id="end_time"
                            value="<?php echo $editMode ? htmlspecialchars(substr($editSlot['end_time'], 0, 5)) : ''; ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="reason">Reason</label>
                        <input
                            type="text"
                            name="reason"
                            id="reason"
                            placeholder="Example: Lunch Break / Holiday / Closed Time"
                            value="<?php echo $editMode ? htmlspecialchars($editSlot['reason'] ?? '') : ''; ?>"
                        >
                    </div>
                </div>

                <div class="admin-actions-row">
                    <button type="submit" class="book-btn admin-action-btn">
                        <?php echo $editMode ? 'Update Blocked Slot' : 'Save Blocked Slot'; ?>
                    </button>

                    <?php if ($editMode): ?>
                        <a href="blocked_slots.php" class="admin-link-btn">Cancel Edit</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="table-wrapper modern-table-wrapper desktop-bookings-table">
            <table class="admin-table modern-admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Reason</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($blockedSlots): ?>
                        <?php foreach ($blockedSlots as $slot): ?>
                            <tr>
                                <td>#<?php echo $slot['blocked_id']; ?></td>
                                <td><?php echo htmlspecialchars($slot['blocked_date']); ?></td>
                                <td><?php echo htmlspecialchars($slot['start_time']); ?></td>
                                <td><?php echo htmlspecialchars($slot['end_time']); ?></td>
                                <td><?php echo htmlspecialchars($slot['reason'] ?? '-'); ?></td>
                                <td>
                                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                        <a href="blocked_slots.php?edit=<?php echo $slot['blocked_id']; ?>" class="small-btn">Edit</a>
                                        <button type="button" class="small-btn cancel-btn" onclick="confirmDelete(<?php echo $slot['blocked_id']; ?>)">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center;">No blocked slots found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mobile-booking-cards">
            <?php if ($blockedSlots): ?>
                <?php foreach ($blockedSlots as $slot): ?>
                    <div class="mobile-booking-card">
                        <div class="mobile-booking-top">
                            <h3>#<?php echo $slot['blocked_id']; ?></h3>
                        </div>

                        <div class="mobile-booking-body">
                            <p><strong>Date:</strong> <?php echo htmlspecialchars($slot['blocked_date']); ?></p>
                            <p><strong>Start:</strong> <?php echo htmlspecialchars($slot['start_time']); ?></p>
                            <p><strong>End:</strong> <?php echo htmlspecialchars($slot['end_time']); ?></p>
                            <p><strong>Reason:</strong> <?php echo htmlspecialchars($slot['reason'] ?? '-'); ?></p>
                        </div>

                        <div class="mobile-booking-actions" style="display:flex; gap:8px; flex-wrap:wrap;">
                            <a href="blocked_slots.php?edit=<?php echo $slot['blocked_id']; ?>" class="small-btn">Edit</a>
                            <button type="button" class="small-btn cancel-btn" onclick="confirmDelete(<?php echo $slot['blocked_id']; ?>)">Delete</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="mobile-booking-card">
                    <p>No blocked slots found.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>
</body>
</html>