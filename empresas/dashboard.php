<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Verificar se o usuário está logado e é uma empresa
if (!is_logged_in() || get_user_type() !== 'empresa') {
    redirect('../login.php');
}

// Obter informações da empresa
$stmt = $pdo->prepare("SELECT * FROM empresas WHERE usuario_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$empresa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$empresa) {
    redirect('cadastro-completo.php');
}

// Obter estatísticas (simplificado)
$stmt = $pdo->prepare("SELECT COUNT(*) as visualizacoes FROM empresa_visualizacoes WHERE empresa_id = ?");
$stmt->execute([$empresa['id']]);
$visualizacoes = $stmt->fetch(PDO::FETCH_ASSOC)['visualizacoes'];
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Empresa - Kuxila</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="bg-blue-800 text-white w-64 flex-shrink-0">
            <div class="p-4 border-b border-blue-700">
                <h1 class="text-xl font-bold"><?= htmlspecialchars($empresa['nome']) ?></h1>
                <p class="text-sm text-blue-200"><?= $_SESSION['user_email'] ?></p>
            </div>
            <nav class="p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="dashboard.php" class="flex items-center space-x-2 px-4 py-2 bg-blue-700 rounded-lg">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="perfil.php" class="flex items-center space-x-2 px-4 py-2 hover:bg-blue-700 rounded-lg">
                            <i class="fas fa-store"></i>
                            <span>Perfil da Empresa</span>
                        </a>
                    </li>
                    <li>
                        <a href="editar.php" class="flex items-center space-x-2 px-4 py-2 hover:bg-blue-700 rounded-lg">
                            <i class="fas fa-edit"></i>
                            <span>Editar Informações</span>
                        </a>
                    </li>
                    <li>
                        <a href="fotos.php" class="flex items-center space-x-2 px-4 py-2 hover:bg-blue-700 rounded-lg">
                            <i class="fas fa-images"></i>
                            <span>Fotos</span>
                        </a>
                    </li>
                    <li>
                        <a href="estatisticas.php" class="flex items-center space-x-2 px-4 py-2 hover:bg-blue-700 rounded-lg">
                            <i class="fas fa-chart-line"></i>
                            <span>Estatísticas</span>
                        </a>
                    </li>
                    <li>
                        <a href="../logout.php" class="flex items-center space-x-2 px-4 py-2 hover:bg-blue-700 rounded-lg">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Sair</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        
        <!-- Conteúdo Principal -->
        <div class="flex-1 overflow-auto">
            <!-- Topbar -->
            <header class="bg-white shadow-sm p-4 flex justify-between items-center">
                <h2 class="text-xl font-semibold">Dashboard</h2>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600"><?= $_SESSION['user_nome'] ?></span>
                    <div class="h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-blue-600"></i>
                    </div>
                </div>
            </header>
            
            <!-- Conteúdo -->
            <main class="p-6">
                <!-- Bem-vindo -->
                <div class="bg-white p-6 rounded-lg shadow-sm mb-8">
                    <h2 class="text-2xl font-bold mb-2">Bem-vindo, <?= $_SESSION['user_nome'] ?>!</h2>
                    <p class="text-gray-600">Gerencie sua empresa no Kuxila e aumente sua visibilidade.</p>
                </div>
                
                <!-- Estatísticas -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500">Visualizações</p>
                                <h3 class="text-2xl font-bold"><?= $visualizacoes ?></h3>
                            </div>
                            <div class="bg-blue-100 p-3 rounded-full">
                                <i class="fas fa-eye text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500">Avaliação</p>
                                <h3 class="text-2xl font-bold">4.5</h3>
                            </div>
                            <div class="bg-yellow-100 p-3 rounded-full">
                                <i class="fas fa-star text-yellow-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500">Clientes Potenciais</p>
                                <h3 class="text-2xl font-bold">12</h3>
                            </div>
                            <div class="bg-green-100 p-3 rounded-full">
                                <i class="fas fa-users text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Ações Rápidas -->
                <div class="bg-white p-6 rounded-lg shadow-sm mb-8">
                    <h3 class="text-lg font-semibold mb-4">Ações Rápidas</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <a href="editar.php" class="bg-blue-50 p-4 rounded-lg text-center hover:bg-blue-100">
                            <i class="fas fa-edit text-blue-600 text-2xl mb-2"></i>
                            <p class="font-medium">Editar Informações</p>
                        </a>
                        <a href="fotos.php" class="bg-purple-50 p-4 rounded-lg text-center hover:bg-purple-100">
                            <i class="fas fa-images text-purple-600 text-2xl mb-2"></i>
                            <p class="font-medium">Adicionar Fotos</p>
                        </a>
                        <a href="promocoes.php" class="bg-yellow-50 p-4 rounded-lg text-center hover:bg-yellow-100">
                            <i class="fas fa-percentage text-yellow-600 text-2xl mb-2"></i>
                            <p class="font-medium">Criar Promoção</p>
                        </a>
                        <a href="perfil.php" class="bg-green-50 p-4 rounded-lg text-center hover:bg-green-100">
                            <i class="fas fa-external-link-alt text-green-600 text-2xl mb-2"></i>
                            <p class="font-medium">Ver Perfil Público</p>
                        </a>
                    </div>
                </div>
                
                <!-- Atualizações Recentes -->
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <h3 class="text-lg font-semibold mb-4">Atualizações Recentes</h3>
                    <div class="space-y-4">
                        <div class="flex items-start space-x-4 p-3 bg-blue-50 rounded-lg">
                            <div class="bg-blue-100 p-2 rounded-full">
                                <i class="fas fa-bell text-blue-600"></i>
                            </div>
                            <div>
                                <p class="font-medium">Seu perfil está 80% completo</p>
                                <p class="text-sm text-gray-600">Complete suas informações para melhorar sua visibilidade</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-4 p-3 bg-green-50 rounded-lg">
                            <div class="bg-green-100 p-2 rounded-full">
                                <i class="fas fa-users text-green-600"></i>
                            </div>
                            <div>
                                <p class="font-medium">5 novos visitantes esta semana</p>
                                <p class="text-sm text-gray-600">Seu perfil foi visualizado por 5 pessoas</p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>