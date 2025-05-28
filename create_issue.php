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
    
    <link rel="stylesheet" href="css/create_issue.css">
    <script src="js/create_issue.js" defer></script>
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
                           <h1>Создание нового выпуска журнала</h1>
        
                            <div class="issue-form-container">
                                <form id="create-issue-form">
                                    <div class="form-group">
                                        <label for="volume">Том:</label>
                                        <input type="number" id="volume" name="volume" min="1" required class="form-input">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="issue_number">Номер выпуска:</label>
                                        <input type="number" id="issue_number" name="issue_number" min="1" required class="form-input">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="year">Год:</label>
                                        <input type="number" id="year" name="year" min="1900" max="<?= date('Y') + 5 ?>" required class="form-input">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="publication_date">Дата публикации (необязательно):</label>
                                        <input type="date" id="publication_date" name="publication_date" class="form-input">
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">Создать выпуск</button>
                                    
                                    <div id="form-response" class="form-response"></div>
                                </form>
                            </div>
                    </article>
                </main>
                <aside class="right-aside">
                    <nav class="side-nav">
                        <h3>Действия</h3>
                        <a href="manage_issue.php">Редактировать выпуск</a>
                    </nav>
                </aside>

            </div>
        </div>

        <footer>
           <?php require_once 'include/footer.php'; ?>
        </footer>
    </div>
</body>
</html>