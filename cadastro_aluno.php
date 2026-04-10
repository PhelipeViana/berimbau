<?php
require_once 'includes/funcoes_alunos.php';

$mensagem = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Captura os dados básicos para o pré-cadastro
    $dados = [
        'nome'       => $_POST['nome'],
        'email'      => $_POST['email'],
        'senha'      => $_POST['senha'],
        'docente_id' => $_POST['docente_id'] // Aluno escolhe quem é o professor dele
    ];

    if (preCadastroAluno($dados)) {
        $mensagem = "sucesso";
    } else {
        $mensagem = "erro";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Solicitar Acesso | CapoeiraOS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f8fafc; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 40px; border-radius: 24px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        input, select { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #e2e8f0; border-radius: 12px; }
        .btn { background: #6366f1; color: white; border: none; padding: 14px; width: 100%; border-radius: 12px; cursor: pointer; font-weight: bold; }
        .msg { padding: 15px; border-radius: 12px; margin-bottom: 20px; text-align: center; }
        .msg-sucesso { background: #dcfce7; color: #166534; }
    </style>
</head>
<body>

<div class="card">
    <?php if ($mensagem === 'sucesso'): ?>
        <div class="msg msg-sucesso">
            <i class="fas fa-check-circle"></i><br>
            Solicitação enviada!<br>
            <small>Aguarde a liberação do seu instrutor para fazer login.</small>
            <br><br>
            <a href="index.php" style="color: #6366f1; text-decoration: none; font-weight: bold;">Voltar para o Login</a>
        </div>
    <?php else: ?>
        <h2 style="text-align: center; margin-top: 0;">Pré-Cadastro</h2>
        <p style="color: #64748b; font-size: 14px; text-align: center;">Preencha seus dados para solicitar acesso ao sistema.</p>
        
        <form method="POST">
            <label>Nome Completo</label>
            <input type="text" name="nome" required placeholder="Seu nome">

            <label>E-mail (Será seu login)</label>
            <input type="email" name="email" required placeholder="exemplo@email.com">

            <label>Crie uma Senha</label>
            <input type="password" name="senha" required placeholder="******">

            <label>Quem é seu Professor?</label>
            <select name="docente" required>
                <option value="">Selecione...</option>
                <option>MESTRE BIRO</option>
                <option>MESTRE KOSKORÃO</option>
                <option>CONTRAMESTRE GALEGO</option>
                <option>CONTRAMESTRE MUTUM</option>
                <option>CONTRAMESTRE CHIQUINHO</option>
                <option>CONTRAMESTRE AMENDOIM</option>
                <option>CONTRAMESTRE COYOT</option>
                <option>PROFESSOR TUIUIÚ</option>
                <option>PROFESSOR RAFAEL</option>
                <option>PROFESSORA CIGANA</option>
                <option>PROFESSOR CAVALLO</option>
                <option>PROFESSOR SAGUI</option>
                <option>PROFESSOR CALADO</option>
                <option>INSTRUTOR ESQUILO</option>
                <option>INSTRUTORA SEREIA</option>
                <option>GRADUADO DUDU</option>
                <option>GRADUADO GUERREIRO</option>
                <option>GRADUADO BIG</option>
                <option>OUTRO</option>
            </select>
                <?php 
                // Usamos a função que já temos para listar os docentes
                $docentes = listarUsuariosDocentes();
                foreach($docentes as $doc) echo "<option value='{$doc['id']}'>{$doc['usuario']}</option>";
                ?>
            </select>

            <button type="submit" class="btn">SOLICITAR ACESSO</button>
        </form>
        <div style="text-align: center; margin-top: 20px;">
            <a href="index.php" style="font-size: 13px; color: #64748b;">Já tenho cadastro</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>