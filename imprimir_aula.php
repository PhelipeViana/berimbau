<?php
require_once 'includes/auth.php';
require_once 'includes/funcoes_alunos.php';
require_once __DIR__ . '/api/services/aulas.php';

$aula_id = $_GET['id'] ?? null;
if (!$aula_id) die("Aula não encontrada.");

$aula = api_aula_buscar_com_docente($aula_id);
$presentes = api_aula_presencas($aula_id, true);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Aula - <?= date('d/m/Y', strtotime($aula['data_aula'])) ?></title>
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; line-height: 1.4; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px; }
        .box { border: 1px solid #ccc; padding: 10px; margin-bottom: 10px; border-radius: 5px; }
        .label { font-weight: bold; font-size: 12px; color: #666; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 8px; text-align: left; font-size: 13px; }
        th { background: #f4f4f4; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">🖨️ Imprimir Diário</button>
        <a href="index.php?page=diario">Voltar</a>
    </div>

    <div class="header">
        <h2>RELATÓRIO DE ATIVIDADES - CAPOEIRA</h2>
        <p>Data: <?= date('d/m/Y H:i', strtotime($aula['data_aula'])) ?></p>
    </div>

    <div class="info-grid">
        <div class="box"><span class="label">Docente:</span><br><?= $aula['docente_nome'] ?></div>
        <div class="box"><span class="label">Local:</span><br><?= $aula['local_treino'] ?></div>
    </div>

    <div class="box">
        <span class="label">Tema da Aula:</span><br>
        <strong><?= $aula['tema_aula'] ?></strong>
    </div>

    <div class="box">
        <span class="label">Conteúdo Desenvolvido:</span><br>
        <?= nl2br($aula['descricao_atividades']) ?>
    </div>

    <h3>Lista de Presença (<?= count($presentes) ?> alunos)</h3>
    <table>
        <thead>
            <tr>
                <th>Nome do Aluno</th>
                <th>Apelido</th>
                <th>Graduação</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($presentes as $p): ?>
            <tr>
                <td><?= $p['nome'] ?></td>
                <td><?= $p['apelido'] ?></td>
                <td><?= $p['graduacao'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="margin-top: 50px; text-align: center;">
        <div style="border-top: 1px solid #000; width: 300px; margin: auto; padding-top: 5px;">
            Assinatura do Responsável
        </div>
    </div>
</body>
</html>
