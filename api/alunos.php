<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/funcoes_alunos.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/services/alunos.php';

$metodo = $_SERVER['REQUEST_METHOD'];
$externo = isset($_GET['externo']);

if (!$externo) {
    api_exigir_login();
}

if ($metodo === 'GET') {
    $filtro = $_GET['filter'] ?? null;
    $alunos = api_alunos_listar($filtro);

    if (api_prefere_html()) {
        api_html(api_render('alunos-list', ['alunos' => $alunos]));
    }

    api_json(['data' => $alunos]);
}

if ($metodo === 'POST') {
    if (!$externo && !isset($_SESSION['usuario_id'])) {
        api_json(['erro' => 'nao_autorizado'], 403);
    }

    if (($_POST['_method'] ?? '') === 'DELETE') {
        $id = $_POST['id'] ?? null;
        if (!$id || !is_numeric($id)) {
            api_json(['erro' => 'id_invalido'], 422);
        }

        $ok = excluirAluno($id);
        if (api_is_htmx()) {
            api_html($ok ? '' : '<div class="msg-alerta">Não foi possível excluir este aluno.</div>', $ok ? 200 : 422);
        }

        header('Location: ../index.php?page=lista&msg=' . ($ok ? 'excluido' : 'erro'));
        exit;
    }

    $dados = $_POST;
    if (!$externo && empty($dados['docente_id'])) {
        $dados['docente_id'] = $_SESSION['usuario_id'];
    }

    if (!empty($dados['id'])) {
        $alunoAtual = buscarAlunoPorId($dados['id']);
        if (!$alunoAtual) {
            api_json(['erro' => 'aluno_nao_encontrado'], 404);
        }

        $dados = array_merge($alunoAtual, $dados);
        $dados['usuario_id'] = $dados['usuario_id'] ?? ($_SESSION['usuario_id'] ?? null);
        $dados['docente_id'] = $dados['docente_id'] ?? ($_SESSION['usuario_id'] ?? null);
        $dados['status'] = $dados['status'] ?? 'ativo';
        $dados['foto'] = $dados['foto'] ?? ($alunoAtual['foto'] ?? 'padrao.png');

        if (!empty($_POST['senha'])) {
            $dados['senha'] = password_hash($_POST['senha'], PASSWORD_DEFAULT);
        } else {
            unset($dados['senha']);
        }

        $ok = atualizarAluno($dados);
    } else {
        $ok = salvarAluno($dados);
    }

    if (api_is_htmx()) {
        api_html($ok ? '<div class="msg-alerta">Cadastro salvo com sucesso.</div>' : '<div class="msg-alerta">Erro ao salvar cadastro.</div>', $ok ? 200 : 422);
    }

    if ($externo) {
        header('Location: ../login_view.php?status=' . ($ok ? 'pendente' : 'erro'));
    } else {
        header('Location: ../index.php?page=lista&msg=' . ($ok ? 'sucesso' : 'erro'));
    }
    exit;
}

api_json(['erro' => 'metodo_nao_suportado'], 405);
