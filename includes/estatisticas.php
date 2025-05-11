<?php
function registrar_visualizacao($pdo, $empresa_id) {
    $hoje = date('Y-m-d');
    
    // Verificar se já existe registro para hoje
    $stmt = $pdo->prepare("SELECT id FROM empresa_estatisticas WHERE empresa_id = ? AND data = ?");
    $stmt->execute([$empresa_id, $hoje]);
    
    if ($stmt->rowCount() > 0) {
        // Atualizar contagem existente
        $stmt = $pdo->prepare("UPDATE empresa_estatisticas SET visualizacoes = visualizacoes + 1 WHERE empresa_id = ? AND data = ?");
    } else {
        // Criar novo registro
        $stmt = $pdo->prepare("INSERT INTO empresa_estatisticas (empresa_id, data, visualizacoes) VALUES (?, ?, 1)");
    }
    
    return $stmt->execute([$empresa_id, $hoje]);
}

function registrar_clique_contato($pdo, $empresa_id) {
    $hoje = date('Y-m-d');
    
    $stmt = $pdo->prepare("SELECT id FROM empresa_estatisticas WHERE empresa_id = ? AND data = ?");
    $stmt->execute([$empresa_id, $hoje]);
    
    if ($stmt->rowCount() > 0) {
        $stmt = $pdo->prepare("UPDATE empresa_estatisticas SET cliques_contato = cliques_contato + 1 WHERE empresa_id = ? AND data = ?");
    } else {
        $stmt = $pdo->prepare("INSERT INTO empresa_estatisticas (empresa_id, data, cliques_contato) VALUES (?, ?, 1)");
    }
    
    return $stmt->execute([$empresa_id, $hoje]);
}

function get_estatisticas_periodo($pdo, $empresa_id, $periodo = '30 days') {
    $data_inicio = date('Y-m-d', strtotime("-$periodo"));
    
    $stmt = $pdo->prepare("SELECT 
                          SUM(visualizacoes) as total_visualizacoes,
                          SUM(cliques_contato) as total_cliques_contato,
                          SUM(cliques_website) as total_cliques_website,
                          SUM(favoritado) as total_favoritado
                          FROM empresa_estatisticas
                          WHERE empresa_id = ? AND data >= ?");
    $stmt->execute([$empresa_id, $data_inicio]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_estatisticas_detalhadas($pdo, $empresa_id, $periodo = '30 days') {
    $data_inicio = date('Y-m-d', strtotime("-$periodo"));
    
    $stmt = $pdo->prepare("SELECT data, 
                          visualizacoes, 
                          cliques_contato, 
                          cliques_website,
                          favoritado
                          FROM empresa_estatisticas
                          WHERE empresa_id = ? AND data >= ?
                          ORDER BY data ASC");
    $stmt->execute([$empresa_id, $data_inicio]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>