<?php
    session_start();
    require_once 'include/roles.php';
    // Проверяем, авторизован ли пользователь
    $isLoggedIn = hasRole('reviewer');
    if (!hasRole('reviewer')) {
        http_response_code(403);
        exit();
    } 

    // Если пользователь не авторизован, выводим предупреждение
    if (!$isLoggedIn) {
        $warningMessage = "Для доступа к этой странице необходимо быть рецензентом.";
    }
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои рецензии</title>
    <link rel="stylesheet" href="z/pattern.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="js/header.js" defer></script>
    
    <?php if ($isLoggedIn): ?>
        <link rel="stylesheet" href="css/reviewer_dashboard.css">
        <script src="js/reviewer_dashboard.js" defer></script>
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
                     <?php if (isset($warningMessage)): ?>
                    <div class="warning-message">
                        <div class="warning-content">
                            <p><?php echo $warningMessage; ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($isLoggedIn): ?>
                    <article>
                        <h1>Мои приглашения на рецензирование</h1>
                        <div id="loading-message" class="loading-message">Загрузка приглашений...</div>
                        <div id="error-message" class="error-message" style="display: none;"></div>
                        
                        <div id="review-invitations" class="review-invitations" style="display: none;">
                            <!-- Здесь будут отображаться карточки приглашений -->
                        </div>
                    </article>
                    <?php endif; ?>
                </main>
                
                <aside class="right-aside">
                <?php if ($isLoggedIn): ?>
                    <nav class="side-nav">
                        <h2><i class="fas fa-search"></i>Поиск</h2>
                        <div class="search-form">
                            <form id="searchForm">
                                <div class="search-row">
                                    <input type="text" id="searchTitle" placeholder="Название статьи">
                                    <input type="text" id="searchAuthor" placeholder="Автор">
                                    <select id="searchStatus">
                                        <option value="">Все статусы</option>
                                        <option value="Pending">На рассмотрении</option>
                                        <option value="Accepted">На рецензии</option>
                                        <option value="Declined">Рецензия готова</option>
                                        <option value="Completed">Требуются правки</option>
                                    </select>
                                    <button type="submit" class="search-btn">Поиск</button>
                                    <button type="button" id="resetSearch" class="reset-btn">Сбросить</button>
                                </div>
                            </form>
                        </div>
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