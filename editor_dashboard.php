<?php
session_start();
    require_once 'include/roles.php';
    // Проверяем, авторизован ли пользователь
    $isLoggedIn = hasRole('editor');
    if (!hasRole('editor')) {
        http_response_code(404);
        exit();
    } 

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель редактора</title>
    <link rel="stylesheet" href="z/pattern.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/modal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <script src="js/header.js" defer></script>
    
    <?php if ($isLoggedIn): ?>
        <link rel="stylesheet" href="css/editor_dashboard.css">
        <script src="js/editor_dashboard.js" defer></script>
        <link rel="stylesheet" href="css/search.css">
    <?php else: ?>
        <link rel="stylesheet" href="css/modal.css">
        <script src="js/modalCheck.js" defer></script>
    <?php endif; ?>
    
</head>
<body>
    <div class="container">
        <header>
            <div class="header-content">
                <?php require_once 'include/header.php'; ?>
            </div>
        </header>
        
        <div class="stratch-container-content">
            <div class="container-content">
                <!-- Боковая панель -->
                 <aside class="left-aside">
                    <?php require_once 'include/profileForm.php' ?>
                </aside>

                <!-- Основной контент -->
                <main class="main-content">
                    <article>
                        <h1>Панель редактора</h1>
                        <div id="manuscripts-list">Загрузка рукописей...</div>
                    </article>
                </main>
                
                <aside class="right-aside">
                    <h2><i class="fas fa-search"></i>Поиск</h2>
                        <?php require_once 'include/search.php' ?>
                </aside>
            </div>
        </div>

        <footer>
            <?php require_once 'include/footer.php'; ?>
        </footer>
        <?php require_once 'include/modalCheck.php'; ?>
    </div>
</body>
</html>