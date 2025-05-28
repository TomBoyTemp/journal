<?php
session_start();
require_once '../../include/db.php';
require_once '../../include/functions.php';

header('Content-Type: application/json');


// if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'editor') {
//     http_response_code(403);
//     echo json_encode(['status' => 'error', 'message' => 'Доступ запрещен.']);
//     exit();
// }


$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Проверяем успешность декодирования
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Неверный формат данных']);
    exit();
}


$article_id = filter_var($data['article_id'] ?? '', FILTER_VALIDATE_INT);
$reviewer_ids = $data['reviewer_ids'] ?? []; // Ожидаем массив ID рецензентов
$deadline = $data['deadline'] ?? '';

if (!$article_id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Не указан ID статьи.']);
    exit();
}

if (!is_array($reviewer_ids) || empty($reviewer_ids)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Не выбраны рецензенты.']);
    exit();
}

try {
    $pdo->beginTransaction();

    //Проверяем, что статья существует
    $stmtCheckArticle = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE id = ?");
    $stmtCheckArticle->execute([$article_id]);
    if ($stmtCheckArticle->fetchColumn() === 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Статья с ID' . $article_id . 'не найдена.']);
        exit();
    }

    //Проверяем, что выбранные ID являются действительными рецензентами
    $placeholders = implode(',', array_fill(0, count($reviewer_ids), '?'));
    $stmtCheckReviewers = $pdo->prepare("SELECT u.id 
                                        FROM users u 
                                        JOIN user_roles ur ON u.id = ur.users_id
                                        JOIN roles r ON ur.role_id = r.id
                                        WHERE u.id IN ($placeholders) AND r.name = 'reviewer'");
    $stmtCheckReviewers->execute($reviewer_ids);
    $valid_reviewer_ids = $stmtCheckReviewers->fetchAll(PDO::FETCH_COLUMN);

    if (count($valid_reviewer_ids) !== count($reviewer_ids)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Один или несколько выбранных ID не являются действительными рецензентами.']);
        exit();
    }

    //Назначаем рецензентов, избегая дубликатов
    $stmtAssign = $pdo->prepare("
        INSERT IGNORE INTO article_reviewers (article_id, reviewer_id, invitation_status, review_deadline)
        VALUES (?, ?, 'Pending', ?)
    ");

    $assigned_count = 0;
    foreach ($valid_reviewer_ids as $reviewer_id) {
        // Устанавливаем дедлайн если не указал редактор, например, через 3 недели
        if(empty($deadeline)){
            $deadline = date('Y-m-d', strtotime('+3 weeks'));
        }
        $stmtAssign->execute([$article_id, $reviewer_id, $deadline]);
        // RowCount = 1 если вставлена новая строка, 0 если уже существует (из-за IGNORE)
        if ($stmtAssign->rowCount() > 0) {
            $assigned_count++;
            sendReviewInvitationEmail($pdo, $article_id, $reviewer_id,$deadline);
        }
    }

    //Обновляем статус статьи, если она еще не "Under Review"
    $stmtUpdateArticleStatus = $pdo->prepare("
        UPDATE articles
        SET status = 'under_review'
        WHERE id = ? AND status NOT IN ('under_review','review_comleted','revision_requested','accepted','published','rejected')
    ");
    $stmtUpdateArticleStatus->execute([$article_id]);

    $pdo->commit();
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Рецензенты успешно назначены и приглашения отправлены (' . $assigned_count . ' новых).']);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Ошибка при назначении рецензентов: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Ошибка: ' . $e->getMessage()]);
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Ошибка PDO при назначении рецензентов: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Ошибка базы данных при назначении рецензентов.']);
}