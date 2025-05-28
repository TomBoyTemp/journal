<?php
// api/user/profile.php
session_start();
header('Content-Type: application/json');

require_once '../../include/db.php'; 
require_once '../../include/functions.php'; 

// Проверка авторизации
if (!isset($_SESSION['user']['id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['status' => 'error', 'message' => 'Неавторизованный доступ.']);
    exit();
}

$userId = $_SESSION['user']['id'];

try {
    $stmt = $pdo->prepare("
        SELECT u.email, au.first_name, au.last_name, au.organization, au.country, uc.want_reviewer , uc.reviewer_interests
        FROM users u
        JOIN about_user au on u.id = au.id
        JOIN user_consents uc on u.id = uc.user_id
        WHERE id = ?
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        http_response_code(200);
        echo json_encode(['status' => 'success', 'user' => $user]);
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Пользователь не найден.']);
    }

} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    error_log("Ошибка при получении профиля пользователя: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Произошла ошибка сервера.']);
}