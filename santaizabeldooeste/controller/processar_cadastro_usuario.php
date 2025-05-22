<?php
// Inicia a sessão
session_start();

// Incluir arquivo de conexão com o banco de dados
require_once '../lib/config.php';

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Coletar dados do formulário com tratamento contra injeção SQL
    $nome = strtoupper(trim(filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS)));
    $cpf = trim(filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_SPECIAL_CHARS));
    $cpf = preg_replace('/[^0-9]/', '', $cpf); // Remove caracteres não numéricos
    $data_nascimento = trim(filter_input(INPUT_POST, 'data_nascimento', FILTER_SANITIZE_SPECIAL_CHARS));
    $telefone = trim(filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_SPECIAL_CHARS));
    $telefone = preg_replace('/[^0-9]/', '', $telefone); // Remove caracteres não numéricos
    $email = strtolower(trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL)));
    $confirma_email = strtolower(trim(filter_input(INPUT_POST, 'confirma_email', FILTER_SANITIZE_EMAIL)));
    $senha = trim(filter_input(INPUT_POST, 'senha', FILTER_SANITIZE_SPECIAL_CHARS));
    $confirma_senha = trim(filter_input(INPUT_POST, 'confirma_senha', FILTER_SANITIZE_SPECIAL_CHARS));
    $cep = trim(filter_input(INPUT_POST, 'cep', FILTER_SANITIZE_SPECIAL_CHARS));
    $cep = preg_replace('/[^0-9]/', '', $cep); // Remove caracteres não numéricos
    $endereco = strtoupper(trim(filter_input(INPUT_POST, 'endereco', FILTER_SANITIZE_SPECIAL_CHARS)));
    $numero = trim(filter_input(INPUT_POST, 'numero', FILTER_SANITIZE_SPECIAL_CHARS));
    $complemento = trim(filter_input(INPUT_POST, 'complemento', FILTER_SANITIZE_SPECIAL_CHARS));
    $bairro = strtoupper(trim(filter_input(INPUT_POST, 'bairro', FILTER_SANITIZE_SPECIAL_CHARS)));
    $cidade = strtoupper(trim(filter_input(INPUT_POST, 'cidade', FILTER_SANITIZE_SPECIAL_CHARS)));
    $uf = strtoupper(trim(filter_input(INPUT_POST, 'uf', FILTER_SANITIZE_SPECIAL_CHARS)));
    
    // Verificar se o checkbox de termos foi marcado
    $termos = isset($_POST['termos']) ? 1 : 0;
    // Verificar se o checkbox de newsletter foi marcado
    $newsletter = isset($_POST['newsletter']) ? 1 : 0;
    
    // Armazenar dados do formulário na sessão para redirecionamento em caso de erro
    $_SESSION['dados_cadastro'] = [
        'nome' => $nome,
        'cpf' => $cpf,
        'data_nascimento' => $data_nascimento,
        'telefone' => $telefone,
        'email' => $email,
        'endereco' => $endereco,
        'numero' => $numero,
        'bairro' => $bairro,
        'cidade' => $cidade,
        'uf' => $uf,
        'cep' => $cep
    ];
    
    // Validação de dados
    $erro = false;
    $mensagem_erro = "";
    
    // Verificar se os e-mails coincidem
    if ($email !== $confirma_email) {
        $erro = true;
        $mensagem_erro = "Os e-mails informados não coincidem.";
    }
    
    // Verificar se as senhas coincidem
    elseif ($senha !== $confirma_senha) {
        $erro = true;
        $mensagem_erro = "As senhas informadas não coincidem.";
    }
    
    // Verificar complexidade da senha (mínimo 8 caracteres, pelo menos uma letra e um número)
    elseif (strlen($senha) < 8 || !preg_match('/[a-zA-Z]/', $senha) || !preg_match('/[0-9]/', $senha)) {
        $erro = true;
        $mensagem_erro = "A senha deve ter pelo menos 8 caracteres, contendo pelo menos uma letra e um número.";
    }
    
    // Verificar validade do CPF
    elseif (!validaCPF($cpf)) {
        $erro = true;
        $mensagem_erro = "O CPF informado é inválido.";
    }
    
    // Verificar se os termos foram aceitos
    elseif ($termos != 1) {
        $erro = true;
        $mensagem_erro = "Você deve aceitar os termos de uso e a política de privacidade para continuar.";
    }
    
    // Se não houver erros, continuar com o cadastro
    if (!$erro) {
        try {
            // Verificar se o CPF já está cadastrado
            $stmt = $conn->prepare("SELECT cad_usu_id FROM tb_cad_usuarios WHERE cad_usu_cpf = ?");
            $stmt->execute([$cpf]);
            if ($stmt->rowCount() > 0) {
                $_SESSION['erro_cadastro'] = "Este CPF já está cadastrado no sistema.";
                header("Location: ../cad_user.php");
                exit;
            }
            
            // Verificar se o e-mail já está cadastrado
            $stmt = $conn->prepare("SELECT cad_usu_id FROM tb_cad_usuarios WHERE cad_usu_email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                $_SESSION['erro_cadastro'] = "Este e-mail já está cadastrado no sistema.";
                header("Location: ../cad_user.php");
                exit;
            }
            
            // Criptografar a senha
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            
            // Data e hora atual
            $data_cadastro = date('Y-m-d H:i:s');
            
            // Inserir usuário no banco de dados
            $stmt = $conn->prepare("
                INSERT INTO tb_cad_usuarios (
                    cad_usu_nome, cad_usu_cpf, cad_usu_data_nasc, cad_usu_contato, cad_usu_email, cad_usu_senha,
                    cad_usu_cep, cad_usu_endereco, cad_usu_numero, cad_usu_complemento, cad_usu_bairro, cad_usu_cidade, cad_usu_estado,
                    cad_usu_aceite_termos, cad_usu_receber_notificacoes, cad_usu_status, cad_usu_data_cad
                ) VALUES (
                    ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, 'ativo', ?
                )
            ");
            
            $stmt->execute([
                $nome, $cpf, $data_nascimento, $telefone, $email, $senha_hash,
                $cep, $endereco, $numero, $complemento, $bairro, $cidade, $uf,
                $termos, $newsletter, $data_cadastro
            ]);
            
            // Limpar dados da sessão
            unset($_SESSION['dados_cadastro']);
            
            // Definir mensagem de sucesso
            $_SESSION['mensagem_sucesso'] = "Cadastro realizado com sucesso! Faça login para continuar.";
            
            // Redirecionar para a página de login
            header("Location: ../sucess.php");
            exit;
            
        } catch (PDOException $e) {
            // Erro no banco de dados
            $_SESSION['erro_cadastro'] = "Erro ao realizar cadastro. Por favor, tente novamente mais tarde.";
            
            // Para debug (remover em produção)
            // $_SESSION['erro_cadastro'] = "Erro: " . $e->getMessage();
            
            header("Location: ../cad_user.php");
            exit;
        }
    } else {
        // Se houver erro, armazenar mensagem e redirecionar de volta para o formulário
        $_SESSION['erro_cadastro'] = $mensagem_erro;
        header("Location: ../cad_user.php");
        exit;
    }
} else {
    // Se tentar acessar diretamente este arquivo sem enviar formulário
    header("Location: ../cad_user.php");
    exit;
}

// Função para validar CPF
function validaCPF($cpf) {
    // Extrai somente os números
    $cpf = preg_replace('/[^0-9]/is', '', $cpf);
    
    // Verifica se foi informado todos os dígitos corretamente
    if (strlen($cpf) != 11) {
        return false;
    }
    
    // Verifica se foi informada uma sequência de dígitos repetidos
    if (preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }
    
    // Faz o cálculo para validar o CPF
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }
    return true;
}
?>