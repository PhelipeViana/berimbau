<?php
// auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ajuste de caminho para o banco de dados conforme sua estrutura
require_once __DIR__ . '/../config/db.php';

if (isset($_POST['login'])) {
    $login_input = trim($_POST['usuario']); 
    $senha = trim($_POST['senha']);

    if (!empty($login_input) && !empty($senha)) {
        
        // 1. TENTAR LOGIN NA TABELA DE USUÁRIOS (ADMIN/DOCENTE)
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ?");
        $stmt->execute([$login_input]);
        $user_db = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user_db && password_verify($senha, $user_db['senha'])) {
            $_SESSION['usuario_id'] = $user_db['id'];
            $_SESSION['usuario'] = $user_db['usuario'];
            $_SESSION['nivel'] = $user_db['nivel'];
            $_SESSION['tipo'] = 'admin'; // Identificador interno de tipo de conta
            
            header("Location: index.php");
            exit;
        } 
        
        // 2. TENTAR LOGIN NA TABELA DE ALUNOS (Utilizando E-mail)
        $stmt_aluno = $pdo->prepare("SELECT * FROM alunos WHERE email = ?");
        $stmt_aluno->execute([$login_input]);
        $aluno_db = $stmt_aluno->fetch(PDO::FETCH_ASSOC);

        if ($aluno_db && password_verify($senha, $aluno_db['senha'])) {
            
            // Verificação de Aprovação baseada no docente_id e no status ativo
            if (empty($aluno_db['docente_id']) || $aluno_db['status'] !== 'ativo') {
                $erro_login = "Seu cadastro está em análise. Aguarde a liberação do seu Mestre!";
            } else {
                // Define as sessões específicas do aluno
                $_SESSION['aluno_id'] = $aluno_db['id'];
                $_SESSION['usuario'] = $aluno_db['apelido'] ?: $aluno_db['nome'];
                $_SESSION['nivel'] = 'aluno';
                $_SESSION['tipo'] = 'aluno';
                
                // Redireciona diretamente para o Dashboard do Aluno
                header("Location: aluno_dashboard.php");
                exit;
            }
        } else {
            $erro_login = "Usuário ou senha incorretos!";
        }
    } else {
        $erro_login = "Preencha todos os campos!";
    }
}

// Função de Logout
if (!function_exists('fazerLogout')) {
    function fazerLogout() {
        session_start();
        session_unset();
        session_destroy();
        header("Location: login_view.php");
        exit;
    }
}
?>