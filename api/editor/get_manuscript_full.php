<?php
session_start();
require_once '../../include/db.php';
require_once '../../include/functions.php';

header('Content-Type: application/json');

// if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'editor') {
//     http_response_code(403);
//     echo json_encode(['status' => 'error', 'message' => 'Доступ запрещен.']);
//     exit();
// }

$article_id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);

if (!$article_id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Не указан ID статьи.']);
    exit();
}

try {
    // Получаем основную информацию о статье
    $stmt = $pdo->prepare("
        SELECT
            a.id, a.user_id, a.title, a.abstract, a.status, a.file_path, a.submission_date, a.status, a.keywords,
            CONCAT(au.first_name, ' ', au.last_name) AS submitted_by_name, u.email AS submitted_by_email
        FROM
            articles a
        JOIN
            users u ON a.user_id = u.id
        LEFT JOIN
            about_user au ON u.id = au.id
        WHERE
            a.id = ?
    ");
    $stmt->execute([$article_id]);
    $article['manuscript'] = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$article) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Статья не найдена.']);
        exit();
    }

    $stmt = $pdo->prepare("
        SELECT name AS first_author_name
        FROM authors
        WHERE article_id = ?
        ORDER BY id ASC
        LIMIT 1;
    ");
    $stmt->execute([$article_id]);
    $article['manuscript']['authors'] = $stmt->fetch(PDO::FETCH_ASSOC);

    // Получаем уже назначенных рецензентов для этой статьи
    $stmtAssignedReviewers = $pdo->prepare("
        SELECT
            ar.reviewer_id,
            ar.invitation_status,
            ar.invitation_date,
            ar.review_deadline,
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
    ");
    $stmtAssignedReviewers->execute([$article_id]);
    $assigned_reviewers = $stmtAssignedReviewers->fetchAll(PDO::FETCH_ASSOC);
    $article['assigned_reviewers'] = $assigned_reviewers;

    // Получаем список всех потенциальных рецензентов (role='reviewer')
    $stmtReviewers = $pdo->prepare("
        SELECT
            u.id, 
            u.email, 
            CONCAT(au.first_name, ' ', au.last_name) AS reviewer_username,
            uc.reviewer_interests
        FROM
            users u
        JOIN 
            user_roles ur ON u.id = ur.users_id
        JOIN 
            roles r ON ur.role_id = r.id
        LEFT JOIN 
            about_user au ON u.id = au.id
        LEFT JOIN 
            user_consents uc ON u.id = uc.user_id
        WHERE 
            r.name = 'reviewer' AND u.id != ? and NOT EXISTS (
        SELECT 1 
        FROM article_reviewers ar 
        WHERE ar.reviewer_id = u.id 
        AND ar.article_id = ?
    	);
    ");
    $stmtReviewers->execute([$article['manuscript']['user_id'], $article_id]);
    $all_reviewers = $stmtReviewers->fetchAll(PDO::FETCH_ASSOC);
    $article['potential_reviewers'] = $all_reviewers;

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

    //  // Получаем всех назначенных рецензентов и их статус приглашения
    // $stmtReviewersStatus = $pdo->prepare("
    //     SELECT
    //         ar.reviewer_id,
    //         ar.invitation_status,
    //         ar.invitation_date,
    //         ar.review_deadline,
    //         ar.review_submitted_date,
    //         CONCAT(au.first_name, ' ', au.last_name) AS reviewer_username,
    //         u.email AS reviewer_email
    //     FROM
    //         article_reviewers ar
    //     JOIN
    //         users u ON ar.reviewer_id = u.id
    //     LEFT JOIN
    //         about_user au ON u.id = au.id
    //     WHERE
    //         ar.article_id = ?
    //     ORDER BY ar.invitation_date DESC
    // ");
    // $stmtReviewersStatus->execute([$article_id]);
    // $article['reviewer_invitations'] = $stmtReviewersStatus->fetchAll(PDO::FETCH_ASSOC);


    http_response_code(200);
    echo json_encode(['status' => 'success', 'data' => $article]);

} catch (PDOException $e) {
    error_log("Ошибка при получении деталей рукописи: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Ошибка сервера.']);
}