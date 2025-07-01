<?php
session_start();

// Verificar se está logado
if (!isset($_SESSION['usersystem_logado'])) {
    echo "Usuário não está logado";
    exit;
}

require_once "../lib/config.php";

// Testar conexão com banco
try {
    $stmt = $conn->query("SELECT COUNT(*) FROM tb_campeonatos");
    echo "Conexão com banco OK - " . $stmt->fetchColumn() . " campeonatos encontrados<br>";
} catch (Exception $e) {
    echo "Erro no banco: " . $e->getMessage() . "<br>";
}

// Verificar dados do usuário
echo "User ID: " . $_SESSION['usersystem_id'] . "<br>";
echo "User Nome: " . $_SESSION['usersystem_nome'] . "<br>";

// Testar se o MenuManager existe
if (file_exists("./core/MenuManager.php")) {
    echo "MenuManager.php existe<br>";
} else {
    echo "MenuManager.php NÃO existe<br>";
}

echo "Teste concluído!";
?>