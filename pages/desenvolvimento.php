<?php
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
    <title>Página em Desenvolvimento - Eai Cidadão!</title>
    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f5f5f5;
        }

        .header {
            background-color: #006937;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .header-left {
            display: flex;
            align-items: center;
        }

        .municipality-logo img {
            height: 60px;
            margin-right: 15px;
        }

        .title-container h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .municipality-name {
            font-size: 16px;
            font-weight: normal;
        }

        .header-buttons {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .login-area {
            display: flex;
            gap: 10px;
        }

        .login-button {
            display: flex;
            align-items: center;
            gap: 8px;
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .login-button:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }

        .user-login {
            background-color: #00563b;
        }

        .admin-login {
            background-color: #004020;
        }

        .back-button {
            display: flex;
            align-items: center;
            gap: 8px;
            background-color: #4CAF50;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .back-button:hover {
            background-color: #3e8e41;
        }

        .container {
            flex: 1;
            padding: 30px;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        .development-message {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 60px 30px;
            text-align: center;
            margin: 50px auto;
            max-width: 800px;
        }

        .development-message i {
            font-size: 80px;
            color: #006937;
            margin-bottom: 30px;
        }

        .development-message h2 {
            font-size: 32px;
            color: #333;
            margin-bottom: 20px;
        }

        .development-message p {
            font-size: 18px;
            color: #666;
            line-height: 1.6;
            max-width: 600px;
            margin: 0 auto 30px;
        }

        .return-link {
            display: inline-block;
            background-color: #006937;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .return-link:hover {
            background-color: #00563b;
        }

        footer {
            background-color: #004020;
            color: white;
            text-align: center;
            padding: 15px;
            font-size: 14px;
        }

        /* Estilos para o dropdown do usuário logado */
        .user-logged-in {
            position: relative;
            display: none; /* Oculto por padrão */
        }
        
        .user-button {
            cursor: pointer;
            padding: 8px 15px;
            border-radius: 5px;
            background-color: #4CAF50;
            color: white;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background-color: white;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            border-radius: 5px;
            min-width: 200px;
            z-index: 1000;
            display: none;
        }
        
        .user-dropdown.show {
            display: block;
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            text-decoration: none;
            color: #333;
            transition: background-color 0.3s;
        }
        
        .dropdown-item:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="header-left">
            <div class="municipality-logo">
                <!-- Substitua pelo caminho da sua logo -->
                <img src="../img/logo_municipio.png" alt="Logo do Município">
            </div>
            <div class="title-container">
                <h1>Eai Cidadão!</h1>
                <h2 class="municipality-name">Município de Santa Izabel do Oeste</h2>
            </div>
        </div>
        <div class="header-buttons">
            <!-- Área de login -->
            <div class="login-area">
                <!-- Botões de login (visíveis quando usuário não está logado) -->
                <a href="../login_cidadao.php" class="login-button user-login">
                    <i class="fas fa-user"></i>
                    Área do Cidadão
                </a>
                <a href="../login.php" class="login-button admin-login">
                    <i class="fas fa-lock"></i>
                    Área Restrita
                </a>

                <!-- Menu do usuário quando está logado (oculto por padrão) -->
                <div class="user-logged-in">
                    <div class="user-button">
                        <i class="fas fa-user-check"></i>
                        Olá, <span id="user-name">Nome do Usuário</span>
                    </div>
                    <div class="user-dropdown">
                        <a href="../app/usuario/perfil.php" class="dropdown-item">
                            <i class="fas fa-id-card"></i>
                            Meu Perfil
                        </a>
                        <a href="../controller/logout.php" class="dropdown-item">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </a>
                    </div>
                </div>
            </div>
            <!-- Botão Voltar para Página Inicial -->
            <a href="../index.php" class="back-button">
                <i class="fas fa-home"></i> 
                Página Inicial
            </a>
        </div>
    </div>

    <div class="container">
        <div class="development-message">
            <i class="fas fa-tools"></i>
            <h2>Esta página está em desenvolvimento</h2>
            <p>Estamos trabalhando para disponibilizar este conteúdo em breve. Por favor, volte mais tarde para verificar as atualizações.</p>
            <a href="../index.php" class="return-link">
                <i class="fas fa-arrow-left"></i> Voltar para a Página Inicial
            </a>
        </div>
    </div>

    <footer>
        &copy; 2025 Prefeitura Municipal de Santa Izabel do Oeste. Todos os direitos reservados.
    </footer>

    <!-- Script para exibir nome do usuário quando estiver logado -->
    <script>
    // Usando as variáveis PHP diretamente no JavaScript
    const isLoggedIn = <?php echo $usuario_logado ? 'true' : 'false'; ?>;
    const userName = "<?php echo addslashes($nome_usuario); ?>";
    
    document.addEventListener("DOMContentLoaded", function() {
        if (isLoggedIn) {
            // Oculta botões de login
            document.querySelectorAll('.login-button').forEach(btn => {
                btn.style.display = 'none';
            });
            
            // Mostra mensagem de usuário logado
            const userLoggedElement = document.querySelector('.user-logged-in');
            userLoggedElement.style.display = 'flex';
            
            // Define o nome do usuário
            document.getElementById('user-name').textContent = userName;
        } else {
            // Garante que os botões de login estejam visíveis
            document.querySelectorAll('.login-button').forEach(btn => {
                btn.style.display = 'flex';
            });
            
            // Garante que a área de usuário logado esteja oculta
            const userLoggedElement = document.querySelector('.user-logged-in');
            userLoggedElement.style.display = 'none';
        }
    });
    
    // Adiciona funcionalidade ao dropdown do usuário
    document.addEventListener("DOMContentLoaded", function() {
        const userButton = document.querySelector('.user-button');
        const userDropdown = document.querySelector('.user-dropdown');
        
        if (userButton) {
            userButton.addEventListener('click', function() {
                userDropdown.classList.toggle('show');
            });
            
            // Fecha o dropdown quando clicar fora dele
            document.addEventListener('click', function(event) {
                if (!userButton.contains(event.target) && !userDropdown.contains(event.target)) {
                    userDropdown.classList.remove('show');
                }
            });
        }
    });
</script>
</body>

</html>