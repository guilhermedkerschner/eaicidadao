<?php
/**
 * Arquivo: functions.php
 * Descrição: Funções auxiliares para o sistema Eai Cidadão!
 */

/**
 * Verifica se o usuário está logado e tem permissão para acessar o módulo
 * 
 * @param int $modulo_id ID do módulo a ser verificado
 * @param string $permissao Tipo de permissão (visualizar, criar, editar, excluir, aprovar)
 * @return bool True se o usuário tem permissão, false caso contrário
 */
function verificarPermissao($modulo_id, $permissao = 'visualizar') {
    if (!isset($_SESSION['usersystem_logado']) || $_SESSION['usersystem_logado'] !== true) {
        return false;
    }
    
    // Verificar expiração da sessão
    if (isset($_SESSION['usersystem_expiracao']) && 
        strtotime($_SESSION['usersystem_expiracao']) < time()) {
        // Sessão expirada, fazer logout
        session_unset();
        session_destroy();
        return false;
    }
    
    // Administrador tem todas as permissões
    if ($_SESSION['usersystem_nivel'] === 'Administrador') {
        return true;
    }
    
    // Verificar se o usuário tem o módulo permitido
    if (isset($_SESSION['usersystem_modulos'][$modulo_id])) {
        $modulo_info = $_SESSION['usersystem_modulos'][$modulo_id];
        
        // Verificar a permissão específica
        if ($permissao === 'visualizar') {
            return true; // Se o módulo está na lista, o usuário pode visualizar
        } else {
            return isset($modulo_info[$permissao]) && $modulo_info[$permissao];
        }
    }
    
    return false;
}

/**
 * Verifica a permissão e redireciona se não tiver acesso
 * 
 * @param int $modulo_id ID do módulo a ser verificado
 * @param string $permissao Tipo de permissão (visualizar, criar, editar, excluir, aprovar)
 * @param string $redirect_url URL para redirecionamento em caso de acesso negado
 */
function verificarPermissaoRedirect($modulo_id, $permissao = 'visualizar', $redirect_url = '../login.php') {
    if (!verificarPermissao($modulo_id, $permissao)) {
        header("Location: " . $redirect_url);
        exit;
    }
}

/**
 * Registra uma ação no sistema
 * 
 * @param PDO $conn Conexão com o banco de dados
 * @param int $usuario_id ID do usuário
 * @param string $acao Descrição da ação
 * @param int $modulo_id ID do módulo (opcional)
 * @param string $detalhes Detalhes adicionais (opcional)
 * @return bool True se registrado com sucesso, false caso contrário
*/

 function registrarLog($conn, $usuario_id, $acao, $modulo_id = null, $detalhes = '') {
    try {
        $stmt = $conn->prepare("
            INSERT INTO tb_logs_acesso 
            (usuario_id, log_ip, log_acao, log_modulo_id, log_detalhes) 
            VALUES 
            (:usuario_id, :ip, :acao, :modulo_id, :detalhes)
        ");
        
        $ip = getClientIP();
        
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':ip', $ip);
        $stmt->bindParam(':acao', $acao);
        $stmt->bindParam(':modulo_id', $modulo_id);
        $stmt->bindParam(':detalhes', $detalhes);
        $stmt->execute();
        
        return true;
    } catch (Exception $e) {
        // Em ambiente de produção, considere logar este erro em um arquivo
        error_log("Erro ao registrar log: " . $e->getMessage());
        return false;
    }
}
 

/**
 * Obtém o endereço IP do cliente
 * 
 * @return string Endereço IP do cliente
 */
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

/**
 * Verifica a sessão do usuário
 * 
 * @param PDO $conn Conexão com o banco de dados
 * @return bool True se a sessão é válida, false caso contrário
 */
function verificarSessao($conn) {
    if (!isset($_SESSION['usersystem_logado']) || 
        !isset($_SESSION['usersystem_sessao_id']) || 
        !isset($_SESSION['usersystem_sessao_token'])) {
        return false;
    }
    
    try {
        $stmt = $conn->prepare("
            SELECT * FROM tb_sessoes_usuario 
            WHERE sessao_id = :sessao_id 
            AND sessao_token = :token 
            AND sessao_status = 'ativa'
            AND sessao_data_expiracao > NOW()
        ");
        
        $stmt->bindParam(':sessao_id', $_SESSION['usersystem_sessao_id']);
        $stmt->bindParam(':token', $_SESSION['usersystem_sessao_token']);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            // Atualizar data da última atividade
            $stmt_update = $conn->prepare("
                UPDATE tb_sessoes_usuario 
                SET sessao_data_ultima_atividade = NOW() 
                WHERE sessao_id = :sessao_id
            ");
            $stmt_update->bindParam(':sessao_id', $_SESSION['usersystem_sessao_id']);
            $stmt_update->execute();
            
            return true;
        }
        
        return false;
    } catch (Exception $e) {
        error_log("Erro ao verificar sessão: " . $e->getMessage());
        return false;
    }
}

/**
 * Encerra a sessão do usuário
 * 
 * @param PDO $conn Conexão com o banco de dados
 */
function encerrarSessao($conn) {
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
    
    // Limpar variáveis de sessão
    session_unset();
    session_destroy();
    
    // Iniciar nova sessão para mensagens
    session_start();
}

/**
 * Formata uma data para o formato brasileiro
 * 
 * @param string $data Data no formato Y-m-d ou Y-m-d H:i:s
 * @param bool $com_hora Incluir hora na formatação
 * @return string Data formatada
 */
function formatarData($data, $com_hora = false) {
    if (empty($data)) return '';
    
    $timestamp = strtotime($data);
    
    if ($com_hora) {
        return date('d/m/Y H:i:s', $timestamp);
    } else {
        return date('d/m/Y', $timestamp);
    }
}

/**
 * Sanitiza um texto para evitar XSS e injection
 * 
 * @param string $texto Texto a ser sanitizado
 * @return string Texto sanitizado
 */
function sanitizarTexto($texto) {
    return htmlspecialchars(strip_tags(trim($texto)), ENT_QUOTES, 'UTF-8');
}

/**
 * Gera um token aleatório
 * 
 * @param int $tamanho Tamanho do token
 * @return string Token gerado
 */
function gerarToken($tamanho = 32) {
    return bin2hex(random_bytes($tamanho / 2));
}

/**
 * Verifica se uma string está em formato JSON válido
 * 
 * @param string $string String a ser verificada
 * @return bool True se for JSON válido, false caso contrário
 */
function isJson($string) {
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

/**
 * Limita o tamanho de uma string, adicionando "..." ao final
 * 
 * @param string $texto Texto original
 * @param int $tamanho Tamanho máximo
 * @return string Texto limitado
 */
function limitarTexto($texto, $tamanho = 100) {
    if (strlen($texto) <= $tamanho) {
        return $texto;
    }
    
    return substr($texto, 0, $tamanho) . '...';
}