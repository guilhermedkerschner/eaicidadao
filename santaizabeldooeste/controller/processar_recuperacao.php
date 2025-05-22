<?php
// Inicia a sessão
session_start();

// Função para gerar tokens aleatórios
function gerarToken($tamanho = 6) {
    $token = "";
    for ($i = 0; $i < $tamanho; $i++) {
        $token .= rand(0, 9);
    }
    return $token;
}

// Função para enviar e-mail (simulação)
function enviarEmailRecuperacao($email, $token, $nome) {
    // Em um ambiente real, aqui utilizaríamos uma biblioteca como PHPMailer
    // Para este exemplo, vamos apenas simular o envio
    
    // O conteúdo do e-mail seria algo como:
    $assunto = "Recuperação de Senha - Eai Cidadão!";
    $mensagem = "Olá {$nome},\n\n";
    $mensagem .= "Recebemos uma solicitação para redefinir sua senha no sistema Eai Cidadão!.\n\n";
    $mensagem .= "Seu código de verificação é: {$token}\n\n";
    $mensagem .= "Este código é válido por 5 minutos. Se você não solicitou esta redefinição, por favor, ignore este e-mail.\n\n";
    $mensagem .= "Atenciosamente,\nEquipe Eai Cidadão!\nPrefeitura Municipal de Santa Izabel do Oeste";
    
    // Exibir mensagem de debug (remover em produção)
    //echo "E-mail enviado para {$email} com o token {$token}";
    
    // Em um ambiente real, enviaríamos o e-mail aqui
    // mail($email, $assunto, $mensagem);
    
    // Para fins de demonstração, consideramos que o e-mail foi enviado com sucesso
    return true;
}

// Verificar se está solicitando reenvio de token
if (isset($_GET['resend']) && $_GET['resend'] == 1) {
    if (isset($_SESSION['email_recuperacao']) && isset($_SESSION['nome_recuperacao'])) {
        // Gerar novo token
        $token = gerarToken();
        
        // Salvar o token na sessão
        $_SESSION['token_recuperacao'] = $token;
        $_SESSION['token_timestamp'] = time(); // Salva o timestamp atual
        
        // Enviar o email com o novo token
        enviarEmailRecuperacao($_SESSION['email_recuperacao'], $token, $_SESSION['nome_recuperacao']);
        
        // Redirecionar de volta para a página de recuperação
        $_SESSION['sucesso_recuperacao'] = "Um novo código foi enviado para seu e-mail.";
        header("Location: recuperar_senha_usuario.php");
        exit;
    } else {
        // Se não houver dados de recuperação na sessão, voltar para o passo 1
        $_SESSION['erro_recuperacao'] = "Sessão expirada. Por favor, inicie o processo novamente.";
        $_SESSION['passo_recuperacao'] = 1;
        header("Location: recuperar_senha_usuario.php");
        exit;
    }
}

// Verificar se o formulário foi enviado via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Identificar o passo atual
    $passo = isset($_POST['passo']) ? (int)$_POST['passo'] : 1;
    
    // Processar o passo 1: Identificação do usuário
    if ($passo == 1) {
        $identificacao = trim($_POST['identificacao']);
        
        // Verificar se o campo está vazio
        if (empty($identificacao)) {
            $_SESSION['erro_recuperacao'] = "Por favor, informe seu CPF ou e-mail.";
            header("Location: recuperar_senha_usuario.php");
            exit;
        }
        
        try {
            // Conectar ao banco de dados
            require_once "../config/conexao.php";
            
            // Verificar se é um e-mail ou CPF
            if (filter_var($identificacao, FILTER_VALIDATE_EMAIL)) {
                // É um e-mail
                $stmt = $conn->prepare("SELECT id, nome, email FROM usuarios WHERE email = :identificacao AND status = 'ativo'");
            } else {
                // Considerar como CPF (removendo caracteres não numéricos)
                $cpf = preg_replace('/[^0-9]/', '', $identificacao);
                $stmt = $conn->prepare("SELECT id, nome, email FROM usuarios WHERE cpf = :identificacao AND status = 'ativo'");
            }
            
            $stmt->bindParam(':identificacao', $identificacao);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                // Usuário encontrado
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Gerar token
                $token = gerarToken();
                
                // Salvar o token e dados do usuário na sessão
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['nome_recuperacao'] = $usuario['nome'];
                $_SESSION['email_recuperacao'] = $usuario['email'];
                $_SESSION['token_recuperacao'] = $token;
                $_SESSION['token_timestamp'] = time(); // Salva o timestamp atual
                
                // Enviar e-mail com o token
                if (enviarEmailRecuperacao($usuario['email'], $token, $usuario['nome'])) {
                    // Avançar para o próximo passo
                    $_SESSION['passo_recuperacao'] = 2;
                    header("Location: recuperar_senha_usuario.php");
                    exit;
                } else {
                    $_SESSION['erro_recuperacao'] = "Erro ao enviar e-mail de recuperação. Por favor, tente novamente.";
                    header("Location: recuperar_senha_usuario.php");
                    exit;
                }
            } else {
                // Usuário não encontrado
                $_SESSION['erro_recuperacao'] = "Não encontramos um usuário ativo com os dados informados.";
                header("Location: recuperar_senha_usuario.php");
                exit;
            }
            
        } catch (PDOException $e) {
            $_SESSION['erro_recuperacao'] = "Erro ao processar sua solicitação. Por favor, tente novamente mais tarde.";
            // Em ambiente de desenvolvimento:
            // $_SESSION['erro_recuperacao'] = "Erro: " . $e->getMessage();
            header("Location: recuperar_senha_usuario.php");
            exit;
        }
    }
    
    // Processar o passo 2: Verificação do código
    else if ($passo == 2) {
        // Verificar se existem dados de recuperação na sessão
        if (!isset($_SESSION['token_recuperacao']) || !isset($_SESSION['token_timestamp'])) {
            $_SESSION['erro_recuperacao'] = "Sessão expirada. Por favor, inicie o processo novamente.";
            $_SESSION['passo_recuperacao'] = 1;
            header("Location: recuperar_senha_usuario.php");
            exit;
        }
        
        // Verificar se o token expirou (5 minutos = 300 segundos)
        $token_age = time() - $_SESSION['token_timestamp'];
        if ($token_age > 300) {
            $_SESSION['erro_recuperacao'] = "O código de verificação expirou. Por favor, solicite um novo código.";
            header("Location: recuperar_senha_usuario.php");
            exit;
        }
        
        // Obter o token digitado pelo usuário
        $token_array = $_POST['token'] ?? [];
        $token_digitado = implode('', $token_array);
        
        // Verificar se o token está correto
        if ($token_digitado === $_SESSION['token_recuperacao']) {
            // Token válido, avançar para o próximo passo
            $_SESSION['passo_recuperacao'] = 3;
            header("Location: recuperar_senha_usuario.php");
            exit;
        } else {
            // Token inválido
            $_SESSION['erro_recuperacao'] = "Código de verificação incorreto. Por favor, tente novamente.";
            header("Location: recuperar_senha_usuario.php");
            exit;
        }
    }
    
    // Processar o passo 3: Definição da nova senha
    else if ($passo == 3) {
        // Verificar se existem dados de recuperação na sessão
        if (!isset($_SESSION['usuario_id'])) {
            $_SESSION['erro_recuperacao'] = "Sessão expirada. Por favor, inicie o processo novamente.";
            $_SESSION['passo_recuperacao'] = 1;
            header("Location: recuperar_senha_usuario.php");
            exit;
        }
        
        // Obter as senhas
        $nova_senha = $_POST['nova_senha'] ?? '';
        $confirma_senha = $_POST['confirma_senha'] ?? '';
        
        // Validar as senhas
        $erros = [];
        
        if (empty($nova_senha)) {
            $erros[] = "A nova senha é obrigatória.";
        } else if (strlen($nova_senha) < 8) {
            $erros[] = "A senha deve ter pelo menos 8 caracteres.";
        } else if (!preg_match('/[A-Za-z]/', $nova_senha) || !preg_match('/[0-9]/', $nova_senha)) {
            $erros[] = "A senha deve conter pelo menos uma letra e um número.";
        }
        
        if ($nova_senha !== $confirma_senha) {
            $erros[] = "As senhas informadas não coincidem.";
        }
        
        // Se houver erros, voltar para o formulário
        if (count($erros) > 0) {
            $_SESSION['erro_recuperacao'] = implode("<br>", $erros);
            header("Location: recuperar_senha_usuario.php");
            exit;
        }
        
        try {
            // Conectar ao banco de dados
            require_once "../config/conexao.php";
            
            // Hash da nova senha
            $senha_hash = password_hash($nova_senha, PASSWORD_BCRYPT);
            
            // Atualizar a senha no banco de dados
            $stmt = $conn->prepare("UPDATE usuarios SET senha = :senha, data_modificacao = NOW() WHERE id = :id");
            $stmt->bindParam(':senha', $senha_hash);
            $stmt->bindParam(':id', $_SESSION['usuario_id']);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                // Senha atualizada com sucesso
                
                // Limpar dados de recuperação da sessão
                unset($_SESSION['usuario_id']);
                unset($_SESSION['nome_recuperacao']);
                unset($_SESSION['email_recuperacao']);
                unset($_SESSION['token_recuperacao']);
                unset($_SESSION['token_timestamp']);
                unset($_SESSION['passo_recuperacao']);
                
                // Definir mensagem de sucesso
                $_SESSION['sucesso_cadastro'] = "Sua senha foi redefinida com sucesso! Você já pode fazer login com a nova senha.";
                
                // Redirecionar para a página de login
                header("Location: login_cidadao.php");
                exit;
            } else {
                // Falha ao atualizar senha
                $_SESSION['erro_recuperacao'] = "Erro ao atualizar sua senha. Por favor, tente novamente.";
                header("Location: recuperar_senha_usuario.php");
                exit;
            }
            
        } catch (PDOException $e) {
            $_SESSION['erro_recuperacao'] = "Erro ao processar sua solicitação. Por favor, tente novamente mais tarde.";
            // Em ambiente de desenvolvimento:
            // $_SESSION['erro_recuperacao'] = "Erro: " . $e->getMessage();
            header("Location: recuperar_senha_usuario.php");
            exit;
        }
    }
    
    // Se o passo não for reconhecido, redirecionar para o passo 1
    else {
        $_SESSION['passo_recuperacao'] = 1;
        header("Location: recuperar_senha_usuario.php");
        exit;
    }
} else {
    // Se alguém tentar acessar este arquivo diretamente, redirecionar para a página de recuperação
    header("Location: recuperar_senha_usuario.php");
    exit;
}
?>