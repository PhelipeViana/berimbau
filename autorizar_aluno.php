<?php
// autorizar_aluno.php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

// Segurança: Apenas Administradores podem autorizar cadastros externos
if (!isset($_SESSION['usuario']) || $_SESSION['nivel'] !== 'admin') {
    header("Location: index.php?msg=acesso_negado");
    exit;
}

$id_aluno = $_GET['id'] ?? null;
$admin_id = $_SESSION['usuario_id']; // O administrador logado assume a responsabilidade inicial

if ($id_aluno && is_numeric($id_aluno)) {
    try {
        // Ao atualizar o docente_id, o aluno deixa de ser "pendente" (docente_id IS NULL)
        // e passa a pertencer à base ativa do sistema.
        $sql = "UPDATE alunos SET docente_id = ? WHERE id = ? AND (docente_id IS NULL OR docente_id = 0)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$admin_id, $id_aluno])) {
            // Sucesso: Redireciona com mensagem
            header("Location: index.php?msg=autorizado_sucesso");
        } else {
            header("Location: index.php?msg=erro_processamento");
        }
    } catch (Exception $e) {
        die("Erro técnico ao autorizar: " . $e->getMessage());
    }
} else {
    header("Location: index.php");
}
exit;