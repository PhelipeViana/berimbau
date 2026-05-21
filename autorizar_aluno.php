<?php
// autorizar_aluno.php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/api/services/validacao.php';

// Segurança: Apenas Administradores podem autorizar cadastros externos
if (!isset($_SESSION['usuario']) || $_SESSION['nivel'] !== 'admin') {
    header("Location: index.php?msg=acesso_negado");
    exit;
}

$id_aluno = $_GET['id'] ?? null;
$admin_id = $_SESSION['usuario_id']; // O administrador logado assume a responsabilidade inicial

if ($id_aluno && is_numeric($id_aluno)) {
    try {
        api_aluno_validar($id_aluno, 'aprovar', $admin_id);
        header("Location: index.php?msg=autorizado_sucesso");
    } catch (Exception $e) {
        die("Erro técnico ao autorizar: " . $e->getMessage());
    }
} else {
    header("Location: index.php");
}
exit;
