<?php
function adicionar_avaliacao($pdo, $empresa_id, $usuario_id, $nota, $comentario) {
    $stmt = $pdo->prepare("INSERT INTO avaliacoes (empresa_id, usuario_id, nota, comentario) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$empresa_id, $usuario_id, $nota, $comentario]);
}

function get_avaliacoes($pdo, $empresa_id) {
    $stmt = $pdo->prepare("SELECT a.*, u.nome as usuario_nome 
                          FROM avaliacoes a
                          JOIN usuarios u ON a.usuario_id = u.id
                          WHERE a.empresa_id = ?
                          ORDER BY a.data_avaliacao DESC");
    $stmt->execute([$empresa_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_media_avaliacoes($pdo, $empresa_id) {
    $stmt = $pdo->prepare("SELECT AVG(nota) as media, COUNT(*) as total 
                          FROM avaliacoes 
                          WHERE empresa_id = ?");
    $stmt->execute([$empresa_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>