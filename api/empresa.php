<?php
header('Content-Type: application/json');
require_once '../includes/db.php';
require_once '../includes/functions.php';

$response = ['success' => false, 'data' => []];

if (!isset($_GET['id'])) {
    $response['error'] = 'ID da empresa não fornecido';
    echo json_encode($response);
    exit;
}

$empresa_id = (int)$_GET['id'];

try {
    // Obter informações básicas da empresa
    $stmt = $pdo->prepare("SELECT e.*, u.nome as proprietario 
                          FROM empresas e 
                          JOIN usuarios u ON e.usuario_id = u.id 
                          WHERE e.id = ?");
    $stmt->execute([$empresa_id]);
    $empresa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$empresa) {
        $response['error'] = 'Empresa não encontrada';
        echo json_encode($response);
        exit;
    }
    
    // Obter visualizações
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM empresa_visualizacoes WHERE empresa_id = ?");
    $stmt->execute([$empresa_id]);
    $visualizacoes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Obter avaliações (simulado)
    $avaliacao_media = 4.5;
    $total_avaliacoes = 12;
    
    $response['success'] = true;
    $response['data'] = [
        'empresa' => $empresa,
        'visualizacoes' => $visualizacoes,
        'avaliacao' => [
            'media' => $avaliacao_media,
            'total' => $total_avaliacoes
        ]
    ];
} catch (PDOException $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>