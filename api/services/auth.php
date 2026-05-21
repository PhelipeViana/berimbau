<?php

function api_auth_buscar_usuario($login) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ?");
    $stmt->execute([$login]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function api_auth_buscar_aluno_por_email($email) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM alunos WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function api_auth_login($login, $senha) {
    $usuario = api_auth_buscar_usuario($login);
    if ($usuario && password_verify($senha, $usuario['senha'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario'] = $usuario['usuario'];
        $_SESSION['nivel'] = $usuario['nivel'];
        $_SESSION['tipo'] = 'admin';
        return ['ok' => true, 'tipo' => 'admin'];
    }

    $aluno = api_auth_buscar_aluno_por_email($login);
    if ($aluno && password_verify($senha, $aluno['senha'])) {
        if (empty($aluno['docente_id']) || ($aluno['status'] ?? '') !== 'ativo') {
            return ['ok' => false, 'erro' => 'Seu cadastro está em análise. Aguarde a liberação do seu Mestre!'];
        }

        $_SESSION['aluno_id'] = $aluno['id'];
        $_SESSION['usuario'] = $aluno['apelido'] ?: $aluno['nome'];
        $_SESSION['nivel'] = 'aluno';
        $_SESSION['tipo'] = 'aluno';
        return ['ok' => true, 'tipo' => 'aluno'];
    }

    return ['ok' => false, 'erro' => 'Usuário ou senha incorretos!'];
}

function api_auth_logout() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}
