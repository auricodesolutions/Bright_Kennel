<?php
$name = isset($_GET['name']) && $_GET['name'] !== ''
    ? htmlspecialchars($_GET['name'])
    : 'Valued Customer';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You | Bright Kennel</title>
    <link rel="stylesheet" href="assets/css/style.css?v=71">
</head>
<body>

<div class="thankyou-page">
    <div class="thank-you-wrapper">
        <div class="thank-you-card">

            <img src="assets/images/logo.png" alt="Bright Kennel Logo" class="site-logo">


            <h1 class="thank-you-title">
                Thank <span class="highlight">You!</span>
            </h1>

            <p class="thank-you-text">
                Hello <strong><?php echo $name; ?></strong>, your appointment booking has been submitted successfully.
            </p>

            <p class="thank-you-text">
                Our team member will contact you soon to confirm your appointment.
            </p>

            <p class="thank-you-text">
                Thank you for choosing <strong>Bright Kennel</strong>.
            </p>

            <div class="thank-you-actions">
    <a href="index.php" class="primary-btn">Back to Home</a>
</div>

        </div>
    </div>
</div>

</body>
</html>