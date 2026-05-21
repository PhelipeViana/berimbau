<?php

$root = dirname(__DIR__);
$failures = [];

function check($condition, $message) {
    global $failures;
    if (!$condition) {
        $failures[] = $message;
        echo "FAIL: $message\n";
        return;
    }
    echo "OK: $message\n";
}

function file_contains($path, $needle) {
    return strpos(file_get_contents($path), $needle) !== false;
}

function extract_create_table($sql, $table) {
    $pattern = '/CREATE TABLE `' . preg_quote($table, '/') . '` \((.*?)\) ENGINE=/s';
    if (!preg_match($pattern, $sql, $matches)) {
        return null;
    }
    return $matches[1];
}

function table_has_column($sql, $table, $column) {
    $tableSql = extract_create_table($sql, $table);
    return $tableSql !== null && preg_match('/`' . preg_quote($column, '/') . '`\s+/m', $tableSql);
}

$requiredFiles = [
    'processar_cadastro.php',
    'processar_diario.php',
    'processar_validacao.php',
    'processar_vivencia.php',
    'processar_competicao.php',
    'processa_externo.php',
    'autorizar_aluno.php',
    'inscrever_aluno.php',
    'excluir_diario.php',
    'recuperar_senha.php',
    'aluno_dashboard.php',
    'docker-compose.yml',
    'Dockerfile',
];

foreach ($requiredFiles as $file) {
    check(is_file("$root/$file"), "arquivo obrigatorio existe: $file");
}

$phpFiles = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
foreach ($iterator as $fileInfo) {
    if (!$fileInfo->isFile() || $fileInfo->getExtension() !== 'php') {
        continue;
    }
    $path = $fileInfo->getPathname();
    if (strpos($path, $root . '/.git/') === 0) {
        continue;
    }
    $phpFiles[] = $path;
}

foreach ($phpFiles as $file) {
    $cmd = 'php -l ' . escapeshellarg($file) . ' 2>&1';
    exec($cmd, $output, $code);
    check($code === 0, 'syntax PHP valida: ' . substr($file, strlen($root) + 1));
}

$sql = file_get_contents("$root/sistema_capoeira.sql");

$requiredTables = [
    'alunos',
    'aulas',
    'historico_graduacoes',
    'presencas',
    'usuarios',
    'vivencia',
    'competicoes',
    'inscricoes_competicao',
];

foreach ($requiredTables as $table) {
    check(extract_create_table($sql, $table) !== null, "tabela existe no dump: $table");
}

$requiredColumns = [
    'alunos' => ['id', 'nome', 'email', 'senha', 'status', 'docente_id', 'usuario_id', 'graduacao'],
    'aulas' => ['id', 'data_aula', 'docente_id', 'tema_aula', 'descricao_atividades', 'local_treino'],
    'vivencia' => ['id', 'titulo', 'descricao', 'categoria', 'tipo', 'url_conteudo', 'usuario_id', 'data_postagem'],
    'competicoes' => ['id', 'nome_evento', 'data_evento', 'local_evento', 'status', 'edital_url', 'descricao'],
    'inscricoes_competicao' => ['id', 'competicao_id', 'aluno_id'],
];

foreach ($requiredColumns as $table => $columns) {
    foreach ($columns as $column) {
        check(table_has_column($sql, $table, $column), "coluna existe no dump: $table.$column");
    }
}

check(!preg_match('/UPDATE\s+usuarios\s+SET\s+senha\s*=\s*\?\s+WHERE\s+email\s*=\s*\?/i', file_get_contents("$root/recuperar_senha.php")), 'recuperar_senha nao consulta usuarios.email inexistente');
check(file_contains("$root/processar_cadastro.php", 'salvarAluno($dados)'), 'processar_cadastro chama salvarAluno');
check(file_contains("$root/processa_externo.php", 'salvarAluno($_POST)'), 'processa_externo chama salvarAluno');
check(file_contains("$root/processar_diario.php", '$pdo->beginTransaction()'), 'processar_diario usa transacao');
check(file_contains("$root/processar_diario.php", '$pdo->commit()'), 'processar_diario confirma transacao');
check(file_contains("$root/processar_diario.php", '$pdo->rollBack()'), 'processar_diario reverte transacao em erro');
check(file_contains("$root/processar_validacao.php", "status = 'ativo'"), 'validacao ativa aluno aprovado');
check(file_contains("$root/processar_validacao.php", 'historico_graduacoes'), 'validacao registra historico inicial');
check(file_contains("$root/processar_vivencia.php", 'INSERT INTO vivencia'), 'vivencia grava material no acervo');
check(file_contains("$root/processar_competicao.php", 'INSERT INTO competicoes'), 'competicao grava evento');
check(file_contains("$root/inscrever_aluno.php", 'inscricoes_competicao'), 'inscricao usa tabela de inscricoes');
check(file_contains("$root/aluno_dashboard.php", 'aluno_dashbord.php'), 'wrapper aluno_dashboard aponta para arquivo existente');

$compose = file_get_contents("$root/docker-compose.yml");
check(strpos($compose, '"9077:80"') !== false || strpos($compose, "'9077:80'") !== false || strpos($compose, '- 9077:80') !== false, 'compose publica app na porta 9077');
check(strpos($compose, '"3377:3306"') !== false || strpos($compose, "'3377:3306'") !== false || strpos($compose, '- 3377:3306') !== false, 'compose publica mysql na porta 3377');
check(file_contains("$root/config/db.php", "getenv('DB_HOST')"), 'config/db.php aceita DB_HOST via ambiente');

echo "\n";
if ($failures) {
    echo count($failures) . " falha(s) encontrada(s).\n";
    exit(1);
}

echo "Todos os testes automatizados de processos passaram.\n";
