<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $senha = $_POST['senha'];
    
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($senha, $user['senha'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_type'] = $user['tipo'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_nome'] = $user['nome'];
        
        // Redirecionar conforme o tipo de usuário
        if ($user['tipo'] === 'admin') {
            redirect('admin/dashboard.php');
        } elseif ($user['tipo'] === 'empresa') {
            redirect('empresas/dashboard.php');
        } else {
            redirect('index.php');
        }
    } else {
        $error = "E-mail ou senha incorretos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Kuxila</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-blue-600">Kuxila</h1>
                <p class="text-gray-600">Acesse sua conta</p>
            </div>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 mb-2">E-mail</label>
                    <input type="email" id="email" name="email" required 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-6">
                    <label for="senha" class="block text-gray-700 mb-2">Senha</label>
                    <input type="password" id="senha" name="senha" required 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                    Entrar
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <p class="text-gray-600">Não tem uma conta? <a href="cadastro.php" class="text-blue-600 hover:underline">Cadastre-se</a></p>
                <p class="mt-2"><a href="#" class="text-blue-600 hover:underline">Esqueceu sua senha?</a></p>
            </div>
            
            <div class="mt-8 pt-6 border-t border-gray-200 text-center">
    <p class="text-gray-600">Ou entre com</p>
    <div class="flex justify-center space-x-4 mt-4">
        <a href="auth/google-login.php" class="bg-red-600 text-white p-3 rounded-full hover:bg-red-700">
            <i class="fab fa-google"></i>
        </a>
        <a href="auth/facebook-login.php" class="bg-blue-800 text-white p-3 rounded-full hover:bg-blue-900">
            <i class="fab fa-facebook-f"></i>
        </a>
    </div>
</div>
        </div>
    </div>
</body>
</html>