<?php if (count($eventos) > 0): ?>
    <?php foreach ($eventos as $ev): ?>
        <div class="card-vivencia" style="border-left: 5px solid <?= ($ev['status'] ?? '') === 'inscricoes_abertas' ? '#0f7a3a' : '#64748b' ?>;">
            <div style="display: flex; justify-content: space-between; gap: 12px;">
                <span class="status-tag" style="font-size: 10px; font-weight: 800; padding: 4px 10px; border-radius: 20px; background: #f1f5f9;">
                    <?= api_escape(strtoupper(str_replace('_', ' ', $ev['status'] ?? ''))) ?>
                </span>
                <span style="font-size: 12px; color: #94a3b8;"><i class="far fa-calendar-alt"></i> <?= api_escape(date('d/m/Y', strtotime($ev['data_evento'] ?? 'now'))) ?></span>
            </div>
            <h3 style="margin: 15px 0;"><?= api_escape($ev['nome_evento'] ?? '') ?></h3>
            <p style="font-size: 13px; color: #64748b; margin-bottom: 15px;"><i class="fas fa-map-marker-alt"></i> <?= api_escape($ev['local_evento'] ?? '') ?></p>

            <?php if (($_SESSION['nivel'] ?? null) === 'admin'): ?>
                <div style="margin-bottom: 15px; padding: 8px; background: #f0f9ff; border-radius: 8px; font-size: 12px; color: #0369a1; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-users"></i> <strong><?= (int) ($ev['total_inscritos'] ?? 0) ?></strong> Alunos confirmados
                </div>
            <?php endif; ?>

            <div style="display: flex; gap: 10px;">
                <a href="<?= api_escape($ev['edital_url'] ?: '#') ?>" target="_blank" class="btn-berimbau" style="flex: 1; text-align: center; text-decoration: none; padding: 10px; border-radius: 8px; background: #f1f5f9; font-weight: bold; font-size: 12px;">REGRAS</a>

                <?php if (($ev['status'] ?? '') === 'inscricoes_abertas'): ?>
                    <a href="api/inscricoes.php?competicao_id=<?= (int) $ev['id'] ?>"
                       class="btn-berimbau btn-primary"
                       style="flex: 1; text-align: center; text-decoration: none; padding: 10px; border-radius: 8px; font-size: 12px;"
                       onclick="return confirm('Confirmar inscrição?')">
                       INSCREVER-SE
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div style="grid-column: 1 / -1; text-align: center; padding: 60px; background: var(--surface); border-radius: 8px; border: 2px dashed var(--border-color);">
        <i class="fas fa-medal" style="font-size: 50px; color: #cbd5e1; margin-bottom: 15px;"></i>
        <p style="color: #94a3b8;">Nenhuma competicao agendada.</p>
    </div>
<?php endif; ?>
