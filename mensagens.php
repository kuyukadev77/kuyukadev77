<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/mensagens.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$aba = isset($_GET['aba']) ? $_GET['aba'] : 'recebidas';
$mensagens = [];
$titulo_aba = '';

if ($aba === 'recebidas') {
    $mensagens = get_mensagens_recebidas($pdo, $_SESSION['user_id']);
    $titulo_aba = 'Mensagens Recebidas';
} else {
    $mensagens = get_mensagens_enviadas($pdo, $_SESSION['user_id']);
    $titulo_aba = 'Mensagens Enviadas';
}

// Marcar como lida se for visualização de mensagem específica
if (isset($_GET['id'])) {
    $mensagem_id = (int)$_GET['id'];
    marcar_como_lida($pdo, $mensagem_id, $_SESSION['user_id']);
    $mensagem_detalhe = array_filter($mensagens, function($m) use ($mensagem_id) {
        return $m['id'] == $mensagem_id;
    });
    $mensagem_detalhe = reset($mensagem_detalhe);
}

// Enviar nova mensagem
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_mensagem'])) {
    $destinatario_id = (int)$_POST['destinatario_id'];
    $empresa_id = (int)$_POST['empresa_id'];
    $assunto = sanitize($_POST['assunto']);
    $mensagem = sanitize($_POST['mensagem']);
    
    if (enviar_mensagem($pdo, $_SESSION['user_id'], $destinatario_id, $empresa_id, $assunto, $mensagem)) {
        $_SESSION['success'] = 'Mensagem enviada com sucesso!';
        header('Location: mensagens.php');
        exit;
    } else {
        $_SESSION['error'] = 'Erro ao enviar mensagem. Tente novamente.';
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensagens - Kuxila</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Cabeçalho -->
    <header class="bg-blue-600 text-white shadow-md">
        <!-- Mesmo cabeçalho das outras páginas -->
    </header>

    <main class="container mx-auto px-4 py-8">
        <div class="flex flex-col md:flex-row gap-6">
            <!-- Sidebar -->
            <div class="md:w-1/4">
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <h3 class="font-bold text-lg mb-4">Caixa de Entrada</h3>
                    
                    <ul class="space-y-2">
                        <li>
                            <a href="mensagens.php?aba=recebidas" class="flex items-center justify-between px-3 py-2 <?= $aba === 'recebidas' ? 'bg-blue-100 text-blue-800' : 'hover:bg-gray-100' ?> rounded">
                                <span>Recebidas</span>
                                <?php if ($aba !== 'recebidas'): ?>
                                    <?php $nao_lidas = contar_mensagens_nao_lidas($pdo, $_SESSION['user_id']); ?>
                                    <?php if ($nao_lidas > 0): ?>
                                        <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full"><?= $nao_lidas ?></span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li>
                            <a href="mensagens.php?aba=enviadas" class="flex items-center px-3 py-2 <?= $aba === 'enviadas' ? 'bg-blue-100 text-blue-800' : 'hover:bg-gray-100' ?> rounded">
                                <span>Enviadas</span>
                            </a>
                        </li>
                        <li>
                            <button onclick="document.getElementById('nova-mensagem-modal').classList.remove('hidden')" class="w-full flex items-center px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                                <i class="fas fa-plus mr-2"></i>
                                Nova Mensagem
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Conteúdo Principal -->
            <div class="md:w-3/4">
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold"><?= $titulo_aba ?></h2>
                    </div>
                    
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
                    
                    <?php if (isset($_GET['id']) && $mensagem_detalhe): ?>
                        <!-- Visualização de mensagem -->
                        <div class="border-b pb-4 mb-4">
                            <h3 class="text-lg font-bold mb-2"><?= htmlspecialchars($mensagem_detalhe['assunto']) ?></h3>
                            <div class="flex justify-between text-sm text-gray-500 mb-4">
                                <span>
                                    <?= $aba === 'recebidas' ? 'De: ' : 'Para: ' ?>
                                    <?= htmlspecialchars($aba === 'recebidas' ? $mensagem_detalhe['remetente_nome'] : $mensagem_detalhe['destinatario_nome']) ?>
                                </span>
                                <span><?= date('d/m/Y H:i', strtotime($mensagem_detalhe['data_envio'])) ?></span>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="whitespace-pre-line"><?= htmlspecialchars($mensagem_detalhe['mensagem']) ?></p>
                            </div>
                        </div>
                        <a href="mensagens.php?aba=<?= $aba ?>" class="text-blue-600 hover:underline">&larr; Voltar para a lista</a>
                    <?php elseif (count($mensagens) > 0): ?>
                        <!-- Lista de mensagens -->
                        <div class="divide-y">
                            <?php foreach ($mensagens as $msg): ?>
                                <a href="mensagens.php?aba=<?= $aba ?>&id=<?= $msg['id'] ?>" class="block py-4 hover:bg-gray-50 px-2 <?= !$msg['lida'] && $aba === 'recebidas' ? 'bg-blue-50' : '' ?>">
                                    <div class="flex justify-between items-start mb-1">
                                        <h4 class="font-semibold <?= !$msg['lida'] && $aba === 'recebidas' ? 'text-blue-800' : '' ?>">
                                            <?= htmlspecialchars($msg['assunto']) ?>
                                        </h4>
                                        <span class="text-sm text-gray-500"><?= date('d/m/Y', strtotime($msg['data_envio'])) ?></span>
                                    </div>
                                    <p class="text-sm text-gray-600 truncate mb-1">
                                        <?= $aba === 'recebidas' ? 'De: ' : 'Para: ' ?>
                                        <?= htmlspecialchars($aba === 'recebidas' ? $msg['remetente_nome'] : $msg['destinatario_nome']) ?>
                                    </p>
                                    <p class="text-sm text-gray-500 truncate"><?= htmlspecialchars(substr($msg['mensagem'], 0, 100)) ?>...</p>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-8">Nenhuma mensagem encontrada.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Nova Mensagem -->
    <div id="nova-mensagem-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-lg">
            <div class="flex justify-between items-center border-b p-4">
                <h3 class="text-lg font-bold">Nova Mensagem</h3>
                <button onclick="document.getElementById('nova-mensagem-modal').classList.add('hidden')" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" action="mensagens.php" class="p-4">
                <input type="hidden" name="enviar_mensagem" value="1">
                <div class="mb-4">
                    <label for="empresa_id" class="block text-gray-700 mb-2">Empresa</label>
                    <select name="empresa_id" id="empresa_id" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Selecione uma empresa</option>
                        <?php 
                        $stmt = $pdo->prepare("SELECT e.id, e.nome, u.id as dono_id 
                                              FROM empresas e
                                              JOIN usuarios u ON e.usuario_id = u.id
                                              ORDER BY e.nome");
                        $stmt->execute();
                        $empresas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach ($empresas as $emp): ?>
                            <option value="<?= $emp['id'] ?>" data-dono="<?= $emp['dono_id'] ?>"><?= htmlspecialchars($emp['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="destinatario_id" id="destinatario_id">
                </div>
                <div class="mb-4">
                    <label for="assunto" class="block text-gray-700 mb-2">Assunto</label>
                    <input type="text" name="assunto" id="assunto" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label for="mensagem" class="block text-gray-700 mb-2">Mensagem</label>
                    <textarea name="mensagem" id="mensagem" rows="5" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="document.getElementById('nova-mensagem-modal').classList.add('hidden')" class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-100">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Enviar Mensagem
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Rodapé -->
    <footer class="bg-gray-800 text-white py-8">
        <!-- Rodapé similar às outras páginas -->
    </footer>

    <script>
        // Atualizar destinatário quando selecionar empresa
        document.getElementById('empresa_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            document.getElementById('destinatario_id').value = selectedOption.dataset.dono;
        });
    </script>
</body>
</html>