<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (!isset($_GET['id'])) {
    redirect('index.php');
}

$empresa_id = (int)$_GET['id'];

// Registrar visualização
if (!isset($_SESSION['empresa_viewed_'.$empresa_id])) {
    $stmt = $pdo->prepare("INSERT INTO empresa_visualizacoes (empresa_id, data) VALUES (?, NOW())");
    $stmt->execute([$empresa_id]);
    $_SESSION['empresa_viewed_'.$empresa_id] = true;
}

// Obter informações da empresa
$stmt = $pdo->prepare("SELECT e.*, u.nome as proprietario 
                      FROM empresas e 
                      JOIN usuarios u ON e.usuario_id = u.id 
                      WHERE e.id = ?");
$stmt->execute([$empresa_id]);
$empresa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$empresa) {
    redirect('index.php');
}

// Obter visualizações
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM empresa_visualizacoes WHERE empresa_id = ?");
$stmt->execute([$empresa_id]);
$visualizacoes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Obter avaliações (simulado)
$avaliacao_media = 4.5;
$total_avaliacoes = 12;
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($empresa['nome']) ?> - Kuxila</title>
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
        <!-- Substituir o div com bg-gray-200 por: -->
<div class="h-48 bg-gray-200 flex items-center justify-center relative overflow-hidden">
    <?php
    $imagens = get_imagens_empresa($pdo, $empresa['id']);
    $imagem_principal = get_imagem_principal($pdo, $empresa['id']);
    ?>
    
    <?php if ($imagem_principal): ?>
        <img src="uploads/<?= htmlspecialchars($imagem_principal) ?>" alt="<?= htmlspecialchars($empresa['nome']) ?>" class="w-full h-full object-cover">
    <?php elseif (count($imagens) > 0): ?>
        <img src="uploads/<?= htmlspecialchars($imagens[0]['caminho']) ?>" alt="<?= htmlspecialchars($empresa['nome']) ?>" class="w-full h-full object-cover">
    <?php else: ?>
        <i class="fas fa-store text-6xl text-gray-400"></i>
    <?php endif; ?>
</div>
    </header>

    <!-- Adicionar este botão próximo ao título da empresa -->
<div class="flex items-center space-x-4 mt-4">
    <?php if (is_logged_in()): ?>
        <?php $is_favorito = is_favorito($pdo, $_SESSION['user_id'], $empresa['id']); ?>
        <button id="favorito-btn" onclick="toggleFavorito(<?= $empresa['id'] ?>)" class="flex items-center px-4 py-2 <?= $is_favorito ? 'bg-yellow-500 text-white' : 'bg-gray-200 text-gray-800' ?> rounded-lg hover:bg-yellow-600">
            <i class="fas fa-star mr-2"></i>
            <span id="favorito-text"><?= $is_favorito ? 'Nos favoritos' : 'Adicionar aos favoritos' ?></span>
        </button>
    <?php else: ?>
        <a href="login.php" class="flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
            <i class="fas fa-star mr-2"></i>
            <span>Faça login para favoritar</span>
        </a>
    <?php endif; ?>
</div>

<script>
    function toggleFavorito(empresaId) {
        const btn = document.getElementById('favorito-btn');
        const text = document.getElementById('favorito-text');
        const isFavorito = btn.classList.contains('bg-yellow-500');
        
        fetch('toggle_favorito.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `empresa_id=${empresaId}&action=${isFavorito ? 'remove' : 'add'}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (isFavorito) {
                    btn.classList.remove('bg-yellow-500', 'text-white');
                    btn.classList.add('bg-gray-200', 'text-gray-800');
                    text.textContent = 'Adicionar aos favoritos';
                } else {
                    btn.classList.remove('bg-gray-200', 'text-gray-800');
                    btn.classList.add('bg-yellow-500', 'text-white');
                    text.textContent = 'Nos favoritos';
                }
            }
        });
    }
</script>

    <!-- Perfil da Empresa -->
    <main class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <!-- Cabeçalho do Perfil -->
            <div class="relative">
                <div class="h-48 bg-gray-200 flex items-center justify-center">
                    <i class="fas fa-store text-6xl text-gray-400"></i>
                </div>
                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent h-32"></div>
                <div class="absolute bottom-4 left-4">
                    <h1 class="text-3xl font-bold text-white"><?= htmlspecialchars($empresa['nome']) ?></h1>
                    <div class="flex items-center mt-2">
                        <div class="flex text-yellow-400">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star<?= $i > $avaliacao_media ? '-half-alt' : '' ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="ml-2 text-white"><?= $avaliacao_media ?> (<?= $total_avaliacoes ?> avaliações)</span>
                    </div>
                </div>
            </div>
            
            <!-- Conteúdo Principal -->
            <div class="p-6">
                <div class="flex flex-col md:flex-row gap-6">
                    <!-- Informações Principais -->
                    <div class="md:w-2/3">
                        <h2 class="text-2xl font-bold mb-4">Sobre a Empresa</h2>
                        <p class="text-gray-700 mb-6"><?= nl2br(htmlspecialchars($empresa['descricao'])) ?></p>
                        
                        <h3 class="text-xl font-semibold mb-3">Detalhes</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                            <div>
                                <p class="text-gray-500">Categoria</p>
                                <p class="font-medium"><?= htmlspecialchars($empresa['categoria']) ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500">Proprietário</p>
                                <p class="font-medium"><?= htmlspecialchars($empresa['proprietario']) ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500">Telefone</p>
                                <p class="font-medium"><?= htmlspecialchars($empresa['telefone']) ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500">E-mail</p>
                                <p class="font-medium"><?= htmlspecialchars($empresa['email']) ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500">Website</p>
                                <p class="font-medium">
                                    <?php if ($empresa['website']): ?>
                                        <a href="<?= htmlspecialchars($empresa['website']) ?>" target="_blank" class="text-blue-600 hover:underline">Visitar site</a>
                                    <?php else: ?>
                                        Não informado
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-500">Horário de Funcionamento</p>
                                <p class="font-medium"><?= $empresa['horario_funcionamento'] ? htmlspecialchars($empresa['horario_funcionamento']) : 'Não informado' ?></p>
                            </div>
                        </div>
                        
                        <!-- Avaliações -->
                        <h3 class="text-xl font-semibold mb-3">Avaliações</h3>
                        <div class="bg-gray-50 p-4 rounded-lg mb-6">
                            <div class="flex items-center mb-4">
                                <div class="text-4xl font-bold mr-4"><?= $avaliacao_media ?></div>
                                <div>
                                    <div class="flex text-yellow-400 mb-1">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?= $i > $avaliacao_media ? '-half-alt' : '' ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <p class="text-gray-600">Baseado em <?= $total_avaliacoes ?> avaliações</p>
                                </div>
                            </div>
                            <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Deixar Avaliação</button>
                        </div>
                    </div>
                    
                    <!-- Sidebar -->
                    <div class="md:w-1/3">
                        <!-- Mapa -->
                        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4 mb-6">
                            <h3 class="text-lg font-semibold mb-3">Localização</h3>
                            <div id="map" class="h-64 w-full mb-3"></div>
                            <p class="text-gray-700 mb-2"><i class="fas fa-map-marker-alt text-red-500 mr-2"></i> <?= htmlspecialchars($empresa['endereco']) ?></p>
                            <a href="https://www.google.com/maps?q=<?= $empresa['latitude'] ?>,<?= $empresa['longitude'] ?>" target="_blank" class="text-blue-600 hover:underline">Ver no Google Maps</a>
                        </div>
                        
                        <!-- Estatísticas -->
                        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4 mb-6">
                            <h3 class="text-lg font-semibold mb-3">Estatísticas</h3>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-gray-500">Visualizações</p>
                                    <p class="font-medium"><?= $visualizacoes ?></p>
                                </div>
                                <div>
                                    <p class="text-gray-500">No Kuxila desde</p>
                                    <p class="font-medium"><?= date('d/m/Y', strtotime($empresa['data_cadastro'])) ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Contato -->
                        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
                            <h3 class="text-lg font-semibold mb-3">Contato</h3>
                            <div class="space-y-2">
                                <?php if ($empresa['telefone']): ?>
                                    <p><i class="fas fa-phone text-blue-500 mr-2"></i> <?= htmlspecialchars($empresa['telefone']) ?></p>
                                <?php endif; ?>
                                <?php if ($empresa['email']): ?>
                                    <p><i class="fas fa-envelope text-blue-500 mr-2"></i> <?= htmlspecialchars($empresa['email']) ?></p>
                                <?php endif; ?>
                                <?php if ($empresa['website']): ?>
                                    <p><i class="fas fa-globe text-blue-500 mr-2"></i> <a href="<?= htmlspecialchars($empresa['website']) ?>" target="_blank" class="text-blue-600 hover:underline">Visitar site</a></p>
                                <?php endif; ?>
                            </div>

                            <!-- Adicionar na seção de contato ou perfil -->
<div class="mt-4">
    <p class="text-gray-700 mb-2">Compartilhar:</p>
    <div class="flex space-x-2">
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . '/empresa.php?id=' . $empresa['id']) ?>" 
           target="_blank" class="bg-blue-800 text-white p-2 rounded hover:bg-blue-900">
            <i class="fab fa-facebook-f"></i>
        </a>
        <a href="https://twitter.com/intent/tweet?url=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . '/empresa.php?id=' . $empresa['id']) ?>&text=<?= urlencode('Confira esta empresa no Kuxila: ' . $empresa['nome']) ?>" 
           target="_blank" class="bg-blue-400 text-white p-2 rounded hover:bg-blue-500">
            <i class="fab fa-twitter"></i>
        </a>
        <a href="https://wa.me/?text=<?= urlencode('Confira esta empresa no Kuxila: ' . $empresa['nome'] . ' - http://' . $_SERVER['HTTP_HOST'] . '/empresa.php?id=' . $empresa['id']) ?>" 
           target="_blank" class="bg-green-500 text-white p-2 rounded hover:bg-green-600">
            <i class="fab fa-whatsapp"></i>
        </a>
    </div>
</div>

                            <!-- Substituir o botão de contato existente por: -->
                            <form method="POST" action="mensagens.php" class="mt-4">
                             <input type="hidden" name="enviar_mensagem" value="1">
                             <input type="hidden" name="destinatario_id" value="<?= $empresa['usuario_id'] ?>">
                            <input type="hidden" name="empresa_id" value="<?= $empresa['id'] ?>">
                             <input type="hidden" name="assunto" value="Contato sobre <?= htmlspecialchars($empresa['nome']) ?>">
    
                            <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                            <i class="fas fa-comment-dots mr-2"></i> Enviar Mensagem
                            </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Adicionar esta seção após a seção de "Detalhes" -->
<section class="bg-gray-50 p-6 rounded-lg mb-6">
    <h3 class="text-xl font-semibold mb-4">Avaliações</h3>
    
    <?php
    $avaliacoes_info = get_media_avaliacoes($pdo, $empresa['id']);
    $avaliacoes = get_avaliacoes($pdo, $empresa['id']);
    ?>
    
    <div class="flex items-center mb-6">
        <div class="text-4xl font-bold mr-6"><?= number_format($avaliacoes_info['media'] ?? 0, 1) ?></div>
        <div>
            <div class="flex text-yellow-400 mb-1">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fas fa-star<?= ($i > ($avaliacoes_info['media'] ?? 0)) ? ($i - 0.5 <= ($avaliacoes_info['media'] ?? 0) ? '-half-alt' : '') : '' ?>"></i>
                <?php endfor; ?>
            </div>
            <p class="text-gray-600">Baseado em <?= $avaliacoes_info['total'] ?? 0 ?> avaliações</p>
        </div>
    </div>
    
    <?php if (is_logged_in()): ?>
        <form method="POST" action="adicionar_avaliacao.php" class="mb-8">
            <input type="hidden" name="empresa_id" value="<?= $empresa['id'] ?>">
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Sua Avaliação</label>
                <div class="flex mb-2">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <button type="button" onclick="setRating(<?= $i ?>)" class="text-2xl mr-1">
                            <i class="far fa-star" id="star-<?= $i ?>"></i>
                        </button>
                    <?php endfor; ?>
                    <input type="hidden" name="nota" id="rating-value" value="0">
                </div>
            </div>
            <div class="mb-4">
                <label for="comentario" class="block text-gray-700 mb-2">Comentário (opcional)</label>
                <textarea name="comentario" id="comentario" rows="3" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Enviar Avaliação</button>
        </form>
    <?php else: ?>
        <div class="bg-blue-50 p-4 rounded-lg mb-6">
            <p class="text-blue-800">Faça <a href="login.php" class="text-blue-600 hover:underline">login</a> para deixar sua avaliação.</p>
        </div>
    <?php endif; ?>
    
    <div class="space-y-6">
        <?php if (count($avaliacoes) > 0): ?>
            <?php foreach ($avaliacoes as $avaliacao): ?>
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <div class="flex justify-between items-start mb-2">
                        <h4 class="font-semibold"><?= htmlspecialchars($avaliacao['usuario_nome']) ?></h4>
                        <span class="text-sm text-gray-500"><?= date('d/m/Y H:i', strtotime($avaliacao['data_avaliacao'])) ?></span>
                    </div>
                    <div class="flex text-yellow-400 mb-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star<?= $i > $avaliacao['nota'] ? '-half-alt' : '' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <?php if (!empty($avaliacao['comentario'])): ?>
                        <p class="text-gray-700"><?= nl2br(htmlspecialchars($avaliacao['comentario'])) ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-gray-500 text-center py-4">Nenhuma avaliação ainda. Seja o primeiro a avaliar!</p>
        <?php endif; ?>
    </div>
</section>

<script>
    function setRating(rating) {
        // Atualizar estrelas visuais
        for (let i = 1; i <= 5; i++) {
            const star = document.getElementById(`star-${i}`);
            if (i <= rating) {
                star.classList.remove('far');
                star.classList.add('fas');
            } else {
                star.classList.remove('fas');
                star.classList.add('far');
            }
        }
        
        // Definir valor oculto
        document.getElementById('rating-value').value = rating;
    }
</script>

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
    <script src="https://maps.googleapis.com/maps/api/js?key=SUA_CHAVE_API&callback=initMap" async defer></script>
    <script>
        function initMap() {
            // Coordenadas da empresa
            const empresaLocation = { 
                lat: <?= $empresa['latitude'] ?: -12.3500 ?>, 
                lng: <?= $empresa['longitude'] ?: 17.3500 ?> 
            };
            
            // Criar o mapa
            const map = new google.maps.Map(document.getElementById("map"), {
                zoom: 15,
                center: empresaLocation,
            });
            
            // Adicionar marcador
            new google.maps.Marker({
                position: empresaLocation,
                map: map,
                title: "<?= addslashes($empresa['nome']) ?>"
            });
        }
    </script>
</body>
</html>