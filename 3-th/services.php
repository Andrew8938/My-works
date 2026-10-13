<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Услуги | DentalCare</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <?php include 'header.php'; ?>

    <section class="page-hero small-hero">
        <div class="page-hero-overlay"></div>
        <div class="page-hero-content reveal">
            <span class="page-badge">Современные стоматологические услуги</span>
            <h1>Услуги нашей клиники</h1>
            <p>
                Подберите подходящую процедуру и узнайте подробности лечения, этапы проведения и рекомендации
                специалистов.
            </p>
        </div>
    </section>

    <section class="services-page reveal">
        <h2 class="title">Популярные направления</h2>
        <p class="section-subtitle">
            Мы предлагаем комплексное лечение и профилактику для взрослых и детей с использованием современных
            технологий.
        </p>

        <div class="services-grid">

            <div class="service-card tilt-card">
                <img src="images/caries.jpg" alt="Лечение кариеса">
                <div class="service-content no-icon">
                    <h3>Лечение кариеса</h3>
                    <p>Современное лечение зубов без боли с восстановлением эстетики и функции.</p>
                    <div class="buttons">
                        <a class="btn" href="service-detail.php?service=caries">Подробнее</a>
                        <a class="btn2" href="appointment.php?service=Лечение кариеса">Записаться</a>
                    </div>
                </div>
            </div>

            <div class="service-card tilt-card">
                <img src="images/cleaning.jpg" alt="Профессиональная чистка">
                <div class="service-content no-icon">
                    <h3>Профессиональная чистка</h3>
                    <p>Удаление налёта, зубного камня, полировка и профилактика заболеваний полости рта.</p>
                    <div class="buttons">
                        <a class="btn" href="service-detail.php?service=cleaning">Подробнее</a>
                        <a class="btn2" href="appointment.php?service=Чистка зубов">Записаться</a>
                    </div>
                </div>
            </div>

            <div class="service-card tilt-card">
                <img src="images/implant.jpg" alt="Имплантация">
                <div class="service-content no-icon">
                    <h3>Имплантация</h3>
                    <p>Надёжное восстановление утраченных зубов с помощью современных имплантов.</p>
                    <div class="buttons">
                        <a class="btn" href="service-detail.php?service=implant">Подробнее</a>
                        <a class="btn2" href="appointment.php?service=Имплантация">Записаться</a>
                    </div>
                </div>
            </div>

            <div class="service-card tilt-card">
                <img src="images/whitening.jpg" alt="Отбеливание зубов">
                <div class="service-content no-icon">
                    <h3>Отбеливание зубов</h3>
                    <p>Эффективное осветление эмали для яркой, здоровой и ухоженной улыбки.</p>
                    <div class="buttons">
                        <a class="btn" href="service-detail.php?service=whitening">Подробнее</a>
                        <a class="btn2" href="appointment.php?service=Отбеливание зубов">Записаться</a>
                    </div>
                </div>
            </div>

            <div class="service-card tilt-card">
                <img src="images/braces.jpg" alt="Брекеты">
                <div class="service-content no-icon">
                    <h3>Брекет-системы</h3>
                    <p>Эффективное исправление прикуса и выравнивание зубов под контролем ортодонта.</p>
                    <div class="buttons">
                        <a class="btn" href="service-detail.php?service=braces">Подробнее</a>
                        <a class="btn2" href="appointment.php?service=Брекеты">Записаться</a>
                    </div>
                </div>
            </div>

            <div class="service-card tilt-card">
                <img src="images/prosthetics.jpg" alt="Протезирование">
                <div class="service-content no-icon">
                    <h3>Протезирование</h3>
                    <p>Коронки, мосты и современные ортопедические решения для восстановления зубов.</p>
                    <div class="buttons">
                        <a class="btn" href="service-detail.php?service=prosthetics">Подробнее</a>
                        <a class="btn2" href="appointment.php?service=Протезирование">Записаться</a>
                    </div>
                </div>
            </div>

            <div class="service-card tilt-card">
                <img src="images/extraction.jpg" alt="Удаление зубов">
                <div class="service-content no-icon">
                    <h3>Удаление зубов</h3>
                    <p>Безопасное удаление зубов любой сложности с современной анестезией.</p>
                    <div class="buttons">
                        <a class="btn" href="service-detail.php?service=removal">Подробнее</a>
                        <a class="btn2" href="appointment.php?service=Удаление зуба">Записаться</a>
                    </div>
                </div>
            </div>

            <div class="service-card tilt-card">
                <img src="images/children.jpg" alt="Детская стоматология">
                <div class="service-content no-icon">
                    <h3>Детская стоматология</h3>
                    <p>Бережное лечение молочных и постоянных зубов у детей в спокойной атмосфере.</p>
                    <div class="buttons">
                        <a class="btn" href="service-detail.php?service=children">Подробнее</a>
                        <a class="btn2" href="appointment.php?service=Детская стоматология">Записаться</a>
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

            const cards = document.querySelectorAll(".tilt-card");

            cards.forEach(card => {
                card.addEventListener("mousemove", (e) => {
                    const rect = card.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    const centerX = rect.width / 2;
                    const centerY = rect.height / 2;

                    const rotateX = ((y - centerY) / centerY) * -6;
                    const rotateY = ((x - centerX) / centerX) * 6;

                    card.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-6px)`;
                });

                card.addEventListener("mouseleave", () => {
                    card.style.transform = "rotateX(0deg) rotateY(0deg) translateY(0)";
                });
            });
        });
    </script>

    <?php include 'ai-widget.php'; ?>

</body>

</html>