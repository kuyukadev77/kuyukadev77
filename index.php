<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$empresas_destaque = get_empresas_destaque($pdo);
$categorias = get_categorias($pdo);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuxila - Encontre empresas em Angola</title>
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
                    <li><a href="#quem-somos" class="hover:underline">Quem Somos</a></li>
                </ul>
            </nav>
            <div class="flex space-x-4">
                <a href="login.php" class="px-4 py-2 rounded hover:bg-blue-700">Login</a>
                <a href="cadastro.php" class="px-4 py-2 bg-white text-blue-600 rounded hover:bg-gray-200">Cadastre-se</a>
            </div>
        </div>
    </header>

    <!-- Barra de Pesquisa -->
    <section class="bg-blue-500 py-12">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold text-white mb-6">Encontre empresas em Angola</h2>
            <form action="busca.php" method="get" class="max-w-2xl mx-auto">
                <div class="flex shadow-lg rounded-lg overflow-hidden">
                    <input type="text" name="q" placeholder="Pesquise por empresa, categoria ou serviço..." 
                           class="flex-grow px-4 py-3 focus:outline-none">
                    <select name="categoria" class="border-l px-4 py-3 focus:outline-none">
                        <option value="">Todas categorias</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?= $categoria['id'] ?>"><?= $categoria['nome'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="bg-blue-700 text-white px-6 py-3 hover:bg-blue-800">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- Empresas em Destaque -->
    <section class="py-12 container mx-auto px-4">
        <h2 class="text-2xl font-bold mb-8 text-center">Empresas em Destaque</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($empresas_destaque as $empresa): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                    <div class="h-48 bg-gray-200 flex items-center justify-center">
                        <i class="fas fa-store text-5xl text-gray-400"></i>
                    </div>
                    <div class="p-4">
                        <h3 class="font-bold text-xl mb-2"><?= htmlspecialchars($empresa['nome']) ?></h3>
                        <p class="text-gray-600 mb-4"><?= htmlspecialchars(substr($empresa['descricao'], 0, 100)) ?>...</p>
                        <div class="flex justify-between items-center">
                            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded"><?= htmlspecialchars($empresa['categoria']) ?></span>
                            <a href="empresa.php?id=<?= $empresa['id'] ?>" class="text-blue-600 hover:underline">Ver detalhes</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-8">
            <a href="busca.php" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Ver todas empresas</a>
        </div>
    </section>

    <!-- Categorias -->
    <section id="categorias" class="py-12 bg-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="text-2xl font-bold mb-8 text-center">Categorias</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <?php foreach ($categorias as $categoria): ?>
                    <a href="busca.php?categoria=<?= $categoria['id'] ?>" 
                       class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow text-center">
                        <i class="<?= htmlspecialchars($categoria['icone']) ?> text-3xl mb-2 text-blue-500"></i>
                        <h3 class="font-medium"><?= htmlspecialchars($categoria['nome']) ?></h3>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Adicionar esta seção após as categorias -->
<section class="py-12 bg-gradient-to-r from-purple-50 to-blue-50">
    <div class="container mx-auto px-4">
        <h2 class="text-2xl font-bold mb-8 text-center">Promoções em Destaque</h2>
        
        <?php
        $promocoes = get_promocoes_recentes($pdo, 4);
        ?>
        
        <?php if (count($promocoes) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($promocoes as $promocao): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                        <?php if ($promocao['imagem']): ?>
                            <div class="h-48 overflow-hidden">
                                <img src="uploads/promocoes/<?= htmlspecialchars($promocao['imagem']) ?>" alt="<?= htmlspecialchars($promocao['titulo']) ?>" class="w-full h-full object-cover">
                            </div>
                        <?php else: ?>
                            <div class="h-48 bg-gradient-to-r from-purple-100 to-blue-100 flex items-center justify-center">
                                <i class="fas fa-tag text-5xl text-purple-400"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="p-4">
                            <div class="flex justify-between items-start">
                                <h3 class="font-bold text-lg mb-2"><?= htmlspecialchars($promocao['titulo']) ?></h3>
                                <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded">
                                    <?= date('d/m', strtotime($promocao['data_inicio'])) ?>-<?= date('d/m', strtotime($promocao['data_fim'])) ?>
                                </span>
                            </div>
                            
                            <p class="text-gray-600 mb-3"><?= htmlspecialchars(substr($promocao['descricao'], 0, 100)) ?>...</p>
                            
                            <div class="flex justify-between items-center">
                                <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded"><?= htmlspecialchars($promocao['empresa_categoria']) ?></span>
                                <a href="empresa.php?id=<?= $promocao['empresa_id'] ?>" class="text-blue-600 hover:underline">Ver empresa</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-8">
                <a href="busca.php?tipo=promocoes" class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700">Ver todas promoções</a>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-sm p-8 text-center">
                <i class="fas fa-tag text-5xl text-gray-400 mb-4"></i>
                <h3 class="text-xl font-bold mb-2">Nenhuma promoção no momento</h3>
                <p class="text-gray-600 mb-4">Volte mais tarde para conferir as melhores ofertas.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

    <!-- Mapa -->
    <section id="mapa" class="py-12">
        <div class="container mx-auto px-4">
            <h2 class="text-2xl font-bold mb-8 text-center">Encontre empresas no mapa</h2>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div id="map" class="h-96 w-full"></div>
            </div>
        </div>
    </section>

    <!-- Sobre -->
    <section id="sobre" class="py-12 bg-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="text-2xl font-bold mb-8 text-center">Sobre o Kuxila</h2>
            <div class="max-w-3xl mx-auto text-center">
                <p class="text-lg mb-6">
                    O Kuxila é uma plataforma digital que conecta consumidores a empresas em Angola, facilitando a descoberta
                    de produtos e serviços próximos a você.
                </p>
                <p class="text-lg mb-6">
                    Nosso objetivo é promover o comércio local, aumentando a visibilidade das empresas e ajudando os
                    consumidores a encontrar exatamente o que precisam.
                </p>
                <div class="flex justify-center space-x-4">
                    <a href="#quem-somos" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Saiba mais</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Quem Somos -->
    <section id="quem-somos" class="py-12">
        <div class="container mx-auto px-4">
            <h2 class="text-2xl font-bold mb-8 text-center">Quem Somos</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-lg shadow-sm text-center">
                    <div class="h-24 w-24 mx-auto mb-4 rounded-full bg-blue-100 flex items-center justify-center">
                        <i class="fas fa-lightbulb text-3xl text-blue-500"></i>
                    </div>
                    <h3 class="font-bold text-xl mb-2">Missão</h3>
                    <p class="text-gray-600">
                        Conectar empresas e consumidores em Angola, facilitando a descoberta de produtos e serviços locais.
                    </p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm text-center">
                    <div class="h-24 w-24 mx-auto mb-4 rounded-full bg-blue-100 flex items-center justify-center">
                        <i class="fas fa-eye text-3xl text-blue-500"></i>
                    </div>
                    <h3 class="font-bold text-xl mb-2">Visão</h3>
                    <p class="text-gray-600">
                        Ser a principal plataforma de referência para busca de empresas e serviços em Angola.
                    </p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm text-center">
                    <div class="h-24 w-24 mx-auto mb-4 rounded-full bg-blue-100 flex items-center justify-center">
                        <i class="fas fa-hand-holding-heart text-3xl text-blue-500"></i>
                    </div>
                    <h3 class="font-bold text-xl mb-2">Valores</h3>
                    <p class="text-gray-600">
                        Inovação, transparência, compromisso com o comércio local e satisfação dos nossos usuários.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter -->
    <section class="py-12 bg-blue-600 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-2xl font-bold mb-4">Receba as melhores ofertas</h2>
            <p class="mb-6 max-w-2xl mx-auto">Inscreva-se na nossa newsletter para receber atualizações e promoções das melhores empresas.</p>
            <form class="max-w-md mx-auto flex">
                <input type="email" placeholder="Seu e-mail" class="flex-grow px-4 py-3 rounded-l focus:outline-none text-gray-800">
                <button type="submit" class="bg-blue-800 px-6 py-3 rounded-r hover:bg-blue-900">Inscrever</button>
            </form>
        </div>
    </section>

    <!-- Rodapé -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
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
                        <li><a href="#mapa" class="hover:text-blue-300">Mapa</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Empresas</h4>
                    <ul class="space-y-2">
                        <li><a href="cadastro-empresa.php" class="hover:text-blue-300">Cadastre sua empresa</a></li>
                        <li><a href="login.php" class="hover:text-blue-300">Área da empresa</a></li>
                        <li><a href="#" class="hover:text-blue-300">Planos</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Contato</h4>
                    <ul class="space-y-2">
                        <li><i class="fas fa-envelope mr-2"></i> contato@kuxila.com</li>
                        <li><i class="fas fa-phone mr-2"></i> +244 123 456 789</li>
                        <li class="flex space-x-4 mt-4">
                            <a href="#" class="text-xl hover:text-blue-300"><i class="fab fa-facebook"></i></a>
                            <a href="#" class="text-xl hover:text-blue-300"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="text-xl hover:text-blue-300"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="text-xl hover:text-blue-300"><i class="fab fa-linkedin"></i></a>
                        </li>
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
            // Coordenadas de Angola (centro do mapa)
            const angola = { lat: -12.3500, lng: 17.3500 };
            
            // Criar o mapa
            const map = new google.maps.Map(document.getElementById("map"), {
                zoom: 6,
                center: angola,
            });
            
            // Aqui você pode adicionar marcadores das empresas
            // Isso seria feito via AJAX buscando as empresas do banco de dados
        }
    </script>
</body>
</html>