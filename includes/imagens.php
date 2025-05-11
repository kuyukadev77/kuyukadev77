<?php
function adicionar_imagem_empresa($pdo, $empresa_id, $caminho, $is_principal = false) {
    // Se for a imagem principal, remover a principal atual
    if ($is_principal) {
        $pdo->prepare("UPDATE empresa_imagens SET is_principal = FALSE WHERE empresa_id = ?")->execute([$empresa_id]);
    }
    
    $stmt = $pdo->prepare("INSERT INTO empresa_imagens (empresa_id, caminho, is_principal) VALUES (?, ?, ?)");
    return $stmt->execute([$empresa_id, $caminho, $is_principal]);
}

function get_imagens_empresa($pdo, $empresa_id) {
    $stmt = $pdo->prepare("SELECT * FROM empresa_imagens WHERE empresa_id = ? ORDER BY is_principal DESC, data_upload DESC");
    $stmt->execute([$empresa_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_imagem_principal($pdo, $empresa_id) {
    $stmt = $pdo->prepare("SELECT caminho FROM empresa_imagens WHERE empresa_id = ? AND is_principal = TRUE LIMIT 1");
    $stmt->execute([$empresa_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['caminho'] : null;
}

function definir_imagem_principal($pdo, $imagem_id, $empresa_id) {
    // Remover a principal atual
    $pdo->prepare("UPDATE empresa_imagens SET is_principal = FALSE WHERE empresa_id = ?")->execute([$empresa_id]);
    
    // Definir nova principal
    $stmt = $pdo->prepare("UPDATE empresa_imagens SET is_principal = TRUE WHERE id = ? AND empresa_id = ?");
    return $stmt->execute([$imagem_id, $empresa_id]);
}

function remover_imagem($pdo, $imagem_id, $empresa_id) {
    // Obter caminho da imagem
    $stmt = $pdo->prepare("SELECT caminho FROM empresa_imagens WHERE id = ? AND empresa_id = ?");
    $stmt->execute([$imagem_id, $empresa_id]);
    $imagem = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($imagem) {
        // Remover do banco de dados
        $stmt = $pdo->prepare("DELETE FROM empresa_imagens WHERE id = ?");
        $deleted = $stmt->execute([$imagem_id]);
        
        if ($deleted) {
            // Remover arquivo
            $caminho_arquivo = 'uploads/' . $imagem['caminho'];
            if (file_exists($caminho_arquivo)) {
                unlink($caminho_arquivo);
            }
            return true;
        }
    }
    
    return false;
}
?>