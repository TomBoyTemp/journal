
<?php
// api/auth/register.php
session_start();
header('Content-Type: application/json');

require_once '../../include/db.php'; // Подключение к БД
require_once '../../include/functions.php'; // sanitizeInput()

$input = json_decode(file_get_contents('php://input'), true);

$email = sanitizeInput($input['email'] ?? '');
$password = $input['password'] ?? '';
$name = sanitizeInput($input['name'] ?? '');
$surname = sanitizeInput($input['surname'] ?? '');
$organization = sanitizeInput($input['organization'] ?? '');
$country = sanitizeInput($input['country'] ?? '');
$consentEmail = isset($input['consent_email']) ? (int)(bool)$input['consent_email'] : 0;
$isReviewer = isset($input['is_reviewer']) ? (int)(bool)$input['is_reviewer'] : 0;
$reviewerInterests = sanitizeInput($input['reviewer_interests'] ?? null);
$consentRule = isset($input['consentRule']) ? (int)(bool)$input['consentRule'] : 0;

$errors = [];

// Валидация
if (empty($consentRule)) {
    $errors['consentRule'] = 'Необходимо согласиться с Уведомлением о конфидециальности.';
}

if (empty($email)) {
    $errors['email'] = 'Email не может быть пустым.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Некорректный формат email.';
}

if (empty($password)) {
    $errors['password'] = 'Пароль не может быть пустым.';
} elseif (strlen($password) < 6) {
    $errors['password'] = 'Пароль должен быть не менее 6 символов.';
}

if (empty($name)) {
    $errors['name'] = 'Имя не может быть пустым.';
} elseif (!preg_match('/^[\p{L}\s\-\']++$/u', $name)) {
    $errors['name'] = 'Имя должно содержать только буквы, пробелы и дефисы.';
}

if (empty($surname)) {
    $errors['surname'] = 'Фамилия не может быть пустой.';
} elseif (!preg_match('/^[\p{L}\s\-\']++$/u', $surname)) {
    $errors['surname'] = 'Фамилия должна содержать только буквы, пробелы и дефисы.';
}

if (empty($organization)) {
    $errors['organization'] = 'Организация не может быть пустой.';
}

if (empty($country)) {
    $errors['country'] = 'Страна не может быть пустой.';
}

if ($isReviewer && empty($reviewerInterests)) {
    $errors['reviewer_interests'] = 'Укажите интересы как рецензента.';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Ошибка валидации.', 'errors' => $errors]);
    exit();
}

try {
    $pdo->beginTransaction();

    // Проверяем, не занят ли email
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $pdo->rollBack();
        http_response_code(409); // Conflict
        echo json_encode(['status' => 'error', 'message' => 'Пользователь с таким email уже зарегистрирован.']);
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

     // Генерация и сохранение токена для верификации email
    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

    
    $stmt = $pdo->prepare("INSERT INTO users (email, email_verified, password_hash, verification_token, expires_at) VALUES (?, FALSE, ?, ?, ?)");
    $stmt->execute([$email, $hashedPassword, $token, $expiresAt]);
   
    $userId = $pdo->lastInsertId();
    
    $stmtAbout = $pdo->prepare("INSERT INTO about_user (id, first_name, last_name, organization, country) VALUES (?, ?, ?, ?, ?)");
    $stmtAbout->execute([$userId, $name, $surname, $organization, $country]);
   
   
    $stmtAbout = $pdo->prepare("INSERT INTO user_consents (user_id, data_processing, notifications, want_reviewer, reviewer_interests) VALUES (?, ?, ?, ?, ?)");
    $stmtAbout->execute([$userId, $consentRule, $consentEmail, $isReviewer, $reviewerInterests]);
    
    
    $stmt = $pdo->prepare("INSERT INTO user_roles (users_id, role_id) SELECT ?, id FROM roles WHERE name = ?");
    $stmt->execute([$userId, 'author']);
  
    $pdo->commit();

    sendVerificationEmail($email, $token);

    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Регистрация прошла успешно! Пожалуйста, проверьте свою почту для подтверждения регистрации.']);

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Ошибка регистрации: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Произошла ошибка сервера. Пожалуйста, попробуйте позже.' . $error]);
}