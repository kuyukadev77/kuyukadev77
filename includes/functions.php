<?php
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function get_user_type() {
    return $_SESSION['user_type'] ?? null;
}

function get_empresas_destaque($pdo, $limit = 6) {
    $stmt = $pdo->prepare("SELECT * FROM empresas ORDER BY RAND() LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_categorias($pdo) {
    $stmt = $pdo->query("SELECT * FROM categorias");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>