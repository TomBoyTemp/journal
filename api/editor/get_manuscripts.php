<?php
//Для формирования предпросмотра статей у редактора
session_start();
require_once '../../include/db.php';
require_once '../../include/functions.php';

header('Content-Type: application/json');

// 1. Проверка авторизации и роли редактора
// if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'editor') {
//     http_response_code(403); // Forbidden
//     echo json_encode(['status' => 'error', 'message' => 'Доступ запрещен. Только для редакторов.']);
//     exit();
// }


try {
    $whereClauses = [];
    $queryParams = [];

    // Получение и обработка параметров из $_GET
    $title = $_GET['title'] ?? '';
    $status = $_GET['status'] ?? '';
    $author = $_GET['author'] ?? '';

     // Добавление условий WHERE на основе полученных параметров

    // Поиск по ключевым словам (keywords): title, abstract, keywords
    if (!empty($title)) {
        $whereClauses[] = "(a.title LIKE :title)";
        $queryParams[':title'] = '%' . $title . '%';
    }

    if (!empty($status)) {
        $whereClauses[] = "a.status = :status";
        $queryParams[':status'] = $status;
    }

    if (!empty($author)) {  
        $whereClauses[] = "EXISTS(SELECT 1 FROM AUTHORS aut WHERE aut.article_id = a.id AND aut.name like :author_name)";
        $queryParams[':author_name'] = '%' . $author . '%';
    }

    $whereSql = '';
    if (!empty($whereClauses)) {
        $whereSql = " WHERE " . implode(" AND ", $whereClauses);
    }   

    //Получение списка рукописей
    // Мы хотим получить: ID статьи, название, ФИО авторов, дату подачи, текущий статус.
    $stmt = $pdo->prepare("
        SELECT
            a.id AS article_id,
            a.title,
            a.keywords,
            a.submission_date,
            a.status,
            JSON_ARRAYAGG(
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
        " . $whereSql . "
        GROUP BY a.id
        ORDER BY
            a.submission_date DESC
    ");
    $stmt->execute($queryParams);
    $manuscripts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // // 3. Форматирование данных (опционально, но полезно)
    // $formattedManuscripts = [];
    // foreach ($manuscripts as $manuscript) {
    //     // Получаем ФИО первого автора из таблицы authors для отображения
    //     // Это может быть более репрезентативно, чем просто user.username
    //     $stmtAuthors = $pdo->prepare("SELECT name, email FROM authors WHERE article_id = ? ORDER BY id");
    //     $stmtAuthors->execute([$manuscript['article_id']]);
    //     $firstAuthor = $stmtAuthors->fetchAll(PDO::FETCH_ASSOC);
    //     $manuscript['first_author_name'] = $firstAuthor ? $firstAuthor : $manuscript['submitted_by_username'];
    //     $formattedManuscripts[] = $manuscript;
    // }

    http_response_code(200);
    echo json_encode(['status' => 'success', 'data' => $manuscripts]);

} catch (PDOException $e) {
    error_log("Ошибка при получении списка рукописей для редактора: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Ошибка сервера при получении данных.']);
}