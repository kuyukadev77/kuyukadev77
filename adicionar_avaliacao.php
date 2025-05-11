<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (!is_logged_in()) {
    $_SESSION['error'] = 'Você precisa estar logado para avaliar.';
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['empresa_id']) || !isset($_POST['nota'])) {
    $_SESSION['error'] = 'Requisição inválida.';
    header('Location: index.php');
    exit;
}

$empresa_id = (int)$_POST['empresa_id'];
$nota = (float)$_POST['nota'];
$comentario = isset($_POST['comentario']) ? sanitize($_POST['comentario']) : null;
$usuario_id = $_SESSION['user_id'];

// Verificar se o usuário já avaliou esta empresa
$stmt = $pdo->prepare("SELECT id FROM avaliacoes WHERE empresa_id = ? AND usuario_id = ?");
$stmt->execute([$empresa_id, $usuario_id]);

if ($stmt->rowCount() > 0) {
    $_SESSION['error'] = 'Você já avaliou esta empresa.';
    header("Location: empresa.php?id=$empresa_id");
    exit;
}

// Após enviar a mensagem com sucesso:
criar_notificacao(
    $pdo,
    $destinatario_id,
    'mensagem',
    'Nova mensagem de ' . $_SESSION['user_nome'],
    substr($mensagem, 0, 100) . '...',
    'mensagens.php?aba=recebidas&id=' . $pdo->lastInsertId()
);

// Adicionar avaliação
if (adicionar_avaliacao($pdo, $empresa_id, $usuario_id, $nota, $comentario)) {
    $_SESSION['success'] = 'Avaliação enviada com sucesso!';
} else {
    $_SESSION['error'] = 'Erro ao enviar avaliação. Tente novamente.';
}

header("Location: empresa.php?id=$empresa_id");
exit;
?>