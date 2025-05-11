<?php
require_once __DIR__ . '/../../vendor/autoload.php';

function getGoogleAuthConfig() {
    return [
        'clientId'     => 'SEU_GOOGLE_CLIENT_ID',
        'clientSecret' => 'SEU_GOOGLE_CLIENT_SECRET',
        'redirectUri'  => 'http://seusite.com/auth/google-callback.php',
        'accessType'   => 'offline',
    ];
}

function getFacebookAuthConfig() {
    return [
        'clientId'     => 'SEU_FACEBOOK_APP_ID',
        'clientSecret' => 'SEU_FACEBOOK_APP_SECRET',
        'redirectUri'  => 'http://seusite.com/auth/facebook-callback.php',
        'graphApiVersion' => 'v12.0',
    ];
}

function handleSocialAuth($provider, $userDetails, $pdo) {
    // Verificar se o usuário já existe
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE provider = ? AND provider_id = ?");
    $stmt->execute([$provider, $userDetails['id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        // Verificar se o email já está cadastrado (para caso de usuário que se cadastrou manualmente)
        if (!empty($userDetails['email'])) {
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
            $stmt->execute([$userDetails['email']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Atualizar com informações do provider
                $stmt = $pdo->prepare("UPDATE usuarios SET provider = ?, provider_id = ?, avatar = ? WHERE id = ?");
                $stmt->execute([$provider, $userDetails['id'], $userDetails['avatar'] ?? null, $user['id']]);
            }
        }
        
        // Se ainda não existe, criar novo usuário
        if (!$user) {
            $nome = $userDetails['name'] ?? $userDetails['first_name'] . ' ' . $userDetails['last_name'];
            $email = $userDetails['email'] ?? null;
            $avatar = $userDetails['avatar'] ?? null;
            
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, provider, provider_id, avatar, tipo, data_criacao) VALUES (?, ?, ?, ?, ?, 'cliente', NOW())");
            $stmt->execute([$nome, $email, $provider, $userDetails['id'], $avatar]);
            
            $user_id = $pdo->lastInsertId();
            $user = [
                'id' => $user_id,
                'nome' => $nome,
                'email' => $email,
                'tipo' => 'cliente'
            ];
        }
    }
    
    // Iniciar sessão
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_nome'] = $user['nome'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_type'] = $user['tipo'];
    $_SESSION['user_avatar'] = $user['avatar'] ?? null;
    
    return true;
}
?>