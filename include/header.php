<?php
    require_once 'db.php';
    $current_page = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $current_page = str_replace('/index.php', '/', $current_page); 

    $about_us_pages = [
        'design.php',
        'contact.php'
    ];

    $is_about_us_parent_active = false;
    foreach ($about_us_pages as $page){
        if (strpos($current_page, $page) !== false){
            $is_about_us_parent_active = true;
            break;
        }
    }
?>
   
   <div class="header">
        <div class="logo">
            <h1 class="logo-title"><a href="/" >Вестник Башкирского университета</a></h1>
            <h3 class="logo-defn">Научный журнал</h3>
        </div> 
    </div>

    <nav class="subheader" id="subheader">
        <ul class="nav-list">
            <li>
            <a href="javascript:void(0);" class="icon" >&#9776;</a>
            </li>
            <li>
                <a href="/index.php" class="<?= ($current_page == '/' || $current_page == '/index.php') ? 'active-nav' : '' ?>">Главная</a>
            </li>
            <li>
                <?php
                    // Динамические данные из БД
                    $stmt = $pdo->query("SELECT * FROM issues WHERE status='published' ORDER BY year DESC, issue_number DESC LIMIT 1");
                    $latestIssue = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    $issue_link = $latestIssue ? "issue.php?id={$latestIssue['id']}" : "issue.php";
                    $current_path = parse_url($current_page, PHP_URL_PATH);
                    $is_active = rtrim($current_path, '/') === '/issue.php';

                    echo "<a href='{$issue_link}' class='" . ($is_active ? 'active-nav' : '') . "'>Текущий выпуск</a>";
                ?>    
            </li>
            <li>
                <a href="/archive.php" class="<?= strpos($current_page, 'archive.php') !== false ? 'active-nav' : '' ?>">Архивы</a>
            </li>
            <li class="navigation">
                <a href="/information_for_users.php" class="<?= strpos($current_page, 'information_for_users.php') !== false ? 'active-nav' : '' ?>">Информация</a>
            </li>
            <li class="navigation <?= $is_about_us_parent_active ? 'active-nav' : '' ?>">
                <a href="#" class="link">О нас &bigtriangledown;</a>
                <ul class="submenu" >
                    <li><a href="/design.php" class="<?= strpos($current_page, 'design.php') !== false ? 'active-nav' : '' ?>">О журнале</a></li>
                    <li><a href="/contact.php" class="<?= strpos($current_page, 'contact.php') !== false ? 'active-nav' : '' ?>">Контакты</a></li>
                </ul>
            </li>
        </ul>
    </nav>   