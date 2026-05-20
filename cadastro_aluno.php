<?php
// cadastro_aluno.php - Versão Unificada (Admin e Externo)
require_once 'includes/funcoes_alunos.php';

$aluno_edicao = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $aluno_edicao = buscarAlunoPorId($_GET['edit']);
}

// Verifica se quem está vendo a página é um Admin logado
$is_admin = isset($_SESSION['usuario_id']);
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    :root {
        --primary: #1e293b;
        --secondary: #6366f1;
        --border: #e2e8f0;
        --danger: #ef4444;
        --bg-input: #f8fafc;
    }

    .matricula-container {
        background: white;
        padding: 30px;
        border-radius: 24px;
        border: 1px solid var(--border);
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);
        max-width: 1000px;
        margin: 20px auto;
        font-family: 'Inter', sans-serif;
    }

    .form-header {
        text-align: center;
        margin-bottom: 30px;
        border-bottom: 2px solid var(--secondary);
        padding-bottom: 15px;
    }

    .section-title {
        color: var(--primary);
        border-left: 4px solid var(--secondary);
        padding: 8px 12px;
        margin: 25px 0 15px 0;
        background: #f1f5f9;
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .grid-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 15px;
        margin-bottom: 15px;
    }

    .form-group-full { grid-column: 1 / -1; }

    .label-matricula {
        display: block;
        font-weight: 700;
        margin-bottom: 6px;
        font-size: 11px;
        color: #64748b;
        text-transform: uppercase;
    }

    .input-matricula {
        width: 100%;
        padding: 12px;
        border: 1px solid var(--border);
        border-radius: 10px;
        background: var(--bg-input);
        font-size: 14px;
        box-sizing: border-box;
    }

    .required-mark::after { content: " *"; color: var(--danger); }

    .btn-area-matricula {
        display: flex;
        gap: 15px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid var(--border);
    }

    .btn-primary {
        background: var(--primary);
        color: white;
        border: none;
        padding: 15px;
        border-radius: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.3s;
    }

    .btn-cancelar {
        flex: 1;
        text-align: center;
        padding: 15px;
        background: #f1f5f9;
        color: #64748b;
        text-decoration: none;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 700;
    }
</style>

<div class="matricula-container">
    <div class="form-header">
        <h2 style="margin:0; letter-spacing:-1px;">FICHA DE MATRÍCULA OFICIAL - 2026</h2>
        <small style="color:#64748b">BERIMBAU - SISTEMA DE GESTÃO PARA ESCOLAS DE CAPOEIRA</small>
    </div>

    <form method="POST" action="<?= $is_admin ? 'processar_cadastro.php' : 'processa_externo.php' ?>" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $aluno_edicao['id'] ?? '' ?>">

        <div class="section-title">1. Identificação e Acesso</div>
        <div class="grid-row">
            <div class="form-group-full">
                <label class="label-matricula required-mark">Nome Completo</label>
                <input type="text" name="nome" class="input-matricula" required value="<?= $aluno_edicao['nome'] ?? '' ?>">
            </div>
            <div>
                <label class="label-matricula">Apelido de Capoeira</label>
                <input type="text" name="apelido" class="input-matricula" value="<?= $aluno_edicao['apelido'] ?? '' ?>">
            </div>
            <div>
                <label class="label-matricula required-mark">E-mail (Seu Login)</label>
                <input type="email" name="email" class="input-matricula" required value="<?= $aluno_edicao['email'] ?? '' ?>">
            </div>
            <div>
                <label class="label-matricula <?= !$aluno_edicao ? 'required-mark' : '' ?>">Senha de Acesso</label>
                <input type="password" name="senha" class="input-matricula" <?= !$aluno_edicao ? 'required' : '' ?> placeholder="Defina sua senha">
            </div>
        </div>

        <div class="section-title">2. Contato e Endereço</div>
        <div class="grid-row">
            <div>
                <label class="label-matricula required-mark">WhatsApp</label>
                <input type="tel" name="celular" class="input-matricula" required value="<?= $aluno_edicao['celular'] ?? '' ?>">
            </div>
            <div>
                <label class="label-matricula required-mark">Data de Nascimento</label>
                <input type="date" name="nascimento" class="input-matricula" required value="<?= $aluno_edicao['nascimento'] ?? '' ?>">
            </div>
            <div class="form-group-full">
                <label class="label-matricula required-mark">Endereço Completo</label>
                <input type="text" name="endereco" class="input-matricula" required value="<?= $aluno_edicao['endereco'] ?? '' ?>">
            </div>
        </div>

        <div class="section-title">3. Dados Técnicos e Docente</div>
        <div class="grid-row">
            <div>
                <label class="label-matricula required-mark">Graduação</label>
                <select name="graduacao" class="input-matricula" required>
                    <option value="">Selecione...</option>
                    <?php 
                    $graduacoes = ["CRUA", "AMARELA", "LARANJA", "VERDE (GRADUADO)", "AZUL (GRADUADO)", "ROXA (INSTRUTOR)", "MARROM (PROFESSOR)", "VERMELHA (MESTRE)"];
                    foreach($graduacoes as $g): 
                        $sel = (isset($aluno_edicao['graduacao']) && $aluno_edicao['graduacao'] == $g) ? 'selected' : '';
                        echo "<option value='$g' $sel>$g</option>";
                    endforeach; ?>
                </select>
            </div>
            <div>
                <label class="label-matricula required-mark">Docente Responsável</label>
                <select name="docente" class="input-matricula" required>
                    <option value="">Selecione...</option>
                    <?php 
                    $lista_docentes = ["MESTRE BIRO", "MESTRE KOSKORÃO", "CONTRAMESTRE GALEGO", "PROFESSOR TUIUIÚ"]; // Sua lista completa aqui
                    foreach($lista_docentes as $d):
                        $sel = (isset($aluno_edicao['docente']) && $aluno_edicao['docente'] == $d) ? 'selected' : '';
                        echo "<option value='$d' $sel>$d</option>";
                    endforeach;
                    ?>
                </select>
            </div>
        </div>

        <div class="btn-area-matricula">
            <button type="submit" class="btn-primary" style="flex:2;">
                <?= $aluno_edicao ? 'ATUALIZAR CADASTRO' : 'ENVIAR SOLICITAÇÃO / SALVAR' ?>
            </button>
            <a href="<?= $is_admin ? 'index.php?page=lista' : 'login_view.php' ?>" class="btn-cancelar">CANCELAR</a>
        </div>
    </form>
</div>