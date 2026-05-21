<?php

function carregarEnv($path) {
    if (!is_file($path) || !is_readable($path)) {
        return;
    }

    $linhas = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($linhas as $linha) {
        $linha = trim($linha);
        if ($linha === '' || strpos($linha, '#') === 0 || strpos($linha, '=') === false) {
            continue;
        }

        [$chave, $valor] = explode('=', $linha, 2);
        $chave = trim($chave);
        $valor = trim($valor);

        if ($valor !== '' && (
            ($valor[0] === '"' && substr($valor, -1) === '"') ||
            ($valor[0] === "'" && substr($valor, -1) === "'")
        )) {
            $valor = substr($valor, 1, -1);
        }

        if (getenv($chave) === false) {
            putenv($chave . '=' . $valor);
            $_ENV[$chave] = $valor;
        }
    }
}

carregarEnv(__DIR__ . '/../.env');
