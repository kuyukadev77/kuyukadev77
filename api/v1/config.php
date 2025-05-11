<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/functions.php';

$response = ['status' => 'error', 'message' => 'Ação inválida'];

// Verificar token de autenticação
function verificarAutenticacao($pdo) {
    $headers = getallheaders();
    $token = $headers['Authorization'] ?? null;
    
    if (!$token) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Token não fornecido']);
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT u.* FROM usuarios u JOIN api_tokens t ON u.id = t.usuario_id WHERE t.token = ? AND t.expira_em > NOW()");
    $stmt->execute([str_replace('Bearer ', '', $token)]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Token inválido ou expirado']);
        exit;
    }
    
    return $usuario;
}
?>