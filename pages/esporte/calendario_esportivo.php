<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendário Esportivo - Eai Cidadão!</title>
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

        /* Botões de navegação */
        .nav-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .nav-button {
            background-color: #0d47a1;
            color: #fff;
            padding: 8px 16px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }

        .nav-button i {
            margin-right: 8px;
        }

        .nav-button:hover {
            background-color: #083378;
            transform: translateY(-2px);
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
            color: #2e7d32;
            font-size: 1.5rem;
        }

        .main-title {
            color: #2e7d32;
            font-size: 2rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #555;
            font-size: 1.2rem;
            text-align: center;
            margin-bottom: 30px;
        }

        .calendar-container {
            margin-bottom: 30px;
        }

        .sport-title {
            background-color: #2e7d32;
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .sport-title i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .date-location {
            background-color: #f5f5f5;
            border-left: 4px solid #2e7d32;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 0 8px 8px 0;
        }

        .date-location-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }

        .date-location-title i {
            margin-right: 10px;
            color: #2e7d32;
        }

        .game {
            background-color: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
        }

        .game-time {
            background-color: #0d47a1;
            color: white;
            font-weight: 600;
            padding: 8px 12px;
            border-radius: 50px;
            min-width: 100px;
            text-align: center;
            margin-right: 15px;
        }

        .game-teams {
            flex: 1;
            font-weight: 500;
            color: #333;
        }

        .vs {
            margin: 0 10px;
            color: #2e7d32;
            font-weight: 700;
        }

        .filter-container {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-button {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            padding: 8px 15px;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.9rem;
        }

        .filter-button:hover, .filter-button.active {
            background-color: #ff5722;
            color: white;
            border-color: #ff5722;
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

        .all-tournaments {
            border-top: 1px solid #eee;
            margin-top: 30px;
            padding-top: 30px;
        }

        .all-tournaments h3 {
            font-size: 1.2rem;
            color: #333;
            margin-bottom: 15px;
        }

        .tournament-list {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }

        .tournament-card {
            background-color: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            text-decoration: none;
            color: #333;
        }

        .tournament-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        .tournament-card h4 {
            font-size: 1.1rem;
            margin-bottom: 10px;
            color: #0d47a1;
        }

        .tournament-card p {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 10px;
        }

        .tournament-status {
            font-size: 0.8rem;
            padding: 3px 8px;
            border-radius: 50px;
            display: inline-block;
        }

        .status-active {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .status-upcoming {
            background-color: #e3f2fd;
            color: #0d47a1;
        }

        .status-completed {
            background-color: #f5f5f5;
            color: #616161;
        }

        /* Media Queries */
        @media (max-width: 992px) {
            .tournament-list {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .game {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .game-time {
                margin-bottom: 10px;
                align-self: flex-start;
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
            }
            
            .login-area {
                flex-direction: column;
                width: 100%;
            }
            
            .login-button {
                width: 100%;
                justify-content: center;
            }
            
            .nav-buttons {
                flex-direction: column;
                width: 100%;
            }
            
            .nav-button {
                width: 100%;
                justify-content: center;
            }
            
            .game-teams {
                font-size: 0.9rem;
            }
            
            .main-title {
                font-size: 1.6rem;
            }
            
            .subtitle {
                font-size: 1rem;
            }
        }

        @media (max-width: 576px) {
            .tournament-list {
                grid-template-columns: 1fr;
            }
            
            .filter-container {
                justify-content: center;
            }
            
            .date-location-title {
                font-size: 1rem;
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
        </div>
    </div>

    <div class="nav-buttons">
        <a href="index.php" class="nav-button">
            <i class="fas fa-home"></i> 
            Página Inicial
        </a>
        <a href="esportes.php" class="nav-button">
            <i class="fas fa-running"></i> 
            Setor de Esportes
        </a>
    </div>

    <div class="container">
        <h2 class="section-title"><i class="fas fa-calendar-alt"></i> Calendário Esportivo</h2>
        
        <h1 class="main-title">Campeonato Municipal de Futebol Suíço 2025</h1>
        <h3 class="subtitle">Secretaria Municipal de Esportes</h3>
        
        <div class="filter-container">
            <button class="filter-button active">Todos</button>
            <button class="filter-button">Futebol</button>
            <button class="filter-button">Futsal</button>
            <button class="filter-button">Vôlei</button>
            <button class="filter-button">Basquete</button>
            <button class="filter-button">Atletismo</button>
        </div>
        
        <div class="calendar-container">
            <div class="sport-title">
                <i class="fas fa-futbol"></i> Futebol Suíço
            </div>
            
            <div class="date-location">
                <div class="date-location-title">
                    <i class="fas fa-calendar"></i> 09/04/2025 - Quarta-Feira - Campo da Linha Anunciação
                </div>
                
                <div class="game">
                    <div class="game-time">19:15</div>
                    <div class="game-teams">RACING F.I. SÃO BRAZ <span class="vs">X</span> ALTO DA COLINA</div>
                </div>
                
                <div class="game">
                    <div class="game-time">20:15</div>
                    <div class="game-teams">ANUNCIAÇÃO B <span class="vs">X</span> INDEPENDENTE ANUNCIAÇÃO</div>
                </div>
            </div>
            
            <div class="date-location">
                <div class="date-location-title">
                    <i class="fas fa-calendar"></i> 09/04/2025 - Quarta-Feira - Campo do Piccoli/Denega
                </div>
                
                <div class="game">
                    <div class="game-time">19:15</div>
                    <div class="game-teams">AMIZADE F.C <span class="vs">X</span> GALÁTICOS/CHÁCARA LINHA PERÓBA</div>
                </div>
                
                <div class="game">
                    <div class="game-time">20:15</div>
                    <div class="game-teams">PICCOLI DENEGA PRÉ-MOLDADOS/MARCÃO BEBIDAS <span class="vs">X</span> NOVA ESTRELA</div>
                </div>
            </div>
            
            <div class="date-location">
                <div class="date-location-title">
                    <i class="fas fa-calendar"></i> 11/04/2025 - Sexta-Feira - Campo da L. São Judas Tadeu
                </div>
                
                <div class="game">
                    <div class="game-time">19:15</div>
                    <div class="game-teams">CRK <span class="vs">X</span> ASSOCIAÇÃO ATLÉTICA UNIÃO SÃO JUDAS</div>
                </div>
                
                <div class="game">
                    <div class="game-time">20:15</div>
                    <div class="game-teams">ESCOLA DE FUTEBOL SUB 17 <span class="vs">X</span> AMIGOS DO GOLE</div>
                </div>
            </div>
            
            <div class="date-location">
                <div class="date-location-title">
                    <i class="fas fa-calendar"></i> 11/04/2025 - Sexta-Feira - Campo São Pedro
                </div>
                
                <div class="game">
                    <div class="game-time">19:15</div>
                    <div class="game-teams">FRIGOBEL/LACTOBEL <span class="vs">X</span> VETERANOS WHISKY JEANS</div>
                </div>
                
                <div class="game">
                    <div class="game-time">20:15</div>
                    <div class="game-teams">ASSOCIAÇÃO AMIGOS SÃO PEDRO <span class="vs">X</span> UNIÃO AMIGOS F.C</div>
                </div>
            </div>
        </div>
        
        <div class="all-tournaments">
            <h3>Outros Campeonatos e Eventos Esportivos</h3>
            <div class="tournament-list">
                <a href="#" class="tournament-card">
                    <h4>Copa Municipal de Futsal 2025</h4>
                    <p>Início: 15/05/2025</p>
                    <span class="tournament-status status-upcoming">Inscrições Abertas</span>
                </a>
                
                <a href="#" class="tournament-card">
                    <h4>Campeonato de Vôlei Misto</h4>
                    <p>Início: 22/03/2025</p>
                    <span class="tournament-status status-active">Em Andamento</span>
                </a>
                
                <a href="#" class="tournament-card">
                    <h4>Torneio de Basquete Interescolar</h4>
                    <p>Início: 12/06/2025</p>
                    <span class="tournament-status status-upcoming">Em Breve</span>
                </a>
                
                <a href="#" class="tournament-card">
                    <h4>Corrida de Rua - 5km e 10km</h4>
                    <p>Data: 25/05/2025</p>
                    <span class="tournament-status status-upcoming">Inscrições Abertas</span>
                </a>
                
                <a href="#" class="tournament-card">
                    <h4>Copa Master de Futebol (40+)</h4>
                    <p>Início: 01/03/2025</p>
                    <span class="tournament-status status-active">Em Andamento</span>
                </a>
                
                <a href="#" class="tournament-card">
                    <h4>Torneio de Tênis de Mesa</h4>
                    <p>Data: 05/02/2025</p>
                    <span class="tournament-status status-completed">Concluído</span>
                </a>
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
            
            // Funcionalidade para os botões de filtro (apenas visual para demonstração)
            document.querySelectorAll('.filter-button').forEach(button => {
                button.addEventListener('click', function() {
                    document.querySelectorAll('.filter-button').forEach(btn => {
                        btn.classList.remove('active');
                    });
                    this.classList.add('active');
                });
            });
        });
    </script>
</body>

</html>