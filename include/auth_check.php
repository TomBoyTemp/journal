<?php

//ДОБАВИТЬ ИМЯ ПОЛЬЗОВАТЕЛЯ
session_start();
header('Content-Type: application/json');

$response = [
    'success' => false,
    'username' => ''
];


if (isset($_SESSION['user']['id'])) {
    $response = [
        'success' => true,
        'username' => $_SESSION['user']['first_name']
    ];
}

echo json_encode($response);
?>