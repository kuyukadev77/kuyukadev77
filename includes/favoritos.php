<?php
function adicionar_favorito($pdo, $usuario_id, $empresa_id) {
    try {
        $stmt = $pdo->prepare("INSERT INTO favoritos (usuario_id, empresa_id) VALUES (?, ?)");
        return $stmt->execute([$usuario_id, $empresa_id]);
    } catch (PDOException $e) {
        // Ignorar erro de duplicação (já está nos favoritos)
        if ($e->getCode() == 23000) {
            return true;
        }
        return false;
    }
}

function remover_favorito($pdo, $usuario_id, $empresa_id) {
    $stmt = $pdo->prepare("DELETE FROM favoritos WHERE usuario_id = ? AND empresa_id = ?");
    return $stmt->execute([$usuario_id, $empresa_id]);
}

function is_favorito($pdo, $usuario_id, $empresa_id) {
    $stmt = $pdo->prepare("SELECT id FROM favoritos WHERE usuario_id = ? AND empresa_id = ?");
    $stmt->execute([$usuario_id, $empresa_id]);
    return $stmt->rowCount() > 0;
}

function get_favoritos($pdo, $usuario_id) {
    $stmt = $pdo->prepare("SELECT e.* 
                          FROM empresas e
                          JOIN favoritos f ON e.id = f.empresa_id
                          WHERE f.usuario_id = ?
                          ORDER BY f.data_adicao DESC");
    $stmt->execute([$usuario_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>