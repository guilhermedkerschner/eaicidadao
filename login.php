<?php
// Incluir arquivos de configuração e funções
require_once 'lib/functions.php';
require 'lib/config.php';

// Variável para armazenar mensagens de erro
$mensagem_erro = "";

// Verificar se o usuário já está logado
if (isset($_SESSION['usersystem_logado']) && $_SESSION['usersystem_logado'] === true) {
    // Redirecionar para a dashboard
    header("Location: system/dashboard.php");
    exit;
}

// Processar o formulário de login quando submetido
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obter e sanitizar dados do formulário
    $usuario = filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_SPECIAL_CHARS);
    $senha = $_POST['password']; // A senha será verificada via password_verify()
    
    // Validar se os campos foram preenchidos
    if (empty($usuario) || empty($senha)) {
        $mensagem_erro = "Por favor, preencha todos os campos.";
    } else {
        try {
            // Buscar usuário pelo nome de usuário ou email
            $stmt = $conn->prepare("SELECT * FROM tb_usuarios_sistema WHERE usuario_login = :usuario OR usuario_email = :email LIMIT 1");
            $stmt->bindParam(':usuario', $usuario);
            $stmt->bindParam(':email', $usuario);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $usuario_dados = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verificar status do usuário
                if ($usuario_dados['usuario_status'] !== 'ativo') {
                    switch ($usuario_dados['usuario_status']) {
                        case 'bloqueado':
                            $mensagem_erro = "Sua conta está bloqueada. Entre em contato com o administrador.";
                            break;
                        case 'inativo':
                            $mensagem_erro = "Sua conta está inativa. Entre em contato com o administrador.";
                            break;
                        case 'pendente':
                            $mensagem_erro = "Sua conta está aguardando aprovação. Tente novamente mais tarde.";
                            break;
                        default:
                            $mensagem_erro = "Não foi possível acessar sua conta. Entre em contato com o administrador.";
                    }
                    
                    // Registrar tentativa de login com conta não ativa
                    registrarLog($conn, $usuario_dados['usuario_id'], 'Tentativa de login', null, 
                                "Tentativa de login em conta {$usuario_dados['usuario_status']}");
                }
                // Verificar senha
                else if (password_verify($senha, $usuario_dados['usuario_senha'])) {
                    // Reset contador de tentativas de login
                    if ($usuario_dados['usuario_tentativas_login'] > 0) {
                        $stmt_reset = $conn->prepare("UPDATE tb_usuarios_sistema SET usuario_tentativas_login = 0 WHERE usuario_id = :id");
                        $stmt_reset->bindParam(':id', $usuario_dados['usuario_id']);
                        $stmt_reset->execute();
                    }
                    
                    // Buscar informações do nível de acesso
                    $stmt_nivel = $conn->prepare("SELECT * FROM tb_niveis_acesso WHERE nivel_id = :nivel_id");
                    $stmt_nivel->bindParam(':nivel_id', $usuario_dados['usuario_nivel_id']);
                    $stmt_nivel->execute();
                    $nivel_acesso = $stmt_nivel->fetch(PDO::FETCH_ASSOC);
                    
                    // Buscar permissões do usuário
                    $stmt_perm = $conn->prepare("
                        SELECT m.modulo_id, m.modulo_nome, m.modulo_icone, 
                               p.permissao_visualizar, p.permissao_criar, 
                               p.permissao_editar, p.permissao_excluir, p.permissao_aprovar
                        FROM tb_permissoes_usuario p
                        JOIN tb_modulos m ON p.modulo_id = m.modulo_id
                        WHERE p.usuario_id = :usuario_id AND m.modulo_status = 'ativo'
                    ");
                    $stmt_perm->bindParam(':usuario_id', $usuario_dados['usuario_id']);
                    $stmt_perm->execute();
                    $permissoes = $stmt_perm->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Preparar array de módulos com permissões
                    $modulos_permitidos = [];
                    foreach ($permissoes as $perm) {
                        if ($perm['permissao_visualizar']) {
                            $modulos_permitidos[$perm['modulo_id']] = [
                                'nome' => $perm['modulo_nome'],
                                'icone' => $perm['modulo_icone'],
                                'criar' => $perm['permissao_criar'],
                                'editar' => $perm['permissao_editar'],
                                'excluir' => $perm['permissao_excluir'],
                                'aprovar' => $perm['permissao_aprovar']
                            ];
                        }
                    }
                    
                    // Se for administrador, adicionar todos os módulos
                    if ($nivel_acesso['nivel_nome'] === 'Administrador' || 
                        json_decode($nivel_acesso['nivel_permissoes'], true)['todos_modulos'] === true) {
                        
                        $stmt_all_modules = $conn->prepare("SELECT * FROM tb_modulos WHERE modulo_status = 'ativo'");
                        $stmt_all_modules->execute();
                        $all_modules = $stmt_all_modules->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach ($all_modules as $module) {
                            if (!isset($modulos_permitidos[$module['modulo_id']])) {
                                $modulos_permitidos[$module['modulo_id']] = [
                                    'nome' => $module['modulo_nome'],
                                    'icone' => $module['modulo_icone'],
                                    'criar' => true,
                                    'editar' => true,
                                    'excluir' => true,
                                    'aprovar' => true
                                ];
                            }
                        }
                    }
                    
                    // Registrar a sessão do usuário
                    $token_session = bin2hex(random_bytes(32)); // Gerar token único
                    $ip = getClientIP();
                    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
                    $data_expiracao = date('Y-m-d H:i:s', strtotime('+8 hours')); // 8 horas de validade
                    
                    $stmt_sessao = $conn->prepare("
                        INSERT INTO tb_sessoes_usuario 
                        (usuario_id, sessao_token, sessao_ip, sessao_user_agent, sessao_data_expiracao) 
                        VALUES 
                        (:usuario_id, :token, :ip, :user_agent, :expiracao)
                    ");
                    $stmt_sessao->bindParam(':usuario_id', $usuario_dados['usuario_id']);
                    $stmt_sessao->bindParam(':token', $token_session);
                    $stmt_sessao->bindParam(':ip', $ip);
                    $stmt_sessao->bindParam(':user_agent', $user_agent);
                    $stmt_sessao->bindParam(':expiracao', $data_expiracao);
                    $stmt_sessao->execute();
                    $sessao_id = $conn->lastInsertId();
                    
                    // Registrar o acesso no log
                    registrarLog($conn, $usuario_dados['usuario_id'], 'Login', null, 'Login bem-sucedido');
                    
                    // Atualizar data do último acesso
                    $stmt_update = $conn->prepare("
                        UPDATE tb_usuarios_sistema 
                        SET usuario_ultimo_acesso = NOW(), usuario_ultimo_ip = :ip 
                        WHERE usuario_id = :usuario_id
                    ");
                    $stmt_update->bindParam(':ip', $ip);
                    $stmt_update->bindParam(':usuario_id', $usuario_dados['usuario_id']);
                    $stmt_update->execute();
                    
                    // Definir variáveis de sessão
                    $_SESSION['usersystem_id'] = $usuario_dados['usuario_id'];
                    $_SESSION['usersystem_nome'] = $usuario_dados['usuario_nome'];
                    $_SESSION['usersystem_email'] = $usuario_dados['usuario_email'];
                    $_SESSION['usersystem_nivel'] = $nivel_acesso['nivel_nome'];
                    $_SESSION['usersystem_cargo'] = $usuario_dados['usuario_cargo'];
                    $_SESSION['usersystem_departamento'] = $usuario_dados['usuario_departamento'];
                    $_SESSION['usersystem_modulos'] = $modulos_permitidos;
                    $_SESSION['usersystem_sessao_id'] = $sessao_id;
                    $_SESSION['usersystem_sessao_token'] = $token_session;
                    $_SESSION['usersystem_logado'] = true;
                    $_SESSION['usersystem_expiracao'] = $data_expiracao;
                    
                    // Redirecionar para o dashboard
                    header("Location: system/dashboard.php");
                    exit;
                } else {
                    // Incrementar contador de tentativas de login
                    $tentativas = $usuario_dados['usuario_tentativas_login'] + 1;
                    $stmt_tentativas = $conn->prepare("UPDATE tb_usuarios_sistema SET usuario_tentativas_login = :tentativas WHERE usuario_id = :id");
                    $stmt_tentativas->bindParam(':tentativas', $tentativas);
                    $stmt_tentativas->bindParam(':id', $usuario_dados['usuario_id']);
                    $stmt_tentativas->execute();
                    
                    // Bloquear conta após 5 tentativas
                    if ($tentativas >= 5) {
                        $stmt_block = $conn->prepare("UPDATE tb_usuarios_sistema SET usuario_status = 'bloqueado' WHERE usuario_id = :id");
                        $stmt_block->bindParam(':id', $usuario_dados['usuario_id']);
                        $stmt_block->execute();
                        
                        registrarLog($conn, $usuario_dados['usuario_id'], 'Bloqueio de conta', null, 
                                   'Conta bloqueada após 5 tentativas de login mal-sucedidas');
                        
                        $mensagem_erro = "Sua conta foi bloqueada após múltiplas tentativas de login mal-sucedidas. Entre em contato com o administrador.";
                    } else {
                        registrarLog($conn, $usuario_dados['usuario_id'], 'Tentativa de login', null, 
                                   "Senha incorreta (tentativa {$tentativas} de 5)");
                        
                        $mensagem_erro = "Credenciais inválidas. Por favor, tente novamente.";
                    }
                }
            } else {
                // Usuário não encontrado
                $mensagem_erro = "Credenciais inválidas. Por favor, tente novamente.";
                
                // Registrar tentativa de login com usuário não existente
                registrarLog($conn, null, 'Tentativa de login', null, 
                           "Tentativa de login com usuário não encontrado: {$usuario}");
            }
        } catch (PDOException $e) {
            // Erro de banco de dados
            registrarLog($conn, null, 'Erro no sistema', null, 
                       "Erro ao processar login: " . $e->getMessage());
            
            $mensagem_erro = "Ocorreu um erro ao processar sua solicitação. Por favor, tente novamente mais tarde.";
            
            // Comentar a linha abaixo em ambiente de produção
            // $mensagem_erro .= "<br>Erro: " . $e->getMessage();
        }
    }
}

// Função para obter o IP do cliente
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

// Função para registrar log no sistema
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
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Eai Cidadão!</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/login-restrict.css">
</head>

<body>
    <!-- Botão para voltar -->
    <a href="index.php" class="back-link">
        <i class="fas fa-arrow-left"></i>
        Voltar
    </a>

    <div class="container">
        <div class="header-container">
            <div class="municipality-logo">
                <!-- Substitua pelo caminho da sua logo -->
                <img src="./img/logo_municipio.png" alt="Logo do Município">
            </div>
            <div class="title-container">
                <h1>Eai Cidadão!</h1>
                <h2 class="municipality-name">Município de Santa Izabel do Oeste</h2>
            </div>
        </div>

        <div class="divider"></div>

        <h3 style="margin-bottom: 20px; color: #0d47a1;">Área Restrita</h3>
        <!-- Exibir mensagem de erro, se houver -->
        <?php if (!empty($mensagem_erro)): ?>
        <div class="alert-error">
            <?php echo $mensagem_erro; ?>
        </div>
        <?php endif; ?>

        <form class="login-form2" method="post">
            <div class="form-group">
                <label for="username">Nome de usuário ou E-mail</label>
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" id="username" name="usuario" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Senha</label>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" class="form-control" required>
                    <button type="button" id="togglePassword" class="password-toggle">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="forgot-password">
                <a href="./login/recuperar_senha_usuario.php">Esqueceu sua senha?</a>
            </div>

            <button type="submit" class="btn-login">Entrar</button>
        </form>
    </div>

    <!-- JavaScript para funcionalidade de mostrar/ocultar senha -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');

        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);

            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    });
    </script>
</body>

</html>