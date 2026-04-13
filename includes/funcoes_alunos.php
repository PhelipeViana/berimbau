<?php
require_once __DIR__ . '/../config/db.php';

// ==========================================
// 1. FUNÇÕES DE ALUNOS
// ==========================================

function buscarAlunoPorId($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM alunos WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function salvarAluno($dados) {
    global $pdo;
    $foto = $dados['foto'] ?? 'padrao.png';
    
    // Garantir que o usuario_id não seja nulo (pega do POST ou da SESSION)
    $usuario_id = $dados['usuario_id'] ?? ($_SESSION['usuario_id'] ?? null);

    if (!$usuario_id) return false;

    $sql = "INSERT INTO alunos (foto, nome, apelido, nascimento, celular, email, mae, pai, endereco, cidade, graduacao, docente, local_treino, saude, usuario_id, senha, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    
    return $stmt->execute([
        $foto,
        $dados['nome'] ?? '',
        $dados['apelido'] ?? '',
        $dados['nascimento'] ?? date('Y-m-d'),
        $dados['celular'] ?? '',
        $dados['email'] ?? '',
        $dados['mae'] ?? '',
        $dados['pai'] ?? '',
        $dados['endereco'] ?? '',
        $dados['cidade'] ?? '',
        $dados['graduacao'] ?? 'INICIANTE',
        $dados['docente'] ?? '',
        $dados['local_treino'] ?? '',
        $dados['saude'] ?? '',
        $usuario_id,
        $dados['senha'] ?? null, // Senha já deve vir criptografada do index.php
        $dados['status'] ?? 'pendente'
    ]);
}

function atualizarAluno($dados) {
    global $pdo;
    
    // Preparação dinâmica para a senha: só atualiza se uma nova senha for definida
    $sql_senha = "";
    $params = [
        $dados['foto'] ?? 'padrao.png',
        $dados['nome'], 
        $dados['apelido'] ?? '', 
        $dados['nascimento'] ?? null, 
        $dados['celular'] ?? '', 
        $dados['email'] ?? '', 
        $dados['mae'] ?? '', 
        $dados['pai'] ?? '', 
        $dados['endereco'] ?? '', 
        $dados['cidade'] ?? '', 
        $dados['graduacao'], 
        $dados['docente'] ?? '', 
        $dados['local_treino'] ?? '', 
        $dados['saude'] ?? '', 
        $dados['usuario_id'],
        $dados['status'] ?? 'pendente'
    ];

    if (!empty($dados['senha'])) {
        $sql_senha = ", senha = ?";
        $params[] = $dados['senha'];
    }

    $params[] = $dados['id'];

    $sql = "UPDATE alunos SET 
                foto=?, nome=?, apelido=?, nascimento=?, celular=?, email=?, 
                mae=?, pai=?, endereco=?, cidade=?, graduacao=?, docente=?, 
                local_treino=?, saude=?, usuario_id=?, status=? 
                $sql_senha 
            WHERE id=?";
            
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

function excluirAluno($id) {
    global $pdo;
    $nivel = $_SESSION['nivel'];
    $usuario_nome = $_SESSION['usuario'];

    if ($nivel === 'admin') {
        $stmt = $pdo->prepare("DELETE FROM alunos WHERE id = ?");
        return $stmt->execute([$id]);
    } else {
        // Só deleta se o ID do aluno pertencer ao docente logado
        $stmt = $pdo->prepare("DELETE FROM alunos WHERE id = ? AND docente = ?");
        return $stmt->execute([$id, $usuario_nome]);
    }
}

function listarAlunos() {
    global $pdo;
    $nivel = $_SESSION['nivel'] ?? 'docente';
    $usuario_nome = $_SESSION['usuario']; // Nome do docente logado (ex: "MESTRE BIRO")

    if ($nivel === 'admin') {
        // Admin vê absolutamente tudo
        $stmt = $pdo->query("SELECT * FROM alunos ORDER BY nome ASC");
    } else {
        // Docente vê apenas alunos onde o campo 'docente' coincide com o seu nome de usuário
        $stmt = $pdo->prepare("SELECT * FROM alunos WHERE docente = ? ORDER BY nome ASC");
        $stmt->execute([$usuario_nome]);
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function obterEstatisticas() {
    global $pdo;
    $nivel = $_SESSION['nivel'] ?? 'docente';
    $usuario_nome = $_SESSION['usuario'];

    if ($nivel === 'admin') {
        $total = $pdo->query("SELECT COUNT(*) FROM alunos")->fetchColumn();
        $alertas = $pdo->query("SELECT COUNT(*) FROM alunos WHERE saude != '' AND saude IS NOT NULL")->fetchColumn();
    } else {
        // Filtra contagem pelo nome do docente
        $stmtT = $pdo->prepare("SELECT COUNT(*) FROM alunos WHERE docente = ?");
        $stmtT->execute([$usuario_nome]);
        $total = $stmtT->fetchColumn();

        $stmtA = $pdo->prepare("SELECT COUNT(*) FROM alunos WHERE docente = ? AND saude != '' AND saude IS NOT NULL");
        $stmtA->execute([$usuario_nome]);
        $alertas = $stmtA->fetchColumn();
    }

    return ['total' => $total, 'alertas_saude' => $alertas];
}

// ==========================================
// 2. FUNÇÕES DE USUÁRIOS (GESTÃO E ACESSO)
// ==========================================

function buscarUsuarioPorId($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id, usuario, nivel FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function atualizarUsuario($id, $usuario, $nivel, $nova_senha = null) {
    global $pdo;
    if (!empty($nova_senha)) {
        $senhaHash = password_hash($nova_senha, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET usuario = ?, nivel = ?, senha = ? WHERE id = ?";
        return $pdo->prepare($sql)->execute([$usuario, $nivel, $senhaHash, $id]);
    } else {
        $sql = "UPDATE usuarios SET usuario = ?, nivel = ? WHERE id = ?";
        return $pdo->prepare($sql)->execute([$usuario, $nivel, $id]);
    }
}

function salvarUsuario($usuario, $senha, $nivel) {
    global $pdo;
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    $sql = "INSERT INTO usuarios (usuario, senha, nivel) VALUES (?, ?, ?)";
    return $pdo->prepare($sql)->execute([$usuario, $senhaHash, $nivel]);
}

function listarUsuarios() {
    global $pdo;
    return $pdo->query("SELECT id, usuario, nivel FROM usuarios ORDER BY usuario ASC")->fetchAll(PDO::FETCH_ASSOC);
}

function listarUsuariosDocentes() {
    global $pdo;
    return $pdo->query("SELECT id, usuario FROM usuarios WHERE nivel = 'docente' ORDER BY usuario ASC")->fetchAll(PDO::FETCH_ASSOC);
}

function excluirUsuario($id) {
    global $pdo;
    if ($id == $_SESSION['usuario_id']) return false; 
    return $pdo->prepare("DELETE FROM usuarios WHERE id = ?")->execute([$id]);
}

function preCadastroAluno($dados) {
    global $pdo;
    $senhaHash = password_hash($dados['senha'], PASSWORD_DEFAULT);
    
    // Agora salvamos 'docente' (o nome selecionado) e definimos o status como 'pendente'
    $sql = "INSERT INTO alunos (nome, email, senha, status, graduacao, docente, usuario_id) 
            VALUES (?, ?, ?, 'pendente', 'INICIANTE', ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    
    // usuario_id pode ser 0 ou o ID de um admin principal para monitoramento
    $admin_id = 1; 

    return $stmt->execute([
        $dados['nome'], 
        $dados['email'], 
        $senhaHash, 
        $dados['docente'], 
        $admin_id
    ]);
}

// ==========================================
// 3. FUNÇÕES DE HISTÓRICO E DIÁRIO
// ==========================================

function registrarTrocaCorda($aluno_id, $antiga, $nova, $obs = '') {
    global $pdo;
    if ($antiga !== $nova) {
        $sql = "INSERT INTO historico_graduacoes (aluno_id, graduacao_anterior, graduacao_nova, observacao) VALUES (?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([$aluno_id, $antiga, $nova, $obs]);
    }
}

function buscarHistorico($aluno_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM historico_graduacoes WHERE aluno_id = ? ORDER BY data_mudanca DESC");
    $stmt->execute([$aluno_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function salvarAula($dados, $alunoid_presentes) {
    global $pdo;
    $docente_id = $_SESSION['usuario_id'] ?? null;
    if (!$docente_id) return false; 

    $sql = "INSERT INTO aulas (docente_id, tema_aula, descricao_atividades, local_treino) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$docente_id, $dados['tema'], $dados['atividades'], $dados['local']]);
    
    $aula_id = $pdo->lastInsertId();

    foreach ($alunoid_presentes as $aluno_id) {
        $pdo->prepare("INSERT INTO presencas (aula_id, aluno_id) VALUES (?, ?)")->execute([$aula_id, $aluno_id]);
    }
    return true;
}

function listarAulasRecentes() {
    global $pdo;
    $nivel = $_SESSION['nivel'] ?? 'docente';
    $usuario_id = $_SESSION['usuario_id'] ?? 0;

    if ($nivel === 'admin') {
        $sql = "SELECT a.*, (SELECT COUNT(*) FROM presencas WHERE aula_id = a.id) as total_presentes, u.usuario as nome_docente
                FROM aulas a JOIN usuarios u ON a.docente_id = u.id ORDER BY a.data_aula DESC LIMIT 20";
        $stmt = $pdo->query($sql);
    } else {
        $sql = "SELECT a.*, (SELECT COUNT(*) FROM presencas WHERE aula_id = a.id) as total_presentes 
                FROM aulas a WHERE a.docente_id = ? ORDER BY a.data_aula DESC LIMIT 20";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}