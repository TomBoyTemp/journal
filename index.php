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


    <link rel="stylesheet" href="css/index.css">
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
                    <article>
                        <div class="hero-section">
                            <h1>Вестник Башкирского университета</h1>
                            <p class="subtitle">Рецензируемый научный журнал ВАК, Scopus, Web of Science</p>
                            <div class="cta-buttons">
                                <a href="/submit_article.php" class="btn btn-primary"><i class="fas fa-upload"></i> Отправить статью</a>
                                <a href="/articles.php" class="btn btn-secondary"><i class="fas fa-search"></i> Поиск публикаций</a>
                            </div>
                            <div class="index-badges">
                                <span class="badge rinc">РИНЦ</span>
                                <span class="badge scopus">Scopus Q3</span>
                                <span class="badge wos">WoS</span>
                                <span class="badge vak">ВАК</span>
                            </div>
                        </div>

                    <!-- Последний выпуск -->
                    <section class="latest-issue">
                        <h2>Последний выпуск</h2>
                        <div class="issue-card">
                            <div class="issue-info">
                                <?php
                                // Динамические данные из БД
                                $stmt = $pdo->query("SELECT * FROM issues WHERE status='published' ORDER BY year DESC, issue_number DESC LIMIT 1");
                                $latestIssue = $stmt->fetch(PDO::FETCH_ASSOC);
                                
                                if ($latestIssue) {
                                    $pubDate = date('d.m.Y', strtotime($latestIssue['publication_date']));
                                    echo "<h3>Том {$latestIssue['volume']}, №{$latestIssue['issue_number']} ({$latestIssue['year']})</h3>";
                                    echo "<p class='date'><i class='far fa-calendar-alt'></i> Опубликован: $pubDate</p>";
                                    echo "<a href='issue.php?id={$latestIssue['id']}' class='btn btn-outline'>Читать выпуск</a>";
                                } else {
                                    echo "<p>Выпуски готовятся к публикации</p>";
                                }
                                ?>
                            </div>
                        </div>
                    </section>

                    <!-- Показатели журнала -->
                    <section class="metrics-section">
                        <h2>Показатели журнала</h2>
                        <div class="metrics-grid">
                            <div class="metric-card">
                                <i class="fas fa-quote-right"></i>
                                <div class="value"><?= getRINZImpactFactor() ?></div>
                                <div class="label">Импакт-фактор РИНЦ</div>
                            </div>
                            <div class="metric-card">
                                <i class="fas fa-globe-asia"></i>
                                <div class="value"><?= getUniqueCountriesCount() ?></div>
                                <div class="label">Стран-участников</div>
                            </div>
                            <div class="metric-card">
                                <i class="fas fa-newspaper"></i>
                                <div class="value"><?= getPublishedArticlesCount() ?></div>
                                <div class="label">Статей</div>
                            </div>
                            <div class="metric-card">
                                <i class="fas fa-users"></i>
                                <div class="value"><?= getVerifiedUsersCount() ?></div>
                                <div class="label">Пользователей</div>
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