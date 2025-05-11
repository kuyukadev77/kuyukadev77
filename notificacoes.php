<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/notificacoes.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

// Marcar notificação como lida se for visualização específica
if (isset($_GET['id'])) {
    $notificacao_id = (int)$_GET['id'];
    marcar_notificacao_como_lida($pdo, $notificacao_id, $_SESSION['user_id']);
}

// Obter todas as notificações
$notificacoes = get_notificacoes($pdo, $_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificações - Kuxila</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Cabeçalho -->
    <header class="bg-blue-600 text-white shadow-md">
        <!-- Cabeçalho padrão -->
    </header>

    <main class="container mx-auto px-4 py-8">
        <div class="bg-white p-6 rounded-lg shadow-sm">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Suas Notificações</h1>
                <span class="text-gray-600"><?= count($notificacoes) ?> itens</span>
            </div>
            
            <?php if (count($notificacoes) > 0): ?>
                <div class="divide-y">
                    <?php foreach ($notificacoes as $notif): ?>
                        <a href="<?= $notif['link'] ? 'notificacao.php?id=' . $notif['id'] . '&redirect=' . urlencode($notif['link']) : 'notificacao.php?id=' . $notif['id'] ?>" 
                           class="block py-4 px-2 hover:bg-gray-50 <?= !$notif['lida'] ? 'bg-blue-50' : '' ?>">
                            <div class="flex justify-between items-start mb-1">
                                <h4 class="font-semibold <?= !$notif['lida'] ? 'text-blue-800' : '' ?>">
                                    <?= htmlspecialchars($notif['titulo']) ?>
                                </h4>
                                <span class="text-sm text-gray-500"><?= date('d/m/Y H:i', strtotime($notif['data_criacao'])) ?></span>
                            </div>
                            <p class="text-gray-600"><?= htmlspecialchars($notif['mensagem']) ?></p>
                            <?php if ($notif['link']): ?>
                                <p class="text-sm text-blue-600 mt-1">Clique para ver mais</p>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500 text-center py-8">Nenhuma notificação encontrada.</p>
            <?php endif; ?>
        </div>
    </main>

    <!-- Rodapé -->
    <footer class="bg-gray-800 text-white py-8">
        <!-- Rodapé padrão -->
    </footer>
</body>
</html>