<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/funcoes_alunos.php';

// Só o admin acessa esta página
if (!isset($_SESSION['usuario']) || $_SESSION['nivel'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Processar Cadastro
if (isset($_POST['btnSalvarUsuario'])) {
    salvarUsuario($_POST['novo_usuario'], $_POST['nova_senha'], $_POST['novo_nivel']);
    header("Location: usuarios.php?msg=criado");
    exit;
}

// Processar Exclusão
if (isset($_GET['delete_user'])) {
    excluirUsuario($_GET['delete_user']);
    header("Location: usuarios.php?msg=removido");
    exit;
}

$lista_usuarios = listarUsuarios();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gestão de Operadores - Capoeira</title>
    <style>
        :root { --primary: #475569; --bg: #f1f5f9; --border: #e2e8f0; --danger: #ef4444; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); padding: 20px; }
        .container { max-width: 600px; margin: auto; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-size: 12px; font-weight: bold; margin-bottom: 5px; }
        input, select { width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 6px; box-sizing: border-box; }
        .btn-add { background: var(--primary); color: white; border: none; padding: 12px; width: 100%; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .user-list { margin-top: 30px; border-top: 2px solid var(--border); padding-top: 20px; }
        .user-item { display: flex; justify-content: space-between; align-items: center; padding: 10px; border-bottom: 1px solid var(--border); }
        .badge { background: #e2e8f0; padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <a href="index.php" style="text-decoration: none; color: var(--primary); font-size: 14px;">⬅ Voltar ao Painel</a>
    <h2>👥 GESTÃO DE OPERADORES</h2>

    <form method="POST">
        <div class="form-group">
            <label>NOME DE USUÁRIO (LOGIN)</label>
            <input type="text" name="novo_usuario" required placeholder="Ex: mestre.biro">
        </div>
        <div class="form-group">
            <label>SENHA DE ACESSO</label>
            <input type="password" name="nova_senha" required>
        </div>
        <div class="form-group">
            <label>NÍVEL DE PERMISSÃO</label>
            <select name="novo_nivel">
                <option value="user">Operador (Apenas vê e cadastra)</option>
                <option value="admin">Administrador (Pode excluir e editar)</option>
            </select>
        </div>
        <button type="submit" name="btnSalvarUsuario" class="btn-add">CADASTRAR OPERADOR</button>
    </form>

    <div class="user-list">
        <h3>Usuários Ativos</h3>
        <?php foreach($lista_usuarios as $u): ?>
            <div class="user-item">
                <div>
                    <strong><?= strtoupper($u['usuario']) ?></strong> 
                    <span class="badge"><?= strtoupper($u['nivel']) ?></span>
                </div>
                <?php if($u['usuario'] !== $_SESSION['usuario']): ?>
                    <a href="?delete_user=<?= $u['id'] ?>" style="color: var(--danger); font-size: 12px; font-weight: bold;" onclick="return confirm('Remover este acesso?')">REMOVER</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>