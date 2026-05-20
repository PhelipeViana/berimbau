<?php
// login_aluno.php
session_start();
require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'] ?? '';

    if ($email && $senha) {
        // Busca o aluno pelo e-mail
        $stmt = $pdo->prepare("SELECT id, nome, apelido, senha, status FROM alunos WHERE email = ?");
        $stmt->execute([$email]);
        $aluno = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifica a senha e se o aluno está ativo
        if ($aluno && password_verify($senha, $aluno['senha'])) {
            if ($aluno['status'] === 'ativo') {
                // Cria a sessão específica para o ALUNO
                $_SESSION['aluno_id'] = $aluno['id'];
                $_SESSION['aluno_nome'] = $aluno['nome'];
                $_SESSION['aluno_apelido'] = $aluno['apelido'];
                $_SESSION['tipo_usuario'] = 'aluno';

                header("Location: aluno_dashboard.php");
                exit;
            } else {
                $erro = "Seu cadastro ainda está pendente de aprovação.";
            }
        } else {
            $erro = "E-mail ou senha incorretos.";
        }
    }
}
header("Location: login_view.php?erro=" . urlencode($erro ?? 'Erro ao acessar'));
exit;