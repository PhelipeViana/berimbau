<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/funcoes_alunos.php';
require_once __DIR__ . '/api/services/aulas.php';

// Proteção: Só permite acesso se o nível for 'aluno'
if (!isset($_SESSION['usuario']) || $_SESSION['nivel'] !== 'aluno') {
    header("Location: index.php");
    exit;
}

// Busca os dados específicos deste aluno
$aluno = buscarAlunoPorId($_SESSION['aluno_id']);
$historico = buscarHistorico($_SESSION['aluno_id']);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Área | CapoeiraOS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --primary: #0f7a3a; --gold: #f3b51b; --clay: #c9561a; --bg: #f4f1e8; --text: #18251c; --muted: #667062; --border: rgba(43, 63, 43, 0.14); }
        * { box-sizing: border-box; }
        body {
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background:
                linear-gradient(135deg, rgba(15, 122, 58, 0.08), transparent 34%),
                linear-gradient(315deg, rgba(243, 181, 27, 0.13), transparent 38%),
                var(--bg);
            color: var(--text);
            margin: 0;
            padding: clamp(18px, 4vw, 34px);
            min-height: 100vh;
        }
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            opacity: 0.26;
            background-image: linear-gradient(90deg, rgba(24,37,28,0.05) 1px, transparent 1px), linear-gradient(rgba(24,37,28,0.04) 1px, transparent 1px);
            background-size: 32px 32px;
        }
        .container { max-width: 900px; margin: 0 auto; }
        
        /* Cabeçalho do Aluno */
        .profile-header { 
            background: rgba(255,255,255,0.9); padding: 30px; border-radius: 8px; 
            display: flex; align-items: center; gap: 25px;
            box-shadow: 0 18px 45px rgba(28, 38, 24, 0.09); border: 1px solid var(--border);
            backdrop-filter: blur(14px);
        }
        .profile-avatar { width: 104px; height: 104px; border-radius: 8px; object-fit: cover; border: 3px solid rgba(243,181,27,0.55); }
        .profile-info h1 { margin: 0; font-size: clamp(26px, 4vw, 36px); color: var(--text); letter-spacing: 0; }
        .badge-graduacao { 
            display: inline-block; padding: 6px 14px; background: rgba(15,122,58,0.12); 
            color: var(--primary); border-radius: 999px; font-size: 12px; font-weight: 900; margin-top: 8px;
        }

        /* Dashboard de Cards */
        .grid-aluno { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-top: 30px; }
        .card-aluno { background: rgba(255,255,255,0.9); padding: 25px; border-radius: 8px; border: 1px solid var(--border); box-shadow: 0 14px 36px rgba(28, 38, 24, 0.08); backdrop-filter: blur(14px); }
        .card-title { font-weight: 900; font-size: 16px; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; color: var(--text); }
        
        /* Linha do Tempo / Histórico */
        .timeline { list-style: none; padding: 0; margin: 0; }
        .timeline-item { padding-left: 20px; border-left: 2px solid rgba(15,122,58,0.18); position: relative; padding-bottom: 20px; }
        .timeline-item::before { 
            content: ''; position: absolute; left: -7px; top: 0; 
            width: 12px; height: 12px; background: var(--gold); border-radius: 50%; 
        }
        .timeline-date { font-size: 11px; color: var(--muted); font-weight: 900; }

        .btn-sair { 
            position: absolute; top: 20px; right: 20px; 
            text-decoration: none; color: #c3382d; font-weight: 900; font-size: 14px; 
        }
        @media (max-width: 640px) {
            .profile-header { display: block; }
            .profile-avatar { margin-bottom: 16px; }
            .btn-sair { position: static; display: inline-flex; margin-bottom: 16px; }
        }
    </style>
</head>
<body>

<div class="container">
    <a href="api/logout.php" class="btn-sair"><i class="fas fa-sign-out-alt"></i> SAIR</a>

    <header class="profile-header">
        <img src="uploads/<?= $aluno['foto'] ?? 'padrao.png' ?>" class="profile-avatar">
        <div class="profile-info">
            <h1>Salve, <?= explode(' ', $aluno['nome'])[0] ?>!</h1>
            <span class="badge-graduacao"><?= $aluno['graduacao'] ?></span>
            <p style="font-size: 14px; color: #64748b; margin-top: 10px;">
                <i class="fas fa-map-marker-alt"></i> <?= $aluno['cidade'] ?? 'Não informada' ?>
            </p>
        </div>
    </header>

    <div class="grid-aluno">
        <div class="card-aluno">
            <div class="card-title"><i class="fas fa-id-card" style="color: #6366f1;"></i> Meus Dados</div>
            <div style="font-size: 14px; line-height: 1.8;">
                <strong>E-mail:</strong> <?= $aluno['email'] ?><br>
                <strong>Celular:</strong> <?= $aluno['celular'] ?><br>
                <strong>Nascimento:</strong> <?= date('d/m/Y', strtotime($aluno['nascimento'])) ?><br>
                <strong>Professor:</strong> <?= $aluno['docente'] ?>
            </div>
        </div>

        <div class="card-aluno">
            <div class="card-title"><i class="fas fa-notes-medical" style="color: #ef4444;"></i> Informações Médicas</div>
            <p style="font-size: 14px; color: #64748b;">
                <?= !empty($aluno['saude']) ? $aluno['saude'] : 'Nenhuma observação médica registrada.' ?>
            </p>
        </div>

        <div class="card-aluno" style="grid-column: span 1;">
            <div class="card-title"><i class="fas fa-history" style="color: #f59e0b;"></i> Jornada na Capoeira</div>
            <ul class="timeline">
                <?php if(empty($historico)): ?>
                    <p style="font-size: 13px; color: #94a3b8;">Sua jornada está apenas começando!</p>
                <?php else: ?>
                    <?php foreach($historico as $h): ?>
                        <li class="timeline-item">
                            <div class="timeline-date"><?= date('d/m/Y', strtotime($h['data_mudanca'])) ?></div>
                            <div style="font-size: 14px; font-weight: 600;"><?= $h['graduacao_nova'] ?></div>
                            <small style="color: #64748b;"><?= $h['observacao'] ?></small>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <div class="card-aluno" style="grid-column: span 1;">
    <div class="card-title">
        <i class="fas fa-calendar-check" style="color: #10b981;"></i> 
        Presenças Recentes
    </div>
    <div style="max-height: 300px; overflow-y: auto;">
        <?php
        $aulas = api_aulas_presencas_aluno($_SESSION['aluno_id'], 10);

        if (empty($aulas)): ?>
            <p style="font-size: 13px; color: #94a3b8;">Nenhuma presença registrada ainda.</p>
        <?php else: 
            foreach($aulas as $aula): ?>
                <div style="padding: 12px 0; border-bottom: 1px solid #f1f5f9;">
                    <div style="font-size: 13px; font-weight: 700; color: #1e293b;">
                        <?= date('d/m/Y', strtotime($aula['data_aula'])) ?>
                    </div>
                    <div style="font-size: 12px; color: #6366f1; font-weight: 600;">
                        <?= htmlspecialchars($aula['tema_aula']) ?>
                    </div>
                    <small style="color: #94a3b8;"><i class="fas fa-map-pin"></i> <?= htmlspecialchars($aula['local_treino']) ?></small>
                </div>
            <?php endforeach; 
        endif; ?>
    </div>
</div>

</div>

</body>
</html>
