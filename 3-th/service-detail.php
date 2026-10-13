<?php

$service = $_GET['service'];

$title = "";
$description = "";

switch ($service) {

    case "caries":
        $title = "Лечение кариеса";
        $description = "Процедура включает удаление поврежденных тканей зуба, обработку антисептиком и установку пломбы.";
        break;

    case "cleaning":
        $title = "Профессиональная чистка зубов";
        $description = "Удаление зубного камня и налета ультразвуком и AirFlow, полировка зубов.";
        break;

    case "whitening":
        $title = "Отбеливание зубов";
        $description = "Используется специальный гель и лампа для осветления эмали на 2–6 тонов.";
        break;

    case "implant":
        $title = "Имплантация зубов";
        $description = "В костную ткань устанавливается титановый имплант, который заменяет корень зуба.";
        break;

    case "braces":
        $title = "Установка брекетов";
        $description = "Брекет-система постепенно выравнивает зубы и исправляет прикус.";
        break;

    case "removal":
        $title = "Удаление зуба";
        $description = "Удаление проводится под местной анестезией современными инструментами.";
        break;

    case "children":
        $title = "Детская стоматология";
        $description = "Безопасное лечение молочных и постоянных зубов у детей.";
        break;

    default:
        $title = "Услуга не найдена";
        $description = "Информация отсутствует.";
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>
        <?php echo $title; ?>
    </title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <h1>
        <?php echo $title; ?>
    </h1>

    <p>
        <?php echo $description; ?>
    </p>

    <a class="btn" href="appointment.php">Записаться на приём</a>
    <br><br>
    <a href="services.php">← Назад к услугам</a>

    <?php include 'ai-widget.php'; ?>

</body>

</html>