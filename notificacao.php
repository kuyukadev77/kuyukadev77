<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/notificacoes.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: notificacoes.php');
    exit;
}

$notificacao_id = (int)$_GET['id'];

// Marcar como lida
marcar_notificacao_como_lida($pdo, $notificacao_id, $_SESSION['user_id']);

// Redirecionar se houver link
if (isset($_GET['redirect'])) {
    header('Location: ' . urldecode($_GET['redirect']));
} else {
    header('Location: notificacoes.php');
}
exit;
?>