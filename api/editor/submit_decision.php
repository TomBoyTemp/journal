    <?php
    session_start();
    require_once '../../include/db.php';
    require_once '../../include/functions.php'; // Для sendAuthorNotificationEmail

    header('Content-Type: application/json');

    // if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'editor') {
    //     http_response_code(403);
    //     echo json_encode(['status' => 'error', 'message' => 'Доступ запрещен.']);
    //     exit();
    // }

    $input = json_decode(file_get_contents('php://input'), true);

    $article_id = filter_var($input['article_id'] ?? '', FILTER_VALIDATE_INT);
    $decision = $input['decision'] ?? '';
    $comments_for_author = trim($input['comments'] ?? '');

    $allowed_decisions = ['Accept', 'Minor Revisions', 'Major Revisions', 'Reject'];

    $errors = [];
    if (!$article_id) {
        $errors[] = "Не указан ID статьи.";
    }
    if (!in_array($decision, $allowed_decisions)) {
        $errors[] = "Некорректное решение.";
    }
    if (empty($comments_for_author)) {
        $errors[] = "Комментарии для автора обязательны.";
    }

    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => implode(' ', $errors)]);
        exit();
    }

    try {
        $pdo->beginTransaction();

        // Определяем новый статус статьи
        $new_article_status = '';
        switch ($decision) {
            case 'Accept':
                $new_article_status = 'accepted';
                break;
            case 'Minor Revisions':
            case 'Major Revisions':
                $new_article_status = 'revisions_required';
                break;
            case 'Reject':
                $new_article_status = 'rejected';
                break;
        }
        if($new_article_status === 'rejected'){
            $stmt = $pdo->prepare("DELETE FROM article_reviewers WHERE article_id = ?");
            $stmt->execute([$article_id]);
        }

        // Обновляем статус статьи в таблице articles
        $stmtUpdateArticle = $pdo->prepare("
            UPDATE articles
            SET status = ?, last_decision_date = NOW(), editor_decision_comments = ?
            WHERE id = ?
        ");
        $stmtUpdateArticle->execute([$new_article_status, $comments_for_author, $article_id]);

        // Получаем email автора, чтобы отправить ему уведомление
        $stmtAuthor = $pdo->prepare("
            SELECT u.email, CONCAT(au.first_name, ' ', au.last_name) AS username
            FROM articles a
            JOIN users u ON a.user_id = u.id
            LEFT JOIN about_user au ON u.id = au.id
            WHERE a.id = ?
        ");
        $stmtAuthor->execute([$article_id]);
        $author_info = $stmtAuthor->fetch(PDO::FETCH_ASSOC);

        if (!$author_info) {
            throw new Exception("Не удалось найти информацию об авторе статьи.");
        }

        // Отправляем уведомление автору
        sendAuthorNotificationEmail($pdo, $article_id, $author_info['email'], $author_info['username'], $decision, $comments_for_author);

        $pdo->commit();
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Решение успешно отправлено автору. Статус статьи обновлен.']);

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Ошибка при отправке решения редактора: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Ошибка: ' . $e->getMessage()]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Ошибка PDO при отправке решения редактора: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Ошибка базы данных.']);
    }