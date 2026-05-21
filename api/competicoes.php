<?php

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/services/competicoes.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $eventos = api_competicoes_listar();

    if (api_prefere_html()) {
        api_html(api_render('competicoes-cards', ['eventos' => $eventos]));
    }

    api_json(['data' => $eventos]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_SESSION['nivel'] ?? null) !== 'admin') {
        api_json(['erro' => 'nao_autorizado'], 403);
    }

    try {
        $id = api_competicao_criar($_POST);
        if (api_is_htmx()) {
            api_html('<div class="msg-alerta">Competição cadastrada.</div>');
        }

        header('Location: ../index.php?page=competicao&msg=evento_criado');
        exit;
    } catch (Exception $e) {
        if (api_is_htmx()) {
            api_html('<div class="msg-alerta">Erro: ' . api_escape($e->getMessage()) . '</div>', 422);
        }

        api_json(['erro' => $e->getMessage()], 422);
    }
}

api_json(['erro' => 'metodo_nao_suportado'], 405);
