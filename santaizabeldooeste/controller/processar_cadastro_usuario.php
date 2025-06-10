<?php
// Inicia a sessão
session_start();

// Incluir arquivo de conexão com o banco de dados
require_once '../lib/config.php';

// Função para processar upload da foto
function processarUploadFoto($arquivo, $cpf) {
    if (!isset($arquivo) || !isset($arquivo['tmp_name']) || empty($arquivo['tmp_name'])) {
        return null;
    }
    
    // Verificar se houve erro no upload
    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Erro no upload da foto: " . getUploadErrorMessage($arquivo['error']));
    }
    
    // Verificar tipo de arquivo
    $tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $tipoArquivo = finfo_file($finfo, $arquivo['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($tipoArquivo, $tiposPermitidos)) {
        throw new Exception("Tipo de arquivo inválido. Apenas JPG, JPEG e PNG são aceitos.");
    }
    
    // Verificar tamanho (2MB)
    if ($arquivo['size'] > 5 * 1024 * 1024) {
        throw new Exception("A foto deve ter no máximo 5MB.");
    }
    
    // Verificar se é uma imagem válida
    $imagemInfo = getimagesize($arquivo['tmp_name']);
    if ($imagemInfo === false) {
        throw new Exception("Arquivo de imagem inválido.");
    }
    
    // Obter extensão baseada no tipo MIME
    $extensoes = [
        'image/jpeg' => 'jpg',
        'image/jpg' => 'jpg',
        'image/png' => 'png'
    ];
    $extensao = $extensoes[$tipoArquivo];
    
    // Gerar nome único para o arquivo
    $nomeArquivo = "foto_" . preg_replace('/[^0-9]/', '', $cpf) . "_" . date('YmdHis') . "." . $extensao;
    
    // Definir diretório de upload
    $diretorioUpload = "../uploads/fotos_usuarios/";
    
    // Criar diretório se não existir
    if (!is_dir($diretorioUpload)) {
        if (!mkdir($diretorioUpload, 0755, true)) {
            throw new Exception("Erro ao criar diretório de upload.");
        }
    }
    
    $caminhoCompleto = $diretorioUpload . $nomeArquivo;
    
    // Redimensionar e otimizar a imagem
    if (redimensionarImagem($arquivo['tmp_name'], $caminhoCompleto, $tipoArquivo, 400, 400)) {
        return $nomeArquivo;
    } else {
        throw new Exception("Erro ao processar a imagem.");
    }
}

// Função para redimensionar e otimizar a imagem
function redimensionarImagem($origem, $destino, $tipoMime, $larguraMax = 400, $alturaMax = 400, $qualidade = 85) {
    // Criar imagem a partir do arquivo original
    switch ($tipoMime) {
        case 'image/jpeg':
        case 'image/jpg':
            $imagemOriginal = imagecreatefromjpeg($origem);
            break;
        case 'image/png':
            $imagemOriginal = imagecreatefrompng($origem);
            break;
        default:
            return false;
    }
    
    if (!$imagemOriginal) {
        return false;
    }
    
    // Obter dimensões originais
    $larguraOriginal = imagesx($imagemOriginal);
    $alturaOriginal = imagesy($imagemOriginal);
    
    // Calcular novas dimensões mantendo proporção
    $ratio = min($larguraMax / $larguraOriginal, $alturaMax / $alturaOriginal);
    $novaLargura = round($larguraOriginal * $ratio);
    $novaAltura = round($alturaOriginal * $ratio);
    
    // Criar nova imagem
    $novaImagem = imagecreatetruecolor($novaLargura, $novaAltura);
    
    // Para PNG, preservar transparência
    if ($tipoMime === 'image/png') {
        imagealphablending($novaImagem, false);
        imagesavealpha($novaImagem, true);
        $transparente = imagecolorallocatealpha($novaImagem, 255, 255, 255, 127);
        imagefill($novaImagem, 0, 0, $transparente);
    }
    
    // Redimensionar
    imagecopyresampled(
        $novaImagem, $imagemOriginal,
        0, 0, 0, 0,
        $novaLargura, $novaAltura,
        $larguraOriginal, $alturaOriginal
    );
    
    // Salvar imagem redimensionada
    $resultado = false;
    switch ($tipoMime) {
        case 'image/jpeg':
        case 'image/jpg':
            $resultado = imagejpeg($novaImagem, $destino, $qualidade);
            break;
        case 'image/png':
            // PNG usa compressão de 0-9, converter qualidade de 0-100 para 0-9
            $compressaoPng = round((100 - $qualidade) / 10);
            $resultado = imagepng($novaImagem, $destino, $compressaoPng);
            break;
    }
    
    // Limpar memória
    imagedestroy($imagemOriginal);
    imagedestroy($novaImagem);
    
    return $resultado;
}

// Função para obter mensagem de erro de upload
function getUploadErrorMessage($codigo) {
    switch ($codigo) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return "Arquivo muito grande";
        case UPLOAD_ERR_PARTIAL:
            return "Upload incompleto";
        case UPLOAD_ERR_NO_FILE:
            return "Nenhum arquivo enviado";
        case UPLOAD_ERR_NO_TMP_DIR:
            return "Diretório temporário não encontrado";
        case UPLOAD_ERR_CANT_WRITE:
            return "Falha ao escrever arquivo";
        case UPLOAD_ERR_EXTENSION:
            return "Upload bloqueado por extensão";
        default:
            return "Erro desconhecido";
    }
}

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
    
    // Inicializar variável da foto
    $foto_usuario = null;
    
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
    
    // Processar upload da foto se enviada
    if (isset($_FILES['foto_usuario']) && $_FILES['foto_usuario']['error'] !== UPLOAD_ERR_NO_FILE) {
        try {
            $foto_usuario = processarUploadFoto($_FILES['foto_usuario'], $cpf);
        } catch (Exception $e) {
            $_SESSION['erro_cadastro'] = $e->getMessage();
            header("Location: ../cad_user.php");
            exit;
        }
    }
    
    // Validar se a foto foi enviada (campo obrigatório)
    if (empty($foto_usuario)) {
        $_SESSION['erro_cadastro'] = "É obrigatório enviar uma foto para o cadastro.";
        header("Location: ../cad_user.php");
        exit;
    }
    
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
                // Se houve erro e uma foto foi enviada, remover o arquivo
                if (!empty($foto_usuario)) {
                    $caminhoFoto = "../uploads/fotos_usuarios/" . $foto_usuario;
                    if (file_exists($caminhoFoto)) {
                        unlink($caminhoFoto);
                    }
                }
                $_SESSION['erro_cadastro'] = "Este CPF já está cadastrado no sistema.";
                header("Location: ../cad_user.php");
                exit;
            }
            
            // Verificar se o e-mail já está cadastrado
            $stmt = $conn->prepare("SELECT cad_usu_id FROM tb_cad_usuarios WHERE cad_usu_email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                // Se houve erro e uma foto foi enviada, remover o arquivo
                if (!empty($foto_usuario)) {
                    $caminhoFoto = "../uploads/fotos_usuarios/" . $foto_usuario;
                    if (file_exists($caminhoFoto)) {
                        unlink($caminhoFoto);
                    }
                }
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
                    cad_usu_aceite_termos, cad_usu_receber_notificacoes, cad_usu_foto, cad_usu_status, cad_usu_data_cad
                ) VALUES (
                    ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, 'ativo', ?
                )
            ");
            
            $stmt->execute([
                $nome, $cpf, $data_nascimento, $telefone, $email, $senha_hash,
                $cep, $endereco, $numero, $complemento, $bairro, $cidade, $uf,
                $termos, $newsletter, $foto_usuario, $data_cadastro
            ]);
            
            // Enviar email de boas-vindas
            try {
                require_once '../lib/EmailService.php';
                $emailService = new EmailService();
                
                // Tentar enviar email de confirmação
                $emailEnviado = $emailService->enviarConfirmacaoCadastro($email, $nome);
                
                if ($emailEnviado) {
                    error_log("Email de boas-vindas enviado para: " . $email);
                } else {
                    error_log("Falha ao enviar email de boas-vindas para: " . $email);
                }
            } catch (Exception $e) {
                // Não interromper o processo se o email falhar
                error_log("Erro ao enviar email de boas-vindas: " . $e->getMessage());
            }
            
            // Limpar dados da sessão
            unset($_SESSION['dados_cadastro']);
            
            // Definir mensagem de sucesso
            $_SESSION['mensagem_sucesso'] = "Cadastro realizado com sucesso! Verifique seu e-mail para mais informações.";
            
            // Redirecionar para a página de sucesso
            header("Location: ../sucess.php");
            exit;
            
        } catch (PDOException $e) {
            // Se houve erro no banco e uma foto foi enviada, remover o arquivo
            if (!empty($foto_usuario)) {
                $caminhoFoto = "../uploads/fotos_usuarios/" . $foto_usuario;
                if (file_exists($caminhoFoto)) {
                    unlink($caminhoFoto);
                }
            }
            
            // Erro no banco de dados
            $_SESSION['erro_cadastro'] = "Erro ao realizar cadastro. Por favor, tente novamente mais tarde.";
            
            // Para debug (remover em produção)
            // $_SESSION['erro_cadastro'] = "Erro: " . $e->getMessage();
            
            header("Location: ../cad_user.php");
            exit;
        }
    } else {
        // Se houver erro, armazenar mensagem e redirecionar de volta para o formulário
        // Se houve erro e uma foto foi enviada, remover o arquivo
        if (!empty($foto_usuario)) {
            $caminhoFoto = "../uploads/fotos_usuarios/" . $foto_usuario;
            if (file_exists($caminhoFoto)) {
                unlink($caminhoFoto);
            }
        }
        
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