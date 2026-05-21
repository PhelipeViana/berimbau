<?php
// inscrever_aluno.php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/api/services/inscricoes.php';

$competicao_id = $_GET['id'] ?? null;
$aluno_id = $_SESSION['aluno_id'] ?? $_SESSION['usuario_id']; // ID de quem está logado

if (!$competicao_id) {
    header("Location: index.php?page=competicao&msg=erro_id");
    exit;
}

try {
    $msg = api_competicao_inscrever_aluno($competicao_id, $aluno_id);
    header("Location: index.php?page=competicao&msg=" . urlencode($msg));
} catch (Exception $e) {
    die("Erro ao processar inscrição: " . $e->getMessage());
}
