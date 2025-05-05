<?php
// Inicia a sessão
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['user_logado']) || $_SESSION['user_logado'] !== true) {
    // Se não estiver logado, redireciona para a página de login
    header("Location: ../../login_cidadao.php");
    exit();
}

// Obtém informações do usuário da sessão
$nome_usuario = $_SESSION['user_nome'];
$email_usuario = $_SESSION['user_email'];
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área do Cidadão - Eai Cidadão!</title>
    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- CSS personalizado -->
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .welcome-box {
            background-color: #e8f5e9;
            border-left: 4px solid #2e7d32;
            padding: 15px;
            margin-bottom: 30px;
            border-radius: 8px;
            text-align: left;
        }
        
        .welcome-box h3 {
            color: #2e7d32;
            margin-bottom: 5px;
        }
        
        .welcome-box p {
            color: #555;
            margin: 0;
        }
        
        .service-cards {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        @media (min-width: 768px) {
            .service-cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        .service-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: left;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }
        
        .service-card h4 {
            color: #2e7d32;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        
        .service-card h4 i {
            margin-right: 10px;
        }
        
        .service-card p {
            color: #666;
            margin-bottom: 15px;
        }
        
        .card-actions {
            display: flex;
            justify-content: flex-end;
        }
        
        .btn-action {
            background-color: #2e7d32;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        
        .btn-action:hover {
            background-color: #1b5e20;
        }
        
        .logout-btn {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }
        
        .logout-btn:hover {
            background-color: #d32f2f;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header-container">
            <div class="municipality-logo">
                <!-- Substitua pelo caminho da sua logo -->
                <img src="../../img/logo_municipio.png" alt="Logo do Município">
            </div>
            <div class="title-container">
                <h1>Eai Cidadão!</h1>
                <h2 class="municipality-name">Município de Santa Izabel do Oeste</h2>
            </div>
        </div>

        <div class="divider"></div>

        <div class="welcome-box">
            <h3>Bem-vindo(a), <?php echo htmlspecialchars($nome_usuario); ?>!</h3>
            <p>Aqui você pode acessar todos os serviços disponíveis para você.</p>
        </div>

        <h3 style="margin-bottom: 20px; color: #2e7d32; text-align: left;">Meus Serviços</h3>

        <div class="service-cards">
            <div class="service-card">
                <h4><i class="fas fa-file-alt"></i> Minhas Solicitações</h4>
                <p>Acompanhe o status das suas solicitações enviadas para a prefeitura.</p>
                <div class="card-actions">
                    <a href="minhas_solicitacoes.php" class="btn-action">Ver Todas</a>
                </div>
            </div>

            <div class="service-card">
                <h4><i class="fas fa-plus-circle"></i> Nova Solicitação</h4>
                <p>Envie uma nova solicitação ou pedido para a prefeitura.</p>
                <div class="card-actions">
                    <a href="../../index.php" class="btn-action">Criar</a>
                </div>
            </div>

            <div class="service-card">
                <h4><i class="fas fa-file-invoice"></i> Meus Documentos</h4>
                <p>Acesse seus documentos emitidos pela prefeitura.</p>
                <div class="card-actions">
                    <a href="meus_documentos.php" class="btn-action">Acessar</a>
                </div>
            </div>

            <div class="service-card">
                <h4><i class="fas fa-user-cog"></i> Meu Perfil</h4>
                <p>Atualize suas informações pessoais e altere sua senha.</p>
                <div class="card-actions">
                    <a href="editar_perfil.php" class="btn-action">Editar</a>
                </div>
            </div>
        </div>

        <form action="../../controller/logout_user.php" method="post">
            <button type="submit" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Sair
            </button>
        </form>

        <div class="footer">
            &copy; 2025 Prefeitura Municipal de Santa Izabel do Oeste. Todos os direitos reservados.
        </div>
    </div>
</body>

</html>