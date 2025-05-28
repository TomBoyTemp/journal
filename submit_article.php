<?php
session_start();
// Проверяем, авторизован ли пользователь
$isLoggedIn = isset($_SESSION['user']);

// Если пользователь не авторизован, выводим предупреждение
if (!$isLoggedIn) {
    $warningMessage = "Для доступа к этой странице необходимо войти или зарегистрироваться.";
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
    <script src="js/header.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <?php if ($isLoggedIn): ?>
        <link rel="stylesheet" href="css/feed.css">
        <link rel="stylesheet" href="https://unpkg.com/@yaireo/tagify/dist/tagify.css">
        <script src="https://unpkg.com/@yaireo/tagify" defer></script>
        <script src="https://unpkg.com/@yaireo/tagify/dist/tagify.polyfills.min.js" defer></script>
        <script src="js/feed.js" defer></script>  
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
                <!-- Боковая панель для профиля-->
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
                    <div id="message" class="modal-message"></div> 
                    <!-- Подача статьи -->
                    <article class="submission-form-feed">
                            <h1>Подача статьи</h1>
                            
                            <form id="submissionForm-feed" enctype="multipart/form-data" action="include/submit_not_validate.php">
                                <!-- Основная информация -->
                                <div class="form-section-feed">
                                    <h2 id="link-page-feed-1">Основная информация</h2>
                                    
                                    <label for="title-main-feed" class="required">Название статьи</label>
                                    <input type="text" id="title-main-feed" name="title-main-feed" class="text-input-feed" required>
                                    
                                    <label for="abstract-main-feed" class="required">Аннотация</label>
                                    <textarea id="abstract-main-feed" class="textarea-feed" name="abstract-main-feed" required></textarea>
                                    
                                    <label for="keywords">Ключевые слова</label>
                                    <input type="text" id="keywords" name="keywords" class="tags-input" placeholder="Введите ключевые слова">
                                    <div class="keywords-hint">Начните вводить ключевое слово и нажмите Enter</div>
                                </div>

                                <!-- Информация об авторах -->
                                <div class="form-section-feed" id="authors-section-feed">
                                    <h2 id="link-page-feed-2">Информация об авторах</h2>
                                    
                                    <div class="author-block-feed" id="author-1">
                                        <label for="author-name-1" class="required">ФИО автора</label>
                                        <input type="text" id="author-name-1" name="authors[0][name]" class="text-input-feed" required>
                                        
                                        <label for="author-affiliation-1" class="required">Аффилиация</label>
                                        <input type="text" id="author-affiliation-1" class="text-input-feed" name="authors[0][affiliation]" required>
                                        
                                        <label for="author-email-1" class="required">Email</label>
                                        <input type="email" id="author-email-1" class="text-input-feed" name="authors[0][email]" required>
                                    </div>
                                    
                                    <button type="button" id="add-author">Добавить автора</button>
                                </div>

                                <!-- Файлы статьи -->
                                <div class="form-section-feed">
                                    <h2 id="link-page-feed-3">Файлы статьи</h2>
                                    
                                    <label for="manuscript" class="required">Рукопись статьи</label>
                                    <input type="file" id="manuscript" name="manuscript" accept=".doc,.docx,.pdf,.tex,.rtf" class="file-upload" required>
                                    <div class="keywords-hint">Поддерживаемые форматы: .doc, .docx, .pdf, .tex, .rtf</div>
                                    
                                    <label for="supplementary-files">Сопроводительные материалы (изображения, таблицы и др.)</label>
                                    <input type="file" id="supplementary-files" name="supplementary_files[]" accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx" multiple class="file-upload">
                                    <div class="keywords-hint">Поддерживаемые форматы: .pdf, .jpg, .jpeg, .png, .xls, .xlsx</div>
                                </div>

                                <!-- Раздел журнала -->
                                <div class="form-section-feed">
                                    <h2 id="link-page-feed-4">Раздел журнала</h2>
                                    
                                    <label for="section" class="required">Выберите рубрику</label>
                                    <select id="section" class="select-feed" name="section" required>
                                        <option disabled selected hidden value="">Выберите раздел...</option>
                                        <?php 
                                            $stmtSection = $pdo->query("SELECT id, name FROM journal_sections ORDER BY name");
                                            while ($row = $stmtSection->fetch(PDO::FETCH_ASSOC)){
                                                echo "<option value='{$row['id']}'>{$row['name']}</option>";
                                            }
                                        ?>
                                    </select>
                                </div>

                                <!-- Соглашения -->
                                <div class="form-section-feed">
                                    <h2  id="link-page-feed-5">Соглашения</h2>
                                    
                                    <input type="checkbox" id="agreement" name="license_agreement"  value="1" required>
                                    <label for="agreement" class="required">Я согласен с <a href="#" class="link-article" target="_blank">условиями публикации</a> и <a href="#" class="link-article" target="_blank">политикой журнала</a></label>
                                </div>

                                <!-- Кнопка отправки -->
                                <button type="submit" class="submit-btn-feed">Отправить статью</button>
                            </form>
                                    
                        </article>
                        <?php endif; ?>
                </main>
                <aside class="right-aside">
                    <?php if ($isLoggedIn): ?>
                    <nav class="side-nav">
                        <h3 id="nav-header">Быстрая навигация</h3>
                        <a class="nav-link-aside" href="#link-page-feed-1">Основная информация</a>
                        <a class="nav-link-aside" href="#link-page-feed-2">Информация об авторах</a>
                        <a class="nav-link-aside" href="#link-page-feed-3">Файлы статьи</a>
                        <a class="nav-link-aside" href="#link-page-feed-4">Раздел журнала</a>
                        <a class="nav-link-aside" href="#link-page-feed-5">Соглашения</a>
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