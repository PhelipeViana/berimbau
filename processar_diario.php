<?php
// processar_diario.php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/funcoes_alunos.php';

// 1. Verificação de Segurança
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: diario_view.php");
    exit;
}

// 2. Coleta de dados do formulário
$docente_id = $_SESSION['usuario_id'];
$data_aula  = $_POST['data_aula'];
$tema       = $_POST['tema_aula'];
$descricao  = $_POST['descricao_atividades'];
$local      = $_POST['local_treino'];
$presentes  = $_POST['presentes'] ?? []; // Array com IDs dos alunos marcados

try {
    // Inicia uma transação no banco (ou grava tudo ou não grava nada se der erro)
    $pdo->beginTransaction();

    // 3. Insere o registro da Aula
    $sql_aula = "INSERT INTO aulas (docente_id, tema_aula, descricao_atividades, local_treino, data_aula) 
                 VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql_aula);
    $stmt->execute([
        $docente_id, 
        strtoupper($tema), 
        $descricao, 
        strtoupper($local), 
        $data_aula
    ]);

    // Pega o ID da aula que acabamos de criar
    $aula_id = $pdo->lastInsertId();

    // 4. Insere as Presenças
    if (!empty($presentes)) {
        $sql_presenca = "INSERT INTO presencas (aula_id, aluno_id) VALUES (?, ?)";
        $stmtP = $pdo->prepare($sql_presenca);

        foreach ($presentes as $aluno_id) {
            $stmtP->execute([$aula_id, $aluno_id]);
        }
    }

    // Confirma as alterações no banco
    $pdo->commit();

    // Redireciona com sucesso
    header("Location: index.php?msg=diario_salvo");
    exit;

} catch (Exception $e) {
    // Se der qualquer erro, desfaz o que foi feito para não corromper os dados
    $pdo->rollBack();
    die("Erro ao salvar o diário: " . $e->getMessage());
}