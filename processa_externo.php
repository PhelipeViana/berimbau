<?php
// processa_externo.php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/funcoes_alunos.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Definimos como vazio para disparar o alerta no Dashboard do Admin
    $_POST['docente'] = ''; 
    
    // Chamamos a função de salvar do seu sistema
    if (salvarAluno($_POST)) {
        // Redireciona de volta para o login com aviso de sucesso
        header("Location: login_view.php?status=pendente");
    } else {
        header("Location: login_view.php?status=erro");
    }
    exit;
}