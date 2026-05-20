<?php
// inscrever_aluno.php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';

$competicao_id = $_GET['id'] ?? null;
$aluno_id = $_SESSION['usuario_id']; // ID de quem está logado

if (!$competicao_id) {
    header("Location: index.php?page=competicao&msg=erro_id");
    exit;
}

try {
    // Verifica se já está inscrito para evitar erro de duplicidade
    $check = $pdo->prepare("SELECT id FROM inscricoes_competicao WHERE competicao_id = ? AND aluno_id = ?");
    $check->execute([$competicao_id, $aluno_id]);

    if ($check->rowCount() == 0) {
        $sql = "INSERT INTO inscricoes_competicao (competicao_id, aluno_id) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$competicao_id, $aluno_id]);
        
        header("Location: index.php?page=competicao&msg=inscrito_sucesso");
    } else {
        header("Location: index.php?page=competicao&msg=ja_inscrito");
    }
} catch (Exception $e) {
    die("Erro ao processar inscrição: " . $e->getMessage());
}