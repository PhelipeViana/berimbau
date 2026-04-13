<?php
// views/diario_view.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/funcoes_alunos.php';

$alunos = listarAlunos();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diário de Classe | BERIMBAU - Sistema de Gestão para Escolas de Capoeira</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { 
            --primary: #6366f1; --secondary: #0f172a; --bg: #f8fafc; 
            --sidebar-w: 260px; --sidebar-c: 80px; --border: #e2e8f0;
        }
        body { font-family: 'Inter', sans-serif; background: var(--bg); margin: 0; display: flex; height: 100vh; overflow: hidden; }
        
        /* REUTILIZANDO O ESTILO DA SUA SIDEBAR */
        .sidebar { width: var(--sidebar-w); background: var(--secondary); color: white; display: flex; flex-direction: column; flex-shrink: 0; }
        .sidebar-header { padding: 25px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #1e293b; }
        .menu-list { list-style: none; padding: 15px; flex-grow: 1; margin: 0; }
        .menu-item { text-decoration: none; color: #94a3b8; display: flex; align-items: center; padding: 12px 15px; border-radius: 12px; margin-bottom: 5px; font-size: 14px; }
        .menu-item:hover, .menu-item.active { background: rgba(99, 102, 241, 0.1); color: white; }
        .menu-item i { width: 25px; font-size: 18px; margin-right: 10px; }

        /* ÁREA DE CONTEÚDO (O DIÁRIO) */
        .main { flex: 1; overflow-y: auto; padding: 40px; }
        .card-glass { background: white; border-radius: 24px; padding: 30px; border: 1px solid var(--border); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #f8fafc; text-align: left; padding: 12px; border-bottom: 2px solid var(--border); font-size: 12px; color: #64748b; }
        td { padding: 12px; border-bottom: 1px solid var(--border); }
        .check-presenca { width: 20px; height: 20px; accent-color: var(--primary); }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-header">
        <span style="font-weight: 800; letter-spacing: -1px;">BERIMBAU<span style="color:var(--primary)">Sistema de Gestão para Escolas de Capoeira</span></span>
    </div>
    <nav class="menu-list">
        <a href="../index.php?page=dashboard" class="menu-item"><i class="fas fa-chart-pie"></i> <span>Dashboard</span></a>
        <a href="../index.php?page=lista" class="menu-item"><i class="fas fa-users"></i> <span>Alunos</span></a>
        <a href="diario_view.php" class="menu-item active"><i class="fas fa-book-open"></i> <span>Diário de Aula</span></a>
    </nav>
    <div style="padding: 20px; border-top: 1px solid #1e293b;">
        <a href="../logout.php" style="color: #ef4444; text-decoration:none; font-size: 13px; font-weight:700;">
            <i class="fas fa-sign-out-alt"></i> <span>SAIR</span>
        </a>
    </div>
</aside>

<main class="main">
    <div class="card-glass">
        <form action="../processar_diario.php" method="POST">
            <h2 style="margin-top:0">Novo Registro de Diário</h2>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                <div>
                    <label><strong>Data:</strong></label>
                    <input type="date" name="data_aula" value="<?= date('Y-m-d') ?>" style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border);">
                </div>
                <div>
                    <label><strong>Local:</strong></label>
                    <input type="text" name="local_treino" placeholder="Ex: E.E. Prof. José Mendes" required style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border);">
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ALUNO</th>
                        <th>APELIDO</th>
                        <th style="text-align: center;">PRESENÇA</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alunos as $aluno): ?>
                    <tr>
                        <td><strong><?= strtoupper($aluno['nome']) ?></strong></td>
                        <td style="color:var(--primary); font-weight:700;"><?= $aluno['apelido'] ?? '-' ?></td>
                        <td style="text-align: center;">
                            <input type="checkbox" name="presentes[]" value="<?= $aluno['id'] ?>" class="check-presenca" checked>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="margin-top: 25px;">
                <label><strong>Tema da Aula:</strong></label>
                <input type="text" name="tema_aula" required style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border); margin-bottom:15px;">
                
                <label><strong>Descrição Detalhada:</strong></label>
                <textarea name="descricao_atividades" rows="4" style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--border);"></textarea>
            </div>

            <button type="submit" style="background:var(--primary); color:white; border:none; padding:15px; border-radius:12px; width:100%; font-weight:700; margin-top:20px; cursor:pointer;">
                <i class="fas fa-save"></i> SALVAR E GERAR DOCUMENTO
            </button>
        </form>
    </div>
</main>

</body>
</html>