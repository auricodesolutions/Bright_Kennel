<?php
session_start();
include '../config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = "Please enter email and password.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && $admin['password'] === $password) {
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['full_name'];

            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Bright Kennel</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=80">
</head>
<body>

<div class="admin-login-page">
    <div class="admin-login-wrapper">

        <div class="admin-login-card">
            <div class="brand-box admin-login-brand">
                <img src="../assets/images/logo.png" alt="Bright Kennel Logo" class="site-logo">
                <p class="admin-mini-text">Bright Kennel</p>
                <h1><span class="dark-text">Admin</span> <span class="gold-text">Login</span></h1>
                <p class="admin-login-subtitle">
                    Sign in to manage bookings, confirmations, and customer appointments.
                </p>
            </div>

            <?php if ($error !== ''): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="booking-form admin-login-form">
                <div class="form-group">
                    <label for="email">Admin Email</label>
                    <input type="email" name="email" id="email" placeholder="Enter admin email" required>
                </div>

                <div class="form-group password-group">
                    <label for="password">Password</label>
                    <div class="password-input-wrap">
                        <input type="password" name="password" id="password" placeholder="Enter password" required>
                        <button type="button" class="toggle-password-btn" onclick="togglePassword()">Show</button>
                    </div>
                </div>

                <button type="submit" class="book-btn admin-login-btn">Login</button>
            </form>

            <div class="admin-login-footer">
                <a href="../index.php" class="back-site-link">← Back to Website</a>
            </div>
        </div>

    </div>
</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleBtn = document.querySelector('.toggle-password-btn');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleBtn.textContent = 'Hide';
    } else {
        passwordInput.type = 'password';
        toggleBtn.textContent = 'Show';
    }
}
</script>

</body>
</html>