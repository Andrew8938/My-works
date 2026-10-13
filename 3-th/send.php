<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Неверный запрос.");
}

$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$service = trim($_POST['service'] ?? '');
$doctorInput = trim($_POST['doctor'] ?? '');
$appointment_date = $_POST['date'] ?? '';
$appointment_time = $_POST['time'] ?? '';
$message = trim($_POST['message'] ?? '');

if ($name === '' || $phone === '' || $appointment_date === '') {
    die("Пожалуйста, заполните обязательные поля.");
}

$doctor_id = null;

if ($doctorInput !== '') {
    if (ctype_digit($doctorInput)) {
        $doctor_id = (int) $doctorInput;
    } else {
        $doctorStmt = $conn->prepare("SELECT id FROM doctors WHERE full_name = ? LIMIT 1");
        $doctorStmt->bind_param("s", $doctorInput);
        $doctorStmt->execute();
        $doctorResult = $doctorStmt->get_result();
        $doctorRow = $doctorResult->fetch_assoc();
        if ($doctorRow) {
            $doctor_id = (int) $doctorRow['id'];
        }
        $doctorStmt->close();
    }
}

$stmt = $conn->prepare("
    INSERT INTO appointments
    (name, phone, service, doctor_id, appointment_date, appointment_time, message, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, 'Запланировано')
");

$stmt->bind_param(
    "sssisss",
    $name,
    $phone,
    $service,
    $doctor_id,
    $appointment_date,
    $appointment_time,
    $message
);

$success = $stmt->execute();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Запись успешна | DentalCare</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <?php include 'header.php'; ?>

    <section class="page-hero small-hero">
        <div class="page-hero-overlay"></div>
        <div class="page-hero-content reveal active">
            <span class="page-badge">Онлайн-заявка</span>
            <h1><?php echo $success ? 'Запись успешно отправлена' : 'Ошибка при отправке'; ?></h1>
            <p>
                <?php echo $success
                    ? 'Спасибо! Ваша заявка принята. Ниже вы можете проверить введённые данные.'
                    : 'К сожалению, запись не была сохранена. Попробуйте ещё раз.'; ?>
            </p>
        </div>
    </section>

    <section class="appointment-section">
        <div class="success-box">
            <?php if ($success): ?>
                <h2>Спасибо за запись!</h2>
                <p><b>Имя:</b> <?php echo htmlspecialchars($name); ?></p>
                <p><b>Телефон:</b> <?php echo htmlspecialchars($phone); ?></p>
                <p><b>Услуга:</b> <?php echo htmlspecialchars($service); ?></p>
                <p><b>Дата:</b> <?php echo htmlspecialchars($appointment_date); ?></p>
                <p><b>Время:</b> <?php echo htmlspecialchars($appointment_time); ?></p>
                <p><b>Комментарий:</b> <?php echo htmlspecialchars($message); ?></p>

                <div style="margin-top:20px; display:flex; gap:12px; flex-wrap:wrap;">
                    <a class="btn" href="index.php">На главную</a>
                    <a class="btn2" href="calendar.php">Перейти в календарь</a>
                </div>
            <?php else: ?>
                <h2>Ошибка</h2>
                <p>Не удалось сохранить запись в базе данных.</p>
                <p><b>Причина:</b> <?php echo htmlspecialchars($conn->error); ?></p>
                <div style="margin-top:20px;">
                    <a class="btn" href="appointment.php">Вернуться к форме</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <footer class="footer">
        <p>© <?php echo date("Y"); ?> DentalCare Clinic. Все права защищены.</p>
    </footer>

</body>

</html>