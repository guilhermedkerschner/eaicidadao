<?php
// Inicia a sessão
session_start();

// Define o cabeçalho para JSON
header('Content-Type: application/json');

// Função para retornar respostas JSON
function json_response($success = false, $message = '', $redirect = null) {
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($redirect) {
        $response['redirect'] = $redirect;
    }
    
    echo json_encode($response);
    exit;
}

// Verifica se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Método de requisição inválido');
}

// Verifica se os campos foram enviados
if (!isset($_POST['login_email']) || !isset($_POST['login_password'])) {
    json_response(false, 'Campos obrigatórios não fornecidos');
}

// Obtém e sanitiza os dados
$email = filter_var($_POST['login_email'], FILTER_SANITIZE_EMAIL);
$password = $_POST['login_password'];

// Valida o formato do email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(false, 'Formato de email inválido');
}

// Proteção contra tentativas em excesso
if (isset($_SESSION['login_attempts'][$email]) && $_SESSION['login_attempts'][$email]['count'] >= 5) {
    $last_attempt = $_SESSION['login_attempts'][$email]['time'];
    $block_time = 15 * 60; // 15 minutos em segundos
    
    if (time() - $last_attempt < $block_time) {
        $time_left = $block_time - (time() - $last_attempt);
        $minutes = ceil($time_left / 60);
        json_response(false, "Muitas tentativas de login. Tente novamente após $minutes minutos.");
    } else {
        // Reset das tentativas após o tempo de bloqueio
        $_SESSION['login_attempts'][$email]['count'] = 0;
    }
}


require_once '../database/conect.php';


try {
    // Prepara a consulta
    $stmt = $pdo->prepare("SELECT cad_usu_id, cad_usu_nome, cad_usu_senha, cad_usu_status FROM tb_cad_usuarios WHERE cad_usu_email = :email LIMIT 1");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    // Verifica se encontrou o usuário
    if ($stmt->rowCount() > 0) {
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verifica se a conta está ativa
        if ($usuario['cad_usu_status'] != 'ativo') {
            // Registra a tentativa
            if (!isset($_SESSION['login_attempts'][$email])) {
                $_SESSION['login_attempts'][$email] = ['count' => 0, 'time' => time()];
            }
            $_SESSION['login_attempts'][$email]['count']++;
            $_SESSION['login_attempts'][$email]['time'] = time();
            
            json_response(false, 'Sua conta não está ativa. Entre em contato com o suporte.');
        }
        
        // Verifica a senha usando password_verify
        if (password_verify($password, $usuario['cad_usu_senha'])) {
            // Sucesso no login
            
            // Reset das tentativas de login
            if (isset($_SESSION['login_attempts'][$email])) {
                unset($_SESSION['login_attempts'][$email]);
            }
            
            // Gera um token de sessão
            $session_token = bin2hex(random_bytes(32));
            
            // Salva dados na sessão
            $_SESSION['usuario_id'] = $usuario['cad_usu_id'];
            $_SESSION['usuario_nome'] = $usuario['cad_usu_nome'];
            $_SESSION['usuario_email'] = $email;
            $_SESSION['usuario_token'] = $session_token;
            $_SESSION['ultimo_acesso'] = time();
            
            // Atualiza o último acesso no banco de dados
            $stmt = $pdo->prepare("UPDATE tb_cad_usuarios SET cad_usu_ultimo_acess = NOW(), cad_usu_token_recuperacao = :token WHERE cad_usu_id = :id");
            $stmt->bindParam(':token', $session_token);
            $stmt->bindParam(':id', $usuario['cad_usu_id']);
            $stmt->execute();
            
            // Registra o login no log
            $ip = $_SERVER['REMOTE_ADDR'];
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            $stmt = $pdo->prepare("INSERT INTO log_acessos (usuario_id, data_acesso, ip, user_agent) VALUES (:usuario_id, NOW(), :ip, :user_agent)");
            $stmt->bindParam(':usuario_id', $usuario['cad_usu_id']);
            $stmt->bindParam(':ip', $ip);
            $stmt->bindParam(':user_agent', $user_agent);
            $stmt->execute();
            
            json_response(true, 'Login realizado com sucesso', './pages/perfil.php');
        } else {
            // Senha incorreta
            
            // Registra a tentativa
            if (!isset($_SESSION['login_attempts'][$email])) {
                $_SESSION['login_attempts'][$email] = ['count' => 0, 'time' => time()];
            }
            $_SESSION['login_attempts'][$email]['count']++;
            $_SESSION['login_attempts'][$email]['time'] = time();
            
            json_response(false, 'Email ou senha incorretos');
        }
    } else {
        // Usuário não encontrado
        
        // Registra a tentativa
        if (!isset($_SESSION['login_attempts'][$email])) {
            $_SESSION['login_attempts'][$email] = ['count' => 0, 'time' => time()];
        }
        $_SESSION['login_attempts'][$email]['count']++;
        $_SESSION['login_attempts'][$email]['time'] = time();
        
        json_response(false, 'Email ou senha incorretos');
    }
} catch (PDOException $e) {
    // Erro no banco de dados
    error_log('Erro de login: ' . $e->getMessage());
    json_response(false, 'Erro ao processar o login. Tente novamente mais tarde.');
}

/*
// Inicia a sessão
session_start();

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recebe os dados do formulário
    $email = $_POST['login_email'];
    $senha = $_POST['login_password'];
    
    // Valida se não está vazio
    $response = [];
    $erro = [];
    empty($_POST['login_email']) ? $erro[] = "Informe o E-mail" : "";
    empty($_POST['login_password']) ? $erro[] = "Informe a Senha" : "";

    if(count($erro) == 0){
        //Não tem erro e continua a sequência
    }else{
        $Response =[
            'status' => false,
            'mensagem' => $erro,
        ];
    }
    echo json_encode($response);

    //Conexão com banco
    require_once '../database/conect.php';
    
    // Verificação de erros na conexão
    // if ($conn->connect_error) {
    //     die("Falha na conexão: " . $conn->connect_error);
    // }
    
    // Exemplo de consulta para verificar as credenciais
    $senha_hash = password_hash($senha, PASSWORD_BCRYPT);
    echo $senha_hash;
    $sql = "SELECT cad_usu_id, cad_usu_nome FROM tb_cad_usuarios WHERE cad_usu_email = '$email' AND cad_usu_senha = '$senha_hash'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $senha_hash);
    $stmt->execute();
     $result = $stmt->get_result();
    
    // Para fins de demonstração, vamos considerar que email@teste.com com senha "123456" é válido
    if ($email === "email@teste.com" && $senha === "123456") {
        // Login bem-sucedido
        // Aqui você deve definir variáveis de sessão para o usuário logado
        $_SESSION['usuario_id'] = 1; // exemplo, substituir pelo ID real do usuário
        $_SESSION['usuario_nome'] = "Usuário Teste"; // exemplo, substituir pelo nome real
        $_SESSION['usuario_email'] = $email;
        $_SESSION['usuario_logado'] = true;
        
        // Redireciona para a página de área do cidadão
        header("Location: ../pages/perfil.php");
        exit();
    } else {
        // Login inválido
        $_SESSION['erro_login_usuario'] = "E-mail ou senha incorretos. Por favor, tente novamente.";
        header("Location: ../login_cidadao.php");
        exit();
    }
    
    // Fecha a conexão com o banco de dados
    $db->close();
} else {
    // Se o método não for POST, redireciona para a página de login
    header("Location: ../login_cidadao.php");
    exit();
}*/
?>