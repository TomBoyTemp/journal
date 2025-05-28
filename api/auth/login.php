<?php
session_start();
header('Content-Type: application/json'); // Указываем, что ответ будет в формате JSON

require_once '../../include/db.php'; // Подключение к БД
require_once '../../include/functions.php'; // sanitizeInput()

$input = json_decode(file_get_contents('php://input'), true);
$email = sanitizeInput($input['email'] ?? '');
$password = $input['password'] ?? '';

$errors = [];

if (empty($email)) {
    $errors['email'] = 'Email не может быть пустым.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Некорректный формат email.';
}

if (empty($password)) {
    $errors['password'] = 'Пароль не может быть пустым.';
}

if (!empty($errors)) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Ошибка валидации.', 'errors' => $errors]);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT u.id, u.email, password_hash, email_verified, au.first_name, au.last_name FROM users u LEFT JOIN about_user au ON u.id = au.id WHERE u.email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        $stmt = $pdo->prepare("SELECT r.id, r.name, r.description FROM user_roles ur JOIN roles r ON ur.role_id = r.id WHERE ur.users_id = ?");
        $stmt->execute([$user['id']]);
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!$user['email_verified']) {
            http_response_code(403); // Forbidden
            echo json_encode(['status' => 'error', 'message' => 'Ваш аккаунт не подтвержден. Пожалуйста, проверьте почту для подтверждения регистрации.']);
            exit();
        }

        // Успешный вход, сохраняем данные пользователя в сессии
        $_SESSION['user'] = [
            'id' => $user['id'],
            'first_name' => $user['first_name'] ?? '',
            'last_name' => $user['last_name'] ?? '',
            'email' => $user['email'],
            'role' => array_column($roles, 'name')
        ];
        $_SESSION['authenticated'] = true;

        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Вход выполнен успешно!', 'user' => $_SESSION['user']]);
    } else {
        http_response_code(401); // Unauthorized
        echo json_encode(['status' => 'error', 'message' => 'Неверный email или пароль.']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    error_log("Ошибка входа: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Произошла ошибка сервера.']);
}