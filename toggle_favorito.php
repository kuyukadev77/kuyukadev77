<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/favoritos.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'error' => 'Não autenticado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['empresa_id']) || !isset($_POST['action'])) {
    echo json_encode(['success' => false, 'error' => 'Requisição inválida']);
    exit;
}

$empresa_id = (int)$_POST['empresa_id'];
$usuario_id = $_SESSION['user_id'];
$action = $_POST['action'];

$success = false;

if ($action === 'add') {
    $success = adicionar_favorito($pdo, $usuario_id, $empresa_id);
} elseif ($action === 'remove') {
    $success = remover_favorito($pdo, $usuario_id, $empresa_id);
}

echo json_encode(['success' => $success]);
?>