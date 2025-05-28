<?php
session_start();
require_once '../../include/db.php';
require_once '../../include/functions.php';

header('Content-Type: application/json');

// if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'editor') {
//     http_response_code(403);
//     echo json_encode(['status' => 'error', 'message' => 'Доступ запрещен. Только для редакторов.']);
//     exit();
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $issue_id = filter_var($input['issue_id'] ?? '', FILTER_VALIDATE_INT);
    $publication_date = sanitizeInput($input['publication_date'] ?? date('Y-m-d')); // Если не передана, берем текущую

    if (!$issue_id || $issue_id <= 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Некорректный ID выпуска.']);
        exit();
    }

    try {
        $pdo->beginTransaction();

        // 1. Проверяем, существует ли выпуск и не опубликован ли он уже
        $stmtCheckIssue = $pdo->prepare("SELECT status FROM issues WHERE id = ?");
        $stmtCheckIssue->execute([$issue_id]);
        $current_issue_status = $stmtCheckIssue->fetchColumn();

        if (!$current_issue_status) {
            $pdo->rollBack();
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Выпуск не найден.']);
            exit();
        }
        if ($current_issue_status === 'published') {
            $pdo->rollBack();
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => 'Выпуск уже опубликован.']);
            exit();
        }

        // 2. Обновляем статус выпуска на 'published' и устанавливаем дату публикации
        $stmtUpdateIssue = $pdo->prepare("UPDATE issues SET status = 'published', publication_date = ? WHERE id = ?");
        $stmtUpdateIssue->execute([$publication_date, $issue_id]);

        // 3. Обновляем статус всех статей в этом выпуске на 'published' и проставляем publication_date
        $stmtUpdateArticles = $pdo->prepare("UPDATE articles SET status = 'published', publication_date = ? WHERE issue_id = ?");
        $stmtUpdateArticles->execute([$publication_date, $issue_id]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Выпуск успешно опубликован.']);

    } catch (PDOException $e) {
        $pdo->rollBack();
        http_response_code(500);
        error_log("Ошибка при публикации выпуска: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Ошибка базы данных при публикации выпуска.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Метод не разрешен.']);
}