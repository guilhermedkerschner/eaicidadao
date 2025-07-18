<?php
/**
 * Arquivo: processar_habitacao.php (Versão Completa Final)
 * Descrição: Processa o formulário de cadastro para programas habitacionais
 * 
 * Parte do sistema Eai Cidadão! - Município de Santa Izabel do Oeste
 */

// Configuração de erros para produção
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Inicia a sessão
session_start();

// Define o cabeçalho para JSON se for uma solicitação AJAX
$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

try {
    // Verifica se o usuário está logado
    if (!isset($_SESSION['user_logado']) || $_SESSION['user_logado'] !== true) {
        throw new Exception('Usuário não autenticado.');
    }

    // Verificar se o formulário foi enviado via POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método de requisição inválido.');
    }

    // Incluir arquivo de configuração com conexão ao banco de dados
    require_once "../lib/config.php";

    // Verificar conexão com o banco
    if (!isset($conn) || !$conn) {
        throw new Exception("Erro na conexão com o banco de dados.");
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

    // Função para validar CPF
    function validarCPF($cpf) {
        // Remove formatação
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        // Verifica se tem 11 dígitos
        if (strlen($cpf) != 11) return false;
        
        // Verifica se todos os dígitos são iguais
        if (preg_match('/(\d)\1{10}/', $cpf)) return false;
        
        // Validação do algoritmo do CPF
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) return false;
        }
        
        return true;
    }

    // Função para validar e-mail
    function validarEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    // Função para verificar se CPF já existe no sistema
    function cpfJaExiste($cpf, $conn, $excluir_id = null) {
        $sql = "SELECT cad_social_id FROM tb_cad_social WHERE cad_social_cpf = :cpf";
        if ($excluir_id) {
            $sql .= " AND cad_social_id != :excluir_id";
        }
        $sql .= " LIMIT 1";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':cpf', $cpf);
        if ($excluir_id) {
            $stmt->bindParam(':excluir_id', $excluir_id);
        }
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    // Função para processar upload de arquivo
    function processarUpload($arquivo, $tipo, $cpf) {
        if (!isset($arquivo) || !isset($arquivo['tmp_name']) || empty($arquivo['tmp_name'])) {
            return null;
        }
        
        // Verificar se houve erro no upload
        if ($arquivo['error'] !== UPLOAD_ERR_OK) {
            switch ($arquivo['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    throw new Exception("Arquivo muito grande. Tamanho máximo permitido: 5MB");
                case UPLOAD_ERR_PARTIAL:
                    throw new Exception("Upload do arquivo foi interrompido");
                case UPLOAD_ERR_NO_FILE:
                    return null;
                default:
                    throw new Exception("Erro no upload do arquivo");
            }
        }
        
        // Verificar tipo de arquivo
        $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $arquivo['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowed)) {
            throw new Exception("Tipo de arquivo não permitido. Use apenas JPG, PNG ou PDF");
        }
        
        // Verificar tamanho (5MB)
        if ($arquivo['size'] > 5 * 1024 * 1024) {
            throw new Exception("Arquivo muito grande. Tamanho máximo: 5MB");
        }
        
        // Obter extensão do arquivo
        $ext = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
        $ext = strtolower($ext);
            
        // Obter a data atual no formato AAAAMMDD
        $data_atual = date('Ymd');

        // Criar nome do arquivo usando o tipo, CPF e data
        $novo_nome = "HAB_{$tipo}_{$cpf}_{$data_atual}_" . uniqid() . ".{$ext}";
        
        // Definir caminho para salvar
        $upload_dir = "../uploads/habitacao/";
        
        // Verificar se o diretório existe, se não, criar
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                throw new Exception("Erro ao criar diretório de upload");
            }
        }
        
        $caminho_completo = $upload_dir . $novo_nome;
        
        // Mover o arquivo
        if (move_uploaded_file($arquivo['tmp_name'], $caminho_completo)) {
            return $novo_nome;
        }
        
        throw new Exception("Erro ao salvar arquivo no servidor");
    }

    // Capturar dados do formulário - RESPONSÁVEL FAMILIAR
    $nome = isset($_POST['nome']) ? strtoupper(sanitize($_POST['nome'])) : null;
    $cpf = isset($_POST['cpf']) ? sanitize($_POST['cpf']) : null;
    $cpf = $cpf ? preg_replace('/[^0-9]/', '', $cpf) : null;
    $rg = isset($_POST['rg']) ? strtoupper(sanitize($_POST['rg'])) : null;
    $nacionalidade = isset($_POST['nacionalidade']) ? strtoupper(sanitize($_POST['nacionalidade'])) : null;
    $nome_social_opcao = isset($_POST['nome_social_opcao']) ? sanitize($_POST['nome_social_opcao']) : 'NÃO';
    $nome_social = ($nome_social_opcao === 'SIM' && isset($_POST['nome_social'])) ? strtoupper(sanitize($_POST['nome_social'])) : null;
    $genero = isset($_POST['genero']) ? strtoupper(sanitize($_POST['genero'])) : null;
    $data_nascimento = isset($_POST['data_nascimento']) ? sanitize($_POST['data_nascimento']) : null;
    $raca = isset($_POST['raca']) ? strtoupper(sanitize($_POST['raca'])) : null;
    $cad_unico = isset($_POST['cad_unico']) ? sanitize($_POST['cad_unico']) : null;
    $nis = isset($_POST['nis']) ? sanitize($_POST['nis']) : null;
    $escolaridade = isset($_POST['escolaridade']) ? strtoupper(sanitize($_POST['escolaridade'])) : null;
    $estado_civil = isset($_POST['estado_civil']) ? strtoupper(sanitize($_POST['estado_civil'])) : null;
    $deficiencia = isset($_POST['deficiencia']) ? strtoupper(sanitize($_POST['deficiencia'])) : 'NÃO';
    $deficiencia_fisica_detalhe = ($deficiencia === 'FISICA' && isset($_POST['deficiencia_fisica_detalhe'])) ? 
                                 strtoupper(sanitize($_POST['deficiencia_fisica_detalhe'])) : null;
    
    // CÔNJUGE (Se aplicável)
    $has_conjuge = ($estado_civil === 'CASADO(A)' || $estado_civil === 'UNIÃO ESTÁVEL/AMASIADO(A)');
    $conjuge_nome = $has_conjuge && isset($_POST['conjuge_nome']) ? strtoupper(sanitize($_POST['conjuge_nome'])) : null;
    $conjuge_cpf = $has_conjuge && isset($_POST['conjuge_cpf']) ? sanitize($_POST['conjuge_cpf']) : null;
    $conjuge_cpf = $conjuge_cpf ? preg_replace('/[^0-9]/', '', $conjuge_cpf) : null;
    $conjuge_rg = $has_conjuge && isset($_POST['conjuge_rg']) ? strtoupper(sanitize($_POST['conjuge_rg'])) : null;
    $conjuge_data_nascimento = $has_conjuge && isset($_POST['conjuge_data_nascimento']) ? sanitize($_POST['conjuge_data_nascimento']) : null;
    $conjuge_renda = $has_conjuge && isset($_POST['conjuge_renda']) ? sanitize($_POST['conjuge_renda']) : 'NÃO';
    
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
        $valor_aluguel = preg_replace('/[^0-9,.]/', '', $valor_aluguel);
        $valor_aluguel = str_replace(',', '.', $valor_aluguel);
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
    $autoriza_credito = isset($_POST['autoriza_credito']) ? 1 : 0;
    $medida_protetiva = isset($_POST['medida_protetiva']) ? 1 : 0;
    $autoriza_email = isset($_POST['autoriza_email']) ? 1 : 0;
    
    // ===== VALIDAÇÕES =====
    
    // Validar campos obrigatórios
    $campos_obrigatorios = [
        'Nome completo' => $nome,
        'CPF' => $cpf,
        'Data de nascimento' => $data_nascimento,
        'Gênero' => $genero,
        'Raça/Cor' => $raca,
        'Estado civil' => $estado_civil,
        'Nome da mãe' => $nome_mae,
        'Situação trabalhista' => $situacao_trabalho,
        'Tipo de moradia' => $tipo_moradia,
        'Situação da propriedade' => $situacao_propriedade,
        'Rua' => $rua,
        'Número' => $numero,
        'Bairro' => $bairro,
        'Cidade' => $cidade,
        'CEP' => $cep,
        'Celular' => $celular,
        'E-mail' => $email,
        'Programa de interesse' => $programa_interesse
    ];
    
    foreach ($campos_obrigatorios as $campo_nome => $valor) {
        if (empty($valor)) {
            throw new Exception("O campo '{$campo_nome}' é obrigatório.");
        }
    }
    
    // Validar CPF principal
    if (!validarCPF($cpf)) {
        throw new Exception("CPF do responsável inválido.");
    }
    
    // Verificar se CPF já existe
    if (cpfJaExiste($cpf, $conn)) {
        throw new Exception("Este CPF já possui um cadastro no sistema.");
    }
    
    // Validar e-mail
    if (!validarEmail($email)) {
        throw new Exception("E-mail inválido.");
    }
    
    // Validar idade mínima (18 anos)
    $data_nasc = new DateTime($data_nascimento);
    $hoje = new DateTime();
    $idade = $hoje->diff($data_nasc)->y;
    if ($idade < 18) {
        throw new Exception("O responsável deve ter pelo menos 18 anos de idade.");
    }
    
    // Validar autorização de crédito (obrigatória)
    if ($autoriza_credito !== 1) {
        throw new Exception("É obrigatório autorizar a consulta de crédito para prosseguir.");
    }
    
    // Validações específicas para cônjuge
    if ($has_conjuge) {
        if (empty($conjuge_nome)) {
            throw new Exception("Nome do cônjuge é obrigatório para estado civil informado.");
        }
        if (empty($conjuge_cpf)) {
            throw new Exception("CPF do cônjuge é obrigatório para estado civil informado.");
        }
        if (!validarCPF($conjuge_cpf)) {
            throw new Exception("CPF do cônjuge inválido.");
        }
        if ($cpf === $conjuge_cpf) {
            throw new Exception("CPF do cônjuge não pode ser igual ao CPF do responsável.");
        }
        if (empty($conjuge_data_nascimento)) {
            throw new Exception("Data de nascimento do cônjuge é obrigatória.");
        }
    }
    
    // Validar documento obrigatório se for viúvo
    if ($estado_civil === 'VIÚVO(A)' && !isset($_FILES['viuvo_documento'])) {
        throw new Exception("É obrigatório anexar certidão de óbito para estado civil viúvo(a).");
    }
    
    // Processar uploads de documentos
    $cpf_documento = isset($_FILES['cpf_documento']) ? processarUpload($_FILES['cpf_documento'], 'cpf', $cpf) : null;
    $escolaridade_documento = isset($_FILES['escolaridade_documento']) ? processarUpload($_FILES['escolaridade_documento'], 'escolaridade', $cpf) : null;
    $viuvo_documento = ($estado_civil === 'VIÚVO(A)' && isset($_FILES['viuvo_documento'])) ? processarUpload($_FILES['viuvo_documento'], 'obito', $cpf) : null;
    $laudo_deficiencia = ($deficiencia !== 'NÃO' && isset($_FILES['laudo_deficiencia'])) ? processarUpload($_FILES['laudo_deficiencia'], 'deficiencia', $cpf) : null;
    $conjuge_comprovante_renda = ($has_conjuge && $conjuge_renda === 'SIM' && isset($_FILES['conjuge_comprovante_renda'])) ? processarUpload($_FILES['conjuge_comprovante_renda'], 'conjuge_renda', $cpf) : null;
    $carteira_trabalho = (($situacao_trabalho === 'EMPREGADO COM CARTEIRA ASSINADA') && isset($_FILES['carteira_trabalho'])) ? processarUpload($_FILES['carteira_trabalho'], 'ctps', $cpf) : null;
    
    // Validar upload obrigatório do CPF
    if ($cpf_documento === null) {
        throw new Exception("É obrigatório anexar um documento de CPF.");
    }
    
    // Iniciar transação
    $conn->beginTransaction();
    
    // Gerar protocolo
    $data_atual = date('Ymd');
    $prefixo_protocolo = "HAB-{$data_atual}-";
    
    // Buscar o último número de protocolo do dia
    $sql_ultimo_protocolo = "SELECT cad_social_protocolo FROM tb_cad_social 
                             WHERE cad_social_protocolo LIKE :prefixo_protocolo 
                             ORDER BY cad_social_protocolo DESC LIMIT 1";
    
    $stmt_protocolo = $conn->prepare($sql_ultimo_protocolo);
    $prefixo_busca = $prefixo_protocolo . '%';
    $stmt_protocolo->bindParam(':prefixo_protocolo', $prefixo_busca);
    $stmt_protocolo->execute();
    
    if ($stmt_protocolo->rowCount() > 0) {
        $ultimo_protocolo = $stmt_protocolo->fetch(PDO::FETCH_ASSOC)['cad_social_protocolo'];
        $ultimo_numero = (int)substr($ultimo_protocolo, -3);
        $novo_numero = $ultimo_numero + 1;
        $protocolo = $prefixo_protocolo . sprintf('%03d', $novo_numero);
    } else {
        $protocolo = $prefixo_protocolo . '001';
    }
    $_SESSION['user_prot_hab'] = $protocolo;
    
    // Inserir dados do responsável na tabela principal
    $sql_inscricao = "INSERT INTO tb_cad_social (
        cad_usu_id, cad_social_nome, cad_social_cpf, cad_social_cpf_documento, cad_social_rg,
        cad_social_nacionalidade, cad_social_nome_social_opcao, cad_social_nome_social, 
        cad_social_genero, cad_social_data_nascimento, cad_social_raca, cad_social_cad_unico, 
        cad_social_nis, cad_social_escolaridade, cad_social_escolaridade_documento, 
        cad_social_estado_civil, cad_social_viuvo_documento, cad_social_conjuge_nome,
        cad_social_conjuge_cpf, cad_social_conjuge_rg, cad_social_conjuge_data_nascimento,
        cad_social_conjuge_renda, cad_social_conjuge_comprovante_renda, cad_social_deficiencia,
        cad_social_deficiencia_fisica_detalhe, cad_social_laudo_deficiencia, cad_social_num_dependentes,
        cad_social_nome_mae, cad_social_nome_pai, cad_social_situacao_trabalho, cad_social_profissao,
        cad_social_empregador, cad_social_cargo, cad_social_ramo_atividade, cad_social_tempo_servico,
        cad_social_carteira_trabalho, cad_social_tipo_moradia, cad_social_situacao_propriedade,
        cad_social_valor_aluguel, cad_social_rua, cad_social_numero, cad_social_complemento,
        cad_social_bairro, cad_social_cidade, cad_social_cep, cad_social_ponto_referencia,
        cad_social_telefone, cad_social_celular, cad_social_email, cad_social_programa_interesse,
        cad_social_autoriza_credito, cad_social_medida_protetiva, 
        cad_social_autoriza_email, cad_social_data_cadastro, cad_social_status, cad_social_protocolo
    ) VALUES (
        :usuario_id, :nome, :cpf, :cpf_documento, :rg,
        :nacionalidade, :nome_social_opcao, :nome_social, 
        :genero, :data_nascimento, :raca, :cad_unico, 
        :nis, :escolaridade, :escolaridade_documento, 
        :estado_civil, :viuvo_documento, :conjuge_nome,
        :conjuge_cpf, :conjuge_rg, :conjuge_data_nascimento,
        :conjuge_renda, :conjuge_comprovante_renda, :deficiencia,
        :deficiencia_detalhe, :laudo_deficiencia, :num_dependentes,
        :nome_mae, :nome_pai, :sit_trabalho, :profissao,
        :empregador, :cargo, :ramo, :tempo_servico,
        :carteira_trabalho, :tipo_moradia, :sit_propriedade,
        :valor_aluguel, :rua, :numero, :complemento,
        :bairro, :cidade, :cep, :referencia,
        :telefone, :celular, :email, :programa,
        :autoriza_credito, :medida_protetiva,
        :autoriza_email, NOW(), 'PENDENTE DE ANÁLISE', :protocolo
    )";
    
    $stmt = $conn->prepare($sql_inscricao);
    
    // Binding de parâmetros
    $stmt->bindParam(':usuario_id', $_SESSION['user_id']);
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':cpf', $cpf);
    $stmt->bindParam(':cpf_documento', $cpf_documento);
    $stmt->bindParam(':rg', $rg);
    $stmt->bindParam(':nacionalidade', $nacionalidade);
    $stmt->bindParam(':nome_social_opcao', $nome_social_opcao);
    $stmt->bindParam(':nome_social', $nome_social);
    $stmt->bindParam(':genero', $genero);
    $stmt->bindParam(':data_nascimento', $data_nascimento);
    $stmt->bindParam(':raca', $raca);
    $stmt->bindParam(':cad_unico', $cad_unico);
    $stmt->bindParam(':nis', $nis);
    $stmt->bindParam(':escolaridade', $escolaridade);
    $stmt->bindParam(':escolaridade_documento', $escolaridade_documento);
    $stmt->bindParam(':estado_civil', $estado_civil);
    $stmt->bindParam(':viuvo_documento', $viuvo_documento);
    $stmt->bindParam(':conjuge_nome', $conjuge_nome);
    $stmt->bindParam(':conjuge_cpf', $conjuge_cpf);
    $stmt->bindParam(':conjuge_rg', $conjuge_rg);
    $stmt->bindParam(':conjuge_data_nascimento', $conjuge_data_nascimento);
    $stmt->bindParam(':conjuge_renda', $conjuge_renda);
    $stmt->bindParam(':conjuge_comprovante_renda', $conjuge_comprovante_renda);
    $stmt->bindParam(':deficiencia', $deficiencia);
    $stmt->bindParam(':deficiencia_detalhe', $deficiencia_fisica_detalhe);
    $stmt->bindParam(':laudo_deficiencia', $laudo_deficiencia);
    $stmt->bindParam(':num_dependentes', $num_dependentes);
    $stmt->bindParam(':nome_mae', $nome_mae);
    $stmt->bindParam(':nome_pai', $nome_pai);
    $stmt->bindParam(':sit_trabalho', $situacao_trabalho);
    $stmt->bindParam(':profissao', $profissao);
    $stmt->bindParam(':empregador', $empregador);
    $stmt->bindParam(':cargo', $cargo);
    $stmt->bindParam(':ramo', $ramo_atividade);
    $stmt->bindParam(':tempo_servico', $tempo_servico);
    $stmt->bindParam(':carteira_trabalho', $carteira_trabalho);
    $stmt->bindParam(':tipo_moradia', $tipo_moradia);
    $stmt->bindParam(':sit_propriedade', $situacao_propriedade);
    $stmt->bindParam(':valor_aluguel', $valor_aluguel);
    $stmt->bindParam(':rua', $rua);
    $stmt->bindParam(':numero', $numero);
    $stmt->bindParam(':complemento', $complemento);
    $stmt->bindParam(':bairro', $bairro);
    $stmt->bindParam(':cidade', $cidade);
    $stmt->bindParam(':cep', $cep);
    $stmt->bindParam(':referencia', $ponto_referencia);
    $stmt->bindParam(':telefone', $telefone);
    $stmt->bindParam(':celular', $celular);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':programa', $programa_interesse);
    $stmt->bindParam(':autoriza_credito', $autoriza_credito);
    $stmt->bindParam(':medida_protetiva', $medida_protetiva);
    $stmt->bindParam(':autoriza_email', $autoriza_email);
    $stmt->bindParam(':protocolo', $protocolo);
    
    $stmt->execute();
    $inscricao_id = $conn->lastInsertId();
    
    // Inserir dependentes, se houver
    if (count($dependentes) > 0 && $inscricao_id) {
        $sql_dependente = "INSERT INTO tb_cad_social_dependentes (
            cad_social_id, cad_social_dependente_nome, cad_social_dependente_data_nascimento,
            cad_social_dependente_cpf, cad_social_dependente_rg, cad_social_dependente_documentos,
            cad_social_dependente_deficiencia, cad_social_dependente_renda, 
            cad_social_dependente_comprovante_renda
        ) VALUES (
            :inscricao_id, :nome, :data_nascimento, 
            :cpf, :rg, :documentos,
            :deficiencia, :renda, :comprovante_renda
        )";
        
        $stmt_dep = $conn->prepare($sql_dependente);
        
        foreach ($dependentes as $i => $dep) {
            // Processar documentos do dependente
            $dep_docs = null;
            if (isset($_FILES["dependente_documentos_" . ($i+1)]) && 
                $_FILES["dependente_documentos_" . ($i+1)]['error'] === UPLOAD_ERR_OK) {
                $dep_docs = processarUpload($_FILES["dependente_documentos_" . ($i+1)], "dep" . ($i+1), $cpf);
            }
            
            // Processar comprovante de renda do dependente
            $dep_renda_docs = null;
            if ($dep['renda'] === 'SIM' && isset($_FILES["dependente_comprovante_renda_" . ($i+1)]) && 
                $_FILES["dependente_comprovante_renda_" . ($i+1)]['error'] === UPLOAD_ERR_OK) {
                $dep_renda_docs = processarUpload($_FILES["dependente_comprovante_renda_" . ($i+1)], "deprenda" . ($i+1), $cpf);
            }
            
            $stmt_dep->bindValue(':inscricao_id', $inscricao_id);
            $stmt_dep->bindValue(':nome', $dep['nome']);
            $stmt_dep->bindValue(':data_nascimento', $dep['data_nascimento']);
            $stmt_dep->bindValue(':cpf', $dep['cpf']);
            $stmt_dep->bindValue(':rg', $dep['rg']);
            $stmt_dep->bindValue(':documentos', $dep_docs);
            $stmt_dep->bindValue(':deficiencia', $dep['deficiencia']);
            $stmt_dep->bindValue(':renda', $dep['renda']);
            $stmt_dep->bindValue(':comprovante_renda', $dep_renda_docs);
            $stmt_dep->execute();
        }
    }

    // Registrar a solicitação para acompanhamento
    $sql_solicitacao = "INSERT INTO tb_solicitacoes (
        usuario_id, 
        tipo_solicitacao,
        subtipo,
        protocolo,
        data_solicitacao,
        status,
        ultima_atualizacao,
        departamento_responsavel
    ) VALUES (
        :usuario_id,
        'HABITACAO',
        :subtipo,
        :protocolo,
        NOW(),
        'PENDENTE DE ANÁLISE',
        NOW(),
        'SECRETARIA DE ASSISTÊNCIA SOCIAL'
    )";

    $stmt_solic = $conn->prepare($sql_solicitacao);
    $stmt_solic->bindParam(':usuario_id', $_SESSION['user_id']);
    $stmt_solic->bindParam(':subtipo', $programa_interesse);
    $stmt_solic->bindParam(':protocolo', $protocolo);
    $stmt_solic->execute();

    // Registrar no histórico
    $solicitacao_id = $conn->lastInsertId();

    $sql_historico = "INSERT INTO tb_solicitacoes_historico (
        solicitacao_id,
        status_anterior,
        status_novo,
        detalhes,
        data_operacao
    ) VALUES (
        :solicitacao_id,
        NULL,
        'PENDENTE DE ANÁLISE',
        'Solicitação recebida e aguardando análise',
        NOW()
    )";

    $stmt_hist = $conn->prepare($sql_historico);
    $stmt_hist->bindParam(':solicitacao_id', $solicitacao_id);
    $stmt_hist->execute();

    // Commit da transação
    $conn->commit();
    
    // Log do sucesso
    error_log("Cadastro habitação realizado com sucesso - ID: {$inscricao_id}, Protocolo: {$protocolo}");
    
    // Armazenar informações de sucesso na sessão
    $_SESSION['cadastro_realizado'] = true;
    $_SESSION['inscricao_id'] = $inscricao_id;
    $_SESSION['protocolo'] = $protocolo;
    $_SESSION['data_cadastro'] = date('Y-m-d H:i:s');
    
    // ===== RESPOSTA PARA AJAX =====
    if ($is_ajax) {
        // Limpar qualquer output anterior
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Definir header JSON
        header('Content-Type: application/json; charset=utf-8');
        
        // Retornar JSON de sucesso
        echo json_encode([
            'status' => 'success',
            'message' => 'Cadastro realizado com sucesso! Protocolo: ' . $protocolo,
            'protocolo' => $protocolo,
            'inscricao_id' => $inscricao_id
        ]);
        exit();
    }
    
    // ===== RESPOSTA PARA FORMULÁRIO NORMAL =====
    // Limpar buffer de saída antes do redirecionamento
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Redirecionar para socialhabitacao.php com parâmetros de sucesso
    $redirect_url = "../pages/socialhabitacao.php?success=true&id=" . $inscricao_id . "&protocolo=" . urlencode($protocolo);
    header("Location: " . $redirect_url);
    exit();
    
} catch (Exception $e) {
    // Rollback em caso de erro
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    // Registrar erro no log
    error_log("Erro no cadastro habitacional: " . $e->getMessage());
    
    // ===== RESPOSTA DE ERRO PARA AJAX =====
    if ($is_ajax) {
        // Limpar qualquer output anterior
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Definir header JSON  
        header('Content-Type: application/json; charset=utf-8');
        
        // Retornar JSON de erro
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
        exit();
    }
    
    // ===== RESPOSTA DE ERRO PARA FORMULÁRIO NORMAL =====
    // Armazenar erro na sessão para exibição
    $_SESSION['erro_habitacao'] = $e->getMessage();
    
    // Redirecionar de volta para o formulário com erro
    $error_redirect = "../pages/socialhabitacao.php?error=true&msg=" . urlencode($e->getMessage());
    
    // Limpar buffer de saída antes do redirecionamento
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    header("Location: " . $error_redirect);
    exit();
}

/**
 * Funções auxiliares adicionais
 */

/**
 * Função para limpar arquivos temporários em caso de erro
 */
function limparArquivosTemporarios($arquivos) {
    foreach ($arquivos as $arquivo) {
        if ($arquivo && file_exists("../uploads/habitacao/" . $arquivo)) {
            unlink("../uploads/habitacao/" . $arquivo);
        }
    }
}

/**
 * Função para validar data
 */
function validarData($data) {
    $d = DateTime::createFromFormat('Y-m-d', $data);
    return $d && $d->format('Y-m-d') === $data;
}

/**
 * Função para validar telefone brasileiro
 */
function validarTelefone($telefone) {
    $telefone = preg_replace('/[^0-9]/', '', $telefone);
    return (strlen($telefone) === 10 || strlen($telefone) === 11);
}

/**
 * Função para validar CEP brasileiro
 */
function validarCEP($cep) {
    $cep = preg_replace('/[^0-9]/', '', $cep);
    return (strlen($cep) === 8 && is_numeric($cep));
}

/**
 * Função para sanitizar nomes próprios
 */
function sanitizarNome($nome) {
    $nome = trim($nome);
    $nome = preg_replace('/\s+/', ' ', $nome); // Remove espaços extras
    $nome = ucwords(strtolower($nome)); // Primeira letra maiúscula
    return $nome;
}

/**
 * Função para gerar hash seguro para arquivos
 */
function gerarHashArquivo($nomeOriginal, $cpf) {
    return hash('sha256', $nomeOriginal . $cpf . time());
}

/**
 * Função para registrar atividade do usuário
 */
function registrarAtividade($conn, $usuario_id, $acao, $detalhes) {
    try {
        $sql = "INSERT INTO tb_log_atividades (usuario_id, acao, detalhes, data_atividade, ip_address) 
                VALUES (:usuario_id, :acao, :detalhes, NOW(), :ip)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':acao', $acao);
        $stmt->bindParam(':detalhes', $detalhes);
        $stmt->bindParam(':ip', $_SERVER['REMOTE_ADDR']);
        $stmt->execute();
    } catch (Exception $e) {
        // Log de erro mas não interrompe o processo principal
        error_log("Erro ao registrar atividade: " . $e->getMessage());
    }
}

/**
 * Função para enviar notificação por email (opcional)
 */
function enviarNotificacaoEmail($email, $nome, $protocolo) {
    // Implementar envio de email se necessário
    // PHPMailer ou função mail() do PHP
    
    $assunto = "Cadastro Habitacional Realizado - Protocolo: {$protocolo}";
    $mensagem = "
        Olá {$nome},
        
        Seu cadastro para programas habitacionais foi realizado com sucesso!
        
        Protocolo: {$protocolo}
        Data: " . date('d/m/Y H:i') . "
        
        Você pode acompanhar o status da sua solicitação através do sistema.
        
        Atenciosamente,
        Prefeitura Municipal de Santa Izabel do Oeste
    ";
    
    // Descomente e configure se quiser enviar emails
    // mail($email, $assunto, $mensagem);
}
?>