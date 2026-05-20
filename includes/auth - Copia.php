<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ajuste de caminho para o banco de dados
$db_path = __DIR__ . '/../config/db.php';
if (file_exists($db_path)) {
    require_once $db_path;
}

// Função de Login
if (isset($_POST['login'])) {
    $login_input = trim($_POST['usuario']); 
    $senha = trim($_POST['senha']);

    // 1. TENTAR LOGIN NA TABELA DE USUÁRIOS (ADMIN/DOCENTE)
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ?");
    $stmt->execute([$login_input]);
    $user_db = $stmt->fetch();

    if ($user_db && password_verify($senha, $user_db['senha'])) {
        $_SESSION['usuario'] = $user_db['usuario'];
        $_SESSION['nivel'] = $user_db['nivel'];
        $_SESSION['usuario_id'] = $user_db['id'];
        
        // Garante que o redirecionamento funcione independente da pasta
        header("Location: index.php");
        exit;
    } 
    
    // 2. TENTAR LOGIN NA TABELA DE ALUNOS
    $stmt_aluno = $pdo->prepare("SELECT * FROM alunos WHERE email = ?");
    $stmt_aluno->execute([$login_input]);
    $aluno_db = $stmt_aluno->fetch();

    if ($aluno_db && password_verify($senha, $aluno_db['senha'])) {
        
        // VERIFICAÇÃO DE APROVAÇÃO (Se o docente for vazio, consideramos pendente)
        if (empty($aluno_db['docente']) || $aluno_db['docente'] == 'Selecione...') {
            $erro_login = "Seu cadastro está em análise. Aguarde a liberação do administrador!";
        } else {
            $_SESSION['usuario'] = $aluno_db['nome'];
            $_SESSION['nivel'] = 'aluno';
            $_SESSION['usuario_id'] = $aluno_db['id'];
            $_SESSION['aluno_id'] = $aluno_db['id'];
            header("Location: index.php"); // Mantemos no index para carregar a área do aluno
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