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
    <title>Форма рецензии</title>
    <link rel="stylesheet" href="z/pattern.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    
 
    <script src="js/header.js" defer></script>
    
    <?php if ($isLoggedIn): ?>
        <link rel="stylesheet" href="css/reviewer_review_form.css">
        <script src="js/reviewer_review_form.js" defer></script>
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
                    <h1 class="page-title">Рецензирование статьи</h1>
                    <div class="review-form-container">
                        <div id="loading-message" class="loading-message">Загрузка данных статьи...</div>
                        <div id="error-message" class="error-message" style="display: none;"></div>
                        
                        <div id="article-details" style="display: none;">
                            <h2 id="article-title" class="section-title"></h2>
                            
                            <div class="detail-row">
                                <div class="detail-label">ID статьи:</div>
                                <div id="article-id" class="detail-value"></div>
                            </div>
                            
                            <h2 class="section-title">Форма рецензии</h2>
                            
                            <form id="review-form">
                                <div class="form-group">
                                    <label for="editor_comments" class="form-label">Комментарии для редактора (обязательно):</label>
                                    <textarea id="editor_comments" name="editor_comments" class="form-textarea" required></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="author_comments" class="form-label">Комментарии для автора (опционально):</label>
                                    <textarea id="author_comments" name="author_comments" class="form-textarea"></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="recommendation" class="form-label">Рекомендация:</label>
                                    <select id="recommendation" name="recommendation" class="form-select" required>
                                        <option value="" disabled selected hidden>Выберите рекомендацию</option>
                                        <option value="accept">Принять</option>
                                        <option value="minor_revisions">Принять после незначительных доработок</option>
                                        <option value="major_revisions">Отправить на серьезную доработку</option>
                                        <option value="reject">Отклонить</option>
                                    </select>
                                </div>
                                <div id="response-message" class="message"></div>
                                <button type="submit" id="submit-btn" class="btn btn-primary">Отправить рецензию</button>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>
                </main>
                
                <aside class="right-aside">
                <?php if ($isLoggedIn): ?>
                    <nav class="side-nav">
                        <h3>Действия</h3>
                        <a href="reviewer_dashboard.php">← Назад к списку</a>
                        <a href="#" id="download-manuscript">Скачать статью</a>
                        <a href="#" id="manuscript-file-link">Скачать доп.файлы</a>
                    </nav>
                </aside>
                <?php else: ?>
                        <h3>Действия</h3>
                        <a href="/">← На главную</a>
                <?php endif; ?>
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