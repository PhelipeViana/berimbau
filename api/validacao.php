<?php

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/services/validacao.php';

if (($_SESSION['nivel'] ?? null) !== 'admin') {
    api_json(['erro' => 'nao_autorizado'], 403);
}

$idAluno = $_POST['id'] ?? $_GET['id'] ?? null;
$acao = $_POST['acao'] ?? $_GET['acao'] ?? null;

if (!$idAluno || !$acao) {
    api_json(['erro' => 'parametros_invalidos'], 422);
}

try {
    $msg = api_aluno_validar($idAluno, $acao, $_SESSION['usuario_id']);
    if (api_is_htmx()) {
        api_html('<div class="msg-alerta">Solicitação ' . api_escape($msg) . '.</div>');
    }

    header('Location: ../index.php?page=dashboard&msg=' . urlencode($msg));
    exit;
} catch (Exception $e) {
    if (api_is_htmx()) {
        api_html('<div class="msg-alerta">Erro: ' . api_escape($e->getMessage()) . '</div>', 422);
    }

    header('Location: ../index.php?page=dashboard&msg=erro');
    exit;
}
