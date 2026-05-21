<?php

function api_competicoes_listar() {
    global $pdo;
    $eventos = $pdo->query("SELECT * FROM competicoes ORDER BY data_evento DESC")->fetchAll(PDO::FETCH_ASSOC);

    if (($_SESSION['nivel'] ?? null) === 'admin') {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM inscricoes_competicao WHERE competicao_id = ?");
        foreach ($eventos as &$evento) {
            $stmt->execute([$evento['id']]);
            $evento['total_inscritos'] = (int) $stmt->fetchColumn();
        }
    }

    return $eventos;
}

function api_competicao_criar($dados) {
    global $pdo;

    $stmt = $pdo->prepare("INSERT INTO competicoes (nome_evento, data_evento, local_evento, status, edital_url, descricao) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $dados['nome_evento'] ?? '',
        normalizarDataMysql($dados['data_evento'] ?? ''),
        $dados['local_evento'] ?? '',
        $dados['status'] ?? 'inscricoes_abertas',
        $dados['edital_url'] ?? '',
        $dados['descricao'] ?? '',
    ]);

    return $pdo->lastInsertId();
}
