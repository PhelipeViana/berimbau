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
$nivel_user = $_SESSION['nivel']; // Pegamos o nível para a trava de segurança
$data_aula  = $_POST['data_aula'];
$tema       = strtoupper($_POST['tema_aula']);
$descricao  = $_POST['descricao_atividades'];
$local      = strtoupper($_POST['local_treino']);
$presentes  = $_POST['presentes'] ?? [];

try {
    $pdo->beginTransaction();

    if (!empty($id_aula)) {
        // --- MODO EDIÇÃO (UPDATE) ---
        // Trava de Segurança: Se não for admin, o docente_id da aula deve ser igual ao do usuário logado
        if ($nivel_user === 'admin') {
            $sql = "UPDATE aulas SET tema_aula = ?, descricao_atividades = ?, local_treino = ?, data_aula = ? 
                    WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$tema, $descricao, $local, $data_aula, $id_aula]);
        } else {
            $sql = "UPDATE aulas SET tema_aula = ?, descricao_atividades = ?, local_treino = ?, data_aula = ? 
                    WHERE id = ? AND docente_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$tema, $descricao, $local, $data_aula, $id_aula, $docente_id]);
            
            // Se não atualizou nada, pode ser tentativa de editar aula de outro
            if ($stmt->rowCount() === 0) {
                throw new Exception("Permissão negada ou aula não encontrada.");
            }
        }
        
        $aula_id = $id_aula;
        // Limpa presenças antigas para reinserir as novas
        $pdo->prepare("DELETE FROM presencas WHERE aula_id = ?")->execute([$aula_id]);

    } else {
        // --- MODO NOVO REGISTRO (INSERT) ---
        $sql = "INSERT INTO aulas (docente_id, tema_aula, descricao_atividades, local_treino, data_aula) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$docente_id, $tema, $descricao, $local, $data_aula]);
        $aula_id = $pdo->lastInsertId();
    }

    // Inserção das presenças
    if (!empty($presentes)) {
        $sql_presenca = "INSERT INTO presencas (aula_id, aluno_id) VALUES (?, ?)";
        $stmtP = $pdo->prepare($sql_presenca);
        foreach ($presentes as $aluno_id) {
            $stmtP->execute([$aula_id, $aluno_id]);
        }
    }

    $pdo->commit();
    
    header("Location: views/diario_view.php?msg=sucesso");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    // Em produção, você pode redirecionar com erro: header("Location: views/diario_view.php?msg=erro");
    die("Erro ao processar diário: " . $e->getMessage());
}