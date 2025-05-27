<?php
// Arquivo: processar_login.php
// Validação de login para o app "Eai Cidadão!"

// Inicia a sessão
session_start();


// Função para limpar e validar input
function limparInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Verifica se o formulário foi enviado via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Obtém e limpa os dados do formulário
    $username = limparInput($_POST['username']);
    $password = limparInput($_POST['password']);
    
    // Valida se os campos foram preenchidos
    if (empty($username) || empty($password)) {
        $_SESSION['erro_login'] = "Todos os campos são obrigatórios.";
        header("Location: login.php");
        exit();
    }
    
    try {
        // Conecta ao banco de dados usando PDO
        $conn = new PDO("mysql:host=$servidor;dbname=$banco", $usuario_bd, $senha_bd);
        // Configura o PDO para lançar exceções em caso de erros
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Prepara a consulta SQL (usando prepared statements para evitar injeção SQL)
        $stmt = $conn->prepare("SELECT usuario_id, usuario_nome, usuario_login, usuario_senha, usuario_nivel_id FROM tb_usuarios_sistema WHERE usuario_login = :username OR usuario_email = :email");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $username); // Permite login com email ou username
        $stmt->execute();
        
        // Verifica se encontrou o usuário
        if ($stmt->rowCount() == 1) {
            // Obtém os dados do usuário
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verifica se a senha está correta (usando password_verify para senhas hasheadas)
            if (password_verify($password, $usuario['usuario_senha'])) {
                // Credenciais corretas - inicia a sessão
                
                // Armazena informações do usuário na sessão (evite armazenar a senha)
                $_SESSION['usersystem_id'] = $usuario['usuario_id'];
                $_SESSION['usersystem_nome'] = $usuario['usuario_nome'];
                $_SESSION['usersystem_nivel_acesso'] = $usuario['usuario_nivel_id'];
                $_SESSION['usersystem_logado'] = true;
                
                // Registra o horário do login
                $_SESSION['ultimo_acesso'] = time();
                
                // Opcional: Registra o login no banco de dados
                $ip = $_SERVER['REMOTE_ADDR'];
                $stmt_log = $conn->prepare("INSERT INTO logs_acesso (usuario_id, data_acesso, ip) VALUES (:usuario_id, NOW(), :ip)");
                $stmt_log->bindParam(':usuario_id', $usuario['id']);
                $stmt_log->bindParam(':ip', $ip);
                $stmt_log->execute();
                
                // Redireciona para a página adequada conforme o nível de acesso
                switch ($usuario['nivel_acesso']) {
                    case 'admin':
                        header("Location: admin/dashboard.php");
                        break;
                    case 'moderador':
                        header("Location: moderador/painel.php");
                        break;
                    default:
                        header("Location: usuario/inicio.php");
                        break;
                }
                exit();
            } else {
                // Senha incorreta
                $_SESSION['erro_login'] = "Usuário ou senha incorretos.";
                header("Location: login.php");
                exit();
            }
        } else {
            // Usuário não encontrado
            $_SESSION['erro_login'] = "Usuário ou senha incorretos.";
            header("Location: login.php");
            exit();
        }
    } catch(PDOException $e) {
        // Erro na conexão ou consulta
        $_SESSION['erro_login'] = "Erro no sistema. Por favor, tente novamente mais tarde.";
        // Para desenvolvimento, descomente a linha abaixo para ver o erro específico
        // $_SESSION['erro_login'] = "Erro: " . $e->getMessage();
        header("Location: login.php");
        exit();
    }
    
    // Fecha a conexão
    $conn = null;
} else {
    // Se alguém tentar acessar este arquivo diretamente sem enviar o formulário
    header("Location: login.php");
    exit();
}
?>