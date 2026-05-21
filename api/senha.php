<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/services/senha.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_json(['erro' => 'metodo_nao_suportado'], 405);
}

$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$novaSenha = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 8);

try {
    $ok = api_aluno_resetar_senha_por_email($email, $novaSenha);
    if (api_is_htmx()) {
        api_html($ok
            ? '<div class="msg-alerta">Senha do aluno resetada. Nova senha: <strong>' . api_escape($novaSenha) . '</strong></div>'
            : '<div class="msg-alerta">E-mail não encontrado no sistema.</div>',
            $ok ? 200 : 404
        );
    }

    $_SESSION['senha_reset_msg'] = $ok
        ? "Senha do aluno resetada! Nova senha: <strong>{$novaSenha}</strong>"
        : 'E-mail não encontrado no sistema.';
    $_SESSION['senha_reset_class'] = $ok ? 'success' : 'error';
    header('Location: ../recuperar_senha.php');
    exit;
} catch (Exception $e) {
    $_SESSION['senha_reset_msg'] = 'Erro ao processar: ' . $e->getMessage();
    $_SESSION['senha_reset_class'] = 'error';
    header('Location: ../recuperar_senha.php');
    exit;
}
