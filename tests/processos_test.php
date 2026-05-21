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
    '.env.example',
    'config/env.php',
    'api/bootstrap.php',
    'api/helpers.php',
    'api/dashboard.php',
    'api/alunos.php',
    'api/vivencia.php',
    'api/competicoes.php',
    'api/aulas.php',
    'api/validacao.php',
    'api/inscricoes.php',
    'api/senha.php',
    'api/usuarios.php',
    'api/logout.php',
    'api/foto.php',
    'api/services/aulas.php',
    'api/services/auth.php',
    'api/services/competicoes.php',
    'api/services/inscricoes.php',
    'api/services/senha.php',
    'api/services/validacao.php',
    'api/services/vivencia.php',
    'assets/js/datas.js',
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
check(file_contains("$root/processar_diario.php", 'api_aula_salvar'), 'processar_diario delega para API de aulas');
check(file_contains("$root/api/services/aulas.php", '$pdo->beginTransaction()'), 'servico de aulas usa transacao');
check(file_contains("$root/api/services/aulas.php", '$pdo->commit()'), 'servico de aulas confirma transacao');
check(file_contains("$root/api/services/aulas.php", '$pdo->rollBack()'), 'servico de aulas reverte transacao em erro');
check(file_contains("$root/processar_validacao.php", 'api_aluno_validar'), 'processar_validacao delega para API de validacao');
check(file_contains("$root/api/services/validacao.php", "status = 'ativo'"), 'servico de validacao ativa aluno aprovado');
check(file_contains("$root/api/services/validacao.php", 'historico_graduacoes'), 'servico de validacao registra historico inicial');
check(file_contains("$root/processar_vivencia.php", 'api_vivencia_criar'), 'processar_vivencia delega para API de vivencia');
check(file_contains("$root/api/services/vivencia.php", 'INSERT INTO vivencia'), 'servico de vivencia grava material no acervo');
check(file_contains("$root/processar_competicao.php", 'api_competicao_criar'), 'processar_competicao delega para API de competicoes');
check(file_contains("$root/api/services/competicoes.php", 'INSERT INTO competicoes'), 'servico de competicoes grava evento');
check(file_contains("$root/inscrever_aluno.php", 'api_competicao_inscrever_aluno'), 'inscricao delega para API de inscricoes');
check(file_contains("$root/aluno_dashboard.php", 'aluno_dashbord.php'), 'wrapper aluno_dashboard aponta para arquivo existente');

$removedFiles = [
    '_index.php',
    'login_view - Copia.php',
    'login_aluno.php',
    'lista_alunos.php',
    'includes/auth - Copia.php',
    'views/dashboard_view.php',
    'views/formulario_view.php',
    'views/lista_view.php',
    'views/lista_diarios.php',
];

foreach ($removedFiles as $file) {
    check(!is_file("$root/$file"), "arquivo obsoleto removido: $file");
}

$frontFiles = [
    'index.php',
    'cadastro_aluno.php',
    'aluno_dashbord.php',
    'area_aluno.php',
    'usuarios.php',
    'historico.php',
    'imprimir.php',
    'imprimir_aula.php',
    'visualizar_aula.php',
    'views/diario_view.php',
    'views/admin_vivencia.php',
    'views/admin_competicao.php',
];

foreach ($frontFiles as $file) {
    $conteudo = file_get_contents("$root/$file");
    check(!preg_match('/\b(SELECT|INSERT\s+INTO|UPDATE|DELETE\s+FROM)\b|->\s*(prepare|query)\s*\(/', $conteudo), "front sem SQL direto: $file");
}

$compose = file_get_contents("$root/docker-compose.yml");
check(strpos($compose, '${APP_PORT:-9077}:80') !== false, 'compose publica app pela APP_PORT com padrao 9077');
check(strpos($compose, '${DB_PORT:-3377}:3306') !== false, 'compose publica mysql pela DB_PORT com padrao 3377');
check(file_contains("$root/config/db.php", "getenv('DB_HOST')"), 'config/db.php aceita DB_HOST via ambiente');
check(file_contains("$root/config/db.php", "env.php"), 'config/db.php carrega .env local');
check(file_contains("$root/config/env.php", 'carregarEnv'), 'config/env.php possui loader de .env');
check(file_contains("$root/index.php", 'htmx.org'), 'index.php carrega HTMX');
check(file_contains("$root/index.php", 'hx-get="api/dashboard.php?partial=stats"'), 'dashboard consome API via HTMX');
check(file_contains("$root/index.php", 'hx-get="api/alunos.php?partial=list'), 'lista de alunos consome API via HTMX');
check(file_contains("$root/index.php", 'hx-get="api/vivencia.php?partial=cards"'), 'vivencia consome API via HTMX');
check(file_contains("$root/index.php", 'hx-get="api/competicoes.php?partial=cards"'), 'competicoes consome API via HTMX');
check(file_contains("$root/api/helpers.php", 'api_json'), 'API possui helper JSON');
check(file_contains("$root/api/helpers.php", 'api_render'), 'API possui helper de partial HTML');
check(file_contains("$root/api/services/auth.php", 'api_auth_logout'), 'servico de auth possui logout');
check(file_contains("$root/logout.php", 'api/logout.php'), 'logout legado redireciona para API');
check(file_contains("$root/index.php", 'api/logout.php'), 'dashboard usa logout via API');
check(file_contains("$root/aluno_dashbord.php", 'api/logout.php'), 'dashboard do aluno usa logout via API');
check(file_contains("$root/aluno_dashbord.php", 'api/foto.php'), 'dashboard do aluno envia foto para API');
check(file_contains("$root/api/services/alunos.php", 'api_aluno_atualizar_foto'), 'servico de alunos possui upload de foto');
check(file_contains("$root/api/foto.php", 'api_aluno_atualizar_foto'), 'endpoint de foto usa servico de alunos');
check(file_contains("$root/cadastro_aluno.php", 'api/alunos.php'), 'formulario de cadastro envia para API de alunos');
check(file_contains("$root/api/alunos.php", "['_method'] ?? '') === 'DELETE'"), 'API de alunos processa exclusao');
check(file_contains("$root/api/alunos.php", 'atualizarAluno($dados)'), 'API de alunos processa edicao');
check(file_contains("$root/api/partials/alunos-list.php", "confirm('Deseja excluir este aluno?"), 'lista de alunos confirma exclusao');
check(file_contains("$root/api/partials/alunos-list.php", '?page=cadastro&edit='), 'lista de alunos abre edicao no cadastro');
check(file_contains("$root/views/diario_view.php", '../api/aulas.php'), 'diario envia para API de aulas');
check(file_contains("$root/views/diario_view.php", 'hx-post="../api/aulas.php"'), 'exclusao de diario usa API via HTMX');
check(file_contains("$root/views/diario_view.php", 'hx-swap="outerHTML"'), 'exclusao de diario remove linha sem redirecionar');
check(file_contains("$root/views/diario_view.php", 'hx-confirm='), 'exclusao de diario confirma antes de chamar API');
check(file_contains("$root/api/aulas.php", "api_is_htmx()"), 'API de aulas responde HTMX sem redirect na exclusao');
check(file_contains("$root/views/admin_vivencia.php", '../api/vivencia.php'), 'admin vivencia envia para API de vivencia');
check(file_contains("$root/views/admin_competicao.php", '../api/competicoes.php'), 'admin competicao envia para API de competicoes');
check(file_contains("$root/usuarios.php", 'api/usuarios.php'), 'usuarios envia operacoes para API de usuarios');

$dateInputFiles = [
    'cadastro_aluno.php',
    'views/diario_view.php',
    'views/admin_competicao.php',
];

foreach ($dateInputFiles as $file) {
    $conteudo = file_get_contents("$root/$file");
    check(strpos($conteudo, 'type="date"') === false && strpos($conteudo, "type='date'") === false, "sem input date nativo: $file");
    check(strpos($conteudo, 'input-date-br') !== false, "input data usa mascara DD/MM/AAAA: $file");
}

check(file_contains("$root/assets/js/datas.js", 'input-date-br'), 'script de mascara de data existe');
check(file_contains("$root/includes/funcoes_alunos.php", 'normalizarDataMysql'), 'helper converte data BR para MySQL');
check(file_contains("$root/includes/funcoes_alunos.php", 'formatarDataBr'), 'helper formata data MySQL para BR');
check(file_contains("$root/api/services/aulas.php", 'normalizarDataMysql'), 'servico de aulas normaliza data');
check(file_contains("$root/api/services/competicoes.php", 'normalizarDataMysql'), 'servico de competicoes normaliza data');

echo "\n";
if ($failures) {
    echo count($failures) . " falha(s) encontrada(s).\n";
    exit(1);
}

echo "Todos os testes automatizados de processos passaram.\n";
