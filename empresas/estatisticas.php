<?php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/functions.php';
require_once '../../includes/estatisticas.php';

if (!is_logged_in() || get_user_type() !== 'empresa') {
    header('Location: ../../login.php');
    exit;
}

// Obter informações da empresa
$stmt = $pdo->prepare("SELECT * FROM empresas WHERE usuario_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$empresa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$empresa) {
    header('Location: ../../index.php');
    exit;
}

// Definir período padrão
$periodo = isset($_GET['periodo']) ? $_GET['periodo'] : '30 days';

// Obter estatísticas
$estatisticas = get_estatisticas_periodo($pdo, $empresa['id'], $periodo);
$detalhadas = get_estatisticas_detalhadas($pdo, $empresa['id'], $periodo);

// Preparar dados para gráficos
$labels = [];
$vis_data = [];
$contato_data = [];
$website_data = [];
$favorito_data = [];

foreach ($detalhadas as $dia) {
    $labels[] = date('d/m', strtotime($dia['data']));
    $vis_data[] = $dia['visualizacoes'];
    $contato_data[] = $dia['cliques_contato'];
    $website_data[] = $dia['cliques_website'];
    $favorito_data[] = $dia['favoritado'];
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estatísticas - Kuxila</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="bg-blue-800 text-white w-64 flex-shrink-0">
            <!-- Sidebar similar ao dashboard da empresa -->
        </div>
        
        <!-- Conteúdo Principal -->
        <div class="flex-1 overflow-auto">
            <!-- Topbar -->
            <header class="bg-white shadow-sm p-4 flex justify-between items-center">
                <h2 class="text-xl font-semibold">Estatísticas</h2>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600"><?= $_SESSION['user_nome'] ?></span>
                    <div class="h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-blue-600"></i>
                    </div>
                </div>
            </header>
            
            <!-- Conteúdo -->
            <main class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold">Estatísticas da Empresa</h1>
                    <div>
                        <select id="periodo-select" class="px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="7 days" <?= $periodo === '7 days' ? 'selected' : '' ?>>Últimos 7 dias</option>
                            <option value="30 days" <?= $periodo === '30 days' ? 'selected' : '' ?>>Últimos 30 dias</option>
                            <option value="90 days" <?= $periodo === '90 days' ? 'selected' : '' ?>>Últimos 90 dias</option>
                        </select>
                    </div>
                </div>
                
                <!-- Resumo -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500">Visualizações</p>
                                <h3 class="text-2xl font-bold"><?= $estatisticas['total_visualizacoes'] ?? 0 ?></h3>
                            </div>
                            <div class="bg-blue-100 p-3 rounded-full">
                                <i class="fas fa-eye text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500">Cliques no Contato</p>
                                <h3 class="text-2xl font-bold"><?= $estatisticas['total_cliques_contato'] ?? 0 ?></h3>
                            </div>
                            <div class="bg-green-100 p-3 rounded-full">
                                <i class="fas fa-phone text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500">Cliques no Site</p>
                                <h3 class="text-2xl font-bold"><?= $estatisticas['total_cliques_website'] ?? 0 ?></h3>
                            </div>
                            <div class="bg-purple-100 p-3 rounded-full">
                                <i class="fas fa-globe text-purple-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500">Favoritado</p>
                                <h3 class="text-2xl font-bold"><?= $estatisticas['total_favoritado'] ?? 0 ?></h3>
                            </div>
                            <div class="bg-yellow-100 p-3 rounded-full">
                                <i class="fas fa-star text-yellow-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Gráficos -->
                <div class="bg-white p-6 rounded-lg shadow-sm mb-8">
                    <h3 class="text-lg font-semibold mb-4">Visualizações</h3>
                    <canvas id="visChart" height="300"></canvas>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <h3 class="text-lg font-semibold mb-4">Cliques no Contato</h3>
                        <canvas id="contatoChart" height="250"></canvas>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <h3 class="text-lg font-semibold mb-4">Cliques no Site</h3>
                        <canvas id="websiteChart" height="250"></canvas>
                    </div>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <h3 class="text-lg font-semibold mb-4">Favoritado</h3>
                    <canvas id="favoritoChart" height="250"></canvas>
                </div>
            </main>
        </div>
    </div>
    
    <script>
        // Mudar período
        document.getElementById('periodo-select').addEventListener('change', function() {
            window.location.href = `estatisticas.php?periodo=${this.value}`;
        });
        
        // Gráfico de Visualizações
        const visCtx = document.getElementById('visChart').getContext('2d');
        const visChart = new Chart(visCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    label: 'Visualizações',
                    data: <?= json_encode($vis_data) ?>,
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // Gráfico de Cliques no Contato
        const contatoCtx = document.getElementById('contatoChart').getContext('2d');
        const contatoChart = new Chart(contatoCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    label: 'Cliques no Contato',
                    data: <?= json_encode($contato_data) ?>,
                    backgroundColor: 'rgba(16, 185, 129, 0.7)'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // Gráfico de Cliques no Site
        const websiteCtx = document.getElementById('websiteChart').getContext('2d');
        const websiteChart = new Chart(websiteCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    label: 'Cliques no Site',
                    data: <?= json_encode($website_data) ?>,
                    backgroundColor: 'rgba(124, 58, 237, 0.7)'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // Gráfico de Favoritado
        const favoritoCtx = document.getElementById('favoritoChart').getContext('2d');
        const favoritoChart = new Chart(favoritoCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    label: 'Favoritado',
                    data: <?= json_encode($favorito_data) ?>,
                    backgroundColor: 'rgba(234, 179, 8, 0.7)'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>