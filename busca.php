<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$termo = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$categoria_id = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;

// Construir a consulta SQL
$sql = "SELECT * FROM empresas WHERE 1=1";
$params = [];

if (!empty($termo)) {
    $sql .= " AND (nome LIKE ? OR descricao LIKE ? OR categoria LIKE ?)";
    $like_term = "%$termo%";
    $params[] = $like_term;
    $params[] = $like_term;
    $params[] = $like_term;
}

if ($categoria_id > 0) {
    $sql .= " AND categoria_id = ?";
    $params[] = $categoria_id;
}

// Na parte da construção da consulta SQL:
if ($lat && $lng) {
    $sql .= " ORDER BY SQRT(POW(69.1 * (latitude - ?), 2) + POW(69.1 * (? - longitude) * COS(latitude / 57.3), 2)) ASC";
    array_push($params, $lat, $lng);
} else {
    $sql .= " ORDER BY nome ASC";
}

// Ordenação
$sql .= " ORDER BY nome ASC";

// Preparar e executar a consulta
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$empresas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Construir a consulta SQL com os novos filtros
$sql = "SELECT e.*, 
       AVG(a.nota) as media_avaliacao,
       COUNT(a.id) as total_avaliacoes
       FROM empresas e
       LEFT JOIN avaliacoes a ON e.id = a.empresa_id
       WHERE 1=1";
$params = [];

// Filtro por termo de busca
if (!empty($termo)) {
    $sql .= " AND (e.nome LIKE ? OR e.descricao LIKE ? OR e.categoria LIKE ?)";
    $like_term = "%$termo%";
    array_push($params, $like_term, $like_term, $like_term);
}

// Filtro por categoria
if ($categoria_id > 0) {
    $sql .= " AND e.categoria_id = ?";
    $params[] = $categoria_id;
}

// Filtro por localização
if (!empty($_GET['localizacao'])) {
    $sql .= " AND (e.endereco LIKE ? OR e.cidade LIKE ?)";
    $localizacao_term = "%" . sanitize($_GET['localizacao']) . "%";
    array_push($params, $localizacao_term, $localizacao_term);
}

// Filtro por avaliação mínima
if (!empty($_GET['avaliacao']) && is_numeric($_GET['avaliacao'])) {
    $sql .= " HAVING media_avaliacao >= ?";
    $params[] = (float)$_GET['avaliacao'];
}

// Filtro por aberto agora (simplificado - precisaria de tabela de horários)
if (isset($_GET['aberto_agora'])) {
    $dia_semana = strtolower(date('l'));
    $hora_atual = date('H:i:s');
    $sql .= " AND EXISTS (
        SELECT 1 FROM empresa_horarios h 
        WHERE h.empresa_id = e.id 
        AND h.dia_semana = ?
        AND h.abertura <= ? 
        AND h.fechamento >= ?
    )";
    array_push($params, $dia_semana, $hora_atual, $hora_atual);
}

// Ordenação
$ordenar = $_GET['ordenar'] ?? 'relevancia';
switch ($ordenar) {
    case 'avaliacao':
        $sql .= " ORDER BY media_avaliacao DESC";
        break;
    case 'proximidade':
        if ($lat && $lng) {
            $sql .= " ORDER BY SQRT(POW(69.1 * (e.latitude - ?), 2) + POW(69.1 * (? - e.longitude) * COS(e.latitude / 57.3), 2)) ASC";
            array_push($params, $lat, $lng);
        } else {
            $sql .= " ORDER BY e.nome ASC";
        }
        break;
    case 'novas':
        $sql .= " ORDER BY e.data_cadastro DESC";
        break;
    default:
        $sql .= " ORDER BY e.nome ASC";
        break;
}

// Executar consulta
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$empresas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obter categorias para o filtro
$categorias = get_categorias($pdo);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Busca - Kuxila</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-100">
    <!-- Cabeçalho -->
    <header class="bg-blue-600 text-white shadow-md">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <i class="fas fa-store text-2xl"></i>
                <h1 class="text-2xl font-bold">Kuxila</h1>
            </div>
            <nav>
                <ul class="flex space-x-6">
                    <li><a href="index.php" class="hover:underline">Início</a></li>
                    <li><a href="#sobre" class="hover:underline">Sobre</a></li>
                    <li><a href="#categorias" class="hover:underline">Categorias</a></li>
                    <li><a href="#mapa" class="hover:underline">Mapa</a></li>
                </ul>
            </nav>
            <div class="flex space-x-4">
                <?php if (is_logged_in()): ?>
                    <a href="logout.php" class="px-4 py-2 rounded hover:bg-blue-700">Sair</a>
                <?php else: ?>
                    <a href="login.php" class="px-4 py-2 rounded hover:bg-blue-700">Login</a>
                    <a href="cadastro.php" class="px-4 py-2 bg-white text-blue-600 rounded hover:bg-gray-200">Cadastre-se</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Barra de Pesquisa -->
    <section class="bg-blue-500 py-8">
        <!-- Atualizar o formulário de busca -->
<form action="busca.php" method="get" class="max-w-4xl mx-auto bg-white p-4 rounded-lg shadow-md">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div>
            <label for="q" class="block text-gray-700 mb-2">O que você procura?</label>
            <input type="text" id="q" name="q" value="<?= htmlspecialchars($termo) ?>" 
                   placeholder="Empresa, produto ou serviço" 
                   class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label for="categoria" class="block text-gray-700 mb-2">Categoria</label>
            <select id="categoria" name="categoria" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todas categorias</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $categoria_id ? 'selected' : '' ?>><?= htmlspecialchars($cat['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="localizacao" class="block text-gray-700 mb-2">Localização</label>
            <input type="text" id="localizacao" name="localizacao" value="<?= htmlspecialchars($_GET['localizacao'] ?? '') ?>" 
                   placeholder="Bairro, cidade ou região" 
                   class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div>
            <label class="block text-gray-700 mb-2">Avaliação mínima</label>
            <select name="avaliacao" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Qualquer</option>
                <option value="4" <?= isset($_GET['avaliacao']) && $_GET['avaliacao'] == '4' ? 'selected' : '' ?>>4+ estrelas</option>
                <option value="3" <?= isset($_GET['avaliacao']) && $_GET['avaliacao'] == '3' ? 'selected' : '' ?>>3+ estrelas</option>
                <option value="2" <?= isset($_GET['avaliacao']) && $_GET['avaliacao'] == '2' ? 'selected' : '' ?>>2+ estrelas</option>
            </select>
        </div>
        <div>
            <label class="block text-gray-700 mb-2">Ordenar por</label>
            <select name="ordenar" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="relevancia" <?= isset($_GET['ordenar']) && $_GET['ordenar'] == 'relevancia' ? 'selected' : '' ?>>Mais relevantes</option>
                <option value="avaliacao" <?= isset($_GET['ordenar']) && $_GET['ordenar'] == 'avaliacao' ? 'selected' : '' ?>>Melhor avaliadas</option>
                <option value="proximidade" <?= isset($_GET['ordenar']) && $_GET['ordenar'] == 'proximidade' ? 'selected' : '' ?>>Mais próximas</option>
                <option value="novas" <?= isset($_GET['ordenar']) && $_GET['ordenar'] == 'novas' ? 'selected' : '' ?>>Mais recentes</option>
            </select>
        </div>
        <div>
            <label class="block text-gray-700 mb-2">Aberto agora</label>
            <div class="flex items-center">
                <input type="checkbox" id="aberto_agora" name="aberto_agora" <?= isset($_GET['aberto_agora']) ? 'checked' : '' ?> class="mr-2">
                <label for="aberto_agora">Mostrar apenas</label>
            </div>
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                <i class="fas fa-search mr-2"></i> Buscar
            </button>
        </div>
    </div>
    
    <div class="text-right">
        <button type="button" id="mais-filtros-btn" class="text-blue-600 hover:underline text-sm">
            <i class="fas fa-sliders-h mr-1"></i> Mais filtros
        </button>
    </div>
    
    <div id="mais-filtros" class="hidden mt-4 pt-4 border-t">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-gray-700 mb-2">Preço</label>
                <select name="preco" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Qualquer</option>
                    <option value="1" <?= isset($_GET['preco']) && $_GET['preco'] == '1' ? 'selected' : '' ?>>$</option>
                    <option value="2" <?= isset($_GET['preco']) && $_GET['preco'] == '2' ? 'selected' : '' ?>>$$</option>
                    <option value="3" <?= isset($_GET['preco']) && $_GET['preco'] == '3' ? 'selected' : '' ?>>$$$</option>
                    <option value="4" <?= isset($_GET['preco']) && $_GET['preco'] == '4' ? 'selected' : '' ?>>$$$$</option>
                </select>
            </div>
            <div>
                <label class="block text-gray-700 mb-2">Possui Wi-Fi</label>
                <select name="wifi" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Não importa</option>
                    <option value="1" <?= isset($_GET['wifi']) && $_GET['wifi'] == '1' ? 'selected' : '' ?>>Sim</option>
                    <option value="0" <?= isset($_GET['wifi']) && $_GET['wifi'] == '0' ? 'selected' : '' ?>>Não</option>
                </select>
            </div>
            <div>
                <label class="block text-gray-700 mb-2">Estacionamento</label>
                <select name="estacionamento" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Não importa</option>
                    <option value="1" <?= isset($_GET['estacionamento']) && $_GET['estacionamento'] == '1' ? 'selected' : '' ?>>Sim</option>
                    <option value="0" <?= isset($_GET['estacionamento']) && $_GET['estacionamento'] == '0' ? 'selected' : '' ?>>Não</option>
                </select>
            </div>
        </div>
    </div>
</form>

<script>
    // Mostrar/ocultar filtros avançados
    document.getElementById('mais-filtros-btn').addEventListener('click', function() {
        document.getElementById('mais-filtros').classList.toggle('hidden');
        this.innerHTML = this.innerHTML.includes('Mais') ? 
            '<i class="fas fa-sliders-h mr-1"></i> Menos filtros' : 
            '<i class="fas fa-sliders-h mr-1"></i> Mais filtros';
    });
</script>
    </section>

    <!-- Resultados da Busca -->
    <main class="container mx-auto px-4 py-8">
        <div class="flex flex-col md:flex-row gap-6">
            <!-- Filtros -->
            <div class="md:w-1/4">
                <div class="bg-white p-4 rounded-lg shadow-sm sticky top-4">
                    <h3 class="font-bold text-lg mb-4">Filtrar</h3>
                    
                    <!-- Filtro por Localização -->
                    <div class="mb-6">
                        <h4 class="font-medium mb-2">Localização</h4>
                        <input type="text" placeholder="Digite uma localização..." 
                               class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <!-- Filtro por Categoria -->
                    <div class="mb-6">
                        <h4 class="font-medium mb-2">Categoria</h4>
                        <ul class="space-y-2">
                            <li>
                                <a href="busca.php?q=<?= urlencode($termo) ?>" 
                                   class="block px-3 py-1 hover:bg-blue-50 rounded <?= $categoria_id == 0 ? 'bg-blue-100 text-blue-800' : '' ?>">
                                    Todas categorias
                                </a>
                            </li>
                            <?php foreach ($categorias as $cat): ?>
                                <li>
                                    <a href="busca.php?q=<?= urlencode($termo) ?>&categoria=<?= $cat['id'] ?>" 
                                       class="block px-3 py-1 hover:bg-blue-50 rounded <?= $cat['id'] == $categoria_id ? 'bg-blue-100 text-blue-800' : '' ?>">
                                        <?= htmlspecialchars($cat['nome']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <!-- Filtro por Avaliação -->
                    <div class="mb-6">
                        <h4 class="font-medium mb-2">Avaliação</h4>
                        <ul class="space-y-2">
                            <li>
                                <a href="#" class="flex items-center px-3 py-1 hover:bg-blue-50 rounded">
                                    <div class="flex text-yellow-400 mr-2">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <span>4+</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="flex items-center px-3 py-1 hover:bg-blue-50 rounded">
                                    <div class="flex text-yellow-400 mr-2">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="far fa-star"></i>
                                    </div>
                                    <span>3+</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="flex items-center px-3 py-1 hover:bg-blue-50 rounded">
                                    <div class="flex text-yellow-400 mr-2">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="far fa-star"></i>
                                        <i class="far fa-star"></i>
                                    </div>
                                    <span>2+</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Lista de Resultados -->
            <div class="md:w-3/4">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">
                        <?php if (!empty($termo) || $categoria_id > 0): ?>
                            Resultados da busca
                        <?php else: ?>
                            Todas as empresas
                        <?php endif; ?>
                    </h2>
                    <div>
                        <select class="px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option>Ordenar por</option>
                            <option>Mais relevantes</option>
                            <option>Melhor avaliadas</option>
                            <option>Mais próximas</option>
                        </select>
                    </div>
                </div>
                
                <?php if (count($empresas) > 0): ?>
                    <div class="space-y-4">
                        <?php foreach ($empresas as $empresa): ?>
                            <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                                <div class="flex flex-col md:flex-row">
                                    <div class="md:w-1/4">
                                        <div class="h-full bg-gray-200 flex items-center justify-center p-6">
                                            <i class="fas fa-store text-5xl text-gray-400"></i>
                                        </div>
                                    </div>
                                    <div class="md:w-3/4 p-6">
                                        <div class="flex justify-between">
                                            <h3 class="text-xl font-bold mb-2"><?= htmlspecialchars($empresa['nome']) ?></h3>
                                            <div class="flex text-yellow-400">
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star-half-alt"></i>
                                            </div>
                                        </div>
                                        <p class="text-gray-600 mb-4"><?= htmlspecialchars(substr($empresa['descricao'], 0, 200)) ?>...</p>
                                        <div class="flex flex-wrap gap-2 mb-4">
                                            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded"><?= htmlspecialchars($empresa['categoria']) ?></span>
                                            <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded"><i class="fas fa-map-marker-alt mr-1"></i> <?= htmlspecialchars($empresa['endereco']) ?></span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <p class="text-sm text-gray-500"><i class="fas fa-phone mr-1"></i> <?= htmlspecialchars($empresa['telefone']) ?></p>
                                            </div>
                                            <a href="empresa.php?id=<?= $empresa['id'] ?>" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Ver detalhes</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-white rounded-lg shadow-sm p-8 text-center">
                        <i class="fas fa-search text-5xl text-gray-400 mb-4"></i>
                        <h3 class="text-xl font-bold mb-2">Nenhum resultado encontrado</h3>
                        <p class="text-gray-600 mb-4">Tente ajustar sua busca ou filtros para encontrar o que procura.</p>
                        <a href="index.php" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Voltar à página inicial</a>
                    </div>
                <?php endif; ?>
                
                <!-- Paginação -->
                <?php if (count($empresas) > 0): ?>
                    <div class="mt-8 flex justify-center">
                        <nav class="flex items-center space-x-2">
                            <a href="#" class="px-3 py-1 rounded border hover:bg-blue-50">&laquo;</a>
                            <a href="#" class="px-3 py-1 rounded border bg-blue-600 text-white">1</a>
                            <a href="#" class="px-3 py-1 rounded border hover:bg-blue-50">2</a>
                            <a href="#" class="px-3 py-1 rounded border hover:bg-blue-50">3</a>
                            <a href="#" class="px-3 py-1 rounded border hover:bg-blue-50">&raquo;</a>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Rodapé -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">Kuxila</h3>
                    <p>Conectando empresas e clientes em Angola.</p>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Links Rápidos</h4>
                    <ul class="space-y-2">
                        <li><a href="index.php" class="hover:text-blue-300">Início</a></li>
                        <li><a href="#sobre" class="hover:text-blue-300">Sobre</a></li>
                        <li><a href="#categorias" class="hover:text-blue-300">Categorias</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Contato</h4>
                    <ul class="space-y-2">
                        <li><i class="fas fa-envelope mr-2"></i> contato@kuxila.com</li>
                        <li><i class="fas fa-phone mr-2"></i> +244 123 456 789</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-6 text-center text-sm">
                <p>&copy; <?= date('Y') ?> Kuxila. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="assets/js/main.js"></script>
</body>
</html>