<?php
// aluno_dashboard.php
require_once __DIR__ . '/includes/auth.php'; // Garante que o aluno está logado
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/funcoes_alunos.php';
require_once __DIR__ . '/api/services/vivencia.php';

// Segurança: Garante que apenas alunos acessem
$aluno_id = $_SESSION['aluno_id'] ?? null; 
if (!$aluno_id) {
    header("Location: login_view.php");
    exit;
}

// 1. Busca os dados do aluno logado (Tabela alunos)
$dados_aluno = buscarAlunoPorId($aluno_id);

// 2. Busca os conteúdos educativos pela camada de API/serviços
$materiais = api_vivencia_listar();

// 3. Busca estatísticas de presença
$estatisticas = obterFrequenciaAluno($aluno_id);
$mensagem = $_GET['msg'] ?? null;
$erroFoto = $_SESSION['foto_upload_erro'] ?? null;
unset($_SESSION['foto_upload_erro']);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área do Aluno - BERIMBAU</title>
    <link rel="stylesheet" href="assets/css/estilo_alunos.css">
    <script src="https://unpkg.com/htmx.org@1.9.12" defer></script>
</head>
<body>
    <div class="container">
        <header class="topbar-aluno">
            <div>
                <span class="eyebrow">Área do aluno</span>
                <h1>Salve, <?php echo htmlspecialchars($dados_aluno['apelido'] ?: explode(' ', $dados_aluno['nome'])[0]); ?>!</h1>
                <p>Acompanhe sua frequência, graduação e materiais de vivência.</p>
            </div>
            <a href="api/logout.php" class="btn-sair">Sair</a>
        </header>

        <?php if ($mensagem === 'foto_atualizada'): ?>
            <div class="upload-feedback success">Foto atualizada com sucesso.</div>
        <?php elseif ($erroFoto): ?>
            <div class="upload-feedback error"><?= htmlspecialchars($erroFoto) ?></div>
        <?php endif; ?>
        
        <div class="card-perfil">
            <div class="perfil-foto-wrap" id="foto-preview">
                <img src="uploads/<?php echo htmlspecialchars($dados_aluno['foto'] ?: 'padrao.png'); ?>" class="perfil-foto" alt="Foto de <?php echo htmlspecialchars($dados_aluno['apelido'] ?: $dados_aluno['nome']); ?>">
            </div>
            <div class="info-aluno">
                <div class="info-heading">
                    <h2><?php echo htmlspecialchars($dados_aluno['nome']); ?></h2>
                    <span class="status-pill"><?php echo strtoupper(htmlspecialchars($dados_aluno['status'])); ?></span>
                </div>
                <div class="metrics-grid">
                    <div>
                        <span>Graduação</span>
                        <strong><?php echo htmlspecialchars($dados_aluno['graduacao']); ?></strong>
                    </div>
                    <div>
                        <span>Frequência</span>
                        <strong><?php echo (int) $estatisticas['aproveitamento']; ?>%</strong>
                    </div>
                    <div>
                        <span>Presenças</span>
                        <strong><?php echo (int) $estatisticas['presencas']; ?></strong>
                    </div>
                </div>
                <form class="foto-form" action="api/foto.php" method="POST" enctype="multipart/form-data">
                    <label for="foto">Atualizar foto do perfil</label>
                    <div class="foto-upload-row">
                        <input type="file" id="foto" name="foto" accept="image/png,image/jpeg,image/webp" required>
                        <button type="submit">Enviar foto</button>
                    </div>
                    <small>JPG, PNG ou WEBP até 2MB.</small>
                </form>
            </div>
        </div>

        <section class="secao-vivencia">
            <div class="section-title-row">
                <div>
                    <span class="eyebrow">Vivência</span>
                    <h2>Biblioteca de estudo</h2>
                </div>
            </div>
            <div class="lista-materiais">
                <?php if (count($materiais) > 0): ?>
                <?php foreach ($materiais as $item): ?>
                    <div class="material-item">
                        <span class="badge"><?php echo strtoupper($item['categoria']); ?></span>
                        <h3><?php echo htmlspecialchars($item['titulo']); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars($item['descricao'])); ?></p>
                        
                        <div class="link-conteudo">
                            <?php if ($item['tipo'] === 'link'): ?>
                                <a href="<?php echo $item['url_conteudo']; ?>" target="_blank">Assistir Vídeo</a>
                            <?php elseif ($item['tipo'] === 'pdf'): ?>
                                <a href="<?php echo $item['url_conteudo']; ?>" target="_blank">Abrir Material PDF</a>
                            <?php elseif ($item['tipo'] === 'audio'): ?>
                                <p>Ouça a Mandinga:</p>
                                <audio controls>
                                    <source src="<?php echo $item['url_conteudo']; ?>" type="audio/mpeg">
                                    Seu navegador não suporta o player de áudio.
                                </audio>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">Nenhum material publicado ainda.</div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</body>
</html>
