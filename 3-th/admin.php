<?php
include 'config.php';

$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

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
    $searchLike = "%" . $search . "%";
    $params[] = $searchLike;
    $params[] = $searchLike;
    $params[] = $searchLike;
    $types .= "sss";
}

if ($statusFilter !== '') {
    $sql .= " AND appointments.status = ?";
    $params[] = $statusFilter;
    $types .= "s";
}

$sql .= " ORDER BY appointments.appointment_date DESC, appointments.appointment_time DESC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

/* Статистика */
$stats = [
    'all' => 0,
    'planned' => 0,
    'confirmed' => 0,
    'done' => 0,
    'cancelled' => 0
];

$statsQuery = $conn->query("
    SELECT status, COUNT(*) as total
    FROM appointments
    GROUP BY status
");

if ($statsQuery) {
    while ($row = $statsQuery->fetch_assoc()) {
        $stats['all'] += (int) $row['total'];

        if ($row['status'] === 'Запланировано')
            $stats['planned'] = (int) $row['total'];
        if ($row['status'] === 'Подтверждено')
            $stats['confirmed'] = (int) $row['total'];
        if ($row['status'] === 'Завершено')
            $stats['done'] = (int) $row['total'];
        if ($row['status'] === 'Отменено')
            $stats['cancelled'] = (int) $row['total'];
    }
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Админ-панель | DentalCare</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <?php include 'header.php'; ?>

    <section class="page-hero small-hero">
        <div class="page-hero-overlay"></div>
        <div class="page-hero-content reveal active">
            <span class="page-badge">Управление клиникой</span>
            <h1>Админ-панель</h1>
            <p>Просмотр, поиск, редактирование и управление статусами записей пациентов.</p>
        </div>
    </section>

    <section class="admin-section">

        <div class="admin-header-block">
            <h2 class="title">Записи пациентов</h2>
            <p class="section-subtitle">Здесь можно быстро найти запись, изменить её или поменять статус приёма.</p>
        </div>

        <div class="admin-stats">
            <div class="admin-stat-card">
                <h3><?php echo $stats['all']; ?></h3>
                <p>Всего записей</p>
            </div>
            <div class="admin-stat-card">
                <h3><?php echo $stats['planned']; ?></h3>
                <p>Запланировано</p>
            </div>
            <div class="admin-stat-card">
                <h3><?php echo $stats['confirmed']; ?></h3>
                <p>Подтверждено</p>
            </div>
            <div class="admin-stat-card">
                <h3><?php echo $stats['done']; ?></h3>
                <p>Завершено</p>
            </div>
            <div class="admin-stat-card">
                <h3><?php echo $stats['cancelled']; ?></h3>
                <p>Отменено</p>
            </div>
        </div>

        <form method="GET" class="admin-filter-form">
            <input type="text" name="search" placeholder="Поиск по имени, телефону или услуге"
                value="<?php echo htmlspecialchars($search); ?>">

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

            <button type="submit">Применить</button>
        </form>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <tr>
                    <th>ID</th>
                    <th>Имя</th>
                    <th>Телефон</th>
                    <th>Услуга</th>
                    <th>Врач</th>
                    <th>Дата</th>
                    <th>Время</th>
                    <th>Статус</th>
                    <th>Быстрые действия</th>
                    <th>Управление</th>
                </tr>

                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <?php
                        $status = $row['status'] ?? 'Запланировано';
                        $statusClass = 'status-plan';

                        if ($status === 'Завершено')
                            $statusClass = 'status-done';
                        elseif ($status === 'Подтверждено')
                            $statusClass = 'status-confirm';
                        elseif ($status === 'Отменено')
                            $statusClass = 'status-cancel';
                        ?>
                        <tr>
                            <td><?php echo (int) $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td><?php echo htmlspecialchars($row['service']); ?></td>
                            <td><?php echo htmlspecialchars($row['doctor_name'] ?: 'Не назначен'); ?></td>
                            <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                            <td><?php echo htmlspecialchars(substr((string) $row['appointment_time'], 0, 5)); ?></td>
                            <td>
                                <span class="status <?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars($status); ?>
                                </span>
                            </td>
                            <td class="admin-actions">
                                <a class="btn-mini btn-confirm"
                                    href="update.php?id=<?php echo (int) $row['id']; ?>&status=Подтверждено">Подтвердить</a>
                                <a class="btn-mini btn-complete"
                                    href="update.php?id=<?php echo (int) $row['id']; ?>&status=Завершено">Завершить</a>
                                <a class="btn-mini btn-cancel"
                                    href="update.php?id=<?php echo (int) $row['id']; ?>&status=Отменено">Отменить</a>
                            </td>
                            <td class="admin-actions">
                                <a class="btn" href="edit.php?id=<?php echo (int) $row['id']; ?>">Редактировать</a>
                                <a class="btn-danger" href="delete.php?id=<?php echo (int) $row['id']; ?>"
                                    onclick="return confirm('Удалить запись?')">Удалить</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10">Записи не найдены.</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
    </section>

    <footer class="footer">
        <p>© <?php echo date("Y"); ?> DentalCare Clinic. Все права защищены.</p>
    </footer>

</body>

</html>