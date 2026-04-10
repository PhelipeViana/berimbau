<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/funcoes_alunos.php';

// 1. PROTEÇÃO: Apenas admins podem ver o histórico detalhado
if (!isset($_SESSION['usuario']) || $_SESSION['nivel'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// 2. BUSCAR DADOS
$id_aluno = $_GET['id'] ?? null;
if (!$id_aluno) {
    header("Location: index.php");
    exit;
}

$aluno = buscarAlunoPorId($id_aluno);
$historico = buscarHistorico($id_aluno); // Certifique-se que esta função está no funcoes_alunos.php
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Graduações - <?= $aluno['nome'] ?></title>
    <style>
        :root { 
            --primary: #475569; --secondary: #0f172a; --bg: #f1f5f9; 
            --border: #e2e8f0; --purple: #8b5cf6;
        }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: var(--bg); color: var(--secondary); margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        
        .header { display: flex; align-items: center; gap: 20px; border-bottom: 2px solid var(--purple); padding-bottom: 20px; margin-bottom: 30px; }
        .foto-perfil { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid var(--purple); }
        
        .timeline { position: relative; padding-left: 30px; border-left: 2px solid var(--border); margin-left: 15px; }
        .timeline-item { position: relative; margin-bottom: 30px; }
        .timeline-item::before { 
            content: ''; position: absolute; left: -39px; top: 5px; 
            width: 16px; height: 16px; border-radius: 50%; background: var(--purple); border: 4px solid white;
        }
        
        .data { font-size: 12px; font-weight: bold; color: #64748b; text-transform: uppercase; }
        .grad-card { background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid var(--border); margin-top: 5px; }
        .grad-change { display: flex; align-items: center; gap: 10px; font-weight: bold; }
        .arrow { color: var(--purple); }
        .obs { font-size: 13px; color: #64748b; margin-top: 8px; font-style: italic; }

        .btn-voltar { display: inline-block; margin-bottom: 20px; text-decoration: none; color: var(--primary); font-weight: bold; font-size: 14px; }
    </style>
</head>
<body>

<div class="container">
    <a href="index.php" class="btn-voltar">⬅ VOLTAR AO SISTEMA</a>

    <div class="header">
        <img src="uploads/<?= $aluno['foto'] ?? 'padrao.png' ?>" class="foto-perfil">
        <div>
            <h1 style="margin:0; font-size: 24px;"><?= strtoupper($aluno['nome']) ?></h1>
            <p style="margin:5px 0; color: #64748b;">Histórico de Evolução de Cordas</p>
        </div>
    </div>

    <?php if (empty($historico)): ?>
        <p style="text-align: center; color: #64748b; padding: 40px;">
            Nenhuma troca de graduação registada para este aluno ainda.
        </p>
    <?php else: ?>
        <div class="timeline">
            <?php foreach ($historico as $item): ?>
                <div class="timeline-item">
                    <div class="data"><?= date('d/m/Y', strtotime($item['data_mudanca'])) ?></div>
                    <div class="grad-card">
                        <div class="grad-change">
                            <span style="color: #94a3b8;"><?= $item['graduacao_anterior'] ?></span>
                            <span class="arrow">➔</span>
                            <span style="color: var(--secondary);"><?= $item['graduacao_nova'] ?></span>
                        </div>
                        <?php if($item['observacao']): ?>
                            <div class="obs">"<?= htmlspecialchars($item['observacao']) ?>"</div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

</body>
</html>