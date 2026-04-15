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

// Lógica para carregar dados em caso de edição
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM aulas WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $diario_edicao = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Função para listar os diários recentes
$sql_recentes = "SELECT a.*, u.usuario as docente_nome 
                 FROM aulas a 
                 LEFT JOIN usuarios u ON a.docente_id = u.id 
                 ORDER BY a.data_aula DESC LIMIT 10";
$diarios_recentes = $pdo->query($sql_recentes)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diário de Classe | BERIMBAU</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { 
            --primary: #6366f1; --secondary: #0f172a; --bg: #f8fafc; 
            --border: #e2e8f0; --warning: #f59e0b; --danger: #ef4444;
        }
        body { font-family: 'Inter', sans-serif; background: var(--bg); margin: 0; display: flex; height: 100vh; overflow: hidden; }
        
        .sidebar { width: 260px; background: var(--secondary); color: white; display: flex; flex-direction: column; flex-shrink: 0; }
        .sidebar-header { padding: 20px; border-bottom: 1px solid #1e293b; min-height: 80px; }
        .logo-text b { font-size: 20px; display: block; }
        .logo-text small { font-size: 9px; color: #94a3b8; display: block; text-transform: uppercase; }

        .menu-list { list-style: none; padding: 15px; flex-grow: 1; }
        .menu-item { text-decoration: none; color: #94a3b8; display: flex; align-items: center; padding: 12px 15px; border-radius: 12px; margin-bottom: 5px; font-size: 14px; }
        .menu-item.active { background: rgba(99, 102, 241, 0.1); color: white; }
        .menu-item i { width: 25px; margin-right: 10px; }

        .main { flex: 1; overflow-y: auto; padding: 40px; }
        .card-glass { background: white; border-radius: 24px; padding: 30px; border: 1px solid var(--border); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); margin-bottom: 30px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #f8fafc; text-align: left; padding: 12px; border-bottom: 2px solid var(--border); font-size: 12px; color: #64748b; }
        td { padding: 12px; border-bottom: 1px solid var(--border); font-size: 14px; }
        
        input[type="text"], input[type="date"], textarea { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); box-sizing: border-box; }
        
        .btn-submit { 
            border: none; 
            padding: 10px 25px; 
            border-radius: 8px; 
            width: auto; 
            font-size: 14px; 
            font-weight: 600; 
            cursor: pointer; 
            color: white; 
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.2s;
        }

        .btn-submit:hover { filter: brightness(1.1); transform: translateY(-1px); box-shadow: 0 4px 6px rgba(0,0,0,0.1); }

        .actions-container {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px solid var(--border);
        }
        
        .history-section { margin-top: 50px; }
        .btn-edit { color: var(--primary); text-decoration: none; font-weight: bold; font-size: 13px; margin-right: 15px; }
        .btn-delete { color: var(--danger); text-decoration: none; font-weight: bold; font-size: 13px; }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo-text">
            <b>BERIMBAU<span style="color:var(--primary)">.it</span></b>
            <small>Gestão de Capoeira</small>
        </div>
    </div>
    <nav class="menu-list">
        <a href="../index.php?page=dashboard" class="menu-item"><i class="fas fa-chart-pie"></i> <span>Dashboard</span></a>
        <a href="../index.php?page=lista" class="menu-item"><i class="fas fa-users"></i> <span>Alunos</span></a>
        <a href="diario_view.php" class="menu-item active"><i class="fas fa-book-open"></i> <span>Diário de Aula</span></a>
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
        <h3><i class="fas fa-history"></i> Registros Recentes</h3>
        <div class="card-glass" style="padding: 10px 20px;">
            <table>
                <thead>
                    <tr>
                        <th>DATA</th>
                        <th>TEMA</th>
                        <th>LOCAL</th>
                        <th>AÇÕES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($diarios_recentes as $row): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($row['data_aula'])) ?></td>
                        <td><strong><?= $row['tema_aula'] ?></strong></td>
                        <td><?= $row['local_treino'] ?></td>
                        <td style="white-space: nowrap;">
                            <a href="diario_view.php?edit=<?= $row['id'] ?>" class="btn-edit">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <a href="../excluir_diario.php?id=<?= $row['id'] ?>" class="btn-delete" 
                               onclick="return confirm('Tem certeza que deseja excluir permanentemente este diário e todas as presenças registradas nele?')">
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