<?php
session_start(); // Начинаем сессию для доступа к данным пользователя

header('Content-Type: application/json'); // Указываем, что ответ будет в формате JSON

// Подключаем файлы с настройками и функциями
require_once '../../include/db.php'; // Файл с подключением к БД (например, $pdo)
require_once '../../include/functions.php'; // Файл с полезными функциями (например, sanitizeInput)
require_once '../../include/roles.php';
// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user']['id']) && hasRole('reviewer')) {
    http_response_code(401); // Unauthorized
    echo json_encode(['status' => 'error', 'message' => 'Необходимо авторизоваться для просмотра ваших статей.']);
    exit();
}

$current_user_id = $_SESSION['user']['id']; // Получаем ID текущего пользователя


try {
    $whereClauses = [];
    $queryParams = [];
    $whereClauses[] = "a.user_id = :user_id";
    $queryParams[':user_id'] = $current_user_id;
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
    // Подготавливаем SQL-запрос для получения статей пользователя
    // Выбираем все необходимые поля
    $stmt = $pdo->prepare("
        SELECT 
            a.id AS article_id,
            a.title,
            a.submission_date,
            a.status,
            a.version,
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
        JOIN
            journal_sections js ON a.section = js.id
        LEFT JOIN
            authors aut ON a.id = aut.article_id 
        " . $whereSql . "
        GROUP BY a.id
        ORDER BY 
            a.submission_date DESC
    ");
    $stmt->execute($queryParams);
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Отправляем успешный ответ с данными статей
    echo json_encode(['status' => 'success', 'data' => $articles]);

} catch (PDOException $e) {
    // В случае ошибки базы данных
    http_response_code(500); // Internal Server Error
    error_log("Ошибка при получении статей пользователя: " . $e->getMessage()); // Логируем ошибку для отладки
    echo json_encode(['status' => 'error', 'message' => 'Произошла ошибка при загрузке ваших статей. Попробуйте позже.']);
}