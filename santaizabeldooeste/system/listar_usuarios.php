<?php
/**
 * Arquivo: listar_usuarios.php
 * Descrição: Lista os usuários cadastrados no sistema
 * 
 * Parte do sistema de gerenciamento da Prefeitura
 */

// Desativar exibição de erros para evitar corromper a saída JSON
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Inicia a sessão
session_start();



// Define o cabeçalho para JSON
header('Content-Type: application/json; charset=utf-8');

// Verifica se o usuário está logado e é administrador
if (!isset($_SESSION['usersystem_logado']) || $_SESSION['usersystem_nivel'] !== 'Administrador') {
    echo json_encode([
        'success' => false,
        'message' => 'Acesso negado. Apenas administradores podem visualizar usuários.'
    ]);
    exit;
}

// Incluir arquivo de configuração com conexão ao banco de dados
require_once "../database/conect.php";

try {
    // Verificar conexão com o banco
    if (!isset($conn) || !$conn) {
        throw new Exception("Erro na conexão com o banco de dados.");
    }
    
    // Buscar usuários do sistema (ajuste conforme a estrutura da sua tabela)
    $sql = "SELECT 
                usuario_id as id,
                usuario_nome as nome,
                usuario_login as login,
                usuario_email as email,
                usuario_nivel_id as nivel,
                usuario_status as status,
                usuario_departamento as setor,
                usuario_ultimo_acesso as ultimo_acesso,
                usuario_data_criacao as data_cadastro
            FROM tb_usuarios_sistema 
            WHERE usuario_nivel_id IN ('1', '2', '3')
            ORDER BY usuario_nome ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    $usuarios = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Formatar data do último acesso
        $ultimo_acesso = null;
        if ($row['ultimo_acesso']) {
            $ultimo_acesso = date('d/m/Y H:i', strtotime($row['ultimo_acesso']));
        }
        
        $usuarios[] = [
            'id' => (int)$row['id'],
            'nome' => $row['nome'],
            'login' => $row['login'],
            'email' => $row['email'],
            'nivel' => $row['nivel'],
            'status' => $row['status'],
            'setor' => $row['setor'],
            'ultimo_acesso' => $ultimo_acesso,
            'data_cadastro' => date('d/m/Y', strtotime($row['data_cadastro']))
        ];
    }
    
    echo json_encode([
        'success' => true,
        'users' => $usuarios,
        'total' => count($usuarios)
    ]);
    
} catch (PDOException $e) {
    // Log do erro
    error_log("Erro ao listar usuários: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor. Tente novamente mais tarde.'
    ]);
    
} catch (Exception $e) {
    // Log do erro
    error_log("Erro ao listar usuários: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>