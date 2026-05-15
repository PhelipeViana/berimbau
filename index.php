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

// 5. CARREGAMENTO DE DADOS PARA A VIEW
$alunos = listarAlunos();
$stats = obterEstatisticas(); 

$solicitacoes = array_filter($alunos, function($a) {
    return empty($a['docente_id']) || $a['docente_id'] == 0; 
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
    <style>
        .card-vivencia {
            background: white; padding: 25px; border-radius: 20px; border: 1px solid #e2e8f0; 
            transition: all 0.3s ease; display: flex; flex-direction: column; justify-content: space-between;
        }
        .card-vivencia:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.08); border-color: var(--primary); }
        .icon-vivencia {
            background: #f1f5f9; width: 50px; height: 50px; border-radius: 12px; 
            display: flex; align-items: center; justify-content: center; margin-bottom: 15px;
        }
        .badge-pendente {
            background: #ef4444; color: white; padding: 2px 8px; border-radius: 10px; font-size: 10px; margin-left: 5px;
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
            <a href="?page=lista" class="menu-item <?= ($page == 'lista' || (isset($_GET['pendentes']))) ? 'active' : '' ?>">
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

        <?php if ($_SESSION['nivel'] === 'admin'): ?>
            <a href="usuarios.php" class="menu-item">
                <i class="fas fa-user-shield"></i> <span>Operadores</span>
            </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer" style="padding: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
        <div class="user-info" style="margin-bottom: 15px;">
            <small style="display: block; color: #94a3b8; font-size: 10px; letter-spacing: 1px;">USUÁRIO CONECTADO</small>
            <strong style="color: white; font-size: 13px;"><?= strtoupper($_SESSION['usuario']) ?></strong>
        </div>
        <a href="logout.php" class="btn-logout" style="display: flex; align-items: center; justify-content: center; gap: 10px; background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 12px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 12px; transition: all 0.3s ease; border: 1px solid rgba(239, 68, 68, 0.2);">
            <i class="fas fa-sign-out-alt"></i> <span>SAIR DO SISTEMA</span>
        </a>
    </div>
</aside>

<main class="main-content">
    <?php if ($page == 'dashboard'): ?>
        <header class="content-header">
            <h1>Olá, <?= explode(' ', $_SESSION['usuario'])[0] ?>! 👋</h1>
            <p>Bem-vindo ao sistema da sua escola.</p>
        </header>

        <?php if ($_SESSION['nivel'] === 'admin' && count($solicitacoes) > 0): ?>
            <div class="alerta-solicitacao" style="background: #fff5f5; border-left: 6px solid #ef4444; padding: 20px; border-radius: 16px; margin-bottom: 30px; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.1);">
                <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                    <div style="background: #ef4444; color: white; width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-user-plus" style="font-size: 20px;"></i>
                    </div>
                    <div>
                        <h3 style="margin:0; color: #991b1b; font-size: 18px;">Cadastros Externos Pendentes</h3>
                        <p style="margin:0; color: #b91c1c; font-size: 14px;">Existem <strong><?= count($solicitacoes) ?></strong> solicitações aguardando vínculo de docente.</p>
                    </div>
                </div>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <?php foreach(array_slice($solicitacoes, 0, 4) as $sol): ?>
                        <a href="?edit=<?= $sol['id'] ?>" style="text-decoration:none; background: white; padding: 10px 18px; border-radius: 10px; border: 1px solid #fee2e2; font-size: 13px; color: #444; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-user-check" style="color: #ef4444;"></i> <?= strtoupper($sol['nome']) ?>
                        </a>
                    <?php endforeach; ?>
                    <a href="?page=lista&filter=pendentes" style="text-decoration:none; background: #ef4444; color: white; padding: 10px 18px; border-radius: 10px; font-size: 13px; font-weight: bold; margin-left: auto;">VER TODAS</a>
                </div>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <?php if ($_SESSION['nivel'] === 'aluno'): 
                $frequencia = obterFrequenciaAluno($_SESSION['usuario_id']); 
            ?>
                <div class="card-estatistica">
                    <div class="icon-box bg-success"><i class="fas fa-check-circle"></i></div>
                    <div><h2><?= $frequencia['presencas'] ?></h2><small>PRESENÇAS</small></div>
                </div>
                <div class="card-estatistica">
                    <div class="icon-box bg-info"><i class="fas fa-percentage"></i></div>
                    <div><h2><?= $frequencia['aproveitamento'] ?>%</h2><small>FREQUÊNCIA</small></div>
                </div>
            <?php else: ?>
                <div class="card-estatistica">
                    <div class="icon-box bg-info"><i class="fas fa-user-graduate"></i></div>
                    <div><h2><?= $stats['total'] ?></h2><small>ALUNOS ATIVOS</small></div>
                </div>
                <div class="card-estatistica">
                    <div class="icon-box bg-danger"><i class="fas fa-heartbeat"></i></div>
                    <div><h2><?= $stats['alertas_saude'] ?></h2><small>ALERTAS SAÚDE</small></div>
                </div>
                <div class="card-estatistica">
                    <div class="icon-box bg-warning"><i class="fas fa-calendar-check"></i></div>
                    <div><h2><?= $stats['aulas_mes'] ?? 0 ?></h2><small>AULAS NO MÊS</small></div>
                </div>
            <?php endif; ?>
        </div>

        <div class="aviso-box">
            <h4>Avisos da Escola</h4>
            <p>Mantenha os registros de graduação atualizados para gerar os certificados corretamente.</p>
        </div>

    <?php elseif ($page == 'lista'): ?>
        <header class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <h1 style="margin:0;">
                    <?= (isset($_GET['filter']) && $_GET['filter'] == 'pendentes') ? 'Solicitações de Cadastro' : 'Relação de Alunos' ?>
                </h1>
                <p style="margin:0; color: #64748b;">Gerencie os integrantes da sua escola</p>
            </div>
            <a href="?page=cadastro" class="btn-berimbau btn-primary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px;">
                <i class="fas fa-plus"></i> NOVO ALUNO
            </a>
        </header>

        <div class="list-container">
            <?php 
            $exibir_alunos = (isset($_GET['filter']) && $_GET['filter'] == 'pendentes') ? $solicitacoes : $alunos;
            
            if(empty($exibir_alunos)): ?>
                <div style="text-align:center; padding:50px; color:#94a3b8;">Nenhum registro encontrado.</div>
            <?php endif;

            foreach ($exibir_alunos as $a): ?>
                <div class="aluno-card" style="<?= (empty($a['docente_id'])) ? 'border-left: 4px solid #ef4444;' : '' ?>">
                    <img src="uploads/<?= $a['foto'] ?? 'padrao.png' ?>" class="avatar-circle">
                    <div class="aluno-info">
                        <strong><?= strtoupper($a['nome']) ?></strong>
                        <span class="grad-tag"><?= $a['graduacao'] ?></span>
                        <?php if(empty($a['docente_id'])): ?>
                            <small style="color:#ef4444; font-weight:bold; display:block; margin-top:5px;">AGUARDANDO DOCENTE</small>
                        <?php endif; ?>
                    </div>
                    <div class="aluno-actions">
                        <a href="?edit=<?= $a['id'] ?>" class="btn-edit" title="Editar / Autorizar"><i class="fas fa-edit"></i></a>
                        <a href="?delete=<?= $a['id'] ?>" class="btn-delete" onclick="return confirm('Excluir aluno?')"><i class="fas fa-trash"></i></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div> 

    <?php elseif ($page == 'cadastro'): ?>
        <?php include 'cadastro_aluno.php'; ?>

    <?php elseif ($page == 'vivencia'): ?>
        <?php 
            $sql_vivencia = "SELECT * FROM vivencia ORDER BY categoria, titulo";
            $conteudos = $pdo->query($sql_vivencia)->fetchAll();
        ?>
        <header class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <h1 style="margin:0;">Vivência e Saber 👋</h1>
                <p style="margin:0; color: #64748b;">Acervo de apoio para a formação do Capoeira.</p>
            </div>
            <?php if ($_SESSION['nivel'] === 'admin'): ?>
                <a href="views/admin_vivencia.php" class="btn-berimbau btn-primary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; background-color: #1e293b;">
                    <i class="fas fa-plus-circle"></i> NOVO MATERIAL
                </a>
            <?php endif; ?>
        </header>

        <div class="vivencia-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
            <?php if (count($conteudos) > 0): ?>
                <?php foreach ($conteudos as $item): ?>
                    <div class="card-vivencia">
                        <div>
                            <div class="icon-vivencia">
                                <?php 
                                    if($item['categoria'] == 'historia') echo '<i class="fas fa-history" style="font-size: 24px; color: var(--primary);"></i>';
                                    elseif($item['categoria'] == 'musica') echo '<i class="fas fa-music" style="font-size: 24px; color: #10b981;"></i>';
                                    else echo '<i class="fas fa-scroll" style="font-size: 24px; color: #f59e0b;"></i>';
                                ?>
                            </div>
                            <h3><?= htmlspecialchars($item['titulo']) ?></h3>
                            <p style="color: #64748b; font-size: 14px; line-height: 1.5;"><?= htmlspecialchars($item['descricao']) ?></p>
                        </div>
                        <a href="<?= $item['url_conteudo'] ?>" target="_blank" class="btn-berimbau btn-primary" style="display: block; text-align: center; text-decoration: none; margin-top: 20px;">
                            <?php 
                                if($item['tipo'] == 'pdf') echo 'ACESSAR PDF';
                                elseif($item['tipo'] == 'audio') echo 'OUVIR ÁUDIO';
                                else echo 'ACESSAR LINK';
                            ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #94a3b8;">
                    <i class="fas fa-folder-open" style="font-size: 40px; margin-bottom: 10px;"></i>
                    <p>Nenhum conteúdo cadastrado no acervo ainda.</p>
                </div>
            <?php endif; ?>
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