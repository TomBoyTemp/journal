<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once 'C:\Web\Core\PHPMailer-6.10.0\src\Exception.php';
require_once 'C:\Web\Core\PHPMailer-6.10.0\src\PHPMailer.php';
require_once 'C:\Web\Core\PHPMailer-6.10.0\src\SMTP.php';


class DatabaseService {
    private PDO $pdo;
    private array $errors = [];
    private ?Throwable  $lastException = null;

    public function __construct(PDO $pdo){
        $this->pdo = $pdo;
    }

    public function getLastError(): ?string {
        return end($this->errors) ?: null;
    }


    private function addError(string $message, ?Throwable  $err = null): void {
        $this->errors[] = $message;
        if ($err) {
            $this->lastException = $err;
        }
    }

    //Проверка существования email в БД
    public function isEmailExists(string $email) : bool {
        try{
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?;");
            $stmt->execute([$email]);
            return (bool)$stmt->fetchColumn();
        } catch (Exception $err){
            $this->addError('Ошибка проверки email', $err);
            return false;
        }
    }

    //Назначение роли
    public function assignRole(int $userId, string $roleName): void {
       try{
            $stmt = $this->pdo->prepare(
                "INSERT INTO user_roles (users_id, role_id) 
                SELECT ?, id FROM roles WHERE name = ?");
            $stmt->execute([$userId, $roleName]);
       } catch (PDOException $err) {
            $this->addError('Ошибка назначения роли', $err);
       }
    }

    //Получение данных пользователя  НУЖНО ВСЕ ДАННЫЕ ПОЛУЧАТЬ
    public function getUserById(int $userId): ?array {
        try{
            $stmt = $this->pdo->prepare(
            "SELECT u.id, u.email, 
            a.first_name, a.last_name, a.organization, a.country, GROUP_CONCAT(r.name) AS roles
            FROM users u
            LEFT JOIN about_user a ON u.id = a.id
            LEFT JOIN user_roles ur ON u.id = ur.users_id
            LEFT JOIN roles r ON ur.role_id = r.id
            WHERE u.id = ?
            GROUP BY u.id"
            );
            $stmt->execute([$userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        } catch (PDOException $e) {
            $this->addError('Ошибка получения пользователя', $e);
            return null;
        }
    }

    //Верификация email
     public function verifyEmail(string $token): bool {
        try{
            $stmt = $this->pdo->prepare(
                "UPDATE users 
                SET email_verified = 1, verification_token = NULL 
                WHERE verification_token = ?"
            );
            $stmt->execute([$token]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $err) {
            $this->addError('Ошибка в получении верификации', $err);
            return false;
        }
    }

    //Роль пользователя
    public function getUserRoles(int $userId): ?string {
        try{
            $stmt = $this->pdo->prepare(
                "SELECT r.name 
                FROM user_roles ur
                JOIN roles r ON ur.role_id = r.id
                WHERE ur.users_id = ?"
            );
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: null;
        } catch (PDOException $err) {
            $this->addError('Ошибка в получении ролей', $err);
            return null;
        }
    }
    
    // Информация о пользователе
    private function insertAboutUser(int $userId, array $aboutData): void {
        try{
            $stmt = $this->pdo->prepare(
                "INSERT INTO about_user 
                (id, first_name, last_name, organization, country) 
                VALUES (?, ?, ?, ?, ?)"
            );
            
            $stmt->execute([
                $userId,
                $aboutData['first_name'],
                $aboutData['last_name'],
                $aboutData['organization'],
                $aboutData['country']
            ]);
        } catch (PDOException $err) {
            $this->addError('Ошибка в установлении информации', $err);
        }
    }

    private function insertConsents(int $userId, array $consents): void 
    {
        try{
            $stmt = $this->pdo->prepare(
                "INSERT INTO user_consents 
                (user_id, data_processing, notifications, want_reviewer, reviewer_interests) 
                VALUES (?, ?, ?, ?, ?)"
            );
            
            $stmt->execute([
                $userId,
                isset($consents['data_processing']) ? (int)$consents['data_processing'] : 1,
                isset($consents['notifications']) ? (int)$consents['notifications'] : 0,
                isset($consents['want_reviewer']) ? (int)$consents['want_reviewer'] : 0,
                isset($consents['reviewer_interests']) ? (string)$consents['reviewer_interests'] : '',
            ]);
        } catch (PDOException $err) {
            $this->addError('Ошибка в установлении соглашений', $err);
        }
    }

    private function sendVerificationEmail(string $email, string $token): bool {

      $mail = new PHPMailer(true);

        try {
            // 1. Настройки MailHog
            $mail->isSMTP();
            $mail->Host = '127.0.0.1';
            $mail->Port = 1025;
            $mail->SMTPAuth = false;
            $mail->SMTPDebug = 2; 
            
            // 2. Отправитель и получатель
            $mail->setFrom('no-reply@yourdomain.local', 'Редакция журнала');
            $mail->addAddress('user@example.com');
            
            // 3. Генерация ссылки (используйте ваш реальный домен в production)
            $verificationLink = "http://localhost/user/verify.php?token=" . urlencode($token);
            
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

    //Регистрация пользователя
    public function registerUser(array $userData): ?int {
        
        if ($this->isEmailExists($userData['email'])) {
            $this->addError('Пользователь с таким email уже зарегестрирован!');
            return null;
        }

         $this->pdo->beginTransaction();

        try {
            // Добавляем пользователя
            $stmt = $this->pdo->prepare(
                "INSERT INTO users (email, password_hash, verification_token, expires_at) 
                VALUES (?, ?, ?, ?)"
            );
            
            $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);

            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt->execute([
                $userData['email'],
                $hashedPassword,
                $token,
                $expires_at
            ]);

            
            $userId = (int)$this->pdo->lastInsertId();

            $this->insertAboutUser($userId, $userData['about']);
            $this->insertConsents($userId, $userData['consents']);
            $this->assignRole($userId, 'author');
            
            $this->pdo->commit();

              if (!$this->sendVerificationEmail($userData['email'], $token)) {
            throw new Exception('Не удалось отправить письмо подтверждения');
            }

            return $userId;
        } catch (Exception $err) {
            $this->pdo->rollBack();
            $this->addError('Ошибка регистрации', $err);
            return null;
        }
    }    

    public function updateVerificationToken(int $userId): bool
    {
        $token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE users SET verification_token = ?, expires_at = ? WHERE id = ?"
            );
            return $stmt->execute([$token, $expires_at, $userId]);
        } catch (PDOException $e) {
            $this->addError('Ошибка обновления токена', $e);
            return false;
        }
    }
    
    public function authenticateUser(string $email, string $password): ?array {
        try {
            // Получаем пользователя по email
            $stmt = $this->pdo->prepare(
                "SELECT id, email, password_hash, email_verified,verification_token, expires_at 
                FROM users 
                WHERE email = ?"
            );
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Если пользователь не найден или пароль не совпадает
            if (!$user || !password_verify($password, $user['password_hash'])) {
                $this->addError('Неверный email или пароль');
                return null;
            }

            // Если email не подтверждён
            if (!$user['email_verified']) {
                if ($user['verification_token'] && strtotime($user['expires_at']) < time()) {
                    $this->updateVerificationToken($user['id']);
                    $stmt = $this->pdo->prepare(
                        "SELECT verification_token FROM users WHERE id = ?"
                    );
                    $stmt->execute([$user['id']]);
                    $newToken = $stmt->fetchColumn();
                    $this->sendVerificationEmail($user['email'], $newToken);
                }
                $this->addError('Пожалуйста, подтвердите ваш email');
                return null;
            }

            // Получаем дополнительные данные и роль
            return  $this->getUserById($user['id']);

        } catch (PDOException $e) {
            $this->addError('Ошибка аутентификации', $e);
            return null;
        }
    }

    
}
