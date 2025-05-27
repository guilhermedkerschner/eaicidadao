<?php
/**
 * Arquivo: verificar_cadastro_habitacao.php
 * Descrição: Verifica se o usuário já possui cadastro habitacional
 */

// Desativar exibição de erros
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Inicia a sessão
session_start();

// Define o cabeçalho para JSON
header('Content-Type: application/json; charset=utf-8');

// Verifica se o usuário está logado
if (!isset($_SESSION['user_logado']) || $_SESSION['user_logado'] !== true) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Usuário não autenticado.'
    ]);
    exit;
}

// Verifica se é uma requisição AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Requisição inválida.'
    ]);
    exit;
}

// Incluir arquivo de configuração
require_once "../database/conect.php";

try {
    // Verificar se o usuário já possui um cadastro habitacional
    $sql_verificar = "SELECT cad_social_id, cad_social_protocolo, cad_social_status, cad_social_data_cadastro 
                     FROM tb_cad_social 
                     WHERE cad_usu_id = :usuario_id 
                     AND cad_social_programa_interesse = 'HABITASIO'
                     LIMIT 1";

    $stmt_verificar = $conn->prepare($sql_verificar);
    $stmt_verificar->bindParam(':usuario_id', $_SESSION['user_id']);
    $stmt_verificar->execute();

    if ($stmt_verificar->rowCount() > 0) {
        $cadastro = $stmt_verificar->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'has_cadastro' => true,
            'cadastro_id' => $cadastro['cad_social_id'],
            'protocolo' => $cadastro['cad_social_protocolo'],
            
            'status_cadastro' => $cadastro['cad_social_status'],
            'data_cadastro' => date('d/m/Y H:i', strtotime($cadastro['cad_social_data_cadastro'])),
            'message' => 'Usuário já possui cadastro habitacional.'
        ]);
    } else {
        echo json_encode([
            'status' => 'success',
            'has_cadastro' => false,
            'message' => 'Usuário pode realizar o cadastro.'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Erro ao verificar cadastro habitacional: " . $e->getMessage());
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro interno do servidor.'
    ]);
}
?>