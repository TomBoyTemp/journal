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
    <script src="js/header.js" defer></script>
    
    <?php if ($isLoggedIn): ?>
        <link rel="stylesheet" href="css/search.css">
        <script src="js/my_articles.js" defer></script>
        <link rel="stylesheet" href="css/my_article.css">
    <?php else: ?>
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
                    <?php if (isset($warningMessage)): ?>
                    <div class="warning-message">
                        <div class="warning-content">
                            <p><?php echo $warningMessage; ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($isLoggedIn): ?>
                    <article>
                        <h2>Мои статьи</h2>
                        <div id="my-articles-list"></div>
                    </article>
                    <?php endif; ?>
                </main>
                <aside class="right-aside">
                    <?php if ($isLoggedIn): ?>
                    <nav class="side-nav">
                        <h2><i class="fas fa-search"></i>Поиск</h2>
                        <?php require_once 'include/search.php' ?>
                    </nav>
                    <?php else: ?>
                        <h3>Действия</h3>
                        <a href="/">← На главную</a>
                    <?php endif; ?>
                </aside>

            </div>
        </div>

        <footer>
           <?php require_once 'include/footer.php'; ?>
        </footer>
         <?php 
        if (!$isLoggedIn) {
            require_once 'include/modalCheck.php'; 
        }
        ?>
    </div>

</body>
</html>





