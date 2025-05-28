<?php
    session_start();
    require_once 'include/db.php';
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


    <link rel="stylesheet" href="css/reset_password.css">

    <script src="js/header.js" defer></script>
    <script src="js/reset_password.js" defer></script>
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

                <!-- Основной контент -->
                <main class="main-content">
                    <article>
                    <form id="resetPasswordForm">
                        <h1>Сброс пароля</h1>
                        <div id="messages" class="message"></div>
                        <div class="form-group">
                            <label for="new_password">Новый пароль:</label>
                            <input type="password" id="new_password" name="new_password" required>
                            <small class="alertMessage" id="newPasswordError"></small>
                        </div>
                        <div class="form-group">
                            <label for="confirm_new_password">Подтвердите новый пароль:</label>
                            <input type="password" id="confirm_new_password" name="confirm_new_password" required>
                            <small class="alertMessage" id="confirmNewPasswordError"></small>
                        </div>
                        <button type="submit" id="resetPasswordButton">Сбросить пароль</button>
                    </form>

                    </article>
                </main>
                

            </div>
        </div>

        <footer>
            <?php require_once 'include/footer.php'; ?>
        </footer>
    </div>

</body>
</html>