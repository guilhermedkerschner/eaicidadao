<?php
// Arquivo: controller/atualizar_perfil.php
// Responsável por processar as atualizações de perfil do usuário

// Inicia a sessão
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['user_logado']) || $_SESSION['user_logado'] !== true) {
    // Se não estiver logado, redireciona para a página de login
    header("Location: ../login_cidadao.php");
    exit();
}

// Incluir arquivo de configuração com conexão ao banco de dados
require_once '../lib/config.php';

// Verificar se a requisição foi feita via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['perfil_msg'] = "Método de requisição inválido.";
    $_SESSION['perfil_tipo'] = "error";
    header("Location: ../app/usuario/useratt.php");
    exit();
}

// Função para sanitizar inputs
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Obter ID do usuário da sessão
$usuario_id = $_SESSION['user_id'];

// Determinar qual formulário foi enviado
$form_type = isset($_POST['form_type']) ? $_POST['form_type'] : '';

// Processar atualização de dados pessoais
if ($form_type === 'dados_pessoais') {
    // Obter e sanitizar dados do formulário
    $nome = strtoupper(sanitizeInput($_POST['nome'] ?? ''));
    $email = sanitizeInput($_POST['email'] ?? '');
    $contato = sanitizeInput($_POST['contato'] ?? '');
    $contato = preg_replace('/[^0-9]/', '', $contato); // Remove caracteres não numéricos
    $endereco = strtoupper(sanitizeInput($_POST['endereco'] ?? ''));
    $numero = sanitizeInput($_POST['numero'] ?? '');
    $complemento = strtoupper(sanitizeInput($_POST['complemento'] ?? ''));
    $bairro = strtoupper(sanitizeInput($_POST['bairro'] ?? ''));
    $cidade = strtoupper(sanitizeInput($_POST['cidade'] ?? ''));
    $cep = sanitizeInput($_POST['cep'] ?? '');
    $cep = preg_replace('/[^0-9]/', '', $cep); // Remove caracteres não numéricos
    $uf = strtoupper(sanitizeInput($_POST['uf'] ?? ''));

    // Validar campos obrigatórios
    if (empty($nome) || empty($email) || empty($contato) || empty($endereco) || 
        empty($numero) || empty($bairro) || empty($cidade) || empty($cep) || empty($uf)) {
        $_SESSION['perfil_msg'] = "Todos os campos obrigatórios devem ser preenchidos.";
        $_SESSION['perfil_tipo'] = "error";
        header("Location: ../app/usuario/useratt.php");
        exit();
    }

    // Validar formato de e-mail
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['perfil_msg'] = "Formato de e-mail inválido.";
        $_SESSION['perfil_tipo'] = "error";
        header("Location: ../app/usuario/useratt.php");
        exit();
    }

    try {
        // Verificar se o e-mail já está em uso (exceto pelo próprio usuário)
        $stmt = $conn->prepare("SELECT cad_usu_id FROM tb_cad_usuarios WHERE cad_usu_email = :email AND cad_usu_id != :id");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':id', $usuario_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['perfil_msg'] = "Este e-mail já está sendo usado por outro usuário.";
            $_SESSION['perfil_tipo'] = "error";
            header("Location: ../app/usuario/useratt.php");
            exit();
        }

        // Atualizar dados no banco de dados
        $sql = "UPDATE tb_cad_usuarios SET 
                cad_usu_email = :email,
                cad_usu_contato = :contato,
                cad_usu_endereco = :endereco,
                cad_usu_numero = :numero,
                cad_usu_complemento = :complemento,
                cad_usu_bairro = :bairro,
                cad_usu_cidade = :cidade,
                cad_usu_cep = :cep,
                cad_usu_estado = :uf
                WHERE cad_usu_id = :id";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':contato', $contato);
        $stmt->bindParam(':endereco', $endereco);
        $stmt->bindParam(':numero', $numero);
        $stmt->bindParam(':complemento', $complemento);
        $stmt->bindParam(':bairro', $bairro);
        $stmt->bindParam(':cidade', $cidade);
        $stmt->bindParam(':cep', $cep);
        $stmt->bindParam(':uf', $uf);
        $stmt->bindParam(':id', $usuario_id);
        
        $stmt->execute();
        
        // Atualizar dados na sessão
        $_SESSION['user_email'] = $email;
        $_SESSION['user_contato'] = $contato;
        $_SESSION['user_endereco'] = $endereco;
        $_SESSION['user_numero'] = $numero;
        $_SESSION['user_complemento'] = $complemento;
        $_SESSION['user_bairro'] = $bairro;
        $_SESSION['user_cidade'] = $cidade;
        $_SESSION['user_cep'] = $cep;
        $_SESSION['user_estado'] = $uf;
        
        // Mensagem de sucesso
        $_SESSION['perfil_msg'] = "Dados pessoais atualizados com sucesso!";
        $_SESSION['perfil_tipo'] = "success";
        header("Location: ../app/usuario/useratt.php");
        exit();
        
    } catch (PDOException $e) {
        // Registrar erro para depuração
        error_log("Erro na atualização de perfil: " . $e->getMessage());
        
        // Mensagem para o usuário
        $_SESSION['perfil_msg'] = "Erro ao atualizar dados. Por favor, tente novamente mais tarde.";
        $_SESSION['perfil_tipo'] = "error";
        header("Location: ../app/usuario/useratt.php");
        exit();
    }
}
// Processar alteração de senha
else if ($form_type === 'alterar_senha') {
    // Obter e sanitizar dados do formulário
    $senha_atual = sanitizeInput($_POST['senha_atual'] ?? '');
    $nova_senha = sanitizeInput($_POST['nova_senha'] ?? '');
    $confirma_senha = sanitizeInput($_POST['confirma_senha'] ?? '');
    
    // Validar campos
    if (empty($senha_atual) || empty($nova_senha) || empty($confirma_senha)) {
        $_SESSION['perfil_msg'] = "Todos os campos são obrigatórios para alterar a senha.";
        $_SESSION['perfil_tipo'] = "error";
        header("Location: ../app/usuario/useratt.php");
        exit();
    }
    
    // Verificar se as senhas coincidem
    if ($nova_senha !== $confirma_senha) {
        $_SESSION['perfil_msg'] = "A nova senha e a confirmação não coincidem.";
        $_SESSION['perfil_tipo'] = "error";
        header("Location: ../app/usuario/useratt.php");
        exit();
    }
    
    // Verificar complexidade da senha
    if (strlen($nova_senha) < 8 || !preg_match('/[a-zA-Z]/', $nova_senha) || !preg_match('/[0-9]/', $nova_senha)) {
        $_SESSION['perfil_msg'] = "A nova senha deve ter pelo menos 8 caracteres e conter letras e números.";
        $_SESSION['perfil_tipo'] = "error";
        header("Location: ../app/usuario/useratt.php");
        exit();
    }
    
    try {
        // Verificar se a senha atual está correta
        $stmt = $conn->prepare("SELECT cad_usu_senha FROM tb_cad_usuarios WHERE cad_usu_id = :id");
        $stmt->bindParam(':id', $usuario_id);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            $_SESSION['perfil_msg'] = "Usuário não encontrado.";
            $_SESSION['perfil_tipo'] = "error";
            header("Location: ../app/usuario/useratt.php");
            exit();
        }
        
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!password_verify($senha_atual, $usuario['cad_usu_senha'])) {
            $_SESSION['perfil_msg'] = "Senha atual incorreta.";
            $_SESSION['perfil_tipo'] = "error";
            header("Location: ../app/usuario/useratt.php");
            exit();
        }
        
        // Gerar hash da nova senha
        $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        
        // Atualizar senha no banco de dados
        $sql = "UPDATE tb_cad_usuarios SET 
                cad_usu_senha = :nova_senha
                WHERE cad_usu_id = :id";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':nova_senha', $nova_senha_hash);
        $stmt->bindParam(':id', $usuario_id);
        
        $stmt->execute();
        
        // Mensagem de sucesso
        $_SESSION['perfil_msg'] = "Senha alterada com sucesso!";
        $_SESSION['perfil_tipo'] = "success";
        header("Location: ../app/usuario/useratt.php");
        exit();
        
    } catch (PDOException $e) {
        // Registrar erro para depuração
        error_log("Erro na alteração de senha: " . $e->getMessage());
        
        // Mensagem para o usuário
        $_SESSION['perfil_msg'] = "Erro ao alterar senha. Por favor, tente novamente mais tarde.";
        $_SESSION['perfil_tipo'] = "error";
        header("Location: ../app/usuario/useratt.php");
        exit();
    }
}
// Tipo de formulário inválido
else {
    $_SESSION['perfil_msg'] = "Requisição inválida.";
    $_SESSION['perfil_tipo'] = "error";
    header("Location: ../app/usuario/useratt.php");
    exit();
}