<?php
// excluir_diario.php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

// Segurança: Apenas admins podem excluir
if (!isset($_SESSION['usuario']) || $_SESSION['nivel'] !== 'admin') {
    header("Location: views/diario_view.php?erro=sem_permissao");
    exit;
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_aula = $_GET['id'];

    try {
        $pdo->beginTransaction();

        // 1. Remove as presenças vinculadas a essa aula primeiro
        $stmtP = $pdo->prepare("DELETE FROM presencas WHERE aula_id = ?");
        $stmtP->execute([$id_aula]);

        // 2. Remove o registro da aula
        $stmtA = $pdo->prepare("DELETE FROM aulas WHERE id = ?");
        
        // CORREÇÃO AQUI: Certifique-se de que a variável é $stmtA e use ->execute()
        $stmtA->execute([$id_aula]);

        $pdo->commit();
        header("Location: views/diario_view.php?msg=excluido");
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        die("Erro ao excluir: " . $e->getMessage());
    }
} else {
    header("Location: views/diario_view.php");
    exit;
}