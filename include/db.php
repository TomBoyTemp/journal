<?php 

    $host = 'localhost';
    $dbname = 'vkr';
    $user = 'root';
    $password = '1234';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = null;

    try{
        $pdo = new PDO($dsn, $user, $password, $options);
    }
    catch (\PDOException $err){
        throw new \PDOException($err->getMessage(), (int)$err->getCode());
    }
    
?>