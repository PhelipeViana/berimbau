<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Função de Login
if (isset($_POST['login'])) {
    $login_input = trim($_POST['usuario']); // Pode ser o nome de usuário ou e-mail
    $senha = trim($_POST['senha']);

    // 1. TENTAR LOGIN NA TABELA DE USUÁRIOS (ADMIN/DOCENTE)
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ?");
    $stmt->execute([$login_input]);
    $user_db = $stmt->fetch();

    if ($user_db && password_verify($senha, $user_db['senha'])) {
        $_SESSION['usuario'] = $user_db['usuario'];
        $_SESSION['nivel'] = $user_db['nivel'];
        $_SESSION['usuario_id'] = $user_db['id'];
        header("Location: index.php");
        exit;
    } 
    
    // 2. SE NÃO ACHOU, TENTAR LOGIN NA TABELA DE ALUNOS
    $stmt_aluno = $pdo->prepare("SELECT * FROM alunos WHERE email = ?");
    $stmt_aluno->execute([$login_input]);
    $aluno_db = $stmt_aluno->fetch();

    if ($aluno_db && password_verify($senha, $aluno_db['senha'])) {
        
        // VERIFICAÇÃO DE APROVAÇÃO
        if (isset($aluno_db['status']) && $aluno_db['status'] === 'pendente') {
            $erro_login = "Seu cadastro está em análise. Aguarde a liberação do administrador!";
        } else {
            $_SESSION['usuario'] = $aluno_db['nome'];
            $_SESSION['nivel'] = 'aluno'; // Definimos o nível fixo como aluno
            $_SESSION['usuario_id'] = $aluno_db['id'];
            $_SESSION['aluno_id'] = $aluno_db['id']; // ID específico para consultas do aluno
            header("Location: area_aluno.php"); // Redireciona para a área específica
            exit;
        }
    } else {
        $erro_login = "Usuário ou senha incorretos!";
    }
}

// Função de Logout (usada no logout.php)
function fazerLogout() {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}
?>