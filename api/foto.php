<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/funcoes_alunos.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/services/alunos.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_json(['erro' => 'metodo_nao_suportado'], 405);
}

$alunoId = $_SESSION['aluno_id'] ?? null;
if (!$alunoId) {
    api_json(['erro' => 'nao_autorizado'], 403);
}

try {
    $foto = api_aluno_atualizar_foto($alunoId, $_FILES['foto'] ?? null);

    if (api_is_htmx()) {
        api_html(
            '<div class="upload-feedback success">Foto atualizada.</div>' .
            '<img src="uploads/' . api_escape($foto) . '" class="perfil-foto" alt="Foto atualizada">'
        );
    }

    header('Location: ../aluno_dashboard.php?msg=foto_atualizada');
    exit;
} catch (Exception $e) {
    if (api_is_htmx()) {
        api_html('<div class="upload-feedback error">' . api_escape($e->getMessage()) . '</div>', 422);
    }

    $_SESSION['foto_upload_erro'] = $e->getMessage();
    header('Location: ../aluno_dashboard.php?msg=erro_foto');
    exit;
}
