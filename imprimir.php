<?php
require_once 'includes/auth.php';
require_once 'includes/funcoes_alunos.php';

if (!isset($_SESSION['usuario']) || !isset($_GET['id'])) {
    die("Acesso negado.");
}

$aluno = buscarAlunoPorId($_GET['id']);
if (!$aluno) die("Aluno não encontrado.");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Ficha de Matrícula - <?= $aluno['nome'] ?></title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; color: #333; line-height: 1.6; }
        .folha-a4 { width: 210mm; margin: auto; border: 1px solid #ccc; padding: 20px; box-sizing: border-box; }
        .cabecalho { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .foto-box { width: 120px; height: 150px; border: 1px solid #000; float: right; text-align: center; line-height: 150px; font-size: 12px; }
        .secao { background: #eee; padding: 5px 10px; font-weight: bold; margin: 20px 0 10px 0; clear: both; }
        .campo { margin-bottom: 10px; border-bottom: 1px solid #eee; }
        .label { font-weight: bold; font-size: 12px; color: #666; text-transform: uppercase; }
        @media print {
            body { padding: 0; }
            .folha-a4 { border: none; width: 100%; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="no-print" style="text-align: center; margin-bottom: 20px;">
    <button onclick="window.print()" style="padding: 10px 20px; cursor:pointer;">🖨️ IMPRIMIR AGORA</button>
    <button onclick="window.close()" style="padding: 10px 20px; cursor:pointer;">FECHAR</button>
</div>

<div class="folha-a4">
    <div class="foto-box">FOTO 3X4</div>
    <div class="cabecalho">
        <h1 style="margin:0;">FICHA DE MATRÍCULA - CAPOEIRA 2026</h1>
        <p>Registro Geral do Aluno</p>
    </div>

    <div class="secao">DADOS PESSOAIS</div>
    <div class="campo"><span class="label">Nome:</span> <?= strtoupper($aluno['nome']) ?></div>
    <div class="campo"><span class="label">Apelido:</span> <?= $aluno['apelido'] ?></div>
    <div class="campo"><span class="label">Nascimento:</span> <?= date('d/m/Y', strtotime($aluno['nascimento'])) ?></div>
    
    <div class="secao">CONTATOS E LOCALIZAÇÃO</div>
    <div class="campo"><span class="label">Celular:</span> <?= $aluno['celular'] ?></div>
    <div class="campo"><span class="label">Cidade:</span> <?= $aluno['cidade'] ?></div>
    <div class="campo"><span class="label">Endereço:</span> <?= $aluno['endereco'] ?></div>
    <div class="campo"><span class="label">Mãe:</span> <?= $aluno['mae'] ?> | <span class="label">Pai:</span> <?= $aluno['pai'] ?></div>

    <div class="secao">DADOS TÉCNICOS</div>
    <div class="campo"><span class="label">Graduação Atual:</span> <strong><?= $aluno['graduacao'] ?></strong></div>
    <div class="campo"><span class="label">Local de Treino:</span> <?= $aluno['local_treino'] ?></div>
    <div class="campo"><span class="label">Docente Responsável:</span> <?= $aluno['docente'] ?></div>

    <div class="secao" style="background: #ffcccc;">INFORMAÇÕES DE SAÚDE</div>
    <div style="border: 1px solid #ffcccc; padding: 10px; min-height: 50px;">
        <?= $aluno['saude'] ? nl2br($aluno['saude']) : "Nenhum alerta médico registrado." ?>
    </div>

    <div style="margin-top: 50px; text-align: center;">
        <br><br>
        ____________________________________________________<br>
        Assinatura do Aluno ou Responsável
    </div>
</div>

</body>
</html>