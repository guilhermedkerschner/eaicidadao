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
        
        // Verificar se é administrador
        $is_admin = ($usuario_nivel_id == 1);
    }
} catch (PDOException $e) {
    error_log("Erro ao buscar dados do usuário: " . $e->getMessage());
}

// Verificar se o usuário tem permissão para acessar esta página
if (!$is_admin) {
    header("Location: dashboard.php");
    exit;
}

// Buscar todos os usuários para o gerenciamento de permissões
$usuarios = [];
try {
    $stmt = $conn->prepare("
        SELECT 
            usuario_id, 
            usuario_nome, 
            usuario_login, 
            usuario_departamento, 
            usuario_nivel_id, 
            usuario_status 
        FROM tb_usuarios_sistema 
        WHERE usuario_status = 'ativo' 
        ORDER BY usuario_nome ASC
    ");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erro ao buscar usuários: " . $e->getMessage());
}

// Definir módulos/permissões do sistema
$modulos_sistema = [
    'dashboard' => [
        'nome' => 'Dashboard',
        'icon' => 'fas fa-tachometer-alt',
        'descricao' => 'Acesso ao painel principal',
        'permissoes' => ['visualizar']
    ],
    'usuarios' => [
        'nome' => 'Gerenciar Usuários',
        'icon' => 'fas fa-users-cog',
        'descricao' => 'Gerenciamento completo de usuários',
        'permissoes' => ['visualizar', 'criar', 'editar', 'excluir']
    ],
    'relatorios' => [
        'nome' => 'Relatórios Gerais',
        'icon' => 'fas fa-chart-pie',
        'descricao' => 'Acesso aos relatórios do sistema',
        'permissoes' => ['visualizar', 'exportar']
    ],
    'agricultura' => [
        'nome' => 'Agricultura',
        'icon' => 'fas fa-leaf',
        'descricao' => 'Módulo de agricultura',
        'permissoes' => ['visualizar', 'criar', 'editar', 'excluir', 'gerenciar']
    ],
    'assistencia_social' => [
        'nome' => 'Assistência Social',
        'icon' => 'fas fa-hands-helping',
        'descricao' => 'Módulo de assistência social',
        'permissoes' => ['visualizar', 'criar', 'editar', 'excluir', 'gerenciar']
    ],
    'cultura_turismo' => [
        'nome' => 'Cultura e Turismo',
        'icon' => 'fas fa-palette',
        'descricao' => 'Módulo de cultura e turismo',
        'permissoes' => ['visualizar', 'criar', 'editar', 'excluir', 'gerenciar']
    ],
    'educacao' => [
        'nome' => 'Educação',
        'icon' => 'fas fa-graduation-cap',
        'descricao' => 'Módulo de educação',
        'permissoes' => ['visualizar', 'criar', 'editar', 'excluir', 'gerenciar']
    ],
    'esporte' => [
        'nome' => 'Esporte',
        'icon' => 'fas fa-running',
        'descricao' => 'Módulo de esporte',
        'permissoes' => ['visualizar', 'criar', 'editar', 'excluir', 'gerenciar']
    ],
    'fazenda' => [
        'nome' => 'Fazenda',
        'icon' => 'fas fa-money-bill-wave',
        'descricao' => 'Módulo financeiro',
        'permissoes' => ['visualizar', 'criar', 'editar', 'excluir', 'gerenciar']
    ],
    'fiscalizacao' => [
        'nome' => 'Fiscalização',
        'icon' => 'fas fa-search',
        'descricao' => 'Módulo de fiscalização',
        'permissoes' => ['visualizar', 'criar', 'editar', 'excluir', 'gerenciar']
    ],
    'meio_ambiente' => [
        'nome' => 'Meio Ambiente',
        'icon' => 'fas fa-tree',
        'descricao' => 'Módulo de meio ambiente',
        'permissoes' => ['visualizar', 'criar', 'editar', 'excluir', 'gerenciar']
    ],
    'obras' => [
        'nome' => 'Obras',
        'icon' => 'fas fa-hard-hat',
        'descricao' => 'Módulo de obras',
        'permissoes' => ['visualizar', 'criar', 'editar', 'excluir', 'gerenciar']
    ],
    'rodoviario' => [
        'nome' => 'Rodoviário',
        'icon' => 'fas fa-truck',
        'descricao' => 'Módulo rodoviário',
        'permissoes' => ['visualizar', 'criar', 'editar', 'excluir', 'gerenciar']
    ],
    'servicos_urbanos' => [
        'nome' => 'Serviços Urbanos',
        'icon' => 'fas fa-city',
        'descricao' => 'Módulo de serviços urbanos',
        'permissoes' => ['visualizar', 'criar', 'editar', 'excluir', 'gerenciar']
    ]
];

// Buscar permissões atuais dos usuários (simulação - você pode implementar uma tabela de permissões)
$permissoes_usuarios = [];

// Verificar mensagens de sucesso ou erro
$sucesso = isset($_SESSION['sucesso_permissoes']) ? $_SESSION['sucesso_permissoes'] : null;
$erro = isset($_SESSION['erro_permissoes']) ? $_SESSION['erro_permissoes'] : null;
unset($_SESSION['sucesso_permissoes']);
unset($_SESSION['erro_permissoes']);

// Definir menus baseados no departamento (mesmo array do dashboard)
$menus_departamentos = [
    'AGRICULTURA' => ['icon' => 'fas fa-leaf', 'name' => 'Agricultura', 'color' => '#2e7d32'],
    'ASSISTENCIA SOCIAL' => ['icon' => 'fas fa-hands-helping', 'name' => 'Assistência Social', 'color' => '#e91e63'],
    'CULTURA E TURISMO' => ['icon' => 'fas fa-palette', 'name' => 'Cultura e Turismo', 'color' => '#ff5722'],
    'EDUCACAO' => ['icon' => 'fas fa-graduation-cap', 'name' => 'Educação', 'color' => '#9c27b0'],
    'ESPORTE' => ['icon' => 'fas fa-running', 'name' => 'Esporte', 'color' => '#4caf50'],
    'FAZENDA' => ['icon' => 'fas fa-money-bill-wave', 'name' => 'Fazenda', 'color' => '#ff9800'],
    'FISCALIZACAO' => ['icon' => 'fas fa-search', 'name' => 'Fiscalização', 'color' => '#673ab7'],
    'MEIO AMBIENTE' => ['icon' => 'fas fa-tree', 'name' => 'Meio Ambiente', 'color' => '#009688'],
    'OBRAS' => ['icon' => 'fas fa-hard-hat', 'name' => 'Obras', 'color' => '#795548'],
    'RODOVIARIO' => ['icon' => 'fas fa-truck', 'name' => 'Rodoviário', 'color' => '#607d8b'],
    'SERVICOS URBANOS' => ['icon' => 'fas fa-city', 'name' => 'Serviços Urbanos', 'color' => '#2196f3']
];

$titulo_sistema = 'Administração Geral';
$cor_tema = '#e74c3c';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Permissões - Sistema da Prefeitura</title>
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

        /* Sidebar styles - mesmos do dashboard */
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

        .toggle-btn {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
        }

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

        .submenu-link:hover,
        .submenu-link.active {
            background-color: rgba(255, 255, 255, 0.05);
            color: var(--secondary-color);
        }

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
            gap: 10px;
        }

        .user-details {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 3px;
        }

        .user-name {
            font-weight: bold;
            color: var(--text-color);
            white-space: nowrap;
        }

        .user-role {
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }

        .admin-badge {
            background-color: #e74c3c;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
        }

        .admin-badge i {
            font-size: 0.7rem;
        }

        .page-title {
            margin-bottom: 20px;
            font-size: 1.5rem;
            color: var(--text-color);
            display: flex;
            align-items: center;
        }

        .page-title i {
            margin-right: 10px;
            color: var(--secondary-color);
        }

        /* Breadcrumb */
        .breadcrumb {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            color: #666;
            font-size: 0.9rem;
        }

        .breadcrumb a {
            color: var(--secondary-color);
            text-decoration: none;
            margin-right: 8px;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .breadcrumb i {
            margin: 0 8px;
            font-size: 0.8rem;
        }

        /* Alertas */
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .alert i {
            margin-right: 10px;
            font-size: 1.1rem;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        /* Seletor de usuário */
        .user-selector {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .selector-title {
            font-size: 1.1rem;
            margin-bottom: 15px;
            color: var(--text-color);
            display: flex;
            align-items: center;
        }

        .selector-title i {
            margin-right: 8px;
            color: var(--secondary-color);
        }

        .user-select {
            width: 100%;
            max-width: 400px;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
        }

        /* Tabela de permissões */
        .permissions-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .permissions-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .permissions-title {
            font-size: 1.1rem;
            color: var(--text-color);
            display: flex;
            align-items: center;
        }

        .permissions-title i {
            margin-right: 8px;
            color: var(--secondary-color);
        }

        .save-permissions {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            transition: all 0.3s;
        }

        .save-permissions:hover {
            background-color: #c0392b;
        }

        .save-permissions i {
            margin-right: 8px;
        }

        .permissions-table {
            width: 100%;
        }

        .module-row {
            border-bottom: 1px solid #eee;
        }

        .module-header {
            padding: 15px 20px;
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .module-header:hover {
            background-color: #e9ecef;
        }

        .module-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--secondary-color);
            color: white;
            border-radius: 50%;
            margin-right: 15px;
        }

        .module-info {
            flex: 1;
        }

        .module-name {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 3px;
        }

        .module-description {
            font-size: 0.85rem;
            color: #666;
        }

        .module-toggle {
            margin-left: auto;
            font-size: 1.2rem;
            color: #666;
            transition: transform 0.3s;
        }

        .module-row.expanded .module-toggle {
            transform: rotate(90deg);
        }

        .permissions-row {
            padding: 20px;
            display: none;
            background-color: #fafafa;
        }

        .module-row.expanded .permissions-row {
            display: block;
        }

        .permissions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }

        .permission-item {
            display: flex;
            align-items: center;
            padding: 10px;
            background-color: white;
            border-radius: 6px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s;
        }

        .permission-item:hover {
            border-color: var(--secondary-color);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .permission-checkbox {
            margin-right: 10px;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .permission-label {
            font-size: 0.9rem;
            color: var(--text-color);
            cursor: pointer;
            text-transform: capitalize;
        }

        .no-user-selected {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .no-user-selected i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #ddd;
        }

        /* User info display */
        .selected-user-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: var(--secondary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
            margin-right: 15px;
        }

        .user-details-info {
            flex: 1;
        }

        .user-details-info h3 {
            margin-bottom: 5px;
            color: var(--text-color);
        }

        .user-details-info p {
            color: #666;
            font-size: 0.9rem;
        }

        .user-level-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
            margin-left: 10px;
        }

        .level-admin {
            background-color: #dc3545;
            color: white;
        }

        .level-user {
            background-color: #17a2b8;
            color: white;
        }

        /* Responsivo */
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

            .permissions-grid {
                grid-template-columns: 1fr;
            }

            .selected-user-info {
                flex-direction: column;
                text-align: center;
            }

            .user-avatar {
                margin-bottom: 10px;
                margin-right: 0;
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
                <a href="dashboard.php" class="menu-link">
                    <span class="menu-icon"><i class="fas fa-tachometer-alt"></i></span>
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>
            
            <div class="menu-separator"></div>
            <div class="menu-category">Administração</div>
            
            <li class="menu-item open">
                <a href="#" class="menu-link">
                    <span class="menu-icon"><i class="fas fa-users-cog"></i></span>
                    <span class="menu-text">Gerenciar Usuários</span>
                    <span class="arrow"><i class="fas fa-chevron-right"></i></span>
                </a>
                <ul class="submenu">
                    <li><a href="lista_usuarios.php" class="submenu-link">Lista de Usuários</a></li>
                    <li><a href="adicionar_usuario.php" class="submenu-link">Adicionar Usuário</a></li>
                    <li><a href="permissoes.php" class="submenu-link active">Permissões</a></li>
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
            
            <?php foreach ($menus_departamentos as $dept => $config): ?>
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <span class="menu-icon"><i class="<?php echo $config['icon']; ?>"></i></span>
                    <span class="menu-text"><?php echo $config['name']; ?></span>
                    <span class="arrow"><i class="fas fa-chevron-right"></i></span>
                </a>
                <ul class="submenu">
                    <li><a href="#" class="submenu-link">Relatórios</a></li>
                    <li><a href="#" class="submenu-link">Configurações</a></li>
                </ul>
            </li>
            <?php endforeach; ?>
            
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
                <h2>Gerenciar Permissões</h2>
            </div>
            <div class="user-info">
                <div style="width: 35px; height: 35px; border-radius: 50%; background-color: var(--secondary-color); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                    <?php echo strtoupper(substr($usuario_nome, 0, 1)); ?>
                </div>
                <div class="user-details">
                    <div class="user-name"><?php echo htmlspecialchars($usuario_nome); ?></div>
                    <div class="user-role">
                        <span class="admin-badge">
                            <i class="fas fa-crown"></i> Administrador
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <h1 class="page-title">
            <i class="fas fa-shield-alt"></i>
            Gerenciar Permissões de Usuários
        </h1>

        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="dashboard.php">Dashboard</a>
            <i class="fas fa-chevron-right"></i>
            <a href="lista_usuarios.php">Lista de Usuários</a>
            <i class="fas fa-chevron-right"></i>
            <span>Permissões</span>
        </div>

        <!-- Mensagens de alerta -->
        <?php if ($sucesso): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($sucesso); ?>
        </div>
        <?php endif; ?>

        <?php if ($erro): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-triangle"></i>
            <?php echo htmlspecialchars($erro); ?>
        </div>
        <?php endif; ?>

        <!-- Seletor de Usuário -->
        <div class="user-selector">
            <div class="selector-title">
                <i class="fas fa-user-check"></i>
                Selecionar Usuário para Gerenciar Permissões
            </div>
            
            <select id="userSelect" class="user-select" onchange="selectUser(this.value)">
                <option value="">Selecione um usuário...</option>
                <?php foreach ($usuarios as $user): ?>
                <option value="<?php echo $user['usuario_id']; ?>" data-name="<?php echo htmlspecialchars($user['usuario_nome']); ?>" data-login="<?php echo htmlspecialchars($user['usuario_login']); ?>" data-dept="<?php echo htmlspecialchars($user['usuario_departamento']); ?>" data-level="<?php echo $user['usuario_nivel_id']; ?>">
                    <?php echo htmlspecialchars($user['usuario_nome']); ?> (<?php echo htmlspecialchars($user['usuario_login']); ?>)
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Container de Permissões -->
        <div class="permissions-container">
            <div class="permissions-header">
                <div class="permissions-title">
                    <i class="fas fa-key"></i>
                    Configurar Permissões
                </div>
                <button class="save-permissions" onclick="savePermissions()" style="display: none;" id="saveBtn">
                    <i class="fas fa-save"></i>
                    Salvar Permissões
                </button>
            </div>
            
            <div id="noUserSelected" class="no-user-selected">
                <i class="fas fa-user-slash"></i>
                <h3>Nenhum usuário selecionado</h3>
                <p>Selecione um usuário acima para configurar suas permissões no sistema.</p>
            </div>
            
            <div id="permissionsContent" style="display: none;">
                <!-- Informações do usuário selecionado -->
                <div id="selectedUserInfo" class="selected-user-info">
                    <div class="user-avatar" id="userAvatar">U</div>
                    <div class="user-details-info">
                        <h3 id="userName">Nome do Usuário</h3>
                        <p>
                            <strong>Login:</strong> <span id="userLogin">login</span> | 
                            <strong>Departamento:</strong> <span id="userDept">Departamento</span>
                            <span id="userLevelBadge" class="user-level-badge level-user">Usuário</span>
                        </p>
                    </div>
                </div>

                <!-- Tabela de Permissões -->
                <form id="permissionsForm">
                    <input type="hidden" id="selectedUserId" name="user_id" value="">
                    
                    <div class="permissions-table">
                        <?php foreach ($modulos_sistema as $modulo_key => $modulo): ?>
                        <div class="module-row" data-module="<?php echo $modulo_key; ?>">
                            <div class="module-header" onclick="toggleModule('<?php echo $modulo_key; ?>')">
                                <div class="module-icon">
                                    <i class="<?php echo $modulo['icon']; ?>"></i>
                                </div>
                                <div class="module-info">
                                    <div class="module-name"><?php echo $modulo['nome']; ?></div>
                                    <div class="module-description"><?php echo $modulo['descricao']; ?></div>
                                </div>
                                <div class="module-toggle">
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                            </div>
                            
                            <div class="permissions-row">
                                <div class="permissions-grid">
                                    <?php foreach ($modulo['permissoes'] as $permissao): ?>
                                    <div class="permission-item">
                                        <input type="checkbox" 
                                               class="permission-checkbox" 
                                               id="<?php echo $modulo_key; ?>_<?php echo $permissao; ?>" 
                                               name="permissions[<?php echo $modulo_key; ?>][<?php echo $permissao; ?>]"
                                               onchange="updatePermissionStatus()">
                                        <label for="<?php echo $modulo_key; ?>_<?php echo $permissao; ?>" class="permission-label">
                                            <?php 
                                            $labels = [
                                                'visualizar' => 'Visualizar',
                                                'criar' => 'Criar',
                                                'editar' => 'Editar',
                                                'excluir' => 'Excluir',
                                                'gerenciar' => 'Gerenciar',
                                                'exportar' => 'Exportar'
                                            ];
                                            echo $labels[$permissao] ?? ucfirst($permissao);
                                            ?>
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </form>
            </div>
        </div>
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
        
        // Função para selecionar usuário
        function selectUser(userId) {
            const noUserDiv = document.getElementById('noUserSelected');
            const permissionsDiv = document.getElementById('permissionsContent');
            const saveBtn = document.getElementById('saveBtn');
            
            if (!userId) {
                noUserDiv.style.display = 'block';
                permissionsDiv.style.display = 'none';
                saveBtn.style.display = 'none';
                return;
            }
            
            const select = document.getElementById('userSelect');
            const selectedOption = select.options[select.selectedIndex];
            
            // Preencher informações do usuário
            const userName = selectedOption.getAttribute('data-name');
            const userLogin = selectedOption.getAttribute('data-login');
            const userDept = selectedOption.getAttribute('data-dept');
            const userLevel = selectedOption.getAttribute('data-level');
            
            document.getElementById('userName').textContent = userName;
            document.getElementById('userLogin').textContent = userLogin;
            document.getElementById('userDept').textContent = userDept || 'Não definido';
            document.getElementById('userAvatar').textContent = userName.charAt(0).toUpperCase();
            document.getElementById('selectedUserId').value = userId;
            
            // Badge de nível
            const levelBadge = document.getElementById('userLevelBadge');
            if (userLevel == '1') {
                levelBadge.textContent = 'Administrador';
                levelBadge.className = 'user-level-badge level-admin';
            } else {
                levelBadge.textContent = 'Usuário';
                levelBadge.className = 'user-level-badge level-user';
            }
            
            // Mostrar interface de permissões
            noUserDiv.style.display = 'none';
            permissionsDiv.style.display = 'block';
            saveBtn.style.display = 'flex';
            
            // Carregar permissões do usuário (simulação)
            loadUserPermissions(userId);
        }
        
        // Função para expandir/contrair módulos
        function toggleModule(moduleKey) {
            const moduleRow = document.querySelector(`[data-module="${moduleKey}"]`);
            moduleRow.classList.toggle('expanded');
        }
        
        // Função para carregar permissões do usuário (simulação)
        function loadUserPermissions(userId) {
            // Aqui você faria uma requisição AJAX para carregar as permissões reais
            // Por enquanto, vamos deixar todas desmarcadas
            const checkboxes = document.querySelectorAll('.permission-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            
            // Exemplo: se for administrador, marcar todas as permissões
            const userSelect = document.getElementById('userSelect');
            const selectedOption = userSelect.options[userSelect.selectedIndex];
            const userLevel = selectedOption.getAttribute('data-level');
            
            if (userLevel == '1') {
                checkboxes.forEach(checkbox => {
                    checkbox.checked = true;
                });
            }
        }
        
        // Função para atualizar status das permissões
        function updatePermissionStatus() {
            // Você pode adicionar lógica aqui para validar permissões
            // Por exemplo, se marcar "gerenciar", marcar automaticamente "visualizar"
        }
        
        // Função para salvar permissões
        function savePermissions() {
            const form = document.getElementById('permissionsForm');
            const formData = new FormData(form);
            const saveBtn = document.getElementById('saveBtn');
            
            // Desabilitar botão
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
            
            // Simular salvamento (aqui você faria uma requisição AJAX real)
            setTimeout(() => {
                alert('Permissões salvas com sucesso!');
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-save"></i> Salvar Permissões';
            }, 1500);
            
            // Código para requisição AJAX real:
            /*
            fetch('../controller/salvar_permissoes.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Permissões salvas com sucesso!');
                } else {
                    alert('Erro ao salvar permissões: ' + data.message);
                }
            })
            .catch(error => {
                alert('Erro de comunicação: ' + error);
            })
            .finally(() => {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-save"></i> Salvar Permissões';
            });
            */
        }
        
        // Função para selecionar/deselecionar todas as permissões de um módulo
        function toggleAllModulePermissions(moduleKey, checked) {
            const checkboxes = document.querySelectorAll(`input[name^="permissions[${moduleKey}]"]`);
            checkboxes.forEach(checkbox => {
                checkbox.checked = checked;
            });
        }
        
        // Adicionar listener para Ctrl+S (atalho para salvar)
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                const permissionsDiv = document.getElementById('permissionsContent');
                if (permissionsDiv.style.display !== 'none') {
                    savePermissions();
                }
            }
        });
    </script>
</body>
</html>