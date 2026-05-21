<?php

function api_is_htmx() {
    return isset($_SERVER['HTTP_HX_REQUEST']) && $_SERVER['HTTP_HX_REQUEST'] === 'true';
}

function api_prefere_html() {
    return api_is_htmx() || ($_GET['format'] ?? '') === 'html' || isset($_GET['partial']);
}

function api_exigir_login() {
    if (!isset($_SESSION['usuario'])) {
        if (api_prefere_html()) {
            http_response_code(401);
            echo '<div class="msg-alerta">Sessao expirada. Acesse novamente.</div>';
            exit;
        }

        api_json(['erro' => 'nao_autenticado'], 401);
    }
}

function api_json($dados, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function api_html($html, $status = 200) {
    http_response_code($status);
    header('Content-Type: text/html; charset=UTF-8');
    echo $html;
    exit;
}

function api_escape($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function api_render($template, array $vars = []) {
    extract($vars);
    ob_start();
    require __DIR__ . '/partials/' . $template . '.php';
    return ob_get_clean();
}

function api_usuario_atual() {
    return [
        'id' => $_SESSION['usuario_id'] ?? null,
        'aluno_id' => $_SESSION['aluno_id'] ?? null,
        'nome' => $_SESSION['usuario'] ?? null,
        'nivel' => $_SESSION['nivel'] ?? null,
        'tipo' => $_SESSION['tipo'] ?? null,
    ];
}
