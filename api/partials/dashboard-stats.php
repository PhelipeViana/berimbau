<?php if (($_SESSION['nivel'] ?? null) === 'aluno'): ?>
    <div class="card-estatistica">
        <div class="icon-box bg-success"><i class="fas fa-check-circle"></i></div>
        <div><h2><?= (int) ($stats['presencas'] ?? 0) ?></h2><small>PRESENCAS</small></div>
    </div>
    <div class="card-estatistica">
        <div class="icon-box bg-info"><i class="fas fa-percentage"></i></div>
        <div><h2><?= (int) ($stats['aproveitamento'] ?? 0) ?>%</h2><small>FREQUENCIA</small></div>
    </div>
<?php else: ?>
    <div class="card-estatistica">
        <div class="icon-box bg-info"><i class="fas fa-user-graduate"></i></div>
        <div><h2><?= (int) ($stats['total'] ?? 0) ?></h2><small>ALUNOS ATIVOS</small></div>
    </div>
    <div class="card-estatistica">
        <div class="icon-box bg-danger"><i class="fas fa-heartbeat"></i></div>
        <div><h2><?= (int) ($stats['alertas_saude'] ?? 0) ?></h2><small>ALERTAS SAUDE</small></div>
    </div>
    <div class="card-estatistica">
        <div class="icon-box bg-warning"><i class="fas fa-calendar-check"></i></div>
        <div><h2><?= (int) ($stats['aulas_mes'] ?? 0) ?></h2><small>AULAS NO MES</small></div>
    </div>
<?php endif; ?>
