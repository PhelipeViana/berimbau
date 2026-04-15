<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/funcoes_alunos.php';

// Apenas Docentes e Admins veem a lista completa
if (!isset($_SESSION['usuario']) || $_SESSION['nivel'] === 'aluno') {
    header("Location: ../index.php");
    exit;
}

// Função para buscar todas as aulas (você pode mover para funcoes_alunos.php depois)
function listarAulas($pdo) {
    $sql = "SELECT a.*, u.usuario as docente_nome 
            FROM aulas a 
            LEFT JOIN usuarios u ON a.docente_id = u.id 
            ORDER BY a.data_aula DESC";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

$aulas = listarAulas($pdo);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Histórico de Diários | BERIMBAU</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --primary: #6366f1; --secondary: #0f172a; --bg: #f8fafc; --border: #e2e8f0; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); padding: 40px; }
        .card { background: white; border-radius: 20px; padding: 30px; border: 1px solid var(--border); box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { text-align: left; padding: 15px; background: #f1f5f9; color: #64748b; font-size: 13px; }
        td { padding: 15px; border-bottom: 1px solid var(--border); font-size: 14px; }
        .btn-edit { color: var(--primary); text-decoration: none; font-weight: bold; }
        .btn-back { display: inline-block; margin-bottom: 20px; text-decoration: none; color: #64748b; font-weight: 600; }
    </style>
</head>
<body>

<div class="card">
    <a href="../index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Voltar ao Painel</a>
    <h2><i class="fas fa-history"></i> Histórico de Diários de Aula</h2>

    <table>
        <thead>
            <tr>
                <th>DATA</th>
                <th>TEMA DA AULA</th>
                <th>LOCAL</th>
                <th>DOCENTE</th>
                <th>AÇÕES</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($aulas as $aula): ?>
            <tr>
                <td><?= date('d/m/Y', strtotime($aula['data_aula'])) ?></td>
                <td><strong><?= $aula['tema_aula'] ?></strong></td>
                <td><?= $aula['local_treino'] ?></td>
                <td><?= $aula['docente_nome'] ?></td>
                <td>
                    <a href="diario_view.php?edit=<?= $aula['id'] ?>" class="btn-edit">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>