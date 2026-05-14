<?php
// Lógica para carregar dados em caso de edição
require_once 'includes/funcoes_alunos.php';

$aluno_edicao = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $aluno_edicao = buscarAlunoPorId($_GET['edit']);
}
?>

<style>
    .matricula-container {
        background: white;
        padding: 30px;
        border-radius: 24px;
        border: 1px solid var(--border);
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);
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
        background: #f8fafc;
        font-size: 14px;
    }

    .required-mark::after { content: " *"; color: var(--danger); }

    .btn-area-matricula {
        display: flex;
        gap: 15px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid var(--border);
    }
</style>

<div class="matricula-container">
    <div class="form-header">
        <h2 style="margin:0; letter-spacing:-1px;">FICHA DE MATRÍCULA OFICIAL - 2026</h2>
        <small style="color:#64748b">BERIMBAU - SISTEMA DE GESTÃO PARA ESCOLAS DE CAPOEIRA</small>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $aluno_edicao['id'] ?? '' ?>">
        <input type="hidden" name="foto_atual" value="<?= $aluno_edicao['foto'] ?? 'padrao.png' ?>">

        <div class="section-title">1. Identificação Individual</div>
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
                <label class="label-matricula required-mark">Data de Nascimento</label>
                <input type="date" name="nascimento" class="input-matricula" required value="<?= $aluno_edicao['nascimento'] ?? '' ?>">
            </div>
        </div>

        <div class="section-title">2. Contato e Endereço</div>
        <div class="grid-row">
            <div>
                <label class="label-matricula required-mark">Número do Celular (WhatsApp)</label>
                <input type="tel" name="celular" class="input-matricula" required value="<?= $aluno_edicao['celular'] ?? '' ?>">
            </div>
            <div>
                <label class="label-matricula">E-mail</label>
                <input type="email" name="email" class="input-matricula" value="<?= $aluno_edicao['email'] ?? '' ?>">
            </div>
            <div>
                <label class="label-matricula required-mark">Nome da Mãe</label>
                <input type="text" name="mae" class="input-matricula" required value="<?= $aluno_edicao['mae'] ?? '' ?>">
            </div>
            <div class="form-group-full">
                <label class="label-matricula required-mark">Endereço Completo</label>
                <input type="text" name="endereco" class="input-matricula" required value="<?= $aluno_edicao['endereco'] ?? '' ?>">
            </div>
        </div>

        <div class="section-title">3. Dados Técnicos e Foto</div>
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
                    $lista_docentes = [
                        "MESTRE BIRO", "MESTRE KOSKORÃO", "CONTRAMESTRE GALEGO", 
                        "CONTRAMESTRE MUTUM", "CONTRAMESTRE CHIQUINHO", "CONTRAMESTRE AMENDOIM", 
                        "CONTRAMESTRE COYOT", "PROFESSOR TUIUIÚ", "PROFESSOR RAFAEL", 
                        "PROFESSORA CIGANA", "PROFESSOR CAVALLO", "PROFESSOR SAGUI", 
                        "PROFESSOR CALADO", "INSTRUTOR ESQUILO", "INSTRUTORA SEREIA", 
                        "GRADUADO DUDU", "GRADUADO GUERREIRO", "GRADUADO BIG"
                    ];
                    foreach($lista_docentes as $d):
                        $sel = (isset($aluno_edicao['docente']) && $aluno_edicao['docente'] == $d) ? 'selected' : '';
                        echo "<option value='$d' $sel>$d</option>";
                    endforeach;
                    ?>
                </select>
            </div>
            <div class="form-group-full">
                <label class="label-matricula">Foto de Identificação</label>
                <input type="file" name="foto" class="input-matricula" accept="image/*">
            </div>
        </div>

        <div class="section-title">4. Saúde e Cuidados</div>
        <div class="form-group-full">
            <label class="label-matricula">Observações Médicas ou Cuidados Especiais</label>
            <textarea name="saude" rows="3" class="input-matricula" placeholder="Alergias, lesões..."><?= $aluno_edicao['saude'] ?? '' ?></textarea>
        </div>

        <div class="btn-area-matricula">
            <button type="submit" name="btnSalvar" class="btn-berimbau btn-primary" style="flex:2; justify-content: center; font-size: 15px;">
                <i class="fas fa-save" style="margin-right: 8px;"></i> <?= $aluno_edicao ? 'ATUALIZAR CADASTRO' : 'SALVAR MATRÍCULA 2026' ?>
            </button>
            <a href="index.php?page=lista" class="btn-berimbau" style="flex:1; background:#f1f5f9; color:#64748b; text-decoration:none; display:flex; align-items:center; justify-content:center;">
                CANCELAR
            </a>
        </div>
    </form>
</div>