<?php

function api_dashboard_stats() {
    $usuario = api_usuario_atual();

    if (($usuario['nivel'] ?? null) === 'aluno') {
        $alunoId = $usuario['aluno_id'] ?? $usuario['id'];
        return obterFrequenciaAluno($alunoId);
    }

    return obterEstatisticas();
}
