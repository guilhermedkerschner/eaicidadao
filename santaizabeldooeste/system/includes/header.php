<?php
/**
 * Header component
 * Sistema Eai Cidadão! - Prefeitura de Santa Izabel do Oeste
 */

// Verificar se a sessão já foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Variáveis para controle de autenticação
$usuario_logado = isset($_SESSION['user_logado']) && $_SESSION['user_logado'] === true;
$nome_usuario = isset($_SESSION['user_nome']) ? $_SESSION['user_nome'] : '';
$nivel_acesso = isset($_SESSION['user_nivel']) ? $_SESSION['user_nivel'] : '';

// Base URL - ajuste conforme a estrutura do seu projeto
$base_url = isset($base_url) ? $base_url : "..";

// Verificar o título da página
$page_title = isset($page_title) ? $page_title : "Eai Cidadão! - Prefeitura de Santa Izabel do Oeste";

// Definir caminho para o CSS
$css_includes = isset($css_includes) ? $css_includes : [];
$js_includes = isset($js_includes) ? $js_includes : [];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- CSS Base -->
    <link rel="stylesheet" type="text/css" href="<?php echo $base_url; ?>/assets/css/main.css">
    
    <!-- CSS específicos adicionais -->
    <?php foreach ($css_includes as $css): ?>
    <link rel="stylesheet" type="text/css" href="<?php echo $base_url; ?>/assets/css/<?php echo $css; ?>">
    <?php endforeach; ?>
</head>
<body>
    <!-- Loading Overlay para AJAX -->
    <div id="loading-overlay" class="loading-overlay">
        <div class="loading-spinner"></div>
        <div id="loading-text" class="loading-text">Processando...</div>
    </div>

    <div class="header">
        <div class="header-left">
            <div class="municipality-logo">
                <img src="<?php echo $base_url; ?>/assets/img/logo_municipio.png" alt="Logo do Município">
            </div>
            <div class="title-container">
                <h1>Eai Cidadão!</h1>
                <h2 class="municipality-name">Município de Santa Izabel do Oeste</h2>
            </div>
        </div>
        <div class="header-buttons">
            <!-- Área de login -->
            <div class="login-area">
                <?php if (!$usuario_logado): ?>
                <!-- Botões de login (visíveis quando usuário não está logado) -->
                <a href="<?php echo $base_url; ?>/login_cidadao.php" class="login-button user-login">
                    <i class="fas fa-user"></i>
                    Área do Cidadão
                </a>
                <a href="<?php echo $base_url; ?>/login.php" class="login-button admin-login">
                    <i class="fas fa-lock"></i>
                    Área Restrita
                </a>
                <?php else: ?>
                <!-- Menu do usuário quando está logado -->
                <div class="user-logged-in" style="display: flex;">
                    <div class="user-button">
                        <i class="fas fa-user-check"></i>
                        Olá, <span id="user-name"><?php echo htmlspecialchars($nome_usuario); ?></span>
                    </div>
                    <div class="user-dropdown">
                        <a href="<?php echo $base_url; ?>/app/usuario/perfil.php" class="dropdown-item">
                            <i class="fas fa-id-card"></i>
                            Meu Perfil
                        </a>
                        <a href="<?php echo $base_url; ?>/controller/logout_user.php" class="dropdown-item">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Botão Voltar definido conforme a página -->
            <?php if (isset($back_link) && $back_link): ?>
            <a href="<?php echo $back_link; ?>" class="back-button">
                <i class="fas fa-arrow-left"></i> 
                <?php echo $back_text ?? 'Voltar'; ?>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div id="status-message" class="status-message"></div>