<?php
// Inicia a sessão (sempre coloque isso no início do arquivo PHP)
session_start();

// Verifica se o usuário está logado
$usuario_logado = isset($_SESSION['user_logado']) && $_SESSION['user_logado'] === true;
$nome_usuario = isset($_SESSION['user_nome']) ? $_SESSION['user_nome'] : '';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eai Cidadão!</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #64b5f6 0%, #6aabec 100%);
            min-height: 100vh;
        }

        .header {
            background-color: rgba(13, 71, 161, 0.9);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .logo {
            font-size: 1.6rem;
            font-weight: bold;
        }

        .nav-buttons {
            display: flex;
            gap: 15px;
        }

        .button {
            display: inline-block;
            background-color: #ffffff;
            color: #0d47a1;
            padding: 8px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .button:hover {
            background-color: #e3f2fd;
            transform: translateY(-2px);
        }

        .button i {
            margin-right: 8px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            color: white;
        }
        
        .user-avatar {
            background-color: #ffffff;
            color: #0d47a1;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 10px;
            font-size: 16px;
        }
        
        .user-name {
            font-weight: 600;
            font-size: 1rem;
        }
        
        .logout-button {
            margin-left: 15px;
            color: white;
            background-color: rgba(255, 255, 255, 0.2);
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.8rem;
            transition: all 0.3s;
        }
        
        .logout-button:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }

        .content {
            padding: 40px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                padding: 15px;
            }
            
            .logo {
                margin-bottom: 15px;
            }
            
            .nav-buttons {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <i class="fas fa-city"></i> Eai Cidadão!
        </div>
        
        <?php if ($usuario_logado): ?>
            <!-- Mostrar informações do usuário quando logado -->
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="user-name">
                    Olá, <?php echo htmlspecialchars($nome_usuario); ?>
                </div>
                <a href="logout.php" class="logout-button">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </div>
        <?php else: ?>
            <!-- Mostrar botões de login/cadastro quando não logado -->
            <div class="nav-buttons">
                <a href="login_cidadao.php" class="button">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
                <a href="cadastro_cidadao.php" class="button">
                    <i class="fas fa-user-plus"></i> Cadastrar
                </a>
            </div>
        <?php endif; ?>
    </div>

    <div class="content">
        <!-- Conteúdo principal da página -->
        <h1>Bem-vindo ao sistema Eai Cidadão!</h1>
        
        <?php if ($usuario_logado): ?>
            <p>Você está logado como <?php echo htmlspecialchars($nome_usuario); ?>.</p>
            <!-- Conteúdo específico para usuários logados -->
        <?php else: ?>
            <p>Faça login ou cadastre-se para acessar os serviços da prefeitura.</p>
            <!-- Conteúdo para visitantes não logados -->
        <?php endif; ?>
    </div>
</body>
</html>