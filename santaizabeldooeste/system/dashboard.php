<?php
// Inicia a sessão
session_start();

if (!isset($_SESSION['usersystem_logado'])) {
    header("Location: ../acessdeniedrestrict.php"); 
    exit;
}

// Incluir arquivo de configuração com conexão ao banco de dados
require_once "../lib/config.php";

// Buscar informações do usuário logado
$usuario_id = $_SESSION['usersystem_id'];
$usuario_nome = $_SESSION['usersystem_nome'] ?? 'Usuário';
$usuario_departamento = null;
$usuario_nivel_id = null;
$is_admin = false;

try {
    $stmt = $conn->prepare("SELECT usuario_nome, usuario_departamento, usuario_nivel_id FROM tb_usuarios_sistema WHERE usuario_id = :id");
    $stmt->bindParam(':id', $usuario_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        $usuario_nome = $usuario['usuario_nome'];
        $usuario_departamento = strtoupper($usuario['usuario_departamento']);
        $usuario_nivel_id = $usuario['usuario_nivel_id'];
        
        // Verificar se é administrador (assumindo que nivel_id = 1 é admin)
        $is_admin = ($usuario_nivel_id == 1);
    }
} catch (PDOException $e) {
    error_log("Erro ao buscar dados do usuário: " . $e->getMessage());
}

// Definir menus baseados no departamento
$menus_departamentos = [
    'AGRICULTURA' => [
        'icon' => 'fas fa-leaf',
        'name' => 'Agricultura',
        'color' => '#2e7d32',
        'submenu' => [
            'Projetos' => '#',
            'Programas' => '#',
            'Relatórios' => '#'
        ]
    ],
    'ASSISTENCIA_SOCIAL' => [
        'icon' => 'fas fa-hands-helping',
        'name' => 'Assistência Social',
        'color' => '#e91e63',
        'submenu' => [
            'Atendimentos' => '#',
            'Benefícios' => '#',
            'Programas Habitacionais' => 'habitacao_lista.php',
            'Relatórios' => '#'
        ]
    ],
    'CULTURA_E_TURISMO' => [
        'icon' => 'fas fa-palette',
        'name' => 'Cultura e Turismo',
        'color' => '#ff5722',
        'submenu' => [
            'Eventos' => '#',
            'Pontos Turísticos' => '#',
            'Relatórios' => '#'
        ]
    ],
    'EDUCACAO' => [
        'icon' => 'fas fa-graduation-cap',
        'name' => 'Educação',
        'color' => '#9c27b0',
        'submenu' => [
            'Escolas' => '#',
            'Professores' => '#',
            'Alunos' => '#',
            'Relatórios' => '#'
        ]
    ],
    'ESPORTE' => [
        'icon' => 'fas fa-running',
        'name' => 'Esporte',
        'color' => '#4caf50',
        'submenu' => [
            'Eventos' => '#',
            'Equipamentos' => '#',
            'Relatórios' => '#'
        ]
    ],
    'FAZENDA' => [
        'icon' => 'fas fa-money-bill-wave',
        'name' => 'Fazenda',
        'color' => '#ff9800',
        'submenu' => [
            'Orçamento' => '#',
            'Receitas' => '#',
            'Despesas' => '#',
            'Relatórios' => '#'
        ]
    ],
    'FISCALIZACAO' => [
        'icon' => 'fas fa-search',
        'name' => 'Fiscalização',
        'color' => '#673ab7',
        'submenu' => [
            'Denúncias' => '#',
            'Fiscalizações' => '#',
            'Relatórios' => '#'
        ]
    ],
    'MEIO_AMBIENTE' => [
        'icon' => 'fas fa-tree',
        'name' => 'Meio Ambiente',
        'color' => '#009688',
        'submenu' => [
            'Licenciamentos' => '#',
            'Projetos' => '#',
            'Relatórios' => '#'
        ]
    ],
    'OBRAS' => [
        'icon' => 'fas fa-hard-hat',
        'name' => 'Obras',
        'color' => '#795548',
        'submenu' => [
            'Projetos' => '#',
            'Licitações' => '#',
            'Andamento' => '#',
            'Relatórios' => '#'
        ]
    ],
    'RODOVIARIO' => [
        'icon' => 'fas fa-truck',
        'name' => 'Rodoviário',
        'color' => '#607d8b',
        'submenu' => [
            'Frota' => '#',
            'Manutenção' => '#',
            'Relatórios' => '#'
        ]
    ],
    'SERVICOS_URBANOS' => [
        'icon' => 'fas fa-city',
        'name' => 'Serviços Urbanos',
        'color' => '#2196f3',
        'submenu' => [
            'Solicitações' => '#',
            'Manutenções' => '#',
            'Relatórios' => '#'
        ]
    ]
];

// Determinar menus disponíveis baseado no nível do usuário
$menus_disponiveis = [];
$titulo_sistema = '';
$cor_tema = '#3498db';

if ($is_admin) {
    // Administrador tem acesso a todos os departamentos
    $menus_disponiveis = $menus_departamentos;
    $titulo_sistema = 'Administração Geral';
    $cor_tema = '#e74c3c'; // Vermelho para admin
} else {
    // Usuário comum tem acesso apenas ao seu departamento
    if ($usuario_departamento && isset($menus_departamentos[$usuario_departamento])) {
        $menus_disponiveis[$usuario_departamento] = $menus_departamentos[$usuario_departamento];
        $titulo_sistema = $menus_departamentos[$usuario_departamento]['name'];
        $cor_tema = $menus_departamentos[$usuario_departamento]['color'];
    }
}
?>

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
            --secondary-color: <?php echo $cor_tema; ?>;
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
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            height: var(--header-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            background-color: var(--primary-color);
        }

        .sidebar-header h3 {
            font-size: 1.1rem;
            color: white;
            line-height: 1.2;
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
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2);
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

        /* Admin menu separator */
        .menu-separator {
            height: 1px;
            background-color: rgba(255, 255, 255, 0.1);
            margin: 10px 0;
        }

        .menu-category {
            padding: 10px 20px 5px;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
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
            border-radius: 8px;
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
            background-color: var(--secondary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
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
            border-left: 4px solid var(--secondary-color);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .card-title {
            font-size: 18px;
            margin-bottom: 15px;
            color: var(--primary-color);
            display: flex;
            align-items: center;
        }

        .card-title i {
            margin-right: 10px;
            color: var(--secondary-color);
        }

        .card-content {
            color: var(--text-color);
        }

        .admin-badge, .department-badge {
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .admin-badge {
            background-color: #e74c3c;
        }

        .department-badge {
            background-color: var(--secondary-color);
        }

        .access-denied {
            text-align: center;
            padding: 50px 20px;
        }

        .access-denied i {
            font-size: 4rem;
            color: #e74c3c;
            margin-bottom: 20px;
        }

        .access-denied h2 {
            color: #e74c3c;
            margin-bottom: 10px;
        }

        .logout-btn {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9rem;
            transition: background-color 0.3s;
        }

        .logout-btn:hover {
            background-color: #c0392b;
        }

        /* Admin dashboard specific styles */
        .admin-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, var(--secondary-color), rgba(231, 76, 60, 0.8));
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .stat-card h3 {
            font-size: 2rem;
            margin-bottom: 5px;
        }

        .stat-card p {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .mobile-toggle {
                display: block;
                background: none;
                border: none;
                font-size: 20px;
                cursor: pointer;
                color: var(--primary-color);
            }

            .header {
                flex-direction: column;
                gap: 10px;
                padding: 15px 20px;
                height: auto;
            }

            .header > div:first-child {
                display: flex;
                align-items: center;
                justify-content: space-between;
                width: 100%;
            }

            .user-details {
                align-items: flex-start;
            }

            .user-name {
                font-size: 0.9rem;
            }

            .admin-badge, .department-badge {
                font-size: 0.7rem;
                padding: 3px 6px;
            }
        }

        @media (max-width: 480px) {
            .header h2 {
                font-size: 1.2rem;
            }

            .user-info {
                gap: 8px;
            }

            .user-details {
                min-width: 0;
            }

            .user-name {
                font-size: 0.85rem;
                overflow: hidden;
                text-overflow: ellipsis;
            }
        }

        .mobile-toggle {
            display: none;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3><?php echo $titulo_sistema; ?></h3>
            <button class="toggle-btn">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        
        <ul class="menu">
            <li class="menu-item">
                <a href="#" class="menu-link active">
                    <span class="menu-icon"><i class="fas fa-tachometer-alt"></i></span>
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>
            
            <?php if ($is_admin): ?>
            <!-- Menu completo para administradores -->
            <div class="menu-separator"></div>
            <div class="menu-category">Administração</div>
            
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <span class="menu-icon"><i class="fas fa-users-cog"></i></span>
                    <span class="menu-text">Gerenciar Usuários</span>
                    <span class="arrow"><i class="fas fa-chevron-right"></i></span>
                </a>
                <ul class="submenu">
                    <li><a href="lista_usuarios.php" class="submenu-link">Lista de Usuários</a></li>
                    <li><a href="#" class="submenu-link">Adicionar Usuário</a></li>
                    <li><a href="#" class="submenu-link">Permissões</a></li>
                </ul>
            </li>
            
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <span class="menu-icon"><i class="fas fa-chart-pie"></i></span>
                    <span class="menu-text">Relatórios Gerais</span>
                    <span class="arrow"><i class="fas fa-chevron-right"></i></span>
                </a>
                <ul class="submenu">
                    <li><a href="#" class="submenu-link">Consolidado Geral</a></li>
                    <li><a href="#" class="submenu-link">Por Departamento</a></li>
                    <li><a href="#" class="submenu-link">Estatísticas</a></li>
                </ul>
            </li>
            
            <div class="menu-separator"></div>
            <div class="menu-category">Departamentos</div>
            
            <?php foreach ($menus_disponiveis as $dept => $config): ?>
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <span class="menu-icon"><i class="<?php echo $config['icon']; ?>"></i></span>
                    <span class="menu-text"><?php echo $config['name']; ?></span>
                    <span class="arrow"><i class="fas fa-chevron-right"></i></span>
                </a>
                <ul class="submenu">
                    <?php foreach ($config['submenu'] as $item => $link): ?>
                    <li><a href="<?php echo $link; ?>" class="submenu-link"><?php echo $item; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </li>
            <?php endforeach; ?>
            
            <?php else: ?>
            <!-- Menu específico do departamento para usuários normais -->
            <?php foreach ($menus_disponiveis as $dept => $config): ?>
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <span class="menu-icon"><i class="<?php echo $config['icon']; ?>"></i></span>
                    <span class="menu-text"><?php echo $config['name']; ?></span>
                    <span class="arrow"><i class="fas fa-chevron-right"></i></span>
                </a>
                <ul class="submenu">
                    <?php foreach ($config['submenu'] as $item => $link): ?>
                    <li><a href="<?php echo $link; ?>" class="submenu-link"><?php echo $item; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </li>
            <?php endforeach; ?>
            <?php endif; ?>
            
            <div class="menu-separator"></div>
            
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <span class="menu-icon"><i class="fas fa-user-cog"></i></span>
                    <span class="menu-text">Meu Perfil</span>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="../controller/logout_system.php" class="menu-link">
                    <span class="menu-icon"><i class="fas fa-sign-out-alt"></i></span>
                    <span class="menu-text">Sair</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <div>
                <button class="mobile-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h2>Sistema de Gerenciamento da Prefeitura</h2>
            </div>
            <div class="user-info">
                <div style="width: 35px; height: 35px; border-radius: 50%; background-color: var(--secondary-color); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                    <?php echo strtoupper(substr($usuario_nome, 0, 1)); ?>
                </div>
                <div class="user-details">
                    <div class="user-name"><?php echo htmlspecialchars($usuario_nome); ?></div>
                    <div class="user-role">
                        <?php if ($is_admin): ?>
                        <span class="admin-badge">
                            <i class="fas fa-crown"></i> Administrador
                        </span>
                        <?php else: ?>
                        <span class="department-badge">
                            <?php echo $usuario_departamento ?? 'Sem Departamento'; ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if (count($menus_disponiveis) > 0): ?>
        <h1 class="page-title">
            <?php if ($is_admin): ?>
            <i class="fas fa-crown" style="color: var(--secondary-color); margin-right: 10px;"></i>
            Dashboard Administrativo
            <?php else: ?>
            <i class="<?php echo current($menus_disponiveis)['icon']; ?>" style="color: var(--secondary-color); margin-right: 10px;"></i>
            Dashboard - <?php echo current($menus_disponiveis)['name']; ?>
            <?php endif; ?>
        </h1>
        
        <?php if ($is_admin): ?>
        <!-- Dashboard específico para administradores -->
        <div class="admin-stats">
            <div class="stat-card">
                <h3>11</h3>
                <p>Departamentos</p>
            </div>
            <div class="stat-card">
                <h3><?php echo count($menus_departamentos); ?></h3>
                <p>Setores Ativos</p>
            </div>
            <div class="stat-card">
                <h3>0</h3>
                <p>Usuários Online</p>
            </div>
            <div class="stat-card">
                <h3>0</h3>
                <p>Solicitações Pendentes</p>
            </div>
        </div>
        
        <div class="dashboard-cards">
            <div class="card">
                <h3 class="card-title">
                    <i class="fas fa-users-cog"></i>
                    Gerenciamento de Usuários
                </h3>
                <div class="card-content">
                    <p>Gerencie usuários, permissões e acessos ao sistema de todos os departamentos.</p>
                </div>
            </div>
            
            <div class="card">
                <h3 class="card-title">
                    <i class="fas fa-chart-line"></i>
                    Relatórios Consolidados
                </h3>
                <div class="card-content">
                    <p>Visualize relatórios consolidados de todos os departamentos da prefeitura.</p>
                </div>
            </div>
            
            <div class="card">
                <h3 class="card-title">
                    <i class="fas fa-cogs"></i>
                    Configurações do Sistema
                </h3>
                <div class="card-content">
                    <p>Configure parâmetros gerais do sistema e definições de cada departamento.</p>
                </div>
            </div>
            
            <div class="card">
                <h3 class="card-title">
                    <i class="fas fa-shield-alt"></i>
                    Auditoria e Logs
                </h3>
                <div class="card-content">
                    <p>Acompanhe logs de acesso e atividades de todos os usuários do sistema.</p>
                </div>
            </div>
        </div>
        
        <?php else: ?>
        <!-- Dashboard específico para usuários de departamento -->
        <div class="dashboard-cards">
            <div class="card">
                <h3 class="card-title">
                    <i class="fas fa-chart-line"></i>
                    Resumo do Setor
                </h3>
                <div class="card-content">
                    <p>Visualize as principais informações e estatísticas do setor de <?php echo current($menus_disponiveis)['name']; ?>.</p>
                </div>
            </div>
            
            <div class="card">
                <h3 class="card-title">
                    <i class="fas fa-tasks"></i>
                    Tarefas Pendentes
                </h3>
                <div class="card-content">
                    <p>Acompanhe as tarefas e solicitações pendentes do seu setor.</p>
                </div>
            </div>
            
            <div class="card">
                <h3 class="card-title">
                    <i class="fas fa-file-alt"></i>
                    Relatórios
                </h3>
                <div class="card-content">
                    <p>Acesse os relatórios específicos do setor de <?php echo current($menus_disponiveis)['name']; ?>.</p>
                </div>
            </div>
            
            <div class="card">
                <h3 class="card-title">
                    <i class="fas fa-calendar-alt"></i>
                    Atividades Recentes
                </h3>
                <div class="card-content">
                    <p>Veja as atividades mais recentes realizadas no seu setor.</p>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php else: ?>
        <div class="access-denied">
            <i class="fas fa-exclamation-triangle"></i>
            <h2>Acesso Restrito</h2>
            <p>Seu usuário não possui um departamento configurado ou o departamento não foi encontrado.</p>
            <p>Entre em contato com o administrador do sistema para resolver esta questão.</p>
            <div style="margin-top: 20px;">
                <a href="../controller/logout_system.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Fazer Logout
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle sidebar
            const toggleBtn = document.querySelector('.toggle-btn');
            const mobileToggle = document.querySelector('.mobile-toggle');
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            
            function toggleSidebar() {
                if (window.innerWidth <= 768) {
                    sidebar.classList.toggle('show');
                } else {
                    sidebar.classList.toggle('collapsed');
                    mainContent.classList.toggle('expanded');
                }
            }
            
            if (toggleBtn) {
                toggleBtn.addEventListener('click', toggleSidebar);
            }
            
            if (mobileToggle) {
                mobileToggle.addEventListener('click', toggleSidebar);
            }
            
            // Toggle submenu
            const menuItems = document.querySelectorAll('.menu-item');
            
            menuItems.forEach(function(item) {
                const menuLink = item.querySelector('.menu-link');
                
                if (menuLink && menuLink.querySelector('.arrow')) {
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
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 768) {
                    const isClickInsideSidebar = sidebar.contains(e.target);
                    const isToggleBtn = e.target.closest('.mobile-toggle') || e.target.closest('.toggle-btn');
                    
                    if (!isClickInsideSidebar && !isToggleBtn && sidebar.classList.contains('show')) {
                        sidebar.classList.remove('show');
                    }
                }
            });
            
            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    sidebar.classList.remove('show');
                }
            });
        });
    </script>
</body>
</html>