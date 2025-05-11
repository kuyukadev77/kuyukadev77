<?php
require_once __DIR__ . '/../includes/auth/social-auth.php';

$provider = new League\OAuth2\Client\Provider\Facebook(getFacebookAuthConfig());

if (!isset($_GET['code'])) {
    // Redirecionar para o provedor de autenticação
    $authUrl = $provider->getAuthorizationUrl([
        'scope' => ['email', 'public_profile']
    ]);
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: ' . $authUrl);
    exit;
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);
    exit('Estado inválido');
} else {
    try {
        // Obter token de acesso
        $token = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);
        
        // Obter informações do usuário
        $user = $provider->getResourceOwner($token);
        $userDetails = $user->toArray();
        
        // Padronizar dados
        $userData = [
            'id' => $userDetails['id'],
            'name' => $userDetails['name'] ?? null,
            'email' => $userDetails['email'] ?? null,
            'first_name' => $userDetails['first_name'] ?? null,
            'last_name' => $userDetails['last_name'] ?? null
        ];
        
        // Processar autenticação
        require_once __DIR__ . '/../../includes/db.php';
        if (handleSocialAuth('facebook', $userData, $pdo)) {
            header('Location: /');
            exit;
        } else {
            header('Location: /login.php?error=social_login');
            exit;
        }
    } catch (Exception $e) {
        exit('Erro: ' . $e->getMessage());
    }
}
?>