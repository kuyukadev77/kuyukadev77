<?php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/functions.php';
require_once '../../includes/promocoes.php';

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

// Processar formulário de nova promoção
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['criar_promocao'])) {
    $titulo = sanitize($_POST['titulo']);
    $descricao = sanitize($_POST['descricao']);
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];
    $desconto = !empty($_POST['desconto']) ? (float)$_POST['desconto'] : null;
    $codigo = !empty($_POST['codigo']) ? sanitize($_POST['codigo']) : null;
    $imagem = null;
    
    // Validar datas
    if ($data_inicio > $data_fim) {
        $_SESSION['error'] = 'A data de início deve ser anterior à data de término.';
        header("Location: promocoes.php");
        exit;
    }
    
    // Processar upload de imagem
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['imagem']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $_SESSION['error'] = 'Tipo de arquivo não permitido. Use JPEG, PNG ou GIF.';
            header("Location: promocoes.php");
            exit;
        }
        
        if ($_FILES['imagem']['size'] > 5 * 1024 * 1024) {
            $_SESSION['error'] = 'O arquivo é muito grande. Tamanho máximo: 5MB.';
            header("Location: promocoes.php");
            exit;
        }
        
        // Criar diretório se não existir
        if (!file_exists('../../uploads/promocoes')) {
            mkdir('../../uploads/promocoes', 0777, true);
        }
        
        // Gerar nome único para o arquivo
        $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('promo_') . '.' . $ext;
        $destination = '../../uploads/promocoes/' . $filename;
        
        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $destination)) {
            $imagem = $filename;
        } else {
            $_SESSION['error'] = 'Erro ao salvar a imagem.';
            header("Location: promocoes.php");
            exit;
        }
    }
    
    // Criar promoção
    if (criar_promocao($pdo, $empresa['id'], $titulo, $descricao, $imagem, $data_inicio, $data_fim, $desconto, $codigo)) {
        $_SESSION['success'] = 'Promoção criada com sucesso!';
    } else {
        // Remover imagem se houve erro no banco de dados
        if ($imagem && file_exists('../../uploads/promocoes/' . $imagem)) {
            unlink('../../uploads/promocoes/' . $imagem);
        }
        $_SESSION['error'] = 'Erro ao criar promoção. Tente novamente.';
    }
    
    header("Location: promocoes.php");
    exit;
}

// Após criar uma promoção com sucesso:
$favoritos = $pdo->prepare("SELECT u.id FROM favoritos f JOIN usuarios u ON f.usuario_id = u.id WHERE f.empresa_id = ?");
$favoritos->execute([$empresa_id]);

while ($user = $favoritos->fetch(PDO::FETCH_ASSOC)) {
    criar_notificacao(
        $pdo,
        $user['id'],
        'promocao',
        'Nova promoção em ' . $empresa['nome'],
        'Confira a nova promoção: ' . $titulo,
        'empresa.php?id=' . $empresa_id
    );
}

// Processar remoção de promoção
if (isset($_GET['action']) && $_GET['action'] === 'remover' && isset($_GET['id'])) {
    $promocao_id = (int)$_GET['id'];
    
    if (remover_promocao($pdo, $promocao_id, $empresa['id'])) {
        $_SESSION['success'] = 'Promoção removida com sucesso!';
    } else {
        $_SESSION['error'] = 'Erro ao remover promoção.';
    }
    
    header("Location: promocoes.php");
    exit;
}

// Obter promoções da empresa
$promocoes = get_promocoes_empresa($pdo, $empresa['id'], false);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promoções - Kuxila</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
                <h2 class="text-xl font-semibold">Gerenciar Promoções</h2>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600"><?= $_SESSION['user_nome'] ?></span>
                    <div class="h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-blue-600"></i>
                    </div>
                </div>
            </header>
            
            <!-- Conteúdo -->
            <main class="p-6">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?= $_SESSION['error'] ?>
                        <?php unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <?= $_SESSION['success'] ?>
                        <?php unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Formulário de Nova Promoção -->
                <div class="bg-white p-6 rounded-lg shadow-sm mb-8">
                    <h3 class="text-lg font-semibold mb-4"><?= count($promocoes) > 0 ? 'Criar Nova Promoção' : 'Crie sua Primeira Promoção' ?></h3>
                    <form method="POST" enctype="multipart/form-data" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="titulo" class="block text-gray-700 mb-2">Título*</label>
                                <input type="text" id="titulo" name="titulo" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="desconto" class="block text-gray-700 mb-2">Desconto (%)</label>
                                <input type="number" id="desconto" name="desconto" min="1" max="100" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        
                        <div>
                            <label for="descricao" class="block text-gray-700 mb-2">Descrição*</label>
                            <textarea id="descricao" name="descricao" rows="3" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="data_inicio" class="block text-gray-700 mb-2">Data de Início*</label>
                                <input type="text" id="data_inicio" name="data_inicio" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500 datepicker">
                            </div>
                            <div>
                                <label for="data_fim" class="block text-gray-700 mb-2">Data de Término*</label>
                                <input type="text" id="data_fim" name="data_fim" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500 datepicker">
                            </div>
                        </div>
                        
                        <div>
                            <label for="codigo" class="block text-gray-700 mb-2">Código Promocional (opcional)</label>
                            <input type="text" id="codigo" name="codigo" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label for="imagem" class="block text-gray-700 mb-2">Imagem (opcional)</label>
                            <input type="file" id="imagem" name="imagem" accept="image/jpeg,image/png,image/gif" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="text-sm text-gray-500 mt-1">Formatos aceitos: JPEG, PNG, GIF. Tamanho máximo: 5MB.</p>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" name="criar_promocao" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                Criar Promoção
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Lista de Promoções -->
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <h3 class="text-lg font-semibold mb-4">Suas Promoções</h3>
                    
                    <?php if (count($promocoes) > 0): ?>
                        <div class="divide-y">
                            <?php foreach ($promocoes as $promocao): ?>
                                <div class="py-4 flex flex-col md:flex-row gap-4">
                                    <?php if ($promocao['imagem']): ?>
                                        <div class="md:w-1/4">
                                            <img src="../../uploads/promocoes/<?= htmlspecialchars($promocao['imagem']) ?>" alt="<?= htmlspecialchars($promocao['titulo']) ?>" class="w-full h-32 object-cover rounded-lg">
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="<?= $promocao['imagem'] ? 'md:w-3/4' : 'w-full' ?>">
                                        <div class="flex justify-between items-start">
                                            <h4 class="font-bold text-lg"><?= htmlspecialchars($promocao['titulo']) ?></h4>
                                            <span class="text-sm <?= $promocao['data_fim'] >= date('Y-m-d') ? 'text-green-600' : 'text-gray-500' ?>">
                                                <?= date('d/m/Y', strtotime($promocao['data_inicio'])) ?> - <?= date('d/m/Y', strtotime($promocao['data_fim'])) ?>
                                            </span>
                                        </div>
                                        
                                        <?php if ($promocao['desconto']): ?>
                                            <span class="inline-block bg-red-100 text-red-800 text-sm px-2 py-1 rounded mb-2">
                                                <?= $promocao['desconto'] ?>% OFF
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if ($promocao['codigo_promocional']): ?>
                                            <span class="inline-block bg-blue-100 text-blue-800 text-sm px-2 py-1 rounded mb-2">
                                                Código: <?= htmlspecialchars($promocao['codigo_promocional']) ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <p class="text-gray-600 mb-2"><?= nl2br(htmlspecialchars($promocao['descricao'])) ?></p>
                                        
                                        <div class="flex justify-end">
                                            <a href="promocoes.php?action=remover&id=<?= $promocao['id'] ?>" class="text-red-600 hover:text-red-800 mr-3" onclick="return confirm('Tem certeza que deseja remover esta promoção?')">
                                                <i class="fas fa-trash mr-1"></i> Remover
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-8">Nenhuma promoção criada ainda.</p>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>
    <script>
        // Inicializar datepickers
        flatpickr('.datepicker', {
            dateFormat: 'Y-m-d',
            locale: 'pt',
            minDate: 'today'
        });
        
        // Validar data fim > data início
        document.getElementById('data_inicio').addEventListener('change', function() {
            const dataFim = document.getElementById('data_fim');
            dataFim._flatpickr.set('minDate', this.value);
        });
    </script>
</body>
</html>