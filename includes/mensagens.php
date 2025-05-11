<?php
function enviar_mensagem($pdo, $remetente_id, $destinatario_id, $empresa_id, $assunto, $mensagem) {
    $stmt = $pdo->prepare("INSERT INTO mensagens (remetente_id, destinatario_id, empresa_id, assunto, mensagem) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$remetente_id, $destinatario_id, $empresa_id, $assunto, $mensagem]);
}

function get_mensagens_recebidas($pdo, $usuario_id) {
    $stmt = $pdo->prepare("SELECT m.*, u.nome as remetente_nome, e.nome as empresa_nome 
                          FROM mensagens m
                          JOIN usuarios u ON m.remetente_id = u.id
                          JOIN empresas e ON m.empresa_id = e.id
                          WHERE m.destinatario_id = ?
                          ORDER BY m.data_envio DESC");
    $stmt->execute([$usuario_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_mensagens_enviadas($pdo, $usuario_id) {
    $stmt = $pdo->prepare("SELECT m.*, u.nome as destinatario_nome, e.nome as empresa_nome 
                          FROM mensagens m
                          JOIN usuarios u ON m.destinatario_id = u.id
                          JOIN empresas e ON m.empresa_id = e.id
                          WHERE m.remetente_id = ?
                          ORDER BY m.data_envio DESC");
    $stmt->execute([$usuario_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function marcar_como_lida($pdo, $mensagem_id, $usuario_id) {
    $stmt = $pdo->prepare("UPDATE mensagens SET lida = TRUE WHERE id = ? AND destinatario_id = ?");
    return $stmt->execute([$mensagem_id, $usuario_id]);
}

function contar_mensagens_nao_lidas($pdo, $usuario_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM mensagens WHERE destinatario_id = ? AND lida = FALSE");
    $stmt->execute([$usuario_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}
?>