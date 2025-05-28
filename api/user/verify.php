<?php
session_start();
require_once '../../include/db.php';

header('Content-Type: application/json');

$token = $_GET['token'] ?? '';

if (empty($token)) {
    // http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Токен не предоставлен']);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE verification_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Неверный токен верификации']);
        exit();
    }

    // 2. Обновляем запись пользователя
    $stmt = $pdo->prepare("UPDATE users SET verification_token = NULL, email_verified = 1 WHERE id = ?");
    $stmt->execute([$user['id']]);
    
    // 3. Перенаправляем на главную с сообщением об успехе
    $_SESSION['verification_success'] = 'Ваш email успешно подтверждён!';
    header("Location: http://localhost/index.php");
    exit();

} catch (PDOException $e) {
    error_log("Ошибка верификации: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Ошибка базы данных']);
    exit();
}