<?php if (count($conteudos) > 0): ?>
    <?php foreach ($conteudos as $item): ?>
        <div class="card-vivencia">
            <div>
                <div class="icon-vivencia">
                    <?php if (($item['categoria'] ?? '') === 'historia'): ?>
                        <i class="fas fa-history" style="font-size: 24px; color: var(--primary);"></i>
                    <?php elseif (($item['categoria'] ?? '') === 'musica'): ?>
                        <i class="fas fa-music" style="font-size: 24px; color: #0f7a3a;"></i>
                    <?php else: ?>
                        <i class="fas fa-scroll" style="font-size: 24px; color: #d99213;"></i>
                    <?php endif; ?>
                </div>
                <h3><?= api_escape($item['titulo'] ?? '') ?></h3>
                <p style="color: #64748b; font-size: 14px;"><?= api_escape($item['descricao'] ?? '') ?></p>
            </div>
            <a href="<?= api_escape($item['url_conteudo'] ?? '#') ?>" target="_blank" class="btn-berimbau btn-primary" style="display: block; text-align: center; text-decoration: none; margin-top: 20px;">
                ACESSAR CONTEUDO
            </a>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div style="grid-column: 1 / -1; text-align: center; padding: 60px; background: var(--surface); border-radius: 8px; border: 2px dashed var(--border-color);">
        <i class="fas fa-book-reader" style="font-size: 50px; color: #cbd5e1; margin-bottom: 15px;"></i>
        <p style="color: #94a3b8;">Nenhum material cadastrado.</p>
    </div>
<?php endif; ?>
