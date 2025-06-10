<?php
// Inicia a sessão
session_start();

// Verifica se o usuário está logado no sistema administrativo
if (!isset($_SESSION['usersystem_logado'])) {
    header("Location: ../acessdeniedrestrict.php"); 
    exit;
}

// Inclui arquivo de configuração com conexão ao banco de dados
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

// Verificar permissões de acesso
$tem_permissao = $is_admin || strtoupper($usuario_departamento) === 'ESPORTE';

if (!$tem_permissao) {
    header("Location: dashboard.php?erro=acesso_negado");
    exit;
}

// Buscar estatísticas do módulo
$estatisticas = [
    'total_atletas' => 0,
    'atletas_ativos' => 0,
    'total_campeonatos' => 0,
    'campeonatos_ativos' => 0,
    'total_espacos' => 0,
    'espacos_ativos' => 0,
    'total_equipe' => 0,
    'equipe_ativa' => 0
];

try {
    // Atletas
    $stmt = $conn->query("SELECT COUNT(*) as total FROM tb_atletas");
    $estatisticas['total_atletas'] = $stmt->fetch()['total'] ?? 0;
    
    $stmt = $conn->query("SELECT COUNT(*) as total FROM tb_atletas WHERE atleta_status = 'ATIVO'");
    $estatisticas['atletas_ativos'] = $stmt->fetch()['total'] ?? 0;
    
    // Campeonatos
    $stmt = $conn->query("SELECT COUNT(*) as total FROM tb_campeonatos");
    $estatisticas['total_campeonatos'] = $stmt->fetch()['total'] ?? 0;
    
    $stmt = $conn->query("SELECT COUNT(*) as total FROM tb_campeonatos WHERE campeonato_status IN ('INSCRICOES_ABERTAS', 'EM_ANDAMENTO')");
    $estatisticas['campeonatos_ativos'] = $stmt->fetch()['total'] ?? 0;
    
    // Espaços Físicos
    $stmt = $conn->query("SELECT COUNT(*) as total FROM tb_espacos_fisicos");
    $estatisticas['total_espacos'] = $stmt->fetch()['total'] ?? 0;
    
    $stmt = $conn->query("SELECT COUNT(*) as total FROM tb_espacos_fisicos WHERE espaco_status = 'ATIVO'");
    $estatisticas['espacos_ativos'] = $stmt->fetch()['total'] ?? 0;
    
    // Equipe
    $stmt = $conn->query("SELECT COUNT(*) as total FROM tb_equipe_momento");
    $estatisticas['total_equipe'] = $stmt->fetch()['total'] ?? 0;
    
    $stmt = $conn->query("SELECT COUNT(*) as total FROM tb_equipe_momento WHERE membro_status = 'ATIVO'");
    $estatisticas['equipe_ativa'] = $stmt->fetch()['total'] ?? 0;
    
} catch (PDOException $e) {
    error_log("Erro ao buscar estatísticas: " . $e->getMessage());
}

// Definir tema baseado no usuário
$titulo_sistema = $is_admin ? 'Administração Geral' : 'Esporte';
$cor_tema = $is_admin ? '#e74c3c' : '#4caf50';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo Esporte - Sistema da Prefeitura</title>
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
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #45a049;
            --info-color: #17a2b8;
        }

        body {
            display: flex;
            min-height: 100vh;
            background-color: #f5f7fa;
        }

        /* Sidebar styles - mesmo padrão das outras páginas */
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

        /* Main content */
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

        .admin-badge, .department-badge {
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

        .admin-badge {
            background-color: #e74c3c;
        }

        .admin-badge i {
            font-size: 0.7rem;
        }

        .department-badge {
            background-color: var(--secondary-color);
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

        /* Cards de Estatísticas */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            position: relative;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--secondary-color), #3498db);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--secondary-color);
            margin-bottom: 8px;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-icon {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 2rem;
            color: rgba(0, 0, 0, 0.1);
        }

        /* Cards de Menu */
        .menu-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .menu-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            border-left: 4px solid var(--secondary-color);
        }

        .menu-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }

        .menu-card-header {
            padding: 20px;
            background: linear-gradient(135deg, var(--secondary-color), #45a049);
            color: white;
        }

        .menu-card-title {
            font-size: 1.2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }

        .menu-card-title i {
            margin-right: 10px;
            font-size: 1.5rem;
        }

        .menu-card-description {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .menu-card-body {
            padding: 20px;
        }

        .menu-card-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
        }

        .btn {
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .btn i {
            margin-right: 6px;
        }

        .btn-primary {
            background-color: var(--secondary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: #45a049;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #545b62;
        }

        .btn-success {
            background-color: var(--success-color);
            color: white;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        .btn-info {
            background-color: var(--info-color);
            color: white;
        }

        .btn-info:hover {
            background-color: #138496;
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

            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }

            .menu-cards {
                grid-template-columns: 1fr;
            }
        }

        .mobile-toggle {
            display: none;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
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
                    <li><a href="adicionar_usuario.php" class="submenu-link">Adicionar Usuário</a></li>
                    <li><a href="permissoes.php" class="submenu-link">Permissões</a></li>
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
                   <li><a href="assistencia.php" class="submenu-link">Programas Habitacionais</a></li>
                   <li><a href="#" class="submenu-link">Relatórios</a></li>
               </ul>
           </li>
           
           <li class="menu-item open">
               <a href="#" class="menu-link">
                   <span class="menu-icon"><i class="fas fa-running"></i></span>
                   <span class="menu-text">Esporte</span>
                   <span class="arrow"><i class="fas fa-chevron-right"></i></span>
               </a>
               <ul class="submenu">
                   <li><a href="esporte_atletas.php" class="submenu-link">Atletas</a></li>
                   <li><a href="esporte_campeonatos.php" class="submenu-link">Campeonatos</a></li>
                   <li><a href="esporte_espacos.php" class="submenu-link">Espaços Físicos</a></li>
                   <li><a href="esporte_equipe.php" class="submenu-link">Equipes</a></li>
                   <li><a href="esporte_relatorios.php" class="submenu-link">Relatórios</a></li>
               </ul>
           </li>
           
           <?php else: ?>
           <!-- Menu específico do departamento para usuários normais -->
           <?php if (strtoupper($usuario_departamento) === 'ESPORTE'): ?>
           <div class="menu-separator"></div>
           <div class="menu-category">Esporte</div>
           
           <li class="menu-item open">
               <a href="#" class="menu-link">
                   <span class="menu-icon"><i class="fas fa-running"></i></span>
                   <span class="menu-text">Esporte</span>
                   <span class="arrow"><i class="fas fa-chevron-right"></i></span>
               </a>
               <ul class="submenu">
                   <li><a href="esporte_atletas.php" class="submenu-link">Atletas</a></li>
                   <li><a href="esporte_campeonatos.php" class="submenu-link">Campeonatos</a></li>
                   <li><a href="esporte_espacos.php" class="submenu-link">Espaços Físicos</a></li>
                   <li><a href="esporte_equipe.php" class="submenu-link">Equipes</a></li>
                   <li><a href="esporte_relatorios.php" class="submenu-link">Relatórios</a></li>
               </ul>
           </li>
           <?php endif; ?>
           <?php endif; ?>
           
           <div class="menu-separator"></div>
           
           <li class="menu-item">
               <a href="perfil.php" class="menu-link">
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
   <div class="main-content" id="mainContent">
       <div class="header">
           <div>
               <button class="mobile-toggle">
                   <i class="fas fa-bars"></i>
               </button>
               <h2>Módulo Esporte</h2>
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
                           Esporte
                       </span>
                       <?php endif; ?>
                   </div>
               </div>
           </div>
       </div>
       
       <h1 class="page-title">
           <i class="fas fa-running"></i>
           Dashboard - Esporte
       </h1>

       <!-- Breadcrumb -->
       <div class="breadcrumb">
           <a href="dashboard.php">Dashboard</a>
           <i class="fas fa-chevron-right"></i>
           <span>Esporte</span>
       </div>

       <!-- Estatísticas -->
       <div class="stats-container">
           <div class="stat-card">
               <div class="stat-number"><?php echo number_format($estatisticas['atletas_ativos']); ?></div>
               <div class="stat-label">Atletas Ativos</div>
               <i class="fas fa-user-friends stat-icon"></i>
           </div>
           <div class="stat-card">
               <div class="stat-number"><?php echo number_format($estatisticas['campeonatos_ativos']); ?></div>
               <div class="stat-label">Campeonatos Ativos</div>
               <i class="fas fa-trophy stat-icon"></i>
           </div>
           <div class="stat-card">
               <div class="stat-number"><?php echo number_format($estatisticas['espacos_ativos']); ?></div>
               <div class="stat-label">Espaços Ativos</div>
               <i class="fas fa-map-marker-alt stat-icon"></i>
           </div>
           <div class="stat-card">
               <div class="stat-number"><?php echo number_format($estatisticas['equipe_ativa']); ?></div>
               <div class="stat-label">Equipe Ativa</div>
               <i class="fas fa-users-cog stat-icon"></i>
           </div>
       </div>

       <!-- Cards de Menu -->
       <div class="menu-cards">
           <div class="menu-card">
               <div class="menu-card-header">
                   <div class="menu-card-title">
                       <i class="fas fa-user-friends"></i>
                       Atletas
                   </div>
                   <div class="menu-card-description">
                       Gerencie o cadastro de atletas, suas modalidades e categorias
                   </div>
               </div>
               <div class="menu-card-body">
                   <div class="menu-card-actions">
                       <a href="esporte_atletas.php" class="btn btn-primary">
                           <i class="fas fa-list"></i> Listar
                       </a>
                       <a href="esporte_atletas.php?acao=adicionar" class="btn btn-success">
                           <i class="fas fa-plus"></i> Adicionar
                       </a>
                   </div>
               </div>
           </div>

           <div class="menu-card">
               <div class="menu-card-header">
                   <div class="menu-card-title">
                       <i class="fas fa-trophy"></i>
                       Campeonatos
                   </div>
                   <div class="menu-card-description">
                       Organize e gerencie torneios, campeonatos e competições
                   </div>
               </div>
               <div class="menu-card-body">
                   <div class="menu-card-actions">
                       <a href="esporte_campeonatos.php" class="btn btn-primary">
                           <i class="fas fa-list"></i> Listar
                       </a>
                       <a href="esporte_campeonatos.php?acao=adicionar" class="btn btn-success">
                           <i class="fas fa-plus"></i> Criar
                       </a>
                   </div>
               </div>
           </div>

           <div class="menu-card">
               <div class="menu-card-header">
                   <div class="menu-card-title">
                       <i class="fas fa-map-marker-alt"></i>
                       Espaços Físicos
                   </div>
                   <div class="menu-card-description">
                       Cadastre e gerencie quadras, campos e espaços esportivos
                   </div>
               </div>
               <div class="menu-card-body">
                   <div class="menu-card-actions">
                       <a href="esporte_espacos.php" class="btn btn-primary">
                           <i class="fas fa-list"></i> Listar
                       </a>
                       <a href="esporte_espacos.php?acao=adicionar" class="btn btn-success">
                           <i class="fas fa-plus"></i> Cadastrar
                       </a>
                   </div>
               </div>
           </div>

           <div class="menu-card">
               <div class="menu-card-header">
                   <div class="menu-card-title">
                       <i class="fas fa-users-cog"></i>
                       Equipes
                   </div>
                   <div class="menu-card-description">
                       Gerencie a equipe técnica, treinadores e colaboradores
                   </div>
               </div>
               <div class="menu-card-body">
                   <div class="menu-card-actions">
                       <a href="esporte_equipe.php" class="btn btn-primary">
                           <i class="fas fa-list"></i> Listar
                       </a>
                       <a href="esporte_equipe.php?acao=adicionar" class="btn btn-success">
                           <i class="fas fa-plus"></i> Adicionar
                       </a>
                   </div>
               </div>
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

           // Submenu toggle
           const menuItems = document.querySelectorAll('.menu-item');
           menuItems.forEach(function(item) {
               const menuLink = item.querySelector('.menu-link');
               if (menuLink && menuLink.querySelector('.arrow')) {
                   menuLink.addEventListener('click', function(e) {
                       e.preventDefault();
                       item.classList.toggle('open');
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