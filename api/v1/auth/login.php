<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $email = $data['email'] ?? null;
    $senha = $data['password'] ?? null;
    
    if (!$email || !$senha) {
        http_response_code(400);
        $response = ['status' => 'error', 'message' => 'E-mail e senha são obrigatórios'];
        echo json_encode($response);
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($senha, $user['senha'])) {
        // Gerar token
        $token = bin2hex(random_bytes(32));
        $expira_em = date('Y-m-d H:i:s', strtotime('+30 days'));
        
        // Salvar token
        $stmt = $pdo->prepare("INSERT INTO api_tokens (usuario_id, token, expira_em) VALUES (?, ?, ?)");
        $stmt->execute([$user['id'], $token, $expira_em]);
        
        http_response_code(200);
        $response = [
            'status' => 'success',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'nome' => $user['nome'],
                    'email' => $user['email'],
                    'tipo' => $user['tipo']
                ]
            ]
        ];
    } else {
        http_response_code(401);
        $response = ['status' => 'error', 'message' => 'Credenciais inválidas'];
    }
}

echo json_encode($response);
?>