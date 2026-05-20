<?php
// processar_competicao.php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['usuario']) || $_SESSION['nivel'] !== 'admin') {
    die("Acesso negado.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome_evento'] ?? '';
    $data = $_POST['data_evento'] ?? '';
    $local = $_POST['local_evento'] ?? '';
    $status = $_POST['status'] ?? 'inscricoes_abertas';
    $edital = $_POST['edital_url'] ?? '';
    $desc = $_POST['descricao'] ?? '';

    try {
        $sql = "INSERT INTO competicoes (nome_evento, data_evento, local_evento, status, edital_url, descricao) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $data, $local, $status, $edital, $desc]);

        header("Location: index.php?page=competicao&msg=evento_criado");
    } catch (Exception $e) {
        die("Erro ao salvar no banco: " . $e->getMessage());
    }
}