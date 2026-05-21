<?php

function api_competicao_inscrever_aluno($competicaoId, $alunoId) {
    global $pdo;

    $check = $pdo->prepare("SELECT id FROM inscricoes_competicao WHERE competicao_id = ? AND aluno_id = ?");
    $check->execute([$competicaoId, $alunoId]);

    if ($check->rowCount() > 0) {
        return 'ja_inscrito';
    }

    $stmt = $pdo->prepare("INSERT INTO inscricoes_competicao (competicao_id, aluno_id) VALUES (?, ?)");
    $stmt->execute([$competicaoId, $alunoId]);
    return 'inscrito_sucesso';
}
