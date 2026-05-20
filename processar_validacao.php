<?php
// processar_validacao.php
require_once __DIR__ . '/includes/auth.php'; 
require_once __DIR__ . '/config/db.php';

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
        $pdo->beginTransaction();

        if ($acao === 'aprovar') {
            // A) Atualiza o status para 'ativo'. É este 'ativo' que fará o aluno sumir do dashboard.
            $sql = "UPDATE alunos SET docente_id = ?, status = 'ativo' WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$admin_id, $id_aluno]);

            // B) Busca graduação para o histórico inicial
            $stmt_aluno = $pdo->prepare("SELECT graduacao FROM alunos WHERE id = ?");
            $stmt_aluno->execute([$id_aluno]);
            $aluno = $stmt_aluno->fetch(PDO::FETCH_ASSOC);

            if ($aluno) {
                $sql_hist = "INSERT INTO historico_graduacoes (aluno_id, graduacao_anterior, graduacao_nova, data_mudanca, observacao) 
                             VALUES (?, ?, ?, CURDATE(), ?)";
                $stmt_hist = $pdo->prepare($sql_hist);
                $stmt_hist->execute([
                    $id_aluno, 
                    'CADASTRO INICIAL', 
                    $aluno['graduacao'], 
                    'Aluno validado e ativado pelo administrador.'
                ]);
            }
            $msg = "validado";

        } elseif ($acao === 'rejeitar') {
            $sql = "DELETE FROM alunos WHERE id = ? AND status = 'pendente'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_aluno]);
            $msg = "rejeitado";
        }

        $pdo->commit();
        // Redireciona explicitamente para o dashboard para atualizar a lista
        header("Location: index.php?page=dashboard&msg=" . $msg);
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        die("Erro crítico ao validar: " . $e->getMessage());
    }
} else {
    header("Location: index.php?page=dashboard");
    exit;
}