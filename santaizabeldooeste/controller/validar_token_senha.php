<?php
/**
 * Validação de Token e Redefinição de Senha
 * Arquivo: controller/validar_token_senha.php
 */

session_start();

require_once '../database/conect.php';

// Função para sanitizar input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Verificar método de requisição
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../recuperar_senha.php');
    exit;
}

// Verificar step da recuperação
$step = isset($_POST['step']) ? (int)$_POST['step'] : 1;

if ($step === 2) {
    // Validação do token
    
    if (!isset($_POST['token']) || empty(trim($_POST['token']))) {
        $_SESSION['erro_recuperacao'] = 'Por favor, informe o código de verificação.';
        header('Location: ../recuperar_senha.php?step=2');
        exit;
    }
    
    $token = sanitize($_POST['token']);
    $email = isset($_SESSION['email_recuperacao']) ? $_SESSION['email_recuperacao'] : '';
    
    if (empty($email)) {
        $_SESSION['erro_recuperacao'] = 'Sessão expirada. Inicie o processo novamente.';
        header('Location: ../recuperar_senha.php');
        exit;
    }
    
    try {
        // Buscar token válido
        $token_hash = hash('sha256', $token);
        
        $sql = "SELECT prt.id, prt.user_id, u.cad_usu_nome, u.cad_usu_email
                FROM password_reset_tokens prt
                INNER JOIN tb_cad_usuarios u ON prt.user_id = u.cad_usu_id
                WHERE u.cad_usu_email = :email 
                AND prt.token_hash = :token_hash 
                AND prt.expires_at > NOW()
                AND prt.used = 0
                LIMIT 1";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':token_hash', $token_hash);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            $_SESSION['erro_recuperacao'] = 'Código inválido ou expirado. Solicite um novo código.';
            header('Location: ../recuperar_senha.php?step=2');
            exit;
        }
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Token válido - avançar para step 3
        $_SESSION['token_valido'] = $resultado['id'];
        $_SESSION['user_recuperacao'] = $resultado['user_id'];
        $_SESSION['sucesso_recuperacao'] = 'Código validado com sucesso! Agora defina sua nova senha.';
        header('Location: ../recuperar_senha.php?step=3');
        
    } catch (PDOException $e) {
        error_log('Erro na validação do token: ' . $e->getMessage());
        $_SESSION['erro_recuperacao'] = 'Erro interno do sistema. Tente novamente.';
        header('Location: ../recuperar_senha.php?step=2');
    }
    
} else if ($step === 3) {
    // Redefinição da senha
    
    if (!isset($_SESSION['token_valido']) || !isset($_SESSION['user_recuperacao'])) {
        $_SESSION['erro_recuperacao'] = 'Sessão inválida. Inicie o processo novamente.';
        header('Location: ../recuperar_senha.php');
        exit;
    }
    
    $nova_senha = isset($_POST['nova_senha']) ? trim($_POST['nova_senha']) : '';
    $confirmar_senha = isset($_POST['confirmar_senha']) ? trim($_POST['confirmar_senha']) : '';
    
    // Validações
    $erros = [];
    
    if (empty($nova_senha)) {
        $erros[] = 'A nova senha é obrigatória.';
    } elseif (strlen($nova_senha) < 8) {
        $erros[] = 'A senha deve ter pelo menos 8 caracteres.';
    } elseif (!preg_match('/[A-Za-z]/', $nova_senha) || !preg_match('/[0-9]/', $nova_senha)) {
        $erros[] = 'A senha deve conter pelo menos uma letra e um número.';
    }
    
    if ($nova_senha !== $confirmar_senha) {
        $erros[] = 'As senhas não coincidem.';
    }
    
    if (!empty($erros)) {
        $_SESSION['erro_recuperacao'] = implode('<br>', $erros);
        header('Location: ../recuperar_senha.php?step=3');
        exit;
    }
    
    try {
        // Atualizar senha no banco
        $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        
        $sql_update = "UPDATE tb_cad_usuarios 
                       SET cad_usu_senha = :senha
                       WHERE cad_usu_id = :user_id";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bindParam(':senha', $senha_hash);
        $stmt_update->bindParam(':user_id', $_SESSION['user_recuperacao']);
        $stmt_update->execute();
        
        // Marcar token como usado
        $sql_token = "UPDATE password_reset_tokens 
                      SET used = 1 
                      WHERE id = :token_id";
        $stmt_token = $conn->prepare($sql_token);
        $stmt_token->bindParam(':token_id', $_SESSION['token_valido']);
        $stmt_token->execute();
        
        // Limpar sessão
        unset($_SESSION['token_valido']);
        unset($_SESSION['user_recuperacao']);
        unset($_SESSION['email_recuperacao']);
        
        $_SESSION['sucesso_login'] = 'Senha redefinida com sucesso! Faça login com sua nova senha.';
        header('Location: ../login_cidadao.php');
        
    } catch (PDOException $e) {
        error_log('Erro ao redefinir senha: ' . $e->getMessage());
        $_SESSION['erro_recuperacao'] = 'Erro ao redefinir senha. Tente novamente.';
        header('Location: ../recuperar_senha.php?step=3');
    }
    
} else {
    header('Location: ../recuperar_senha.php');
}

exit;
?>