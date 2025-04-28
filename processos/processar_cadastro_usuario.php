<?php
// Inicia a sessão
session_start();

// Função para validar CPF
function validarCPF($cpf) {
    // Elimina CPFs inválidos conhecidos
    if ($cpf == '00000000000' || 
        $cpf == '11111111111' || 
        $cpf == '22222222222' || 
        $cpf == '33333333333' || 
        $cpf == '44444444444' || 
        $cpf == '55555555555' || 
        $cpf == '66666666666' || 
        $cpf == '77777777777' || 
        $cpf == '88888888888' || 
        $cpf == '99999999999') {
        return false;
    }
    
    // Valida 1º dígito verificador
    $soma = 0;
    for ($i = 0; $i < 9; $i++) {
        $soma += (int)$cpf[$i] * (10 - $i);
    }
    $resto = $soma % 11;
    $dv1 = ($resto < 2) ? 0 : 11 - $resto;
    if ($dv1 != (int)$cpf[9]) {
        return false;
    }
    
    // Valida 2º dígito verificador
    $soma = 0;
    for ($i = 0; $i < 10; $i++) {
        $soma += (int)$cpf[$i] * (11 - $i);
    }
    $resto = $soma % 11;
    $dv2 = ($resto < 2) ? 0 : 11 - $resto;
    if ($dv2 != (int)$cpf[10]) {
        return false;
    }
    
    return true;
}

// Verifica se o formulário foi enviado via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Recebe os dados do formulário
    $nome = trim($_POST['nome'] ?? '');
    $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf'] ?? ''); // Remove tudo que não for número
    $data_nascimento = $_POST['data_nascimento'] ?? '';
    $telefone = preg_replace('/[^0-9]/', '', $_POST['telefone'] ?? ''); // Remove tudo que não for número
    $email = trim(strtolower($_POST['email'] ?? ''));
    $confirma_email = trim(strtolower($_POST['confirma_email'] ?? ''));
    $senha = $_POST['senha'] ?? '';
    $confirma_senha = $_POST['confirma_senha'] ?? '';
    $cep = preg_replace('/[^0-9]/', '', $_POST['cep'] ?? ''); // Remove tudo que não for número
    $endereco = trim($_POST['endereco'] ?? '');
    $numero = trim($_POST['numero'] ?? '');
    $complemento = trim($_POST['complemento'] ?? '');
    $bairro = trim($_POST['bairro'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $uf = trim($_POST['uf'] ?? '');
    $newsletter = isset($_POST['newsletter']) ? 1 : 0;
    $termos = isset($_POST['termos']) ? 1 : 0;
    
    // Armazena temporariamente os dados do formulário para repreenchimento em caso de erro
    $_SESSION['dados_cadastro'] = [
        'nome' => $nome,
        'cpf' => $_POST['cpf'] ?? '',
        'data_nascimento' => $data_nascimento,
        'telefone' => $_POST['telefone'] ?? '',
        'email' => $email,
        'endereco' => $endereco,
        'numero' => $numero,
        'bairro' => $bairro,
        'cidade' => $cidade,
        'uf' => $uf,
        'cep' => $_POST['cep'] ?? ''
    ];
    
    // Validações
    $erros = [];
    
    // Verifica se os campos obrigatórios estão preenchidos
    if (empty($nome)) {
        $erros[] = "O nome completo é obrigatório.";
    }
    
    if (empty($cpf) || strlen($cpf) != 11) {
        $erros[] = "CPF inválido.";
    } else {
        // Validação de CPF (algoritmo básico)
        $cpf_validado = validarCPF($cpf);
        if (!$cpf_validado) {
            $erros[] = "CPF inválido.";
        }
    }
    
    if (empty($data_nascimento)) {
        $erros[] = "A data de nascimento é obrigatória.";
    } else {
        // Verifica se a data está no formato correto e é válida
        $data = DateTime::createFromFormat('Y-m-d', $data_nascimento);
        if (!$data || $data->format('Y-m-d') !== $data_nascimento) {
            $erros[] = "Data de nascimento inválida.";
        } else {
            // Verifica se é maior de 16 anos
            $hoje = new DateTime();
            $idade = $hoje->diff($data)->y;
            if ($idade < 16) {
                $erros[] = "É necessário ter pelo menos 16 anos para se cadastrar.";
            }
        }
    }
    
    if (empty($telefone) || strlen($telefone) < 10) {
        $erros[] = "Telefone inválido.";
    }
    
    if (empty($email)) {
        $erros[] = "E-mail é obrigatório.";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = "E-mail inválido.";
    }
    
    if ($email !== $confirma_email) {
        $erros[] = "Os e-mails informados não coincidem.";
    }
    
    if (empty($senha)) {
        $erros[] = "Senha é obrigatória.";
    } else if (strlen($senha) < 8) {
        $erros[] = "A senha deve ter pelo menos 8 caracteres.";
    } else if (!preg_match('/[A-Za-z]/', $senha) || !preg_match('/[0-9]/', $senha)) {
        $erros[] = "A senha deve conter pelo menos uma letra e um número.";
    }
    
    if ($senha !== $confirma_senha) {
        $erros[] = "As senhas informadas não coincidem.";
    }
    
    if (empty($cep) || strlen($cep) != 8) {
        $erros[] = "CEP inválido.";
    }
    
    if (empty($endereco)) {
        $erros[] = "Endereço é obrigatório.";
    }
    
    if (empty($numero)) {
        $erros[] = "Número é obrigatório.";
    }
    
    if (empty($bairro)) {
        $erros[] = "Bairro é obrigatório.";
    }
    
    if (empty($cidade)) {
        $erros[] = "Cidade é obrigatória.";
    }
    
    if (empty($uf)) {
        $erros[] = "UF é obrigatória.";
    }
    
    if (!$termos) {
        $erros[] = "Você precisa concordar com os termos de uso e política de privacidade.";
    }
    
    try {
        // Conectar ao banco de dados (ajuste o caminho conforme necessário)
        require_once '../database/conect.php';


        // Verifica se já existe um usuário com o mesmo e-mail
        //$stmt = $conn->prepare("SELECT cad_usu_id FROM tb_cad_usuarios WHERE cad_usu_email = :email");
        //$stmt->bindParam(':email', $email);
        //$stmt->execute();
        
        $sql = ("SELECT cad_usu_id FROM tb_cad_usuarios WHERE cad_usu_email = '$email'");
        $query = mysqli_query($db, $sql);
        $stmt = mysqli_num_rows($query)>0;

        if ($stmt > 0) {
            $erros[] = "E-mail já cadastrado. Por favor, use outro e-mail ou faça login.";
        }
        
        // Verifica se já existe um usuário com o mesmo CPF
        //$stmt = $conn->prepare("SELECT cad_usu_id FROM tb_cad_usuarios WHERE cad_usu_cpf = :cpf");
        //$stmt->bindParam(':cpf', $cpf);
        //$stmt->execute();
        
        $sql = ("SELECT cad_usu_id FROM tb_cad_usuarios WHERE cad_usu_cpf = '$cpf'");
        $query = mysqli_query($db, $sql);
        $stmt = mysqli_num_rows($query);

        if ($stmt > 0) {
            $erros[] = "CPF já cadastrado. Por favor, faça login ou recupere sua senha.";
        }

    } catch (PDOException $e) {
        // Erro na conexão com o banco de dados
        $erros[] = "Erro ao verificar dados. Por favor, tente novamente mais tarde.";
        // Em ambiente de desenvolvimento, pode ser útil ver o erro específico:
        // $erros[] = "Erro: " . $e->getMessage();
    }
    
    // Se há erros, redireciona de volta para o formulário
    if (count($erros) > 0) {
        $_SESSION['erro_cadastro'] = implode("<br>", $erros);
        header("Location: ../pages/cadastro_usuario.php");
        exit;
    }
    
    try {
        // Hash da senha com Bcrypt
        $senha_hash = password_hash($senha, PASSWORD_BCRYPT);
        
        // Data de criação
        $data_criacao = date('Y-m-d H:i:s');
        
        // Prepara e executa a inserção
//        $stmt = $conn->prepare("
//            INSERT INTO usuarios (
//                cad_usu_nome, cad_usu_cpf, cad_usu_data_nasc, cad_usu_contato, cad_usu_email,
//                cad_usu_senha, cad_usu_cep, cad_usu_endereco, cad_usu_numero, complemento, cad_usu_bairro,
//                cad_usu_cidade, cad_usu_estado, cad_usu_data_cad, cad_usu_status
//            ) VALUES (
//                :nome, :cpf, :data_nascimento, :telefone, :email,
//                :senha, :cep, :endereco, :numero, :complemento, :bairro,
//                :cidade, :uf, :data_criacao, 'ativo'
//            )
//        ");
        
//        $stmt->bindParam(':nome', $cad_usu_nome);
//        $stmt->bindParam(':cpf', $cad_usu_cpf);
//        $stmt->bindParam(':data_nascimento', $cad_usu_data_nasc);
//        $stmt->bindParam(':telefone', $cad_usu_contato);
//        $stmt->bindParam(':email', $cad_usu_email);
//        $stmt->bindParam(':senha', $cad_usu_senha_hash);
//        $stmt->bindParam(':cep', $cep);
//        $stmt->bindParam(':endereco', $endereco);
//        $stmt->bindParam(':numero', $numero);
//        $stmt->bindParam(':complemento', $complemento);
//        $stmt->bindParam(':bairro', $bairro);
//        $stmt->bindParam(':cidade', $cidade);
 ///       $stmt->bindParam(':uf', $uf);
   //     $stmt->bindParam(':newsletter', $newsletter);
     //   $stmt->bindParam(':data_criacao', $data_criacao);
        //$stmt-> $db->query();
        
        $sql = "INSERT INTO tb_cad_usuarios (
                cad_usu_nome, cad_usu_cpf, cad_usu_data_nasc, cad_usu_contato, cad_usu_email,
                cad_usu_senha, cad_usu_cep, cad_usu_endereco, cad_usu_numero, cad_usu_bairro,
                cad_usu_cidade, cad_usu_estado, cad_usu_data_cad, cad_usu_status
            ) VALUES (
                '$nome', '$cpf', '$data_nascimento', '$telefone', '$email',
                '$senha_hash', '$cep', '$endereco', '$numero', '$bairro',
                '$cidade', '$uf', '$data_criacao', 'ativo')";
        $query = mysqli_query($db, $sql);

        // Limpa os dados da sessão
        if (isset($_SESSION['dados_cadastro'])) {
            unset($_SESSION['dados_cadastro']);
        }
        
        // Define mensagem de sucesso e redireciona para a página de login
        $_SESSION['sucesso_cadastro'] = "Cadastro realizado com sucesso! Você já pode fazer login.";
        header("Location: ../pages/sucess.php");
        exit;
        
    } catch (PDOException $e) {
        // Erro ao inserir no banco de dados
        $_SESSION['erro_cadastro'] = "Erro ao processar o cadastro. Por favor, tente novamente mais tarde.";
        // Em ambiente de desenvolvimento, pode ser útil ver o erro específico:
        // $_SESSION['erro_cadastro'] = "Erro: " . $e->getMessage();
        header("Location: ../pages/cadastro_usuario.php");
        exit;
    }
} else {
    // Se alguém tentar acessar este arquivo diretamente, redireciona para a página de cadastro
    header("Location: ../pages/cadastro_usuario.php");
    exit;
}