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

// Inclui configurações comuns
require_once "includes/config_comum.php";

// Definir variáveis específicas da página
$page_header = "Gerenciamento de Atletas";
$acao = $_GET['acao'] ?? 'listar';
$atleta_id = intval($_GET['id'] ?? 0);
$mensagem = "";
$tipo_mensagem = "";

// Função para sanitizar inputs
function sanitizeInput($data) {
    if (is_null($data) || $data === '') {
        return null;
    }
    return trim(htmlspecialchars(stripslashes($data)));
}

// Função para validar CPF
function validarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }
    
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

// Processamento de ações
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $acao = $_POST['acao'] ?? '';
    
    if ($acao === 'salvar') {
        $dados = [
            'nome' => sanitizeInput($_POST['nome']),
            'cpf' => preg_replace('/[^0-9]/', '', $_POST['cpf']),
            'rg' => sanitizeInput($_POST['rg']),
            'data_nascimento' => $_POST['data_nascimento'],
            'genero' => $_POST['genero'],
            'telefone' => preg_replace('/[^0-9]/', '', $_POST['telefone']),
            'celular' => preg_replace('/[^0-9]/', '', $_POST['celular']),
            'email' => sanitizeInput($_POST['email']),
            'endereco' => sanitizeInput($_POST['endereco']),
            'bairro' => sanitizeInput($_POST['bairro']),
            'cidade' => sanitizeInput($_POST['cidade']),
            'cep' => preg_replace('/[^0-9]/', '', $_POST['cep']),
            'modalidade_principal' => sanitizeInput($_POST['modalidade_principal']),
            'categoria' => $_POST['categoria'],
            'responsavel_nome' => sanitizeInput($_POST['responsavel_nome']),
            'responsavel_telefone' => preg_replace('/[^0-9]/', '', $_POST['responsavel_telefone']),
            'observacoes' => sanitizeInput($_POST['observacoes']),
            'status' => $_POST['status'] ?? 'ATIVO'
        ];
        
        $atleta_id = intval($_POST['atleta_id'] ?? 0);
        
        // Validações
        $erros = [];
        
        if (empty($dados['nome'])) {
            $erros[] = "Nome é obrigatório";
        }
        
        if (empty($dados['cpf']) || !validarCPF($dados['cpf'])) {
            $erros[] = "CPF inválido";
        }
        
        if (empty($dados['data_nascimento'])) {
            $erros[] = "Data de nascimento é obrigatória";
        }
        
        if (empty($dados['celular'])) {
            $erros[] = "Celular é obrigatório";
        }
        
        if (!empty($dados['email']) && !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            $erros[] = "E-mail inválido";
        }
        
        // Verificar se é menor de idade
        if (!empty($dados['data_nascimento'])) {
            $data_nasc = new DateTime($dados['data_nascimento']);
            $hoje = new DateTime();
            $idade = $hoje->diff($data_nasc)->y;
            
            if ($idade < 18 && empty($dados['responsavel_nome'])) {
                $erros[] = "Nome do responsável é obrigatório para menores de idade";
            }
        }
        
        if (empty($erros)) {
            try {
                $conn->beginTransaction();
                
                if ($atleta_id > 0) {
                    // Atualizar - CORREÇÃO: usar array associativo para bindValue
                    $sql = "UPDATE tb_atletas SET 
                            atleta_nome = :nome,
                            atleta_cpf = :cpf,
                            atleta_rg = :rg,
                            atleta_data_nascimento = :data_nascimento,
                            atleta_genero = :genero,
                            atleta_telefone = :telefone,
                            atleta_celular = :celular,
                            atleta_email = :email,
                            atleta_endereco = :endereco,
                            atleta_bairro = :bairro,
                            atleta_cidade = :cidade,
                            atleta_cep = :cep,
                            atleta_modalidade_principal = :modalidade_principal,
                            atleta_categoria = :categoria,
                            atleta_responsavel_nome = :responsavel_nome,
                            atleta_responsavel_telefone = :responsavel_telefone,
                            atleta_observacoes = :observacoes,
                            atleta_status = :status
                            WHERE atleta_id = :atleta_id";
                    
                    $stmt = $conn->prepare($sql);
                    
                    // Bind de todos os valores
                    foreach ($dados as $key => $value) {
                        $stmt->bindValue(':' . $key, $value);
                    }
                    $stmt->bindValue(':atleta_id', $atleta_id, PDO::PARAM_INT);
                    
                    $acao_historico = "Atleta atualizado";
                } else {
                    // Inserir - CORREÇÃO: usar bindValue em vez de bindParam
                    $sql = "INSERT INTO tb_atletas (
                            atleta_nome, atleta_cpf, atleta_rg, atleta_data_nascimento,
                            atleta_genero, atleta_telefone, atleta_celular, atleta_email,
                            atleta_endereco, atleta_bairro, atleta_cidade, atleta_cep,
                            atleta_modalidade_principal, atleta_categoria, atleta_responsavel_nome,
                            atleta_responsavel_telefone, atleta_observacoes, atleta_status,
                            atleta_cadastrado_por
                        ) VALUES (
                            :nome, :cpf, :rg, :data_nascimento,
                            :genero, :telefone, :celular, :email,
                            :endereco, :bairro, :cidade, :cep,
                            :modalidade_principal, :categoria, :responsavel_nome,
                            :responsavel_telefone, :observacoes, :status,
                            :cadastrado_por
                        )";
                    
                    $stmt = $conn->prepare($sql);
                    
                    // Bind de todos os valores
                    foreach ($dados as $key => $value) {
                        $stmt->bindValue(':' . $key, $value);
                    }
                    $stmt->bindValue(':cadastrado_por', $usuario_id, PDO::PARAM_INT);
                    
                    $acao_historico = "Atleta cadastrado";
                }
                
                $stmt->execute();
                
                if ($atleta_id == 0) {
                    $atleta_id = $conn->lastInsertId();
                }
                
                // Registrar no histórico
                $stmt_hist = $conn->prepare("INSERT INTO tb_esporte_historico (historico_tipo, historico_referencia_id, historico_acao, historico_detalhes, historico_usuario_id) VALUES (?, ?, ?, ?, ?)");
                $stmt_hist->execute(['ATLETA', $atleta_id, $acao_historico, "Atleta: " . $dados['nome'], $usuario_id]);
                
                $conn->commit();
                
                $mensagem = $acao_historico . " com sucesso!";
                $tipo_mensagem = "success";
                $acao = 'listar';
                
            } catch (PDOException $e) {
                $conn->rollBack();
                $mensagem = "Erro ao salvar atleta: " . $e->getMessage();
                $tipo_mensagem = "error";
                error_log("Erro ao salvar atleta: " . $e->getMessage());
            }
        } else {
            $mensagem = implode("<br>", $erros);
            $tipo_mensagem = "error";
        }
    }
}

// Buscar dados para edição
$atleta_atual = null;
if ($acao === 'editar' && $atleta_id > 0) {
    try {
        $stmt = $conn->prepare("SELECT * FROM tb_atletas WHERE atleta_id = ?");
        $stmt->execute([$atleta_id]);
        
        if ($stmt->rowCount() > 0) {
            $atleta_atual = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $mensagem = "Atleta não encontrado.";
            $tipo_mensagem = "error";
            $acao = 'listar';
        }
    } catch (PDOException $e) {
        $mensagem = "Erro ao buscar atleta: " . $e->getMessage();
        $tipo_mensagem = "error";
        $acao = 'listar';
        error_log("Erro ao buscar atleta: " . $e->getMessage());
    }
}

// Buscar lista de atletas para exibição
$atletas = [];
$total_registros = 0;
$total_paginas = 1;

if ($acao === 'listar') {
    // Parâmetros de paginação e filtros
    $registros_por_pagina = 15;
    $pagina_atual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
    $offset = ($pagina_atual - 1) * $registros_por_pagina;
    
    // Filtros de busca
    $filtros = [
        'nome' => sanitizeInput($_GET['filtro_nome'] ?? ''),
        'cpf' => preg_replace('/[^0-9]/', '', $_GET['filtro_cpf'] ?? ''),
        'modalidade' => sanitizeInput($_GET['filtro_modalidade'] ?? ''),
        'categoria' => sanitizeInput($_GET['filtro_categoria'] ?? ''),
        'status' => sanitizeInput($_GET['filtro_status'] ?? '')
    ];
    
    // Construir query com filtros
    $where_conditions = [];
    $params = [];
    
    foreach ($filtros as $key => $value) {
        if (!empty($value)) {
            switch ($key) {
                case 'nome':
                    $where_conditions[] = "atleta_nome LIKE ?";
                    $params[] = "%{$value}%";
                    break;
                case 'cpf':
                    $where_conditions[] = "atleta_cpf LIKE ?";
                    $params[] = "%{$value}%";
                    break;
                case 'modalidade':
                    $where_conditions[] = "atleta_modalidade_principal LIKE ?";
                    $params[] = "%{$value}%";
                    break;
                case 'categoria':
                    $where_conditions[] = "atleta_categoria = ?";
                    $params[] = $value;
                    break;
                case 'status':
                    $where_conditions[] = "atleta_status = ?";
                    $params[] = $value;
                    break;
            }
        }
    }
    
    $where_sql = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
    
    try {
        // Contar total de registros
        $count_sql = "SELECT COUNT(*) as total FROM tb_atletas {$where_sql}";
        $stmt = $conn->prepare($count_sql);
        $stmt->execute($params);
        $total_registros = $stmt->fetch()['total'];
        
        // Buscar registros com paginação
        $sql = "SELECT * FROM tb_atletas {$where_sql} ORDER BY atleta_nome ASC LIMIT ? OFFSET ?";
        $params_paginacao = array_merge($params, [$registros_por_pagina, $offset]);
        $stmt = $conn->prepare($sql);
        $stmt->execute($params_paginacao);
        
        $atletas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        $mensagem = "Erro ao buscar atletas: " . $e->getMessage();
        $tipo_mensagem = "error";
        error_log("Erro ao buscar atletas: " . $e->getMessage());
    }
    
    $total_paginas = ceil($total_registros / $registros_por_pagina);
}

// Funções específicas da página
function calcularIdade($data_nascimento) {
    $data_nasc = new DateTime($data_nascimento);
    $hoje = new DateTime();
    return $hoje->diff($data_nasc)->y;
}

function getStatusClass($status) {
    $classes = [
        'ATIVO' => 'status-ativo',
        'INATIVO' => 'status-inativo',
        'SUSPENSO' => 'status-suspenso'
    ];
    return $classes[$status] ?? '';
}

// Listas para dropdowns
$modalidades = [
    'Futebol', 'Futsal', 'Vôlei', 'Basquete', 'Handebol', 'Tênis', 'Tênis de Mesa',
    'Natação', 'Atletismo', 'Judô', 'Karatê', 'Taekwondo', 'Capoeira', 'Xadrez',
    'Damas', 'Corrida', 'Ciclismo', 'Ginástica', 'Outros'
];

$categorias = ['INFANTIL', 'JUVENIL', 'JUNIOR', 'ADULTO', 'MASTER'];
$status_atleta = ['ATIVO', 'INATIVO', 'SUSPENSO'];
$generos = ['MASCULINO', 'FEMININO', 'OUTRO'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atletas - Sistema da Prefeitura</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <?php include 'includes/esporte_styles.php'; ?>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <?php include 'includes/header_esporte.php'; ?>
        
        <h1 class="page-title">
            <i class="fas fa-user-friends"></i>
            <?php 
            switch($acao) {
                case 'adicionar':
                    echo 'Cadastrar Atleta';
                    break;
                case 'editar':
                    echo 'Editar Atleta';
                    break;
                default:
                    echo 'Gerenciar Atletas';
            }
            ?>
        </h1>

        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="dashboard.php">Dashboard</a>
            <i class="fas fa-chevron-right"></i>
            <a href="esporte.php">Esporte</a>
            <i class="fas fa-chevron-right"></i>
            <span>Atletas</span>
        </div>

        <!-- Mensagens -->
        <?php if ($mensagem): ?>
        <div class="alert alert-<?php echo $tipo_mensagem == 'success' ? 'success' : 'error'; ?>">
            <i class="fas fa-<?php echo $tipo_mensagem == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
            <?php echo $mensagem; ?>
        </div>
        <?php endif; ?>

        <?php if ($acao === 'listar'): ?>
        
        <!-- Botões de ação -->
        <div class="action-buttons">
            <div>
                <a href="?acao=adicionar" class="btn btn-success">
                    <i class="fas fa-plus"></i> Cadastrar Atleta
                </a>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filters-container">
            <div class="filters-header">
                <div class="filters-title">
                    <i class="fas fa-filter"></i>
                    Filtros de Busca
                </div>
                <button class="filters-toggle" onclick="toggleFilters()">
                    <i class="fas fa-chevron-down" id="filtersChevron"></i>
                </button>
            </div>
            
            <div class="filters-content" id="filtersContent">
                <form method="GET" action="" id="filtersForm">
                    <input type="hidden" name="acao" value="listar">
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label for="filtro_nome">Nome</label>
                            <input type="text" id="filtro_nome" name="filtro_nome" 
                                   value="<?php echo htmlspecialchars($filtros['nome']); ?>" 
                                   placeholder="Digite o nome...">
                        </div>
                        
                        <div class="filter-group">
                            <label for="filtro_cpf">CPF</label>
                            <input type="text" id="filtro_cpf" name="filtro_cpf" 
                                   value="<?php echo htmlspecialchars($filtros['cpf']); ?>" 
                                   placeholder="000.000.000-00" maxlength="14">
                        </div>
                        
                        <div class="filter-group">
                            <label for="filtro_modalidade">Modalidade</label>
                            <input type="text" id="filtro_modalidade" name="filtro_modalidade" 
                                   value="<?php echo htmlspecialchars($filtros['modalidade']); ?>" 
                                   placeholder="Digite a modalidade...">
                        </div>
                        
                        <div class="filter-group">
                            <label for="filtro_categoria">Categoria</label>
                            <select id="filtro_categoria" name="filtro_categoria">
                                <option value="">Todas as Categorias</option>
                                <?php foreach ($categorias as $categoria): ?>
                                <option value="<?php echo $categoria; ?>" 
                                        <?php echo ($filtros['categoria'] == $categoria) ? 'selected' : ''; ?>>
                                    <?php echo $categoria; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="filtro_status">Status</label>
                            <select id="filtro_status" name="filtro_status">
                                <option value="">Todos os Status</option>
                                <?php foreach ($status_atleta as $status): ?>
                                <option value="<?php echo $status; ?>" 
                                        <?php echo ($filtros['status'] == $status) ? 'selected' : ''; ?>>
                                    <?php echo $status; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="filters-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                        <a href="esporte_atletas.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Limpar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabela de Atletas -->
        <div class="table-container">
            <div class="table-header">
                <div class="table-title">
                    <i class="fas fa-list"></i>
                    Lista de Atletas
                </div>
                <div class="table-info">
                    <i class="fas fa-info-circle"></i>
                    <?php echo number_format($total_registros); ?> atleta(s) encontrado(s)
                </div>
            </div>
            
            <?php if (count($atletas) > 0): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>CPF</th>
                            <th>Idade</th>
                            <th>Modalidade</th>
                            <th>Categoria</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($atletas as $atleta): ?>
                        <tr>
                            <td>
                                <div>
                                    <strong><?php echo htmlspecialchars($atleta['atleta_nome']); ?></strong>
                                    <?php if ($atleta['atleta_celular']): ?>
                                    <br><small class="text-muted"><?php echo formatarTelefone($atleta['atleta_celular']); ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><?php echo formatarCPF($atleta['atleta_cpf']); ?></td>
                            <td><?php echo calcularIdade($atleta['atleta_data_nascimento']); ?> anos</td>
                            <td>
                                <small><?php echo htmlspecialchars($atleta['atleta_modalidade_principal'] ?? 'Não informado'); ?></small>
                            </td>
                            <td>
                                <small><?php echo htmlspecialchars($atleta['atleta_categoria']); ?></small>
                            </td>
                            <td>
                                <span class="status-badge <?php echo getStatusClass($atleta['atleta_status']); ?>">
                                    <?php echo htmlspecialchars($atleta['atleta_status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="?acao=editar&id=<?php echo $atleta['atleta_id']; ?>" 
                                       class="btn-action btn-edit" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            <?php if ($total_paginas > 1): ?>
            <div class="pagination-container">
                <div class="pagination-info">
                    Mostrando <?php echo (($pagina_atual - 1) * $registros_por_pagina) + 1; ?> a 
                    <?php echo min($pagina_atual * $registros_por_pagina, $total_registros); ?> de 
                    <?php echo number_format($total_registros); ?> registros
                </div>
                
                <div class="pagination">
                    <?php if ($pagina_atual > 1): ?>
                    <a href="?acao=listar&pagina=1<?php echo '&' . http_build_query(array_filter($filtros)); ?>">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a href="?acao=listar&pagina=<?php echo $pagina_atual - 1; ?><?php echo '&' . http_build_query(array_filter($filtros)); ?>">
                        <i class="fas fa-angle-left"></i>
                    </a>
                    <?php endif; ?>
                    
                    <?php
                    $inicio = max(1, $pagina_atual - 2);
                    $fim = min($total_paginas, $pagina_atual + 2);
                    
                    for ($i = $inicio; $i <= $fim; $i++):
                    ?>
                    <?php if ($i == $pagina_atual): ?>
                    <span class="active"><?php echo $i; ?></span>
                    <?php else: ?>
                    <a href="?acao=listar&pagina=<?php echo $i; ?><?php echo '&' . http_build_query(array_filter($filtros)); ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($pagina_atual < $total_paginas): ?>
                    <a href="?acao=listar&pagina=<?php echo $pagina_atual + 1; ?><?php echo '&' . http_build_query(array_filter($filtros)); ?>">
                        <i class="fas fa-angle-right"></i>
                    </a>
                    <a href="?acao=listar&pagina=<?php echo $total_paginas; ?><?php echo '&' . http_build_query(array_filter($filtros)); ?>">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-user-friends"></i>
                <h3>Nenhum atleta encontrado</h3>
                <p>Não há atletas cadastrados que correspondam aos filtros aplicados.</p>
                <div>
                    <a href="?acao=adicionar" class="btn btn-success">
                        <i class="fas fa-plus"></i> Cadastrar Primeiro Atleta
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <?php elseif ($acao === 'adicionar' || $acao === 'editar'): ?>

        <!-- Formulário de Cadastro/Edição -->
        <div class="form-container">
            <div class="form-header">
                <div class="form-title">
                    <i class="fas fa-<?php echo $acao === 'adicionar' ? 'plus' : 'edit'; ?>"></i>
                    <?php echo $acao === 'adicionar' ? 'Cadastrar' : 'Editar'; ?> Atleta
                </div>
            </div>
            
            <form method="POST" action="" id="atletaForm">
                <input type="hidden" name="acao" value="salvar">
                <input type="hidden" name="atleta_id" value="<?php echo $atleta_atual['atleta_id'] ?? '0'; ?>">
                
                <div class="form-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nome" class="required">Nome Completo</label>
                            <input type="text" id="nome" name="nome" required maxlength="255"
                                   value="<?php echo htmlspecialchars($atleta_atual['atleta_nome'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="cpf" class="required">CPF</label>
                            <input type="text" id="cpf" name="cpf" required maxlength="14" 
                                   placeholder="000.000.000-00"
                                   value="<?php echo $atleta_atual ? formatarCPF($atleta_atual['atleta_cpf']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="rg">RG</label>
                            <input type="text" id="rg" name="rg" maxlength="20"
                                   value="<?php echo htmlspecialchars($atleta_atual['atleta_rg'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="data_nascimento" class="required">Data de Nascimento</label>
                            <input type="date" id="data_nascimento" name="data_nascimento" required
                                   value="<?php echo $atleta_atual['atleta_data_nascimento'] ?? ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="genero" class="required">Gênero</label>
                            <select id="genero" name="genero" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($generos as $genero): ?>
                                <option value="<?php echo $genero; ?>" 
                                        <?php echo (($atleta_atual['atleta_genero'] ?? '') == $genero) ? 'selected' : ''; ?>>
                                    <?php echo $genero; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="telefone">Telefone</label>
                            <input type="text" id="telefone" name="telefone" maxlength="15" 
                                   placeholder="(00) 0000-0000"
                                   value="<?php echo $atleta_atual ? formatarTelefone($atleta_atual['atleta_telefone']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="celular" class="required">Celular</label>
                            <input type="text" id="celular" name="celular" required maxlength="15" 
                                   placeholder="(00) 00000-0000"
                                   value="<?php echo $atleta_atual ? formatarTelefone($atleta_atual['atleta_celular']) : ''; ?>">
                       </div>
                       
                       <div class="form-group">
                           <label for="email">E-mail</label>
                           <input type="email" id="email" name="email" maxlength="255"
                                  value="<?php echo htmlspecialchars($atleta_atual['atleta_email'] ?? ''); ?>">
                       </div>
                       
                       <div class="form-group">
                           <label for="modalidade_principal">Modalidade Principal</label>
                           <select id="modalidade_principal" name="modalidade_principal">
                               <option value="">Selecione...</option>
                               <?php foreach ($modalidades as $modalidade): ?>
                               <option value="<?php echo $modalidade; ?>" 
                                       <?php echo (($atleta_atual['atleta_modalidade_principal'] ?? '') == $modalidade) ? 'selected' : ''; ?>>
                                   <?php echo $modalidade; ?>
                               </option>
                               <?php endforeach; ?>
                           </select>
                       </div>
                       
                       <div class="form-group">
                           <label for="categoria" class="required">Categoria</label>
                           <select id="categoria" name="categoria" required>
                               <option value="">Selecione...</option>
                               <?php foreach ($categorias as $categoria): ?>
                               <option value="<?php echo $categoria; ?>" 
                                       <?php echo (($atleta_atual['atleta_categoria'] ?? '') == $categoria) ? 'selected' : ''; ?>>
                                   <?php echo $categoria; ?>
                               </option>
                               <?php endforeach; ?>
                           </select>
                       </div>
                       
                       <div class="form-group">
                           <label for="cidade">Cidade</label>
                           <input type="text" id="cidade" name="cidade" maxlength="100"
                                  value="<?php echo htmlspecialchars($atleta_atual['atleta_cidade'] ?? 'Santa Izabel do Oeste'); ?>">
                       </div>
                       
                       <div class="form-group">
                           <label for="bairro">Bairro</label>
                           <input type="text" id="bairro" name="bairro" maxlength="100"
                                  value="<?php echo htmlspecialchars($atleta_atual['atleta_bairro'] ?? ''); ?>">
                       </div>
                       
                       <div class="form-group">
                           <label for="cep">CEP</label>
                           <input type="text" id="cep" name="cep" maxlength="9" 
                                  placeholder="00000-000"
                                  value="<?php echo $atleta_atual && $atleta_atual['atleta_cep'] ? 
                                         substr($atleta_atual['atleta_cep'], 0, 5) . '-' . substr($atleta_atual['atleta_cep'], 5) : ''; ?>">
                       </div>
                       
                       <div class="form-group full-width">
                           <label for="endereco">Endereço Completo</label>
                           <textarea id="endereco" name="endereco" rows="3"><?php echo htmlspecialchars($atleta_atual['atleta_endereco'] ?? ''); ?></textarea>
                       </div>
                       
                       <div class="form-group" id="responsavel_nome_group" style="display: none;">
                           <label for="responsavel_nome">Nome do Responsável</label>
                           <input type="text" id="responsavel_nome" name="responsavel_nome" maxlength="255"
                                  value="<?php echo htmlspecialchars($atleta_atual['atleta_responsavel_nome'] ?? ''); ?>">
                       </div>
                       
                       <div class="form-group" id="responsavel_telefone_group" style="display: none;">
                           <label for="responsavel_telefone">Telefone do Responsável</label>
                           <input type="text" id="responsavel_telefone" name="responsavel_telefone" maxlength="15" 
                                  placeholder="(00) 00000-0000"
                                  value="<?php echo $atleta_atual ? formatarTelefone($atleta_atual['atleta_responsavel_telefone']) : ''; ?>">
                       </div>
                       
                       <div class="form-group">
                           <label for="status">Status</label>
                           <select id="status" name="status">
                               <?php foreach ($status_atleta as $status): ?>
                               <option value="<?php echo $status; ?>" 
                                       <?php echo (($atleta_atual['atleta_status'] ?? 'ATIVO') == $status) ? 'selected' : ''; ?>>
                                   <?php echo $status; ?>
                               </option>
                               <?php endforeach; ?>
                           </select>
                       </div>
                       
                       <div class="form-group full-width">
                           <label for="observacoes">Observações</label>
                           <textarea id="observacoes" name="observacoes" rows="4"><?php echo htmlspecialchars($atleta_atual['atleta_observacoes'] ?? ''); ?></textarea>
                       </div>
                   </div>
               </div>
               
               <div class="form-actions">
                   <a href="?acao=listar" class="btn btn-secondary">
                       <i class="fas fa-arrow-left"></i> Voltar
                   </a>
                   <button type="submit" class="btn btn-success">
                       <i class="fas fa-save"></i> Salvar
                   </button>
               </div>
           </form>
       </div>

       <?php endif; ?>
   </div>

   <?php include 'includes/esporte_scripts.php'; ?>

   <script>
       // Scripts específicos da página de atletas
       document.addEventListener('DOMContentLoaded', function() {
           // Check age for responsible fields
           const dataNascInput = document.getElementById('data_nascimento');
           if (dataNascInput) {
               dataNascInput.addEventListener('change', function() {
                   checkAge();
               });
               
               // Check on page load
               checkAge();
           }
           
           function checkAge() {
               const dataNasc = document.getElementById('data_nascimento').value;
               const responsavelNomeGroup = document.getElementById('responsavel_nome_group');
               const responsavelTelefoneGroup = document.getElementById('responsavel_telefone_group');
               const responsavelNomeInput = document.getElementById('responsavel_nome');
               
               if (dataNasc) {
                   const birthDate = new Date(dataNasc);
                   const today = new Date();
                   let age = today.getFullYear() - birthDate.getFullYear();
                   const monthDiff = today.getMonth() - birthDate.getMonth();
                   
                   if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                       age--;
                   }
                   
                   if (age < 18) {
                       responsavelNomeGroup.style.display = 'flex';
                       responsavelTelefoneGroup.style.display = 'flex';
                       responsavelNomeInput.required = true;
                   } else {
                       responsavelNomeGroup.style.display = 'none';
                       responsavelTelefoneGroup.style.display = 'none';
                       responsavelNomeInput.required = false;
                   }
               }
           }

           // Form validation
           const atletaForm = document.getElementById('atletaForm');
           if (atletaForm) {
               atletaForm.addEventListener('submit', function(e) {
                   if (!validateAtletaForm()) {
                       e.preventDefault();
                   }
               });
           }
       });

       function validateAtletaForm() {
           let isValid = true;
           const errors = [];
           
           // Validate CPF
           const cpfInput = document.getElementById('cpf');
           if (cpfInput && !validateCPF(cpfInput.value)) {
               errors.push('CPF inválido');
               cpfInput.style.borderColor = '#dc3545';
               isValid = false;
           } else if (cpfInput) {
               cpfInput.style.borderColor = '#ddd';
           }
           
           // Validate email
           const emailInput = document.getElementById('email');
           if (emailInput && emailInput.value) {
               if (!validateEmail(emailInput.value)) {
                   errors.push('E-mail inválido');
                   emailInput.style.borderColor = '#dc3545';
                   isValid = false;
               } else {
                   emailInput.style.borderColor = '#ddd';
               }
           }
           
           // Check if responsible is required for minors
           const dataNasc = document.getElementById('data_nascimento').value;
           const responsavelNome = document.getElementById('responsavel_nome').value;
           
           if (dataNasc) {
               const birthDate = new Date(dataNasc);
               const today = new Date();
               let age = today.getFullYear() - birthDate.getFullYear();
               const monthDiff = today.getMonth() - birthDate.getMonth();
               
               if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                   age--;
               }
               
               if (age < 18 && !responsavelNome.trim()) {
                   errors.push('Nome do responsável é obrigatório para menores de idade');
                   document.getElementById('responsavel_nome').style.borderColor = '#dc3545';
                   isValid = false;
               }
           }
           
           if (!isValid) {
               showAlert('Por favor, corrija os seguintes erros:\n' + errors.join('\n'), 'error');
           }
           
           return isValid;
       }
   </script>
</body>
</html>