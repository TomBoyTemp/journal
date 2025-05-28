<?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    use PHPMailer\PHPMailer\SMTP;

    require_once 'C:\Web\Core\PHPMailer-6.10.0\src\Exception.php';
    require_once 'C:\Web\Core\PHPMailer-6.10.0\src\PHPMailer.php';
    require_once 'C:\Web\Core\PHPMailer-6.10.0\src\SMTP.php';

    function sanitizeInput($data) {
        // return htmlspecialchars(trim(stripslashes($data)));
        $data = (string) $data;
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    function sendReviewInvitationEmail($pdo, $article_id, $reviewer_id, $deadline) {
        // Получаем данные статьи
        $stmtArticle = $pdo->prepare("SELECT title FROM articles WHERE id = ?");
        $stmtArticle->execute([$article_id]);
        $article = $stmtArticle->fetch(PDO::FETCH_ASSOC);

        // Получаем данные рецензента
        $stmtReviewer = $pdo->prepare("SELECT 
                                            u.id,
                                            u.email,
                                            CONCAT(au.first_name, ' ', au.last_name) AS username
                                        FROM 
                                            users u
                                        JOIN 
                                            about_user au ON u.id = au.id
                                        JOIN 
                                            user_roles ur ON u.id = ur.users_id
                                        JOIN 
                                            roles r ON ur.role_id = r.id
                                        WHERE 
                                            u.id = ? AND r.name = 'reviewer'");
        $stmtReviewer->execute([$reviewer_id]);
        $reviewer = $stmtReviewer->fetch(PDO::FETCH_ASSOC);

        if (!$article || !$reviewer) {
            error_log("Не удалось найти данные статьи или рецензента для отправки приглашения.");
            return false;
        }

        $mail = new PHPMailer(true); // Включаем исключения

        try {
             $mail->isSMTP();
            $mail->Host = '127.0.0.1';
            $mail->Port = 1025;
            $mail->SMTPAuth = false;
            $mail->SMTPDebug = 0; 

            // Кодировка
            $mail->CharSet = 'UTF-8';

            // От кого
            $mail->setFrom('no-reply@yourdomain.local', 'Редакция журнала'); // Email и имя отправителя
            // Кому
            $mail->addAddress($reviewer['email'], $reviewer['username']);

            // Содержимое письма
            $mail->isHTML(true); // Формат HTML
            $mail->Subject = 'Приглашение к рецензированию рукописи: ' . $article['title'];
            $mail->Body    = "
                <p>Уважаемый(ая) " . htmlspecialchars($reviewer['username']) . ",</p>
                <p>Редакция журнала приглашает Вас рассмотреть рукопись <b>«" . htmlspecialchars($article['title']) . "»</b> для рецензирования.</p>
                <p>Вы можете ознакомиться с рукописью и предоставить Вашу рецензию по следующей ссылке:</p>
                <p><a href='localhost/reviewer_dashboard.php" . $article_id . "&reviewer_id=" . $reviewer_id . "'>Перейти к рецензированию</a></p>
                <p>Ожидаемый срок рецензирования: $deadline</p>
                <p>Благодарим Вас за вклад в развитие науки.</p>
                <p>С уважением,</p>
                <p>Редакция журнала</p>
            ";
            $mail->AltBody = "Уважаемый(ая) " . $reviewer['username'] . ",\n\nРедакция журнала приглашает Вас рассмотреть рукопись «" . $article['title'] . "» для рецензирования.\nВы можете ознакомиться с рукописью и предоставить Вашу рецензию по следующей ссылке: http://your_journal_domain.com/reviewer/review_manuscript.php?article_id=" . $article_id . "&reviewer_id=" . $reviewer_id . "\n\nБлагодарим Вас.\nРедакция журнала";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Ошибка отправки письма рецензенту " . $reviewer['email'] . ": " . $mail->ErrorInfo);
            return false;
        }
    }

function sendAuthorNotificationEmail($pdo, $article_id, $author_email, $author_username, $decision, $comments_for_author) {
    // Получаем название статьи для письма
    $stmtArticleTitle = $pdo->prepare("SELECT title FROM articles WHERE id = ?");
    $stmtArticleTitle->execute([$article_id]);
    $article_title = $stmtArticleTitle->fetchColumn();

    $subject = '';
    $body = '';

    switch ($decision) {
        case 'Accept':
            $subject = 'Решение по Вашей рукописи: Принята к публикации';
            $body = "<p>Уважаемый(ая) " . htmlspecialchars($author_username) . ",</p>
                     <p>Ваша рукопись <b>«" . htmlspecialchars($article_title) . "»</b> (ID: " . $article_id . ") была <strong>принята к публикации</strong> в нашем журнале!</p>";
            break;
        case 'Minor Revisions':
        case 'Major Revisions':
            $subject = 'Решение по Вашей рукописи: Требуются доработки';
            $body = "<p>Уважаемый(ая) " . htmlspecialchars($author_username) . ",</p>
                     <p>Ваша рукопись <b>«" . htmlspecialchars($article_title) . "»</b> (ID: " . $article_id . ") требует <strong>доработок</strong> перед публикацией.</p>
                     <p>Пожалуйста, ознакомьтесь с комментариями редактора и рецензентов (если применимо):</p>";
            break;
        case 'Reject':
            $subject = 'Решение по Вашей рукописи: Отклонена';
            $body = "<p>Уважаемый(ая) " . htmlspecialchars($author_username) . ",</p>
                     <p>К сожалению, Ваша рукопись <b>«" . htmlspecialchars($article_title) . "»</b> (ID: " . $article_id . ") была <strong>отклонена</strong>.</p>";
            break;
    }

    $body .= "<p><strong>Комментарии от редактора:</strong></p><p>" . nl2br(htmlspecialchars($comments_for_author)) . "</p>";

    if (in_array($decision, ['Minor Revisions', 'Major Revisions', 'Reject'])) {
        // Если вы хотите включить комментарии рецензентов (author_comments) напрямую в письмо автору:
        // Получите все author_comments для этой статьи и добавьте их в тело письма.
        $stmtAuthorComments = $pdo->prepare("SELECT r.comments_for_author, 
                                                    r.review_date,
                                                    CONCAT(au.first_name, ' ', au.last_name) AS username
                                            FROM reviews r 
                                            JOIN users u ON r.reviewer_id = u.id 
                                            LEFT JOIN
                                                 about_user au ON u.id = au.id
                                            WHERE r.article_id = ? AND r.comments_for_author IS NOT NULL AND r.comments_for_author != ''");
        $stmtAuthorComments->execute([$article_id]);
        $all_author_comments = $stmtAuthorComments->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($all_author_comments)) {
            $body .= "<p><strong>Комментарии рецензентов:</strong></p>";
            foreach ($all_author_comments as $ac) {
                $body .= "<div style='border-left: 3px solid #ccc; padding-left: 10px; margin-bottom: 10px;'>";
                $body .= "<p><i>(От рецензента, " . $ac['review_date'] . ")</i></p>";
                $body .= "<p>" . nl2br(htmlspecialchars($ac['comments_for_author'])) . "</p>";
                $body .= "</div>";
            }
        }
        $body .= "<p>Для доработок, пожалуйста, загрузите пересмотренную версию рукописи через Ваш личный кабинет.</p>";
    }

    $mail = new PHPMailer(true);
    try {
        // Настройки SMTP 
        $mail->isSMTP();
        $mail->Host = '127.0.0.1';
        $mail->Port = 1025;
        $mail->SMTPAuth = false;
        $mail->SMTPDebug = 0; 
        
        $mail->validateAddress(false);

        // Кодировка
        $mail->CharSet = 'UTF-8';

        // От кого
        $mail->setFrom('no-reply@yourdomain.local', 'Редакция журнала'); // Email и имя отправителя
        $mail->addAddress($author_email, $author_username);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body); // Простой текст для клиентов без HTML

        $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Ошибка отправки письма автору " . $author_email . ": " . $mail->ErrorInfo);
            return false;
        }
    }

    function sendResetPasswordEmail($pdo, $email, $link) {

        $mail = new PHPMailer(true); // Включаем исключения
 
        try {
            $mail->isSMTP();
            $mail->Host = '127.0.0.1';
            $mail->Port = 1025;
            $mail->SMTPAuth = false;
            $mail->SMTPDebug = 0; 

            // Кодировка
            $mail->CharSet = 'UTF-8';

            // От кого
            $mail->setFrom('no-reply@yourdomain.local', 'Администрация журнала'); // Email и имя отправителя
            // Кому
            $mail->addAddress($email);

            // Содержимое письма
            $mail->isHTML(true); // Формат HTML
            $mail->Subject = 'Сброс пароля на сайте журнала';
            $mail->Body    = "
                <p>Здравствуйте,</p>
                <p>Вы запросили сброс пароля на сайте научного журнала. Для этого, пожалуйста, перейдите по следующей ссылке:</p>
                <p>Вы можете ознакомиться с рукописью и предоставить Вашу рецензию по следующей ссылке:</p>
                <p><a href='$link'>Восстановить пароль</a></p>
                <p>Срок действия ссылки истекает через 1 час.</p>
                <p>Если вы не запрашивали сброс пароля, проигнорируйте это письмо.</p>
                <p>С уважением,</p>
                <p>Администрация журнала</p>
            ";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Ошибка отправки письма рецензенту " . $reviewer['email'] . ": " . $mail->ErrorInfo);
            return false;
        }
    }

    function sendVerificationEmail(string $email, string $token): bool {

      $mail = new PHPMailer(true);

        try {
            // 1. Настройки MailHog
            $mail->isSMTP();
            $mail->Host = '127.0.0.1';
            $mail->Port = 1025;
            $mail->SMTPAuth = false;
            $mail->SMTPDebug = 0; 
            
            // 2. Отправитель и получатель
            $mail->setFrom('no-reply@yourdomain.local', 'Редакция журнала');
            $mail->addAddress('user@example.com');
            
            // 3. Генерация ссылки (используйте ваш реальный домен в production)
            $verificationLink = "http://localhost/api/user/verify.php?token=" . urlencode($token);
            
            // 4. HTML-шаблон письма
            $htmlTemplate = "
                <!DOCTYPE html>
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; }
                        .button {
                            display: inline-block;
                            padding: 10px 20px;
                            background-color: #4CAF50;
                            color: white;
                            text-decoration: none;
                            border-radius: 5px;
                        }
                    </style>
                </head>
                <body>
                    <h2>Подтверждение регистрации</h2>
                    <p>Благодарим вас за регистрацию!</p>
                    <p>Для завершения процесса, пожалуйста, подтвердите ваш email:</p>
                    <p>
                        <a href='$verificationLink' class='button'>
                            Подтвердить Email
                        </a>
                    </p>
                    <p>Или скопируйте ссылку в браузер:<br>
                    <code>$verificationLink</code></p>
                    <p><small>Ссылка действительна в течение 1 часа.</small></p>
                </body>
                </html>
            ";
            
            // 5. Текстовая версия для почтовых клиентов без HTML
            $textVersion = "Подтвердите ваш email, перейдя по ссылке:\n$verificationLink\n\n" . "Ссылка действительна 1 час.";

            // 6. Настройка письма
            $mail->isHTML(true);
            $mail->Subject = 'Подтверждение регистрации';
            $mail->Body = $htmlTemplate;
            $mail->AltBody = $textVersion;
            $mail->CharSet = 'UTF-8';
            
            // 7. Отправка
            $mail->send();
            // if (!$mail->send()) {
            //     throw new Exception('Mailer Error: ' . $mail->ErrorInfo);
            // }
            
            return true;

        } catch (Exception $e) {
            $this->addError('Ошибка отправки email', $e);
            return false;
        }
    }
?>