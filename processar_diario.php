<?php
// processar_diario.php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/funcoes_alunos.php';
require_once __DIR__ . '/api/services/aulas.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: views/diario_view.php");
    exit;
}

try {
    api_aula_salvar($_POST, $_POST['presentes'] ?? [], $_SESSION['usuario_id'], $_SESSION['nivel']);
    header("Location: views/diario_view.php?msg=sucesso");
    exit;

} catch (Exception $e) {
    die("Erro ao processar diário: " . $e->getMessage());
}
