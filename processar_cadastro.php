<?php
// processar_cadastro.php
require_once __DIR__ . '/includes/auth.php'; // Garante que apenas usuários logados acessem
require_once __DIR__ . '/includes/funcoes_alunos.php';

// Verifica se os dados foram enviados via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Como é um cadastro interno feito pelo Admin/Mestre, 
    // garantimos que o docente_id seja preenchido corretamente.
    $dados = $_POST;
    
    // Se o docente não foi selecionado no select, usamos o ID de quem está logado
    if (empty($dados['docente_id'])) {
        $dados['docente_id'] = $_SESSION['usuario_id'];
    }

    // Chama a função principal que já ajustamos para lidar com a senha e status
    if (salvarAluno($dados)) {
        // Sucesso: Redireciona para a lista de alunos
        header("Location: index.php?page=lista&msg=sucesso");
        exit;
    } else {
        die("Erro ao salvar o aluno no banco de dados.");
    }
} else {
    // Se tentarem acessar o arquivo diretamente sem POST
    header("Location: index.php?page=cadastro");
    exit;
}