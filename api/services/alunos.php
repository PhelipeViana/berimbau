<?php

function api_alunos_listar($filtro = null) {
    $alunos = listarAlunos();

    if ($filtro === 'pendentes') {
        return array_values(array_filter($alunos, function ($aluno) {
            return empty($aluno['docente_id']) || (int) $aluno['docente_id'] === 0 || ($aluno['status'] ?? '') === 'pendente';
        }));
    }

    return $alunos;
}

function api_alunos_solicitacoes() {
    return api_alunos_listar('pendentes');
}

function api_aluno_atualizar_foto($alunoId, $arquivo) {
    global $pdo;

    if (!$alunoId || !isset($arquivo) || ($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new InvalidArgumentException('Envie uma imagem válida.');
    }

    $limiteBytes = 2 * 1024 * 1024;
    if (($arquivo['size'] ?? 0) > $limiteBytes) {
        throw new InvalidArgumentException('A imagem deve ter no máximo 2MB.');
    }

    $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
    $permitidas = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($extensao, $permitidas, true)) {
        throw new InvalidArgumentException('Use uma imagem JPG, PNG ou WEBP.');
    }

    $mime = mime_content_type($arquivo['tmp_name']);
    $mimesPermitidos = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($mime, $mimesPermitidos, true)) {
        throw new InvalidArgumentException('O arquivo enviado não parece ser uma imagem.');
    }

    $destinoDir = __DIR__ . '/../../uploads/';
    if (!is_dir($destinoDir)) {
        mkdir($destinoDir, 0755, true);
    }

    $novoNome = 'ALU_' . $alunoId . '_' . uniqid() . '.' . $extensao;
    $destino = $destinoDir . $novoNome;

    if (!move_uploaded_file($arquivo['tmp_name'], $destino)) {
        throw new RuntimeException('Não foi possível salvar a imagem.');
    }

    $stmt = $pdo->prepare("UPDATE alunos SET foto = ? WHERE id = ?");
    $stmt->execute([$novoNome, $alunoId]);

    return $novoNome;
}
