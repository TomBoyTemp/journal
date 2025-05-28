<?php
session_start();
require_once '../../include/db.php';
require_once '../../include/functions.php';

header('Content-Type: application/json');


const MAX_REVISION_FILE_SIZE = 20 * 1024 * 1024; // Макс. размер для ревизии, можно как для обычной

// if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'author') {
//     http_response_code(403);
//     echo json_encode(['status' => 'error', 'message' => 'Доступ запрещен. Только для авторов.']);
//     exit();
// }

$author_id = $_SESSION['user']['id'];
$article_id = $_POST['article_id'] ?? null;
$comments = sanitizeInput($_POST['comments'] ?? '');

$errors = [];
if (!$article_id) {
    $errors[] = "Не указан ID статьи.";
}

// Валидация файла
$file = $_FILES['revision_file'] ?? null;
if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
    switch ($file['error'] ?? UPLOAD_ERR_NO_FILE) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            $errors[] = "Размер файла превышает допустимый лимит.";
            break;
        case UPLOAD_ERR_NO_FILE:
            $errors[] = "Файл рукописи не был выбран.";
            break;
        case UPLOAD_ERR_PARTIAL:
            $errors[] = "Файл был загружен не полностью.";
            break;
        default:
            $errors[] = "Ошибка загрузки файла. Код: " . ($file['error'] ?? 'неизвестно');
            break;
    }
} else {
    // Проверки аналогичные тем, что были при первой загрузке (расширение, MIME-тип, размер)
    $fileTmpPath = $file['tmp_name'];
    $fileName = basename($file['name']);
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $fileSize = $file['size'];

    $allowedExts = ['doc', 'docx', 'pdf', 'tex', 'rtf']; // Те же, что и для основной рукописи
    $allowedMimeTypes = [
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/pdf',
        'application/x-tex',
        'application/rtf'
    ];

    $finfo = null;
    $mimeType = false;
    if (extension_loaded('fileinfo')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mimeType = finfo_file($finfo, $fileTmpPath);
            finfo_close($finfo); // Закрываем сразу после использования
        }
    }

    if ($fileSize > MAX_REVISION_FILE_SIZE) {
        $errors[] = "Размер пересмотренного файла слишком большой. Максимальный размер: " . (MAX_REVISION_FILE_SIZE / (1024 * 1024)) . " МБ.";
    }
    if (!in_array($fileExt, $allowedExts) || ($mimeType !== false && !in_array($mimeType, $allowedMimeTypes))) {
        $errors[] = "Недопустимый формат файла рукописи. Разрешены: " . implode(', ', $allowedExts) . ".";
    }
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => implode(' ', $errors)]);
    exit();
}

try {
    $pdo->beginTransaction();

    // 1. Проверяем, что статья принадлежит этому автору и находится в статусе, требующем доработки
    $stmtCheckArticle = $pdo->prepare("SELECT status FROM articles WHERE id = ? AND user_id = ? AND status = 'revisions_required'");
    $stmtCheckArticle->execute([$article_id, $author_id]);
    $current_status = $stmtCheckArticle->fetchColumn();

    if (!$current_status) {
        throw new Exception("Вы не можете загрузить новую версию для этой статьи. Возможно, она не требует доработок или не принадлежит вам.");
    }

    // 2. Определяем новый номер версии
    $stmtMaxVersion = $pdo->prepare("SELECT MAX(version_number) FROM article_versions WHERE article_id = ?");
    $stmtMaxVersion->execute([$article_id]);
    $max_version = $stmtMaxVersion->fetchColumn();
    $new_version_number = ($max_version === null) ? 1 : $max_version + 1;

    // 3. Сохраняем файл новой версии
    $uploadDir = 'D:/uploads/manuscript/'; // Используйте тот же путь, что и для основной
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $newFileName = uniqid('revision_') . '_' . $article_id . '_v' . $new_version_number . '.' . $fileExt;
    $revision_file_path = $uploadDir . $newFileName;

    if (!move_uploaded_file($fileTmpPath, $revision_file_path)) {
        throw new Exception("Ошибка при загрузке файла пересмотренной версии на сервер.");
    }

    // 4. Добавляем запись в таблицу article_versions
    $stmtInsertVersion = $pdo->prepare("
        INSERT INTO article_versions (article_id, file_path, version_number, comments)
        VALUES (?, ?, ?, ?)
    ");
    $stmtInsertVersion->execute([
        $article_id,
        $revision_file_path,
        $new_version_number,
        $comments
    ]);

    // 5. Обновляем articles.current_version_path и status
    $stmtUpdateArticle = $pdo->prepare("
        UPDATE articles
        SET current_version_path = ?, status = 're-submitted_for_review'
        WHERE id = ?
    ");
    $stmtUpdateArticle->execute([$revision_file_path, $article_id]);

    $pdo->commit();
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Пересмотренная версия успешно загружена.']);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Ошибка при загрузке пересмотренной версии: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Ошибка: ' . $e->getMessage()]);
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Ошибка PDO при загрузке пересмотренной версии: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Ошибка базы данных.']);
}