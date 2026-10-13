<?php
$service = $_GET['service'] ?? '';
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Запись на приём | DentalCare</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <?php include 'header.php'; ?>

    <section class="page-hero small-hero">
        <div class="page-hero-overlay"></div>
        <div class="page-hero-content reveal active">
            <span class="page-badge">Онлайн-запись</span>
            <h1>Запись на приём</h1>
            <p>Выберите удобную услугу, врача, дату и время. Мы свяжемся с вами для подтверждения записи.</p>
        </div>
    </section>

    <section class="appointment-section">
        <div class="appointment-wrapper reveal active">
            <div class="appointment-info">
                <h2>Оставьте заявку</h2>
                <p>
                    Заполните форму, и администратор клиники свяжется с вами для уточнения деталей.
                </p>

                <div class="appointment-features">
                    <div class="appointment-feature">
                        <span>🕒</span>
                        <p>Удобное время приёма</p>
                    </div>
                    <div class="appointment-feature">
                        <span>👨‍⚕️</span>
                        <p>Выбор специалиста</p>
                    </div>
                    <div class="appointment-feature">
                        <span>🦷</span>
                        <p>Все основные услуги</p>
                    </div>
                </div>
            </div>

            <form action="send.php" method="POST" class="premium-form">
                <div class="form-row">
                    <input type="text" name="name" placeholder="Ваше имя" required>
                    <input type="text" name="phone" placeholder="Телефон" required>
                </div>

                <div class="form-row">
                    <input type="text" name="service" value="<?php echo htmlspecialchars($service); ?>"
                        placeholder="Выбранная услуга">
                    <select name="doctor" required>
                        <option value="">Выберите врача</option>
                        <option value="Иван Петров">Иван Петров</option>
                        <option value="Анна Смирнова">Анна Смирнова</option>
                        <option value="Дмитрий Иванов">Дмитрий Иванов</option>
                    </select>
                </div>

                <div class="form-row">
                    <input type="date" name="date" required>
                    <input type="time" name="time" value="09:00" required>
                </div>

                <textarea name="message" placeholder="Комментарий"></textarea>

                <button type="submit">Записаться</button>
            </form>
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