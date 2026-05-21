<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/funcoes_alunos.php';
require_once __DIR__ . '/api/services/aulas.php';

$aula_id = $_GET['id'] ?? null;

if (!$aula_id) {
    die("Aula não encontrada.");
}

$aula = api_aula_buscar_com_docente($aula_id);
$presencas = api_aula_presencas($aula_id);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Diário de Classe - Visualização</title>
    <style>
        body { font-family: sans-serif; padding: 40px; color: #333; line-height: 1.6; }
        .folha { max-width: 800px; margin: auto; border: 2px solid #000; padding: 20px; position: relative; }
        .header { text-align: center; border-bottom: 2px solid #000; margin-bottom: 20px; padding-bottom: 10px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background: #f2f2f2; }
        .assinatura { margin-top: 50px; text-align: center; border-top: 1px solid #000; width: 300px; margin-left: auto; margin-right: auto; }
        @media print { .btn-print { display: none; } }
    </style>
</head>
<body>

<div class="folha">
    <button class="btn-print" onclick="window.print()" style="float: right;">Imprimir PDF</button>
    
    <div class="header">
        <h2>PROJETO COLETIVOS – CICLO 4</h2>
        <p>DIÁRIO DE ATIVIDADES DE CAPOEIRA</p>
    </div>

    <div class="info-grid">
        <div><strong>DOCENTE:</strong> <?= $aula['nome_docente'] ?></div>
        <div><strong>DATA:</strong> <?= date('d/m/Y', strtotime($aula['data_aula'])) ?></div>
        <div><strong>LOCAL:</strong> <?= $aula['local_treino'] ?></div>
        <div><strong>TEMA:</strong> <?= $aula['tema_aula'] ?></div>
    </div>

    <p><strong>DESCRIÇÃO DA ATIVIDADE:</strong><br> <?= nl2br($aula['descricao_atividades']) ?></p>

    <h3>LISTA DE PRESENÇA (<?= count($presencas) ?> PRESENTES)</h3>
    <table>
        <thead>
            <tr>
                <th width="50">Nº</th>
                <th>NOME DO ALUNO</th>
                <th>APELIDO</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($presencas as $i => $p): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= strtoupper($p['nome']) ?></td>
                <td><?= strtoupper($p['apelido']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="assinatura">
        <p>Assinatura do Responsável</p>
    </div>
</div>

</body>
</html>
