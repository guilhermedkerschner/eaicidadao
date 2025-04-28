<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setor de Cultura e Turismo - Eai Cidadão!</title>
    <!-- Font Awesome para ícones -->
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
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }

        .header {
            width: 100%;
            max-width: 1200px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            margin-bottom: 20px;
        }

        .header-left {
            display: flex;
            align-items: center;
        }

        .municipality-logo {
            width: 80px;
            height: 80px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 20px;
        }

        .municipality-logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .title-container h1 {
            color: #0d47a1;
            font-weight: 600;
            font-size: 1.8rem;
        }

        .municipality-name {
            color: #000000;
            font-size: 1rem;
            text-transform: uppercase;
            font-weight: 700;
        }

        /* Área de login */
        .login-area {
            display: flex;
            gap: 10px;
        }

        .login-button {
            background-color: #fff;
            padding: 10px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }

        .login-button i {
            margin-right: 8px;
        }

        .user-login {
            color: #2e7d32;
        }

        .user-login:hover {
            background-color: #2e7d32;
            color: #fff;
        }

        .admin-login {
            color: #0d47a1;
        }

        .admin-login:hover {
            background-color: #0d47a1;
            color: #fff;
        }

        /* Estilo para o botão do usuário logado */
        .user-logged-in {
            display: none; /* Oculto por padrão, mostrado quando logado */
            position: relative;
        }

        .user-button {
            background-color: #e8f5e9;
            color: #2e7d32;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 500;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .user-button:hover {
            background-color: #c8e6c9;
        }

        .user-button i {
            margin-right: 8px;
        }

        /* Menu dropdown */
        .user-dropdown {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            min-width: 180px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 100;
        }

        .user-logged-in:hover .user-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: #333;
            text-decoration: none;
            transition: background-color 0.2s ease;
        }

        .dropdown-item:first-child {
            border-radius: 8px 8px 0 0;
        }

        .dropdown-item:last-child {
            border-radius: 0 0 8px 8px;
        }

        .dropdown-item:hover {
            background-color: #f5f5f5;
        }

        .dropdown-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
            font-size: 1rem;
        }

        /* Botão voltar */
        .back-button {
            background-color: #0d47a1;
            color: #fff;
            padding: 8px 16px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            margin-left: 10px;
        }

        .back-button i {
            margin-right: 8px;
        }

        .back-button:hover {
            background-color: #083378;
            transform: translateY(-2px);
        }

        .header-buttons {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            padding: 30px;
            width: 100%;
            max-width: 1200px;
            z-index: 1;
            margin-bottom: 20px;
        }

        .section-title {
            color: #0d47a1;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e0e0;
            display: flex;
            align-items: center;
            font-size: 1.5rem;
        }

        .section-title i {
            margin-right: 10px;
            color: #9c27b0;
            font-size: 1.5rem;
        }

        .intro-text {
            margin-bottom: 30px;
            line-height: 1.6;
            color: #333;
        }

        .service-buttons {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }

        .service-button {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 25px 15px;
            text-align: center;
            transition: all 0.3s;
            text-decoration: none;
            color: #333;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
        }

        .service-button:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }

        .service-button i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: #9c27b0;
            transition: all 0.3s;
        }

        .service-button:hover i {
            transform: scale(1.2);
        }

        .service-button h3 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .service-button p {
            font-size: 0.9rem;
            color: #666;
            margin: 0;
        }

        .contact-section {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 30px;
            margin-top: 20px;
        }

        .contact-title {
            color: #0d47a1;
            margin-bottom: 20px;
            font-size: 1.3rem;
            font-weight: 600;
            display: flex;
            align-items: center;
        }

        .contact-title i {
            margin-right: 10px;
            color: #9c27b0;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .contact-info {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            height: 100%;
        }

        .contact-item {
            display: flex;
            align-items: flex-start;
        }

        .contact-item i {
            color: #9c27b0;
            font-size: 1.2rem;
            margin-right: 15px;
            margin-top: 2px;
            width: 20px;
            text-align: center;
        }

        .contact-text {
            flex: 1;
        }

        .contact-text h4 {
            font-size: 1.1rem;
            margin-bottom: 10px;
            color: #333;
        }

        .contact-text p {
            color: #666;
            line-height: 1.5;
            margin: 0;
        }

        .opening-hours {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .opening-hours h4 {
            font-size: 1.1rem;
            margin-bottom: 15px;
            color: #333;
            display: flex;
            align-items: center;
        }

        .opening-hours h4 i {
            margin-right: 10px;
            color: #9c27b0;
        }

        .hours-table {
            width: 100%;
            border-collapse: collapse;
        }

        .hours-table th, .hours-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        .hours-table th {
            color: #0d47a1;
            font-weight: 600;
            width: 40%;
        }

        .hours-table tr:last-child td,
        .hours-table tr:last-child th {
            border-bottom: none;
        }

        footer {
            background-color: rgba(255, 255, 255, 0.95);
            width: 100%;
            max-width: 1200px;
            padding: 15px;
            text-align: center;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            font-size: 0.9rem;
            color: #555;
            margin-top: auto;
        }

        /* Media Queries */
        @media (max-width: 992px) {
            .service-buttons {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .contact-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
                padding: 15px;
            }
            
            .header-left {
                margin-bottom: 15px;
                align-items: center;
                justify-content: center;
            }
            
            .header-buttons {
                flex-direction: column;
                width: 100%;
            }
            
            .login-area {
                flex-direction: column;
                width: 100%;
                margin-bottom: 10px;
            }
            
            .login-button, .back-button {
                width: 100%;
                justify-content: center;
                margin-left: 0;
            }
        }

        @media (max-width: 576px) {
            .service-buttons {
                grid-template-columns: 1fr;
            }
            
            .header-left {
                flex-direction: column;
            }
            
            .municipality-logo {
                margin-right: 0;
                margin-bottom: 10px;
            }
            
            .title-container {
                text-align: center;
            }
        }

        @media (max-width: 768px) {
            a[href="status_servicos.php"] {
                flex-direction: row !important;
                align-items: center !important;
                padding: 15px 20px !important;
            }
            
            a[href="status_servicos.php"] i {
                font-size: 1.5rem !important;
                margin-right: 15px !important;
            }
            
            a[href="status_servicos.php"] > div:nth-child(2) {
                height: 30px !important; /* Barra vertical menor em telas médias */
                margin-right: 15px !important;
            }
            
            a[href="status_servicos.php"] h3 {
                font-size: 1.1rem !important;
            }
            
            a[href="status_servicos.php"] p {
                font-size: 0.9rem !important;
            }
        }

        @media (max-width: 480px) {
            a[href="status_servicos.php"] {
                padding: 12px 15px !important;
            }
            
            a[href="status_servicos.php"] i {
                font-size: 1.2rem !important;
                margin-right: 10px !important;
            }
            
            a[href="status_servicos.php"] > div:nth-child(2) {
                height: 25px !important; /* Barra vertical ainda menor em telas pequenas */
                margin-right: 10px !important;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="header-left">
            <div class="municipality-logo">
                <!-- Substitua pelo caminho da sua logo -->
                <img src="../../img/logo_municipio.png" alt="Logo do Município">
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
                <a href="login_cidadao.php" class="login-button user-login">
                    <i class="fas fa-user"></i>
                    Área do Cidadão
                </a>
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
                        <a href="meu_perfil.php" class="dropdown-item">
                            <i class="fas fa-id-card"></i>
                            Meu Perfil
                        </a>
                        <a href="logout.php" class="dropdown-item">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </a>
                    </div>
                </div>
            </div>
            <!-- Botão Voltar para Página Inicial -->
            <a href="index.php" class="back-button">
                <i class="fas fa-home"></i> 
                Página Inicial
            </a>
        </div>
    </div>

    <div class="container">
        <h2 class="section-title"><i class="fas fa-theater-masks"></i> Setor de Cultura e Turismo</h2>

        <p class="intro-text">
            O Setor de Cultura e Turismo do Município de Santa Izabel do Oeste é responsável por promover, difundir e valorizar as manifestações culturais e o potencial turístico da nossa cidade. Trabalhamos para preservar o patrimônio histórico e cultural, incentivar a produção artística local e desenvolver atrativos turísticos que valorizem nossas belezas naturais e tradições. Selecione abaixo o serviço que você deseja acessar.
        </p>

        <div class="service-buttons">
            <a href="agenda_cultural.php" class="service-button">
                <i class="fas fa-calendar-alt"></i>
                <h3>Agenda Cultural</h3>
                <p>Calendário de eventos culturais do município</p>
            </a>

            <a href="pontos_turisticos.php" class="service-button">
                <i class="fas fa-mountain"></i>
                <h3>Pontos Turísticos</h3>
                <p>Conheça os atrativos turísticos da nossa cidade</p>
            </a>

            <a href="patrimonio_cultural.php" class="service-button">
                <i class="fas fa-landmark"></i>
                <h3>Patrimônio Cultural</h3>
                <p>Informações sobre o patrimônio histórico e cultural</p>
            </a>

            <a href="biblioteca.php" class="service-button">
                <i class="fas fa-book"></i>
                <h3>Biblioteca Municipal</h3>
                <p>Horários e serviços da Biblioteca Municipal</p>
            </a>

            <a href="form_incentivo_cultural.php" class="service-button">
                <i class="fas fa-paint-brush"></i>
                <h3>Incentivo Cultural</h3>
                <p>Programas de apoio e fomento à cultura local</p>
            </a>

            <a href="guia_turistico.php" class="service-button">
                <i class="fas fa-map-marked-alt"></i>
                <h3>Guia Turístico</h3>
                <p>Roteiros e informações para visitantes</p>
            </a>

            <a href="artesanato.php" class="service-button">
                <i class="fas fa-hands"></i>
                <h3>Artesanato</h3>
                <p>Conheça o artesanato local e pontos de venda</p>
            </a>

            <a href="gastronomia.php" class="service-button">
                <i class="fas fa-utensils"></i>
                <h3>Gastronomia</h3>
                <p>Culinária típica e estabelecimentos gastronômicos</p>
            </a>

            <a href="eventos.php" class="service-button">
                <i class="fas fa-music"></i>
                <h3>Cadastro de Eventos</h3>
                <p>Solicitar autorização para realização de eventos</p>
            </a>
        </div>

        <a href="status_servicos.php" class="service-button" style="width: 100%; max-width: 600px; margin: 0 auto 30px auto; background-color: #0d47a1; color: white; display: flex; flex-direction: row; align-items: center; justify-content: flex-start; padding: 20px 25px; text-align: left;">
            <i class="fas fa-tasks" style="color: white; font-size: 2rem; margin-right: 20px; margin-bottom: 0;"></i>
            <div style="width: 1px; height: 40px; background-color: rgba(255, 255, 255, 0.5); margin-right: 20px;"></div>
            <div>
                <h3 style="color: white; margin-bottom: 5px; font-size: 1.3rem;">Consultar Status de Solicitações</h3>
                <p style="color: #e0e0e0; font-size: 1rem;">Acompanhe o andamento das suas solicitações</p>
            </div>
        </a>

        <div class="contact-section">
            <h3 class="contact-title"><i class="fas fa-address-card"></i> Contato do Setor de Cultura e Turismo</h3>
            
            <div class="contact-grid">
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div class="contact-text">
                            <h4>Endereço</h4>
                            <p>Rua das Flores, 340 - Centro<br>Santa Izabel do Oeste - PR<br>CEP: 85650-000</p>
                        </div>
                    </div>
                </div>
                
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <div class="contact-text">
                            <h4>Telefones</h4>
                            <p>(46) 3552-1275 (Secretaria)<br>(46) 3552-1276 (Biblioteca Municipal)</p>
                        </div>
                    </div>
                </div>
                
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <div class="contact-text">
                            <h4>E-mail</h4>
                            <p>cultura@santaizabel.pr.gov.br<br>turismo@santaizabel.pr.gov.br</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="opening-hours">
                <h4><i class="fas fa-clock"></i> Horário de Atendimento</h4>
                <table class="hours-table">
                    <tr>
                        <th>Local</th>
                        <th>Horário</th>
                    </tr>
                    <tr>
                        <td>Secretaria</td>
                        <td>Segunda a Sexta: 08:00 às 11:30 | 13:00 às 17:00</td>
                    </tr>
                    <tr>
                        <td>Biblioteca Municipal</td>
                        <td>Segunda a Sexta: 08:00 às 18:00</td>
                    </tr>
                    <tr>
                        <td>Centro Cultural</td>
                        <td>Segunda a Sexta: 09:00 às 21:00</td>
                    </tr>
                    <tr>
                        <td>Casa do Artesão</td>
                        <td>Terça a Domingo: 09:00 às 17:00</td>
                    </tr>
                    <tr>
                        <td>Ponto de Informações Turísticas</td>
                        <td>Todos os dias: 08:00 às 18:00</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <footer>
        &copy; 2025 Prefeitura Municipal de Santa Izabel do Oeste. Todos os direitos reservados.
    </footer>

    <!-- Script para exibir nome do usuário quando estiver logado (exemplo) -->
    <script>
        // Exemplo de verificação de login (substitua por sua lógica real)
        // Quando o usuário estiver logado, oculte os botões de login e mostre a mensagem de boas-vindas
        
        // Exemplo: se usuário estiver logado
        const isLoggedIn = false; // Mude para true para testar o estado de logado
        const userName = "João Silva"; // Nome do usuário logado
        
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
            }
        });
    </script>
</body>

</html>