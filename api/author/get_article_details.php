<?php
session_start(); // Начинаем сессию для доступа к данным пользователя

header('Content-Type: application/json'); // Указываем, что ответ будет в формате JSON

// Подключаем файлы с настройками и функциями
require_once '../../include/db.php'; // Файл с подключением к БД (например, $pdo)
require_once '../../include/functions.php'; // Файл с полезными функциями (например, sanitizeInput)

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user']['id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['status' => 'error', 'message' => 'Необходимо авторизоваться для просмотра ваших статей.']);
    exit();
}

$article_id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);
$current_user_id = $_SESSION['user']['id']; // Получаем ID текущего пользователя


try {
    // Подготавливаем SQL-запрос для получения статей пользователя
    // Выбираем все необходимые поля
    $stmt = $pdo->prepare("
        SELECT 
            a.id AS article_id,
            a.title,
            a.abstract,
            a.submission_date,
            a.status,
            a.version,
            a.file_path,
            a.supplementary_files,
            CONCAT(au.first_name, ' ', au.last_name) AS submitted_by_name,
            js.name AS section_name,
            a.editor_decision_comments
        FROM 
            articles a
        JOIN
            users u ON a.user_id = u.id
        LEFT JOIN
            about_user au ON u.id = au.id
        JOIN
            journal_sections js ON a.section = js.id
        WHERE 
            a.user_id = ? AND a.id = ?
        ORDER BY 
            a.submission_date DESC
    ");
    $stmt->execute([$current_user_id, $article_id]);
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $formattedArticles = [];
    foreach ($articles as $article) {
        // Получаем ФИО авторов из таблицы authors для отображения
        $stmtAuthors = $pdo->prepare("SELECT name, email FROM authors WHERE article_id = ? ORDER BY id");
        $stmtAuthors->execute([$article['article_id']]);
        $authors = $stmtAuthors->fetchAll(PDO::FETCH_ASSOC);
        $article['first_author_name'] = $authors ? $authors : $article['submitted_by_name'];

        $formattedArticles[] = $article;
    }

    // Отправляем успешный ответ с данными статей
    echo json_encode(['status' => 'success', 'data' => $formattedArticles]);

} catch (PDOException $e) {
    // В случае ошибки базы данных
    http_response_code(500); // Internal Server Error
    error_log("Ошибка при получении статей пользователя: " . $e->getMessage()); // Логируем ошибку для отладки
    echo json_encode(['status' => 'error', 'message' => 'Произошла ошибка при загрузке ваших статей. Попробуйте позже.']);
}