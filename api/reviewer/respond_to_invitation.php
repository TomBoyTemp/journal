<?php
session_start();
require_once '../../include/db.php';
require_once '../../include/roles.php';


header('Content-Type: application/json');

if (!isset($_SESSION['user']) || !hasRole('reviewer')) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Доступ запрещен.']);
    exit();
}

$reviewer_id = $_SESSION['user']['id'];

$input = json_decode(file_get_contents('php://input'), true);

$article_id = filter_var($input['article_id'] ?? '', FILTER_VALIDATE_INT);
$action = $input['action'] ?? ''; // 'accept' or 'decline'

if (!$article_id || !in_array($action, ['accept', 'decline'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Некорректные данные запроса.']);
    exit();
}

try {
    $pdo->beginTransaction();

    // Проверяем статус приглашения
    $stmtCheck = $pdo->prepare("SELECT invitation_status FROM article_reviewers WHERE article_id = ? AND reviewer_id = ?");
    $stmtCheck->execute([$article_id, $reviewer_id]);
    $current_status = $stmtCheck->fetchColumn();

    if (!$current_status || $current_status !== 'Pending') {
        throw new Exception("Приглашение уже было обработано или не существует.");
    }

    $new_status = ($action === 'accept') ? 'Accepted' : 'Declined';

    $stmtUpdate = $pdo->prepare("
        UPDATE article_reviewers
        SET invitation_status = ?
        WHERE article_id = ? AND reviewer_id = ?
    ");
    $stmtUpdate->execute([$new_status, $article_id, $reviewer_id]);

    $pdo->commit();

    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Приглашение ' . ($action === 'accept' ? 'принято' : 'отклонено') . '.']);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Ошибка при обработке приглашения рецензента: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Ошибка: ' . $e->getMessage()]);
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Ошибка PDO при обработке приглашения рецензента: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Ошибка базы данных.']);
}