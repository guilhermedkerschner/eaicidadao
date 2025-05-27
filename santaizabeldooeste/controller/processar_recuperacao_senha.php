<?php
/**
 * Controlador para Recuperação de Senha
 * Arquivo: controller/processar_recuperacao_senha.php
 */

session_start();

require_once '../database/conect.php';
require_once '../lib/EmailService.php';

// Função para gerar token aleatório
function gerarToken($tamanho = 6) {
    return sprintf('%0' . $tamanho . 'd', rand(0, pow(10, $tamanho) - 1));
}

// Função para sanitizar input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../recuperar_senha.php');
    exit;
}

// Verificar se há dados POST
if (!isset($_POST['email']) || empty(trim($_POST['email']))) {
    $_SESSION['erro_recuperacao'] = 'Por favor, informe seu e-mail.';
    header('Location: ../recuperar_senha.php');
    exit;
}

$email = sanitize($_POST['email']);

// Validar formato do e-mail
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['erro_recuperacao'] = 'Por favor, informe um e-mail válido.';
    header('Location: ../recuperar_senha.php');
    exit;
}

try {
    // Verificar se o e-mail existe na base de dados
    $sql = "SELECT cad_usu_id, cad_usu_nome, cad_usu_email, cad_usu_status 
            FROM tb_cad_usuarios 
            WHERE cad_usu_email = :email 
            LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        // E-mail não encontrado - por segurança, não informamos isso diretamente
        $_SESSION['sucesso_recuperacao'] = 'Se o e-mail informado estiver cadastrado, você receberá as instruções de recuperação.';
        header('Location: ../recuperar_senha.php');
        exit;
    }
    
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Verificar se a conta está ativa
    if ($usuario['cad_usu_status'] !== 'ativo') {
        $_SESSION['erro_recuperacao'] = 'Esta conta não está ativa. Entre em contato com o suporte.';
        header('Location: ../recuperar_senha.php');
        exit;
    }
    
        // Gerar token de recuperação
        $token = gerarToken(6);
        $token_hash = hash('sha256', $token);
        $expira_em = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        $criado_em = date('Y-m-d H:i:s', strtotime('+1 minutes'));
        
        // Verificar se já existe um token para este usuário
        $sql_check_token = "SELECT id FROM password_reset_tokens WHERE user_id = :user_id";
        $stmt_check = $conn->prepare($sql_check_token);
        $stmt_check->bindParam(':user_id', $usuario['cad_usu_id']);
        $stmt_check->execute();
        
       if ($stmt_check->rowCount() > 0) {
            // Atualizar token existente
            $sql_update = "UPDATE password_reset_tokens 
                           SET token_hash = :token_hash, expires_at = :expires_at, used = 0, created_at = :created_at
                           WHERE user_id = :user_id";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bindParam(':token_hash', $token_hash);
            $stmt_update->bindParam(':expires_at', $expira_em);
            $stmt_update->bindParam(':created_at', $criado_em);
            $stmt_update->bindParam(':user_id', $usuario['cad_usu_id']);
            $stmt_update->execute();
        } else {
            // Inserir novo token
            $sql_insert = "INSERT INTO password_reset_tokens (user_id, token_hash, expires_at, created_at) 
                           VALUES (:user_id, :token_hash, :expires_at, :created_at";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bindParam(':user_id', $usuario['cad_usu_id']);
            $stmt_insert->bindParam(':token_hash', $token_hash);
            $stmt_insert->bindParam(':expires_at', $expira_em);
            $stmt_update->bindParam(':created_at', $criado_em);
            $stmt_insert->execute();
        }
    
    // Enviar e-mail de recuperação
    $emailService = new EmailService();
    $emailEnviado = $emailService->enviarRecuperacaoSenha(
        $usuario['cad_usu_email'],
        $usuario['cad_usu_nome'],
        $token
    );
    
    if ($emailEnviado) {
        $_SESSION['sucesso_recuperacao'] = 'Enviamos um código de recuperação para seu e-mail. Verifique sua caixa de entrada e spam.';
        $_SESSION['email_recuperacao'] = $email;
        header('Location: ../recuperar_senha.php?step=2');
    } else {
        $_SESSION['erro_recuperacao'] = 'Erro ao enviar e-mail. Tente novamente em alguns minutos.';
        header('Location: ../recuperar_senha.php');
    }
    
} catch (PDOException $e) {
    error_log('Erro na recuperação de senha: ' . $e->getMessage());
    $_SESSION['erro_recuperacao'] = 'Erro interno do sistema. Tente novamente mais tarde.';
    header('Location: ../recuperar_senha.php');
} catch (Exception $e) {
    error_log('Erro no envio de e-mail: ' . $e->getMessage());
    $_SESSION['erro_recuperacao'] = 'Erro ao enviar e-mail. Verifique sua conexão e tente novamente.';
    header('Location: ../recuperar_senha.php');
}

exit;
?>