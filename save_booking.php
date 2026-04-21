<?php
include 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request.");
}

$full_name = trim($_POST['full_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$pet_name = trim($_POST['pet_name'] ?? '');
$breed = trim($_POST['breed'] ?? '');
$size = trim($_POST['size'] ?? '');
$service_id = (int)($_POST['service_id'] ?? 0);
$appointment_date = trim($_POST['appointment_date'] ?? '');
$start_time = trim($_POST['start_time'] ?? '');
$booking_note = trim($_POST['booking_note'] ?? '');

if (
    $full_name === '' ||
    $phone === '' ||
    $pet_name === '' ||
    $size === '' ||
    $service_id <= 0 ||
    $appointment_date === '' ||
    $start_time === ''
) {
    die("Please fill all required fields.");
}

$uploadedFileName = null;

if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['attachment']['error'] !== UPLOAD_ERR_OK) {
        die("File upload failed.");
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
    $originalName = $_FILES['attachment']['name'];
    $tmpName = $_FILES['attachment']['tmp_name'];
    $fileSize = $_FILES['attachment']['size'];

    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if (!in_array($extension, $allowedExtensions)) {
        die("Invalid file type. Allowed: jpg, jpeg, png, pdf, doc, docx");
    }

    if ($fileSize > 5 * 1024 * 1024) {
        die("File is too large. Maximum size is 5MB.");
    }

    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $uploadedFileName = time() . '_' . uniqid() . '.' . $extension;
    $destination = $uploadDir . $uploadedFileName;

    if (!move_uploaded_file($tmpName, $destination)) {
        die("Failed to save uploaded file.");
    }
}

try {
    $pdo->beginTransaction();

    $customer_id = null;

    if ($email !== '') {
        $findCustomerStmt = $pdo->prepare("
            SELECT customer_id
            FROM customers
            WHERE email = ?
            LIMIT 1
        ");
        $findCustomerStmt->execute([$email]);
        $existingCustomer = $findCustomerStmt->fetch(PDO::FETCH_ASSOC);

        if ($existingCustomer) {
            $customer_id = $existingCustomer['customer_id'];

            $updateCustomerStmt = $pdo->prepare("
                UPDATE customers
                SET full_name = ?, phone = ?
                WHERE customer_id = ?
            ");
            $updateCustomerStmt->execute([$full_name, $phone, $customer_id]);
        }
    }

    if (!$customer_id) {
        $customerStmt = $pdo->prepare("
            INSERT INTO customers (full_name, phone, email)
            VALUES (?, ?, ?)
        ");
        $customerStmt->execute([
            $full_name,
            $phone,
            $email !== '' ? $email : null
        ]);
        $customer_id = $pdo->lastInsertId();
    }

    $findPetStmt = $pdo->prepare("
        SELECT pet_id
        FROM pets
        WHERE customer_id = ? AND pet_name = ?
        LIMIT 1
    ");
    $findPetStmt->execute([$customer_id, $pet_name]);
    $existingPet = $findPetStmt->fetch(PDO::FETCH_ASSOC);

    if ($existingPet) {
        $pet_id = $existingPet['pet_id'];

        $updatePetStmt = $pdo->prepare("
            UPDATE pets
            SET breed = ?, size = ?
            WHERE pet_id = ?
        ");
        $updatePetStmt->execute([
            $breed !== '' ? $breed : null,
            $size,
            $pet_id
        ]);
    } else {
        $petStmt = $pdo->prepare("
            INSERT INTO pets (customer_id, pet_name, breed, size)
            VALUES (?, ?, ?, ?)
        ");
        $petStmt->execute([
            $customer_id,
            $pet_name,
            $breed !== '' ? $breed : null,
            $size
        ]);
        $pet_id = $pdo->lastInsertId();
    }

    $serviceStmt = $pdo->prepare("
        SELECT service_name, price
        FROM services
        WHERE service_id = ? AND is_active = 1
        LIMIT 1
    ");
    $serviceStmt->execute([$service_id]);
    $service = $serviceStmt->fetch(PDO::FETCH_ASSOC);

    if (!$service) {
        $pdo->rollBack();
        die("Invalid service selected.");
    }

    $total_price = $service['price'];

    $appointmentStmt = $pdo->prepare("
        INSERT INTO appointments (
            customer_id,
            pet_id,
            appointment_date,
            start_time,
            end_time,
            confirmed_duration_minutes,
            total_price,
            advance_amount,
            status,
            booking_note,
            attachment
        ) VALUES (?, ?, ?, ?, NULL, NULL, ?, 0.00, 'pending', ?, ?)
    ");
    $appointmentStmt->execute([
        $customer_id,
        $pet_id,
        $appointment_date,
        $start_time,
        $total_price,
        $booking_note !== '' ? $booking_note : null,
        $uploadedFileName
    ]);

    $appointment_id = $pdo->lastInsertId();

    $appointmentServiceStmt = $pdo->prepare("
        INSERT INTO appointment_services (
            appointment_id,
            service_id,
            price_at_booking,
            duration_at_booking
        ) VALUES (?, ?, ?, ?)
    ");
    $appointmentServiceStmt->execute([
        $appointment_id,
        $service_id,
        $total_price,
        0
    ]);

    $pdo->commit();

    header("Location: booking.php?success=1");
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("Error: " . $e->getMessage());
}
?>