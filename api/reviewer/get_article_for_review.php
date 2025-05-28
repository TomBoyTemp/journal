<?php
session_start();
require_once '../../include/db.php';

    $article_id = filter_var($_GET['article_id'] ?? '', FILTER_VALIDATE_INT);
    $current_reviewer_id = $_SESSION['user']['id'] ?? null;

    if (!$article_id || !$current_reviewer_id) {
        die("Неверный ID статьи или неавторизованный доступ.");
    }

try {
    // Проверяем, имеет ли рецензент доступ к этой статье
    // $stmt = $pdo->prepare("SELECT invitation_status FROM article_reviewers 
    //                     WHERE article_id = ? AND reviewer_id = ? AND invitation_status = 'Accepted'");
    // $stmt->execute([$article_id, $current_reviewer_id]);
    // if (!$stmt->fetch()) {
    //     die("У вас нет доступа к рецензированию этой статьи.");
    // }

    // Получаем данные статьи
    $stmt = $pdo->prepare("SELECT 
        a.id, 
        a.title, 
        a.file_path, 
        a.supplementary_files,
        (SELECT COUNT(*) FROM reviews WHERE article_id = a.id AND reviewer_id = ?) as has_submitted_review
        FROM articles a WHERE a.id = ?");
    $stmt->execute([$current_reviewer_id, $article_id]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$article) {
        die("Статья не найдена.");
    }

    http_response_code(200);
    echo json_encode(['status' => 'success', 'data' => $article]);


} catch (PDOException $e) {
    error_log("Ошибка при получении рукописи для рецензента: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Ошибка сервера при получении данных.']);
}
?>