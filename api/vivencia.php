<?php

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/services/vivencia.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $conteudos = api_vivencia_listar();

    if (api_prefere_html()) {
        api_html(api_render('vivencia-cards', ['conteudos' => $conteudos]));
    }

    api_json(['data' => $conteudos]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_SESSION['nivel'] ?? null) !== 'admin') {
        api_json(['erro' => 'nao_autorizado'], 403);
    }

    try {
        $id = api_vivencia_criar($_POST, $_FILES, $_SESSION['usuario_id']);
        if (api_is_htmx()) {
            api_html('<div class="msg-alerta">Material publicado no acervo.</div>');
        }

        header('Location: ../index.php?page=vivencia&msg=postado');
        exit;
    } catch (Exception $e) {
        if (api_is_htmx()) {
            api_html('<div class="msg-alerta">Erro: ' . api_escape($e->getMessage()) . '</div>', 422);
        }

        header('Location: ../views/admin_vivencia.php?msg=erro_dados');
        exit;
    }
}

api_json(['erro' => 'metodo_nao_suportado'], 405);
