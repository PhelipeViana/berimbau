<?php
// auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ajuste de caminho para o banco de dados conforme sua estrutura
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../api/services/auth.php';

if (isset($_POST['login'])) {
    $login_input = trim($_POST['usuario']); 
    $senha = trim($_POST['senha']);

    if (!empty($login_input) && !empty($senha)) {
        
        $resultadoLogin = api_auth_login($login_input, $senha);
        if ($resultadoLogin['ok'] && $resultadoLogin['tipo'] === 'admin') {
            header("Location: index.php");
            exit;
        }

        if ($resultadoLogin['ok'] && $resultadoLogin['tipo'] === 'aluno') {
            header("Location: aluno_dashboard.php");
            exit;
        }

        $erro_login = $resultadoLogin['erro'] ?? "Usuário ou senha incorretos!";
    } else {
        $erro_login = "Preencha todos os campos!";
    }
}

// Função de Logout
if (!function_exists('fazerLogout')) {
    function fazerLogout() {
        api_auth_logout();
        header("Location: login_view.php");
        exit;
    }
}
?>
