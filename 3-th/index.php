<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>DentalCare | Стоматологическая клиника</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <?php include 'header.php'; ?>

    <section class="hero">
        <div class="hero-content">
            <div class="hero-text reveal active">
                <div class="hero-badge">Премиальная стоматология европейского уровня</div>

                <h1>Эстетика, здоровье и комфорт вашей улыбки</h1>

                <p>
                    Современная стоматологическая клиника для всей семьи: лечение, имплантация,
                    ортодонтия, профилактика и эстетическая стоматология с вниманием к каждому пациенту.
                </p>

                <div class="hero-actions">
                    <a class="btn" href="appointment.php">Записаться на приём</a>
                    <a class="btn-outline" href="services.php">Смотреть услуги</a>
                </div>
            </div>

            <div class="hero-card reveal active">
                <h3>Почему пациенты выбирают нас</h3>
                <ul>
                    <li>Современное цифровое оборудование</li>
                    <li>Опытные врачи с узкой специализацией</li>
                    <li>Безболезненное и бережное лечение</li>
                    <li>Индивидуальный план лечения и сопровождение</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="advantages reveal active">
        <h2 class="title">Почему выбирают нашу клинику</h2>
        <p class="section-subtitle">
            Мы объединяем современные технологии, комфорт и профессиональный подход,
            чтобы каждая процедура проходила спокойно, безопасно и эффективно.
        </p>

        <div class="adv-grid">
            <div class="adv">
                <div class="adv-icon">🦷</div>
                <h3>Современное оборудование</h3>
                <p>Используем цифровую диагностику и передовые технологии лечения зубов.</p>
            </div>

            <div class="adv">
                <div class="adv-icon">👨‍⚕️</div>
                <h3>Опытные врачи</h3>
                <p>Наши специалисты работают по современным протоколам и регулярно повышают квалификацию.</p>
            </div>

            <div class="adv">
                <div class="adv-icon">💙</div>
                <h3>Безболезненное лечение</h3>
                <p>Применяем щадящие методы и комфортную анестезию для спокойного лечения.</p>
            </div>

            <div class="adv">
                <div class="adv-icon">✨</div>
                <h3>Индивидуальный подход</h3>
                <p>Для каждого пациента мы подбираем персональный план лечения и профилактики.</p>
            </div>
        </div>
    </section>

    <section class="services-preview reveal active">
        <h2 class="title">Популярные услуги</h2>
        <p class="section-subtitle">
            Выберите интересующее направление и перейдите к подробной информации или записи.
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
                    <p>Удаление налёта, зубного камня и профилактика заболеваний полости рта.</p>
                    <div class="buttons">
                        <a class="btn" href="service-detail.php?service=cleaning">Подробнее</a>
                        <a class="btn2" href="appointment.php?service=Профессиональная чистка">Записаться</a>
                    </div>
                </div>
            </div>

            <div class="service-card tilt-card">
                <img src="images/implant.jpg" alt="Имплантация">
                <div class="service-content no-icon">
                    <h3>Имплантация</h3>
                    <p>Надёжное восстановление отсутствующих зубов с помощью современных имплантов.</p>
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
                    <p>Эффективное осветление эмали для яркой, ухоженной и красивой улыбки.</p>
                    <div class="buttons">
                        <a class="btn" href="service-detail.php?service=whitening">Подробнее</a>
                        <a class="btn2" href="appointment.php?service=Отбеливание зубов">Записаться</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="stats reveal active">
        <div class="stats-grid">
            <div class="stat">
                <h2>10+</h2>
                <p>лет успешной практики</p>
            </div>

            <div class="stat">
                <h2>5000+</h2>
                <p>довольных пациентов</p>
            </div>

            <div class="stat">
                <h2>15</h2>
                <p>квалифицированных специалистов</p>
            </div>

            <div class="stat">
                <h2>98%</h2>
                <p>пациентов рекомендуют нас</p>
            </div>
        </div>
    </section>

    <section class="doctors-section reveal active">
        <h2 class="title">Наши врачи</h2>
        <p class="section-subtitle">
            Команда специалистов, которым доверяют здоровье и красоту улыбки.
        </p>

        <div class="doctors-slider-wrap">
            <button class="slider-btn slider-prev" type="button" aria-label="Назад">‹</button>

            <div class="doctors-slider">
                <div class="doctors-track" id="doctorsTrack">
                    <div class="doctor-card">
                        <img src="images/doctor1.jpg" alt="Иван Петров">
                        <div class="doctor-info">
                            <h3>Иван Петров</h3>
                            <p class="specialty">Стоматолог-терапевт</p>
                            <p class="doctor-meta">Лечение кариеса, профилактика, профессиональная гигиена</p>
                            <div class="doctor-tags">
                                <span>Кариес</span>
                                <span>Гигиена</span>
                                <span>Профилактика</span>
                            </div>
                        </div>
                    </div>

                    <div class="doctor-card">
                        <img src="images/doctor2.jpg" alt="Анна Смирнова">
                        <div class="doctor-info">
                            <h3>Анна Смирнова</h3>
                            <p class="specialty">Ортодонт</p>
                            <p class="doctor-meta">Брекеты, каппы, исправление прикуса</p>
                            <div class="doctor-tags">
                                <span>Брекеты</span>
                                <span>Каппы</span>
                                <span>Прикус</span>
                            </div>
                        </div>
                    </div>

                    <div class="doctor-card">
                        <img src="images/doctor3.jpg" alt="Дмитрий Иванов">
                        <div class="doctor-info">
                            <h3>Дмитрий Иванов</h3>
                            <p class="specialty">Имплантолог</p>
                            <p class="doctor-meta">Имплантация, удаление зубов, протезирование</p>
                            <div class="doctor-tags">
                                <span>Имплантация</span>
                                <span>Удаление</span>
                                <span>Протезирование</span>
                            </div>
                        </div>
                    </div>

                    <div class="doctor-card">
                        <img src="images/doctor1.jpg" alt="Елена Орлова">
                        <div class="doctor-info">
                            <h3>Елена Орлова</h3>
                            <p class="specialty">Гигиенист</p>
                            <p class="doctor-meta">Профессиональная чистка и профилактика заболеваний полости рта</p>
                            <div class="doctor-tags">
                                <span>AirFlow</span>
                                <span>Гигиена</span>
                                <span>Профилактика</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button class="slider-btn slider-next" type="button" aria-label="Вперёд">›</button>
        </div>
    </section>

    <section class="cta reveal active">
        <h2>Запишитесь на консультацию уже сегодня</h2>
        <p>
            Подберём удобное время, ответим на вопросы и предложим лучшее решение для вашей улыбки.
        </p>
        <a class="btn" href="appointment.php">Записаться сейчас</a>
    </section>

    <footer class="footer">
        <p>©
            <?php echo date("Y"); ?> DentalCare Clinic. Все права защищены.
        </p>
    </footer>

    <?php include 'ai-widget.php'; ?>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
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

                    card.style.transform = "rotateX(" + rotateX + "deg) rotateY(" + rotateY + "deg) translateY(-6px)";
                });

                card.addEventListener("mouseleave", () => {
                    card.style.transform = "rotateX(0deg) rotateY(0deg) translateY(0)";
                });
            });

            const track = document.getElementById("doctorsTrack");
            const prevBtn = document.querySelector(".slider-prev");
            const nextBtn = document.querySelector(".slider-next");

            if (track && prevBtn && nextBtn) {
                let currentIndex = 0;

                function getCardsPerView() {
                    if (window.innerWidth <= 640) return 1;
                    if (window.innerWidth <= 1200) return 2;
                    return 3;
                }

                function updateSlider() {
                    const cards = track.querySelectorAll(".doctor-card");
                    if (!cards.length) return;

                    const totalCards = cards.length;
                    const cardsPerView = getCardsPerView();
                    const maxIndex = Math.max(0, totalCards - cardsPerView);

                    if (currentIndex > maxIndex) {
                        currentIndex = maxIndex;
                    }

                    const cardWidth = cards[0].offsetWidth;
                    const gap = 24;
                    const offset = currentIndex * (cardWidth + gap);

                    track.style.transform = "translateX(-" + offset + "px)";
                }

                nextBtn.addEventListener("click", () => {
                    const cards = track.querySelectorAll(".doctor-card");
                    const maxIndex = Math.max(0, cards.length - getCardsPerView());
                    currentIndex = currentIndex < maxIndex ? currentIndex + 1 : 0;
                    updateSlider();
                });

                prevBtn.addEventListener("click", () => {
                    const cards = track.querySelectorAll(".doctor-card");
                    const maxIndex = Math.max(0, cards.length - getCardsPerView());
                    currentIndex = currentIndex > 0 ? currentIndex - 1 : maxIndex;
                    updateSlider();
                });

                window.addEventListener("resize", updateSlider);
                updateSlider();
            }
        });
    </script>

</body>

</html>