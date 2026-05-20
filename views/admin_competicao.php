<?php
// views/admin_competicao.php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

// Segurança: Apenas Administradores podem gerenciar competições
if (!isset($_SESSION['usuario']) || $_SESSION['nivel'] !== 'admin') {
    header("Location: ../index.php?msg=acesso_negado");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Evento | BERIMBAU</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/estilo_padrao.css">
</head>
<body style="background: #f8fafc; padding: 20px;">

<div style="max-width: 800px; margin: 0 auto;">
    <div style="margin-bottom: 20px;">
        <a href="../index.php?page=competicao" style="text-decoration: none; color: #64748b; font-weight: bold;">
            <i class="fas fa-arrow-left"></i> Voltar para Painel
        </a>
    </div>

    <div class="card-glass" style="background: white; padding: 40px; border-radius: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.05);">
        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 30px;">
            <div style="background: #1e293b; color: white; width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-trophy" style="font-size: 24px;"></i>
            </div>
            <div>
                <h2 style="margin:0;">Cadastrar Nova Competição</h2>
                <p style="margin:0; color: #64748b;">Organize campeonatos, batizados ou torneios.</p>
            </div>
        </div>

        <form action="../processar_competicao.php" method="POST" enctype="multipart/form-data">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                
                <div style="grid-column: span 2;">
                    <label style="display:block; margin-bottom:8px; font-weight:bold; color:#1e293b;">Nome do Evento</label>
                    <input type="text" name="nome_evento" required placeholder="Ex: I Open de Capoeira Regional" 
                           style="width:100%; padding:12px; border: 1px solid #e2e8f0; border-radius:10px;">
                </div>

                <div>
                    <label style="display:block; margin-bottom:8px; font-weight:bold; color:#1e293b;">Data do Evento</label>
                    <input type="date" name="data_evento" required 
                           style="width:100%; padding:12px; border: 1px solid #e2e8f0; border-radius:10px;">
                </div>

                <div>
                    <label style="display:block; margin-bottom:8px; font-weight:bold; color:#1e293b;">Local (Cidade/Ginásio)</label>
                    <input type="text" name="local_evento" placeholder="Ex: Ginásio Municipal" 
                           style="width:100%; padding:12px; border: 1px solid #e2e8f0; border-radius:10px;">
                </div>

                <div>
                    <label style="display:block; margin-bottom:8px; font-weight:bold; color:#1e293b;">Status</label>
                    <select name="status" style="width:100%; padding:12px; border: 1px solid #e2e8f0; border-radius:10px;">
                        <option value="inscricoes_abertas">Inscrições Abertas</option>
                        <option value="em_andamento">Em Andamento</option>
                        <option value="finalizado">Finalizado</option>
                    </select>
                </div>

                <div>
                    <label style="display:block; margin-bottom:8px; font-weight:bold; color:#1e293b;">URL do Edital (Opcional)</label>
                    <input type="url" name="edital_url" placeholder="https://..." 
                           style="width:100%; padding:12px; border: 1px solid #e2e8f0; border-radius:10px;">
                </div>

                <div style="grid-column: span 2;">
                    <label style="display:block; margin-bottom:8px; font-weight:bold; color:#1e293b;">Descrição / Observações</label>
                    <textarea name="descricao" rows="4" placeholder="Detalhes sobre categorias, premiação..." 
                              style="width:100%; padding:12px; border: 1px solid #e2e8f0; border-radius:10px; resize:none;"></textarea>
                </div>
            </div>

            <div style="margin-top: 30px; display: flex; gap: 15px;">
                <button type="submit" class="btn-berimbau btn-primary" style="flex: 2; padding: 15px; border: none; border-radius: 12px; font-weight: bold; cursor: pointer;">
                    SALVAR COMPETIÇÃO
                </button>
                <a href="../index.php?page=competicao" style="flex: 1; text-align: center; padding: 15px; background: #f1f5f9; color: #1e293b; text-decoration: none; border-radius: 12px; font-weight: bold;">
                    CANCELAR
                </a>
            </div>
        </form>
    </div>
</div>

</body>
</html>