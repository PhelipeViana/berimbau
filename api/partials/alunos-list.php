<?php if (count($alunos) > 0): ?>
    <?php foreach ($alunos as $a): ?>
        <div class="aluno-card" style="<?= (empty($a['docente_id']) || ($a['status'] ?? '') === 'pendente') ? 'border-left: 4px solid #c3382d;' : '' ?>">
            <img src="uploads/<?= api_escape($a['foto'] ?? 'padrao.png') ?>" class="avatar-circle" alt="Foto de <?= api_escape($a['nome'] ?? 'aluno') ?>">
            <div class="aluno-info">
                <strong><?= api_escape(strtoupper($a['nome'] ?? '')) ?></strong>
                <span class="grad-tag"><?= api_escape($a['graduacao'] ?? '') ?></span>
            </div>
            <div class="aluno-actions">
                <a href="?page=cadastro&edit=<?= (int) $a['id'] ?>" class="btn-edit" title="Editar / Validar"><i class="fas fa-edit"></i></a>
                <form action="api/alunos.php" method="POST" onsubmit="return confirm('Deseja excluir este aluno? Esta ação não poderá ser desfeita.')" style="display:inline;">
                    <input type="hidden" name="_method" value="DELETE">
                    <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                    <button type="submit" class="btn-delete" title="Excluir" style="border:0; cursor:pointer;"><i class="fas fa-trash"></i></button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p style="text-align: center; color: #94a3b8; padding: 40px;">Nenhum aluno encontrado nesta categoria.</p>
<?php endif; ?>
