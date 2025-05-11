<?php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/functions.php';
require_once '../../includes/imagens.php';

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

// Processar upload de imagem
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['imagem'])) {
    $is_principal = isset($_POST['is_principal']);
    
    // Verificar erros no upload
    if ($_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = 'Erro no upload da imagem.';
        header("Location: fotos.php");
        exit;
    }
    
    // Validar tipo de arquivo
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $file_type = $_FILES['imagem']['type'];
    
    if (!in_array($file_type, $allowed_types)) {
        $_SESSION['error'] = 'Tipo de arquivo não permitido. Use JPEG, PNG ou GIF.';
        header("Location: fotos.php");
        exit;
    }
    
    // Validar tamanho do arquivo (máximo 5MB)
    if ($_FILES['imagem']['size'] > 5 * 1024 * 1024) {
        $_SESSION['error'] = 'O arquivo é muito grande. Tamanho máximo: 5MB.';
        header("Location: fotos.php");
        exit;
    }
    
    // Criar diretório de uploads se não existir
    if (!file_exists('../../uploads')) {
        mkdir('../../uploads', 0777, true);
    }
    
    // Gerar nome único para o arquivo
    $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_') . '.' . $ext;
    $destination = '../../uploads/' . $filename;
    
    // Mover arquivo para o diretório de uploads
    if (move_uploaded_file($_FILES['imagem']['tmp_name'], $destination)) {
        // Adicionar ao banco de dados
        if (adicionar_imagem_empresa($pdo, $empresa['id'], $filename, $is_principal)) {
            $_SESSION['success'] = 'Imagem adicionada com sucesso!';
        } else {
            unlink($destination); // Remover arquivo se falhar no banco de dados
            $_SESSION['error'] = 'Erro ao registrar a imagem no banco de dados.';
        }
    } else {
        $_SESSION['error'] = 'Erro ao salvar a imagem.';
    }
    
    header("Location: fotos.php");
    exit;
}

// Processar ações (definir como principal ou remover)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $imagem_id = (int)$_GET['id'];
    
    if ($action === 'set-main') {
        if (definir_imagem_principal($pdo, $imagem_id, $empresa['id'])) {
            $_SESSION['success'] = 'Imagem principal definida com sucesso!';
        } else {
            $_SESSION['error'] = 'Erro ao definir imagem principal.';
        }
    } elseif ($action === 'delete') {
        if (remover_imagem($pdo, $imagem_id, $empresa['id'])) {
            $_SESSION['success'] = 'Imagem removida com sucesso!';
        } else {
            $_SESSION['error'] = 'Erro ao remover imagem.';
        }
    }
    
    header("Location: fotos.php");
    exit;
}

// Obter imagens da empresa
$imagens = get_imagens_empresa($pdo, $empresa['id']);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Fotos - Kuxila</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
                <h2 class="text-xl font-semibold">Gerenciar Fotos</h2>
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
                
                <!-- Formulário de Upload -->
                <div class="bg-white p-6 rounded-lg shadow-sm mb-8">
                    <h3 class="text-lg font-semibold mb-4">Adicionar Nova Foto</h3>
                    <form method="POST" enctype="multipart/form-data" class="space-y-4">
                        <div>
                            <label class="block text-gray-700 mb-2">Selecione uma imagem</label>
                            <input type="file" name="imagem" accept="image/jpeg,image/png,image/gif" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="text-sm text-gray-500 mt-1">Formatos aceitos: JPEG, PNG, GIF. Tamanho máximo: 5MB.</p>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" name="is_principal" id="is_principal" class="mr-2">
                            <label for="is_principal" class="text-gray-700">Definir como imagem principal</label>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Enviar Imagem</button>
                    </form>
                </div>
                
                <!-- Galeria de Imagens -->
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <h3 class="text-lg font-semibold mb-4">Suas Fotos</h3>
                    
                    <?php if (count($imagens) > 0): ?>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            <?php foreach ($imagens as $imagem): ?>
                                <div class="relative group">
                                    <img src="../../uploads/<?= htmlspecialchars($imagem['caminho']) ?>" alt="Foto da empresa" class="w-full h-48 object-cover rounded-lg">
                                    
                                    <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 flex items-center justify-center space-x-2 rounded-lg transition-opacity">
                                        <?php if (!$imagem['is_principal']): ?>
                                            <a href="fotos.php?action=set-main&id=<?= $imagem['id'] ?>" class="p-2 bg-blue-500 text-white rounded-full hover:bg-blue-600" title="Definir como principal">
                                                <i class="fas fa-star"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="p-2 bg-yellow-500 text-white rounded-full" title="Imagem principal">
                                                <i class="fas fa-star"></i>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <a href="fotos.php?action=delete&id=<?= $imagem['id'] ?>" class="p-2 bg-red-500 text-white rounded-full hover:bg-red-600" title="Remover" onclick="return confirm('Tem certeza que deseja remover esta imagem?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                    
                                    <?php if ($imagem['is_principal']): ?>
                                        <div class="absolute top-2 left-2 bg-yellow-500 text-white px-2 py-1 rounded text-xs">
                                            Principal
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-8">Nenhuma foto adicionada ainda.</p>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
</body>
</html>