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

    <link rel="stylesheet" href="css/design.css">
    <!-- <link rel="stylesheet" href="css/index.css"> -->
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
                <!-- Основной контент -->
<main class="main-content">
    <article>
        <section class="info-section requirements-section">
            <h2><i class="fas fa-file-alt"></i> Требования к оформлению статей</h2>
            <div class="info-content">
                <div class="requirement-block">
                    <h3><i class="fas fa-file-word"></i> Общие требования</h3>
                    <ul class="requirements-list">
                        <li>Формат: Microsoft Word (соответствие актуальному макету с сайта)</li>
                        <li>При несоответствии требований - отклонение с формулировкой "Не соответствие требованиям"</li>
                        <li>Количество авторов: не более 5 человек</li>
                        <li>Объем: 6-12 страниц (превышение рассматривается редколлегией индивидуально)</li>
                    </ul>
                </div>

                <div class="requirement-block">
                    <h3><i class="fas fa-font"></i> Текст статьи</h3>
                    <ul class="requirements-list">
                        <li>Формат А4: поля по 2 см, верхнее 2.5 см</li>
                        <li>Шрифт: Times New Roman, 12 кегль</li>
                        <li>Выравнивание: по ширине</li>
                        <li>Абзацный отступ: 0.63 см</li>
                        <li>Перенос слов: автоматический</li>
                        <li>Запрещены лишние пробелы между словами</li>
                        <li>Использовать неразрывный пробел для единиц измерения, инициалов</li>
                        <li>Различать дефис (-) и тире (–)</li>
                        <li>Знак "×" не заменять буквой "х"</li>
                    </ul>
                </div>

                <div class="requirement-block">
                    <h3><i class="fas fa-image"></i> Рисунки</h3>
                    <ul class="requirements-list">
                        <li>Обязательное название/описание (кегль 10)</li>
                        <li>Связь с текстом и упоминание в нем (рис. 1 и т.д.)</li>
                        <li>Высокое качество и информативность</li>
                        <li>Цвет: оттенки серого</li>
                        <li>Составные рисунки:
                            <ul>
                                <li>Части обозначаются русскими строчными буквами курсивом (а, б, в)</li>
                                <li>Номера деталей - арабскими цифрами (1, 2, 3)</li>
                            </ul>
                        </li>
                    </ul>
                </div>

                <div class="requirement-block">
                    <h3><i class="fas fa-table"></i> Таблицы</h3>
                    <ul class="requirements-list">
                        <li>Обязательное название/описание (кегль 10, полужирный)</li>
                        <li>Связь с текстом и упоминание (табл. 1, табл. 2)</li>
                        <li>Единообразное оформление ячеек:
                            <ul>
                                <li>Одинаковый шрифт</li>
                                <li>Кегль 10</li>
                                <li>Выравнивание текста</li>
                            </ul>
                        </li>
                        <li>Границы: нижняя и боковые - прозрачные</li>
                    </ul>
                </div>

                <div class="requirement-block">
                    <h3><i class="fas fa-square-root-alt"></i> Формулы</h3>
                    <ul class="requirements-list">
                        <li>Положение: по центру</li>
                        <li>Нумерация: по правому краю (только для формул, на которые есть ссылки)</li>
                        <li>Набор:
                            <ul>
                                <li>Простые - текст</li>
                                <li>Сложные - Equation Editor</li>
                            </ul>
                        </li>
                        <li>Размеры:
                            <ul>
                                <li>Обычный: 12 пт</li>
                                <li>Крупный индекс: 7 пт</li>
                                <li>Мелкий индекс: 5 пт</li>
                                <li>Крупный символ: 18 пт</li>
                                <li>Мелкий символ: 12 пт</li>
                            </ul>
                        </li>
                        <li>Переносы:
                            <ul>
                                <li>В первую очередь: на знаках соотношений (=, ≠, <, >)</li>
                                <li>Во вторую: на знаках +, –</li>
                                <li>В последнюю: на знаке умножения (×)</li>
                                <li>Запрещены: на знаке деления</li>
                                <li>Повтор знака в начале новой строки обязателен</li>
                            </ul>
                        </li>
                    </ul>
                </div>

                <div class="requirement-block">
                    <h3><i class="fas fa-list-ol"></i> Перечисления</h3>
                    <ul class="requirements-list">
                        <li>Без "подвешенных" отступов</li>
                        <li>Стиль - согласно макету</li>
                        <li>Автоматическое форматирование не рекомендуется</li>
                    </ul>
                </div>

                <div class="important-note">
                    <h4><i class="fas fa-exclamation-triangle"></i> Ответственность</h4>
                    <p>За точность воспроизведения имен, цитат, формул ответственность несут авторы.</p>
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