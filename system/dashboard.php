<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gerenciamento da Prefeitura</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --text-color: #333;
            --light-color: #ecf0f1;
            --sidebar-width: 250px;
            --header-height: 60px;
        }

        body {
            display: flex;
            min-height: 100vh;
            background-color: #f5f7fa;
        }

        /* Sidebar styles */
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--primary-color);
            color: white;
            position: fixed;
            height: 100%;
            left: 0;
            top: 0;
            z-index: 100;
            transition: all 0.3s;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            height: var(--header-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar-header h3 {
            font-size: 1.2rem;
            color: white;
        }

        .sidebar.collapsed {
            width: 70px;
        }

        .sidebar.collapsed .menu-text,
        .sidebar.collapsed .sidebar-header h3 {
            display: none;
        }

        .sidebar.collapsed .submenu {
            position: absolute;
            left: 70px;
            top: 0;
            width: 200px;
            background-color: var(--primary-color);
            display: none;
        }

        .sidebar.collapsed .menu-item:hover .submenu {
            display: block;
        }

        .toggle-btn {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
        }

        /* Menu styles */
        .menu {
            list-style: none;
            padding: 10px 0;
        }

        .menu-item {
            position: relative;
        }

        .menu-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }

        .menu-link:hover, 
        .menu-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--secondary-color);
        }

        .menu-icon {
            margin-right: 10px;
            font-size: 18px;
            width: 25px;
            text-align: center;
        }

        .arrow {
            margin-left: auto;
            transition: transform 0.3s;
        }

        .menu-item.open .arrow {
            transform: rotate(90deg);
        }

        .submenu {
            list-style: none;
            background-color: rgba(0, 0, 0, 0.1);
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .menu-item.open .submenu {
            max-height: 1000px;
        }

        .submenu-link {
            display: block;
            padding: 10px 10px 10px 55px;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }

        .submenu-link:hover {
            background-color: rgba(255, 255, 255, 0.05);
            color: var(--secondary-color);
        }

        /* Main content styles */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: margin-left 0.3s;
        }

        .main-content.expanded {
            margin-left: 70px;
        }

        .header {
            height: var(--header-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .user-info img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .page-title {
            margin-bottom: 20px;
            font-size: 1.5rem;
            color: var(--text-color);
        }

        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .card-title {
            font-size: 18px;
            margin-bottom: 15px;
            color: var(--primary-color);
        }

        .card-content {
            color: var(--text-color);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Prefeitura</h3>
            <button class="toggle-btn">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        
        <ul class="menu">
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <span class="menu-icon"><i class="fas fa-tachometer-alt"></i></span>
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>
            
            <!-- Secretarias (em ordem alfabética) -->
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <span class="menu-icon"><i class="fas fa-leaf"></i></span>
                    <span class="menu-text">Agricultura</span>
                    <span class="arrow"><i class="fas fa-chevron-right"></i></span>
                </a>
                <ul class="submenu">
                    <li><a href="#" class="submenu-link">Projetos</a></li>
                    <li><a href="#" class="submenu-link">Programas</a></li>
                    <li><a href="#" class="submenu-link">Relatórios</a></li>
                </ul>
            </li>
            
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <span class="menu-icon"><i class="fas fa-hands-helping"></i></span>
                    <span class="menu-text">Assistência Social</span>
                    <span class="arrow"><i class="fas fa-chevron-right"></i></span>
                </a>
                <ul class="submenu">
                    <li><a href="#" class="submenu-link">Atendimentos</a></li>
                    <li><a href="#" class="submenu-link">Benefícios</a></li>
                    <li><a href="#" class="submenu-link">Relatórios</a></li>
                </ul>
            </li>
            
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <span class="menu-icon"><i class="fas fa-palette"></i></span>
                    <span class="menu-text">Cultura e Turismo</span>
                    <span class="arrow"><i class="fas fa-chevron-right"></i></span>
                </a>
                <ul class="submenu">
                    <li><a href="#" class="submenu-link">Eventos</a></li>
                    <li><a href="#" class="submenu-link">Pontos Turísticos</a></li>
                    <li><a href="#" class="submenu-link">Relatórios</a></li>
                </ul>
            </li>
            
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <span class="menu-icon"><i class="fas fa-graduation-cap"></i></span>
                    <span class="menu-text">Educação</span>
                    <span class="arrow"><i class="fas fa-chevron-right"></i></span>
                </a>
                <ul class="submenu">
                    <li><a href="#" class="submenu-link">Escolas</a></li>
                    <li><a href="#" class="submenu-link">Professores</a></li>
                    <li><a href="#" class="submenu-link">Alunos</a></li>
                    <li><a href="#" class="submenu-link">Relatórios</a></li>
                </ul>
            </li>
            
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <span class="menu-icon"><i class="fas fa-running"></i></span>
                    <span class="menu-text">Esporte</span>
                    <span class="arrow"><i class="fas fa-chevron-right"></i></span>
                </a>
                <ul class="submenu">
                    <li><a href="#" class="submenu-link">Eventos</a></li>
                    <li><a href="#" class="submenu-link">Equipamentos</a></li>
                    <li><a href="#" class="submenu-link">Relatórios</a></li>
                </ul>
            </li>
            
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <span class="menu-icon"><i class="fas fa-money-bill-wave"></i></span>
                    <span class="menu-text">Fazenda</span>
                    <span class="arrow"><i class="fas fa-chevron-right"></i></span>
                </a>
                <ul class="submenu">
                    <li><a href="#" class="submenu-link">Orçamento</a></li>
                    <li><a href="#" class="submenu-link">Receitas</a></li>
                    <li><a href="#" class="submenu-link">Despesas</a></li>
                    <li><a href="#" class="submenu-link">Relatórios</a></li>
                </ul>
            </li>
            
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <span class="menu-icon"><i class="fas fa-search"></i></span>
                    <span class="menu-text">Fiscalização</span>
                    <span class="arrow"><i class="fas fa-chevron-right"></i></span>
                </a>
                <ul class="submenu">
                    <li><a href="#" class="submenu-link">Denúncias</a></li>
                    <li><a href="#" class="submenu-link">Fiscalizações</a></li>
                    <li><a href="#" class="submenu-link">Relatórios</a></li>
                </ul>
            </li>
            
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <span class="menu-icon"><i class="fas fa-tree"></i></span>
                    <span class="menu-text">Meio Ambiente</span>
                    <span class="arrow"><i class="fas fa-chevron-right"></i></span>
                </a>
                <ul class="submenu">
                    <li><a href="#" class="submenu-link">Licenciamentos</a></li>
                    <li><a href="#" class="submenu-link">Projetos</a></li>
                    <li><a href="#" class="submenu-link">Relatórios</a></li>
                </ul>
            </li>
            
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <span class="menu-icon"><i class="fas fa-hard-hat"></i></span>
                    <span class="menu-text">Obras</span>
                    <span class="arrow"><i class="fas fa-chevron-right"></i></span>
                </a>
                <ul class="submenu">
                    <li><a href="#" class="submenu-link">Projetos</a></li>
                    <li><a href="#" class="submenu-link">Licitações</a></li>
                    <li><a href="#" class="submenu-link">Andamento</a></li>
                    <li><a href="#" class="submenu-link">Relatórios</a></li>
                </ul>
            </li>
            
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <span class="menu-icon"><i class="fas fa-truck"></i></span>
                    <span class="menu-text">Rodoviário</span>
                    <span class="arrow"><i class="fas fa-chevron-right"></i></span>
                </a>
                <ul class="submenu">
                    <li><a href="#" class="submenu-link">Frota</a></li>
                    <li><a href="#" class="submenu-link">Manutenção</a></li>
                    <li><a href="#" class="submenu-link">Relatórios</a></li>
                </ul>
            </li>
            
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <span class="menu-icon"><i class="fas fa-city"></i></span>
                    <span class="menu-text">Serviços Urbanos</span>
                    <span class="arrow"><i class="fas fa-chevron-right"></i></span>
                </a>
                <ul class="submenu">
                    <li><a href="#" class="submenu-link">Solicitações</a></li>
                    <li><a href="#" class="submenu-link">Manutenções</a></li>
                    <li><a href="#" class="submenu-link">Relatórios</a></li>
                </ul>
            </li>
            
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <span class="menu-icon"><i class="fas fa-cog"></i></span>
                    <span class="menu-text">Configurações</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h2>Sistema de Gerenciamento da Prefeitura</h2>
            <div class="user-info">
                <img src="https://via.placeholder.com/35" alt="Usuário">
                <span>Administrador</span>
            </div>
        </div>
        
        <h1 class="page-title">Dashboard</h1>
        
        <div class="dashboard-cards">
            <div class="card">
                <h3 class="card-title">Resumo de Orçamento</h3>
                <div class="card-content">
                    <p>Visualize as informações do orçamento municipal.</p>
                </div>
            </div>
            
            <div class="card">
                <h3 class="card-title">Projetos em Andamento</h3>
                <div class="card-content">
                    <p>Acompanhe o status de todos os projetos municipais.</p>
                </div>
            </div>
            
            <div class="card">
                <h3 class="card-title">Solicitações Recentes</h3>
                <div class="card-content">
                    <p>Veja as solicitações recentes de serviços urbanos.</p>
                </div>
            </div>
            
            <div class="card">
                <h3 class="card-title">Eventos Próximos</h3>
                <div class="card-content">
                    <p>Fique por dentro dos próximos eventos municipais.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle sidebar
            const toggleBtn = document.querySelector('.toggle-btn');
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            
            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
            });
            
            // Toggle submenu
            const menuItems = document.querySelectorAll('.menu-item');
            
            menuItems.forEach(function(item) {
                const menuLink = item.querySelector('.menu-link');
                
                if (menuLink.querySelector('.arrow')) {
                    menuLink.addEventListener('click', function(e) {
                        e.preventDefault();
                        item.classList.toggle('open');
                        
                        // Close other open menus
                        menuItems.forEach(function(otherItem) {
                            if (otherItem !== item && otherItem.classList.contains('open')) {
                                otherItem.classList.remove('open');
                            }
                        });
                    });
                }
            });
        });
    </script>
</body>
</html>