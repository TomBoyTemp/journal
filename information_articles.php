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
    <!-- <link rel="stylesheet" href="css/index.css"> -->
    
    <link rel="stylesheet" href="css/editors_manuscript_details.css">

    <script src="js/information_articles.js" defer></script>
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
                      <h1>Детали рукописи</h1>
                        <div id="loading-message" class="loading-message">Загрузка данных рукописи...</div>
                        <div id="error-message" class="error-message" style="display: none;"></div>
                        
                        <div id="manuscript-details" class="manuscript-details" style="display: none;">
                            <h2 id="manuscript-title" class="section-title"></h2>
                            
                            <div class="detail-row">
                                <div class="detail-label">ID статьи:</div>
                                <div class="detail-value" id="manuscript-id"></div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">Авторы:</div>
                                <div class="detail-value" id="manuscript-authors"></div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">Дата публикации:</div>
                                <div class="detail-value" id="manuscript-date"></div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">Ключевые слова:</div>
                                <div class="detail-value" id="manuscript-keywords"></div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">Аннотация:</div>
                                <div class="detail-value" id="manuscript-abstract"></div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">Файл статьи:</div>
                                <div class="detail-value">
                                    <a href="#" id="manuscript-file-link" class="file_download">Скачать</a>
                                </div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Доп. файл(ы):</div>
                                <div class="detail-value">
                                    <a href="#" id="download-manuscript" class="file_download">Скачать</a>
                                </div>
                            </div>

                        </div>
                </main>
                
                <aside class="right-aside">
                    
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