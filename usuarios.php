<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/funcoes_alunos.php';

if (!isset($_SESSION['usuario']) || $_SESSION['nivel'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Lógica para carregar utilizador em caso de edição
$user_edicao = null;
if (isset($_GET['edit_user']) && is_numeric($_GET['edit_user'])) {
    $user_edicao = buscarUsuarioPorId($_GET['edit_user']);
}

// Processar formulário (Salvar ou Atualizar)
if (isset($_POST['btnSalvarUsuario'])) {
    if (!empty($_POST['user_id'])) {
        // Modo Edição
        atualizarUsuario($_POST['user_id'], $_POST['novo_usuario'], $_POST['novo_nivel'], $_POST['nova_senha']);
        $msg = "atualizado";
    } else {
        // Modo Cadastro
        salvarUsuario($_POST['novo_usuario'], $_POST['nova_senha'], $_POST['novo_nivel']);
        $msg = "criado";
    }
    header("Location: usuarios.php?msg=$msg");
    exit;
}

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Operadores | BERIMBAU - SISTEMA DE GESTÃO PARA ESCOLAS DE CAPOEIRA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="assets/css/estilo_padrao.css">

    <style>
        /* Estilos específicos apenas para a gestão de usuários */
        .main { flex: 1; overflow-y: auto; padding: 40px; }
        .card-glass { background: white; border-radius: 24px; padding: 30px; border: 1px solid var(--border); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); margin-bottom: 30px; }
        
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
        label { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; }
        input, select { width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 12px; background: #f8fafc; margin-top: 8px; font-family: inherit; }
        
        .btn-primary { background: var(--primary); color: white; border: none; padding: 12px 25px; border-radius: 12px; cursor: pointer; font-weight: 600; transition: all 0.2s; }
        .btn-primary:hover { filter: brightness(1.1); transform: translateY(-1px); }
        
        .user-row { display: flex; align-items: center; padding: 15px; border-bottom: 1px solid var(--border); }
        .badge { font-size: 10px; padding: 4px 10px; border-radius: 20px; font-weight: 700; text-transform: uppercase; margin-left: 10px; }
        .badge-admin { background: #fef2f2; color: var(--danger); }
        .badge-docente { background: #f0fdf4; color: var(--success); }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo-text">
            <b>BERIMBAU<span style="color:var(--primary)">.</span></b>
            <small>GESTÃO PARA ESCOLAS DE CAPOEIRA</small>
        </div>
    </div>
    <nav class="menu-list">
        <a href="index.php?page=dashboard" class="menu-item"><i class="fas fa-chart-pie"></i> <span>Dashboard</span></a>
        <a href="index.php?page=lista" class="menu-item"><i class="fas fa-users"></i> <span>Alunos</span></a>
        <a href="views/diario_view.php" class="menu-item"><i class="fas fa-book-open"></i> <span>Diário de Aula</span></a>
        <a href="index.php?page=vivencia" class="menu-item"><i class="fas fa-book-reader"></i> <span>Vivência</span></a>
        <a href="usuarios.php" class="menu-item active"><i class="fas fa-user-shield"></i> <span>Operadores</span></a>
    </nav>
</aside>

<main class="main">
    <div class="card-glass">
        <h3><?= $user_edicao ? 'Editar Utilizador' : 'Novo Operador' ?></h3>
        <form method="POST">
            <input type="hidden" name="user_id" value="<?= $user_edicao['id'] ?? '' ?>">
            <div class="form-grid">
                <div>
                    <label>Login</label>
                    <input type="text" name="novo_usuario" required value="<?= $user_edicao['usuario'] ?? '' ?>">
                </div>
                <div>
                    <label><?= $user_edicao ? 'Nova Senha (vazio p/ manter)' : 'Senha' ?></label>
                    <input type="password" name="nova_senha" <?= $user_edicao ? '' : 'required' ?>>
                </div>
                <div>
                    <label>Nível</label>
                    <select name="novo_nivel">
                        <option value="docente" <?= (isset($user_edicao['nivel']) && $user_edicao['nivel'] == 'docente') ? 'selected' : '' ?>>Docente</option>
                        <option value="admin" <?= (isset($user_edicao['nivel']) && $user_edicao['nivel'] == 'admin') ? 'selected' : '' ?>>Administrador</option>
                    </select>
                </div>
            </div>
            <div style="margin-top: 20px; display:flex; gap:10px;">
                <button type="submit" name="btnSalvarUsuario" class="btn-primary">
                    <?= $user_edicao ? 'ATUALIZAR DADOS' : 'CADASTRAR' ?>
                </button>
                <?php if($user_edicao): ?>
                    <a href="usuarios.php" style="padding:12px; color:#64748b; text-decoration:none; font-size:14px;">Cancelar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="card-glass" style="padding:0;">
        <div style="padding:20px; border-bottom: 1px solid var(--border);"><strong>Utilizadores do Sistema</strong></div>
        <?php foreach($lista_usuarios as $u): ?>
            <div class="user-row">
                <div style="flex:1">
                    <strong><?= strtoupper($u['usuario']) ?></strong>
                    <span class="badge <?= $u['nivel'] === 'admin' ? 'badge-admin' : 'badge-docente' ?>"><?= $u['nivel'] ?></span>
                </div>
                <div class="actions">
                    <a href="?edit_user=<?= $u['id'] ?>" style="color: var(--primary); margin-right: 15px;"><i class="fas fa-edit"></i></a>
                    <?php if($u['id'] != $_SESSION['usuario_id']): ?>
                        <a href="?delete_user=<?= $u['id'] ?>" style="color: var(--danger);" onclick="return confirm('Excluir?')"><i class="fas fa-trash"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

</body>
</html>