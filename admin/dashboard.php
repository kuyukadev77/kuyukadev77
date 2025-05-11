<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Verificar se o usuário está logado e é admin
if (!is_logged_in() || get_user_type() !== 'admin') {
    redirect('../login.php');
}

// Obter estatísticas
$stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
$total_usuarios = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM empresas");
$total_empresas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM categorias");
$total_categorias = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Últimas empresas cadastradas
$stmt = $pdo->query("SELECT e.*, u.nome as usuario_nome FROM empresas e JOIN usuarios u ON e.usuario_id = u.id ORDER BY e.data_cadastro DESC LIMIT 5");
$ultimas_empresas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Kuxila</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="bg-blue-800 text-white w-64 flex-shrink-0">
            <div class="p-4 border-b border-blue-700">
                <h1 class="text-xl font-bold">Kuxila Admin</h1>
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
                        <a href="empresas.php" class="flex items-center space-x-2 px-4 py-2 hover:bg-blue-700 rounded-lg">
                            <i class="fas fa-store"></i>
                            <span>Empresas</span>
                        </a>
                    </li>
                    <li>
                        <a href="usuarios.php" class="flex items-center space-x-2 px-4 py-2 hover:bg-blue-700 rounded-lg">
                            <i class="fas fa-users"></i>
                            <span>Usuários</span>
                        </a>
                    </li>
                    <li>
                        <a href="categorias.php" class="flex items-center space-x-2 px-4 py-2 hover:bg-blue-700 rounded-lg">
                            <i class="fas fa-tags"></i>
                            <span>Categorias</span>
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
                <!-- Estatísticas -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500">Total de Usuários</p>
                                <h3 class="text-2xl font-bold"><?= $total_usuarios ?></h3>
                            </div>
                            <div class="bg-blue-100 p-3 rounded-full">
                                <i class="fas fa-users text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500">Total de Empresas</p>
                                <h3 class="text-2xl font-bold"><?= $total_empresas ?></h3>
                            </div>
                            <div class="bg-green-100 p-3 rounded-full">
                                <i class="fas fa-store text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500">Total de Categorias</p>
                                <h3 class="text-2xl font-bold"><?= $total_categorias ?></h3>
                            </div>
                            <div class="bg-purple-100 p-3 rounded-full">
                                <i class="fas fa-tags text-purple-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Últimas Empresas -->
                <div class="bg-white p-6 rounded-lg shadow-sm mb-8">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold">Últimas Empresas Cadastradas</h3>
                        <a href="empresas.php" class="text-blue-600 hover:underline">Ver todas</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proprietário</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($ultimas_empresas as $empresa): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($empresa['nome']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($empresa['categoria']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($empresa['usuario_nome']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?= date('d/m/Y', strtotime($empresa['data_cadastro'])) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a href="empresa-detalhes.php?id=<?= $empresa['id'] ?>" class="text-blue-600 hover:text-blue-900 mr-3">Ver</a>
                                            <a href="empresa-editar.php?id=<?= $empresa['id'] ?>" class="text-yellow-600 hover:text-yellow-900 mr-3">Editar</a>
                                            <a href="empresa-excluir.php?id=<?= $empresa['id'] ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Tem certeza que deseja excluir esta empresa?')">Excluir</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Gráficos (seriam implementados com Chart.js) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <h3 class="text-lg font-semibold mb-4">Empresas por Categoria</h3>
                        <div class="h-64">
                            <!-- Espaço para gráfico -->
                            <div class="flex items-center justify-center h-full bg-gray-100 rounded">
                                <p class="text-gray-500">Gráfico de Empresas por Categoria</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <h3 class="text-lg font-semibold mb-4">Cadastros Recentes</h3>
                        <div class="h-64">
                            <!-- Espaço para gráfico -->
                            <div class="flex items-center justify-center h-full bg-gray-100 rounded">
                                <p class="text-gray-500">Gráfico de Cadastros Recentes</p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>