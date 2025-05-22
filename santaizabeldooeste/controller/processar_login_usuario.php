<?php
/**
 * Processador de Login de Usuários
 * Este arquivo processa as credenciais de login e autentica o usuário
 */

// Iniciar sessão
session_start();

// Incluir arquivo de configuração
require_once '../lib/config.php';

// Verificar se a requisição é do tipo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Redirecionar para a página de login com mensagem de erro
    $_SESSION['erro_login_usuario'] = "Método de requisição inválido.";
    header('Location: ../login_usuario.php');
    exit;
}

// Verificar se os campos necessários foram enviados
if (empty($_POST['login_email']) || empty($_POST['login_password'])) {
    $_SESSION['erro_login_usuario'] = "Por favor, preencha todos os campos.";
    header('Location: ../login_usuario.php');
    exit;
}

// Obter e sanitizar dados
$email = filter_var($_POST['login_email'], FILTER_SANITIZE_EMAIL);
$senha = $_POST['login_password'];

// Validar formato de email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['erro_login_usuario'] = "Formato de email inválido.";
    header('Location: ../login_usuario.php');
    exit;
}

try {

    // Buscar usuário pelo email
    $stmt = $conn->prepare("SELECT * FROM tb_cad_usuarios WHERE cad_usu_email = :email LIMIT 1");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    // Verificar se o usuário existe
    if ($stmt->rowCount() === 0) {
        $_SESSION['erro_login_usuario'] = "Email não existe ou está incorreto.";
        header('Location: ../login_cidadao.php');
        exit;
    }
    
    // Obter dados do usuário
    $usuario = $stmt->fetch();
    
    // Verificar senha
    if (!password_verify($senha, $usuario['cad_usu_senha'])) {
        $_SESSION['erro_login_usuario'] = "Senha incorreta.";
        header('Location: ../login_cidadao.php');
        exit;
    }
        
    // Login bem-sucedido - configurar sessão
    $_SESSION['user_id'] = $usuario['cad_usu_id'];
    $_SESSION['user_nome'] = $usuario['cad_usu_nome'];
    $_SESSION['user_cpf'] = $usuario['cad_usu_cpf'];
    $_SESSION['user_contato'] = $usuario['cad_usu_contato'];
    $_SESSION['user_email'] = $usuario['cad_usu_email'];
    $_SESSION['user_endereco'] = $usuario['cad_usu_endereco'];
    $_SESSION['user_numero'] = $usuario['cad_usu_numero'];
    $_SESSION['user_bairro'] = $usuario['cad_usu_bairro'];
    $_SESSION['user_cidade'] = $usuario['cad_usu_cidade'];
    $_SESSION['user_endereco'] = $usuario['cad_usu_endereco'];
    $_SESSION['user_complemento'] = $usuario['cad_usu_complemento'];
    $_SESSION['user_data_nasc'] = $usuario['cad_usu_data_nasc'];
    $_SESSION['user_cep'] = $usuario['cad_usu_cep'];
    $_SESSION['user_logado'] = true;
    $_SESSION['user_nivel'] = $usuario['cad_usu_nivel'] ?? 'cidadao';
    $_SESSION['user_hora_login'] = time();
    
    // Atualizar a data do último login
    $stmt = $conn->prepare("UPDATE tb_cad_usuarios SET cad_usu_ultimo_acess = NOW() WHERE cad_usu_id = :id");
    $stmt->bindParam(':id', $usuario['cad_usu_id']);
    $stmt->execute();
        
    
    // Redirecionar com base no nível de acesso
    if (isset($usuario['cad_usu_nivel'])) {
        switch ($usuario['cad_usu_nivel']) {
            case 'admin':
                header('Location: ../admin/dashboard.php');
                exit;
            case 'funcionario':
                header('Location: ../funcionario/dashboard.php');
                exit;
            default:
                header('Location: ../index.php');
                exit;
        }
    } else {
        // Redirecionamento padrão
        header('Location: ../index.php');
        exit;
    }
    
} catch (PDOException $e) {
    // Registrar erro para depuração
    error_log("Erro no login: " . $e->getMessage());
    
    // Mostrar mensagem genérica ao usuário
    $_SESSION['erro_login_usuario'] = "Ocorreu um erro ao processar a solicitação. Tente novamente mais tarde.";
    header('Location: ../login_cidadao.php');
    exit;
}