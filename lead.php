<?php
declare(strict_types=1);

require __DIR__ . '/config.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo 'Method Not Allowed';
    exit;
}

$input = [
    'name' => trim((string) ($_POST['name'] ?? '')),
    'phone' => trim((string) ($_POST['phone'] ?? '')),
    'service' => trim((string) ($_POST['service'] ?? '')),
    'message' => trim((string) ($_POST['message'] ?? '')),
    'consent' => (string) ($_POST['consent'] ?? ''),
    'source' => trim((string) ($_POST['source'] ?? 'landing')),
    'honeypot' => trim((string) ($_POST['honeypot'] ?? '')),
    'csrf_token' => (string) ($_POST['csrf_token'] ?? ''),
];

$errors = [];

if (!hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $input['csrf_token'])) {
    $errors['form'] = 'Обновите страницу и отправьте заявку еще раз.';
}

if ($input['honeypot'] !== '') {
    $errors['form'] = 'Заявка не отправлена. Попробуйте еще раз.';
}

if ($input['phone'] === '') {
    $errors['phone'] = 'Введите телефон.';
} elseif (!preg_match('/^[0-9+()\-\s]{10,24}$/u', $input['phone'])) {
    $errors['phone'] = 'Введите телефон в формате +7 900 000-00-00.';
}

if ($input['name'] !== '' && mb_strlen($input['name']) > 80) {
    $errors['name'] = 'Имя слишком длинное.';
}

if (mb_strlen($input['service']) > 120) {
    $errors['service'] = 'Выберите услугу из списка.';
}

if (mb_strlen($input['message']) > 1200) {
    $errors['message'] = 'Сократите описание до 1200 символов.';
}

if ($input['consent'] !== '1') {
    $errors['consent'] = 'Подтвердите согласие на обработку данных.';
}

if (!$errors && is_rate_limited()) {
    $errors['form'] = 'Слишком много заявок подряд. Попробуйте отправить еще раз через несколько минут.';
}

if ($errors) {
    respond_with_errors($errors, $input);
}

save_lead($input);

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

if (is_ajax_request()) {
    json_response([
        'ok' => true,
        'message' => 'Заявка отправлена. Мы свяжемся с вами по телефону.',
        'csrfToken' => $_SESSION['csrf_token'],
    ]);
}

$_SESSION['lead_flash'] = [
    'type' => 'success',
    'message' => 'Заявка отправлена. Мы свяжемся с вами по телефону.',
];

header('Location: /?lead=success#lead-form', true, 303);
exit;

function respond_with_errors(array $errors, array $input): never
{
    if (is_ajax_request()) {
        http_response_code(422);
        json_response([
            'ok' => false,
            'errors' => $errors,
        ]);
    }

    $_SESSION['lead_errors'] = $errors;
    $_SESSION['lead_old'] = [
        'name' => $input['name'],
        'phone' => $input['phone'],
        'service' => $input['service'],
        'message' => $input['message'],
        'consent' => $input['consent'] === '1',
    ];
    $_SESSION['lead_flash'] = [
        'type' => 'error',
        'message' => $errors['form'] ?? 'Проверьте поля формы и отправьте заявку еще раз.',
    ];

    header('Location: /?lead=error#lead-form', true, 303);
    exit;
}

function json_response(array $payload): never
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function save_lead(array $input): void
{
    $dir = dirname(LEADS_CSV);

    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $isNew = !file_exists(LEADS_CSV);
    $handle = fopen(LEADS_CSV, 'ab');

    if (!$handle) {
        throw new RuntimeException('Cannot open leads storage.');
    }

    flock($handle, LOCK_EX);

    if ($isNew) {
        fputcsv($handle, ['created_at', 'name', 'phone', 'service', 'message', 'source', 'ip', 'user_agent'], ',', '"', '');
    }

    fputcsv($handle, [
        date(DATE_ATOM),
        $input['name'],
        $input['phone'],
        $input['service'],
        $input['message'],
        $input['source'],
        $_SERVER['REMOTE_ADDR'] ?? '',
        substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
    ], ',', '"', '');

    flock($handle, LOCK_UN);
    fclose($handle);
}

function is_rate_limited(): bool
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = hash('sha256', $ip);
    $now = time();
    $window = 10 * 60;
    $maxAttempts = 5;
    $dir = dirname(RATE_LIMIT_FILE);

    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $handle = fopen(RATE_LIMIT_FILE, 'c+');

    if (!$handle) {
        return false;
    }

    flock($handle, LOCK_EX);
    $contents = stream_get_contents($handle);
    $data = $contents ? json_decode($contents, true) : [];

    if (!is_array($data)) {
        $data = [];
    }

    foreach ($data as $storedKey => $events) {
        $data[$storedKey] = array_values(array_filter((array) $events, static fn ($time): bool => (int) $time > $now - $window));

        if (!$data[$storedKey]) {
            unset($data[$storedKey]);
        }
    }

    $events = $data[$key] ?? [];
    $limited = count($events) >= $maxAttempts;
    $events[] = $now;
    $data[$key] = $events;

    ftruncate($handle, 0);
    rewind($handle);
    fwrite($handle, json_encode($data));
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);

    return $limited;
}
