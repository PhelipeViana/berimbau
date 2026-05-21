<?php

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/services/inscricoes.php';

$competicaoId = $_POST['competicao_id'] ?? $_GET['competicao_id'] ?? $_GET['id'] ?? null;
$alunoId = $_SESSION['aluno_id'] ?? $_SESSION['usuario_id'] ?? null;

if (!$competicaoId || !$alunoId) {
    api_json(['erro' => 'parametros_invalidos'], 422);
}

try {
    $msg = api_competicao_inscrever_aluno($competicaoId, $alunoId);
    if (api_is_htmx()) {
        api_html('<div class="msg-alerta">Inscrição processada.</div>');
    }

    header('Location: ../index.php?page=competicao&msg=' . urlencode($msg));
    exit;
} catch (Exception $e) {
    if (api_is_htmx()) {
        api_html('<div class="msg-alerta">Erro: ' . api_escape($e->getMessage()) . '</div>', 422);
    }

    die('Erro ao processar inscrição: ' . $e->getMessage());
}
