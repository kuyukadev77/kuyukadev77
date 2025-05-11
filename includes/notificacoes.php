<?php
function criar_notificacao($pdo, $usuario_id, $tipo, $titulo, $mensagem, $link = null) {
    $stmt = $pdo->prepare("INSERT INTO notificacoes (usuario_id, tipo, titulo, mensagem, link) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$usuario_id, $tipo, $titulo, $mensagem, $link]);
}

function get_notificacoes($pdo, $usuario_id, $nao_lidas = false) {
    $sql = "SELECT * FROM notificacoes WHERE usuario_id = ?";
    $params = [$usuario_id];
    
    if ($nao_lidas) {
        $sql .= " AND lida = FALSE";
    }
    
    $sql .= " ORDER BY data_criacao DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function marcar_notificacao_como_lida($pdo, $notificacao_id, $usuario_id) {
    $stmt = $pdo->prepare("UPDATE notificacoes SET lida = TRUE WHERE id = ? AND usuario_id = ?");
    return $stmt->execute([$notificacao_id, $usuario_id]);
}

function contar_notificacoes_nao_lidas($pdo, $usuario_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM notificacoes WHERE usuario_id = ? AND lida = FALSE");
    $stmt->execute([$usuario_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}
?>