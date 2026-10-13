<div class="ai-chat-widget">
    <button id="aiChatToggle" class="ai-chat-toggle">ИИ-консультант</button>

    <div id="aiChatBox" class="ai-chat-box">
        <div class="ai-chat-header">
            <span>Виртуальный помощник DentalCare</span>
            <button id="aiChatReset" class="ai-chat-reset" type="button">Новый диалог</button>
        </div>

        <div id="aiChatMessages" class="ai-chat-messages">
            <div class="ai-message bot">
                Здравствуйте! Я помогу подобрать услугу, врача, объясню как проходит процедура и подскажу, как
                записаться на приём.
            </div>
        </div>

        <div class="ai-chat-input-wrap">
            <input type="text" id="aiChatInput" placeholder="Напишите ваш вопрос...">
            <button id="aiChatSend" type="button">Отправить</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const toggle = document.getElementById("aiChatToggle");
        const box = document.getElementById("aiChatBox");
        const sendBtn = document.getElementById("aiChatSend");
        const resetBtn = document.getElementById("aiChatReset");
        const input = document.getElementById("aiChatInput");
        const messages = document.getElementById("aiChatMessages");

        if (!toggle || !box || !sendBtn || !input || !messages || !resetBtn) return;

        toggle.addEventListener("click", () => {
            box.classList.toggle("open");
        });

        function scrollToBottom() {
            messages.scrollTop = messages.scrollHeight;
        }

        function addMessage(text, type) {
            const div = document.createElement("div");
            div.className = "ai-message " + type;
            div.textContent = text;
            messages.appendChild(div);
            scrollToBottom();
            return div;
        }

        function addUrgentAlert(text) {
            const div = document.createElement("div");
            div.className = "ai-urgent-alert";
            div.textContent = text;
            messages.appendChild(div);
            scrollToBottom();
        }

        function addBookingButton(url, label) {
            const wrap = document.createElement("div");
            wrap.className = "ai-booking-wrap";

            const link = document.createElement("a");
            link.href = url;
            link.className = "ai-booking-btn";
            link.textContent = label || "Записаться";

            wrap.appendChild(link);
            messages.appendChild(wrap);
            scrollToBottom();
        }

        function addRecommendationBlock(recommendation) {
            if (!recommendation) return;

            const block = document.createElement("div");
            block.className = "ai-recommendation-card";

            const title = document.createElement("div");
            title.className = "ai-recommendation-title";
            title.textContent = "Рекомендация";
            block.appendChild(title);

            if (recommendation.service) {
                const service = document.createElement("div");
                service.className = "ai-recommendation-line";
                service.innerHTML = "<strong>Услуга:</strong> " + recommendation.service;
                block.appendChild(service);
            }

            if (recommendation.doctor) {
                const doctor = document.createElement("div");
                doctor.className = "ai-recommendation-line";
                doctor.innerHTML = "<strong>Врач:</strong> " + recommendation.doctor;
                block.appendChild(doctor);
            }

            if (recommendation.actions) {
                const actions = document.createElement("div");
                actions.className = "ai-recommendation-line";
                actions.innerHTML = "<strong>Дальнейшие действия:</strong> " + recommendation.actions;
                block.appendChild(actions);
            }

            messages.appendChild(block);
            scrollToBottom();
        }

        async function sendMessage() {
            const text = input.value.trim();
            if (!text) return;

            addMessage(text, "user");
            input.value = "";

            const typing = addMessage("Печатаю ответ...", "bot typing");

            const formData = new FormData();
            formData.append("message", text);

            try {
                const response = await fetch("clinic-ai.php", {
                    method: "POST",
                    body: formData
                });

                const data = await response.json();

                if (typing) typing.remove();

                addMessage(data.reply || "Нет ответа.", "bot");

                if (data.recommendation) {
                    addRecommendationBlock(data.recommendation);
                }

                if (data.is_urgent && data.urgent_text) {
                    addUrgentAlert(data.urgent_text);
                }

                if (data.booking_url) {
                    addBookingButton(
                        data.booking_url,
                        data.booking_label || "Записаться"
                    );
                }

            } catch (e) {
                if (typing) typing.remove();
                addMessage("Ошибка локального ИИ. Проверьте файл clinic-ai.php.", "bot");
            }
        }

        async function resetDialog() {
            try {
                const formData = new FormData();
                formData.append("action", "reset");

                await fetch("clinic-ai.php", {
                    method: "POST",
                    body: formData
                });

                messages.innerHTML = "";
                addMessage("Диалог очищен. Задайте новый вопрос, и я помогу подобрать услугу или врача.", "bot");
            } catch (e) {
                addMessage("Не удалось очистить диалог.", "bot");
            }
        }

        sendBtn.addEventListener("click", sendMessage);

        resetBtn.addEventListener("click", resetDialog);

        input.addEventListener("keydown", function (e) {
            if (e.key === "Enter") {
                sendMessage();
            }
        });
    });
</script>