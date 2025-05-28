<?php
require_once 'db.php'; // Подключение к БД

// 1. Импакт-фактор РИНЦ (можно хранить в настройках или отдельной таблице)
function getRINZImpactFactor() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT impact_factor FROM journal_metrics WHERE metric_name = 'rinz_impact_factor'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? number_format($result['impact_factor'], 2) : '0.38';
    } catch (PDOException $e) {
        error_log("Ошибка получения импакт-фактора: " . $e->getMessage());
        return '0.38'; // Значение по умолчанию
    }
}

// 2. Количество уникальных стран
function getUniqueCountriesCount() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT COUNT(DISTINCT country) as count FROM about_user");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['count'] : 0;
    } catch (PDOException $e) {
        error_log("Ошибка получения стран: " . $e->getMessage());
        return 0;
    }
}

// 3. Количество опубликованных статей
function getPublishedArticlesCount() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM articles WHERE status = 'published'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['count'] : 0;
    } catch (PDOException $e) {
        error_log("Ошибка получения статей: " . $e->getMessage());
        return 0;
    }
}

// 4. Количество верифицированных пользователей
function getVerifiedUsersCount() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE email_verified = 1");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['count'] : 0;
    } catch (PDOException $e) {
        error_log("Ошибка получения пользователей: " . $e->getMessage());
        return 0;
    }
}
?>