<?php
/*
**********************************************************************
       PROCESSA_LOGIN.PHP - VALIDAÇÃO DE LOGIN - EAI CIDADAO
**********************************************************************
*/

// Iniciando a Sessão
session_start();

// Configuração de Timezone
date_default_timezone_set("America/Sao_Paulo");

// Inclui o arquivo de conexão com o banco
require_once '../database/conect.php';

// Inicializa a resposta
$response = [
    'success' => false,
    'message' => '',
    'redirect' => ''
];

// Verifica se a requisição é do tipo POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Verifica a ação solicitada
    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        
        // Obtém os dados do formulário
        $email = filter_input(INPUT_POST, 'login_email', FILTER_SANITIZE_EMAIL);
        $senha = filter_input(INPUT_POST, 'login_password', FILTER_SANITIZE_STRING);
        
        // Valida o email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['message'] = 'Email inválido. Por favor, insira um email válido.';
            echo json_encode($response);
            exit;
        }
        
        // Valida a senha
        if (strlen($senha) < 6) {
            $response['message'] = 'Senha inválida. A senha deve ter pelo menos 6 caracteres.';
            echo json_encode($response);
            exit;
        }
        
        try {
            // Prepara consulta SQL para verificar o usuário
            $sql = "SELECT * FROM tb_cad_usuarios WHERE cad_usu_email = :email LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            
            // Verifica se o usuário existe
            if ($stmt->rowCount() > 0) {
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verifica se a senha está correta
                if (password_verify($senha, $usuario['cad_usu_senha'])) {
                    
                    // Atualiza a data do último acesso
                    $update_sql = "UPDATE tb_cad_usuarios SET cad_usu_ultimo_acess = NOW() WHERE cad_usu_email = :email";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bindParam(':email', $email, PDO::PARAM_STR);
                    $update_stmt->execute();
                    
                    // Define dados da sessão
                    $_SESSION['usuario'] = [
                        'id' => $usuario['cad_usu_id'],
                        'nome' => $usuario['cad_usu_nome'],
                        'email' => $usuario['cad_usu_email'],
                        'cidade' => $usuario['cad_usu_cidade'],
                        'estado' => $usuario['cad_usu_estado'],
                        'ultimo_acesso' => $usuario['cad_usu_ultimo_acess']
                    ];
                    
                    // Define resposta de sucesso
                    $response['success'] = true;
                    $response['message'] = 'Login realizado com sucesso!';
                    $response['redirect'] = '../painel_cidadao.php';
                    
                } else {
                    // Senha incorreta
                    $response['message'] = 'Email ou senha incorretos.';
                }
            } else {
                // Usuário não encontrado
                $response['message'] = 'Email ou senha incorretos.';
            }
        } catch (PDOException $e) {
            // Erro no banco de dados
            $response['message'] = 'Erro ao processar login. Tente novamente mais tarde.';
            error_log('Erro no processamento de login: ' . $e->getMessage());
        }
    } else {
        // Ação não reconhecida
        $response['message'] = 'Ação inválida.';
    }
} else {
    // Método HTTP não permitido
    $response['message'] = 'Método não permitido.';
}

// Retorna a resposta como JSON
header('Content-Type: application/json');
echo json_encode($response);
exit;