<?php
    session_start();
    require_once 'include/db.php';
    require_once 'include/getMetrics.php';
    $isLoggedIn = isset($_SESSION['authenticated']);
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
    
    <link rel="stylesheet" href="css/articles.css">

    <link rel="stylesheet" href="css/index.css">
    <script src="js/articles.js" defer></script>
    <script src="js/header.js" defer></script>
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
                    <div id="searchResults"></div>
                </main>
                
                <aside class="right-aside">
                    <form id="searchForm" action="/api/user/search_articles.php" method="get">
                        <input type="text" name="query" placeholder="Введите поисковый запрос...">
                        <button type="submit">Поиск</button>
                        <button type="button" id="resetSearch" class="reset-btn">Сбросить</button>
                    </form>
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