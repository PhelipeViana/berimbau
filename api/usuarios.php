<?php

require_once __DIR__ . '/bootstrap.php';

if (($_SESSION['nivel'] ?? null) !== 'admin') {
    api_json(['erro' => 'nao_autorizado'], 403);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['_method'] ?? '') === 'DELETE') {
        $id = $_POST['id'] ?? null;
        $ok = false;
        if ($id && $id != $_SESSION['usuario_id']) {
            $ok = excluirUsuario($id);
        }
        if (api_is_htmx()) {
            api_html($ok ? '' : '<div class="msg-alerta">Erro ao excluir operador.</div>', $ok ? 200 : 422);
        }
        header('Location: ../usuarios.php?msg=' . ($ok ? 'removido' : 'erro'));
        exit;
    }

    if (!empty($_POST['user_id'])) {
        atualizarUsuario($_POST['user_id'], $_POST['novo_usuario'], $_POST['novo_nivel'], $_POST['nova_senha']);
        $msg = 'atualizado';
    } else {
        salvarUsuario($_POST['novo_usuario'], $_POST['nova_senha'], $_POST['novo_nivel']);
        $msg = 'criado';
    }

    header('Location: ../usuarios.php?msg=' . $msg);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_user'])) {
    $id = $_GET['delete_user'];
    $ok = false;
    if ($id && $id != $_SESSION['usuario_id']) {
        $ok = excluirUsuario($id);
    }
    header('Location: ../usuarios.php?msg=' . ($ok ? 'removido' : 'erro'));
    exit;
}

api_json(['data' => listarUsuarios()]);
