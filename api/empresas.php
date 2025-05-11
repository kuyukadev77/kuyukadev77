<?php
header('Content-Type: application/json');
require_once '../includes/db.php';
require_once '../includes/functions.php';

$response = ['success' => false, 'data' => []];

try {
    $termo = isset($_GET['q']) ? sanitize($_GET['q']) : '';
    $categoria_id = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;
    $lat = isset($_GET['lat']) ? (float)$_GET['lat'] : null;
    $lng = isset($_GET['lng']) ? (float)$_GET['lng'] : null;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    
    $sql = "SELECT id, nome, categoria, endereco, latitude, longitude FROM empresas WHERE 1=1";
    $params = [];
    
    if (!empty($termo)) {
        $sql .= " AND (nome LIKE ? OR descricao LIKE ? OR categoria LIKE ?)";
        $like_term = "%$termo%";
        array_push($params, $like_term, $like_term, $like_term);
    }
    
    if ($categoria_id > 0) {
        $sql .= " AND categoria_id = ?";
        $params[] = $categoria_id;
    }
    
    // Ordenação por proximidade se coordenadas forem fornecidas
    if ($lat && $lng) {
        $sql .= " ORDER BY SQRT(POW(69.1 * (latitude - ?), 2) + POW(69.1 * (? - longitude) * COS(latitude / 57.3), 2)) ASC";
        array_push($params, $lat, $lng);
    } else {
        $sql .= " ORDER BY nome ASC";
    }
    
    $sql .= " LIMIT ?";
    $params[] = $limit;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $empresas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response['success'] = true;
    $response['data'] = $empresas;
} catch (PDOException $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>