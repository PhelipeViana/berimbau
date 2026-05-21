<?php

function api_aluno_resetar_senha_por_email($email, $novaSenhaPlana) {
    global $pdo;
    $senhaHash = password_hash($novaSenhaPlana, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("UPDATE alunos SET senha = ? WHERE email = ?");
    $stmt->execute([$senhaHash, $email]);

    return $stmt->rowCount() > 0;
}
