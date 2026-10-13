<?php
include 'config.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $service = trim($_POST['service'] ?? '');
    $doctor_id = !empty($_POST['doctor_id']) ? (int) $_POST['doctor_id'] : null;
    $appointment_date = $_POST['appointment_date'] ?? '';
    $appointment_time = $_POST['appointment_time'] ?? '';
    $message = trim($_POST['message'] ?? '');
    $status = $_POST['status'] ?? 'Запланировано';

    if ($id > 0 && $name !== '' && $phone !== '' && $service !== '' && $appointment_date !== '') {
        $stmt = $conn->prepare("
            UPDATE appointments
            SET name = ?, phone = ?, service = ?, doctor_id = ?, appointment_date = ?, appointment_time = ?, message = ?, status = ?
            WHERE id = ?
        ");

        $stmt->bind_param(
            "sssissssi",
            $name,
            $phone,
            $service,
            $doctor_id,
            $appointment_date,
            $appointment_time,
            $message,
            $status,
            $id
        );

        $stmt->execute();
        $stmt->close();

        header("Location: admin.php");
        exit;
    }
}

if ($id <= 0) {
    die("Некорректный ID записи.");
}

$stmt = $conn->prepare("SELECT * FROM appointments WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$appointment = $result->fetch_assoc();
$stmt->close();

if (!$appointment) {
    die("Запись не найдена.");
}

$doctors = [];
$doctorsResult = $conn->query("SELECT id, full_name, specialty FROM doctors ORDER BY full_name ASC");
if ($doctorsResult) {
    while ($row = $doctorsResult->fetch_assoc()) {
        $doctors[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Редактирование записи | DentalCare</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <?php include 'header.php'; ?>

    <section class="page-hero small-hero">
        <div class="page-hero-overlay"></div>
        <div class="page-hero-content reveal active">
            <span class="page-badge">Редактирование и контроль</span>
            <h1>Редактирование записи</h1>
            <p>Изменяйте данные пациента, назначайте врача, корректируйте дату, время и статус приёма.</p>
        </div>
    </section>

    <section class="edit-section">
        <div class="edit-layout">

            <div class="edit-side-card">
                <h2>Подсказка</h2>
                <p>Здесь можно полностью обновить запись пациента и сразу сохранить изменения в базе данных.</p>

                <div class="edit-side-points">
                    <div class="edit-point">
                        <span>📞</span>
                        <p>Проверьте телефон перед сохранением</p>
                    </div>
                    <div class="edit-point">
                        <span>👨‍⚕️</span>
                        <p>Назначьте нужного специалиста</p>
                    </div>
                    <div class="edit-point">
                        <span>📌</span>
                        <p>Укажите актуальный статус записи</p>
                    </div>
                </div>
            </div>

            <form method="POST" class="premium-form edit-form">
                <input type="hidden" name="id" value="<?php echo (int) $appointment['id']; ?>">

                <div class="form-row">
                    <input type="text" name="name" value="<?php echo htmlspecialchars($appointment['name']); ?>"
                        placeholder="Имя пациента" required>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($appointment['phone']); ?>"
                        placeholder="Телефон" required>
                </div>

                <div class="form-row">
                    <input type="text" name="service" value="<?php echo htmlspecialchars($appointment['service']); ?>"
                        placeholder="Услуга" required>

                    <select name="doctor_id">
                        <option value="">Выберите врача</option>
                        <?php foreach ($doctors as $doctor): ?>
                            <option value="<?php echo (int) $doctor['id']; ?>" <?php echo ((int) $appointment['doctor_id'] === (int) $doctor['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($doctor['full_name'] . ' — ' . $doctor['specialty']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <input type="date" name="appointment_date"
                        value="<?php echo htmlspecialchars($appointment['appointment_date']); ?>" required>
                    <input type="time" name="appointment_time"
                        value="<?php echo htmlspecialchars(substr((string) $appointment['appointment_time'], 0, 5)); ?>">
                </div>

                <select name="status">
                    <option value="Запланировано" <?php echo ($appointment['status'] === 'Запланировано') ? 'selected' : ''; ?>>Запланировано</option>
                    <option value="Подтверждено" <?php echo ($appointment['status'] === 'Подтверждено') ? 'selected' : ''; ?>>Подтверждено</option>
                    <option value="Завершено" <?php echo ($appointment['status'] === 'Завершено') ? 'selected' : ''; ?>>
                        Завершено</option>
                    <option value="Отменено" <?php echo ($appointment['status'] === 'Отменено') ? 'selected' : ''; ?>>
                        Отменено</option>
                </select>

                <textarea name="message"
                    placeholder="Комментарий"><?php echo htmlspecialchars($appointment['message'] ?? ''); ?></textarea>

                <div class="edit-form-actions">
                    <button type="submit">Сохранить изменения</button>
                    <a class="btn-outline-dark" href="admin.php">Назад в админку</a>
                </div>
            </form>
        </div>
    </section>

    <footer class="footer">
        <p>© <?php echo date("Y"); ?> DentalCare Clinic. Все права защищены.</p>
    </footer>

</body>

</html>