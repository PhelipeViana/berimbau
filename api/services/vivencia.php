<?php

function api_vivencia_listar() {
    global $pdo;
    return $pdo->query("SELECT * FROM vivencia ORDER BY categoria, titulo")->fetchAll(PDO::FETCH_ASSOC);
}

function api_vivencia_criar($dados, $arquivos, $usuarioId) {
    global $pdo;

    $titulo = $dados['titulo'] ?? '';
    $descricao = $dados['descricao'] ?? '';
    $categoria = $dados['categoria'] ?? 'outros';
    $tipo = $dados['tipo'] ?? 'link';
    $urlFinal = '';

    if ($tipo === 'link' && !empty($dados['url_externa'])) {
        $urlFinal = $dados['url_externa'];
    } elseif (isset($arquivos['arquivo_upload']) && $arquivos['arquivo_upload']['error'] === 0) {
        $diretorioDestino = __DIR__ . '/../../uploads/vivencia/';
        if (!is_dir($diretorioDestino)) {
            mkdir($diretorioDestino, 0755, true);
        }

        $extensao = strtolower(pathinfo($arquivos['arquivo_upload']['name'], PATHINFO_EXTENSION));
        $extensoesPermitidas = ['pdf', 'mp3', 'wav', 'png', 'jpg'];
        if (!in_array($extensao, $extensoesPermitidas, true)) {
            throw new InvalidArgumentException('Tipo de arquivo não permitido.');
        }

        $novoNome = uniqid('VIV_') . '.' . $extensao;
        $caminhoCompleto = $diretorioDestino . $novoNome;

        if (!move_uploaded_file($arquivos['arquivo_upload']['tmp_name'], $caminhoCompleto)) {
            throw new RuntimeException('Erro ao mover o arquivo para o servidor.');
        }

        $urlFinal = 'uploads/vivencia/' . $novoNome;
    }

    if ($urlFinal === '') {
        throw new InvalidArgumentException('Informe um link ou arquivo válido.');
    }

    $stmt = $pdo->prepare("INSERT INTO vivencia (titulo, descricao, categoria, tipo, url_conteudo, usuario_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$titulo, $descricao, $categoria, $tipo, $urlFinal, $usuarioId]);

    return $pdo->lastInsertId();
}
