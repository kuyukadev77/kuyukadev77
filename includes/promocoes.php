<?php
function criar_promocao($pdo, $empresa_id, $titulo, $descricao, $imagem, $data_inicio, $data_fim, $desconto = null, $codigo = null) {
    $stmt = $pdo->prepare("INSERT INTO promocoes (empresa_id, titulo, descricao, imagem, data_inicio, data_fim, desconto, codigo_promocional) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$empresa_id, $titulo, $descricao, $imagem, $data_inicio, $data_fim, $desconto, $codigo]);
}

function get_promocoes_empresa($pdo, $empresa_id, $ativas = true) {
    $sql = "SELECT * FROM promocoes WHERE empresa_id = ?";
    $params = [$empresa_id];
    
    if ($ativas) {
        $sql .= " AND data_inicio <= CURDATE() AND data_fim >= CURDATE()";
    }
    
    $sql .= " ORDER BY data_inicio DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_promocoes_recentes($pdo, $limit = 10) {
    $stmt = $pdo->prepare("SELECT p.*, e.nome as empresa_nome, e.categoria as empresa_categoria 
                          FROM promocoes p
                          JOIN empresas e ON p.empresa_id = e.id
                          WHERE p.data_inicio <= CURDATE() AND p.data_fim >= CURDATE()
                          ORDER BY p.data_criacao DESC
                          LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_promocao($pdo, $promocao_id) {
    $stmt = $pdo->prepare("SELECT p.*, e.nome as empresa_nome, e.categoria as empresa_categoria 
                          FROM promocoes p
                          JOIN empresas e ON p.empresa_id = e.id
                          WHERE p.id = ?");
    $stmt->execute([$promocao_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function remover_promocao($pdo, $promocao_id, $empresa_id) {
    // Obter informações da promoção para remover a imagem
    $stmt = $pdo->prepare("SELECT imagem FROM promocoes WHERE id = ? AND empresa_id = ?");
    $stmt->execute([$promocao_id, $empresa_id]);
    $promocao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($promocao && $promocao['imagem']) {
        $caminho_arquivo = 'uploads/promocoes/' . $promocao['imagem'];
        if (file_exists($caminho_arquivo)) {
            unlink($caminho_arquivo);
        }
    }
    
    $stmt = $pdo->prepare("DELETE FROM promocoes WHERE id = ? AND empresa_id = ?");
    return $stmt->execute([$promocao_id, $empresa_id]);
}
?>