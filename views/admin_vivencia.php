<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

// Proteção: Apenas Admins alimentam o acervo
if ($_SESSION['nivel'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Acervo | BERIMBAU</title>
    <link rel="stylesheet" href="../assets/css/estilo_padrao.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .main { padding: 40px; flex: 1; }
        .card-form { background: white; padding: 30px; border-radius: 20px; border: 1px solid #e2e8f0; }
        .form-group { margin-bottom: 20px; }
        
        input, select, textarea { 
            width: 100%; padding: 12px; border-radius: 10px; 
            border: 2px solid #cbd5e1; outline: none; margin-top: 5px;
        }
        input:focus { border-color: var(--primary); }
        
        .btn-salvar { 
            background: var(--primary); color: white; border: none; 
            padding: 12px 30px; border-radius: 10px; cursor: pointer; font-weight: bold;
        }
    </style>
</head>
<body style="display: flex;">

<aside class="sidebar">
    <div class="sidebar-header"><b>BERIMBAU.</b></div>
    <nav class="menu-list">
        <a href="../index.php?page=dashboard" class="menu-item"><i class="fas fa-arrow-left"></i> Voltar</a>
    </nav>
</aside>

<main class="main">
    <div class="card-form">
        <h2><i class="fas fa-plus-circle"></i> Alimentar Acervo Vivência</h2>
        <form action="../processar_vivencia.php" method="POST" enctype="multipart/form-data">
            
            <div class="form-group">
                <label>Título do Material</label>
                <input type="text" name="titulo" placeholder="Ex: Fundamentos da Regional" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Categoria</label>
                    <select name="categoria">
                        <option value="historia">Fundamentos e História</option>
                        <option value="musica">Cantigas e Toques</option>
                        <option value="graduacao">Sistema de Graduação</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tipo de Mídia</label>
                    <select name="tipo">
                        <option value="pdf">Arquivo PDF</option>
                        <option value="link">Link Externo (YouTube/Drive)</option>
                        <option value="audio">Arquivo de Áudio</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Descrição Breve</label>
                <textarea name="descricao" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label>Link ou Arquivo</label>
                <input type="text" name="url_externa" placeholder="Cole o link aqui ou use o campo abaixo para upload">
                <input type="file" name="arquivo_upload" style="margin-top: 10px; border: none;">
            </div>

            <button type="submit" class="btn-salvar">PUBLICAR NO ACERVO</button>
        </form>
    </div>
</main>
</body>
</html>