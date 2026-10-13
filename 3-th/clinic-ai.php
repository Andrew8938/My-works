<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$knowledge = require 'knowledge.php';

$message = trim(isset($_POST['message']) ? $_POST['message'] : '');
$action = trim(isset($_POST['action']) ? $_POST['action'] : '');

if (!isset($_SESSION['clinic_ai'])) {
    $_SESSION['clinic_ai'] = [
        'history' => [],
        'last_service' => null,
        'last_doctor_key' => null,
        'last_topic' => null
    ];
}

if ($action === 'reset') {
    $_SESSION['clinic_ai'] = [
        'history' => [],
        'last_service' => null,
        'last_doctor_key' => null,
        'last_topic' => null
    ];

    echo json_encode([
        'reply' => 'Диалог очищен. Можете задать новый вопрос.',
        'reset' => true
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($message === '') {
    echo json_encode([
        'reply' => 'Пожалуйста, напишите ваш вопрос.',
        'is_urgent' => false,
        'urgent_text' => null,
        'booking_url' => null,
        'booking_label' => null,
        'recommendation' => null
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/* =========================
   НОРМАЛИЗАЦИЯ ТЕКСТА
========================= */

function normalizeText($text)
{
    $text = mb_strtolower($text, 'UTF-8');
    $text = str_replace(['ё'], ['е'], $text);
    $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
    $text = preg_replace('/\s+/u', ' ', $text);
    return trim($text);
}

$text = normalizeText($message);

/* =========================
   ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ
========================= */

function randomItem($items)
{
    return $items[array_rand($items)];
}

function tokenizeText($text)
{
    $text = normalizeText($text);

    if ($text === '') {
        return [];
    }

    $parts = explode(' ', $text);
    $tokens = [];

    foreach ($parts as $part) {
        $part = trim($part);

        if ($part === '') {
            continue;
        }

        if (mb_strlen($part, 'UTF-8') < 2) {
            continue;
        }

        $tokens[] = $part;
    }

    return array_values(array_unique($tokens));
}

function containsExactPhrase($text, $phrase)
{
    $phrase = normalizeText($phrase);

    if ($phrase === '') {
        return false;
    }

    return mb_strpos($text, $phrase, 0, 'UTF-8') !== false;
}

function tokenOverlapScore($textTokens, $phrase)
{
    $phraseTokens = tokenizeText($phrase);

    if (empty($phraseTokens)) {
        return 0;
    }

    $matched = 0;

    foreach ($phraseTokens as $token) {
        if (in_array($token, $textTokens, true)) {
            $matched++;
        }
    }

    $ratio = $matched / count($phraseTokens);

    if (count($phraseTokens) === 1) {
        return $matched === 1 ? 1.0 : 0.0;
    }

    return $ratio;
}

function containsAny($text, $keywords)
{
    $textTokens = tokenizeText($text);

    foreach ($keywords as $keyword) {
        if (containsExactPhrase($text, $keyword)) {
            return true;
        }

        $score = tokenOverlapScore($textTokens, $keyword);

        if ($score >= 1.0) {
            return true;
        }

        if ($score >= 0.75 && count(tokenizeText($keyword)) >= 2) {
            return true;
        }
    }

    return false;
}

function scoreKeywords($text, $keywords)
{
    $textTokens = tokenizeText($text);
    $score = 0;

    foreach ($keywords as $keyword) {
        $phraseTokens = tokenizeText($keyword);
        $tokenCount = count($phraseTokens);

        if ($tokenCount === 0) {
            continue;
        }

        if (containsExactPhrase($text, $keyword)) {
            $score += 5 + $tokenCount;
            continue;
        }

        $overlap = tokenOverlapScore($textTokens, $keyword);

        if ($tokenCount === 1) {
            if ($overlap >= 1.0) {
                $score += 2;
            }
            continue;
        }

        if ($overlap >= 1.0) {
            $score += 4 + $tokenCount;
        } elseif ($overlap >= 0.75) {
            $score += 3;
        } elseif ($overlap >= 0.5 && $tokenCount >= 3) {
            $score += 1;
        }
    }

    return $score;
}

function getDoctorData($knowledge, $doctorKey)
{
    if ($doctorKey && !empty($knowledge['doctors'][$doctorKey])) {
        return $knowledge['doctors'][$doctorKey];
    }

    return [
        'name' => 'Специалист клиники',
        'specialty' => '',
        'description' => ''
    ];
}

function findServiceByName($knowledge, $serviceName)
{
    if (!$serviceName) {
        return null;
    }

    foreach ($knowledge['services'] as $service) {
        if ($service['name'] === $serviceName) {
            return $service;
        }
    }

    return null;
}

function buildRecommendation($knowledge, $service, $actions)
{
    $doctor = getDoctorData($knowledge, isset($service['doctor_key']) ? $service['doctor_key'] : null);

    return [
        'service' => isset($service['name']) ? $service['name'] : 'Первичная консультация',
        'doctor' => $doctor['name'] . (!empty($doctor['specialty']) ? ' — ' . $doctor['specialty'] : ''),
        'actions' => $actions
    ];
}

function saveContext($service, $topic)
{
    if ($service) {
        $_SESSION['clinic_ai']['last_service'] = isset($service['name']) ? $service['name'] : null;
        $_SESSION['clinic_ai']['last_doctor_key'] = isset($service['doctor_key']) ? $service['doctor_key'] : null;
    }

    $_SESSION['clinic_ai']['last_topic'] = $topic;
}

function pushHistory($role, $text)
{
    $_SESSION['clinic_ai']['history'][] = [
        'role' => $role,
        'text' => $text
    ];

    if (count($_SESSION['clinic_ai']['history']) > 12) {
        $_SESSION['clinic_ai']['history'] = array_slice($_SESSION['clinic_ai']['history'], -12);
    }
}

function mergeKeywordsFromGroups($knowledge, $groupRefs)
{
    $keywords = [];

    if (!is_array($groupRefs)) {
        return $keywords;
    }

    foreach ($groupRefs as $groupName) {
        if (!empty($knowledge['symptom_groups'][$groupName]) && is_array($knowledge['symptom_groups'][$groupName])) {
            $keywords = array_merge($keywords, $knowledge['symptom_groups'][$groupName]);
        }
    }

    return array_values(array_unique($keywords));
}

function detectBestFaq($knowledge, $text)
{
    $bestFaq = null;
    $bestScore = 0;

    if (empty($knowledge['procedure_faq']) || !is_array($knowledge['procedure_faq'])) {
        return ['faq' => null, 'score' => 0];
    }

    foreach ($knowledge['procedure_faq'] as $faq) {
        $score = scoreKeywords($text, isset($faq['keywords']) ? $faq['keywords'] : []);

        if ($score > $bestScore) {
            $bestScore = $score;
            $bestFaq = $faq;
        }
    }

    return [
        'faq' => $bestFaq,
        'score' => $bestScore
    ];
}

function detectBestService($knowledge, $text)
{
    $bestService = null;
    $bestScore = 0;
    $groupMatches = [];

    if (empty($knowledge['services']) || !is_array($knowledge['services'])) {
        return [
            'service' => null,
            'score' => 0,
            'group_matches' => []
        ];
    }

    foreach ($knowledge['services'] as $service) {
        $groupRefs = isset($service['group_refs']) ? $service['group_refs'] : [];
        $keywords = mergeKeywordsFromGroups($knowledge, $groupRefs);

        $score = scoreKeywords($text, $keywords);

        $serviceGroupMatches = [];

        foreach ($groupRefs as $groupName) {
            if (!empty($knowledge['symptom_groups'][$groupName])) {
                $groupScore = scoreKeywords($text, $knowledge['symptom_groups'][$groupName]);
                if ($groupScore > 0) {
                    $serviceGroupMatches[$groupName] = $groupScore;
                }
            }
        }

        if ($score > $bestScore) {
            $bestScore = $score;
            $bestService = $service;
            $groupMatches = $serviceGroupMatches;
        }
    }

    return [
        'service' => $bestService,
        'score' => $bestScore,
        'group_matches' => $groupMatches
    ];
}

function addHelpfulTip($knowledge, $service)
{
    $name = isset($service['name']) ? $service['name'] : '';

    if ($name === 'Профессиональная чистка' && !empty($knowledge['prevention_tips'])) {
        return ' ' . randomItem($knowledge['prevention_tips']);
    }

    if ($name === 'Отбеливание зубов' && !empty($knowledge['esthetics_tips'])) {
        return ' ' . randomItem($knowledge['esthetics_tips']);
    }

    if ($name === 'Детская стоматология' && !empty($knowledge['child_tips'])) {
        return ' ' . randomItem($knowledge['child_tips']);
    }

    if (!empty($knowledge['prevention_tips'])) {
        return ' ' . randomItem($knowledge['prevention_tips']);
    }

    return '';
}

/* =========================
   СРОЧНОСТЬ
========================= */

$isUrgent = containsAny($text, isset($knowledge['urgent_keywords']) ? $knowledge['urgent_keywords'] : []);

$urgentText = null;
if ($isUrgent) {
    $urgentText = 'Похоже, ситуация может быть срочной. При сильной боли, отёке, температуре, кровотечении или травме зуба лучше обратиться в клинику как можно скорее.';
}

/* =========================
   SMALL TALK
========================= */

if (!empty($knowledge['small_talk']) && is_array($knowledge['small_talk'])) {
    foreach ($knowledge['small_talk'] as $key => $answer) {
        if (containsExactPhrase($text, $key) && mb_strlen($text, 'UTF-8') < 50) {
            pushHistory('user', $message);
            pushHistory('assistant', $answer);

            echo json_encode([
                'reply' => $answer,
                'is_urgent' => false,
                'urgent_text' => null,
                'booking_url' => 'appointment.php',
                'booking_label' => 'Записаться на консультацию',
                'recommendation' => [
                    'service' => 'Первичная консультация',
                    'doctor' => 'Специалист будет подобран после уточнения жалоб',
                    'actions' => 'Опишите симптомы подробнее или перейдите к записи на консультацию.'
                ]
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}

$lastService = findServiceByName($knowledge, isset($_SESSION['clinic_ai']['last_service']) ? $_SESSION['clinic_ai']['last_service'] : null);
$hasReference = containsAny($text, isset($knowledge['follow_up_reference_words']) ? $knowledge['follow_up_reference_words'] : []);

/* =========================
   FOLLOW-UP: КАКОЙ ВРАЧ
========================= */

if (containsAny($text, isset($knowledge['doctor_question_intents']) ? $knowledge['doctor_question_intents'] : []) && $lastService !== null) {
    $doctor = getDoctorData($knowledge, isset($lastService['doctor_key']) ? $lastService['doctor_key'] : null);

    $reply = 'По предыдущему запросу вам лучше обратиться к врачу ' . $doctor['name'];

    if (!empty($doctor['specialty'])) {
        $reply .= ' (' . $doctor['specialty'] . ')';
    }

    $reply .= '. ';
    $reply .= randomItem($lastService['actions']);

    if ($isUrgent) {
        $reply .= ' ' . $urgentText;
    }

    pushHistory('user', $message);
    pushHistory('assistant', $reply);
    saveContext($lastService, 'doctor');

    echo json_encode([
        'reply' => $reply,
        'is_urgent' => $isUrgent,
        'urgent_text' => $urgentText,
        'booking_url' => $lastService['booking_url'],
        'booking_label' => 'Записаться: ' . $lastService['name'],
        'recommendation' => buildRecommendation($knowledge, $lastService, randomItem($lastService['actions']))
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/* =========================
   FOLLOW-UP: ЗАПИСЬ
========================= */

if (containsAny($text, isset($knowledge['booking_intents']) ? $knowledge['booking_intents'] : [])) {
    if ($lastService !== null) {
        $doctor = getDoctorData($knowledge, isset($lastService['doctor_key']) ? $lastService['doctor_key'] : null);

        $reply = 'Хорошо. По нашему диалогу наиболее подходит услуга «' . $lastService['name'] . '». ';
        $reply .= 'Рекомендуемый специалист: ' . $doctor['name'];

        if (!empty($doctor['specialty'])) {
            $reply .= ' (' . $doctor['specialty'] . ')';
        }

        $reply .= '. Ниже вы можете перейти к записи.';

        pushHistory('user', $message);
        pushHistory('assistant', $reply);
        saveContext($lastService, 'booking');

        echo json_encode([
            'reply' => $reply,
            'is_urgent' => $isUrgent,
            'urgent_text' => $urgentText,
            'booking_url' => $lastService['booking_url'],
            'booking_label' => 'Записаться: ' . $lastService['name'],
            'recommendation' => buildRecommendation($knowledge, $lastService, 'Перейдите к записи и выберите удобные дату и время.')
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $reply = 'Конечно. Вы можете перейти к форме записи на первичную консультацию, а специалист будет подобран после уточнения жалоб.';

    pushHistory('user', $message);
    pushHistory('assistant', $reply);

    echo json_encode([
        'reply' => $reply,
        'is_urgent' => $isUrgent,
        'urgent_text' => $urgentText,
        'booking_url' => 'appointment.php',
        'booking_label' => 'Записаться на консультацию',
        'recommendation' => [
            'service' => 'Первичная консультация',
            'doctor' => 'Специалист будет подобран после уточнения жалоб',
            'actions' => 'Перейдите к форме записи и укажите удобное время.'
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/* =========================
   FOLLOW-UP ПО КОНТЕКСТУ
========================= */

if ($lastService !== null && $hasReference) {
    if (containsAny($text, isset($knowledge['pain_question_intents']) ? $knowledge['pain_question_intents'] : [])) {
        $reply = !empty($lastService['pain_answer'])
            ? $lastService['pain_answer']
            : 'Точные ощущения зависят от клинической ситуации, это лучше уточнить у врача на консультации.';

        pushHistory('user', $message);
        pushHistory('assistant', $reply);

        echo json_encode([
            'reply' => $reply,
            'is_urgent' => $isUrgent,
            'urgent_text' => $urgentText,
            'booking_url' => $lastService['booking_url'],
            'booking_label' => 'Записаться: ' . $lastService['name'],
            'recommendation' => buildRecommendation($knowledge, $lastService, 'Если хотите, можно сразу перейти к записи на консультацию.')
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (containsAny($text, isset($knowledge['process_question_intents']) ? $knowledge['process_question_intents'] : [])) {
        $reply = !empty($lastService['process_answer'])
            ? $lastService['process_answer']
            : 'Этапы процедуры зависят от клинической ситуации и уточняются врачом после осмотра.';

        pushHistory('user', $message);
        pushHistory('assistant', $reply);

        echo json_encode([
            'reply' => $reply,
            'is_urgent' => $isUrgent,
            'urgent_text' => $urgentText,
            'booking_url' => $lastService['booking_url'],
            'booking_label' => 'Записаться: ' . $lastService['name'],
            'recommendation' => buildRecommendation($knowledge, $lastService, 'Если хотите, можно записаться на консультацию и уточнить план лечения.')
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (containsAny($text, isset($knowledge['duration_question_intents']) ? $knowledge['duration_question_intents'] : [])) {
        $reply = !empty($lastService['duration_answer'])
            ? $lastService['duration_answer']
            : 'Сроки зависят от клинической ситуации и определяются после осмотра.';

        pushHistory('user', $message);
        pushHistory('assistant', $reply);

        echo json_encode([
            'reply' => $reply,
            'is_urgent' => $isUrgent,
            'urgent_text' => $urgentText,
            'booking_url' => $lastService['booking_url'],
            'booking_label' => 'Записаться: ' . $lastService['name'],
            'recommendation' => buildRecommendation($knowledge, $lastService, 'Запишитесь на консультацию для уточнения сроков и этапов.')
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (containsAny($text, isset($knowledge['preparation_question_intents']) ? $knowledge['preparation_question_intents'] : [])) {
        $reply = !empty($lastService['preparation_answer'])
            ? $lastService['preparation_answer']
            : 'Подготовка зависит от процедуры и уточняется на консультации.';

        pushHistory('user', $message);
        pushHistory('assistant', $reply);

        echo json_encode([
            'reply' => $reply,
            'is_urgent' => $isUrgent,
            'urgent_text' => $urgentText,
            'booking_url' => $lastService['booking_url'],
            'booking_label' => 'Записаться: ' . $lastService['name'],
            'recommendation' => buildRecommendation($knowledge, $lastService, 'Лучше сначала записаться на консультацию и уточнить подготовку.')
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (containsAny($text, isset($knowledge['price_question_intents']) ? $knowledge['price_question_intents'] : [])) {
        $reply = !empty($lastService['price_answer'])
            ? $lastService['price_answer']
            : 'Стоимость зависит от клинической ситуации и объёма лечения. Точную цену лучше уточнить после консультации.';

        pushHistory('user', $message);
        pushHistory('assistant', $reply);

        echo json_encode([
            'reply' => $reply,
            'is_urgent' => $isUrgent,
            'urgent_text' => $urgentText,
            'booking_url' => $lastService['booking_url'],
            'booking_label' => 'Записаться: ' . $lastService['name'],
            'recommendation' => buildRecommendation($knowledge, $lastService, 'Если хотите, можно записаться на консультацию для точной оценки стоимости.')
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

/* =========================
   FAQ
========================= */

$faqResult = detectBestFaq($knowledge, $text);
$bestFaq = $faqResult['faq'];
$bestFaqScore = $faqResult['score'];

if ($bestFaq !== null && $bestFaqScore >= 3) {
    $doctor = getDoctorData($knowledge, isset($bestFaq['doctor_key']) ? $bestFaq['doctor_key'] : null);
    $reply = $bestFaq['answer'] . ' ' . $bestFaq['actions'];

    if ($isUrgent) {
        $reply .= ' ' . $urgentText;
    }

    $serviceForContext = findServiceByName($knowledge, isset($bestFaq['service']) ? $bestFaq['service'] : null);

    if ($serviceForContext !== null) {
        saveContext($serviceForContext, 'faq');
    }

    pushHistory('user', $message);
    pushHistory('assistant', $reply);

    echo json_encode([
        'reply' => $reply,
        'is_urgent' => $isUrgent,
        'urgent_text' => $urgentText,
        'booking_url' => 'appointment.php?service=' . urlencode($bestFaq['service']),
        'booking_label' => 'Записаться: ' . $bestFaq['service'],
        'recommendation' => [
            'service' => $bestFaq['service'],
            'doctor' => $doctor['name'] . (!empty($doctor['specialty']) ? ' — ' . $doctor['specialty'] : ''),
            'actions' => $bestFaq['actions']
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/* =========================
   ОПРЕДЕЛЕНИЕ УСЛУГИ ПО ГРУППАМ
========================= */

$serviceResult = detectBestService($knowledge, $text);
$bestService = $serviceResult['service'];
$bestServiceScore = $serviceResult['score'];

if ($bestService !== null && $bestServiceScore >= 2) {
    $doctor = getDoctorData($knowledge, isset($bestService['doctor_key']) ? $bestService['doctor_key'] : null);

    $reply = randomItem($bestService['answers']) . ' ';
    $reply .= 'Рекомендуемая услуга: ' . $bestService['name'] . '. ';
    $reply .= 'Подходящий специалист: ' . $doctor['name'];

    if (!empty($doctor['specialty'])) {
        $reply .= ' (' . $doctor['specialty'] . ')';
    }

    $reply .= '. ';
    $reply .= randomItem($bestService['actions']);
    $reply .= addHelpfulTip($knowledge, $bestService);

    if ($isUrgent) {
        $reply .= ' ' . $urgentText;
    }

    saveContext($bestService, 'symptom');
    pushHistory('user', $message);
    pushHistory('assistant', $reply);

    echo json_encode([
        'reply' => $reply,
        'is_urgent' => $isUrgent,
        'urgent_text' => $urgentText,
        'booking_url' => $bestService['booking_url'],
        'booking_label' => 'Записаться: ' . $bestService['name'],
        'recommendation' => buildRecommendation($knowledge, $bestService, randomItem($bestService['actions']))
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/* =========================
   ВОПРОС ПРО ВРАЧА БЕЗ КОНТЕКСТА
========================= */

if (containsAny($text, isset($knowledge['doctor_question_intents']) ? $knowledge['doctor_question_intents'] : [])) {
    $reply = 'Чтобы точно подсказать врача, мне нужно понять вашу ситуацию. Напишите, что именно вас беспокоит или какая процедура вас интересует.';

    pushHistory('user', $message);
    pushHistory('assistant', $reply);

    echo json_encode([
        'reply' => $reply,
        'is_urgent' => $isUrgent,
        'urgent_text' => $urgentText,
        'booking_url' => 'appointment.php',
        'booking_label' => 'Записаться на консультацию',
        'recommendation' => [
            'service' => 'Первичная консультация',
            'doctor' => 'Специалист будет подобран после уточнения жалоб',
            'actions' => 'Опишите жалобы подробнее или перейдите к записи на консультацию.'
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/* =========================
   FALLBACK
========================= */

$fallbackReply = randomItem($knowledge['fallback_answers']) . ' ' . randomItem($knowledge['fallback_actions']);

if ($isUrgent) {
    $fallbackReply .= ' ' . $urgentText;
}

pushHistory('user', $message);
pushHistory('assistant', $fallbackReply);

echo json_encode([
    'reply' => $fallbackReply,
    'is_urgent' => $isUrgent,
    'urgent_text' => $urgentText,
    'booking_url' => 'appointment.php',
    'booking_label' => 'Записаться на консультацию',
    'recommendation' => [
        'service' => 'Первичная консультация',
        'doctor' => 'Специалист будет подобран после уточнения жалоб',
        'actions' => randomItem($knowledge['fallback_actions'])
    ]
], JSON_UNESCAPED_UNICODE);