<?php
session_start();
require_once '../../include/db.php';
require_once '../../include/roles.php';

// Проверяем авторизацию
// if (!isset($_SESSION['user'])) {
//     http_response_code(403);
//     exit(); // Или перенаправление на страницу входа
// }
$is_zip = false;
$article_id = filter_var($_GET['article_id'] ?? '', FILTER_VALIDATE_INT);
$file_type = $_GET['type'] ?? 'main'; // 'main' или 'supplementary'
$file_index = filter_var($_GET['index'] ?? '', FILTER_VALIDATE_INT); // Для дополнительных файлов

if (!$article_id) {
    die("Не указан ID статьи.");
}

try {
    $stmt = $pdo->prepare("SELECT a.file_path, a.supplementary_files, a.user_id, a.status FROM articles a WHERE id = ?");
    $stmt->execute([$article_id]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$article) {
        die("Статья не найдена.");
    }
    $is_published = ($article['status'] === 'published');

    $file_path = null;
    $original_filename = "downloaded_file";

    if ($file_type === 'main') {
        $file_path = $article['file_path'];
        $original_filename .= '.' . pathinfo($file_path, PATHINFO_EXTENSION); // Добавляем расширение
    } elseif ($file_type === 'supplementary') {
        $supplementary_files = json_decode($article['supplementary_files'], true);

        if (empty($supplementary_files)) {
            die("Нет дополнительных файлов для скачивания.");
        }

        $zip = new ZipArchive();
        $zip_filename = tempnam(sys_get_temp_dir(), 'supp_') . '.zip';
        
        if ($zip->open($zip_filename, ZipArchive::CREATE) !== TRUE) {
            die("Не удалось создать архив.");
        }

        foreach ($supplementary_files as $file) {
            if (file_exists($file)) {
                $zip->addFile($file, basename($file));
            }
        }
        
        $zip->close();
        $file_path = $zip_filename;
        $original_filename .= '_supplementary.zip';
        $is_zip = true;
    }

    if (!$file_path || !file_exists($file_path)) {
        die("Файл не найден.");
    }

    // Проверяем права доступа:
    // Все пользователи: если опубликована статья !!Добавить
    // Редактор: может скачивать все файлы.
    // Рецензент: может скачивать только те файлы статей, которые ему назначены и он принял.
    // Автор: может скачивать только свои файлы.

    $current_user_id = $_SESSION['user']['id'];
    $is_authorized = false;
    $allow_access = false;

    if ($is_published) {
        $allow_access = true;
    }
    // Или если пользователь авторизован и имеет права
    elseif (isset($_SESSION['user'])) {
        $current_user_id = $_SESSION['user']['id'];
        
        if (hasRole('editor')) {
            $allow_access = true;
        }
        elseif (hasRole('author') && $article['user_id'] == $current_user_id) {
            $allow_access = true;
        }
        elseif (hasRole('reviewer')) {
            $stmtReviewerCheck = $pdo->prepare("SELECT invitation_status FROM article_reviewers 
                                              WHERE article_id = ? AND reviewer_id = ? 
                                              AND invitation_status = 'Accepted'");
            $stmtReviewerCheck->execute([$article_id, $current_user_id]);
            if ($stmtReviewerCheck->fetchColumn()) {
                $allow_access = true;
            }
        }
    }

    if (!$allow_access) {
        die("У вас нет прав для скачивания этого файла.");
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file_path);
    finfo_close($finfo);

    // Отправка файла
    header('Content-Description: File Transfer');
    header('Content-Type: ' . $mime_type);
    header('Content-Disposition: attachment; filename="' . basename($original_filename) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file_path));
    readfile($file_path);

     // Удаляем временный ZIP-файл, если он был создан
    if ($is_zip) {
        unlink($file_path);
    }
    exit;

} catch (PDOException $e) {
    error_log("Ошибка при скачивании файла: " . $e->getMessage());
    die("Произошла ошибка при доступе к файлу.");
}
?>