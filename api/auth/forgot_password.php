
<?php
session_start();
header('Content-Type: application/json');

require_once '../../include/db.php'; 
require_once '../../include/functions.php'; 

$input = json_decode(file_get_contents('php://input'), true);
$email = sanitizeInput($input['email'] ?? '');

if (empty($email)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Email не может быть пустым.']);
    exit();
}

try {
    $pdo->beginTransaction();

    // 1. Найти пользователя по email
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Важно: всегда возвращать общее сообщение об успехе/неудаче
        // чтобы не раскрывать, существует ли email в базе.
        // Это предотвращает перебор email-адресов.
        $pdo->commit(); // Завершаем транзакцию, даже если пользователь не найден
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Если ваш email зарегистрирован, вам будет отправлена инструкция по сбросу пароля.']);
        exit();
    }

    $userId = $user['id'];

    //Удалить все старые (неиспользованные) токены сброса пароля для этого пользователя
    $stmtDelete = $pdo->prepare("DELETE FROM password_reset WHERE user_id = ?");
    $stmtDelete->execute([$userId]);

    //Сгенерировать новый уникальный токен
    $token = bin2hex(random_bytes(32)); 
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

    //Сохранить токен в базе данных
    $stmtInsert = $pdo->prepare("INSERT INTO password_reset (user_id, token, expires_at) VALUES (?, ?, ?)");
    $stmtInsert->execute([$userId, $token, $expiresAt]);

    $pdo->commit();

    //Отправить email с ссылкой для сброса пароля
    try {
        $resetLink = "http://localhost/reset_password.html?token=" . $token;
        sendResetPasswordEmail($pdo, $email, $resetLink);
    } catch (Exception $e) {
        error_log("Ошибка отправки письма для сброса пароля: {$mail->ErrorInfo}");
        // Не сообщайте пользователю об ошибке отправки письма, чтобы не раскрывать детали системы.
        // Просто продолжаем показывать сообщение об успехе.
    }

    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Если ваш email зарегистрирован, вам будет отправлена инструкция по сбросу пароля.']);

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Ошибка при запросе сброса пароля: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Произошла ошибка сервера. Пожалуйста, попробуйте позже.']);
}