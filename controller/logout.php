<?php
/**
 * Arquivo: logout.php
 * Descrição: Encerra a sessão do usuário no sistema Eai Cidadão!
 */

// Inicia a sessão
session_start();

// Incluir arquivos necessários
require_once 'lib/config.php';
require_once 'lib/functions.php';

// Verificar se o usuário está logado
if (isset($_SESSION['usersystem_logado']) && $_SESSION['usersystem_logado'] === true) {
    // Registrar log de logout
    if (isset($_SESSION['usersystem_id'])) {
        registrarLog($conn, $_SESSION['usersystem_id'], 'Logout', null, 'Logout realizado com sucesso');
    }
    
    // Atualizar status da sessão no banco de dados
    if (isset($_SESSION['usersystem_sessao_id'])) {
        try {
            $stmt = $conn->prepare("
                UPDATE tb_sessoes_usuario 
                SET sessao_status = 'encerrada' 
                WHERE sessao_id = :sessao_id
            ");
            $stmt->bindParam(':sessao_id', $_SESSION['usersystem_sessao_id']);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Erro ao encerrar sessão: " . $e->getMessage());
        }
    }
}

// Destruir a sessão
session_unset();
session_destroy();

// Iniciar nova sessão para mensagem de feedback
session_start();
$_SESSION['mensagem'] = "Você saiu do sistema com sucesso.";
$_SESSION['tipo_mensagem'] = "success";

// Redirecionar para a página de login
header("Location: ../login.php");
exit;