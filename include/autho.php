<?php
session_start();
    require_once 'db.php';
    require_once 'DatabaseService.php';
    require_once 'functions.php';
    
    header('Content-Type: application/json; charset=utf-8');

    $dbService = new DatabaseService($pdo);

    
    function registerUser(DatabaseService $dbService, array $postData): array {
        $userData = [
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
            'about' => [
                'first_name' => $_POST['regName'] ?? '',
                'last_name' => $_POST['regSurname'] ?? '',
                'organization' => $_POST['regOrganization'] ?? '',
                'country' => $_POST['regCountry'] ?? ''
            ],
            'consents' => [
                'data_processing' => isset($_POST['consentRule']) ??  null,
                'notifications' => isset($_POST['emailConsent']) ?? null,
                'want_reviewer' => isset($_POST['reviewer']) ?? null,
                'reviewer_interests' => isset($_POST['reviewerInterests']) ?$_POST['reviewerInterests'] : ''
            ]
        ];
       
        if($dbService->registerUser($userData)) 
            return ['success' => true, 'message' => 'Регистрация успешна! Проверьте почту.'];

        return ['success' => false, 'message' => $dbService->getLastError()];
    }


    function loginUser(DatabaseService $dbService, array $postData): array 
    {
        $email = sanitizeInput($postData['login'] ?? '');
        $password = $postData['passw'] ?? '';

        $user = $dbService->authenticateUser($email, $password);
        
        if ($user) {
            // Стартуем сессию и сохраняем данные
            $_SESSION['user'] = [
                'id' => $user['id'],
                'email' => $user['email'],
                'first_name' => $user['first_name'] ?? '',
                'last_name' => $user['last_name'] ?? '',
                'organization' => $user['roles'],
                'roles' => $user['roles'],
            ];
            return [
                'success' => true,
                'message' => 'Вход выполнен успешно',
                'location' => "../pattern.php"
            ];
        }
        
        return [
            'success' => false,
            'message' => $dbService->getLastError()
        ];
    }


    try {
        if (!isset($_POST['form_type'])) {
            throw new InvalidArgumentException('Не указан тип формы');
        }
        
        $response = match ($_POST['form_type']) {
            'register' => registerUser($dbService, $_POST),
            'login'    => loginUser($dbService, $_POST),
            default    => throw new InvalidArgumentException('Неизвестный тип формы')
        };
        
        echo json_encode($response);
    
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        echo json_encode([
        'success' => false,
        'message' => 'Произошла ошибка. Пожалуйста, попробуйте позже.'
        ]);
    }

