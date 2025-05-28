<?php
session_start();
require_once '../../include/db.php'; 

header('Content-Type: application/json');

//Проверка авторизации и роли редактора
// if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'editor') {
//     http_response_code(403); // Forbidden
//     echo json_encode(['status' => 'error', 'message' => 'Доступ запрещен. Только для редакторов.']);
//     exit();
// }

$input = json_decode(file_get_contents('php://input'), true);

$reviewer_id = filter_var($input['reviewer_id'] ?? '', FILTER_VALIDATE_INT);
$article_id = filter_var($input['article_id'] ?? '', FILTER_VALIDATE_INT);

// 2. Валидация входных данных
if (!$reviewer_id || !$article_id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Некорректные данные запроса (ID рецензента или статьи отсутствуют).']);
    exit();
}

try {
    $pdo->beginTransaction();

    // Проверяем, что рецензент действительно назначен этой статье
    $stmtCheckAssignment = $pdo->prepare("SELECT COUNT(*) FROM article_reviewers WHERE article_id = ? AND reviewer_id = ?");
    $stmtCheckAssignment->execute([$article_id, $reviewer_id]);
    if ($stmtCheckAssignment->fetchColumn() === 0) {
        throw new Exception("Этот рецензент не назначен данной статье.");
    }

    //Удаляем назначение рецензента
    $stmtDeleteAssignment = $pdo->prepare("DELETE FROM article_reviewers WHERE article_id = ? AND reviewer_id = ?");
    $stmtDeleteAssignment->execute([$article_id, $reviewer_id]);

    // Также удаляем рецензию, если она была подана, т.к. рецензент удален.
    $stmtDeleteReview = $pdo->prepare("DELETE FROM reviews WHERE article_id = ? AND reviewer_id = ?");
    $stmtDeleteReview->execute([$article_id, $reviewer_id]);

    //Проверяем, остались ли ещё активные рецензенты для данной статьи
    // Активные - это те, кто 'Pending', 'Accepted',
    $stmtActiveReviewers = $pdo->prepare("
        SELECT COUNT(*)
        FROM article_reviewers
        WHERE article_id = ?
        AND invitation_status IN ('Pending', 'Accepted') 
    ");
    $stmtActiveReviewers->execute([$article_id]);
    $remaining_reviewers_count = $stmtActiveReviewers->fetchColumn();

    // 5. Обновляем статус статьи, если необходимо
    $stmtGetArticleStatus = $pdo->prepare("SELECT status FROM articles WHERE id = ?");
    $stmtGetArticleStatus->execute([$article_id]);
    $current_article_status = $stmtGetArticleStatus->fetchColumn();

    // Если статья была на рецензии ('Under Review') и больше нет активных рецензентов
    if ($current_article_status === 'under_review' && $remaining_reviewers_count === 0) {
        $stmtUpdateArticleStatus = $pdo->prepare("
            UPDATE articles
            SET status = 'submitted'
            WHERE id = ?
        ");
        $stmtUpdateArticleStatus->execute([$article_id]);
    }

    $pdo->commit();
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Рецензент успешно удален.']);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Ошибка при удалении рецензента: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Ошибка: ' . $e->getMessage()]);
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Ошибка PDO при удалении рецензента: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Ошибка базы данных.']);
}