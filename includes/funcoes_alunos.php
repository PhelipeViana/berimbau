<?php
require_once __DIR__ . '/../config/db.php';

function normalizarDataMysql($data) {
    $data = trim((string) $data);
    if ($data === '') {
        return null;
    }

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
        return $data;
    }

    $dt = DateTime::createFromFormat('d/m/Y', $data);
    if ($dt instanceof DateTime && $dt->format('d/m/Y') === $data) {
        return $dt->format('Y-m-d');
    }

    return $data;
}

function formatarDataBr($data) {
    $data = trim((string) $data);
    if ($data === '') {
        return '';
    }

    if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $data)) {
        return $data;
    }

    $timestamp = strtotime($data);
    return $timestamp ? date('d/m/Y', $timestamp) : $data;
}

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

    $senha_hash = "";
    if (!empty($dados['senha'])) {
        $senha_hash = password_hash($dados['senha'], PASSWORD_DEFAULT);
    }

    if (!empty($dados['id']) && empty($dados['senha'])) {
        $stmt = $pdo->prepare("SELECT senha FROM alunos WHERE id = ?");
        $stmt->execute([$dados['id']]);
        $senha_hash = $stmt->fetchColumn();
    }

    // ON DUPLICATE KEY UPDATE atualizado para incluir o status e docente_id
    $sql = "INSERT INTO alunos (nome, apelido, email, senha, nascimento, celular, endereco, graduacao, status, docente_id) 
            VALUES (:nome, :apelido, :email, :senha, :nascimento, :celular, :endereco, :graduacao, :status, :docente_id)
            ON DUPLICATE KEY UPDATE 
            nome = VALUES(nome), apelido = VALUES(apelido), email = VALUES(email), 
            senha = VALUES(senha), nascimento = VALUES(nascimento), celular = VALUES(celular), 
            endereco = VALUES(endereco), graduacao = VALUES(graduacao), 
            status = VALUES(status), docente_id = VALUES(docente_id)";

    $stmt = $pdo->prepare($sql);
    
    $status = (!empty($dados['id'])) ? 'ativo' : 'pendente';
    $docente_id = (!empty($dados['docente_id'])) ? $dados['docente_id'] : null;

    return $stmt->execute([
        ':nome'        => $dados['nome'],
        ':apelido'     => $dados['apelido'],
        ':email'       => $dados['email'],
        ':senha'       => $senha_hash,
        ':nascimento'  => normalizarDataMysql($dados['nascimento'] ?? null),
        ':celular'     => $dados['celular'],
        ':endereco'    => $dados['endereco'],
        ':graduacao'   => $dados['graduacao'],
        ':status'      => $status,
        ':docente_id'  => $docente_id
    ]);
}

function atualizarAluno($dados) {
    global $pdo;
    
    $params = [
        $dados['foto'] ?? 'padrao.png',
        $dados['nome'], 
        $dados['apelido'] ?? '', 
        normalizarDataMysql($dados['nascimento'] ?? null),
        $dados['celular'] ?? '', 
        $dados['email'] ?? '', 
        $dados['mae'] ?? '', 
        $dados['pai'] ?? '', 
        $dados['endereco'] ?? '', 
        $dados['cidade'] ?? '', 
        $dados['graduacao'], 
        $dados['docente'] ?? '', 
        $dados['docente_id'] ?? null,
        $dados['local_treino'] ?? '', 
        $dados['saude'] ?? '', 
        $dados['usuario_id'],
        $dados['status'] ?? 'pendente'
    ];

    $sql_senha = "";
    if (!empty($dados['senha'])) {
        $sql_senha = ", senha = ?";
        $params[] = $dados['senha'];
    }

    $params[] = $dados['id'];

    $sql = "UPDATE alunos SET 
                foto=?, nome=?, apelido=?, nascimento=?, celular=?, email=?, 
                mae=?, pai=?, endereco=?, cidade=?, graduacao=?, docente=?, docente_id=?,
                local_treino=?, saude=?, usuario_id=?, status=? 
                $sql_senha 
            WHERE id=?";
            
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

function excluirAluno($id) {
    global $pdo;
    $nivel = $_SESSION['nivel'] ?? 'visitante';
    $usuario_id = $_SESSION['usuario_id'];

    if ($nivel === 'admin') {
        $stmt = $pdo->prepare("DELETE FROM alunos WHERE id = ?");
        return $stmt->execute([$id]);
    } else {
        $stmt = $pdo->prepare("DELETE FROM alunos WHERE id = ? AND docente_id = ?");
        return $stmt->execute([$id, $usuario_id]);
    }
}

function listarAlunos() {
    global $pdo;
    $nivel = $_SESSION['nivel'] ?? 'visitante';
    $usuario_id = $_SESSION['usuario_id'];

    if ($nivel === 'admin') {
        $stmt = $pdo->query("SELECT * FROM alunos ORDER BY nome ASC");
    } else {
        $stmt = $pdo->prepare("SELECT * FROM alunos WHERE docente_id = ? ORDER BY nome ASC");
        $stmt->execute([$usuario_id]);
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function obterEstatisticas() {
    global $pdo;
    $nivel = $_SESSION['nivel'] ?? 'visitante';
    $usuario_id = $_SESSION['usuario_id'];

    if ($nivel === 'admin') {
        $total = $pdo->query("SELECT COUNT(*) FROM alunos")->fetchColumn();
        $alertas = $pdo->query("SELECT COUNT(*) FROM alunos WHERE saude != '' AND saude IS NOT NULL")->fetchColumn();
    } else {
        $stmtT = $pdo->prepare("SELECT COUNT(*) FROM alunos WHERE docente_id = ?");
        $stmtT->execute([$usuario_id]);
        $total = $stmtT->fetchColumn();

        $stmtA = $pdo->prepare("SELECT COUNT(*) FROM alunos WHERE docente_id = ? AND saude != '' AND saude IS NOT NULL");
        $stmtA->execute([$usuario_id]);
        $alertas = $stmtA->fetchColumn();
    }

    return ['total' => $total, 'alertas_saude' => $alertas];
}

// ==========================================
// 2. FUNÇÕES DE USUÁRIOS
// ==========================================

function buscarUsuarioPorId($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id, usuario, nivel FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function atualizarUsuario($id, $usuario, $nivel, $nova_senha = null) {
    global $pdo;
    if ($nivel === 'docente') {
        $nivel = 'visitante';
    }
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
    if ($nivel === 'docente') {
        $nivel = 'visitante';
    }
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
    return $pdo->query("SELECT id, usuario FROM usuarios WHERE nivel != 'admin' ORDER BY usuario ASC")->fetchAll(PDO::FETCH_ASSOC);
}

function excluirUsuario($id) {
    global $pdo;
    if ($id == $_SESSION['usuario_id']) return false; 
    return $pdo->prepare("DELETE FROM usuarios WHERE id = ?")->execute([$id]);
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
    $nivel = $_SESSION['nivel'] ?? 'visitante';
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

function obterFrequenciaAluno($aluno_id) {
    global $pdo;
    $total_aulas = $pdo->query("SELECT COUNT(*) FROM aulas")->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM presencas WHERE aluno_id = ?");
    $stmt->execute([$aluno_id]);
    $presencas = $stmt->fetchColumn();
    
    $faltas = $total_aulas - $presencas;
    $aproveitamento = ($total_aulas > 0) ? round(($presencas / $total_aulas) * 100) : 0;
    
    return [
        'presencas' => $presencas,
        'faltas' => $faltas,
        'aproveitamento' => $aproveitamento
    ];
}

function buscarDiarioPorId($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM aulas WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
