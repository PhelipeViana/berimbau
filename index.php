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

// 2. CARREGAR DADOS PARA EDIÇÃO (Utilizado para validar solicitações do Dashboard)
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $aluno_edicao = buscarAlunoPorId($_GET['edit']);
    if ($aluno_edicao) {
        $page = 'cadastro'; // Abre o formulário de cadastro em modo edição
    }
}

// 3. CARREGAMENTO DE DADOS PARA A VIEW
$alunos = listarAlunos();
$stats = obterEstatisticas(); 

// Lógica de Solicitações: Alunos que não tem docente vinculado OU estão com status pendente
$solicitacoes = array_filter($alunos, function($a) {
    return (empty($a['docente_id']) || $a['docente_id'] == 0 || $a['status'] === 'pendente'); 
});
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BERIMBAU | Gestão de Capoeira</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/estilo_padrao.css">
    <script src="https://unpkg.com/htmx.org@1.9.12" defer></script>
    <style>
        .card-vivencia {
            background: var(--surface); padding: 25px; border-radius: 8px; border: 1px solid var(--border-color); 
            transition: all 0.3s ease; display: flex; flex-direction: column; justify-content: space-between;
        }
        .card-vivencia:hover { transform: translateY(-3px); box-shadow: var(--shadow-soft); border-color: rgba(15, 122, 58, 0.34); }
        .icon-vivencia {
            background: rgba(15, 122, 58, 0.1); width: 50px; height: 50px; border-radius: 8px; 
            display: flex; align-items: center; justify-content: center; margin-bottom: 15px;
        }
        .badge-pendente {
            background: #ef4444; color: white; padding: 2px 8px; border-radius: 10px; font-size: 10px; margin-left: 5px;
        }
        .msg-alerta { 
            background: rgba(15, 122, 58, 0.12); color: var(--primary); padding: 15px; border-radius: 8px; 
            margin-bottom: 20px; text-align: center; font-weight: bold; border: 1px solid rgba(15, 122, 58, 0.22);
        }
    </style>
</head>
<body class="app-layout">

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo-text">
            <b>BERIMBAU<span style="color:var(--primary)">.</span></b>
            <small>GESTÃO PARA ESCOLAS DE CAPOEIRA</small>
        </div>
        <button onclick="toggleSidebar()" class="btn-toggle"><i class="fas fa-bars"></i></button>
    </div>
    
    <nav class="menu-list">
        <a href="?page=dashboard" class="menu-item <?= $page == 'dashboard' ? 'active' : '' ?>">
            <i class="fas fa-chart-pie"></i> <span>Dashboard</span>
        </a>
        
        <?php if ($_SESSION['nivel'] !== 'aluno'): ?>
            <a href="?page=lista" class="menu-item <?= ($page == 'lista' || (isset($_GET['filter']) && $_GET['filter'] == 'pendentes')) ? 'active' : '' ?>">
                <i class="fas fa-users"></i> <span>Alunos</span>
                <?php if(count($solicitacoes) > 0) echo '<span class="badge-pendente">'.count($solicitacoes).'</span>'; ?>
            </a>
            <a href="views/diario_view.php" class="menu-item">
                <i class="fas fa-book-open"></i> <span>Diário de Aula</span>
            </a>
        <?php endif; ?>
        
        <a href="?page=vivencia" class="menu-item <?= $page == 'vivencia' ? 'active' : '' ?>">
            <i class="fas fa-book-reader"></i> <span>Vivência</span>
        </a>

        <a href="?page=competicao" class="menu-item <?= $page == 'competicao' ? 'active' : '' ?>">
            <i class="fas fa-trophy"></i> <span>Competição</span>
        </a>

        <?php if ($_SESSION['nivel'] === 'admin'): ?>
            <a href="usuarios.php" class="menu-item">
                <i class="fas fa-user-shield"></i> <span>Operadores</span>
            </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer" style="padding: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
        <button type="button" class="theme-toggle" onclick="toggleTheme()" aria-label="Alternar tema">
            <i class="fas fa-circle-half-stroke"></i> <span class="theme-label">TEMA</span>
        </button>
        <div class="user-info" style="margin-bottom: 15px;">
            <small style="display: block; color: #94a3b8; font-size: 10px; letter-spacing: 1px;">USUÁRIO CONECTADO</small>
            <strong style="color: white; font-size: 13px;"><?= strtoupper($_SESSION['usuario']) ?></strong>
        </div>
        <a href="api/logout.php" class="btn-logout" style="display: flex; align-items: center; justify-content: center; gap: 10px; background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 12px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 12px; transition: all 0.3s ease; border: 1px solid rgba(239, 68, 68, 0.2);">
            <i class="fas fa-sign-out-alt"></i> <span>SAIR DO SISTEMA</span>
        </a>
    </div>
</aside>

<main class="main-content">

    <?php if(isset($_GET['msg'])): ?>
        <?php if($_GET['msg'] == 'inscrito_sucesso'): ?>
            <div class="msg-alerta">Inscrição confirmada! Bom treino, camarada!</div>
        <?php elseif($_GET['msg'] == 'sucesso'): ?>
            <div class="msg-alerta">Ação realizada com sucesso!</div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($page == 'dashboard'): ?>
        <header class="content-header">
            <h1>Olá, <?= explode(' ', $_SESSION['usuario'])[0] ?>!</h1>
            <p>Organize alunos, aulas e vivências com a energia da roda.</p>
        </header>

        <?php if ($_SESSION['nivel'] === 'admin' && count($solicitacoes) > 0): ?>
            <div class="alerta-solicitacao" style="background: #fff5f5; border-left: 6px solid #ef4444; padding: 20px; border-radius: 16px; margin-bottom: 30px; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.1);">
                <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                    <div style="background: #ef4444; color: white; width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-user-plus" style="font-size: 20px;"></i>
                    </div>
                    <div>
                        <h3 style="margin:0; color: #991b1b; font-size: 18px;">Novas Solicitações de Inclusão</h3>
                        <p style="margin:0; color: #b91c1c; font-size: 14px;">Clique no nome para validar e ativar o cadastro.</p>
                    </div>
                </div>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <?php foreach(array_slice($solicitacoes, 0, 6) as $sol): ?>
                        <a href="?edit=<?= $sol['id'] ?>" style="text-decoration:none; background: white; padding: 10px 18px; border-radius: 10px; border: 1px solid #fee2e2; font-size: 13px; color: #444; display: flex; align-items: center; gap: 8px; transition: 0.2s;">
                            <i class="fas fa-id-badge" style="color: #ef4444;"></i> <?= strtoupper($sol['nome']) ?>
                        </a>
                    <?php endforeach; ?>
                    <a href="?page=lista&filter=pendentes" style="text-decoration:none; background: #ef4444; color: white; padding: 10px 18px; border-radius: 10px; font-size: 13px; font-weight: bold; margin-left: auto;">VER TODAS</a>
                </div>
            </div>
        <?php endif; ?>

        <div class="stats-grid"
             hx-get="api/dashboard.php?partial=stats"
             hx-trigger="load"
             hx-swap="innerHTML">
            <div class="card-estatistica">
                <div class="icon-box bg-info"><i class="fas fa-spinner fa-spin"></i></div>
                <div><h2>...</h2><small>CARREGANDO</small></div>
            </div>
        </div>

    <?php elseif ($page == 'lista'): ?>
        <header class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <h1><?= (isset($_GET['filter']) && $_GET['filter'] == 'pendentes') ? 'Solicitações Pendentes' : 'Relação de Alunos' ?></h1>
                <p style="color: #64748b;">Gerencie os integrantes da sua escola</p>
            </div>
            <a href="?page=cadastro" class="btn-berimbau btn-primary" style="text-decoration: none; padding: 12px 24px;">
                <i class="fas fa-plus"></i> NOVO ALUNO
            </a>
        </header>

        <div class="list-container"
             hx-get="api/alunos.php?partial=list<?= (isset($_GET['filter']) && $_GET['filter'] == 'pendentes') ? '&filter=pendentes' : '' ?>"
             hx-trigger="load"
             hx-swap="innerHTML">
            <p style="text-align: center; color: #94a3b8; padding: 40px;">Carregando alunos...</p>
        </div>

    <?php elseif ($page == 'cadastro'): ?>
        <?php include 'cadastro_aluno.php'; ?>

    <?php elseif ($page == 'vivencia'): ?>
        <header class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <h1>Vivência e Saber</h1>
                <p style="color: #64748b;">Acervo de apoio para a formação do Capoeira.</p>
            </div>
            <?php if ($_SESSION['nivel'] === 'admin'): ?>
                <a href="views/admin_vivencia.php" class="btn-berimbau btn-primary" style="text-decoration: none; padding: 12px 24px;">
                    <i class="fas fa-plus-circle"></i> NOVO MATERIAL
                </a>
            <?php endif; ?>
        </header>

        <div class="vivencia-grid"
             style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;"
             hx-get="api/vivencia.php?partial=cards"
             hx-trigger="load"
             hx-swap="innerHTML">
            <p style="grid-column: 1 / -1; text-align: center; color: #94a3b8; padding: 40px;">Carregando acervo...</p>
        </div>

    <?php elseif ($page == 'competicao'): ?>
        <header class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <h1>Competições e Eventos</h1>
                <p style="color: #64748b;">Acompanhe campeonatos e torneios internos.</p>
            </div>
            <?php if ($_SESSION['nivel'] === 'admin'): ?>
                <a href="views/admin_competicao.php" class="btn-berimbau btn-primary" style="text-decoration: none; padding: 12px 24px;">
                    <i class="fas fa-calendar-plus"></i> NOVO EVENTO
                </a>
            <?php endif; ?>
        </header>

        <div class="competicao-grid"
             style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px;"
             hx-get="api/competicoes.php?partial=cards"
             hx-trigger="load"
             hx-swap="innerHTML">
            <p style="grid-column: 1 / -1; text-align: center; color: #94a3b8; padding: 40px;">Carregando competições...</p>
        </div>
    <?php endif; ?>
</main>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('collapsed');
}

(function initTheme() {
    const savedTheme = localStorage.getItem('berimbau-theme');
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
        document.body.classList.add('theme-dark');
    }
})();

function toggleTheme() {
    document.body.classList.toggle('theme-dark');
    localStorage.setItem('berimbau-theme', document.body.classList.contains('theme-dark') ? 'dark' : 'light');
}
</script>
</body>
</html>
