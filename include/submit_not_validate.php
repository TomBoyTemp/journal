<?php 
session_start();
    
    require_once 'db.php';
    require_once 'DatabaseService.php';
    require_once 'functions.php';

    function containsNonNumeric($string) {
        return preg_match('/[^0-9]/', $string);
    }


    header('Content-Type: application/json');

    if (!isset($_SESSION['user']['id'])){
        echo json_encode(['status' => 'error', 'message' => 'Нужно зарегестрироваться']);
        exit();
    }


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        //Обработка данных

        $title = sanitizeInput($_POST['title-main-feed'] ?? '');
        $abstract = sanitizeInput($_POST['abstract-main-feed'] ?? '');
        $section_id = filter_var($_POST['section'] ?? '', FILTER_VALIDATE_INT);
        $license_agreement_accepted = isset($_POST['license_agreement']) ? 1 : 0;
        $current_user_id = $_SESSION['user']['id'];

        $keywords_json = $_POST['keywords'] ?? '[]';
        $keywords_array = json_decode($keywords_json, true);

        $processed_keywords = [];
        if(is_array($keywords_array)){
            foreach($keywords_array as $keywords_obj){
                if (isset($keywords_obj['value'])){
                    $processed_keywords[] = sanitizeInput($keywords_obj['value']);
                }
            }
        }

        $keywords_for_db = implode(', ', $processed_keywords);

        //Валидация данных
        $errors = [];
        
        if(!empty($keywords_for_db)){
            if(!containsNonNumeric($keywords_for_db)){
                $errors[] = "Ключевые слова не могут состоять только из цифр";
            }
        }

        if(empty($title)){
            $errors[] = "Название статьи не может быть пустым.";
        } elseif (!containsNonNumeric($title)) {
            $errors[] = "Название статьи не может состоять только из цифр.";
        }


        if(empty($abstract)){
            $errors[] = "Аннотация не может быть пустой.";
        } elseif (!containsNonNumeric($abstract)){
            $errors[] = "Аннотация не может состоять только из цифр.";
        }
        
        if($section_id === false || $section_id <= 0){
            $errors[] = "Необходимо выбрать корреектную секцию журнала.";
        } else {
            $stmtCheckSection = $pdo->prepare("SELECT COUNT(*) FROM journal_sections WHERE id=?");
            $stmtCheckSection->execute([$section_id]);
            if($stmtCheckSection->fetchColumn() === 0){
                $errors[] = "Выбранная секция журнала не существует.";
            }
        }
        if($license_agreement_accepted === 0){
            $errors[] = "Прежде чем отправить статью, нужно согласиться с лицензионным договором";
        }

        if(extension_loaded('fileinfo')){
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if (!$finfo){
                $errors[] = "Ошибка сервера: не удалось инициализировать проверку типа файла.";
            }
        }
        

        //Загрузка файла рукописи с валидацией.
        $main_file_path = null;
        if(isset($_FILES['manuscript']) && $_FILES['manuscript']['error'] === UPLOAD_ERR_OK){
            $fileTmpPath = $_FILES['manuscript']['tmp_name'];
            $fileName = basename($_FILES['manuscript']['name']);
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExts = ['doc', 'docx', 'pdf', 'tex', 'rtf'];
            $allowedMimeTypes = [
                'application/msword', // .doc
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
                'application/pdf', // .pdf
                'application/x-tex', // .tex (общий для TeX/LaTeX)
                'application/rtf', // .rtf
                'text/rtf',
                'text/plain'
            ];

            $mimeType = false;
            if($finfo){
                $mimeType = finfo_file($finfo, $fileTmpPath);
            }

            if(!in_array($fileExt, $allowedExts) || !in_array($mimeType, $allowedMimeTypes)){
                $errors[] = "Недопустимый формат файла рукописи. Разрешены: " . implode(', ', $allowedExts) . ".";
            } else {
                $uploadDir = 'D:/uploads/manuscript/';
                if(!is_dir($uploadDir)){
                    mkdir($uploadDir, 0777, true);
                }
                $newFileName = uniqid('manuscript_') . '.' . $fileExt;
                $main_file_path = $uploadDir . $newFileName;

                if(!move_uploaded_file($fileTmpPath, $main_file_path)){
                    $errors[] = "Ошибка при загрузке файла рукописи на сервер.";
                    $main_file_path = null;
                }
            }
        } else {
            switch ($_FILES['manuscript']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $errors[] = "Размер файла рукописи превышает допустимый лимит сервера.";
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $errors[] = "Файл рукописи не был выбран";
                    break;
                default:
                    $errors[] = "Незивестная ошибка при загрузке файла рукописи.";
                    break;
            }
        }

        $supplementary_file_paths = [];
        if(isset($_FILES['supplementary_files']) && is_array($_FILES['supplementary_files']['name'])) {
            $uploadDir = 'D:/uploads/supplementary/';
            if(!is_dir($uploadDir)){
                mkdir($uploadDir, 0777, true);
            }
            $allowedSuppExts = ['pdf', 'jpg', 'jpeg', 'png', 'xls', 'xlsx'];
            $allowedSuppMimeTypes = [
                'application/pdf',
                'image/jpeg',
                'image/png',
                'application/vnd.ms-excel', // .xls
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
                'text/plain' // .txt
            ];

            foreach($_FILES['supplementary_files']['tmp_name'] as $key => $tmp_name){
                $fileName = $_FILES['supplementary_files']['name'][$key];
                $fileError = $_FILES['supplementary_files']['error'][$key];
                $fileSize = $_FILES['supplementary_files']['size'][$key];

                if ($fileError === UPLOAD_ERR_NO_FILE){
                    continue;
                }
                
                if ($fileError !== UPLOAD_ERR_OK) {
                    switch ($fileError) {
                        case UPLOAD_ERR_INI_SIZE:
                        case UPLOAD_ERR_FORM_SIZE:
                            $errors[] = "Размер дополнительного файла '" . $fileName . "' превышает допустимый лимит сервера.";
                            break;
                        case UPLOAD_ERR_PARTIAL:
                            $errors[] = "Дополнительный файл '" . $fileName . "' был загружен не полностью.";
                            break;
                        default:
                            $errors[] = "Ошибка загрузки дополнительного файла '" . $fileName . "': Код ошибки " . $fileError;
                            break;
                    }
                    continue; // Переходим к следующему файлу, так как этот с ошибкой
                }

                $suppFileName = basename($_FILES['supplementary_files']['name'][$key]);
                $suppFileEXt = strtolower(pathinfo($suppFileName, PATHINFO_EXTENSION));

                $suppMimeType = false;
                if ($finfo){
                    $suppMimeType = finfo_file($finfo, $tmp_name);
                } else {
                    $errors[] = "Расширение fileinfo не загружено, проверка MIME-типа для '" . $fileName . "' пропущена.";
                }

                if (!in_array($suppFileEXt, $allowedSuppExts) || ($suppMimeType && !in_array($suppMimeType, $allowedSuppMimeTypes))) {
                    $errors[] = "Недопустимый формат дополнительного файла: " . $fileName;
                    continue; // Переходим к следующему файлу
                }

                    // Все проверки пройдены, перемещаем файл
                $newSuppFileName = uniqid('supp_') . '.' . $suppFileEXt;
                $suppFilePath = $uploadDir . $newSuppFileName;

                if(move_uploaded_file($tmp_name, $suppFilePath)){
                    $supplementary_file_paths[] = $suppFilePath;
                } else {
                    $errors[] = "Не удалось переместить дополнительный файл '" . $fileName . "' на сервер.";
                }
            }
        }

        if(isset($finfo) && is_resource($finfo)){
            finfo_close($finfo);
        }

        $authors_data = $_POST['authors'] ?? [];
        $valid_authors = [];
        if(!empty($authors_data)){
            foreach($authors_data as $index => $author){
                $name = sanitizeInput($author['name'] ?? '');
                $affiliation = sanitizeInput($author['affiliation'] ?? '');
                $email = sanitizeInput($author['email'] ?? '');

                if(empty($name)){
                    $errors[] = "ФИО автора №" . ($index + 1) . " не может быть пустым.";
                }
                if(empty($affiliation)){
                    $errors[] = "Аффилиация автора №" . ($index + 1) . " не может быть пустой.";
                }
                if(empty($email) && $index == 0){
                    $errors[] = "Email автора №" . ($index + 1) . " не может быть пустой.";
                }
                if(!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)){
                    $errors[] = "Некорректный email для автора №" . ($index + 1) . ".";
                }
                if(!empty($name) && !empty($affiliation)){
                    $valid_authors[] = [
                        'name' => $name,
                        'affiliation' => $affiliation,
                        'email' => $email
                    ];
                }
            }
        }

        if(!empty($errors)){
            echo json_encode($errors);
            exit();
        }

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO articles (user_id, title, abstract, keywords, section, file_path, supplementary_files, license_agreement_accepted, current_version_path)
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $current_user_id,
                $title,
                $abstract,
                $keywords_for_db,
                $section_id,
                $main_file_path,
                json_encode($supplementary_file_paths),
                $license_agreement_accepted,
                $main_file_path
            ]);
            $articleId = $pdo->lastInsertId();

            if(!empty($valid_authors)){
                $stmtAuthors = $pdo->prepare("INSERT INTO authors (article_id, name, affiliation, email) VALUES (?, ?, ?, ?)");
                foreach ($valid_authors as $author) {
                    $stmtAuthors->execute([$articleId, $author['name'], $author['affiliation'], $author['email']]);
                }
            }

            $pdo->commit();

            echo json_encode(['status' => 'success', 'message' => "ID вашей статьи: " . $articleId]);

        } catch (PDOException $err){
            $pdo->rollBack();
            error_log("Ошибка при подаче рукописи:" . $err->getMessage());
            echo json_encode($response);
            exit();
        }

    }


?>