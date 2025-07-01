<?php
// Inicia a sessão
session_start();

// Verifica se o usuário está logado no sistema administrativo
if (!isset($_SESSION['usersystem_logado'])) {
    header("Location: ../acessdeniedrestrict.php"); 
    exit;
}

// Inclui arquivo de configuração com conexão ao banco de dados
require_once "../lib/config.php";

// Definição de variáveis
$mensagem = "";
$tipo_mensagem = "";
$is_exibir_modal = false;
$inscricao_atual = null;
$dependentes = [];
$comentarios = [];
$arquivos = [];

// Processamento de ações
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Atualizar status
    if (isset($_POST['acao']) && $_POST['acao'] == 'atualizar_status') {
        $inscricao_id = filter_input(INPUT_POST, 'inscricao_id', FILTER_VALIDATE_INT);
        $novo_status = filter_input(INPUT_POST, 'novo_status', FILTER_SANITIZE_SPECIAL_CHARS);
        $observacao = filter_input(INPUT_POST, 'observacao', FILTER_SANITIZE_SPECIAL_CHARS);
        
        try {
            // Atualiza o status
            $stmt = $conn->prepare("UPDATE tb_cad_social SET cad_social_status = :status WHERE cad_social_id = :id");
            $stmt->bindParam(':status', $novo_status);
            $stmt->bindParam(':id', $inscricao_id);
            $stmt->execute();
            // Atualiza no tb_solicitacoes
            $stmt = $conn->prepare("UPDATE tb_solicitacoes SET status = :status WHERE protocolo = :protocolo");
            // Vincular os parâmetros
            $stmt->bindParam(':status', $novo_status);
            $stmt->bindParam(':protocolo', $inscricao_atual['cad_social_protocolo']);
            // Executar a consulta
            $stmt->execute();

        
            
            $mensagem = "Status atualizado com sucesso!";
            $tipo_mensagem = "success";
        } catch (PDOException $e) {
            $mensagem = "Erro ao atualizar status: " . $e->getMessage();
            $tipo_mensagem = "danger";
        }
    }
    
    // Adicionar comentário
    elseif (isset($_POST['acao']) && $_POST['acao'] == 'adicionar_comentario') {
        $inscricao_id = filter_input(INPUT_POST, 'inscricao_id', FILTER_VALIDATE_INT);
        $comentario = filter_input(INPUT_POST, 'comentario', FILTER_SANITIZE_SPECIAL_CHARS);
        
        try {
            $stmt = $conn->prepare("INSERT INTO tb_cad_social_historico 
                (cad_social_id, cad_social_hist_acao, cad_social_hist_observacao, cad_social_hist_usuario, cad_social_hist_data) 
                VALUES (:inscricao_id, :acao, :observacao, :usuario, NOW())");
            $stmt->bindParam(':inscricao_id', $inscricao_id);
            $acao = "Comentário";
            $stmt->bindParam(':acao', $acao);
            $stmt->bindParam(':observacao', $comentario);
            $usuario_id = $_SESSION['usersystem_id'] ?? 0;
            $stmt->bindParam(':usuario', $usuario_id);
            $stmt->execute();
            
            $mensagem = "Comentário adicionado com sucesso!";
            $tipo_mensagem = "success";
        } catch (PDOException $e) {
            $mensagem = "Erro ao adicionar comentário: " . $e->getMessage();
            $tipo_mensagem = "danger";
        }
    }
    
    // Anexar arquivo
    elseif (isset($_POST['acao']) && $_POST['acao'] == 'anexar_arquivo') {
        $inscricao_id = filter_input(INPUT_POST, 'inscricao_id', FILTER_VALIDATE_INT);
        $descricao_arquivo = filter_input(INPUT_POST, 'descricao_arquivo', FILTER_SANITIZE_SPECIAL_CHARS);
        
        // Verifica se o arquivo foi enviado
        if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] == 0) {
            $arquivo = $_FILES['arquivo'];
            
            // Validar arquivo
            $tipos_permitidos = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
            $tamanho_maximo = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($arquivo['type'], $tipos_permitidos)) {
                $mensagem = "Tipo de arquivo não permitido. Apenas PDF, JPG e PNG são aceitos.";
                $tipo_mensagem = "danger";
            } elseif ($arquivo['size'] > $tamanho_maximo) {
                $mensagem = "O arquivo excede o tamanho máximo permitido (5MB).";
                $tipo_mensagem = "danger";
            } else {
                // Nome do arquivo
                $extensao = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
                $data_atual = date('Ymd_His');
                $nome_arquivo = "HABSYS_{$inscricao_id}_{$data_atual}.{$extensao}";
                
                // Diretório para salvar
                $upload_dir = "../uploads/habitacao/sistema/";
                
                // Verificar se o diretório existe, se não, criar
                if (!is_dir($upload_dir)) {
                    if (!mkdir($upload_dir, 0755, true)) {
                        $mensagem = "Erro ao criar diretório para upload.";
                        $tipo_mensagem = "danger";
                    }
                }
                
                // Move o arquivo para o destino
                if (empty($mensagem)) {
                    $caminho_completo = $upload_dir . $nome_arquivo;
                    
                    if (move_uploaded_file($arquivo['tmp_name'], $caminho_completo)) {
                        try {
                            // Registra o arquivo no banco de dados
                            $stmt = $conn->prepare("INSERT INTO tb_cad_social_arquivos 
                                (cad_social_id, cad_social_arq_nome, cad_social_arq_descricao, cad_social_arq_usuario, cad_social_arq_data) 
                                VALUES (:inscricao_id, :nome_arquivo, :descricao, :usuario, NOW())");
                            $stmt->bindParam(':inscricao_id', $inscricao_id);
                            $stmt->bindParam(':nome_arquivo', $nome_arquivo);
                            $stmt->bindParam(':descricao', $descricao_arquivo);
                            $usuario_id = $_SESSION['usersystem_id'] ?? 0;
                            $stmt->bindParam(':usuario', $usuario_id);
                            $stmt->execute();
                            
                            // Registra a ação no histórico
                            $stmt = $conn->prepare("INSERT INTO tb_cad_social_historico 
                                (cad_social_id, cad_social_hist_acao, cad_social_hist_observacao, cad_social_hist_usuario, cad_social_hist_data) 
                                VALUES (:inscricao_id, :acao, :observacao, :usuario, NOW())");
                            $stmt->bindParam(':inscricao_id', $inscricao_id);
                            $acao = "Arquivo anexado";
                            $stmt->bindParam(':acao', $acao);
                            $observacao = "Arquivo: {$arquivo['name']} - {$descricao_arquivo}";
                            $stmt->bindParam(':observacao', $observacao);
                            $stmt->bindParam(':usuario', $usuario_id);
                            $stmt->execute();
                            
                            $mensagem = "Arquivo anexado com sucesso!";
                            $tipo_mensagem = "success";
                        } catch (PDOException $e) {
                            $mensagem = "Erro ao registrar arquivo: " . $e->getMessage();
                            $tipo_mensagem = "danger";
                        }
                    } else {
                        $mensagem = "Erro ao fazer upload do arquivo.";
                        $tipo_mensagem = "danger";
                    }
                }
            }
        } else {
            $mensagem = "Nenhum arquivo foi selecionado ou ocorreu um erro no upload.";
            $tipo_mensagem = "danger";
        }
    }
}

// Buscar inscrição específica para exibir detalhes
if (isset($_GET['id'])) {
    $inscricao_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    $is_exibir_modal = true;
    
    try {
        // Consulta a inscrição
        $stmt = $conn->prepare("SELECT * FROM tb_cad_social WHERE cad_social_id = :id");
        $stmt->bindParam(':id', $inscricao_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $inscricao_atual = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Busca os dependentes
            $stmt = $conn->prepare("SELECT * FROM tb_cad_social_dependentes WHERE cad_social_id = :id");
            $stmt->bindParam(':id', $inscricao_id);
            $stmt->execute();
            $dependentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Busca o histórico/comentários
            $stmt = $conn->prepare("SELECT h.*, u.usuario_nome
                                   FROM tb_cad_social_historico h
                                   LEFT JOIN tb_usuarios_sistema u ON h.cad_social_hist_usuario = u.usuario_id
                                   WHERE h.cad_social_id = :id
                                   ORDER BY h.cad_social_hist_data DESC");
            $stmt->bindParam(':id', $inscricao_id);
            $stmt->execute();
            $comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Busca os arquivos anexados
            $stmt = $conn->prepare("SELECT a.*, u.usuario_nome 
                                   FROM tb_cad_social_arquivos a
                                   LEFT JOIN tb_usuarios_sistema u ON a.cad_social_arq_usuario = u.usuario_id
                                   WHERE a.cad_social_id = :id
                                   ORDER BY a.cad_social_arq_data DESC");
            $stmt->bindParam(':id', $inscricao_id);
            $stmt->execute();
            $arquivos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $mensagem = "Inscrição não encontrada.";
            $tipo_mensagem = "danger";
        }
    } catch (PDOException $e) {
        $mensagem = "Erro ao buscar informações: " . $e->getMessage();
        $tipo_mensagem = "danger";
    }
}

// Consulta básica para paginação
$registros_por_pagina = 10;
$pagina_atual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($pagina_atual - 1) * $registros_por_pagina;

// Contar total de registros para paginação
try {
    $stmt = $conn->query("SELECT COUNT(*) FROM tb_cad_social");
    $total_registros = $stmt->fetchColumn();
    $total_paginas = ceil($total_registros / $registros_por_pagina);
} catch (PDOException $e) {
    $mensagem = "Erro ao contar registros: " . $e->getMessage();
    $tipo_mensagem = "danger";
    $total_registros = 0;
    $total_paginas = 0;
}

// Filtros de busca
$filtro_protocolo = isset($_GET['filtro_protocolo']) ? $_GET['filtro_protocolo'] : '';
$filtro_cpf = isset($_GET['filtro_cpf']) ? $_GET['filtro_cpf'] : '';
$filtro_nome = isset($_GET['filtro_nome']) ? $_GET['filtro_nome'] : '';
$filtro_status = isset($_GET['filtro_status']) ? $_GET['filtro_status'] : '';

$where_clauses = [];
$params = [];

if (!empty($filtro_protocolo)) {
    $where_clauses[] = "cad_social_protocolo LIKE :protocolo";
    $params[':protocolo'] = "%{$filtro_protocolo}%";
}

if (!empty($filtro_cpf)) {
    $filtro_cpf = preg_replace('/[^0-9]/', '', $filtro_cpf);
    $where_clauses[] = "cad_social_cpf LIKE :cpf";
    $params[':cpf'] = "%{$filtro_cpf}%";
}

if (!empty($filtro_nome)) {
    $where_clauses[] = "cad_social_nome LIKE :nome";
    $params[':nome'] = "%{$filtro_nome}%";
}

if (!empty($filtro_status)) {
    $where_clauses[] = "cad_social_status = :status";
    $params[':status'] = $filtro_status;
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Consulta para obter as inscrições com paginação e filtros
try {
    $sql = "SELECT s.*, u.cad_usu_nome FROM tb_cad_social s
            LEFT JOIN tb_cad_usuarios u ON s.cad_usu_id = u.cad_usu_id
            {$where_sql}
            ORDER BY s.cad_social_data_cadastro DESC
            LIMIT :offset, :limit";
    
    $stmt = $conn->prepare($sql);
    
    // Bind dos parâmetros de filtro
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    // Bind dos parâmetros de paginação
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $registros_por_pagina, PDO::PARAM_INT);
    
    $stmt->execute();
    $inscricoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Se estamos filtrando, atualiza a contagem
    if (!empty($where_clauses)) {
        $sql_count = "SELECT COUNT(*) FROM tb_cad_social {$where_sql}";
        $stmt_count = $conn->prepare($sql_count);
        
        foreach ($params as $key => $value) {
            $stmt_count->bindValue($key, $value);
        }
        
        $stmt_count->execute();
        $total_registros = $stmt_count->fetchColumn();
        $total_paginas = ceil($total_registros / $registros_por_pagina);
    }
} catch (PDOException $e) {
    $mensagem = "Erro ao buscar inscrições: " . $e->getMessage();
    $tipo_mensagem = "danger";
    $inscricoes = [];
}

// Função para formatar CPF
function formatarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) != 11) {
        return $cpf;
    }
    return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
}

// Função para formatar data
function formatarData($data) {
    return date('d/m/Y H:i', strtotime($data));
}

// Lista de possíveis status para o dropdown
$lista_status = [
    'PENDENTE DE ANÁLISE',
    'EM ANÁLISE',
    'DOCUMENTAÇÃO PENDENTE',
    'APROVADO',
    'REPROVADO',
    'CANCELADO',
    'EM ESPERA',
    'CONCLUÍDO'
];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Habitação - Sistema Administrativo</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-color: #ecf0f1;
        }
        
        body {
            background-color: #f8f9fa;
            color: #333;
            padding-top: 20px;
        }
        
        .card {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-radius: 0.5rem;
            border: none;
            margin-bottom: 2rem;
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            font-weight: bold;
            border-radius: 0.5rem 0.5rem 0 0 !important;
            padding: 1rem 1.5rem;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #1e2b3a;
            border-color: #1e2b3a;
        }
        
        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .btn-success:hover {
            background-color: #27ae60;
            border-color: #27ae60;
        }
        
        .badge-status {
            font-size: 0.8rem;
            padding: 0.4rem 0.6rem;
            border-radius: 1rem;
        }
        
        .status-pendente {
            background-color: #f39c12;
            color: white;
        }
        
        .status-analise {
            background-color: #3498db;
            color: white;
        }
        
        .status-aprovado {
            background-color: #2ecc71;
            color: white;
        }
        
        .status-reprovado {
            background-color: #e74c3c;
            color: white;
        }
        
        .status-cancelado {
            background-color: #7f8c8d;
            color: white;
        }
        
        .status-espera {
            background-color: #9b59b6;
            color: white;
        }
        
        .status-documentacao {
            background-color: #e67e22;
            color: white;
        }
        
        .status-concluido {
            background-color: #27ae60;
            color: white;
        }
        
        .filter-card {
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        .filter-card .card-header {
            background-color: #34495e;
            font-size: 1rem;
        }
        
        .table-responsive {
            padding: 0;
        }
        
        table.dataTable {
            width: 100% !important;
        }
        
        .details-container {
            max-height: 75vh;
            overflow-y: auto;
        }
        
        .nav-tabs .nav-link {
            color: var(--primary-color);
        }
        
        .nav-tabs .nav-link.active {
            font-weight: bold;
            color: var(--primary-color);
            border-bottom: 3px solid var(--primary-color);
        }
        
        .comment-box {
            background-color: var(--light-color);
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .comment-header {
            font-weight: bold;
            margin-bottom: 0.5rem;
            display: flex;
            justify-content: space-between;
        }
        
        .comment-date {
            font-size: 0.8rem;
            color: #6c757d;
        }
        
        .info-group {
            margin-bottom: 1rem;
        }
        
        .info-label {
            font-weight: bold;
            color: #555;
        }
        
        .info-value {
            margin-bottom: 0.5rem;
        }
        
        .dependente-card {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            border-left: 4px solid var(--secondary-color);
        }
        
        .file-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            margin-bottom: 0.5rem;
            background-color: #f8f9fa;
        }
        
        .file-item:hover {
            background-color: #e9ecef;
        }
        
        .file-info {
            flex-grow: 1;
        }
        
        .file-name {
            font-weight: 500;
            margin-bottom: 0.25rem;
        }
        
        .file-meta {
            font-size: 0.8rem;
            color: #6c757d;
        }
        
        .file-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <?php if (!empty($mensagem)): ?>
            <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show" role="alert">
                <?php echo $mensagem; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        <?php endif; ?>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-home text-primary me-2"></i> Gerenciamento de Cadastros Habitacionais
                </h1>
                <p class="text-muted">Sistema Administrativo - Prefeitura Municipal de Santa Izabel do Oeste</p>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="dashboard.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Voltar para Dashboard
                </a>
            </div>
        </div>
        
        <!-- Filtros -->
        <div class="card filter-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-filter me-2"></i> Filtros de Busca</span>
                <button class="btn btn-sm btn-outline-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilters">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
            <div class="collapse show" id="collapseFilters">
                <div class="card-body">
                    <form method="get" action="" class="row g-3">
                        <div class="col-md-3">
                            <label for="filtro_protocolo" class="form-label">Protocolo</label>
                            <input type="text" class="form-control" id="filtro_protocolo" name="filtro_protocolo" 
                                   value="<?php echo htmlspecialchars($filtro_protocolo); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="filtro_cpf" class="form-label">CPF</label>
                            <input type="text" class="form-control" id="filtro_cpf" name="filtro_cpf" 
                                   value="<?php echo htmlspecialchars($filtro_cpf); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="filtro_nome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="filtro_nome" name="filtro_nome" 
                                   value="<?php echo htmlspecialchars($filtro_nome); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="filtro_status" class="form-label">Status</label>
                            <select class="form-select" id="filtro_status" name="filtro_status">
                                <option value="">Todos</option>
                                <?php foreach ($lista_status as $status): ?>
                                    <option value="<?php echo $status; ?>" <?php echo ($filtro_status == $status) ? 'selected' : ''; ?>>
                                        <?php echo $status; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-1"></i> Buscar
                            </button>
                            <a href="assistencia_habitacao.php" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Limpar Filtros
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Lista de Inscrições -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-list me-2"></i> Cadastros Habitacionais</span>
                <span class="badge bg-primary"><?php echo $total_registros; ?> registros encontrados</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="tabelaInscricoes">
                        <thead class="table-dark">
                            <tr>
                                <th>Protocolo</th>
                                <th>Nome</th>
                                <th>CPF</th>
                                <th>Data Cadastro</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($inscricoes)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">Nenhum registro encontrado</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($inscricoes as $inscricao): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($inscricao['cad_social_protocolo']); ?></td>
                                        <td><?php echo htmlspecialchars($inscricao['cad_social_nome']); ?></td>
                                        <td><?php echo formatarCPF($inscricao['cad_social_cpf']); ?></td>
                                        <td><?php echo formatarData($inscricao['cad_social_data_cadastro']); ?></td>
                                        <td>
                                            <?php 
                                                $status = $inscricao['cad_social_status'];
                                                $status_class = '';
                                                
                                                switch ($status) {
                                                    case 'PENDENTE DE ANÁLISE':
                                                        $status_class = 'status-pendente';
                                                        break;
                                                    case 'EM ANÁLISE':
                                                        $status_class = 'status-analise';
                                                        break;
                                                    case 'DOCUMENTAÇÃO PENDENTE':
                                                        $status_class = 'status-documentacao';
                                                        break;
                                                    case 'APROVADO':
                                                        $status_class = 'status-aprovado';
                                                        break;
                                                    case 'REPROVADO':
                                                        $status_class = 'status-reprovado';
                                                        break;
                                                    case 'CANCELADO':
                                                        $status_class = 'status-cancelado';
                                                        break;
                                                    case 'EM ESPERA':
                                                        $status_class = 'status-espera';
                                                        break;
                                                    case 'CONCLUÍDO':
                                                        $status_class = 'status-concluido';
                                                        break;
                                                    default:
                                                        $status_class = '';
                                                }?>
                                                <span class="badge badge-status <?php echo $status_class; ?>">
                                                    <?php echo $status; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="assistencia_habitacao.php?id=<?php echo $inscricao['cad_social_id']; ?>" 
                                                   class="btn btn-sm btn-primary" title="Ver Detalhes">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-success" title="Alterar Status"
                                                        data-bs-toggle="modal" data-bs-target="#alterarStatusModal"
                                                        data-id="<?php echo $inscricao['cad_social_id']; ?>"
                                                        data-status="<?php echo $inscricao['cad_social_status']; ?>">
                                                    <i class="fas fa-exchange-alt"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-info" title="Adicionar Comentário"
                                                        data-bs-toggle="modal" data-bs-target="#adicionarComentarioModal"
                                                        data-id="<?php echo $inscricao['cad_social_id']; ?>">
                                                    <i class="fas fa-comment"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-warning" title="Anexar Arquivo"
                                                        data-bs-toggle="modal" data-bs-target="#anexarArquivoModal"
                                                        data-id="<?php echo $inscricao['cad_social_id']; ?>">
                                                    <i class="fas fa-paperclip"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <!-- Paginação -->
                    <?php if ($total_paginas > 1): ?>
                        <div class="pagination-container">
                            <ul class="pagination">
                                <?php if ($pagina_atual > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?pagina=1<?php echo !empty($filtro_protocolo) ? '&filtro_protocolo=' . urlencode($filtro_protocolo) : ''; ?><?php echo !empty($filtro_cpf) ? '&filtro_cpf=' . urlencode($filtro_cpf) : ''; ?><?php echo !empty($filtro_nome) ? '&filtro_nome=' . urlencode($filtro_nome) : ''; ?><?php echo !empty($filtro_status) ? '&filtro_status=' . urlencode($filtro_status) : ''; ?>">
                                            Primeira
                                        </a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="?pagina=<?php echo $pagina_atual - 1; ?><?php echo !empty($filtro_protocolo) ? '&filtro_protocolo=' . urlencode($filtro_protocolo) : ''; ?><?php echo !empty($filtro_cpf) ? '&filtro_cpf=' . urlencode($filtro_cpf) : ''; ?><?php echo !empty($filtro_nome) ? '&filtro_nome=' . urlencode($filtro_nome) : ''; ?><?php echo !empty($filtro_status) ? '&filtro_status=' . urlencode($filtro_status) : ''; ?>">
                                            &laquo;
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php
                                $start_page = max(1, $pagina_atual - 2);
                                $end_page = min($total_paginas, $pagina_atual + 2);
                                
                                for ($i = $start_page; $i <= $end_page; $i++):
                                ?>
                                    <li class="page-item <?php echo ($i == $pagina_atual) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?pagina=<?php echo $i; ?><?php echo !empty($filtro_protocolo) ? '&filtro_protocolo=' . urlencode($filtro_protocolo) : ''; ?><?php echo !empty($filtro_cpf) ? '&filtro_cpf=' . urlencode($filtro_cpf) : ''; ?><?php echo !empty($filtro_nome) ? '&filtro_nome=' . urlencode($filtro_nome) : ''; ?><?php echo !empty($filtro_status) ? '&filtro_status=' . urlencode($filtro_status) : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($pagina_atual < $total_paginas): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?pagina=<?php echo $pagina_atual + 1; ?><?php echo !empty($filtro_protocolo) ? '&filtro_protocolo=' . urlencode($filtro_protocolo) : ''; ?><?php echo !empty($filtro_cpf) ? '&filtro_cpf=' . urlencode($filtro_cpf) : ''; ?><?php echo !empty($filtro_nome) ? '&filtro_nome=' . urlencode($filtro_nome) : ''; ?><?php echo !empty($filtro_status) ? '&filtro_status=' . urlencode($filtro_status) : ''; ?>">
                                            &raquo;
                                        </a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="?pagina=<?php echo $total_paginas; ?><?php echo !empty($filtro_protocolo) ? '&filtro_protocolo=' . urlencode($filtro_protocolo) : ''; ?><?php echo !empty($filtro_cpf) ? '&filtro_cpf=' . urlencode($filtro_cpf) : ''; ?><?php echo !empty($filtro_nome) ? '&filtro_nome=' . urlencode($filtro_nome) : ''; ?><?php echo !empty($filtro_status) ? '&filtro_status=' . urlencode($filtro_status) : ''; ?>">
                                            Última
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Modal para visualizar detalhes da inscrição -->
            <div class="modal fade" id="detalhesModal" tabindex="-1" aria-labelledby="detalhesModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="detalhesModalLabel">
                                <i class="fas fa-info-circle me-2"></i> Detalhes da Inscrição
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>
                        <div class="modal-body details-container">
                            <?php if ($inscricao_atual): ?>
                                <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="dados-tab" data-bs-toggle="tab" data-bs-target="#dados-tab-pane" type="button" role="tab" aria-controls="dados-tab-pane" aria-selected="true">
                                            <i class="fas fa-user me-1"></i> Dados Pessoais
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="dependentes-tab" data-bs-toggle="tab" data-bs-target="#dependentes-tab-pane" type="button" role="tab" aria-controls="dependentes-tab-pane" aria-selected="false">
                                            <i class="fas fa-users me-1"></i> Dependentes 
                                            <span class="badge bg-secondary"><?php echo count($dependentes); ?></span>
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="comentarios-tab" data-bs-toggle="tab" data-bs-target="#comentarios-tab-pane" type="button" role="tab" aria-controls="comentarios-tab-pane" aria-selected="false">
                                            <i class="fas fa-comments me-1"></i> Histórico/Comentários 
                                            <span class="badge bg-secondary"><?php echo count($comentarios); ?></span>
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="arquivos-tab" data-bs-toggle="tab" data-bs-target="#arquivos-tab-pane" type="button" role="tab" aria-controls="arquivos-tab-pane" aria-selected="false">
                                            <i class="fas fa-file-alt me-1"></i> Arquivos 
                                            <span class="badge bg-secondary"><?php echo count($arquivos); ?></span>
                                        </button>
                                    </li>
                                </ul>
                                
                                <div class="tab-content" id="myTabContent">
                                    <!-- Aba de Dados Pessoais -->
                                    <div class="tab-pane fade show active" id="dados-tab-pane" role="tabpanel" aria-labelledby="dados-tab" tabindex="0">
                                        <div class="container-fluid">
                                            <div class="row mb-4">
                                                <div class="col-md-6">
                                                    <div class="card">
                                                        <div class="card-header bg-primary text-white">
                                                            <i class="fas fa-id-card me-2"></i> Informações Básicas
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="col-md-12 mb-3">
                                                                    <div class="info-group">
                                                                        <div class="info-label">Protocolo</div>
                                                                        <div class="info-value"><?php echo htmlspecialchars($inscricao_atual['cad_social_protocolo']); ?></div>
                                                                    </div>
                                                                    <div class="info-group">
                                                                        <div class="info-label">Status</div>
                                                                        <div class="info-value">
                                                                            <span class="badge badge-status <?php echo $status_class; ?>">
                                                                                <?php echo htmlspecialchars($inscricao_atual['cad_social_status']); ?>
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="info-group">
                                                                        <div class="info-label">Data de Cadastro</div>
                                                                        <div class="info-value"><?php echo formatarData($inscricao_atual['cad_social_data_cadastro']); ?></div>
                                                                    </div>
                                                                    <div class="info-group">
                                                                        <div class="info-label">Programa de Interesse</div>
                                                                        <div class="info-value"><?php echo htmlspecialchars($inscricao_atual['cad_social_programa_interesse']); ?></div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-6">
                                                    <div class="card">
                                                        <div class="card-header bg-primary text-white">
                                                            <i class="fas fa-user me-2"></i> Dados do Responsável
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="col-md-12 mb-3">
                                                                    <div class="info-group">
                                                                        <div class="info-label">Nome Completo</div>
                                                                        <div class="info-value"><?php echo htmlspecialchars($inscricao_atual['cad_social_nome']); ?></div>
                                                                    </div>
                                                                    <div class="info-group">
                                                                        <div class="info-label">CPF</div>
                                                                        <div class="info-value"><?php echo formatarCPF($inscricao_atual['cad_social_cpf']); ?></div>
                                                                    </div>
                                                                    <div class="info-group">
                                                                        <div class="info-label">RG</div>
                                                                        <div class="info-value"><?php echo $inscricao_atual['cad_social_rg'] ? htmlspecialchars($inscricao_atual['cad_social_rg']) : 'Não informado'; ?></div>
                                                                    </div>
                                                                    <div class="info-group">
                                                                        <div class="info-label">Data de Nascimento</div>
                                                                        <div class="info-value"><?php echo date('d/m/Y', strtotime($inscricao_atual['cad_social_data_nascimento'])); ?></div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="card mb-4">
                                                        <div class="card-header bg-primary text-white">
                                                            <i class="fas fa-info-circle me-2"></i> Informações Adicionais
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <div class="info-group">
                                                                        <div class="info-label">Gênero</div>
                                                                        <div class="info-value"><?php echo htmlspecialchars($inscricao_atual['cad_social_genero']); ?></div>
                                                                    </div>
                                                                    <div class="info-group">
                                                                        <div class="info-label">Escolaridade</div>
                                                                        <div class="info-value"><?php echo htmlspecialchars($inscricao_atual['cad_social_escolaridade']); ?></div>
                                                                    </div>
                                                                    <div class="info-group">
                                                                        <div class="info-label">Estado Civil</div>
                                                                        <div class="info-value"><?php echo htmlspecialchars($inscricao_atual['cad_social_estado_civil']); ?></div>
                                                                    </div>
                                                                    <div class="info-group">
                                                                        <div class="info-label">Possui Deficiência</div>
                                                                        <div class="info-value"><?php echo htmlspecialchars($inscricao_atual['cad_social_deficiencia']); ?></div>
                                                                    </div>
                                                                    <?php if ($inscricao_atual['cad_social_deficiencia'] !== 'NÃO'): ?>
                                                                        <div class="info-group">
                                                                            <div class="info-label">Detalhes da Deficiência</div>
                                                                            <div class="info-value"><?php echo $inscricao_atual['cad_social_deficiencia_fisica_detalhe'] ? htmlspecialchars($inscricao_atual['cad_social_deficiencia_fisica_detalhe']) : 'Não informado'; ?></div>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="card mb-4">
                                                        <div class="card-header bg-primary text-white">
                                                            <i class="fas fa-briefcase me-2"></i> Situação Trabalhista
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <div class="info-group">
                                                                        <div class="info-label">Situação de Trabalho</div>
                                                                        <div class="info-value"><?php echo htmlspecialchars($inscricao_atual['cad_social_situacao_trabalho']); ?></div>
                                                                    </div>
                                                                    <?php if ($inscricao_atual['cad_social_situacao_trabalho'] != 'DESEMPREGADO'): ?>
                                                                        <div class="info-group">
                                                                            <div class="info-label">Profissão</div>
                                                                            <div class="info-value"><?php echo $inscricao_atual['cad_social_profissao'] ? htmlspecialchars($inscricao_atual['cad_social_profissao']) : 'Não informado'; ?></div>
                                                                        </div>
                                                                        <div class="info-group">
                                                                            <div class="info-label">Empregador</div>
                                                                            <div class="info-value"><?php echo $inscricao_atual['cad_social_empregador'] ? htmlspecialchars($inscricao_atual['cad_social_empregador']) : 'Não informado'; ?></div>
                                                                        </div>
                                                                        <div class="info-group">
                                                                            <div class="info-label">Cargo</div>
                                                                            <div class="info-value"><?php echo $inscricao_atual['cad_social_cargo'] ? htmlspecialchars($inscricao_atual['cad_social_cargo']) : 'Não informado'; ?></div>
                                                                        </div>
                                                                        <div class="info-group">
                                                                            <div class="info-label">Tempo de Serviço</div>
                                                                            <div class="info-value"><?php echo $inscricao_atual['cad_social_tempo_servico'] ? htmlspecialchars($inscricao_atual['cad_social_tempo_servico']) : 'Não informado'; ?></div>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-6">
                                                    <div class="card mb-4">
                                                        <div class="card-header bg-primary text-white">
                                                            <i class="fas fa-home me-2"></i> Endereço
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <div class="info-group">
                                                                        <div class="info-label">Tipo de Moradia</div>
                                                                        <div class="info-value"><?php echo htmlspecialchars($inscricao_atual['cad_social_tipo_moradia']); ?></div>
                                                                    </div>
                                                                    <div class="info-group">
                                                                        <div class="info-label">Situação da Propriedade</div>
                                                                        <div class="info-value"><?php echo htmlspecialchars($inscricao_atual['cad_social_situacao_propriedade']); ?></div>
                                                                    </div>
                                                                    <?php if ($inscricao_atual['cad_social_situacao_propriedade'] == 'ALUGADA'): ?>
                                                                        <div class="info-group">
                                                                            <div class="info-label">Valor do Aluguel</div>
                                                                            <div class="info-value">R$ <?php echo number_format($inscricao_atual['cad_social_valor_aluguel'], 2, ',', '.'); ?></div>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                    <div class="info-group">
                                                                        <div class="info-label">Endereço Completo</div>
                                                                        <div class="info-value">
                                                                            <?php echo htmlspecialchars($inscricao_atual['cad_social_rua']); ?>, 
                                                                            <?php echo htmlspecialchars($inscricao_atual['cad_social_numero']); ?>
                                                                            <?php echo $inscricao_atual['cad_social_complemento'] ? ', ' . htmlspecialchars($inscricao_atual['cad_social_complemento']) : ''; ?><br>
                                                                            <?php echo htmlspecialchars($inscricao_atual['cad_social_bairro']); ?><br>
                                                                            <?php echo htmlspecialchars($inscricao_atual['cad_social_cidade']); ?> - 
                                                                            CEP: <?php echo substr($inscricao_atual['cad_social_cep'], 0, 5) . '-' . substr($inscricao_atual['cad_social_cep'], 5); ?>
                                                                        </div>
                                                                    </div>
                                                                    <?php if ($inscricao_atual['cad_social_ponto_referencia']): ?>
                                                                        <div class="info-group">
                                                                            <div class="info-label">Ponto de Referência</div>
                                                                            <div class="info-value"><?php echo htmlspecialchars($inscricao_atual['cad_social_ponto_referencia']); ?></div>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="card mb-4">
                                                        <div class="card-header bg-primary text-white">
                                                            <i class="fas fa-phone me-2"></i> Contato
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <?php if ($inscricao_atual['cad_social_telefone']): ?>
                                                                        <div class="info-group">
                                                                            <div class="info-label">Telefone</div>
                                                                            <div class="info-value">
                                                                                <?php 
                                                                                    $telefone = $inscricao_atual['cad_social_telefone'];
                                                                                    if (strlen($telefone) == 10) {
                                                                                        echo '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 4) . '-' . substr($telefone, 6);
                                                                                    } elseif (strlen($telefone) == 11) {
                                                                                        echo '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 5) . '-' . substr($telefone, 7);
                                                                                    } else {
                                                                                        echo $telefone;
                                                                                    }
                                                                                ?>
                                                                            </div>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                    <div class="info-group">
                                                                        <div class="info-label">Celular</div>
                                                                        <div class="info-value">
                                                                            <?php 
                                                                                $celular = $inscricao_atual['cad_social_celular'];
                                                                                if (strlen($celular) == 11) {
                                                                                    echo '(' . substr($celular, 0, 2) . ') ' . substr($celular, 2, 5) . '-' . substr($celular, 7);
                                                                                } else {
                                                                                    echo $celular;
                                                                                }
                                                                            ?>
                                                                        </div>
                                                                    </div>
                                                                    <div class="info-group">
                                                                        <div class="info-label">E-mail</div>
                                                                        <div class="info-value"><?php echo htmlspecialchars($inscricao_atual['cad_social_email']); ?></div>
                                                                    </div>
                                                                    <div class="info-group">
                                                                        <div class="info-label">Autoriza notificações</div>
                                                                        <div class="info-value"><?php echo $inscricao_atual['cad_social_autoriza_email'] ? 'Sim' : 'Não'; ?></div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <?php if ($inscricao_atual['cad_social_estado_civil'] == 'CASADO(A)' || $inscricao_atual['cad_social_estado_civil'] == 'UNIÃO ESTÁVEL/AMASIADO(A)'): ?>
                                                        <div class="card mb-4">
                                                            <div class="card-header bg-primary text-white">
                                                                <i class="fas fa-user-friends me-2"></i> Informações do Cônjuge
                                                            </div>
                                                            <div class="card-body">
                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <div class="info-group">
                                                                            <div class="info-label">Nome do Cônjuge</div>
                                                                            <div class="info-value"><?php echo $inscricao_atual['cad_social_conjuge_nome'] ? htmlspecialchars($inscricao_atual['cad_social_conjuge_nome']) : 'Não informado'; ?></div>
                                                                        </div>
                                                                        <?php if ($inscricao_atual['cad_social_conjuge_cpf']): ?>
                                                                            <div class="info-group">
                                                                                <div class="info-label">CPF do Cônjuge</div>
                                                                                <div class="info-value"><?php echo formatarCPF($inscricao_atual['cad_social_conjuge_cpf']); ?></div>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                        <?php if ($inscricao_atual['cad_social_conjuge_data_nascimento']): ?>
                                                                            <div class="info-group">
                                                                                <div class="info-label">Data de Nascimento do Cônjuge</div>
                                                                                <div class="info-value"><?php echo date('d/m/Y', strtotime($inscricao_atual['cad_social_conjuge_data_nascimento'])); ?></div>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                        <div class="info-group">
                                                                            <div class="info-label">Cônjuge possui renda</div>
                                                                            <div class="info-value"><?php echo $inscricao_atual['cad_social_conjuge_renda'] ? htmlspecialchars($inscricao_atual['cad_social_conjuge_renda']) : 'Não informado'; ?></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Aba de Dependentes -->
                                    <div class="tab-pane fade" id="dependentes-tab-pane" role="tabpanel" aria-labelledby="dependentes-tab" tabindex="0">
                                        <div class="container-fluid">
                                            <h5 class="mb-3">
                                                <i class="fas fa-users me-2"></i> Dependentes
                                                <span class="badge bg-primary"><?php echo count($dependentes); ?></span>
                                            </h5>
                                            
                                            <?php if (empty($dependentes)): ?>
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle me-2"></i> Não há dependentes cadastrados para esta inscrição.
                                                </div>
                                            <?php else: ?>
                                                <?php foreach ($dependentes as $index => $dependente): ?>
                                                    <div class="dependente-card">
                                                        <h5>
                                                            <i class="fas fa-user me-2"></i> 
                                                            Dependente <?php echo $index + 1; ?>: 
                                                            <?php echo htmlspecialchars($dependente['cad_social_dependente_nome']); ?>
                                                        </h5>
                                                        <div class="row mt-3">
                                                            <div class="col-md-6">
                                                                <div class="info-group">
                                                                    <div class="info-label">Data de Nascimento</div>
                                                                    <div class="info-value">
                                                                        <?php echo date('d/m/Y', strtotime($dependente['cad_social_dependente_data_nascimento'])); ?>
                                                                    </div>
                                                                </div>
                                                                <?php if ($dependente['cad_social_dependente_cpf']): ?>
                                                                    <div class="info-group">
                                                                        <div class="info-label">CPF</div>
                                                                        <div class="info-value">
                                                                            <?php echo formatarCPF($dependente['cad_social_dependente_cpf']); ?>
                                                                        </div>
                                                                    </div>
                                                                <?php endif; ?>
                                                                <?php if ($dependente['cad_social_dependente_rg']): ?>
                                                                    <div class="info-group">
                                                                        <div class="info-label">RG</div>
                                                                        <div class="info-value">
                                                                            <?php echo htmlspecialchars($dependente['cad_social_dependente_rg']); ?>
                                                                        </div>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="info-group">
                                                                    <div class="info-label">Possui Deficiência</div>
                                                                    <div class="info-value">
                                                                        <?php echo htmlspecialchars($dependente['cad_social_dependente_deficiencia']); ?>
                                                                    </div>
                                                                </div>
                                                                <div class="info-group">
                                                                    <div class="info-label">Possui Renda</div>
                                                                    <div class="info-value">
                                                                        <?php echo htmlspecialchars($dependente['cad_social_dependente_renda']); ?>
                                                                    </div>
                                                                </div>
                                                                <?php if ($dependente['cad_social_dependente_documentos']): ?>
                                                                    <div class="info-group">
                                                                        <div class="info-label">Documento Anexado</div>
                                                                        <div class="info-value">
                                                                            <a href="../uploads/habitacao/<?php echo htmlspecialchars($dependente['cad_social_dependente_documentos']); ?>" 
                                                                               class="btn btn-sm btn-outline-primary" target="_blank">
                                                                                <i class="fas fa-file-download"></i> Ver Documento
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Aba de Histórico/Comentários -->
                                    <div class="tab-pane fade" id="comentarios-tab-pane" role="tabpanel" aria-labelledby="comentarios-tab" tabindex="0">
                                        <div class="container-fluid">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h5>
                                                    <i class="fas fa-comments me-2"></i> Histórico e Comentários
                                                </h5>
                                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" 
                                                        data-bs-target="#adicionarComentarioModal" data-id="<?php echo $inscricao_atual['cad_social_id']; ?>">
                                                    <i class="fas fa-plus me-1"></i> Adicionar Comentário
                                                </button>
                                            </div>
                                            
                                            <?php if (empty($comentarios)): ?>
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle me-2"></i> Não há históricos ou comentários para esta inscrição.
                                                </div>
                                            <?php else: ?>
                                                <?php foreach ($comentarios as $comentario): ?>
                                                    <div class="comment-box">
                                                        <div class="comment-header">
                                                            <span>
                                                                <i class="fas fa-user-circle me-1"></i> 
                                                                <?php echo htmlspecialchars($comentario['usuario_nome'] ?: 'Sistema'); ?>
                                                            </span>
                                                            <span class="comment-date">
                                                                <i class="fas fa-clock me-1"></i>
                                                                <?php echo formatarData($comentario['cad_social_hist_data']); ?>
                                                            </span>
                                                        </div>
                                                        <div class="comment-title">
                                                            <strong>Ação: <?php echo htmlspecialchars($comentario['cad_social_hist_acao']); ?></strong>
                                                        </div>
                                                        <div class="comment-content mt-2">
                                                            <?php echo nl2br(htmlspecialchars($comentario['cad_social_hist_observacao'])); ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Aba de Arquivos -->
                                    <div class="tab-pane fade" id="arquivos-tab-pane" role="tabpanel" aria-labelledby="arquivos-tab" tabindex="0">
                                        <div class="container-fluid">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h5>
                                                    <i class="fas fa-file-alt me-2"></i> Arquivos
                                                </h5>
                                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" 
                                                        data-bs-target="#anexarArquivoModal" data-id="<?php echo $inscricao_atual['cad_social_id']; ?>">
                                                    <i class="fas fa-paperclip me-1"></i> Anexar Arquivo
                                                </button>
                                            </div>
                                            
                                            <!-- Arquivos do Sistema -->
                                            <h6 class="mt-4 mb-3"><i class="fas fa-folder me-2"></i> Arquivos do Sistema</h6>
                                            <?php if (empty($arquivos)): ?>
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle me-2"></i> Não há arquivos do sistema anexados a esta inscrição.
                                                </div>
                                            <?php else: ?>
                                                <div class="file-list">
                                                    <?php foreach ($arquivos as $arquivo): ?>
                                                        <div class="file-item">
                                                            <div class="file-info">
                                                                <div class="file-name">
                                                                    <i class="fas fa-file me-1"></i>
                                                                    <?php echo htmlspecialchars($arquivo['cad_social_arq_nome']); ?>
                                                                </div>
                                                                <div class="file-meta">
                                                                    <span><i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($arquivo['usersystem_nome'] ?: 'Sistema'); ?></span>
                                                                    <span class="ms-2"><i class="fas fa-calendar me-1"></i> <?php echo formatarData($arquivo['cad_social_arq_data']); ?></span>
                                                                    <?php if ($arquivo['cad_social_arq_descricao']): ?>
                                                                        <span class="ms-2"><i class="fas fa-info-circle me-1"></i> <?php echo htmlspecialchars($arquivo['cad_social_arq_descricao']); ?></span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                            <div class="file-actions">
                                                                <a href="../uploads/habitacao/sistema/<?php echo htmlspecialchars($arquivo['cad_social_arq_nome']); ?>" 
                                                                   class="btn btn-sm btn-primary" target="_blank" title="Visualizar">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                                <a href="../uploads/habitacao/sistema/<?php echo htmlspecialchars($arquivo['cad_social_arq_nome']); ?>" 
                                                                   class="btn btn-sm btn-success" download title="Baixar">
                                                                    <i class="fas fa-download"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <!-- Arquivos do Cidadão -->
                                            <h6 class="mt-4 mb-3"><i class="fas fa-folder-open me-2"></i> Arquivos do Cidadão</h6>
                                            <div class="file-list">
                                                <?php if ($inscricao_atual['cad_social_cpf_documento']): ?>
                                                    <div class="file-item">
                                                        <div class="file-info">
                                                            <div class="file-name">
                                                                <i class="fas fa-id-card me-1"></i>
                                                                Documento de CPF
                                                            </div>
                                                            <div class="file-meta">
                                                                <span><i class="fas fa-user me-1"></i> Cidadão</span>
                                                                <span class="ms-2"><i class="fas fa-calendar me-1"></i> <?php echo formatarData($inscricao_atual['cad_social_data_cadastro']); ?></span>
                                                            </div>
                                                        </div>
                                                        <div class="file-actions">
                                                            <a href="../uploads/habitacao/<?php echo htmlspecialchars($inscricao_atual['cad_social_cpf_documento']); ?>" 
                                                               class="btn btn-sm btn-primary" target="_blank" title="Visualizar">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="../uploads/habitacao/<?php echo htmlspecialchars($inscricao_atual['cad_social_cpf_documento']); ?>" 
                                                               class="btn btn-sm btn-success" download title="Baixar">
                                                                <i class="fas fa-download"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if ($inscricao_atual['cad_social_escolaridade_documento']): ?>
                                                    <div class="file-item">
                                                        <div class="file-info">
                                                            <div class="file-name">
                                                                <i class="fas fa-graduation-cap me-1"></i>
                                                                Comprovante de Escolaridade
                                                            </div>
                                                            <div class="file-meta">
                                                                <span><i class="fas fa-user me-1"></i> Cidadão</span>
                                                                <span class="ms-2"><i class="fas fa-calendar me-1"></i> <?php echo formatarData($inscricao_atual['cad_social_data_cadastro']); ?></span>
                                                            </div>
                                                        </div>
                                                        <div class="file-actions">
                                                            <a href="../uploads/habitacao/<?php echo htmlspecialchars($inscricao_atual['cad_social_escolaridade_documento']); ?>" 
                                                               class="btn btn-sm btn-primary" target="_blank" title="Visualizar">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="../uploads/habitacao/<?php echo htmlspecialchars($inscricao_atual['cad_social_escolaridade_documento']); ?>" 
                                                               class="btn btn-sm btn-success" download title="Baixar">
                                                                <i class="fas fa-download"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if ($inscricao_atual['cad_social_viuvo_documento']): ?>
                                                    <div class="file-item">
                                                        <div class="file-info">
                                                            <div class="file-name">
                                                                <i class="fas fa-file-alt me-1"></i>
                                                                Certidão de Óbito
                                                            </div>
                                                            <div class="file-meta">
                                                                <span><i class="fas fa-user me-1"></i> Cidadão</span>
                                                                <span class="ms-2"><i class="fas fa-calendar me-1"></i> <?php echo formatarData($inscricao_atual['cad_social_data_cadastro']); ?></span>
                                                            </div>
                                                        </div>
                                                        <div class="file-actions">
                                                            <a href="../uploads/habitacao/<?php echo htmlspecialchars($inscricao_atual['cad_social_viuvo_documento']); ?>" 
                                                               class="btn btn-sm btn-primary" target="_blank" title="Visualizar">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="../uploads/habitacao/<?php echo htmlspecialchars($inscricao_atual['cad_social_viuvo_documento']); ?>" 
                                                               class="btn btn-sm btn-success" download title="Baixar">
                                                                <i class="fas fa-download"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if ($inscricao_atual['cad_social_laudo_deficiencia']): ?>
                                                    <div class="file-item">
                                                        <div class="file-info">
                                                            <div class="file-name">
                                                                <i class="fas fa-file-medical me-1"></i>
                                                                Laudo Médico de Deficiência
                                                            </div>
                                                            <div class="file-meta">
                                                                <span><i class="fas fa-user me-1"></i> Cidadão</span>
                                                                <span class="ms-2"><i class="fas fa-calendar me-1"></i> <?php echo formatarData($inscricao_atual['cad_social_data_cadastro']); ?></span>
                                                            </div>
                                                        </div>
                                                        <div class="file-actions">
                                                            <a href="../uploads/habitacao/<?php echo htmlspecialchars($inscricao_atual['cad_social_laudo_deficiencia']); ?>" 
                                                               class="btn btn-sm btn-primary" target="_blank" title="Visualizar">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="../uploads/habitacao/<?php echo htmlspecialchars($inscricao_atual['cad_social_laudo_deficiencia']); ?>" 
                                                               class="btn btn-sm btn-success" download title="Baixar">
                                                                <i class="fas fa-download"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if ($inscricao_atual['cad_social_conjuge_comprovante_renda']): ?>
                                                    <div class="file-item">
                                                        <div class="file-info">
                                                            <div class="file-name">
                                                                <i class="fas fa-money-bill-wave me-1"></i>
                                                                Comprovante de Renda do Cônjuge
                                                            </div>
                                                            <div class="file-meta">
                                                                <span><i class="fas fa-user me-1"></i> Cidadão</span>
                                                                <span class="ms-2"><i class="fas fa-calendar me-1"></i> <?php echo formatarData($inscricao_atual['cad_social_data_cadastro']); ?></span>
                                                            </div>
                                                        </div>
                                                        <div class="file-actions">
                                                            <a href="../uploads/habitacao/<?php echo htmlspecialchars($inscricao_atual['cad_social_conjuge_comprovante_renda']); ?>" 
                                                               class="btn btn-sm btn-primary" target="_blank" title="Visualizar">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="../uploads/habitacao/<?php echo htmlspecialchars($inscricao_atual['cad_social_conjuge_comprovante_renda']); ?>" 
                                                               class="btn btn-sm btn-success" download title="Baixar">
                                                                <i class="fas fa-download"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if ($inscricao_atual['cad_social_carteira_trabalho']): ?>
                                                    <div class="file-item">
                                                        <div class="file-info">
                                                            <div class="file-name">
                                                                <i class="fas fa-id-badge me-1"></i>
                                                                Carteira de Trabalho
                                                            </div>
                                                            <div class="file-meta">
                                                                <span><i class="fas fa-user me-1"></i> Cidadão</span>
                                                                <span class="ms-2"><i class="fas fa-calendar me-1"></i> <?php echo formatarData($inscricao_atual['cad_social_data_cadastro']); ?></span>
                                                            </div>
                                                        </div>
                                                        <div class="file-actions">
                                                            <a href="../uploads/habitacao/<?php echo htmlspecialchars($inscricao_atual['cad_social_carteira_trabalho']); ?>" 
                                                               class="btn btn-sm btn-primary" target="_blank" title="Visualizar">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="../uploads/habitacao/<?php echo htmlspecialchars($inscricao_atual['cad_social_carteira_trabalho']); ?>" 
                                                               class="btn btn-sm btn-success" download title="Baixar">
                                                                <i class="fas fa-download"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#alterarStatusModal"
                                    data-id="<?php echo $inscricao_atual['cad_social_id']; ?>"
                                    data-status="<?php echo $inscricao_atual['cad_social_status']; ?>">
                                <i class="fas fa-exchange-alt me-1"></i> Alterar Status
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Modal para alterar status -->
            <div class="modal fade" id="alterarStatusModal" tabindex="-1" aria-labelledby="alterarStatusModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title" id="alterarStatusModalLabel">
                                <i class="fas fa-exchange-alt me-2"></i> Alterar Status da Inscrição
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>
                        <form method="post" action="">
                            <div class="modal-body">
                                <input type="hidden" name="acao" value="atualizar_status">
                                <input type="hidden" name="inscricao_id" id="status_inscricao_id">
                                
                                <div class="mb-3">
                                    <label for="status_atual" class="form-label">Status Atual</label>
                                    <input type="text" class="form-control" id="status_atual" readonly>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="novo_status" class="form-label">Novo Status</label>
                                    <select class="form-select" id="novo_status" name="novo_status" required>
                                        <?php foreach ($lista_status as $status): ?>
                                            <option value="<?php echo $status; ?>"><?php echo $status; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="observacao" class="form-label">Observação</label>
                                    <textarea class="form-control" id="observacao" name="observacao" rows="3" placeholder="Descreva o motivo da alteração de status..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save me-1"></i> Salvar Alteração
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Modal para adicionar comentário -->
            <div class="modal fade" id="adicionarComentarioModal" tabindex="-1" aria-labelledby="adicionarComentarioModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title" id="adicionarComentarioModalLabel">
                                <i class="fas fa-comment me-2"></i> Adicionar Comentário
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>
                        <form method="post" action="">
                            <div class="modal-body">
                                <input type="hidden" name="acao" value="adicionar_comentario">
                                <input type="hidden" name="inscricao_id" id="comentario_inscricao_id">
                                
                                <div class="mb-3">
                                    <label for="comentario" class="form-label">Comentário</label>
                                    <textarea class="form-control" id="comentario" name="comentario" rows="5" required
                                              placeholder="Digite seu comentário ou observação..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-info">
                                    <i class="fas fa-save me-1"></i> Salvar Comentário
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Modal para anexar arquivo -->
            <div class="modal fade" id="anexarArquivoModal" tabindex="-1" aria-labelledby="anexarArquivoModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-warning text-dark">
                            <h5 class="modal-title" id="anexarArquivoModalLabel">
                                <i class="fas fa-paperclip me-2"></i> Anexar Arquivo
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>
                        <form method="post" action="" enctype="multipart/form-data">
                            <div class="modal-body">
                                <input type="hidden" name="acao" value="anexar_arquivo">
                                <input type="hidden" name="inscricao_id" id="arquivo_inscricao_id">
                                
                                <div class="mb-3">
                                    <label for="arquivo" class="form-label">Selecione o Arquivo</label>
                                    <input type="file" class="form-control" id="arquivo" name="arquivo" required>
                                    <div class="form-text">Formatos aceitos: PDF, JPG, JPEG, PNG (Máx: 5MB)</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="descricao_arquivo" class="form-label">Descrição do Arquivo</label>
                                    <textarea class="form-control" id="descricao_arquivo" name="descricao_arquivo" rows="3"
                                              placeholder="Descreva o conteúdo deste arquivo..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-upload me-1"></i> Enviar Arquivo
                                    </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Inicializar DataTables
            $('#tabelaInscricoes').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.1/i18n/pt-BR.json'
                },
                paging: false,
                searching: false,
                info: false
            });
            
            // Mostrar modal de detalhes se necessário
            <?php if ($is_exibir_modal): ?>
                var detalhesModal = new bootstrap.Modal(document.getElementById('detalhesModal'));
                detalhesModal.show();
            <?php endif; ?>
            
            // Configurar modal de alterar status
            $('#alterarStatusModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var id = button.data('id');
                var status = button.data('status');
                
                var modal = $(this);
                modal.find('#status_inscricao_id').val(id);
                modal.find('#status_atual').val(status);
                modal.find('#novo_status').val(status);
            });
            
            // Configurar modal de adicionar comentário
            $('#adicionarComentarioModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var id = button.data('id');
                
                var modal = $(this);
                modal.find('#comentario_inscricao_id').val(id);
            });
            
            // Configurar modal de anexar arquivo
            $('#anexarArquivoModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var id = button.data('id');
                
                var modal = $(this);
                modal.find('#arquivo_inscricao_id').val(id);
            });
            
            // Auto-fechar alertas após 5 segundos
            setTimeout(function() {
                $('.alert-dismissible').alert('close');
            }, 5000);
            
            // Mostrar carregamento durante o envio de formulários
            $('form').on('submit', function() {
                var submitButton = $(this).find('button[type="submit"]');
                var originalHTML = submitButton.html();
                
                submitButton.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processando...');
                submitButton.prop('disabled', true);
                
                // Restaurar botão após 10 segundos (caso algo dê errado)
                setTimeout(function() {
                    submitButton.html(originalHTML);
                    submitButton.prop('disabled', false);
                }, 10000);
            });
            
            // Confirmar alteração de status
            $('form[action=""][name="acao"][value="atualizar_status"]').on('submit', function(e) {
                if (!confirm('Tem certeza que deseja alterar o status desta inscrição?')) {
                    e.preventDefault();
                }
            });
            
            // Máscara para CPF nos filtros
            $('#filtro_cpf').on('input', function() {
                var v = this.value;
                v = v.replace(/\D/g, '');
                v = v.replace(/(\d{3})(\d)/, '$1.$2');
                v = v.replace(/(\d{3})(\d)/, '$1.$2');
                v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                this.value = v;
            });
        });
    </script>
</body>
</html>