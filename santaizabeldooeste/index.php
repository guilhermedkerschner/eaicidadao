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
    <link rel="icon" href="./images/logo_eai.ico" type="imagem/x-icon">
    <title>Eai Cidadão! - Município de Santa Izabel do Oeste</title>
    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="./css/index.css"/>
    <style>
        /* Estilos para os novos botões de ação */
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin: 40px 0;
            padding: 0 20px;
        }

        .action-button {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 20px 35px;
            border: 2px solid;
            border-radius: 15px;
            font-size: 17px;
            font-weight: 600;
            text-decoration: none;
            color: white;
            transition: all 0.3s ease;
            cursor: pointer;
            min-width: 200px;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .action-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            background-color: white !important;
            background-image: none !important;
        }

        .action-button i {
            font-size: 18px;
        }

        .btn-register {
            background: linear-gradient(135deg, #007bff, #0056b3);
            border-color: #007bff;
        }

        .btn-register:hover {
            color: #007bff !important;
            border-color: #007bff;
        }

        .btn-login {
            background: linear-gradient(135deg, #27ae60, #229954);
            border-color: #27ae60;
        }

        .btn-login:hover {
            color: #27ae60 !important;
            border-color: #27ae60;
        }

        /* Área do usuário logado */
        .user-welcome-area {
            display: none;
            justify-content: center;
            align-items: center;
            margin: 40px 20px;
            padding: 30px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            width: calc(100% - 40px);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 25px;
            width: 100%;
            max-width: 800px;
        }

        .user-avatar {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            border: 4px solid #27ae60;
            object-fit: cover;
            box-shadow: 0 6px 15px rgba(39, 174, 96, 0.3);
            flex-shrink: 0;
        }

        .user-details {
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex-grow: 1;
        }

        .user-welcome-text {
            font-size: 28px;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }

        .user-subtitle {
            font-size: 18px;
            color: #7f8c8d;
            margin: 0;
        }

        .user-buttons {
            display: flex;
            gap: 15px;
            flex-shrink: 0;
        }

        .profile-button {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 18px 28px;
            background: linear-gradient(135deg, #27ae60, #229954);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 6px 18px rgba(39, 174, 96, 0.3);
            border: 2px solid #27ae60;
        }

        .profile-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(39, 174, 96, 0.4);
            background: white;
            color: #27ae60 !important;
            border-color: #27ae60;
        }

        .logout-button {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 18px 28px;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 6px 18px rgba(231, 76, 60, 0.3);
            border: 2px solid #e74c3c;
        }

        .logout-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(231, 76, 60, 0.4);
            background: white;
            color: #e74c3c !important;
            border-color: #e74c3c;
        }

        .profile-button i,
        .logout-button i {
            font-size: 18px;
        }

        /* Responsividade para os botões */
        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
                align-items: center;
                gap: 20px;
            }

            .action-button {
                width: 100%;
                max-width: 300px;
                padding: 18px 30px;
            }

            .user-welcome-area {
                margin: 30px 10px;
                padding: 25px 20px;
            }

            .user-info {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }

            .user-avatar {
                width: 80px;
                height: 80px;
            }

            .user-details {
                align-items: center;
            }

            .user-buttons {
                flex-direction: column;
                gap: 12px;
            }

            .profile-button,
            .logout-button {
                justify-content: center;
                padding: 16px 24px;
            }

            .user-welcome-text {
                font-size: 22px;
            }

            .user-subtitle {
                font-size: 16px;
            }
        }
    </style>
</head>

<body>
    <header class="header">
        <div class="header-left">
            <div class="municipality-logo">
                <img src="./img/logo_municipio.png" alt="Logo do Município">
            </div>
            <div class="title-container">
                <h1>Eai Cidadão!</h1>
                <h2 class="municipality-name">Município de Santa Izabel do Oeste</h2>
            </div>
        </div>

        <div class="login-area">
            <a href="login.php" class="login-button admin-login">
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
                    <a href="./app/usuario/perfil.php" class="dropdown-item">
                        <i class="fas fa-id-card"></i>
                        Meu Perfil
                    </a>
                    <a href="./controller/logout_user.php" class="dropdown-item">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="main-container">
        <!-- Botões de ação principais -->
        <div class="action-buttons">
            <a href="cad_user.php" class="action-button btn-register">
                <i class="fas fa-user-plus"></i>
                Cadastre-se
            </a>
            <a href="login_cidadao.php" class="action-button btn-login">
                <i class="fas fa-sign-in-alt"></i>
                Login Cidadão
            </a>
        </div>

        <!-- Área de boas-vindas para usuário logado -->
        <div class="user-welcome-area">
            <div class="user-info">
                <img src="./uploads/fotos_perfil/default_avatar.png" alt="Foto do usuário" class="user-avatar" id="user-avatar">
                <div class="user-details">
                    <h2 class="user-welcome-text">Olá, <span id="user-display-name">Nome do Usuário</span>!</h2>
                    <p class="user-subtitle">Seja bem-vindo ao Portal do Cidadão</p>
                </div>
                <div class="user-buttons">
                    <a href="./app/usuario/perfil.php" class="profile-button">
                        <i class="fas fa-user-cog"></i>
                        Meu Perfil
                    </a>
                    <a href="./controller/logout_user.php" class="logout-button">
                        <i class="fas fa-sign-out-alt"></i>
                        Sair
                    </a>
                </div>
            </div>
        </div>

        <!-- Mensagem de boas-vindas e instruções -->
        <div class="welcome-message">
            <h2>Bem-vindo ao Portal do Cidadão!</h2>
            <p>Aqui você pode acessar todos os serviços disponibilizados pela Prefeitura de Santa Izabel do Oeste.</p>
            <div class="instructions">
                <p><i class="fas fa-info-circle"></i> Para fazer uma solicitação, selecione uma das áreas abaixo e siga as instruções na página correspondente.</p>
                <p><i class="fas fa-exclamation-circle"></i> Caso precise de ajuda, entre em contato com nossa central de atendimento pelos contatos no rodapé desta página.</p>
            </div>
        </div>
        
        <div class="divider"></div>

        <div class="options">
            <!--
            <a href="" class="option-box">
                <i class="fas fa-volleyball-ball"></i>
                <h3>Esporte</h3>
            </a>-->
            <a href="./pages/social.php" class="option-box">
                <i class="fas fa-heart"></i>
                <h3>Assistência Social</h3>
            </a>
            <!--
            <a href="" class="option-box">
                <i class="fas fa-seedling"></i>
                <h3>Agricultura</h3>
            </a>
            <a href="" class="option-box">
                <i class="fas fa-bus-alt"></i>
                <h3>Rodoviário</h3>
            </a>
            <a href="" class="option-box">
                <i class="fas fa-tree"></i>
                <h3>Meio Ambiente</h3>
            </a>
            <a href="" class="option-box">
                <i class="fas fa-building"></i>
                <h3>Serviços Urbanos</h3>
            </a>
            <a href="" class="option-box">
                <i class="fas fa-book-open"></i>
                <h3>Educação</h3>
            </a>
            <a href="" class="option-box">
                <i class="fas fa-search-dollar"></i>
                <h3>Fiscalização</h3>
            </a>
            <a href="" class="option-box">
                <i class="fas fa-tools"></i>
                <h3>Obras</h3>
            </a>
            <a href="" class="option-box">
                <i class="fas fa-monument"></i>
                <h3>Cultura e Turismo</h3>
            </a>
            <a href="" class="option-box">
                <i class="fas fa-landmark"></i>
                <h3>Fazenda</h3>
            </a>-->
        </div>
    </div>

    <!-- Rodapé com informações de contato -->
    <footer class="footer">
        <div class="footer-content">
            <div class="contact-info">
                <h3>Contatos</h3>
                <div class="contact-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Rua Acácia, 1317 - Centro, Santa Izabel do Oeste - PR</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-phone"></i>
                    <span>(46) 3542-1360</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <span>prefsio@gmail.com</span>
                </div>
            </div>

            <div class="contact-info">
                <h3>Horário de Atendimento</h3>
                <div class="contact-item">
                    <i class="fas fa-clock"></i>
                    <span>Segunda a Sexta: 07:30 às 11:30 / 13:00 às 17:00</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Sábados, Domingos e Feriados: Fechado</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-info-circle"></i>
                    <span>CNPJ: 76.205.715/0001-42</span>
                </div>
            </div>

            <div class="copyright">
                &copy; 2025. Todos os direitos reservados.
            </div>
        </div>
    </footer>

    <!-- Script para exibir nome do usuário quando estiver logado -->
<script>
    // Usando as variáveis PHP diretamente no JavaScript
    const isLoggedIn = <?php echo $usuario_logado ? 'true' : 'false'; ?>;
    const userName = "<?php echo addslashes($nome_usuario); ?>";
    
    document.addEventListener("DOMContentLoaded", function() {
        const actionButtons = document.querySelector('.action-buttons');
        const userWelcomeArea = document.querySelector('.user-welcome-area');
        
        if (isLoggedIn) {
            // Mantém o botão "Área Restrita" visível sempre
            document.querySelectorAll('.admin-login').forEach(btn => {
                btn.style.display = 'flex';
            });
            
            // Oculta os botões de ação principais quando usuário está logado
            if (actionButtons) {
                actionButtons.style.display = 'none';
            }
            
            // Mostra a área de boas-vindas do usuário
            if (userWelcomeArea) {
                userWelcomeArea.style.display = 'flex';
            }
            
            // Garante que a área de usuário logado do cabeçalho esteja oculta
            const userLoggedElement = document.querySelector('.user-logged-in');
            userLoggedElement.style.display = 'none';
            
            // Define o nome do usuário na área de boas-vindas
            document.getElementById('user-display-name').textContent = userName;
            
            // Aqui você pode adicionar lógica para carregar a foto do usuário
            // Exemplo: se você tiver o caminho da foto na sessão PHP
            // const userPhoto = "<?php echo isset($_SESSION['user_foto']) ? addslashes($_SESSION['user_foto']) : './uploads/fotos_perfil/default_avatar.png'; ?>";
            // document.getElementById('user-avatar').src = userPhoto;
            
        } else {
            // Garante que o botão de área restrita esteja visível
            document.querySelectorAll('.admin-login').forEach(btn => {
                btn.style.display = 'flex';
            });
            
            // Garante que os botões de ação estejam visíveis
            if (actionButtons) {
                actionButtons.style.display = 'flex';
            }
            
            // Garante que a área de boas-vindas esteja oculta
            if (userWelcomeArea) {
                userWelcomeArea.style.display = 'none';
            }
            
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