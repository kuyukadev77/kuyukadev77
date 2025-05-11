<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/favoritos.php';

if (!is_logged_in()) {
    $_SESSION['error'] = 'Você precisa estar logado para acessar esta página.';
    header('Location: login.php');
    exit;
}

$favoritos = get_favoritos($pdo, $_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <!-- Cabeçalho similar às outras páginas -->
    <title>Meus Favoritos - Kuxila</title>
</head>
<body class="bg-gray-100">
    <!-- Cabeçalho similar às outras páginas -->
    
    <main class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold">Meus Favoritos</h1>
            <span class="text-gray-600"><?= count($favoritos) ?> itens</span>
        </div>
        
        <?php if (count($favoritos) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($favoritos as $empresa): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                        <div class="h-48 bg-gray-200 flex items-center justify-center">
                            <i class="fas fa-store text-5xl text-gray-400"></i>
                        </div>
                        <div class="p-4">
                            <div class="flex justify-between items-start">
                                <h3 class="font-bold text-xl mb-2"><?= htmlspecialchars($empresa['nome']) ?></h3>
                                <button onclick="toggleFavorito(<?= $empresa['id'] ?>)" class="text-yellow-500 hover:text-yellow-600">
                                    <i class="fas fa-star"></i>
                                </button>
                            </div>
                            <p class="text-gray-600 mb-4"><?= htmlspecialchars(substr($empresa['descricao'], 0, 100)) ?>...</p>
                            <div class="flex justify-between items-center">
                                <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded"><?= htmlspecialchars($empresa['categoria']) ?></span>
                                <a href="empresa.php?id=<?= $empresa['id'] ?>" class="text-blue-600 hover:underline">Ver detalhes</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-sm p-8 text-center">
                <i class="fas fa-star text-5xl text-gray-400 mb-4"></i>
                <h3 class="text-xl font-bold mb-2">Nenhum favorito ainda</h3>
                <p class="text-gray-600 mb-4">Adicione empresas aos seus favoritos para encontrá-las facilmente depois.</p>
                <a href="busca.php" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Explorar empresas</a>
            </div>
        <?php endif; ?>
    </main>
    
    <!-- Rodapé similar às outras páginas -->
    
    <script>
        function toggleFavorito(empresaId) {
            fetch('toggle_favorito.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `empresa_id=${empresaId}&action=remove`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            });
        }
    </script>
</body>
</html>