<?php

function api_aula_buscar_edicao($id, $nivel, $usuarioId) {
    global $pdo;
    if ($nivel === 'admin') {
        $stmt = $pdo->prepare("SELECT * FROM aulas WHERE id = ?");
        $stmt->execute([$id]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM aulas WHERE id = ? AND docente_id = ?");
        $stmt->execute([$id, $usuarioId]);
    }
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function api_aulas_recentes($nivel, $usuarioId, $limite = 20) {
    global $pdo;
    $limite = max(1, (int) $limite);

    if ($nivel === 'admin') {
        $stmt = $pdo->query("SELECT a.*, u.usuario as docente_nome
                             FROM aulas a
                             LEFT JOIN usuarios u ON a.docente_id = u.id
                             ORDER BY a.data_aula DESC LIMIT {$limite}");
    } else {
        $stmt = $pdo->prepare("SELECT a.*, u.usuario as docente_nome
                               FROM aulas a
                               LEFT JOIN usuarios u ON a.docente_id = u.id
                               WHERE a.docente_id = ?
                               ORDER BY a.data_aula DESC LIMIT {$limite}");
        $stmt->execute([$usuarioId]);
    }

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function api_aula_salvar($dados, $presentes, $usuarioId, $nivel) {
    global $pdo;

    $idAula = $dados['id_aula'] ?? null;
    $dataAula = normalizarDataMysql($dados['data_aula'] ?? date('d/m/Y'));
    $tema = strtoupper($dados['tema_aula'] ?? '');
    $descricao = $dados['descricao_atividades'] ?? '';
    $local = strtoupper($dados['local_treino'] ?? '');

    $pdo->beginTransaction();
    try {
        if (!empty($idAula)) {
            if ($nivel === 'admin') {
                $stmt = $pdo->prepare("UPDATE aulas SET tema_aula = ?, descricao_atividades = ?, local_treino = ?, data_aula = ? WHERE id = ?");
                $stmt->execute([$tema, $descricao, $local, $dataAula, $idAula]);
            } else {
                $stmt = $pdo->prepare("UPDATE aulas SET tema_aula = ?, descricao_atividades = ?, local_treino = ?, data_aula = ? WHERE id = ? AND docente_id = ?");
                $stmt->execute([$tema, $descricao, $local, $dataAula, $idAula, $usuarioId]);
                if ($stmt->rowCount() === 0) {
                    throw new Exception('Permissão negada ou aula não encontrada.');
                }
            }

            $aulaId = $idAula;
            $pdo->prepare("DELETE FROM presencas WHERE aula_id = ?")->execute([$aulaId]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO aulas (docente_id, tema_aula, descricao_atividades, local_treino, data_aula) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$usuarioId, $tema, $descricao, $local, $dataAula]);
            $aulaId = $pdo->lastInsertId();
        }

        if (!empty($presentes)) {
            $stmtPresenca = $pdo->prepare("INSERT INTO presencas (aula_id, aluno_id) VALUES (?, ?)");
            foreach ($presentes as $alunoId) {
                $stmtPresenca->execute([$aulaId, $alunoId]);
            }
        }

        $pdo->commit();
        return $aulaId;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function api_aula_excluir($id, $usuarioId, $nivel) {
    global $pdo;

    $aula = api_aula_buscar_edicao($id, $nivel, $usuarioId);
    if (!$aula) {
        return false;
    }

    $pdo->beginTransaction();
    try {
        $pdo->prepare("DELETE FROM presencas WHERE aula_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM aulas WHERE id = ?")->execute([$id]);
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function api_aula_buscar_com_docente($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT a.*, u.usuario as nome_docente, u.usuario as docente_nome
                           FROM aulas a
                           JOIN usuarios u ON a.docente_id = u.id
                           WHERE a.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function api_aula_presencas($id, $comGraduacao = false) {
    global $pdo;
    $campos = $comGraduacao ? 'al.id, al.nome, al.apelido, al.graduacao' : 'al.id, al.nome, al.apelido';
    $stmt = $pdo->prepare("SELECT {$campos}
                           FROM presencas p
                           JOIN alunos al ON p.aluno_id = al.id
                           WHERE p.aula_id = ?
                           ORDER BY al.nome ASC");
    $stmt->execute([$id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function api_aulas_presencas_aluno($alunoId, $limite = 10) {
    global $pdo;
    $limite = max(1, (int) $limite);
    $stmt = $pdo->prepare("SELECT a.tema_aula, a.data_aula, a.local_treino
                           FROM presencas p
                           JOIN aulas a ON p.aula_id = a.id
                           WHERE p.aluno_id = ?
                           ORDER BY a.data_aula DESC
                           LIMIT {$limite}");
    $stmt->execute([$alunoId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
