<?php
session_start();
require_once '../../include/db.php';

header('Content-Type: application/json');

// if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'editor') {
//     http_response_code(403);
//     echo json_encode(['status' => 'error', 'message' => 'Доступ запрещен.']);
//     exit();
// }

$article_id = filter_var($_GET['article_id'] ?? '', FILTER_VALIDATE_INT);

if (!$article_id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Не указан ID статьи.']);
    exit();
}

try {
    // Получаем основную информацию о статье
    $stmtArticle = $pdo->prepare("
        SELECT id, title, abstract, status, file_path, current_version_path, user_id
        FROM articles WHERE id = ?
    ");
    $stmtArticle->execute([$article_id]);
    $article = $stmtArticle->fetch(PDO::FETCH_ASSOC);

    if (!$article) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Статья не найдена.']);
        exit();
    }

    // Получаем все рецензии для этой статьи
    $stmtReviews = $pdo->prepare("
        SELECT
            r.id AS review_id,
            r.comments_for_editor,
            r.comments_for_author,
            r.recommendation,
            r.review_date,
            CONCAT(au.first_name, ' ', au.last_name) AS reviewer_username,
            u.email AS reviewer_email
        FROM
            reviews r
        JOIN
            users u ON r.reviewer_id = u.id
        LEFT JOIN
	        about_user au ON u.id = au.id
        WHERE
            r.article_id = ?
        ORDER BY r.review_date DESC
    ");
    $stmtReviews->execute([$article_id]);
    $reviews = $stmtReviews->fetchAll(PDO::FETCH_ASSOC);
    $article['reviews'] = $reviews;

    // Получаем всех назначенных рецензентов и их статус приглашения
    $stmtReviewersStatus = $pdo->prepare("
        SELECT
            ar.reviewer_id,
            ar.invitation_status,
            ar.invitation_date,
            ar.review_deadline,
            ar.review_submitted_date,
            CONCAT(au.first_name, ' ', au.last_name) AS reviewer_username,
            u.email AS reviewer_email
        FROM
            article_reviewers ar
        JOIN
            users u ON ar.reviewer_id = u.id
        LEFT JOIN
            about_user au ON u.id = au.id
        WHERE
            ar.article_id = ?
        ORDER BY ar.invitation_date DESC
    ");
    $stmtReviewersStatus->execute([$article_id]);
    $article['reviewer_invitations'] = $stmtReviewersStatus->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode(['status' => 'success', 'data' => $article]);

} catch (PDOException $e) {
    error_log("Ошибка при получении рецензий статьи для редактора: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Ошибка сервера.']);
}