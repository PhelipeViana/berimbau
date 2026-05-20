<?php
// lista_alunos.php
require_once __DIR__ . '/config/db.php';

// 1. Buscar Alunos Pendentes (Novas solicitações)
$stmt_pendentes = $pdo->query("SELECT * FROM alunos WHERE status = 'pendente' ORDER BY id DESC");
$pendentes = $stmt_pendentes->fetchAll(PDO::FETCH_ASSOC);

// 2. Buscar Alunos Ativos (Sua turma oficial)
$stmt_ativos = $pdo->query("SELECT * FROM alunos WHERE status = 'ativo' ORDER BY nome ASC");
$ativos = $stmt_ativos->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-admin">
    <h2><i class="fas fa-user-clock"></i> Solicitações Pendentes (Aguardando Aprovação)</h2>
    
    <?php if (count($pendentes) > 0): ?>
        <table class="tabela-berimbau">
            <thead>
                <tr>
                    <th>Nome / Apelido</th>
                    <th>E-mail</th>
                    <th>Data Solicitação</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendentes as $p): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($p['nome']) ?></strong> (<?= htmlspecialchars($p['apelido']) ?>)</td>
                    <td><?= htmlspecialchars($p['email']) ?></td>
                    <td><?= date('d/m/Y', strtotime($p['nascimento'])) ?></td>
                    <td>
                        <a href="processar_validacao.php?id=<?= $p['id'] ?>&acao=aprovar" class="btn-aprovar">
                            <i class="fas fa-check-circle"></i> APROVAR ACESSO
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Nenhuma solicitação nova no momento.</p>
    <?php endif; ?>

    <hr style="margin: 40px 0;">

    <h2><i class="fas fa-users"></i> Alunos Ativos</h2>
    <table class="tabela-berimbau">
        <thead>
            <tr>
                <th>Foto</th>
                <th>Nome</th>
                <th>Graduação</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ativos as $a): ?>
            <tr>
                <td><img src="uploads/<?= $a['foto'] ?>" width="40" style="border-radius: 50%;"></td>
                <td><?= htmlspecialchars($a['nome']) ?></td>
                <td><?= htmlspecialchars($a['graduacao']) ?></td>
                <td><span class="badge-ativo">ATIVO</span></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<style>
    .tabela-berimbau { width: 100%; border-collapse: collapse; margin-top: 15px; background: white; border-radius: 10px; overflow: hidden; }
    .tabela-berimbau th, .tabela-berimbau td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
    .tabela-berimbau th { background: #1e293b; color: white; }
    .btn-aprovar { background: #27ae60; color: white; padding: 8px 12px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 12px; }
    .btn-aprovar:hover { background: #219150; }
    .badge-ativo { background: #dcfce7; color: #166534; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
</style>