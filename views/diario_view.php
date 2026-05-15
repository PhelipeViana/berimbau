<?php
// views/diario_view.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/funcoes_alunos.php';

// Apenas Docentes e Admins acessam
if (!isset($_SESSION['usuario']) || $_SESSION['nivel'] === 'aluno') {
    header("Location: ../index.php");
    exit;
}

$alunos = listarAlunos();
$diario_edicao = null;
$usuario_id_logado = $_SESSION['usuario_id'];
$nivel_logado = $_SESSION['nivel'];

// Lógica para carregar dados em caso de edição
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $sql_edit = ($nivel_logado === 'admin') 
        ? "SELECT * FROM aulas WHERE id = ?" 
        : "SELECT * FROM aulas WHERE id = ? AND docente_id = ?";
    
    $params_edit = ($nivel_logado === 'admin') ? [$_GET['edit']] : [$_GET['edit'], $usuario_id_logado];
    
    $stmt = $pdo->prepare($sql_edit);
    $stmt->execute($params_edit);
    $diario_edicao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$diario_edicao && isset($_GET['edit'])) {
        header("Location: diario_view.php?msg=permissao_negada");
        exit;
    }
}

// Filtro de Privacidade
if ($nivel_logado === 'admin') {
    $sql_recentes = "SELECT a.*, u.usuario as docente_nome 
                     FROM aulas a 
                     LEFT JOIN usuarios u ON a.docente_id = u.id 
                     ORDER BY a.data_aula DESC LIMIT 20";
    $stmt_recentes = $pdo->query($sql_recentes);
} else {
    $sql_recentes = "SELECT a.*, u.usuario as docente_nome 
                     FROM aulas a 
                     LEFT JOIN usuarios u ON a.docente_id = u.id 
                     WHERE a.docente_id = ? 
                     ORDER BY a.data_aula DESC LIMIT 20";
    $stmt_recentes = $pdo->prepare($sql_recentes);
    $stmt_recentes->execute([$usuario_id_logado]);
}

$diarios_recentes = $stmt_recentes->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diário de Classe | BERIMBAU</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="../assets/css/estilo_padrao.css">

    <style>
    /* AQUI SÓ FICA O QUE É EXCLUSIVO DO DIÁRIO */
    .main { flex: 1; overflow-y: auto; padding: 40px; }
    .card-glass { background: white; border-radius: 24px; padding: 30px; border: 1px solid #e2e8f0; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); margin-bottom: 30px; }
    
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    th { background: #f8fafc; text-align: left; padding: 12px; border-bottom: 2px solid #e2e8f0; font-size: 12px; color: #64748b; }
    td { padding: 12px; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
    
    /* CORREÇÃO DOS CONTORNOS DOS CAMPOS */
    input[type="text"], 
    input[type="date"], 
    textarea { 
        width: 100%; 
        padding: 12px; 
        border-radius: 10px; 
        border: 2px solid #cbd5e1; /* Borda mais nítida (cinza médio) */
        background-color: #ffffff;
        box-sizing: border-box; 
        font-family: inherit;
        font-size: 14px;
        color: #1e293b;
        outline: none;
        transition: all 0.2s ease;
        margin-top: 5px;
    }

    /* Efeito ao clicar no campo para digitar */
    input[type="text"]:focus, 
    input[type="date"]:focus, 
    textarea:focus { 
        border-color: var(--primary); /* Usa o azul do sistema ao focar */
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); /* Brilho suave ao redor */
        background-color: #fff;
    }

    label {
        font-weight: 700;
        font-size: 12px;
        color: #475569;
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

    .actions-container { display: flex; align-items: center; gap: 20px; margin-top: 25px; padding-top: 15px; border-top: 1px solid #e2e8f0; }
</style>
</head>
<body>

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
    <div class="card-glass">
        <form action="../processar_diario.php" method="POST">
            <input type="hidden" name="id_aula" value="<?= $diario_edicao['id'] ?? '' ?>">
            <h2 style="margin-top:0"><?= $diario_edicao ? 'Editar Registro' : 'Novo Registro de Diário' ?></h2>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label>Data:</label>
                    <input type="date" name="data_aula" value="<?= $diario_edicao['data_aula'] ?? date('Y-m-d') ?>">
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
                            <input type="checkbox" name="presentes[]" value="<?= $aluno['id'] ?>" checked>
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
                    <tr>
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
                            <a href="../excluir_diario.php?id=<?= $row['id'] ?>" class="btn-delete" 
                               onclick="return confirm('Tem certeza?')">
                                <i class="fas fa-trash-alt"></i> Excluir
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</body>
</html>