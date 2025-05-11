<?php
require_once '../config.php';

$usuario = verificarAutenticacao($pdo);
$response = [];

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // Listar favoritos do usuário
        $stmt = $pdo->prepare("SELECT e.* 
                              FROM empresas e
                              JOIN favoritos f ON e.id = f.empresa_id
                              WHERE f.usuario_id = ?
                              ORDER BY f.data_adicao DESC");
        $stmt->execute([$usuario['id']]);
        $favoritos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response = ['status' => 'success', 'data' => $favoritos];
        break;
        
    case 'POST':
        // Adicionar/remover favorito
        $data = json_decode(file_get_contents('php://input'), true);
        $empresa_id = $data['empresa_id'] ?? null;
        
        if (!$empresa_id) {
            http_response_code(400);
            $response = ['status' => 'error', 'message' => 'ID da empresa é obrigatório'];
            break;
        }
        
        // Verificar se já é favorito
        $stmt = $pdo->prepare("SELECT id FROM favoritos WHERE usuario_id = ? AND empresa_id = ?");
        $stmt->execute([$usuario['id'], $empresa_id]);
        
        if ($stmt->rowCount() > 0) {
            // Remover favorito
            $stmt = $pdo->prepare("DELETE FROM favoritos WHERE usuario_id = ? AND empresa_id = ?");
            $stmt->execute([$usuario['id'], $empresa_id]);
            $response = ['status' => 'success', 'action' => 'removed'];
        } else {
            // Adicionar favorito
            $stmt = $pdo->prepare("INSERT INTO favoritos (usuario_id, empresa_id) VALUES (?, ?)");
            $stmt->execute([$usuario['id'], $empresa_id]);
            $response = ['status' => 'success', 'action' => 'added'];
        }
        break;
        
    default:
        http_response_code(405);
        $response = ['status' => 'error', 'message' => 'Método não permitido'];
}

echo json_encode($response);
?>