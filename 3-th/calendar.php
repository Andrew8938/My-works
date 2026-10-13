<?php
include 'config.php';

$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$doctorFilter = trim($_GET['doctor'] ?? '');
$dateFilter = trim($_GET['date'] ?? '');

$sql = "
    SELECT 
        appointments.*,
        doctors.full_name AS doctor_name
    FROM appointments
    LEFT JOIN doctors ON appointments.doctor_id = doctors.id
    WHERE 1=1
";

$params = [];
$types = "";

if ($search !== '') {
    $sql .= " AND (appointments.name LIKE ? OR appointments.phone LIKE ? OR appointments.service LIKE ?)";
    $like = "%" . $search . "%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= "sss";
}

if ($statusFilter !== '') {
    $sql .= " AND appointments.status = ?";
    $params[] = $statusFilter;
    $types .= "s";
}

if ($doctorFilter !== '') {
    $sql .= " AND appointments.doctor_id = ?";
    $params[] = (int) $doctorFilter;
    $types .= "i";
}

if ($dateFilter !== '') {
    $sql .= " AND appointments.appointment_date = ?";
    $params[] = $dateFilter;
    $types .= "s";
}

$sql .= " ORDER BY appointments.appointment_date ASC, appointments.appointment_time ASC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$today = date('Y-m-d');

/* Сегодняшние записи */
$todayStmt = $conn->prepare("
    SELECT appointments.*, doctors.full_name AS doctor_name
    FROM appointments
    LEFT JOIN doctors ON appointments.doctor_id = doctors.id
    WHERE appointments.appointment_date = ?
    ORDER BY appointments.appointment_time ASC
");
$todayStmt->bind_param("s", $today);
$todayStmt->execute();
$todayResult = $todayStmt->get_result();

/* Ближайшая будущая запись */
$nextStmt = $conn->prepare("
    SELECT appointments.*, doctors.full_name AS doctor_name
    FROM appointments
    LEFT JOIN doctors ON appointments.doctor_id = doctors.id
    WHERE 
        (appointments.appointment_date > CURDATE())
        OR
        (appointments.appointment_date = CURDATE() AND appointments.appointment_time >= CURTIME())
    ORDER BY appointments.appointment_date ASC, appointments.appointment_time ASC
    LIMIT 1
");
$nextStmt->execute();
$nextResult = $nextStmt->get_result();
$nextAppointment = $nextResult->fetch_assoc();

/* Список врачей */
$doctors = [];
$doctorsResult = $conn->query("SELECT id, full_name, specialty FROM doctors ORDER BY full_name ASC");
if ($doctorsResult) {
    while ($doctor = $doctorsResult->fetch_assoc()) {
        $doctors[] = $doctor;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Календарь записей | DentalCare</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <?php include 'header.php'; ?>

    <section class="page-hero small-hero">
        <div class="page-hero-overlay"></div>
        <div class="page-hero-content reveal active">
            <span class="page-badge">Календарь и планирование</span>
            <h1>Календарь записей</h1>
            <p>Следите за расписанием, фильтруйте записи и быстро управляйте статусами пациентов.</p>
        </div>
    </section>

    <section class="calendar-section">

        <div class="calendar-dashboard">
            <div class="calendar-dashboard-card">
                <h3>Сегодня</h3>
                <p class="calendar-dashboard-value">
                    <?php echo date('d.m.Y'); ?>
                </p>
                <span>Текущая дата в системе</span>
            </div>

            <div class="calendar-dashboard-card">
                <h3>Ближайший приём</h3>
                <?php if ($nextAppointment): ?>
                    <p class="calendar-dashboard-value">
                        <?php echo htmlspecialchars($nextAppointment['name']); ?>
                    </p>
                    <span>
                        <?php echo htmlspecialchars($nextAppointment['appointment_date']); ?>
                        в
                        <?php echo htmlspecialchars(substr((string) $nextAppointment['appointment_time'], 0, 5)); ?>
                    </span>
                <?php else: ?>
                    <p class="calendar-dashboard-value">Нет</p>
                    <span>Ближайших записей нет</span>
                <?php endif; ?>
            </div>

            <div class="calendar-dashboard-card">
                <h3>Сегодняшних записей</h3>
                <p class="calendar-dashboard-value">
                    <?php echo $todayResult->num_rows; ?>
                </p>
                <span>На текущую дату</span>
            </div>
        </div>

        <form method="GET" class="calendar-filter-form">
            <input type="text" name="search" placeholder="Поиск по имени, телефону или услуге"
                value="<?php echo htmlspecialchars($search); ?>">

            <select name="doctor">
                <option value="">Все врачи</option>
                <?php foreach ($doctors as $doctor): ?>
                    <option value="<?php echo (int) $doctor['id']; ?>" <?php echo ($doctorFilter == $doctor['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($doctor['full_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="status">
                <option value="">Все статусы</option>
                <option value="Запланировано" <?php echo $statusFilter === 'Запланировано' ? 'selected' : ''; ?>>
                    Запланировано</option>
                <option value="Подтверждено" <?php echo $statusFilter === 'Подтверждено' ? 'selected' : ''; ?>>
                    Подтверждено</option>
                <option value="Завершено" <?php echo $statusFilter === 'Завершено' ? 'selected' : ''; ?>>Завершено
                </option>
                <option value="Отменено" <?php echo $statusFilter === 'Отменено' ? 'selected' : ''; ?>>Отменено</option>
            </select>

            <input type="date" name="date" value="<?php echo htmlspecialchars($dateFilter); ?>">

            <button type="submit">Применить</button>
        </form>

        <div class="calendar-layout">
            <div class="calendar-main">
                <h2 class="title">Все записи</h2>

                <div class="calendar-container">
                    <?php
                    $currentDate = '';

                    if ($result && $result->num_rows > 0):
                        while ($row = $result->fetch_assoc()):
                            if ($currentDate !== $row['appointment_date']) {
                                $currentDate = $row['appointment_date'];
                                echo "<h3 class='date-title'>" . htmlspecialchars($currentDate) . "</h3>";
                            }

                            $status = $row['status'] ?? 'Запланировано';
                            $statusClass = 'status-plan';

                            if ($status === 'Завершено')
                                $statusClass = 'status-done';
                            elseif ($status === 'Подтверждено')
                                $statusClass = 'status-confirm';
                            elseif ($status === 'Отменено')
                                $statusClass = 'status-cancel';
                            ?>
                            <div class="appointment-card premium-appointment-card">
                                <div class="appointment-top">
                                    <div>
                                        <h3>
                                            <?php echo htmlspecialchars($row['name']); ?>
                                        </h3>
                                        <p><b>Телефон:</b>
                                            <?php echo htmlspecialchars($row['phone']); ?>
                                        </p>
                                    </div>
                                    <span class="status <?php echo $statusClass; ?>">
                                        <?php echo htmlspecialchars($status); ?>
                                    </span>
                                </div>

                                <div class="appointment-meta-grid">
                                    <p><b>Услуга:</b>
                                        <?php echo htmlspecialchars($row['service']); ?>
                                    </p>
                                    <p><b>Врач:</b>
                                        <?php echo htmlspecialchars($row['doctor_name'] ?: 'Не назначен'); ?>
                                    </p>
                                    <p><b>Дата:</b>
                                        <?php echo htmlspecialchars($row['appointment_date']); ?>
                                    </p>
                                    <p><b>Время:</b>
                                        <?php echo htmlspecialchars(substr((string) $row['appointment_time'], 0, 5)); ?>
                                    </p>
                                </div>

                                <?php if (!empty($row['message'])): ?>
                                    <div class="appointment-note">
                                        <b>Комментарий:</b>
                                        <?php echo htmlspecialchars($row['message']); ?>
                                    </div>
                                <?php endif; ?>

                                <div class="appointment-actions">
                                    <a class="btn-mini btn-confirm"
                                        href="update.php?id=<?php echo (int) $row['id']; ?>&status=Подтверждено">Подтвердить</a>
                                    <a class="btn-mini btn-complete"
                                        href="update.php?id=<?php echo (int) $row['id']; ?>&status=Завершено">Завершить</a>
                                    <a class="btn-mini btn-cancel"
                                        href="update.php?id=<?php echo (int) $row['id']; ?>&status=Отменено">Отменить</a>
                                    <a class="btn" href="edit.php?id=<?php echo (int) $row['id']; ?>">Редактировать</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <h3>Записи не найдены</h3>
                            <p>Попробуйте изменить фильтры или добавить новую запись через форму на сайте.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <aside class="calendar-sidebar">
                <div class="calendar-side-card">
                    <h3>Сегодняшние записи</h3>

                    <?php if ($todayResult && $todayResult->num_rows > 0): ?>
                        <div class="today-list">
                            <?php while ($todayRow = $todayResult->fetch_assoc()): ?>
                                <div class="today-item">
                                    <strong>
                                        <?php echo htmlspecialchars(substr((string) $todayRow['appointment_time'], 0, 5)); ?>
                                    </strong>
                                    <div>
                                        <p>
                                            <?php echo htmlspecialchars($todayRow['name']); ?>
                                        </p>
                                        <span>
                                            <?php echo htmlspecialchars($todayRow['service']); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="side-empty">На сегодня записей нет.</p>
                    <?php endif; ?>
                </div>

                <div class="calendar-side-card">
                    <h3>Быстрые действия</h3>
                    <div class="quick-links">
                        <a class="btn" href="appointment.php">Новая запись</a>
                        <a class="btn2" href="admin.php">Перейти в админку</a>
                    </div>
                </div>
            </aside>
        </div>
    </section>

    <footer class="footer">
        <p>©
            <?php echo date("Y"); ?> DentalCare Clinic. Все права защищены.
        </p>
    </footer>

    <?php include 'ai-widget.php'; ?>

</body>

</html>