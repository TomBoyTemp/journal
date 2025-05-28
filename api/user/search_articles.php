<?php
session_start();
require_once '../../include/db.php';
require_once '../../include/functions.php';

header('Content-Type: application/json');

$search_query = sanitizeInput($_GET['query'] ?? '');

// if (empty($search_query)) {
//     http_response_code(400);
//     echo json_encode(['status' => 'error', 'message' => 'Поисковый запрос не может быть пустым.']);
//     exit();
// }

try {
    // Ищем по названию, аннотации, ключевым словам статьи, и по имени автора
    $stmt = $pdo->prepare("
        SELECT DISTINCT
            a.id, a.title, a.abstract, a.keywords, a.publication_date, a.file_path,
            GROUP_CONCAT(auth.name ORDER BY auth.id ASC SEPARATOR '; ') AS authors_list,
            i.volume, i.issue_number, i.year
        FROM 
            articles a
        LEFT JOIN 
            authors auth ON a.id = auth.article_id
        LEFT JOIN
            issues i ON a.issue_id = i.id
        WHERE 
            a.status = 'published' AND i.status = 'published' AND (
                a.title LIKE ? OR 
                a.keywords LIKE ? OR 
                auth.name LIKE ?
            )
        GROUP BY
            a.id
        ORDER BY 
            a.publication_date DESC
    ");

    $search_param = '%' . $search_query . '%';
    $stmt->execute([$search_param, $search_param, $search_param]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as &$article) {
        if (!empty($article['keywords'])) {
            $article['keywords'] = explode(', ', $article['keywords']);
        } else {
            $article['keywords'] = [];
        }
    }
    unset($article);

    echo json_encode(['status' => 'success', 'data' => $results]);

} catch (PDOException $e) {
    http_response_code(500);
    error_log("Ошибка при поиске статей: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Ошибка базы данных при поиске.']);
}