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
            *
        FROM 
            issues 
        WHERE 
            status = 'draft'
    ");
    $stmt->execute();
    $accepted_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $accepted_articles]);

} catch (PDOException $e) {
    http_response_code(500);
    error_log("Ошибка при получении принятых статей: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Ошибка базы данных.']);
}