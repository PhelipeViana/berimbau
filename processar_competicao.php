<?php
// processar_competicao.php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/api/services/competicoes.php';

if (!isset($_SESSION['usuario']) || $_SESSION['nivel'] !== 'admin') {
    die("Acesso negado.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        api_competicao_criar($_POST);
        header("Location: index.php?page=competicao&msg=evento_criado");
    } catch (Exception $e) {
        die("Erro ao salvar no banco: " . $e->getMessage());
    }
}
