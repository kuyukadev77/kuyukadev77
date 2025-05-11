<?php
require_once __DIR__ . '/../includes/auth/social-auth.php';

$provider = new League\OAuth2\Client\Provider\Google(getGoogleAuthConfig());

if (!isset($_GET['code'])) {
    // Redirecionar para o provedor de autenticação
    $authUrl = $provider->getAuthorizationUrl();
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
            'name' => $userDetails['name'],
            'email' => $userDetails['email'],
            'avatar' => $userDetails['picture'] ?? null,
            'first_name' => $userDetails['given_name'] ?? null,
            'last_name' => $userDetails['family_name'] ?? null
        ];
        
        // Processar autenticação
        require_once __DIR__ . '/../../includes/db.php';
        if (handleSocialAuth('google', $userData, $pdo)) {
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