<?php
session_start();
require_once '../../include/db.php';
require_once '../../include/roles.php';

header('Content-Type: application/json');


if (!isset($_SESSION['user']) || !hasRole("reviewer")){
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Доступ запрещен.']);
    exit();
}

$reviewer_id = $_SESSION['user']['id'];
$input = json_decode(file_get_contents('php://input'), true);

$article_id = filter_var($input['article_id'] ?? '', FILTER_VALIDATE_INT);
$editor_comments = trim($input['editor_comments'] ?? '');
$author_comments = trim($input['author_comments'] ?? '');
$recommendation = $input['recommendation'] ?? '';

$allowed_recommendations = ['accept', 'minor_revisions', 'major_revisions', 'reject'];

$errors = [];
if (!$article_id) {
    $errors[] = "Не указан ID статьи.";
}
if (empty($editor_comments)) {
    $errors[] = "Комментарии для редактора обязательны.";
}
if (!in_array($recommendation, $allowed_recommendations)) {
    $errors[] = "Некорректная рекомендация.";
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => implode(' ', $errors)]);
    exit();
}

try {
    $pdo->beginTransaction();

    //Проверяем, что статья назначена этому рецензенту и он ее принял
    $stmtCheckAssignment = $pdo->prepare("SELECT invitation_status FROM article_reviewers WHERE article_id = ? AND reviewer_id = ? AND invitation_status = 'Accepted'");
    $stmtCheckAssignment->execute([$article_id, $reviewer_id]);
    if ($stmtCheckAssignment->fetchColumn() !== 'Accepted') {
        throw new Exception("Вы не можете отправить рецензию на эту статью. Либо она не назначена вам, либо вы не приняли приглашение.");
    }

    //Проверяем, не была ли рецензия уже отправлена этим рецензентом на эту статью
    $stmtCheckReviewExists = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE article_id = ? AND reviewer_id = ?");
    $stmtCheckReviewExists->execute([$article_id, $reviewer_id]);
    if ($stmtCheckReviewExists->fetchColumn() > 0) {
        throw new Exception("Вы уже отправили рецензию на эту статью.");
    }

    // 3. Вставляем рецензию
    $stmtInsertReview = $pdo->prepare("
        INSERT INTO reviews (article_id, reviewer_id, comments_for_editor, comments_for_author, recommendation)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmtInsertReview->execute([
        $article_id,
        $reviewer_id,
        $editor_comments,
        $author_comments,
        $recommendation
    ]);

    //Обновляем статус приглашения рецензента в article_reviewers на 'Completed'
    $stmtUpdateReviewerStatus = $pdo->prepare("
        UPDATE article_reviewers
        SET invitation_status = 'Completed', review_submitted_date = NOW()
        WHERE article_id = ? AND reviewer_id = ?
    ");
    $stmtUpdateReviewerStatus->execute([$article_id, $reviewer_id]);


    $pdo->commit();
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Ваша рецензия успешно отправлена.']);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Ошибка при сохранении рецензии: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Ошибка: ' . $e->getMessage()]);
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Ошибка PDO при сохранении рецензии: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Ошибка базы данных.']);
}