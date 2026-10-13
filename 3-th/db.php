<?php

$host = "localhost";
$user = "root";
$password = "";
$dbname = "dental_clinic";

$conn = new mysqli($host, $user, $password);

if ($conn->connect_error) {
    die("Ошибка подключения к MySQL: " . $conn->connect_error);
}

// создаём базу если её нет
$conn->query("CREATE DATABASE IF NOT EXISTS $dbname");

$conn->select_db($dbname);

// создаём таблицу если её нет
$conn->query("CREATE TABLE IF NOT EXISTS appointments (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(100),
phone VARCHAR(50),
service VARCHAR(150),
appointment_date DATE,
message TEXT
)");

?>