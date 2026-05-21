<?php

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/services/aulas.php';

$metodo = $_SERVER['REQUEST_METHOD'];
$usuarioId = $_SESSION['usuario_id'] ?? null;
$nivel = $_SESSION['nivel'] ?? null;

if ($nivel === 'aluno') {
    api_json(['erro' => 'nao_autorizado'], 403);
}

if ($metodo === 'GET') {
    if (isset($_GET['id'])) {
        $aula = api_aula_buscar_com_docente($_GET['id']);
        api_json(['data' => $aula]);
    }

    api_json(['data' => api_aulas_recentes($nivel, $usuarioId)]);
}

if ($metodo === 'POST') {
    try {
        if (($_POST['_method'] ?? '') === 'DELETE') {
            $ok = api_aula_excluir($_POST['id'] ?? null, $usuarioId, $nivel);
            if (api_is_htmx()) {
                api_html($ok ? '' : '<div class="msg-alerta">Não foi possível excluir este registro.</div>', $ok ? 200 : 403);
            }
            header('Location: ../views/diario_view.php?msg=' . ($ok ? 'excluido' : 'permissao_negada'));
            exit;
        }

        api_aula_salvar($_POST, $_POST['presentes'] ?? [], $usuarioId, $nivel);
        header('Location: ../views/diario_view.php?msg=sucesso');
        exit;
    } catch (Exception $e) {
        if (api_is_htmx()) {
            api_html('<div class="msg-alerta">Erro: ' . api_escape($e->getMessage()) . '</div>', 422);
        }
        die('Erro ao processar diário: ' . $e->getMessage());
    }
}

api_json(['erro' => 'metodo_nao_suportado'], 405);
