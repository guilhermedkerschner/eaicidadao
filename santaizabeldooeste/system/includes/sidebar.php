<?php
/**
 * Sidebar component for admin area
 * Sistema Eai Cidadão! - Prefeitura de Santa Izabel do Oeste
 */

// Verificar se a sessão já foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se o usuário tem permissão para acessar esta área
if (!isset($_SESSION['usersystem_logado'])) {
    header("Location: ../acessdeniedrestrict.php");
    exit;
}

// Base URL - ajuste conforme a estrutura do seu projeto
$base_url = isset($base_url) ? $base_url : "..";

// Obter a página atual para destacar o item do menu
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h3>Prefeitura</h3>
        <button class="toggle-btn" id="toggle-sidebar">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    
    <ul class="menu">
        <li class="menu-item <?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
            <a href="<?php echo $base_url; ?>/system/dashboard.php" class="menu-link">
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
        
        <!-- Restante dos itens do menu existente -->
        <!-- ... -->
        
        <li class="menu-item">
            <a href="<?php echo $base_url; ?>/controller/logout.php" class="menu-link">
                <span class="menu-icon"><i class="fas fa-sign-out-alt"></i></span>
                <span class="menu-text">Sair</span>
            </a>
        </li>
    </ul>
</div>