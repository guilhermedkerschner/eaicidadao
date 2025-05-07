<?php
/**
 * Arquivo: processar_habitacao.php
 * Descrição: Processa o formulário de cadastro para programas habitacionais
 * 
 * Parte do sistema Eai Cidadão! - Município de Santa Izabel do Oeste
 */

// Desativar exibição de erros para evitar corromper a saída JSON
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Inicia a sessão
session_start();

// Define o cabeçalho para JSON se for uma solicitação AJAX
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    header('Content-Type: application/json; charset=utf-8');
}

// Verifica se o usuário está logado
if (!isset($_SESSION['user_logado'])) {
    // Resposta para requisição AJAX
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        echo json_encode(['status' => 'error', 'message' => 'Usuário não autenticado.']);
        exit;
    }
    
    // Redirecionamento padrão
    header("Location: ../acessdenied.php");
    exit;
}

// Incluir arquivo de configuração com conexão ao banco de dados
require_once "../lib/config.php";

// Verificar se o formulário foi enviado via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Resposta para requisição AJAX
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        echo json_encode(['status' => 'error', 'message' => 'Método de requisição inválido.']);
        exit;
    }
    
    // Redirecionamento padrão
    header("Location: ../pages/socialhabitacao.php");
    exit;
}

// Função para sanitizar inputs
function sanitize($data) {
    if (is_null($data) || $data === '') {
        return null;
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Função para processar upload de arquivo
function processarUpload($arquivo, $tipo) {
    if (!isset($arquivo) || !isset($arquivo['tmp_name']) || empty($arquivo['tmp_name'])) {
        return null;
    }
    
    // Verificar tipo de arquivo
    $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
    if (!in_array($arquivo['type'], $allowed)) {
        return false;
    }
    
    // Verificar tamanho (5MB)
    if ($arquivo['size'] > 5 * 1024 * 1024) {
        return false;
    }
    
    // Criar nome único para o arquivo
    $timestamp = time();
    $hash = md5(uniqid($timestamp, true));
    $ext = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
    $novo_nome = "hab_{$tipo}_{$timestamp}_{$hash}.{$ext}";
    
    // Definir caminho para salvar
    $upload_dir = "../uploads/habitacao/";
    
    // Verificar se o diretório existe, se não, criar
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            return false;
        }
    }
    
    $caminho_completo = $upload_dir . $novo_nome;
    
    // Mover o arquivo
    if (move_uploaded_file($arquivo['tmp_name'], $caminho_completo)) {
        return $novo_nome;
    }
    
    return false;
}

try {
    // Verificar conexão com o banco
    if (!isset($conn) || !$conn) {
        throw new Exception("Erro na conexão com o banco de dados.");
    }
    
    // Capturar dados do formulário - RESPONSÁVEL FAMILIAR
    $nome = isset($_POST['nome']) ? strtoupper(sanitize($_POST['nome'])) : null;
    $cpf = isset($_POST['cpf']) ? sanitize($_POST['cpf']) : null;
    $cpf = $cpf ? preg_replace('/[^0-9]/', '', $cpf) : null;
    $nacionalidade = isset($_POST['nacionalidade']) ? strtoupper(sanitize($_POST['nacionalidade'])) : null;
    $nome_social_opcao = isset($_POST['nome_social_opcao']) ? sanitize($_POST['nome_social_opcao']) : null;
    $nome_social = ($nome_social_opcao === 'SIM' && isset($_POST['nome_social'])) ? strtoupper(sanitize($_POST['nome_social'])) : null;
    $genero = isset($_POST['genero']) ? strtoupper(sanitize($_POST['genero'])) : null;
    $data_nascimento = isset($_POST['data_nascimento']) ? sanitize($_POST['data_nascimento']) : null;
    $raca = isset($_POST['raca']) ? strtoupper(sanitize($_POST['raca'])) : null;
    $cad_unico = isset($_POST['cad_unico']) ? sanitize($_POST['cad_unico']) : null;
    $nis = isset($_POST['nis']) ? sanitize($_POST['nis']) : null;
    $escolaridade = isset($_POST['escolaridade']) ? strtoupper(sanitize($_POST['escolaridade'])) : null;
    $estado_civil = isset($_POST['estado_civil']) ? strtoupper(sanitize($_POST['estado_civil'])) : null;
    $deficiencia = isset($_POST['deficiencia']) ? strtoupper(sanitize($_POST['deficiencia'])) : null;
    $deficiencia_fisica_detalhe = ($deficiencia === 'FISICA' && isset($_POST['deficiencia_fisica_detalhe'])) ? 
                                 strtoupper(sanitize($_POST['deficiencia_fisica_detalhe'])) : null;
    
    // CÔNJUGE (Se aplicável)
    $has_conjuge = ($estado_civil === 'CASADO(A)' || $estado_civil === 'UNIÃO ESTÁVEL/AMASIADO(A)');
    $conjuge_nome = $has_conjuge && isset($_POST['conjuge_nome']) ? strtoupper(sanitize($_POST['conjuge_nome'])) : null;
    $conjuge_cpf = $has_conjuge && isset($_POST['conjuge_cpf']) ? sanitize($_POST['conjuge_cpf']) : null;
    $conjuge_cpf = $conjuge_cpf ? preg_replace('/[^0-9]/', '', $conjuge_cpf) : null;
    $conjuge_rg = $has_conjuge && isset($_POST['conjuge_rg']) ? strtoupper(sanitize($_POST['conjuge_rg'])) : null;
    $conjuge_data_nascimento = $has_conjuge && isset($_POST['conjuge_data_nascimento']) ? sanitize($_POST['conjuge_data_nascimento']) : null;
    $conjuge_renda = $has_conjuge && isset($_POST['conjuge_renda']) ? sanitize($_POST['conjuge_renda']) : null;
    
    // COMPOSIÇÃO FAMILIAR
    $num_dependentes = isset($_POST['num_dependentes']) ? intval(sanitize($_POST['num_dependentes'])) : 0;
    $dependentes = [];
    for ($i = 1; $i <= $num_dependentes; $i++) {
        if (isset($_POST["dependente_nome_{$i}"])) {
            $dependentes[] = [
                'nome' => strtoupper(sanitize($_POST["dependente_nome_{$i}"])),
                'data_nascimento' => isset($_POST["dependente_data_nascimento_{$i}"]) ? 
                                   sanitize($_POST["dependente_data_nascimento_{$i}"]) : null,
                'cpf' => isset($_POST["dependente_cpf_{$i}"]) ? 
                      preg_replace('/[^0-9]/', '', sanitize($_POST["dependente_cpf_{$i}"])) : null,
                'rg' => isset($_POST["dependente_rg_{$i}"]) ? 
                      strtoupper(sanitize($_POST["dependente_rg_{$i}"])) : null,
                'deficiencia' => isset($_POST["dependente_deficiencia_{$i}"]) ? 
                             sanitize($_POST["dependente_deficiencia_{$i}"]) : null,
                'renda' => isset($_POST["dependente_renda_{$i}"]) ? 
                         sanitize($_POST["dependente_renda_{$i}"]) : null
            ];
        }
    }
    
    // FILIAÇÃO
    $nome_mae = isset($_POST['nome_mae']) ? strtoupper(sanitize($_POST['nome_mae'])) : null;
    $nome_pai = isset($_POST['nome_pai']) ? strtoupper(sanitize($_POST['nome_pai'])) : null;
    
    // SITUAÇÃO TRABALHISTA
    $situacao_trabalho = isset($_POST['situacao_trabalho']) ? strtoupper(sanitize($_POST['situacao_trabalho'])) : null;
    $profissao = isset($_POST['profissao']) ? strtoupper(sanitize($_POST['profissao'])) : null;
    $empregador = isset($_POST['empregador']) ? strtoupper(sanitize($_POST['empregador'])) : null;
    $cargo = isset($_POST['cargo']) ? strtoupper(sanitize($_POST['cargo'])) : null;
    $ramo_atividade = isset($_POST['ramo_atividade']) ? strtoupper(sanitize($_POST['ramo_atividade'])) : null;
    $tempo_servico = isset($_POST['tempo_servico']) ? sanitize($_POST['tempo_servico']) : null;
    
    // ENDEREÇO
    $tipo_moradia = isset($_POST['tipo_moradia']) ? strtoupper(sanitize($_POST['tipo_moradia'])) : null;
    $situacao_propriedade = isset($_POST['situacao_propriedade']) ? strtoupper(sanitize($_POST['situacao_propriedade'])) : null;
    $valor_aluguel = ($situacao_propriedade === 'ALUGADA' && isset($_POST['valor_aluguel'])) ? sanitize($_POST['valor_aluguel']) : null;
    if ($valor_aluguel) {
        $valor_aluguel = preg_replace('/[^0-9,.]/', '', $valor_aluguel); // Remove caracteres não numéricos, exceto virgula e ponto
        $valor_aluguel = str_replace(',', '.', $valor_aluguel); // Substitui vírgula por ponto para armazenar como decimal
    }
    $rua = isset($_POST['rua']) ? strtoupper(sanitize($_POST['rua'])) : null;
    $numero = isset($_POST['numero']) ? sanitize($_POST['numero']) : null;
    $complemento = isset($_POST['complemento']) ? strtoupper(sanitize($_POST['complemento'])) : null;
    $bairro = isset($_POST['bairro']) ? strtoupper(sanitize($_POST['bairro'])) : null;
    $cidade = isset($_POST['cidade']) ? strtoupper(sanitize($_POST['cidade'])) : null;
    $cep = isset($_POST['cep']) ? sanitize($_POST['cep']) : null;
    $cep = $cep ? preg_replace('/[^0-9]/', '', $cep) : null;
    $ponto_referencia = isset($_POST['ponto_referencia']) ? strtoupper(sanitize($_POST['ponto_referencia'])) : null;
    
    // CONTATO
    $telefone = isset($_POST['telefone']) ? sanitize($_POST['telefone']) : null;
    $telefone = $telefone ? preg_replace('/[^0-9]/', '', $telefone) : null;
    $celular = isset($_POST['celular']) ? sanitize($_POST['celular']) : null;
    $celular = $celular ? preg_replace('/[^0-9]/', '', $celular) : null;
    $email = isset($_POST['email']) ? sanitize($_POST['email']) : null;
    
    // INTERESSE
    $programa_interesse = isset($_POST['programa_interesse']) ? strtoupper(sanitize($_POST['programa_interesse'])) : null;
    $autoriza_email = isset($_POST['autoriza_email']) ? 1 : 0;
    
    // Processar uploads de documentos
    $cpf_documento = isset($_FILES['cpf_documento']) ? processarUpload($_FILES['cpf_documento'], 'cpf') : null;
    $escolaridade_documento = isset($_FILES['escolaridade_documento']) ? processarUpload($_FILES['escolaridade_documento'], 'escolaridade') : null;
    $viuvo_documento = ($estado_civil === 'VIÚVO(A)' && isset($_FILES['viuvo_documento'])) ? processarUpload($_FILES['viuvo_documento'], 'obito') : null;
    $laudo_deficiencia = ($deficiencia !== 'NÃO' && isset($_FILES['laudo_deficiencia'])) ? processarUpload($_FILES['laudo_deficiencia'], 'deficiencia') : null;
    $conjuge_comprovante_renda = ($has_conjuge && $conjuge_renda === 'SIM' && isset($_FILES['conjuge_comprovante_renda'])) ? processarUpload($_FILES['conjuge_comprovante_renda'], 'conjuge_renda') : null;
    $carteira_trabalho = (($situacao_trabalho === 'EMPREGADO COM CARTEIRA ASSINADA') && isset($_FILES['carteira_trabalho'])) ? processarUpload($_FILES['carteira_trabalho'], 'ctps') : null;
    
    // Validar campos obrigatórios
    $campos_obrigatorios = [
        'nome' => $nome,
        'cpf' => $cpf,
        'data_nascimento' => $data_nascimento,
        'genero' => $genero,
        'raca' => $raca,
        'estado_civil' => $estado_civil,
        'nome_mae' => $nome_mae,
        'situacao_trabalho' => $situacao_trabalho,
        'tipo_moradia' => $tipo_moradia,
        'situacao_propriedade' => $situacao_propriedade,
        'rua' => $rua,
        'numero' => $numero,
        'bairro' => $bairro,
        'cidade' => $cidade,
        'cep' => $cep,
        'celular' => $celular,
        'email' => $email,
        'programa_interesse' => $programa_interesse
    ];
    
    foreach ($campos_obrigatorios as $campo => $valor) {
        if (empty($valor)) {
            throw new Exception("O campo {$campo} é obrigatório.");
        }
    }
    
    // Validar uploads obrigatórios
    if ($cpf_documento === false) {
        throw new Exception("O documento de CPF enviado é inválido. Verifique o formato ou tamanho do arquivo.");
    }
    
    if ($cpf_documento === null) {
        throw new Exception("É obrigatório anexar um documento de CPF.");
    }
    
    // Iniciar transação
    $conn->beginTransaction();
    
    // Gerar protocolo
    $data_atual = date('Ymd');
    $protocolo = "HAB-{$data_atual}-" . sprintf('%06d', rand(1, 999999));
    
    try {
        // 1. Inserir dados do responsável na tabela de inscrições
        $sql_inscricao = "INSERT INTO tb_habitacao_inscricao (
            hab_usuario_id, hab_protocolo, hab_nome, hab_cpf, hab_nacionalidade,
            hab_nome_social_opcao, hab_nome_social, hab_genero, hab_data_nasc, hab_raca,
            hab_cad_unico, hab_nis, hab_escolaridade, hab_estado_civil, hab_deficiencia,
            hab_deficiencia_detalhe, hab_endereco, hab_numero, hab_complemento, hab_bairro,
            hab_cidade, hab_cep, hab_referencia, hab_telefone, hab_celular, hab_email,
            hab_nome_mae, hab_nome_pai, hab_sit_trabalho, hab_profissao, hab_empregador,
            hab_cargo, hab_ramo, hab_tempo_servico, hab_tipo_moradia, hab_sit_propriedade,
            hab_valor_aluguel, hab_programa, hab_autoriza_email, hab_data_inscricao,
            hab_status, hab_doc_cpf, hab_doc_escolaridade, hab_doc_obito, 
            hab_doc_deficiencia, hab_doc_ctps
        ) VALUES (
            :usuario_id, :protocolo, :nome, :cpf, :nacionalidade,
            :nome_social_opcao, :nome_social, :genero, :data_nascimento, :raca,
            :cad_unico, :nis, :escolaridade, :estado_civil, :deficiencia,
            :deficiencia_detalhe, :endereco, :numero, :complemento, :bairro,
            :cidade, :cep, :referencia, :telefone, :celular, :email,
            :nome_mae, :nome_pai, :sit_trabalho, :profissao, :empregador,
            :cargo, :ramo, :tempo_servico, :tipo_moradia, :sit_propriedade,
            :valor_aluguel, :programa, :autoriza_email, NOW(),
            'PENDENTE DE ANÁLISE', :doc_cpf, :doc_escolaridade, :doc_obito, 
            :doc_deficiencia, :doc_ctps
        )";
        
        $stmt = $conn->prepare($sql_inscricao);
        $stmt->bindParam(':usuario_id', $_SESSION['user_id']);
        $stmt->bindParam(':protocolo', $protocolo);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':cpf', $cpf);
        $stmt->bindParam(':nacionalidade', $nacionalidade);
        $stmt->bindParam(':nome_social_opcao', $nome_social_opcao);
        $stmt->bindParam(':nome_social', $nome_social);
        $stmt->bindParam(':genero', $genero);
        $stmt->bindParam(':data_nascimento', $data_nascimento);
        $stmt->bindParam(':raca', $raca);
        $stmt->bindParam(':cad_unico', $cad_unico);
        $stmt->bindParam(':nis', $nis);
        $stmt->bindParam(':escolaridade', $escolaridade);
        $stmt->bindParam(':estado_civil', $estado_civil);
        $stmt->bindParam(':deficiencia', $deficiencia);
        $stmt->bindParam(':deficiencia_detalhe', $deficiencia_fisica_detalhe);
        $stmt->bindParam(':endereco', $rua);
        $stmt->bindParam(':numero', $numero);
        $stmt->bindParam(':complemento', $complemento);
        $stmt->bindParam(':bairro', $bairro);
        $stmt->bindParam(':cidade', $cidade);
        $stmt->bindParam(':cep', $cep);
        $stmt->bindParam(':referencia', $ponto_referencia);
        $stmt->bindParam(':telefone', $telefone);
        $stmt->bindParam(':celular', $celular);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':nome_mae', $nome_mae);
        $stmt->bindParam(':nome_pai', $nome_pai);
        $stmt->bindParam(':sit_trabalho', $situacao_trabalho);
        $stmt->bindParam(':profissao', $profissao);
        $stmt->bindParam(':empregador', $empregador);
        $stmt->bindParam(':cargo', $cargo);
        $stmt->bindParam(':ramo', $ramo_atividade);
        $stmt->bindParam(':tempo_servico', $tempo_servico);
        $stmt->bindParam(':tipo_moradia', $tipo_moradia);
        $stmt->bindParam(':sit_propriedade', $situacao_propriedade);
        $stmt->bindParam(':valor_aluguel', $valor_aluguel);
        $stmt->bindParam(':programa', $programa_interesse);
        $stmt->bindParam(':autoriza_email', $autoriza_email);
        $stmt->bindParam(':doc_cpf', $cpf_documento);
        $stmt->bindParam(':doc_escolaridade', $escolaridade_documento);
        $stmt->bindParam(':doc_obito', $viuvo_documento);
        $stmt->bindParam(':doc_deficiencia', $laudo_deficiencia);
        $stmt->bindParam(':doc_ctps', $carteira_trabalho);
        
        $stmt->execute();
        $inscricao_id = $conn->lastInsertId();
        
        // 2. Inserir dados do cônjuge, se aplicável
        if ($has_conjuge && $conjuge_nome) {
            $sql_conjuge = "INSERT INTO tb_habitacao_conjuge (
                hab_conj_inscricao_id, hab_conj_nome, hab_conj_cpf, hab_conj_rg,
                hab_conj_data_nasc, hab_conj_renda, hab_conj_doc_renda
            ) VALUES (
                :inscricao_id, :nome, :cpf, :rg, 
                :data_nascimento, :renda, :doc_renda
            )";
            
            $stmt = $conn->prepare($sql_conjuge);
            $stmt->bindParam(':inscricao_id', $inscricao_id);
            $stmt->bindParam(':nome', $conjuge_nome);
            $stmt->bindParam(':cpf', $conjuge_cpf);
            $stmt->bindParam(':rg', $conjuge_rg);
            $stmt->bindParam(':data_nascimento', $conjuge_data_nascimento);
            $stmt->bindParam(':renda', $conjuge_renda);
            $stmt->bindParam(':doc_renda', $conjuge_comprovante_renda);
            $stmt->execute();
        }
        
        // 3. Inserir dependentes, se houver
        if (count($dependentes) > 0) {
            $sql_dependente = "INSERT INTO tb_habitacao_dependentes (
                hab_dep_inscricao_id, hab_dep_nome, hab_dep_data_nasc,
                hab_dep_cpf, hab_dep_rg, hab_dep_deficiencia, 
                hab_dep_renda, hab_dep_docs
            ) VALUES (
                :inscricao_id, :nome, :data_nascimento, 
                :cpf, :rg, :deficiencia, 
                :renda, :docs
            )";
            
            $stmt = $conn->prepare($sql_dependente);
            
            foreach ($dependentes as $i => $dep) {
                // Processar documentos do dependente
                $dep_docs = null;
                if (isset($_FILES["dependente_documentos_" . ($i+1)]) && 
                    $_FILES["dependente_documentos_" . ($i+1)]['error'] === UPLOAD_ERR_OK) {
                    $dep_docs = processarUpload($_FILES["dependente_documentos_" . ($i+1)], "dep_" . ($i+1));
                }
                
                $stmt->bindValue(':inscricao_id', $inscricao_id);
                $stmt->bindValue(':nome', $dep['nome']);
                $stmt->bindValue(':data_nascimento', $dep['data_nascimento']);
                $stmt->bindValue(':cpf', $dep['cpf']);
                $stmt->bindValue(':rg', $dep['rg']);
                $stmt->bindValue(':deficiencia', $dep['deficiencia']);
                $stmt->bindValue(':renda', $dep['renda']);
                $stmt->bindValue(':docs', $dep_docs);
                $stmt->execute();
            }
        }
        
        // Commit da transação
        $conn->commit();
        
        // Resposta para requisição AJAX
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            echo json_encode([
                'status' => 'success',
                'message' => 'Cadastro realizado com sucesso! Seu protocolo é: ' . $protocolo,
                'redirect' => "../pages/social-relatorio-habitacao.php?id={$inscricao_id}",
                'inscricao_id' => $inscricao_id,
                'protocolo' => $protocolo
            ]);
            exit;
        }
        
        // Redirecionamento padrão
        $_SESSION['sucesso_habitacao'] = "Cadastro realizado com sucesso! Seu protocolo é: " . $protocolo;
        header("Location: ../pages/social-relatorio-habitacao.php?id={$inscricao_id}");
        exit;
        
    } catch (PDOException $e) {
        // Rollback em caso de erro
        $conn->rollBack();
        
        // Registrar erro no log
        error_log("Erro no cadastro habitacional: " . $e->getMessage());
        
        // Resposta para requisição AJAX
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            echo json_encode([
                'status' => 'error',
                'message' => "Erro ao salvar no banco de dados: " . $e->getMessage()
            ]);
            exit;
        }
        
        // Redirecionamento padrão
        $_SESSION['erro_habitacao'] = "Erro ao processar o cadastro: " . $e->getMessage();
        header("Location: ../pages/socialhabitacao.php");
        exit;
    }
    
} catch (Exception $e) {
    // Rollback em caso de erro
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    // Registrar erro no log
    error_log("Erro no cadastro habitacional: " . $e->getMessage());
    
    // Resposta para requisição AJAX
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
        exit;
    }
    
    // Redirecionamento padrão
    $_SESSION['erro_habitacao'] = "Erro ao processar o cadastro: " . $e->getMessage();
    header("Location: ../pages/socialhabitacao.php");
    exit;
}
?>