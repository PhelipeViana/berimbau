<?php
// processar_vivencia.php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';

// Segurança: Apenas Administradores podem alimentar o acervo
if (!isset($_SESSION['usuario']) || $_SESSION['nivel'] !== 'admin') {
    header("Location: index.php?msg=acesso_negado");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $categoria = $_POST['categoria'] ?? 'outros';
    $tipo = $_POST['tipo'] ?? 'link';
    $usuario_id = $_SESSION['usuario_id'];
    $url_final = "";

    // 1. TRATAMENTO DE LINK EXTERNO
    if ($tipo === 'link' && !empty($_POST['url_externa'])) {
        $url_final = $_POST['url_externa'];
    } 
    // 2. TRATAMENTO DE UPLOAD DE ARQUIVOS (PDF ou Áudio)
    elseif (isset($_FILES['arquivo_upload']) && $_FILES['arquivo_upload']['error'] === 0) {
        $diretorio_destino = __DIR__ . "/uploads/vivencia/";
        
        // Criar pasta caso não exista e dar permissão
        if (!is_dir($diretorio_destino)) {
            mkdir($diretorio_destino, 0755, true);
        }

        $extensao = strtolower(pathinfo($_FILES['arquivo_upload']['name'], PATHINFO_EXTENSION));
        
        // Validação de segurança (Analista de Segurança focado aqui!)
        $extensoes_permitidas = ['pdf', 'mp3', 'wav', 'png', 'jpg'];
        if (!in_array($extensao, $extensoes_permitidas)) {
            die("Erro: Tipo de arquivo não permitido.");
        }

        // Renomeia o arquivo para evitar conflitos e caracteres especiais
        $novo_nome = uniqid("VIV_") . "." . $extensao;
        $caminho_completo = $diretorio_destino . $novo_nome;

        if (move_uploaded_file($_FILES['arquivo_upload']['tmp_name'], $caminho_completo)) {
            $url_final = "uploads/vivencia/" . $novo_nome;
        } else {
            die("Erro ao mover o arquivo para o servidor. Verifique permissões da pasta uploads.");
        }
    }

    // 3. GRAVAÇÃO NO BANCO
    if (!empty($url_final)) {
        try {
            $sql = "INSERT INTO vivencia (titulo, descricao, categoria, tipo, url_conteudo, usuario_id) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$titulo, $descricao, $categoria, $tipo, $url_final, $usuario_id]);

            // Sucesso! Volta para a página de vivência
            header("Location: index.php?page=vivencia&msg=postado");
        } catch (Exception $e) {
            die("Erro crítico de banco de dados: " . $e->getMessage());
        }
    } else {
        header("Location: views/admin_vivencia.php?msg=erro_dados");
    }
    exit;
}