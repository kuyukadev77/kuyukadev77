<?php
require_once '../config.php';

$usuario = verificarAutenticacao($pdo);
$response = [];

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $id = $_GET['id'] ?? null;
        
        if ($id) {
            // Detalhes de uma empresa específica
            $stmt = $pdo->prepare("SELECT e.*, 
                                 AVG(a.nota) as media_avaliacao,
                                 COUNT(a.id) as total_avaliacoes
                                 FROM empresas e
                                 LEFT JOIN avaliacoes a ON e.id = a.empresa_id
                                 WHERE e.id = ?");
            $stmt->execute([$id]);
            $empresa = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($empresa) {
                // Obter imagens
                $stmt = $pdo->prepare("SELECT caminho FROM empresa_imagens WHERE empresa_id = ?");
                $stmt->execute([$id]);
                $imagens = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
                
                // Obter avaliações
                $stmt = $pdo->prepare("SELECT a.*, u.nome as usuario_nome 
                                      FROM avaliacoes a
                                      JOIN usuarios u ON a.usuario_id = u.id
                                      WHERE a.empresa_id = ?
                                      ORDER BY a.data_avaliacao DESC
                                      LIMIT 5");
                $stmt->execute([$id]);
                $avaliacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $empresa['imagens'] = $imagens;
                $empresa['avaliacoes'] = $avaliacoes;
                
                $response = ['status' => 'success', 'data' => $empresa];
            } else {
                http_response_code(404);
                $response = ['status' => 'error', 'message' => 'Empresa não encontrada'];
            }
        } else {
            // Lista de empresas com filtros
            $params = [];
            $sql = "SELECT e.*, 
                   AVG(a.nota) as media_avaliacao,
                   COUNT(a.id) as total_avaliacoes
                   FROM empresas e
                   LEFT JOIN avaliacoes a ON e.id = a.empresa_id";
            
            // Filtros
            $where = [];
            if (isset($_GET['categoria'])) {
                $where[] = "e.categoria_id = ?";
                $params[] = $_GET['categoria'];
            }
            
            if (isset($_GET['localizacao'])) {
                $where[] = "(e.endereco LIKE ? OR e.cidade LIKE ?)";
                $params[] = "%" . $_GET['localizacao'] . "%";
                $params[] = "%" . $_GET['localizacao'] . "%";
            }
            
            if (isset($_GET['nome'])) {
                $where[] = "e.nome LIKE ?";
                $params[] = "%" . $_GET['nome'] . "%";
            }
            
            if (!empty($where)) {
                $sql .= " WHERE " . implode(" AND ", $where);
            }
            
            $sql .= " GROUP BY e.id";
            
            // Filtro por avaliação
            if (isset($_GET['avaliacao_minima'])) {
                $sql .= " HAVING media_avaliacao >= ?";
                $params[] = (float)$_GET['avaliacao_minima'];
            }
            
            // Ordenação
            $ordenar = $_GET['ordenar'] ?? 'relevancia';
            switch ($ordenar) {
                case 'avaliacao':
                    $sql .= " ORDER BY media_avaliacao DESC";
                    break;
                case 'proximidade':
                    if (isset($_GET['lat']) && isset($_GET['lng'])) {
                        $sql .= " ORDER BY SQRT(POW(69.1 * (e.latitude - ?), 2) + POW(69.1 * (? - e.longitude) * COS(e.latitude / 57.3), 2)) ASC";
                        $params[] = (float)$_GET['lat'];
                        $params[] = (float)$_GET['lng'];
                    } else {
                        $sql .= " ORDER BY e.nome ASC";
                    }
                    break;
                case 'novas':
                    $sql .= " ORDER BY e.data_cadastro DESC";
                    break;
                default:
                    $sql .= " ORDER BY e.nome ASC";
                    break;
            }
            
            // Paginação
            $pagina = max(1, $_GET['pagina'] ?? 1);
            $por_pagina = $_GET['por_pagina'] ?? 10;
            $offset = ($pagina - 1) * $por_pagina;
            
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = (int)$por_pagina;
            $params[] = (int)$offset;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $empresas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Contar total para paginação
            $count_sql = "SELECT COUNT(*) as total FROM empresas e";
            if (!empty($where)) {
                $count_sql .= " WHERE " . implode(" AND ", $where);
            }
            
            $stmt = $pdo->prepare($count_sql);
            $stmt->execute(array_slice($params, 0, count($params) - 2)); // Remover LIMIT e OFFSET
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            $response = [
                'status' => 'success',
                'data' => $empresas,
                'paginacao' => [
                    'pagina' => (int)$pagina,
                    'por_pagina' => (int)$por_pagina,
                    'total' => (int)$total,
                    'total_paginas' => ceil($total / $por_pagina)
                ]
            ];
        }
        break;
        
    default:
        http_response_code(405);
        $response = ['status' => 'error', 'message' => 'Método não permitido'];
}

echo json_encode($response);
?>