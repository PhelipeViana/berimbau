<?php
// processar_diario.php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/funcoes_alunos.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: views/diario_view.php");
    exit;
}

$id_aula    = $_POST['id_aula'] ?? null; 
$docente_id = $_SESSION['usuario_id'];
$data_aula  = $_POST['data_aula'];
$tema       = strtoupper($_POST['tema_aula']);
$descricao  = $_POST['descricao_atividades'];
$local      = strtoupper($_POST['local_treino']);
$presentes  = $_POST['presentes'] ?? [];

try {
    $pdo->beginTransaction();

    if (!empty($id_aula)) {
        // --- MODO EDIÇÃO (UPDATE) ---
        $sql = "UPDATE aulas SET tema_aula = ?, descricao_atividades = ?, local_treino = ?, data_aula = ? 
                WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tema, $descricao, $local, $data_aula, $id_aula]);
        $aula_id = $id_aula;

        $pdo->prepare("DELETE FROM presencas WHERE aula_id = ?")->execute([$aula_id]);
    } else {
        // --- MODO NOVO REGISTRO (INSERT) ---
        $sql = "INSERT INTO aulas (docente_id, tema_aula, descricao_atividades, local_treino, data_aula) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$docente_id, $tema, $descricao, $local, $data_aula]);
        $aula_id = $pdo->lastInsertId();
    }

    if (!empty($presentes)) {
        $sql_presenca = "INSERT INTO presencas (aula_id, aluno_id) VALUES (?, ?)";
        $stmtP = $pdo->prepare($sql_presenca);
        foreach ($presentes as $aluno_id) {
            $stmtP->execute([$aula_id, $aluno_id]);
        }
    }

    $pdo->commit();
    
    // ALTERAÇÃO AQUI: Redireciona de volta para a tela de registro/novo diário
    header("Location: views/diario_view.php?msg=sucesso");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    die("Erro ao processar diário: " . $e->getMessage());
}