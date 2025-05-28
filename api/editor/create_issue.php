<?php
session_start();
require_once '../../include/db.php';
require_once '../../include/functions.php'; // Для sanitizeInput

header('Content-Type: application/json');

// if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'editor') {
    //     http_response_code(403);
    //     echo json_encode(['status' => 'error', 'message' => 'Доступ запрещен. Только для редакторов.']);
    //     exit();
    // }
    
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $volume = filter_var($input['volume'] ?? '', FILTER_VALIDATE_INT);
    $issue_number = filter_var($input['issue_number'] ?? '', FILTER_VALIDATE_INT);
    $year = filter_var($input['year'] ?? '', FILTER_VALIDATE_INT);
    $publication_date = isset($input['publication_date']) ? sanitizeInput($input['publication_date']) : null;

    $errors = [];

    if (!$volume || $volume <= 0) {
        $errors[] = 'Некорректный том.';
    }
    if (!$issue_number || $issue_number <= 0) {
        $errors[] = 'Некорректный номер выпуска.';
    }
    if (!$year || $year < 1900 || $year > (date('Y') + 5)) { // Пример валидации года
        $errors[] = 'Некорректный год.';
    }
    // Можно добавить валидацию даты публикации, если она обязательна на этом этапе

    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $errors]);
        exit();
    }

    try {
        // Проверяем, существует ли уже выпуск с такими данными
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM issues WHERE volume = ? AND issue_number = ? AND year = ?");
        $stmtCheck->execute([$volume, $issue_number, $year]);
        if ($stmtCheck->fetchColumn() > 0) {
            http_response_code(409); // Conflict
            echo json_encode(['status' => 'error', 'message' => 'Выпуск с таким томом, номером и годом уже существует.']);
            exit();
        }

        $stmt = $pdo->prepare("INSERT INTO issues (volume, issue_number, year, publication_date, status) VALUES (?, ?, ?, ?, 'draft')");
        $stmt->execute([$volume, $issue_number, $year, $publication_date]);
        $issueId = $pdo->lastInsertId();

        echo json_encode(['status' => 'success', 'message' => 'Выпуск успешно создан.', 'issue_id' => $issueId]);

    } catch (PDOException $e) {
        http_response_code(500);
        error_log("Ошибка при создании выпуска: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Ошибка базы данных при создании выпуска.']);
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Метод не разрешен.']);
}