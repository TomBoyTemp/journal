<?php
session_start();
// Проверяем, авторизован ли пользователь
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
    <link rel="stylesheet" href="css/archive.css">


    <?php if (!$isLoggedIn): ?>
        <link rel="stylesheet" href="css/modal.css">
        <script src="js/modalCheck.js" defer></script>
    <?php endif; ?>
    <script src="js/header.js" defer></script>
    <script src="js/archive.js" defer></script>
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
                        <div id="issues-archive" class="issues-grid">
                            <div class="loading-message">Загрузка архива выпусков...</div>
                        </div>
                    </article>
                </main>
                <aside class="right-aside">
                    <nav class="side-nav">
                        <h3>Меню</h3>
                        <a href="#">Раздел 1</a>
                        <a href="#">Раздел 2</a>
                        <a href="#">Раздел 3</a>
                        <a href="#">Раздел 4</a>
                    </nav>
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