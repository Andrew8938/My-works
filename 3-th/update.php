<?php
include 'config.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$status = trim($_GET['status'] ?? '');

$allowedStatuses = ['Запланировано', 'Подтверждено', 'Завершено', 'Отменено'];

if ($id > 0 && in_array($status, $allowedStatuses, true)) {
    $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();
}

header("Location: admin.php");
exit;
?>