<?php

$host = "localhost";
$user = "root";
$password = "";
$dbname = "dental_clinic";

$conn = new mysqli($host, $user, $password);

if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

$conn->set_charset("utf8");

$conn->query("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8 COLLATE utf8_general_ci");
$conn->select_db($dbname);

/* ===== doctors ===== */
$conn->query("
CREATE TABLE IF NOT EXISTS doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    specialty VARCHAR(100) NOT NULL,
    photo VARCHAR(255),
    experience INT,
    schedule TEXT
)
");

/* ===== appointments ===== */
$conn->query("
CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    service VARCHAR(150) NOT NULL,
    doctor_id INT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NULL,
    message TEXT,
    status ENUM('Запланировано','Подтверждено','Завершено','Отменено') DEFAULT 'Запланировано',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
");

/* ===== users ===== */
$conn->query("
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','doctor') NOT NULL,
    doctor_id INT UNIQUE NULL
)
");

/* ===== ДОБАВЛЯЕМ НЕДОСТАЮЩИЕ ПОЛЯ В appointments ===== */
function columnExists($conn, $table, $column)
{
    $table = $conn->real_escape_string($table);
    $column = $conn->real_escape_string($column);
    $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    return $result && $result->num_rows > 0;
}

if (!columnExists($conn, 'appointments', 'doctor_id')) {
    $conn->query("ALTER TABLE appointments ADD COLUMN doctor_id INT NULL AFTER service");
}

if (!columnExists($conn, 'appointments', 'appointment_time')) {
    $conn->query("ALTER TABLE appointments ADD COLUMN appointment_time TIME NULL AFTER appointment_date");
}

if (!columnExists($conn, 'appointments', 'status')) {
    $conn->query("ALTER TABLE appointments ADD COLUMN status ENUM('Запланировано','Подтверждено','Завершено','Отменено') DEFAULT 'Запланировано' AFTER message");
}

if (!columnExists($conn, 'appointments', 'created_at')) {
    $conn->query("ALTER TABLE appointments ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER status");
}

/* ===== ДОБАВЛЯЕМ НЕДОСТАЮЩИЕ ПОЛЯ В users ===== */
if (!columnExists($conn, 'users', 'doctor_id')) {
    $conn->query("ALTER TABLE users ADD COLUMN doctor_id INT UNIQUE NULL");
}

/* ===== АДМИН ПО УМОЛЧАНИЮ ===== */
$checkAdmin = $conn->query("SELECT id FROM users WHERE username='admin' LIMIT 1");

if ($checkAdmin && $checkAdmin->num_rows == 0) {
    $passwordHash = password_hash("admin123", PASSWORD_DEFAULT);
    $conn->query("
        INSERT INTO users (username, password_hash, role)
        VALUES ('admin', '$passwordHash', 'admin')
    ");
}

/* ===== ТЕСТОВЫЕ ВРАЧИ, ЕСЛИ ТАБЛИЦА ПУСТАЯ ===== */
$checkDoctors = $conn->query("SELECT COUNT(*) as total FROM doctors");
if ($checkDoctors) {
    $row = $checkDoctors->fetch_assoc();
    if ((int) $row['total'] === 0) {
        $conn->query("
            INSERT INTO doctors (full_name, specialty, experience) VALUES
            ('Иван Петров', 'Стоматолог-терапевт', 12),
            ('Анна Смирнова', 'Ортодонт', 9),
            ('Дмитрий Иванов', 'Имплантолог', 14)
        ");
    }
}

?>