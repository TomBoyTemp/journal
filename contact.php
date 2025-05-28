<?php
session_start();
    require_once 'include/db.php';

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
    <script src="js/header.js" defer></script>


    <link rel="stylesheet" href="css/contact.css">
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
                    <article>
                        <section class="info-section">
                            <h2><i class="fas fa-address-book"></i> Контакты</h2>
                            <div class="info-content">
                                <div class="contact-block">
                                    <h3><i class="fas fa-map-marker-alt"></i> Адрес</h3>
                                    <p>450008, г. Уфа, ул. К. Маркса, д. 12</p>
                                    <div class="map-container">
                                        <iframe src="https://yandex.ru/map-widget/v1/?ll=55.942450%2C54.724989&z=16&l=map&pt=55.942450,54.724989,flag" 
                                                width="100%" 
                                                height="300" 
                                                frameborder="0"
                                                style="border-radius: 8px; margin-top: 15px;">
                                        </iframe>
                                    </div>
                                </div>

                                <div class="contact-block">
                                    <h3><i class="fas fa-user-tie"></i> Представитель редакции</h3>
                                    <p>
                                        <strong>Фамилия Имя Отчество</strong><br>
                                        ФГБОУ ВО «Уфимский зелёный виноградный отдел независимости»
                                    </p>
                                </div>

                                <div class="contact-block">
                                    <h3><i class="fas fa-phone"></i> Телефон</h3>
                                    <p>
                                        +7 (999) 999-99-99
                                    </p>
                                </div>

                                <div class="contact-block">
                                    <h3><i class="fas fa-envelope"></i> Электронная почта</h3>
                                    <p>
                                       example@localhost
                                    </p>
                                </div>

                                <div class="contact-block">
                                    <h3><i class="fas fa-clock"></i> Часы работы</h3>
                                    <p>
                                        Понедельник-пятница: 9:00 - 18:00<br>
                                        Суббота: 10:00 - 15:00<br>
                                        Воскресенье: выходной
                                    </p>
                                </div>
                            </div>
                        </section>
                    </article>
                </main>
                
                <aside class="right-aside">
                    <div class="sidebar-block">
                        <h3><i class="fas fa-calendar-alt"></i> График выпусков</h3>
                        <ul class="schedule">
                             <?php
                            // 1. Получаем текущий год и последний том из БД
                            $currentYear = date('Y');
                            
                            // Запрос для получения максимального (текущего) тома
                            $stmt = $pdo->query("SELECT MAX(volume) as current_volume FROM issues");
                            $currentVolume = $stmt->fetch(PDO::FETCH_ASSOC)['current_volume'] ?? 1; // 1 как fallback
                            
                            // 2. Определяем месяцы выпусков (можно хранить в настройках БД)
                            $releaseMonths = [
                                1 => 'Март',
                                2 => 'Июнь',
                                3 => 'Сентябрь',
                                4 => 'Декабрь'
                            ];
                            
                            // 3. Генерируем список выпусков на текущий год
                            foreach ($releaseMonths as $issueNumber => $month) {
                                // Проверяем, есть ли уже такой выпуск в БД
                                $stmt = $pdo->prepare("
                                    SELECT publication_date, status 
                                    FROM issues 
                                    WHERE volume = :volume AND issue_number = :issue AND year = :year
                                ");
                                $stmt->execute([
                                    ':volume' => $currentVolume,
                                    ':issue' => $issueNumber,
                                    ':year' => $currentYear
                                ]);
                                $issueData = $stmt->fetch(PDO::FETCH_ASSOC);
                                
                                $statusClass = '';
                                if ($issueData) {
                                    $statusClass = $issueData['status'] === 'published' ? 'published' : 'draft';
                                    $date = $issueData['publication_date'] 
                                        ? date('d.m.Y', strtotime($issueData['publication_date'])) 
                                        : "$month $currentYear";
                                } else {
                                    $date = "$month $currentYear";
                                }
                                
                                echo "<li class='$statusClass'>Том $currentVolume, №$issueNumber - $date</li>";
                            }
                            ?>
                        </ul>
                    </div>
                     <div class="sidebar-block">
                        <h3><i class="fas fa-link"></i> Полезные ссылки</h3>
                        <a href="#" class="useful-link"><i class="fas fa-external-link-alt"></i> Башкирский университет</a>
                        <a href="#" class="useful-link"><i class="fas fa-external-link-alt"></i> Министерство науки</a>
                        <a href="#" class="useful-link"><i class="fas fa-external-link-alt"></i> Электронная библиотека</a>
                    </div>
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