<?php
session_start();
require_once '../../include/db.php';

header('Content-Type: application/json');

// if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'editor') {
//     http_response_code(403);
//     echo json_encode(['status' => 'error', 'message' => 'Доступ запрещен. Только для редакторов.']);
//     exit();
// }

try {
    $stmt = $pdo->prepare("
        SELECT 
            a.id, a.title, a.abstract, a.keywords, a.submission_date, a.user_id, a.issue_id, JSON_ARRAYAGG(
                JSON_OBJECT(
                'id', aut.id,
                'name', aut.name,
                'email', aut.email,
                'affiliation', aut.affiliation
                )
            ) AS authors
        FROM 
            articles a
        LEFT JOIN
            authors aut ON a.id = aut.article_id 
        WHERE 
            a.status = 'accepted' 
        GROUP BY
            a.id
        ORDER BY 
            submission_date DESC
    ");
    // AND issue_id IS NULL 
    $stmt->execute();
    $accepted_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $accepted_articles]);

} catch (PDOException $e) {
    http_response_code(500);
    error_log("Ошибка при получении принятых статей: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Ошибка базы данных.']);
}