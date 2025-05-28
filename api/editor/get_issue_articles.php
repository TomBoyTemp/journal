
<?php
session_start();
require_once '../../include/db.php';
require_once '../../include/functions.php';

header('Content-Type: application/json');

$issue_id = filter_var($_GET['issue_id'] ?? '', FILTER_VALIDATE_INT);

// if (!$issue_id || $issue_id <= 0) {
//     http_response_code(400);
//     echo json_encode(['status' => 'error', 'message' => 'Некорректный ID выпуска.']);
//     exit();
// }

try {
    // Получаем информацию о выпуске
    $stmtIssue = $pdo->prepare("SELECT id, volume, issue_number, year, publication_date FROM issues WHERE id = ? AND status = 'published'");
    $stmtIssue->execute([$issue_id]);
    $issue_info = $stmtIssue->fetch(PDO::FETCH_ASSOC);

    if (!$issue_info) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Опубликованный выпуск не найден.']);
        exit();
    }

    // Получаем статьи для этого выпуска
    $stmtArticles = $pdo->prepare("
        SELECT 
            a.id, a.title, a.abstract, a.keywords, a.file_path, a.supplementary_files, a.publication_date, a.version,
            (SELECT GROUP_CONCAT(name SEPARATOR '; ') 
    		 FROM authors 
     			WHERE article_id = a.id) AS authors_list
        FROM 
            articles a
        JOIN
            authors aut ON a.id = aut.article_id
        WHERE 
            a.issue_id = ? AND a.status = 'published'
        GROUP BY
            a.id
        ORDER BY 
            a.order_in_issue ASC
    ");
    $stmtArticles->execute([$issue_id]);
    $articles = $stmtArticles->fetchAll(PDO::FETCH_ASSOC);

    foreach ($articles as &$article) {
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
    }
    unset($article);

    echo json_encode(['status' => 'success', 'issue' => $issue_info, 'articles' => $articles]);

} catch (PDOException $e) {
    http_response_code(500);
    error_log("Ошибка при получении статей выпуска: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Ошибка базы данных.']);
}