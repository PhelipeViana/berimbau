<?php
// processar_validacao.php
require_once __DIR__ . '/includes/auth.php'; 
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/api/services/validacao.php';

// Segurança: Apenas administradores acessam esta função
if (!isset($_SESSION['usuario']) || $_SESSION['nivel'] !== 'admin') {
    header("Location: index.php?msg=acesso_negado");
    exit;
}

$id_aluno = $_GET['id'] ?? null;
$acao = $_GET['acao'] ?? null;
$admin_id = $_SESSION['usuario_id']; 

if ($id_aluno && $acao) {
    try {
        $msg = api_aluno_validar($id_aluno, $acao, $admin_id);
        header("Location: index.php?page=dashboard&msg=" . $msg);
        exit;

    } catch (Exception $e) {
        die("Erro crítico ao validar: " . $e->getMessage());
    }
} else {
    header("Location: index.php?page=dashboard");
    exit;
}
