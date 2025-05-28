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
    <title>Детали рукописи | Панель редактора</title>
    <link rel="stylesheet" href="z/pattern.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="js/header.js" defer></script>
    <?php if ($isLoggedIn): ?>
        <script src="js/editors_manuscript_details.js" defer></script>
        <link rel="stylesheet" href="css/editors_manuscript_details.css">
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
                        <div id="message" class="modal-message">
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
                                <div class="detail-label">Статус:</div>
                                <div class="detail-value" id="manuscript-status"></div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">Ключевые слова:</div>
                                <div class="detail-value" id="manuscript-keywords"></div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">Аннотация:</div>
                                <div class="detail-value" id="manuscript-abstract"></div>
                            </div>
                        </div>
                        
                        <div id="reviewers-section" class="reviewers-section" style="display: none;">
                            <h2 class="section-title">Назначенные рецензенты</h2>
                            <div id="assigned-reviewers"></div>
                            
                            <h2 class="section-title">Назначить новых рецензентов</h2>
                            <div class="form-group">
                                <label class="form-label">Выберите рецензентов:</label>
                                <select id="reviewers-select" class="form-select" multiple>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Срок рецензирования:</label>
                                <input type="date" id="review-deadline" class="form-select">
                            </div>
                            
                            <button id="assign-reviewers-btn" class="btn btn-primary">Назначить рецензентов</button>
                        </div>

                        <!-- Новый раздел для отображения рецензий -->
                        <div id="reviews-section" class="reviews-section" style="display: none;">
                            <h2 class="section-title">Полученные рецензии</h2>
                            <div id="reviews-list"></div>
                        </div>

                        <!-- Новый раздел для принятия решения -->
                        <div id="decision-section" class="decision-section" style="display: none;">
                            <h2 class="section-title">Принять решение по статье</h2>
                            <form id="editor-decision-form">
                                <div class="form-group">
                                    <label for="editor_final_decision" class="form-label">Решение:</label>
                                    <select id="editor_final_decision" name="decision" class="form-select" required>
                                        <option value="" disabled hidden selected>Выберите решение</option>
                                        <option value="Accept">Принять</option>
                                        <option value="Minor Revisions">Требуются незначительные доработки</option>
                                        <option value="Major Revisions">Требуются серьезные доработки</option>
                                        <option value="Reject">Отклонить</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="decision_comments" class="form-label">Комментарии для автора:</label>
                                    <textarea id="decision_comments" name="comments" class="form-textarea" required></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Отправить решение автору</button>
                            </form>
                            <div id="decision-response"></div>
                        </div>

                    </article>
                </main>
                
                <aside class="right-aside">
                    <nav class="side-nav">
                        <h3>Действия</h3>
                        <a href="editor_dashboard.php">← Назад к списку</a>
                        <a href="#" id="download-manuscript">Скачать статью</a>
                        <a href="#" id="manuscript-file-link">Скачать доп.файлы</a>
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