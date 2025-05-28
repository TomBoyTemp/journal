
<?php
// api/auth/reset_password.php
session_start();
header('Content-Type: application/json');

require_once '../../include/db.php'; // Подключение к БД
require_once '../../include/functions.php'; // Для sanitizeInput()

$input = json_decode(file_get_contents('php://input'), true);
$token = sanitizeInput($input['token'] ?? '');
$newPassword = $input['new_password'] ?? '';

if (empty($token) || empty($newPassword)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Токен и новый пароль не могут быть пустыми.']);
    exit();
}

if (strlen($newPassword) < 6) { // Минимальная длина пароля
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Новый пароль должен содержать не менее 6 символов.']);
    exit();
}

try {
    $pdo->beginTransaction();

    //Найти токен сброса пароля
    $stmt = $pdo->prepare("SELECT user_id, expires_at FROM password_reset WHERE token = ?");
    $stmt->execute([$token]);
    $resetData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$resetData) {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Недействительный или уже использованный токен сброса пароля.']);
        exit();
    }

    // 2. Проверить срок действия токена
    $currentTime = new DateTime();
    $expiresAt = new DateTime($resetData['expires_at']);

    if ($currentTime > $expiresAt) {
        // Токен истек - удалить его
        $stmtDelete = $pdo->prepare("DELETE FROM password_reset WHERE token = ?");
        $stmtDelete->execute([$token]);
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Срок действия токена истек. Пожалуйста, запросите сброс пароля повторно.']);
        exit();
    }

    $userId = $resetData['user_id'];

    //Хешировать новый пароль
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    //Обновить пароль пользователя
    $stmtUpdateUser = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $stmtUpdateUser->execute([$hashedPassword, $userId]);

    //Удалить использованный токен сброса пароля
    $stmtDelete = $pdo->prepare("DELETE FROM password_reset WHERE token = ?");
    $stmtDelete->execute([$token]);

    $pdo->commit();

    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Ваш пароль успешно обновлен. Теперь вы можете войти, используя новый пароль.']);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Ошибка при сбросе пароля: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Произошла ошибка сервера. Пожалуйста, попробуйте позже.']);
}