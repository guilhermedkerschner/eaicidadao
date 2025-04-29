<?php
// Inicia a sessão
session_start();

// Limpa todas as variáveis de sessão
$_SESSION = array();

// Destrói a sessão
if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

// Redireciona para a página inicial
header("Location: index.php");
exit();
?>