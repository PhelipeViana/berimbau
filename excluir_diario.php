<?php
// excluir_diario.php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/api/services/aulas.php';

// Segurança: Apenas admins podem excluir
if (!isset($_SESSION['usuario']) || $_SESSION['nivel'] !== 'admin') {
    header("Location: views/diario_view.php?erro=sem_permissao");
    exit;
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    try {
        $ok = api_aula_excluir($_GET['id'], $_SESSION['usuario_id'], $_SESSION['nivel']);
        header("Location: views/diario_view.php?msg=" . ($ok ? 'excluido' : 'permissao_negada'));
        exit;
    } catch (Exception $e) {
        die("Erro ao excluir: " . $e->getMessage());
    }
} else {
    header("Location: views/diario_view.php");
    exit;
}
