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
        --primary: #0f7a3a;
        --secondary: #f3b51b;
        --border: rgba(43, 63, 43, 0.14);
        --danger: #c3382d;
        --bg-input: rgba(255,255,255,0.74);
    }

    .matricula-container {
        background: var(--surface, rgba(255,255,255,0.9));
        padding: 30px;
        border-radius: 8px;
        border: 1px solid var(--border);
        box-shadow: 0 18px 45px rgba(28, 38, 24, 0.09);
        max-width: 1000px;
        margin: 20px auto;
        font-family: 'Inter', sans-serif;
        backdrop-filter: blur(14px);
    }

    .form-header {
        margin-bottom: 30px;
        border-bottom: 1px solid var(--border);
        padding-bottom: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        text-align: left;
    }

    .form-actions-top {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .btn-mini {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 38px;
        padding: 9px 13px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 900;
        font-size: 12px;
        border: 1px solid var(--border);
        color: var(--primary);
        background: rgba(15, 122, 58, 0.08);
    }

    @media (max-width: 720px) {
        .form-header,
        .btn-area-matricula {
            display: block;
        }

        .form-actions-top {
            justify-content: stretch;
            margin-top: 14px;
        }

        .btn-mini,
        .btn-area-matricula .btn-primary,
        .btn-area-matricula .btn-cancelar {
            width: 100%;
            margin-top: 10px;
        }
    }

    .section-title {
        color: var(--primary);
        border-left: 4px solid var(--secondary);
        padding: 8px 12px;
        margin: 25px 0 15px 0;
        background: rgba(15, 122, 58, 0.08);
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
        border-radius: 8px;
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
        border-radius: 8px;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.3s;
    }

    .btn-cancelar {
        flex: 1;
        text-align: center;
        padding: 15px;
        background: rgba(23, 32, 25, 0.07);
        color: #64748b;
        text-decoration: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 700;
    }
</style>

<div class="matricula-container">
    <div class="form-header">
        <div>
            <h2 style="margin:0; letter-spacing:-1px;"><?= $aluno_edicao ? 'EDITAR ALUNO' : 'NOVO ALUNO' ?></h2>
            <small style="color:#64748b">BERIMBAU - SISTEMA DE GESTÃO PARA ESCOLAS DE CAPOEIRA</small>
        </div>
        <?php if ($is_admin): ?>
            <div class="form-actions-top">
                <?php if ($aluno_edicao): ?>
                    <a href="index.php?page=cadastro" class="btn-mini"><i class="fas fa-plus"></i>&nbsp; Novo aluno</a>
                <?php endif; ?>
                <a href="index.php?page=lista" class="btn-mini"><i class="fas fa-list"></i>&nbsp; Ver lista</a>
            </div>
        <?php endif; ?>
    </div>

    <form method="POST" action="<?= $is_admin ? 'api/alunos.php' : 'api/alunos.php?externo=1' ?>" enctype="multipart/form-data">
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
                <input type="text" name="nascimento" class="input-matricula input-date-br" inputmode="numeric" maxlength="10" pattern="\d{2}/\d{2}/\d{4}" placeholder="DD/MM/AAAA" required value="<?= formatarDataBr($aluno_edicao['nascimento'] ?? '') ?>">
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
                <i class="fas fa-save"></i>
                <?= $aluno_edicao ? 'ATUALIZAR CADASTRO' : ($is_admin ? 'CRIAR ALUNO' : 'ENVIAR SOLICITAÇÃO') ?>
            </button>
            <a href="<?= $is_admin ? 'index.php?page=lista' : 'login_view.php' ?>" class="btn-cancelar">CANCELAR</a>
        </div>
    </form>
</div>
<script src="assets/js/datas.js"></script>
