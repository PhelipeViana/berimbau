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

$lista_usuarios = listarUsuarios();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Operadores | BERIMBAU</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="assets/css/estilo_padrao.css">
    <script src="https://unpkg.com/htmx.org@1.9.12" defer></script>

    <style>
        /* Estilos específicos apenas para a gestão de usuários */
        .main { flex: 1; overflow-y: auto; padding: 40px; }
        .card-glass { padding: 30px; margin-bottom: 30px; }
        
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
        label { font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; }
        input, select { 
            width: 100%; 
            padding: 12px; 
            border: 1px solid var(--border-color); 
            border-radius: 12px; 
            background: var(--surface-strong); 
            margin-top: 8px; 
            font-family: inherit; 
            color: var(--text-main);
            outline: none;
            transition: all 0.2s ease;
        }
        
        input:focus, select:focus {
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 4px rgba(15, 122, 58, 0.12) !important;
        }
        
        .btn-primary { 
            background: linear-gradient(135deg, var(--primary), var(--primary-hover)); 
            color: white; 
            border: none; 
            padding: 12px 25px; 
            border-radius: 12px; 
            cursor: pointer; 
            font-weight: 600; 
            transition: all 0.2s; 
        }
        .btn-primary:hover { filter: brightness(1.1); transform: translateY(-1px); }
        
        .user-row { display: flex; align-items: center; padding: 15px; border-bottom: 1px solid var(--border-color); }
        .badge { font-size: 10px; padding: 4px 10px; border-radius: 20px; font-weight: 700; text-transform: uppercase; margin-left: 10px; }
        .badge-admin { background: rgba(195, 56, 45, 0.11); color: var(--danger); }
        .badge-docente { background: rgba(15, 122, 58, 0.12); color: var(--primary); }
        
        .msg-alerta { 
            background: rgba(15, 122, 58, 0.12); color: var(--primary); padding: 15px; border-radius: 8px; 
            margin-bottom: 20px; text-align: center; font-weight: bold; border: 1px solid rgba(15, 122, 58, 0.22);
        }
    </style>
</head>
<body>
<script>
    (function initTheme() {
        const savedTheme = localStorage.getItem('berimbau-theme');
        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
            document.body.classList.add('theme-dark');
        }
    })();
</script>

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
    <?php if(isset($_GET['msg'])): ?>
        <?php if($_GET['msg'] == 'criado'): ?>
            <div class="msg-alerta">Operador cadastrado com sucesso!</div>
        <?php elseif($_GET['msg'] == 'atualizado'): ?>
            <div class="msg-alerta">Dados do operador atualizados!</div>
        <?php elseif($_GET['msg'] == 'removido'): ?>
            <div class="msg-alerta">Operador removido com sucesso!</div>
        <?php elseif($_GET['msg'] == 'erro'): ?>
            <div class="msg-alerta" style="background: rgba(195, 56, 45, 0.12); color: var(--danger); border-color: rgba(195, 56, 45, 0.22);">Não foi possível realizar a ação.</div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="card-glass">
        <h3><?= $user_edicao ? 'Editar Utilizador' : 'Novo Operador' ?></h3>
        <form method="POST" action="api/usuarios.php">
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
                    <a href="usuarios.php" style="padding:12px; color:var(--text-muted); text-decoration:none; font-size:14px;">Cancelar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="card-glass" style="padding:0;">
        <div style="padding:20px; border-bottom: 1px solid var(--border-color);"><strong>Utilizadores do Sistema</strong></div>
        <?php foreach($lista_usuarios as $u): ?>
            <div class="user-row" id="user-row-<?= (int) $u['id'] ?>">
                <div style="flex:1">
                    <strong><?= strtoupper($u['usuario']) ?></strong>
                    <span class="badge <?= $u['nivel'] === 'admin' ? 'badge-admin' : 'badge-docente' ?>"><?= $u['nivel'] ?></span>
                </div>
                <div class="actions" style="display: flex; gap: 8px; align-items: center;">
                    <a href="?edit_user=<?= $u['id'] ?>" class="btn-edit"><i class="fas fa-edit"></i></a>
                    <?php if($u['id'] != $_SESSION['usuario_id']): ?>
                        <form action="api/usuarios.php"
                              method="POST"
                              style="display:inline;"
                              hx-post="api/usuarios.php"
                              hx-target="#user-row-<?= (int) $u['id'] ?>"
                              hx-swap="outerHTML"
                              hx-confirm="Excluir este operador?">
                            <input type="hidden" name="_method" value="DELETE">
                            <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                            <button type="submit" class="btn-delete" style="border:0; cursor:pointer;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

</body>
</html>
