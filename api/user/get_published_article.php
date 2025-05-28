<?php
session_start();
require_once '../../include/db.php';
require_once '../../include/functions.php';

header('Content-Type: application/json');

$article_id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);

if (!$article_id || $article_id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Некорректный ID статьи.']);
    exit();
}

try {

    // Получаем статьи
    $stmtArticles = $pdo->prepare("
        SELECT 
            a.id, a.title, a.abstract, a.keywords, a.file_path, a.supplementary_files, a.publication_date, a.version,
            GROUP_CONCAT(auth.name ORDER BY auth.id ASC SEPARATOR '; ') AS authors_list
        FROM 
            articles a
        LEFT JOIN 
            authors auth ON a.id = auth.article_id
        WHERE 
            a.id= ? AND a.status = 'published'
        GROUP BY
            a.id
        ORDER BY 
            a.order_in_issue ASC
    ");
    $stmtArticles->execute([$article_id]);
    $articles = $stmtArticles->fetch(PDO::FETCH_ASSOC);

    // foreach ($articles as &$article) {
    if (!empty($article['supplementary_files'])) {
        $article['supplementary_files'] = json_decode($article['supplementary_files'], true);
    } else {
        $article['supplementary_files'] = [];
    }
    if (!empty($article['keywords'])) {
        $article['keywords'] = explode(', ', $article['keywords']);
    } else {
        $article['keywords'] = [];
    }
    // }
    unset($article);

    echo json_encode(['status' => 'success','data' => $articles]);

} catch (PDOException $e) {
    http_response_code(500);
    error_log("Ошибка при получении статей выпуска: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Ошибка базы данных.']);
}