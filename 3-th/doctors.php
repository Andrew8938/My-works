<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Врачи | DentalCare</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <?php include 'header.php'; ?>

    <section class="page-hero doctors-hero">
        <div class="page-hero-overlay"></div>
        <div class="page-hero-content reveal">
            <span class="page-badge">Профессиональная команда</span>
            <h1>Наши врачи</h1>
            <p>
                Специалисты с опытом, современным подходом к лечению и вниманием к каждому пациенту.
            </p>
        </div>
    </section>

    <section class="doctors-page reveal">
        <h2 class="title">Команда DentalCare</h2>
        <p class="section-subtitle">
            Наши врачи работают по современным клиническим протоколам и подбирают персональный план лечения.
        </p>

        <div class="doctors-grid premium-doctors-grid">

            <div class="doctor-card premium-doctor-card">
                <img src="images/doctor1.jpg" alt="Иван Петров">
                <div class="doctor-info">
                    <h3>Иван Петров</h3>
                    <p class="specialty">Стоматолог-терапевт</p>
                    <p class="doctor-meta">Опыт работы: 12 лет</p>
                    <div class="doctor-tags">
                        <span>Лечение кариеса</span>
                        <span>Эстетика</span>
                        <span>Профилактика</span>
                    </div>
                </div>
            </div>

            <div class="doctor-card premium-doctor-card">
                <img src="images/doctor2.jpg" alt="Анна Смирнова">
                <div class="doctor-info">
                    <h3>Анна Смирнова</h3>
                    <p class="specialty">Ортодонт</p>
                    <p class="doctor-meta">Исправление прикуса и брекет-системы</p>
                    <div class="doctor-tags">
                        <span>Брекеты</span>
                        <span>Капы</span>
                        <span>Диагностика</span>
                    </div>
                </div>
            </div>

            <div class="doctor-card premium-doctor-card">
                <img src="images/doctor3.jpg" alt="Дмитрий Иванов">
                <div class="doctor-info">
                    <h3>Дмитрий Иванов</h3>
                    <p class="specialty">Имплантолог</p>
                    <p class="doctor-meta">Имплантация и хирургическая стоматология</p>
                    <div class="doctor-tags">
                        <span>Имплантация</span>
                        <span>Хирургия</span>
                        <span>Протезирование</span>
                    </div>
                </div>
            </div>

            <div class="doctor-card premium-doctor-card">
                <img src="images/doctor1.jpg" alt="Елена Орлова">
                <div class="doctor-info">
                    <h3>Елена Орлова</h3>
                    <p class="specialty">Гигиенист</p>
                    <p class="doctor-meta">Профессиональная чистка и профилактика</p>
                    <div class="doctor-tags">
                        <span>AirFlow</span>
                        <span>Гигиена</span>
                        <span>Профилактика</span>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <footer class="footer">
        <p>©
            <?php echo date("Y"); ?> DentalCare Clinic. Все права защищены.
        </p>
    </footer>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const revealElements = document.querySelectorAll(".reveal");

            const revealObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add("active");
                    }
                });
            }, { threshold: 0.15 });

            revealElements.forEach(el => revealObserver.observe(el));
        });
    </script>

    <?php include 'ai-widget.php'; ?>

</body>

</html>