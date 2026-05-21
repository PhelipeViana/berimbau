<?php 
// Inclui a lógica de autenticação antes de renderizar o HTML
require_once __DIR__ . '/includes/auth.php'; 
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BERIMBAU</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --green: #0f7a3a;
            --green-dark: #0a5f2e;
            --gold: #f3b51b;
            --clay: #c9561a;
            --ink: #18251c;
            --paper: #f4f1e8;
            --muted: #667062;
        }
        * { box-sizing: border-box; }
        body {
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background:
                linear-gradient(135deg, rgba(15, 122, 58, 0.14), transparent 36%),
                linear-gradient(315deg, rgba(243, 181, 27, 0.18), transparent 42%),
                var(--paper);
            display: grid;
            place-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 24px;
            color: var(--ink);
        }
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            opacity: 0.28;
            background-image:
                linear-gradient(90deg, rgba(24, 37, 28, 0.05) 1px, transparent 1px),
                linear-gradient(rgba(24, 37, 28, 0.04) 1px, transparent 1px);
            background-size: 34px 34px;
        }
        .login-box {
            position: relative;
            background: rgba(255, 255, 255, 0.9);
            padding: clamp(26px, 4vw, 42px);
            border-radius: 8px;
            box-shadow: 0 22px 60px rgba(28, 38, 24, 0.14);
            width: 100%;
            max-width: 420px;
            border: 1px solid rgba(43, 63, 43, 0.14);
            backdrop-filter: blur(16px);
            overflow: hidden;
        }
        .login-box::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 7px;
            background: linear-gradient(90deg, var(--green), var(--gold), var(--clay));
        }
        .brand-mark {
            width: 58px;
            height: 58px;
            margin: 0 auto 16px;
            border-radius: 8px;
            background: linear-gradient(160deg, var(--gold), var(--clay));
            display: grid;
            place-items: center;
            color: #fff;
            box-shadow: 0 12px 26px rgba(201, 86, 26, 0.24);
        }
        input {
            width: 100%;
            padding: 14px;
            margin: 10px 0;
            border: 1px solid rgba(43, 63, 43, 0.18);
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 14px;
            background: rgba(255,255,255,0.78);
            color: var(--ink);
        }
        input:focus { border-color: var(--green); outline: none; box-shadow: 0 0 0 4px rgba(15, 122, 58, 0.12); }

        .btn-entrar { width: 100%; padding: 14px; background: linear-gradient(135deg, var(--green), var(--green-dark)); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 900; font-size: 14px; margin-top: 10px; }
        .btn-entrar:hover { filter: brightness(1.04); }

        .divider { margin-top: 25px; padding-top: 20px; border-top: 1px solid rgba(43, 63, 43, 0.12); text-align: center; }
        .btn-solicitar {
            display: inline-flex; 
            align-items: center; 
            gap: 8px; 
            color: var(--green); 
            text-decoration: none; 
            font-weight: 900; 
            font-size: 13px;
            padding: 11px 18px;
            border: 1px solid rgba(15, 122, 58, 0.2);
            border-radius: 999px;
            transition: all 0.3s ease;
        }
        .btn-solicitar:hover { background: rgba(15, 122, 58, 0.08); border-color: var(--green); }
        
        .alert-error { color:#ef4444; font-size:13px; text-align:center; background:#fee2e2; padding:10px; border-radius:8px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="brand-mark"><i class="fas fa-drum"></i></div>
        <h2 style="text-align:center; color:#18251c; margin-bottom: 30px; letter-spacing: 0;">
            BERIMBAU<br>
            <span style="color:#667062; font-size: 0.58em; font-weight: 700;">SISTEMA DE GESTÃO PARA ESCOLAS DE CAPOEIRA</span>
        </h2>
        
        <?php if(isset($erro_login)): ?>
            <div class="alert-error"><?= htmlspecialchars($erro_login) ?></div>
        <?php endif; ?>

        <form method="POST" action="login_view.php">
            <input type="text" name="usuario" placeholder="Usuário (Admin) ou E-mail (Aluno)" required>
            <input type="password" name="senha" placeholder="Sua senha" required>
            <button type="submit" name="login" class="btn-entrar">ENTRAR NO SISTEMA</button>
        </form>

        <div class="divider">
            <p style="font-size: 13px; color: #667062; margin-bottom: 15px;">É aluno da escola?</p>
            <a href="cadastro_aluno.php" class="btn-solicitar">
                <i class="fas fa-user-plus"></i>
                SOLICITAR MEU ACESSO
            </a>
        </div>
    </div>
</body>
</html>
