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
        <link rel="stylesheet" href="css/manage_issue.css">
        <script src="js/manage_issue.js" defer></script>
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
                     <article>
                            <h1>Управление статьями в выпуске</h1>
                            
                            <div class="issue-selector">
                                <label for="issue-select" disabled selected>Выберите выпуск:</label>
                                <select id="issue-select" class="form-select">
                                    <option value="">Загрузка выпусков...</option>
                                </select>
                                <button id="publish-issue" class="btn btn-publish" style="display: none;">Опубликовать</button>
                            </div>
                            
                            <div class="articles-management-container">
                                <div class="articles-column">
                                    <h2>Принятые статьи</h2>
                                    <div id="accepted-articles" class="articles-list">
                                        <!-- Список будет заполнен через JavaScript -->
                                        <div class="loading-message">Загрузка статей...</div>
                                    </div>
                                </div>
                                
                                <div class="articles-column">
                                    <h2>Статьи в выпуске</h2>
                                    <div id="issue-articles" class="articles-list">
                                        <!-- Список будет заполнен при выборе выпуска -->
                                        <div class="empty-message">Выберите выпуск</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button id="save-changes" class="btn btn-primary" disabled>Сохранить изменения</button>
                                <div id="save-response" class="form-response"></div>
                            </div>
                    </article>
                </main>
                    <aside class="right-aside">
                        <nav class="side-nav">
                            <h3>Действия</h3>
                            <a href="/create_issue.php">← Вернуться к созданию</a>
                        </nav>
                    </aside>
            </div>
        </div>

        <footer>
            <?php require_once 'include/footer.php'; ?>
        </footer>
        <?php require_once 'include/modalCheck.php'; ?>
    </div>
        <div id="publish-modal" class="modal" style="display: none;">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <h2>Подтверждение публикации</h2>
                <p>Вы уверены, что хотите опубликовать этот выпуск?</p>
                <div class="form-group">
                    <label for="publish-date">Дата публикации:</label>
                    <input type="datetime-local" id="publish-date" class="form-control">
                </div>
                <div class="modal-actions">
                    <button id="confirm-publish" class="btn btn-primary">Опубликовать</button>
                    <button id="cancel-publish" class="btn btn-secondary">Отмена</button>
                </div>
                <div id="publish-response" class="form-response"></div>
            </div>
        </div>
</body>
</html>