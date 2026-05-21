<?php
// processar_vivencia.php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/api/services/vivencia.php';

// Segurança: Apenas Administradores podem alimentar o acervo
if (!isset($_SESSION['usuario']) || $_SESSION['nivel'] !== 'admin') {
    header("Location: index.php?msg=acesso_negado");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        api_vivencia_criar($_POST, $_FILES, $_SESSION['usuario_id']);
        header("Location: index.php?page=vivencia&msg=postado");
    } catch (Exception $e) {
        header("Location: views/admin_vivencia.php?msg=erro_dados");
    }
    exit;
}
