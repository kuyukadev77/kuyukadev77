<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = sanitize($_POST['nome']);
    $email = sanitize($_POST['email']);
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];
    $tipo = sanitize($_POST['tipo']);
    
    // Validações
    if (empty($nome) || empty($email) || empty($senha) || empty($confirmar_senha)) {
        $error = "Todos os campos são obrigatórios.";
    } elseif ($senha !== $confirmar_senha) {
        $error = "As senhas não coincidem.";
    } elseif (strlen($senha) < 6) {
        $error = "A senha deve ter pelo menos 6 caracteres.";
    } else {
        // Verificar se o e-mail já existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $error = "Este e-mail já está em uso.";
        } else {
            // Hash da senha
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            
            // Inserir no banco de dados
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$nome, $email, $senha_hash, $tipo])) {
                $success = "Cadastro realizado com sucesso!";
                
                // Se for empresa, redirecionar para cadastro completo
                if ($tipo === 'empresa') {
                    $_SESSION['temp_user_id'] = $pdo->lastInsertId();
                    redirect('empresas/cadastro-completo.php');
                } else {
                    // Login automático para clientes
                    $_SESSION['user_id'] = $pdo->lastInsertId();
                    $_SESSION['user_type'] = $tipo;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_nome'] = $nome;
                    redirect('index.php');
                }
            } else {
                $error = "Erro ao cadastrar. Tente novamente.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Kuxila</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-blue-600">Kuxila</h1>
                <p class="text-gray-600">Crie sua conta</p>
            </div>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?= $success ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-4">
                    <label for="nome" class="block text-gray-700 mb-2">Nome</label>
                    <input type="text" id="nome" name="nome" required 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 mb-2">E-mail</label>
                    <input type="email" id="email" name="email" required 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label for="senha" class="block text-gray-700 mb-2">Senha</label>
                    <input type="password" id="senha" name="senha" required 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label for="confirmar_senha" class="block text-gray-700 mb-2">Confirmar Senha</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" required 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 mb-2">Você é:</label>
                    <div class="flex space-x-4">
                        <label class="inline-flex items-center">
                            <input type="radio" name="tipo" value="cliente" checked class="form-radio text-blue-600">
                            <span class="ml-2">Cliente</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="tipo" value="empresa" class="form-radio text-blue-600">
                            <span class="ml-2">Empresa</span>
                        </label>
                    </div>
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                    Cadastrar
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <p class="text-gray-600">Já tem uma conta? <a href="login.php" class="text-blue-600 hover:underline">Faça login</a></p>
            </div>
            
            <div class="mt-8 pt-6 border-t border-gray-200 text-center">
                <p class="text-gray-600">Ou cadastre-se com</p>
                <div class="flex justify-center space-x-4 mt-4">
                    <button class="bg-blue-800 text-white p-2 rounded-full hover:bg-blue-900">
                        <i class="fab fa-facebook-f"></i>
                    </button>
                    <button class="bg-red-600 text-white p-2 rounded-full hover:bg-red-700">
                        <i class="fab fa-google"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>