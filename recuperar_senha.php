<?php
// recuperar_senha.php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/funcoes_alunos.php';
require_once __DIR__ . '/api/services/senha.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$mensagem = $_SESSION['senha_reset_msg'] ?? null;
$classe = $_SESSION['senha_reset_class'] ?? null;
unset($_SESSION['senha_reset_msg'], $_SESSION['senha_reset_class']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $nova_senha_plana = "Capoeira2026"; // Senha temporária padrão

    try {
        if (api_aluno_resetar_senha_por_email($email, $nova_senha_plana)) {
            $mensagem = "Senha do aluno resetada! Nova senha: <strong>$nova_senha_plana</strong>";
            $classe = "success";
        } else {
            $mensagem = "E-mail não encontrado no sistema.";
            $classe = "error";
        }
    } catch (Exception $e) {
        $mensagem = "Erro ao processar: " . $e->getMessage();
        $classe = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Senha | CapoeiraOS</title>
    <style>
        body { font-family: 'Inter', sans-serif; background: #f8fafc; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 15px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .success { color: #166534; background: #dcfce7; padding: 10px; border-radius: 8px; margin-bottom: 20px; }
        .error { color: #991b1b; background: #fee2e2; padding: 10px; border-radius: 8px; margin-bottom: 20px; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #e2e8f0; border-radius: 10px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #6366f1; color: white; border: none; border-radius: 10px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Recuperar Acesso</h2>
        <?php if (isset($mensagem)): ?>
            <div class="<?= $classe ?>"><?= $mensagem ?></div>
        <?php endif; ?>
        
        <form method="POST" action="api/senha.php">
            <label>Digite seu E-mail cadastrado:</label>
            <input type="email" name="email" required placeholder="email@exemplo.com">
            <button type="submit">RESETAR SENHA</button>
        </form>
        <p style="text-align:center;"><a href="login_view.php" style="color:#6366f1; font-size:14px;">Voltar ao Login</a></p>
    </div>
</body>
</html>
