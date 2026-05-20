<?php
// aluno_dashboard.php
require_once __DIR__ . '/includes/auth.php'; // Garante que o aluno está logado
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/funcoes_alunos.php';

// Segurança: Garante que apenas alunos acessem
$aluno_id = $_SESSION['aluno_id'] ?? null; 
if (!$aluno_id) {
    header("Location: login_view.php");
    exit;
}

// 1. Busca os dados do aluno logado (Tabela alunos)
$dados_aluno = buscarAlunoPorId($aluno_id);

// 2. Busca os conteúdos educativos (Tabela vivencia)
$stmt_vivencia = $pdo->query("SELECT * FROM vivencia ORDER BY data_postagem DESC");
$materiais = $stmt_vivencia->fetchAll(PDO::FETCH_ASSOC);

// 3. Busca estatísticas de presença
$estatisticas = obterFrequenciaAluno($aluno_id);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área do Aluno - Sistema Capoeira</title>
    <link rel="stylesheet" href="assets/css/estilo_aluno.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Bem-vindo, <?php echo htmlspecialchars($dados_aluno['apelido'] ?: $dados_aluno['nome']); ?>!</h1>
        </header>
        
        <div class="card-perfil">
            <img src="uploads/<?php echo $dados_aluno['foto']; ?>" alt="Foto de <?php echo $dados_aluno['apelido']; ?>">
            <div class="info-aluno">
                <p><strong>Apelido:</strong> <?php echo htmlspecialchars($dados_aluno['apelido']); ?></p>
                <p><strong>Graduação:</strong> <?php echo htmlspecialchars($dados_aluno['graduacao']); ?></p>
                <p><strong>Frequência:</strong> <?php echo $estatisticas['aproveitamento']; ?>%</p>
                <p><strong>Status:</strong> <span style="color: green; font-weight: bold;"><?php echo strtoupper($dados_aluno['status']); ?></span></p>
            </div>
        </div>

        <section class="secao-vivencia">
            <h2>Biblioteca de Vivência</h2>
            <div class="lista-materiais">
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
            </div>
        </section>
    </div>
</body>
</html>