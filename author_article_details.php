<?php
session_start();
    require_once 'include/roles.php';
    // Проверяем, авторизован ли пользователь
    $isLoggedIn = hasRole('author');
    if (!hasRole('author')) {
        http_response_code(403);
        exit();
    } 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site</title>
    <link rel="stylesheet" href="z/pattern.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="css/author_article_details.css">
    
    <script src="js/header.js" defer></script>
    <script src="js/author_article_details.js" defer></script>
    <?php if (!$isLoggedIn): ?>
        <link rel="stylesheet" href="css/modal.css">
        <script src="js/modalCheck.js" defer></script>
    <?php endif; ?>
</head>
<body>
    <!--Обёртка для страницы сайта, чтобы легче манипулировать содержимым-->
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
                        <h2>Детали рукописи</h2>
                        <div id="article-details" class="article-container"></div>
                    </article>
                </main>
                <aside class="right-aside">
                    <nav class="side-nav">
                        <h3>Действия</h3>
                        <a href="my_articles.php">← Назад к списку</a>
                        <a href="#" id="download-manuscript" class="link-file">Скачать статью</a>
                        <a href="#" id="manuscript-file-link" class="link-file">Скачать доп.файлы</a>
                    </nav>
                </aside>
            </div>
        </div>

        <footer>
            <?php require_once 'include/footer.php'; ?>
        </footer>
    </div>
    <?php 
        if (!$isLoggedIn) {
            require_once 'include/modalCheck.php'; 
        }
        ?>
</body>
</html>





