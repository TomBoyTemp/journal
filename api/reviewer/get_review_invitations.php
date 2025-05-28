
<?php
//Для формирования списка приглашений для рецензирования

session_start();
require_once '../../include/db.php';
require_once '../../include/functions.php'; // Для sanitizeInput или других утилит
require_once '../../include/roles.php';

header('Content-Type: application/json');

//Проверка авторизации и роли рецензента
if (!isset($_SESSION['user']) || !hasRole('reviewer')) {
    http_response_code(403); // Forbidden
    echo json_encode(['status' => 'error', 'message' => 'Доступ запрещен. Только для рецензентов.']);
    exit();
}

$reviewer_id = $_SESSION['user']['id'];

try {
    //Получение списка назначенных рукописей id-статьи, текущий статус приглашения рецензента, дата отправки приглашения рецензенту, дедлайн,
    //дата подачи отзыва, если отзыв уже предоставлен, название статьи, нотация, общий статус статьи
    $stmt = $pdo->prepare("
        SELECT
            ar.article_id,
            ar.invitation_status,
            ar.invitation_date,
            ar.review_deadline,
            ar.review_submitted_date,
            a.title AS article_title,
            a.abstract AS article_abstract,
            a.status AS article_overall_status,
            (SELECT name FROM authors WHERE article_id = a.id ORDER BY id ASC LIMIT 1) AS first_author_name,
            (SELECT COUNT(*) FROM reviews r WHERE r.article_id = ar.article_id AND r.reviewer_id = ar.reviewer_id) AS has_submitted_review
        FROM
            article_reviewers ar
        JOIN
            articles a ON ar.article_id = a.id
        WHERE
            ar.reviewer_id = ?
        ORDER BY
            ar.invitation_date DESC
    ");
    $stmt->execute([$reviewer_id]);
    $assigned_manuscripts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode(['status' => 'success', 'data' => $assigned_manuscripts]);

} catch (PDOException $e) {
    error_log("Ошибка при получении списка рукописей для рецензента: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Ошибка сервера при получении данных.']);
}