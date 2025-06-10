<?php
// Inicia a sessão
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['user_logado']) || $_SESSION['user_logado'] !== true) {
    // Se não estiver logado, redireciona para a página de login
    header("Location: ../../login_cidadao.php");
    exit();
}

// Obtém ID do usuário
$usuario_id = $_SESSION['user_id'];

// Inclui arquivo de conexão com o banco de dados
require_once '../../database/conect.php';

// Parâmetros de filtro
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$periodo = isset($_GET['periodo']) ? $_GET['periodo'] : '30'; // padrão: últimos 30 dias

// Consulta SQL base
$sql = "SELECT s.*, 
               DATE_FORMAT(s.data_solicitacao, '%d/%m/%Y %H:%i') as data_formatada,
               DATE_FORMAT(s.ultima_atualizacao, '%d/%m/%Y %H:%i') as atualizacao_formatada
        FROM tb_solicitacoes s
        WHERE s.usuario_id = :usuario_id";

// Adicionar filtros
if (!empty($tipo)) {
    $sql .= " AND s.tipo_solicitacao = :tipo";
}
if (!empty($status)) {
    $sql .= " AND s.status = :status";
}
if (!empty($periodo)) {
    $sql .= " AND s.data_solicitacao >= DATE_SUB(NOW(), INTERVAL :periodo DAY)";
}

// Ordenação
$sql .= " ORDER BY s.ultima_atualizacao DESC";

// Preparar e executar a consulta
$stmt = $conn->prepare($sql);
$stmt->bindParam(':usuario_id', $usuario_id);

if (!empty($tipo)) {
    $stmt->bindParam(':tipo', $tipo);
}
if (!empty($status)) {
    $stmt->bindParam(':status', $status);
}
if (!empty($periodo)) {
    $stmt->bindParam(':periodo', $periodo, PDO::PARAM_INT);
}

$stmt->execute();
$solicitacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consulta para obter os tipos de solicitação distintos
$stmt_tipos = $conn->prepare("SELECT DISTINCT tipo_solicitacao FROM tb_solicitacoes WHERE usuario_id = :usuario_id");
$stmt_tipos->bindParam(':usuario_id', $usuario_id);
$stmt_tipos->execute();
$tipos = $stmt_tipos->fetchAll(PDO::FETCH_COLUMN);

// Consulta para obter os status distintos
$stmt_status = $conn->prepare("SELECT DISTINCT status FROM tb_solicitacoes WHERE usuario_id = :usuario_id");
$stmt_status->bindParam(':usuario_id', $usuario_id);
$stmt_status->execute();
$status_lista = $stmt_status->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Solicitações - Eai Cidadão!</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .container {
            max-width: 1200px;
            padding: 20px;
        }
        
        /* Filtros responsivos */
        .filters {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        
        .filter-form {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        
        .filter-group label {
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }
        
        .filter-group select {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px; /* Evita zoom no iOS */
            background-color: white;
        }
        
        .btn-filter {
            background-color: #0d47a1;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background-color 0.3s;
        }
        
        .btn-filter:hover {
            background-color: #1565c0;
        }
        
        /* Cards para mobile */
        .solicitacoes-cards {
            display: grid;
            gap: 15px;
        }
        
        .solicitacao-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            border-left: 4px solid #2e7d32;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            gap: 10px;
        }
        
        .card-protocol {
            font-size: 14px;
            font-weight: 600;
            color: #0d47a1;
            background-color: #e3f2fd;
            padding: 4px 8px;
            border-radius: 12px;
            white-space: nowrap;
        }
        
        .card-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
            line-height: 1.3;
        }
        
        .card-subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }
        
        .card-info {
            display: grid;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 5px;
        }
        
        .info-label {
            font-size: 14px;
            color: #666;
            font-weight: 500;
        }
        
        .info-value {
            font-size: 14px;
            color: #333;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-align: center;
            white-space: nowrap;
        }
        
        .status-recebido { background-color: #e3f2fd; color: #0d47a1; }
        .status-analise { background-color: #fff8e1; color: #ff8f00; }
        .status-aprovado { background-color: #e8f5e9; color: #2e7d32; }
        .status-reprovado { background-color: #ffebee; color: #c62828; }
        .status-pendente { background-color: #f3e5f5; color: #7b1fa2; }
        
        .card-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .btn-ver {
            background-color: #2e7d32;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s;
        }
        
        .btn-ver:hover {
            background-color: #1b5e20;
            transform: translateY(-2px);
        }
        
        /* Tabela para desktop */
        .table-container {
            display: none;
            overflow-x: auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .solicitacoes-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }
        
        .solicitacoes-table th {
            background-color: #f5f5f5;
            padding: 15px 12px;
            text-align: left;
            border-bottom: 2px solid #ddd;
            font-weight: 600;
            color: #333;
            white-space: nowrap;
        }
        
        .solicitacoes-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        
        .solicitacoes-table tr:hover {
            background-color: #f9f9f9;
        }
        
        .no-results {
            text-align: center;
            padding: 40px 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            color: #666;
        }
        
        .no-results i {
            font-size: 48px;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .no-results h3 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            margin-bottom: 20px;
            color: #2e7d32;
            text-decoration: none;
            font-weight: 500;
            gap: 8px;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .page-title {
            margin-bottom: 25px;
            color: #2e7d32;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.5rem;
        }
        
        /* Media queries */
        @media (min-width: 480px) {
            .filter-form {
                grid-template-columns: 1fr 1fr;
            }
            
            .btn-filter {
                grid-column: 1 / -1;
            }
        }
        
        @media (min-width: 768px) {
            .container {
                padding: 30px;
            }
            
            .filter-form {
                grid-template-columns: repeat(3, 1fr) auto;
                align-items: end;
            }
            
            .btn-filter {
                grid-column: auto;
                align-self: end;
            }
            
            .page-title {
                font-size: 1.8rem;
            }
        }
        
        @media (min-width: 1024px) {
            .solicitacoes-cards {
                display: none;
            }
            
            .table-container {
                display: block;
            }
        }
        
        /* Melhorias de acessibilidade */
        @media (prefers-reduced-motion: reduce) {
            .btn-ver,
            .btn-filter {
                transition: none;
            }
        }
        
        
    </style>
</head>
<body>
    <div class="container">
        <div class="header-container">
            <div class="municipality-logo">
                <img src="../../img/logo_municipio.png" alt="Logo do Município">
            </div>
            <div class="title-container">
                <h1>Eai Cidadão!</h1>
                <h2 class="municipality-name">Município de Santa Izabel do Oeste</h2>
            </div>
        </div>

        <div class="divider"></div>

        <a href="perfil.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Voltar para Meu Perfil
        </a>

        <h2 class="page-title">
            <i class="fas fa-clipboard-list"></i> Minhas Solicitações
        </h2>

        <div class="filters">
            <form action="" method="GET" class="filter-form">
                <div class="filter-group">
                    <label for="tipo">Tipo</label>
                    <select id="tipo" name="tipo">
                        <option value="">Todos</option>
                        <?php foreach ($tipos as $t): ?>
                            <option value="<?php echo htmlspecialchars($t); ?>" <?php echo ($tipo == $t) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($t); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">Todos</option>
                        <?php foreach ($status_lista as $s): ?>
                            <option value="<?php echo htmlspecialchars($s); ?>" <?php echo ($status == $s) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($s); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="periodo">Período</label>
                    <select id="periodo" name="periodo">
                        <option value="30" <?php echo ($periodo == '30') ? 'selected' : ''; ?>>Últimos 30 dias</option>
                        <option value="90" <?php echo ($periodo == '90') ? 'selected' : ''; ?>>Últimos 3 meses</option>
                        <option value="180" <?php echo ($periodo == '180') ? 'selected' : ''; ?>>Últimos 6 meses</option>
                        <option value="365" <?php echo ($periodo == '365') ? 'selected' : ''; ?>>Último ano</option>
                        <option value="9999" <?php echo ($periodo == '9999') ? 'selected' : ''; ?>>Todos</option>
                    </select>
                </div>
                
                <button type="submit" class="btn-filter">
                    <i class="fas fa-filter"></i>
                    <span>Filtrar</span>
                </button>
            </form>
        </div>

        <?php if (count($solicitacoes) > 0): ?>
            <!-- Layout em cards para mobile/tablet -->
            <div class="solicitacoes-cards">
                <?php foreach ($solicitacoes as $solicitacao): ?>
                    <div class="solicitacao-card">
                        <div class="card-header">
                            <div>
                                <div class="card-title">
                                    <?php echo htmlspecialchars($solicitacao['tipo_solicitacao']); ?>
                                </div>
                                <?php if (!empty($solicitacao['subtipo'])): ?>
                                    <div class="card-subtitle">
                                        <?php echo htmlspecialchars($solicitacao['subtipo']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-protocol">
                                <?php echo htmlspecialchars($solicitacao['protocolo']); ?>
                            </div>
                        </div>
                        
                        <div class="card-info">
                            <div class="info-row">
                                <span class="info-label">
                                    <i class="far fa-calendar"></i> Data:
                                </span>
                                <span class="info-value">
                                    <?php echo htmlspecialchars($solicitacao['data_formatada']); ?>
                                </span>
                            </div>
                            
                            <div class="info-row">
                                <span class="info-label">
                                    <i class="fas fa-info-circle"></i> Status:
                                </span>
                                <span class="info-value">
                                    <?php
                                    $statusClass = '';
                                    switch (strtoupper($solicitacao['status'])) {
                                        case 'RECEBIDO':
                                            $statusClass = 'status-recebido';
                                            break;
                                        case 'EM ANÁLISE':
                                            $statusClass = 'status-analise';
                                            break;
                                        case 'APROVADO':
                                            $statusClass = 'status-aprovado';
                                            break;
                                        case 'REPROVADO':
                                            $statusClass = 'status-reprovado';
                                            break;
                                        case 'PENDENTE DE ANÁLISE':
                                            $statusClass = 'status-pendente';
                                            break;
                                        default:
                                            $statusClass = 'status-recebido';
                                    }
                                    ?>
                                    <span class="status-badge <?php echo $statusClass; ?>">
                                        <?php echo htmlspecialchars($solicitacao['status']); ?>
                                    </span>
                                </span>
                            </div>
                            
                            <div class="info-row">
                                <span class="info-label">
                                    <i class="far fa-clock"></i> Atualização:
                                </span>
                                <span class="info-value">
                                    <?php echo htmlspecialchars($solicitacao['atualizacao_formatada']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="card-actions">
                            <a href="detalhe_solicitacao.php?id=<?php echo $solicitacao['solicitacao_id']; ?>" class="btn-ver">
                                <i class="fas fa-eye"></i>
                                <span>Ver detalhes</span>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Layout em tabela para desktop -->
            <div class="table-container">
                <table class="solicitacoes-table">
                    <thead>
                        <tr>
                            <th>Protocolo</th>
                            <th>Tipo</th>
                            <th>Data</th>
                            <th>Status</th>
                            <th>Última Atualização</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($solicitacoes as $solicitacao): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($solicitacao['protocolo']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($solicitacao['tipo_solicitacao']); ?>
                                    <?php if (!empty($solicitacao['subtipo'])): ?>
                                        <br><small><?php echo htmlspecialchars($solicitacao['subtipo']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($solicitacao['data_formatada']); ?></td>
                                <td>
                                    <?php
                                    $statusClass = '';
                                    switch (strtoupper($solicitacao['status'])) {
                                        case 'RECEBIDO':
                                            $statusClass = 'status-recebido';
                                            break;
                                        case 'EM ANÁLISE':
                                            $statusClass = 'status-analise';
                                            break;
                                        case 'APROVADO':
                                            $statusClass = 'status-aprovado';
                                            break;
                                        case 'REPROVADO':
                                            $statusClass = 'status-reprovado';
                                            break;
                                        case 'PENDENTE DE ANÁLISE':
                                            $statusClass = 'status-pendente';
                                            break;
                                        default:
                                            $statusClass = 'status-recebido';
                                    }
                                    ?>
                                    <span class="status-badge <?php echo $statusClass; ?>">
                                        <?php echo htmlspecialchars($solicitacao['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($solicitacao['atualizacao_formatada']); ?></td>
                                <td>
                                    <a href="detalhe_solicitacao.php?id=<?php echo $solicitacao['solicitacao_id']; ?>" class="btn-ver">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="no-results">
                <i class="fas fa-search"></i>
                <h3>Nenhuma solicitação encontrada</h3>
                <p>Não encontramos solicitações com os filtros selecionados.<br>Tente ajustar os filtros ou faça uma nova solicitação.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>