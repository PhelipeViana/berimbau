<?php
// views/diario_view.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/funcoes_alunos.php';
require_once __DIR__ . '/../api/services/aulas.php';

// Apenas Docentes e Admins acessam
if (!isset($_SESSION['usuario']) || $_SESSION['nivel'] === 'aluno') {
    header("Location: ../index.php");
    exit;
}

$alunos = listarAlunos();
$diario_edicao = null;
$usuario_id_logado = $_SESSION['usuario_id'];
$nivel_logado = $_SESSION['nivel'];
$presentes_ids = [];

// Lógica para carregar dados em caso de edição
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $diario_edicao = api_aula_buscar_edicao($_GET['edit'], $nivel_logado, $usuario_id_logado);

    if (!$diario_edicao && isset($_GET['edit'])) {
        header("Location: diario_view.php?msg=permissao_negada");
        exit;
    }

    if ($diario_edicao) {
        $presentes_ids = array_column(api_aula_presencas($diario_edicao['id']), 'id');
    }
}

$diarios_recentes = api_aulas_recentes($nivel_logado, $usuario_id_logado);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diário de Classe | BERIMBAU</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="../assets/css/estilo_padrao.css">
    <script src="https://unpkg.com/htmx.org@1.9.12" defer></script>

    <style>
    /* AQUI SÓ FICA O QUE É EXCLUSIVO DO DIÁRIO */
    .main { flex: 1; overflow-y: auto; padding: 40px; }
    .card-glass { padding: 30px; margin-bottom: 30px; }
    
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    th { text-align: left; padding: 12px; border-bottom: 2px solid var(--border-color); font-size: 12px; }
    td { padding: 12px; border-bottom: 1px solid var(--border-color); font-size: 14px; }
    
    /* CORREÇÃO DOS CONTORNOS DOS CAMPOS */
    input[type="text"], 
    .input-date-br,
    textarea { 
        width: 100%; 
        padding: 12px; 
        border-radius: 10px; 
        border: 2px solid var(--border-color); 
        background-color: var(--surface-strong);
        box-sizing: border-box; 
        font-family: inherit;
        font-size: 14px;
        color: var(--text-main);
        outline: none;
        transition: all 0.2s ease;
        margin-top: 5px;
    }

    /* Efeito ao clicar no campo para digitar */
    input[type="text"]:focus, 
    .input-date-br:focus,
    textarea:focus { 
        border-color: var(--primary); /* Usa a cor primária ao focar */
        box-shadow: 0 0 0 4px rgba(15, 122, 58, 0.12); /* Brilho suave ao redor */
    }

    label {
        font-weight: 700;
        font-size: 12px;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .btn-submit { 
        border: none; padding: 12px 25px; border-radius: 10px; 
        font-size: 14px; font-weight: 600; cursor: pointer; color: white; 
        display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s;
        margin-top: 10px;
    }
    .btn-submit:hover { filter: brightness(1.1); transform: translateY(-1px); }

    .actions-container { display: flex; align-items: center; gap: 20px; margin-top: 25px; padding-top: 15px; border-top: 1px solid var(--border-color); }

    /* Correção para evitar que os botões de texto fiquem espremidos com 42px fixos */
    .history-section .btn-edit, 
    .history-section .btn-delete {
        width: auto !important;
        padding: 0 15px !important;
    }
    
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
            <b>BERIMBAU<span style="color:var(--primary)"></span></b>
            <small>GESTÃO PARA ESCOLAS DE CAPOEIRA</small>
        </div>
    </div>
    <nav class="menu-list">
        <a href="../index.php?page=dashboard" class="menu-item"><i class="fas fa-chart-pie"></i> <span>Dashboard</span></a>
        
        <?php if ($nivel_logado !== 'aluno'): ?>
            <a href="../index.php?page=lista" class="menu-item"><i class="fas fa-users"></i> <span>Alunos</span></a>
            <a href="diario_view.php" class="menu-item active"><i class="fas fa-book-open"></i> <span>Diário de Aula</span></a>
        <?php endif; ?>

        <a href="../index.php?page=vivencia" class="menu-item"><i class="fas fa-book-reader"></i> <span>Vivência</span></a>

        <?php if ($nivel_logado === 'admin'): ?>
            <a href="../usuarios.php" class="menu-item"><i class="fas fa-user-shield"></i> <span>Operadores</span></a>
        <?php endif; ?>
    </nav>
</aside>

<main class="main">
    <?php if(isset($_GET['msg'])): ?>
        <?php if($_GET['msg'] == 'sucesso'): ?>
            <div class="msg-alerta">Diário registrado com sucesso!</div>
        <?php elseif($_GET['msg'] == 'excluido'): ?>
            <div class="msg-alerta">Registro excluído com sucesso!</div>
        <?php elseif($_GET['msg'] == 'permissao_negada'): ?>
            <div class="msg-alerta" style="background: rgba(195, 56, 45, 0.12); color: var(--danger); border-color: rgba(195, 56, 45, 0.22);">Permissão negada para realizar esta ação.</div>
        <?php endif; ?>
    <?php endif; ?>
    <div class="card-glass">
        <form action="../api/aulas.php" method="POST">
            <input type="hidden" name="id_aula" value="<?= $diario_edicao['id'] ?? '' ?>">
            <h2 style="margin-top:0"><?= $diario_edicao ? 'Editar Registro' : 'Novo Registro de Diário' ?></h2>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label>Data:</label>
                    <input type="text" name="data_aula" class="input-date-br" inputmode="numeric" maxlength="10" pattern="\d{2}/\d{2}/\d{4}" placeholder="DD/MM/AAAA" value="<?= formatarDataBr($diario_edicao['data_aula'] ?? date('Y-m-d')) ?>">
                </div>
                <div>
                    <label>Local:</label>
                    <input type="text" name="local_treino" value="<?= $diario_edicao['local_treino'] ?? '' ?>" required>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ALUNO</th>
                        <th style="text-align: center;">PRESENÇA</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alunos as $aluno): ?>
                    <tr>
                        <td><strong><?= strtoupper($aluno['nome']) ?></strong></td>
                        <td style="text-align: center;">
                            <input type="checkbox" name="presentes[]" value="<?= $aluno['id'] ?>" <?= (!$diario_edicao || in_array($aluno['id'], $presentes_ids)) ? 'checked' : '' ?>>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="margin-top: 20px;">
                <label>Tema da Aula:</label>
                <input type="text" name="tema_aula" value="<?= $diario_edicao['tema_aula'] ?? '' ?>" required style="margin-bottom:15px;">
                
                <label>Descrição:</label>
                <textarea name="descricao_atividades" rows="3"><?= $diario_edicao['descricao_atividades'] ?? '' ?></textarea>
            </div>

            <div class="actions-container">
                <button type="submit" class="btn-submit" style="background: <?= $diario_edicao ? 'var(--warning)' : 'var(--primary)' ?>;">
                    <i class="fas fa-save"></i> 
                    <?= $diario_edicao ? 'Atualizar Registro' : 'Salvar Diário' ?>
                </button>
                
                <?php if($diario_edicao): ?>
                    <a href="diario_view.php" style="color:#64748b; text-decoration:none; font-size:13px; font-weight:500;">
                        <i class="fas fa-times"></i> Cancelar Edição
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="history-section">
        <h3><i class="fas fa-history"></i> <?= $nivel_logado === 'admin' ? 'Todos os Registros' : 'Meus Registros Recentes' ?></h3>
        <div class="card-glass" style="padding: 10px 20px;">
            <table>
                <thead>
                    <tr>
                        <th>DATA</th>
                        <th>TEMA</th>
                        <?php if($nivel_logado === 'admin'): ?><th>MESTRE/DOCENTE</th><?php endif; ?>
                        <th>LOCAL</th>
                        <th>AÇÕES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($diarios_recentes as $row): ?>
                    <tr id="aula-row-<?= (int) $row['id'] ?>">
                        <td><?= date('d/m/Y', strtotime($row['data_aula'])) ?></td>
                        <td><strong><?= $row['tema_aula'] ?></strong></td>
                        <?php if($nivel_logado === 'admin'): ?>
                            <td><span class="badge-docente"><?= strtoupper($row['docente_nome'] ?? 'N/A') ?></span></td>
                        <?php endif; ?>
                        <td><?= $row['local_treino'] ?></td>
                        <td style="white-space: nowrap;">
                            <a href="diario_view.php?edit=<?= $row['id'] ?>" class="btn-edit">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <form action="../api/aulas.php"
                                  method="POST"
                                  style="display:inline;"
                                  hx-post="../api/aulas.php"
                                  hx-target="#aula-row-<?= (int) $row['id'] ?>"
                                  hx-swap="outerHTML"
                                  hx-confirm="Excluir este registro de diário?">
                                <input type="hidden" name="_method" value="DELETE">
                                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                <button type="submit" class="btn-delete" style="border:0; cursor:pointer;">
                                    <i class="fas fa-trash-alt"></i> Excluir
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<script src="../assets/js/datas.js"></script>
</body>
</html>
