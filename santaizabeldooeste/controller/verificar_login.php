
<?php
// Arquivo: verificar_login.php
// Este arquivo verifica se o usuário está logado
// Inclua-o no início das páginas que requerem autenticação

// Inicia a sessão se ainda não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Define o tempo limite da sessão (em segundos) - 30 minutos
$tempo_limite = 30 * 60;

// Verifica se o usuário está logado
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    // Usuário não está logado, redireciona para a página de login
    header("Location: login.php");
    exit();
}

// Verifica se a sessão expirou
if (isset($_SESSION['ultimo_acesso']) && (time() - $_SESSION['ultimo_acesso'] > $tempo_limite)) {
    // A sessão expirou, destroi a sessão e redireciona para o login
    session_unset();
    session_destroy();
    
    // Inicia uma nova sessão para armazenar a mensagem de erro
    session_start();
    $_SESSION['erro_login'] = "Sua sessão expirou. Por favor, faça login novamente.";
    header("Location: login.php");
    exit();
}

// Atualiza o tempo do último acesso
$_SESSION['ultimo_acesso'] = time();

// Função para verificar o nível de acesso (use conforme necessário)
function verificarAcesso($niveis_permitidos) {
    // Verifica se o nível de acesso do usuário está no array de níveis permitidos
    if (!in_array($_SESSION['nivel_acesso'], $niveis_permitidos)) {
        // Acesso negado
        header("Location: acesso_negado.php");
        exit();
    }
    // Se chegar aqui, o acesso é permitido
    return true;
}
?>