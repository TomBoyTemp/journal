<?php
session_start();
require_once '../../include/db.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->prepare("
        SELECT id, volume, issue_number, year, publication_date
        FROM issues
        WHERE status = 'published'
        ORDER BY year DESC, volume DESC, issue_number DESC
    ");
    $stmt->execute();
    $issues = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $issues]);

} catch (PDOException $e) {
    http_response_code(500);
    error_log("Ошибка при получении опубликованных выпусков: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Ошибка базы данных.']);
}