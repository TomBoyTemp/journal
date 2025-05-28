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
    // articles_data будет массив объектов: [{id: 1, order: 1}, {id: 2, order: 2}, ...]
    $articles_data = $input['articles'] ?? [];

    $errors = [];

    if (!$issue_id || $issue_id <= 0) {
        $errors[] = 'Некорректный ID выпуска.';
    }

    // Проверяем существование выпуска
    $stmtCheckIssue = $pdo->prepare("SELECT status FROM issues WHERE id = ?");
    $stmtCheckIssue->execute([$issue_id]);
    $issue_status = $stmtCheckIssue->fetchColumn();
    if (!$issue_status) {
        $errors[] = 'Выпуск не найден.';
    }
    if ($issue_status === 'published') {
        $errors[] = 'Нельзя изменять статьи в уже опубликованном выпуске.';
    }

    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $errors]);
        exit();
    }

    try {
        $pdo->beginTransaction();

        // 1. Сначала "отвязываем" все статьи от этого выпуска (обнуляем issue_id и order_in_issue),
        // чтобы избежать проблем с уникальностью порядка и легко удалить статьи.
        $stmtClearArticles = $pdo->prepare("UPDATE articles SET issue_id = NULL, order_in_issue = NULL WHERE issue_id = ?");
        $stmtClearArticles->execute([$issue_id]);

        // 2. Добавляем/обновляем статьи, переданные в запросе
        if (!empty($articles_data)) {
            $stmtUpdateArticle = $pdo->prepare("UPDATE articles SET issue_id = ?, order_in_issue = ? WHERE id = ? AND status = 'accepted'");
            foreach ($articles_data as $article) {
                $article_id = filter_var($article['id'] ?? '', FILTER_VALIDATE_INT);
                $order = filter_var($article['order'] ?? '', FILTER_VALIDATE_INT);

                if (!$article_id || $article_id <= 0 || !$order || $order <= 0) {
                    $pdo->rollBack();
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'Некорректные данные статьи для добавления/обновления.']);
                    exit();
                }
                $stmtUpdateArticle->execute([$issue_id, $order, $article_id]);
            }
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Статьи в выпуске успешно обновлены.']);

    } catch (PDOException $e) {
        $pdo->rollBack();
        http_response_code(500);
        error_log("Ошибка при управлении статьями выпуска: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Ошибка базы данных при управлении статьями выпуска.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Метод не разрешен.']);
}