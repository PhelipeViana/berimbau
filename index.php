<?php
// Ativar exibição de erros para desenvolvimento
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/funcoes_alunos.php';
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['usuario'])) {
    include __DIR__ . '/login_view.php';
    exit;
}

// 1. LÓGICA DE NAVEGAÇÃO E EDIÇÃO
$page = $_GET['page'] ?? 'dashboard';
$aluno_edicao = null;

// 2. LÓGICA PARA EXCLUSÃO DE ALUNO
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    excluirAluno($_GET['delete']);
    header("Location: index.php?page=lista&msg=excluido");
    exit;
}

// 3. CARREGAR DADOS PARA EDIÇÃO
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $aluno_edicao = buscarAlunoPorId($_GET['edit']);
    if ($aluno_edicao) $page = 'cadastro';
}

// 4. PROCESSAMENTO DE DADOS (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['btnSalvar'])) {
        $_POST['usuario_id'] = ($_SESSION['nivel'] === 'admin') ? ($_POST['usuario_id'] ?? $_SESSION['usuario_id']) : $_SESSION['usuario_id'];
        
        $nome_foto = $_POST['foto_atual'] ?? 'padrao.png';
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
            $extensao = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $novo_nome = uniqid() . "." . $extensao;
            if (move_uploaded_file($_FILES['foto']['tmp_name'], __DIR__ . "/uploads/" . $novo_nome)) {
                $nome_foto = $novo_nome;
            }
        }
        $_POST['foto'] = $nome_foto;

        if (!empty($_POST['nova_senha'])) {
            $_POST['senha'] = password_hash($_POST['nova_senha'], PASSWORD_DEFAULT);
        }

        !empty($_POST['id']) ? atualizarAluno($_POST) : salvarAluno($_POST);
        header("Location: index.php?page=lista&msg=sucesso");
        exit;
    }
}

// 5. CARREGAMENTO DE DADOS
$alunos = listarAlunos();
$stats = obterEstatisticas(); 
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BERIMBAU | Gestão de Capoeira</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { 
            --primary: #6366f1; --secondary: #0f172a; --bg: #f8fafc; 
            --sidebar-w: 260px; --sidebar-c: 80px; --accent: #8b5cf6;
            --danger: #ef4444; --success: #10b981; --warning: #f59e0b; --border: #e2e8f0;
        }
        * { box-sizing: border-box; transition: all 0.2s ease; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); margin: 0; display: flex; height: 100vh; color: #1e293b; overflow: hidden; }

        /* SIDEBAR CORRIGIDA */
        .sidebar { width: var(--sidebar-w); background: var(--secondary); color: white; display: flex; flex-direction: column; flex-shrink: 0; z-index: 100; }
        .sidebar.collapsed { width: var(--sidebar-c); }
        .sidebar-header { padding: 20px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #1e293b; min-height: 80px; }
        
        .logo-text b { font-size: 20px; letter-spacing: -1px; display: block; }
        .logo-text small { font-size: 9px; color: #94a3b8; display: block; text-transform: uppercase; font-weight: 400; line-height: 1.2; }
        
        .sidebar.collapsed .logo-text, .sidebar.collapsed span, .sidebar.collapsed .footer-info { display: none; }
        
        .menu-list { list-style: none; padding: 15px; flex-grow: 1; margin: 0; }
        .menu-item { text-decoration: none; color: #94a3b8; display: flex; align-items: center; padding: 12px 15px; border-radius: 12px; margin-bottom: 5px; font-size: 14px; }
        .menu-item:hover, .menu-item.active { background: rgba(99, 102, 241, 0.1); color: white; }
        .menu-item i { width: 25px; font-size: 18px; margin-right: 10px; }

        .main { flex: 1; overflow-y: auto; padding: 40px; position: relative; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        
        .grid-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 25px; margin-bottom: 40px; }
        .stat-card { background: white; padding: 25px; border-radius: 20px; border: 1px solid var(--border); display: flex; align-items: center; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .stat-icon { width: 50px; height: 50px; border-radius: 14px; display: flex; align-items: center; justify-content: center; margin-right: 20px; font-size: 20px; }
        .stat-data h3 { margin: 0; font-size: 24px; color: var(--secondary); }
        .stat-data p { margin: 0; font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase; }

        .card-glass { background: white; border-radius: 24px; padding: 30px; border: 1px solid var(--border); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        input, select, textarea { width: 100%; padding: 12px 16px; border: 1px solid var(--border); border-radius: 12px; background: #f8fafc; font-size: 14px; margin-top: 8px; }
        
        .aluno-item { display: flex; align-items: center; padding: 15px; border-bottom: 1px solid var(--border); }
        .avatar { width: 45px; height: 45px; border-radius: 12px; object-fit: cover; margin-right: 15px; }
        .btn-primary { background: var(--primary); color: white; border: none; padding: 14px; border-radius: 12px; cursor: pointer; font-weight: 600; width: 100%; margin-top: 25px; text-decoration: none; display: inline-block; text-align: center; }
        
        .badge-status { font-size: 9px; padding: 2px 8px; border-radius: 10px; text-transform: uppercase; font-weight: bold; }
        .status-ativo { background: #dcfce7; color: #166534; }
        .status-pendente { background: #fef3c7; color: #92400e; }
    </style>
</head>
<body>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo-text">
            <b>BERIMBAU<span style="color:var(--primary)"></span></b>
            <small>Gestão de Capoeira</small>
        </div>
        <button onclick="toggleSidebar()" style="background:none; border:none; color:white; cursor:pointer;"><i class="fas fa-bars"></i></button>
    </div>
    
    <nav class="menu-list">
        <a href="?page=dashboard" class="menu-item <?= $page == 'dashboard' ? 'active' : '' ?>">
            <i class="fas fa-chart-pie"></i> <span>Dashboard</span>
        </a>
        <a href="?page=lista" class="menu-item <?= $page == 'lista' ? 'active' : '' ?>">
            <i class="fas fa-users"></i> <span>Alunos</span>
        </a>
        <a href="views/diario_view.php" class="menu-item">
            <i class="fas fa-book-open"></i> <span>Diário de Aula</span>
        </a>
        
        <?php if ($_SESSION['nivel'] === 'admin'): ?>
            <a href="usuarios.php" class="menu-item">
                <i class="fas fa-user-shield"></i> <span>Operadores</span>
            </a>
        <?php endif; ?>
    </nav>

    <div style="padding: 20px; border-top: 1px solid #1e293b;">
        <div class="footer-info" style="margin-bottom: 15px;">
            <small style="color: #475569; display:block;">LOGADO COMO</small>
            <strong style="font-size: 13px;"><?= strtoupper($_SESSION['usuario']) ?></strong>
        </div>
        <a href="logout.php" style="color: var(--danger); text-decoration:none; font-size: 13px; font-weight:700;">
            <i class="fas fa-sign-out-alt"></i> <span>SAIR</span>
        </a>
    </div>
</aside>

<main class="main">
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'diario_salvo'): ?>
        <div style="background: var(--success); color: white; padding: 15px; border-radius: 12px; margin-bottom: 20px;">
            <i class="fas fa-check-circle"></i> Diário registado com sucesso!
        </div>
    <?php endif; ?>

    <div class="top-bar">
        <h1 style="font-size: 24px; margin:0;"><?= ucfirst($page) ?></h1>
        <div style="color: #64748b; font-size: 14px;"><i class="far fa-calendar"></i> <?= date('d M, Y') ?></div>
    </div>

    <?php if ($page == 'dashboard'): ?>
        <div class="grid-stats">
            <div class="stat-card">
                <div class="stat-icon" style="background: #e0e7ff; color: #4338ca;"><i class="fas fa-user-graduate"></i></div>
                <div class="stat-data"><h3><?= $stats['total'] ?></h3><p>Alunos Ativos</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #fef2f2; color: #b91c1c;"><i class="fas fa-heartbeat"></i></div>
                <div class="stat-data"><h3><?= $stats['alertas_saude'] ?></h3><p>Alertas de Saúde</p></div>
            </div>
        </div>

    <?php elseif ($page == 'lista'): ?>
        <div class="card-glass" style="padding:0">
            <div style="padding: 25px; border-bottom: 1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
                <h3 style="margin:0">Relação de Alunos</h3>
                <a href="?page=cadastro" class="btn-primary" style="width:auto; margin:0; padding:10px 20px;">+ Novo Aluno</a>
            </div>
            <?php foreach ($alunos as $a): ?>
                <div class="aluno-item">
                    <img src="uploads/<?= $a['foto'] ?? 'padrao.png' ?>" class="avatar">
                    <div style="flex:1">
                        <div style="font-weight: 700;"><?= strtoupper($a['nome']) ?> 
                            <span class="badge-status status-<?= $a['status'] ?? 'pendente' ?>">
                                <?= $a['status'] ?? 'pendente' ?>
                            </span>
                        </div>
                        <span style="font-size:12px; color:#64748b;"><?= $a['graduacao'] ?></span>
                    </div>
                    <div class="actions">
                        <a href="?edit=<?= $a['id'] ?>" style="color:var(--primary); margin-right:15px;"><i class="fas fa-edit"></i></a>
                        <a href="?delete=<?= $a['id'] ?>" style="color:var(--danger);" onclick="return confirm('Excluir aluno?')"><i class="fas fa-trash"></i></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php elseif ($page == 'cadastro'): ?>
        <div class="card-glass">
            <h2 style="margin-top:0"><?= $aluno_edicao ? 'Atualizar Aluno' : 'Nova Matrícula' ?></h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $aluno_edicao['id'] ?? '' ?>">
                <input type="hidden" name="foto_atual" value="<?= $aluno_edicao['foto'] ?? 'padrao.png' ?>">
                <div class="form-grid">
                    <div style="grid-column: span 2;">
                        <label>Nome Completo</label>
                        <input type="text" name="nome" required value="<?= $aluno_edicao['nome'] ?? '' ?>">
                    </div>
                </div>
                <button type="submit" name="btnSalvar" class="btn-primary">SALVAR REGISTRO</button>
            </form>
        </div>
    <?php endif; ?>
</main>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('collapsed');
}
</script>
</body>
</html>