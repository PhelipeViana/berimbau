<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login - Sistema Capoeira</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f8fafc; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .login-box { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); width: 100%; max-width: 380px; border: 1px solid #e2e8f0; }
        input { width: 100%; padding: 14px; margin: 10px 0; border: 1px solid #cbd5e1; border-radius: 12px; box-sizing: border-box; font-size: 14px; }
        input:focus { border-color: #6366f1; outline: none; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); }
        
        /* Botão Principal */
        .btn-entrar { width: 100%; padding: 14px; background: #1e293b; color: white; border: none; border-radius: 12px; cursor: pointer; font-weight: 700; font-size: 14px; margin-top: 10px; }
        .btn-entrar:hover { background: #0f172a; }

        /* Divisor e Link de Aluno */
        .divider { margin-top: 25px; padding-top: 20px; border-top: 1px solid #f1f5f9; text-align: center; }
        .btn-solicitar {
            display: inline-flex; 
            align-items: center; 
            gap: 8px; 
            color: #6366f1; 
            text-decoration: none; 
            font-weight: 700; 
            font-size: 13px;
            padding: 10px 20px;
            border: 1px solid #e2e8f0;
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        .btn-solicitar:hover { background: #f5f3ff; border-color: #6366f1; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2 style="text-align:center; color:#0f172a; margin-bottom: 30px; letter-spacing: -1px;">SISTEMA <span style="color:#6366f1">CAPOEIRA</span></h2>
        
        <?php if(isset($erro_login)): ?>
            <p style="color:#ef4444; font-size:13px; text-align:center; background:#fee2e2; padding:10px; border-radius:8px;"><?= $erro_login ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="usuario" placeholder="Seu usuário ou e-mail" required>
            <input type="password" name="senha" placeholder="Sua senha" required>
            <button type="submit" name="login" class="btn-entrar">ENTRAR NO SISTEMA</button>
        </form>

        <div class="divider">
            <p style="font-size: 13px; color: #64748b; margin-bottom: 15px;">É aluno da escola?</p>
            <a href="cadastro_aluno.php" class="btn-solicitar">
                <i class="fas fa-user-plus"></i>
                SOLICITAR MEU ACESSO
            </a>
        </div>
    </div>
</body>
</html>