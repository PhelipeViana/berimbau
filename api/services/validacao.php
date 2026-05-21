<?php

function api_aluno_validar($idAluno, $acao, $docenteId) {
    global $pdo;

    $pdo->beginTransaction();
    try {
        if ($acao === 'aprovar') {
            $stmt = $pdo->prepare("UPDATE alunos SET docente_id = ?, usuario_id = ?, status = 'ativo' WHERE id = ?");
            $stmt->execute([$docenteId, $docenteId, $idAluno]);

            $stmtAluno = $pdo->prepare("SELECT graduacao FROM alunos WHERE id = ?");
            $stmtAluno->execute([$idAluno]);
            $graduacao = $stmtAluno->fetchColumn();

            if ($graduacao) {
                $stmtHist = $pdo->prepare("INSERT INTO historico_graduacoes (aluno_id, graduacao_anterior, graduacao_nova, data_mudanca, observacao) VALUES (?, 'INICIO', ?, NOW(), 'Validação inicial do cadastro')");
                $stmtHist->execute([$idAluno, $graduacao]);
            }

            $mensagem = 'aprovado';
        } elseif ($acao === 'rejeitar') {
            $stmt = $pdo->prepare("DELETE FROM alunos WHERE id = ? AND status = 'pendente'");
            $stmt->execute([$idAluno]);
            $mensagem = 'rejeitado';
        } else {
            throw new InvalidArgumentException('Ação inválida.');
        }

        $pdo->commit();
        return $mensagem;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}
