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

// Verifica se foi informado um ID de solicitação
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['erro_msg'] = "ID de solicitação inválido.";
    header("Location: minhas_solicitacoes.php");
    exit();
}

$solicitacao_id = (int)$_GET['id'];

// Inclui arquivo de conexão com o banco de dados
require_once '../../database/conect.php';

// Verifica se a solicitação pertence ao usuário logado
$stmt = $conn->prepare("
    SELECT s.*, 
           DATE_FORMAT(s.data_solicitacao, '%d/%m/%Y %H:%i') as data_formatada,
           DATE_FORMAT(s.ultima_atualizacao, '%d/%m/%Y %H:%i') as atualizacao_formatada
    FROM tb_solicitacoes s
    WHERE s.solicitacao_id = :solicitacao_id AND s.usuario_id = :usuario_id
");
$stmt->bindParam(':solicitacao_id', $solicitacao_id);
$stmt->bindParam(':usuario_id', $usuario_id);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    $_SESSION['erro_msg'] = "Solicitação não encontrada ou você não tem permissão para visualizá-la.";
    header("Location: minhas_solicitacoes.php");
    exit();
}

$solicitacao = $stmt->fetch(PDO::FETCH_ASSOC);

// Recupera o histórico da solicitação
$stmt = $conn->prepare("
    SELECT h.*, 
           DATE_FORMAT(h.data_operacao, '%d/%m/%Y %H:%i') as data_formatada,
           u.cad_usu_nome as nome_operador
    FROM tb_solicitacoes_historico h
    LEFT JOIN tb_cad_usuarios u ON h.usuario_operacao = u.cad_usu_id
    WHERE h.solicitacao_id = :solicitacao_id
    ORDER BY h.data_operacao DESC
");
$stmt->bindParam(':solicitacao_id', $solicitacao_id);
$stmt->execute();
$historico = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Recupera os comentários públicos (visíveis para o cidadão)
$stmt = $conn->prepare("
    SELECT c.*, 
           DATE_FORMAT(c.data_comentario, '%d/%m/%Y %H:%i') as data_formatada,
           u.cad_usu_nome as nome_usuario
    FROM tb_solicitacoes_comentarios c
    JOIN tb_cad_usuarios u ON c.usuario_id = u.cad_usu_id
    WHERE c.solicitacao_id = :solicitacao_id AND c.visibilidade = 'EXTERNO'
    ORDER BY c.data_comentario DESC
");
$stmt->bindParam(':solicitacao_id', $solicitacao_id);
$stmt->execute();
$comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Recupera os anexos
$stmt = $conn->prepare("
    SELECT a.*, 
           DATE_FORMAT(a.data_upload, '%d/%m/%Y') as data_formatada
    FROM tb_solicitacoes_anexos a
    WHERE a.solicitacao_id = :solicitacao_id
    ORDER BY a.data_upload DESC
");
$stmt->bindParam(':solicitacao_id', $solicitacao_id);
$stmt->execute();
$anexos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Processar envio de novo comentário, se houver
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'comentar') {
    $comentario = trim($_POST['comentario'] ?? '');
    
    if (!empty($comentario)) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO tb_solicitacoes_comentarios (
                    solicitacao_id, usuario_id, comentario, data_comentario, visibilidade
                ) VALUES (
                    :solicitacao_id, :usuario_id, :comentario, NOW(), 'EXTERNO'
                )
            ");
            $stmt->bindParam(':solicitacao_id', $solicitacao_id);
            $stmt->bindParam(':usuario_id', $usuario_id);
            $stmt->bindParam(':comentario', $comentario);
            $stmt->execute();
            
            // Atualiza a data de última atualização da solicitação
            $stmt = $conn->prepare("
                UPDATE tb_solicitacoes 
                SET ultima_atualizacao = NOW()
                WHERE solicitacao_id = :solicitacao_id
            ");
            $stmt->bindParam(':solicitacao_id', $solicitacao_id);
            $stmt->execute();
            
            // Redireciona para evitar reenvio do formulário
            header("Location: detalhe_solicitacao.php?id={$solicitacao_id}&comentario_enviado=1");
            exit();
        } catch (PDOException $e) {
            $erro_comentario = "Erro ao enviar comentário. Por favor, tente novamente.";
        }
    } else {
        $erro_comentario = "O comentário não pode estar vazio.";
    }
}

// Função para obter a classe CSS de acordo com o status
function getStatusClass($status) {
    switch (strtoupper($status)) {
        case 'RECEBIDO':
            return 'status-recebido';
        case 'EM ANÁLISE':
            return 'status-analise';
        case 'APROVADO':
            return 'status-aprovado';
        case 'REPROVADO':
            return 'status-reprovado';
        case 'PENDENTE DE ANÁLISE':
            return 'status-pendente';
        case 'CONCLUÍDO':
            return 'status-concluido';
        case 'CANCELADO':
            return 'status-cancelado';
        default:
            return 'status-recebido';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes da Solicitação - Eai Cidadão!</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <!-- CSS adicional para esta página -->
    <style>
        .container {
            max-width: 1000px;
            padding: 30px;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            color: #2e7d32;
            text-decoration: none;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .back-link i {
            margin-right: 8px;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .page-title {
            color: #2e7d32;
            font-size: 1.8rem;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
        }
        
        .page-title i {
            margin-right: 10px;
        }
        
        .card-solicitacao {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .card-title {
            font-size: 1.5rem;
            color: #333;
            margin: 0;
        }
        
        .protocolo {
            font-size: 1.2rem;
            font-weight: 600;
            color: #0d47a1;
        }
        
        .info-item {
            margin-bottom: 15px;
            display: flex;
            flex-wrap: wrap;
        }
        
        .info-label {
            font-weight: 600;
            width: 180px;
            color: #555;
        }
        
        .info-value {
            flex: 1;
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            text-align: center;
        }
        
        .status-recebido { background-color: #e3f2fd; color: #0d47a1; }
        .status-analise { background-color: #fff8e1; color: #ff8f00; }
        .status-aprovado { background-color: #e8f5e9; color: #2e7d32; }
        .status-reprovado { background-color: #ffebee; color: #c62828; }
        .status-pendente { background-color: #f3e5f5; color: #7b1fa2; }
        .status-concluido { background-color: #e0f2f1; color: #00796b; }
        .status-cancelado { background-color: #f5f5f5; color: #616161; }
        
        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        
        .tab-btn {
            padding: 12px 20px;
            cursor: pointer;
            border: none;
            background: none;
            font-size: 1rem;
            font-weight: 500;
            color: #666;
            position: relative;
        }
        
        .tab-btn.active {
            color: #2e7d32;
        }
        
        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: #2e7d32;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .timeline {
            position: relative;
            padding-left: 30px;
            margin-top: 20px;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            top: 0;
            left: 8px;
            height: 100%;
            width: 2px;
            background-color: #ddd;
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 25px;
        }
        
        .timeline-item:last-child {
            margin-bottom: 0;
        }
        
        .timeline-bullet {
            position: absolute;
            left: -30px;
            top: 5px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background-color: #2e7d32;
            z-index: 2;
        }
        
        .timeline-content {
            background-color: #f9f9f9;
            border-radius: 6px;
            padding: 15px;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.05);
        }
        
        .timeline-date {
            font-size: 0.85rem;
            color: #777;
            margin-bottom: 8px;
        }
        
        .timeline-text {
            color: #333;
        }
        
        .timeline-status {
            margin-top: 10px;
            display: flex;
            align-items: center;
        }
        
        .timeline-status-badge {
            font-size: 0.85rem;
            padding: 4px 10px;
            border-radius: 15px;
            margin-right: 10px;
        }
        
        .timeline-operator {
            font-size: 0.85rem;
            color: #666;
        }
        
        .comentario-form {
            margin-top: 30px;
            background-color: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
        }
        
        .comentario-form textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 15px;
            resize: vertical;
            min-height: 120px;
        }
        
        .comentario-submit {
            background-color: #2e7d32;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .comentario-submit:hover {
            background-color: #1b5e20;
            transform: translateY(-2px);
        }
        
        .comentarios-list {
            margin-top: 30px;
        }
        
        .comentario-item {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        
        .comentario-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
            font-size: 0.9rem;
            color: #666;
        }
        
        .comentario-text {
            white-space: pre-line;
            color: #333;
        }
        
        .anexos-list {
            margin-top: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
        }
        
        .anexo-item {
            background-color: #f5f5f5;
            border-radius: 6px;
            padding: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        
        .anexo-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: #0d47a1;
        }
        
        .anexo-name {
            font-weight: 500;
            margin-bottom: 5px;
            word-break: break-all;
        }
        
        .anexo-info {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 10px;
        }
        
        .anexo-download {
            color: #2e7d32;
            text-decoration: none;
            display: flex;
            align-items: center;
            font-size: 0.9rem;
        }
        
        .anexo-download i {
            margin-right: 5px;
        }
        
        .no-data-message {
            text-align: center;
            padding: 40px 20px;
            color: #777;
            background-color: #f5f5f5;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .no-data-message i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #ddd;
        }
        
        .error-message {
            color: #c62828;
            background-color: #ffebee;
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        
        .success-message {
            color: #2e7d32;
            background-color: #e8f5e9;
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        
        @media (max-width: 768px) {
            .info-label {
                width: 100%;
                margin-bottom: 5px;
            }
            
            .info-value {
                width: 100%;
            }
            
            .card-header {
                flex-direction: column;
            }
            
            .protocolo {
                margin-top: 10px;
            }
            
            .tabs {
                overflow-x: auto;
            }
            
            .tab-btn {
                padding: 12px 15px;
                white-space: nowrap;
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

        <a href="minhas_solicitacoes.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Voltar para Minhas Solicitações
        </a>

        <h2 class="page-title"><i class="fas fa-clipboard-check"></i> Detalhes da Solicitação</h2>

        <?php if (isset($_GET['comentario_enviado']) && $_GET['comentario_enviado'] == 1): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> Seu comentário foi enviado com sucesso!
            </div>
        <?php endif; ?>

        <?php if (isset($erro_comentario)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo $erro_comentario; ?>
            </div>
        <?php endif; ?>

        <div class="card-solicitacao">
            <div class="card-header">
                <h3 class="card-title">
                    <?php echo htmlspecialchars($solicitacao['tipo_solicitacao']); ?>
                    <?php if (!empty($solicitacao['subtipo'])): ?>
                        <span style="font-size: 0.8em; color: #666;">
                            (<?php echo htmlspecialchars($solicitacao['subtipo']); ?>)
                        </span>
                    <?php endif; ?>
                </h3>
                <div class="protocolo">
                    Protocolo: <?php echo htmlspecialchars($solicitacao['protocolo']); ?>
                </div>
            </div>

            <div class="info-item">
                <div class="info-label">Data da Solicitação:</div>
                <div class="info-value"><?php echo htmlspecialchars($solicitacao['data_formatada']); ?></div>
            </div>

            <div class="info-item">
                <div class="info-label">Status Atual:</div>
                <div class="info-value">
                    <span class="status-badge <?php echo getStatusClass($solicitacao['status']); ?>">
                        <?php echo htmlspecialchars($solicitacao['status']); ?>
                    </span>
                </div>
            </div>

            <?php if (!empty($solicitacao['status_detalhes'])): ?>
                <div class="info-item">
                    <div class="info-label">Detalhes do Status:</div>
                    <div class="info-value"><?php echo nl2br(htmlspecialchars($solicitacao['status_detalhes'])); ?></div>
                </div>
            <?php endif; ?>

            <div class="info-item">
                <div class="info-label">Última Atualização:</div>
                <div class="info-value"><?php echo htmlspecialchars($solicitacao['atualizacao_formatada']); ?></div>
            </div>

            <?php if (!empty($solicitacao['departamento_responsavel'])): ?>
                <div class="info-item">
                    <div class="info-label">Departamento Responsável:</div>
                    <div class="info-value"><?php echo htmlspecialchars($solicitacao['departamento_responsavel']); ?></div>
                </div>
            <?php endif; ?>

            <!-- Tabs de navegação -->
            <div class="tabs">
                <button class="tab-btn active" data-tab="historico">
                    <i class="fas fa-history"></i> Histórico
                </button>
                <button class="tab-btn" data-tab="comentarios">
                    <i class="fas fa-comments"></i> Comentários
                    <?php if (count($comentarios) > 0): ?>
                        <span style="margin-left: 5px; font-size: 0.8em;">(<?php echo count($comentarios); ?>)</span>
                    <?php endif; ?>
                </button>
                <button class="tab-btn" data-tab="anexos">
                    <i class="fas fa-paperclip"></i> Anexos
                    <?php if (count($anexos) > 0): ?>
                        <span style="margin-left: 5px; font-size: 0.8em;">(<?php echo count($anexos); ?>)</span>
                    <?php endif; ?>
                </button>
            </div>

            <!-- Conteúdo da tab Histórico -->
            <div class="tab-content active" id="historico-tab">
                <?php if (count($historico) > 0): ?>
                    <div class="timeline">
                        <?php foreach ($historico as $item): ?>
                            <div class="timeline-item">
                                <div class="timeline-bullet"></div>
                                <div class="timeline-content">
                                    <div class="timeline-date">
                                        <i class="far fa-calendar-alt"></i> <?php echo htmlspecialchars($item['data_formatada']); ?>
                                    </div>
                                    
                                    <div class="timeline-text">
                                        <?php echo nl2br(htmlspecialchars($item['detalhes'])); ?>
                                    </div>
                                    
                                    <div class="timeline-status">
                                        <?php if (!empty($item['status_novo'])): ?>
                                            <span class="timeline-status-badge status-badge <?php echo getStatusClass($item['status_novo']); ?>">
                                                <?php echo htmlspecialchars($item['status_novo']); ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($item['nome_operador'])): ?>
                                            <span class="timeline-operator">
                                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($item['nome_operador']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-data-message">
                        <i class="far fa-calendar-times"></i>
                        <h4>Sem registros de histórico</h4>
                        <p>Não há registros de histórico para esta solicitação.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Conteúdo da tab Comentários -->
            <div class="tab-content" id="comentarios-tab">
                <div class="comentario-form">
                    <h4>Adicionar Comentário</h4>
                    <form method="post" action="">
                        <input type="hidden" name="acao" value="comentar">
                        <textarea name="comentario" placeholder="Digite seu comentário ou dúvida aqui..." required></textarea>
                        <button type="submit" class="comentario-submit">
                            <i class="fas fa-paper-plane"></i> Enviar Comentário
                        </button>
                    </form>
                </div>

                <div class="comentarios-list">
                    <?php if (count($comentarios) > 0): ?>
                        <?php foreach ($comentarios as $comentario): ?>
                            <div class="comentario-item">
                                <div class="comentario-header">
                                    <div><i class="fas fa-user"></i> <?php echo htmlspecialchars($comentario['nome_usuario']); ?></div>
                                    <div><i class="far fa-clock"></i> <?php echo htmlspecialchars($comentario['data_formatada']); ?></div>
                                </div>
                                <div class="comentario-text">
                                    <?php echo nl2br(htmlspecialchars($comentario['comentario'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-data-message">
                            <i class="far fa-comments"></i>
                            <h4>Sem comentários</h4>
                            <p>Não há comentários para esta solicitação.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Conteúdo da tab Anexos -->
            <div class="tab-content" id="anexos-tab">
                <?php if (count($anexos) > 0): ?>
                    <div class="anexos-list">
                        <?php foreach ($anexos as $anexo): ?>
                            <div class="anexo-item">
                                <?php
                                // Determina o ícone com base no tipo de arquivo
                                $icon_class = 'fa-file';
                                $ext = strtolower(pathinfo($anexo['nome_arquivo'], PATHINFO_EXTENSION));
                                
                                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                                    $icon_class = 'fa-file-image';
                                } elseif (in_array($ext, ['pdf'])) {
                                    $icon_class = 'fa-file-pdf';
                                } elseif (in_array($ext, ['doc', 'docx'])) {
                                    $icon_class = 'fa-file-word';
                                } elseif (in_array($ext, ['xls', 'xlsx'])) {
                                    $icon_class = 'fa-file-excel';
                                }
                                ?>
                                <div class="anexo-icon">
                                    <i class="far <?php echo $icon_class; ?>"></i>
                                </div>
                                <div class="anexo-name">
                                    <?php echo htmlspecialchars($anexo['nome_arquivo']); ?>
                                </div>
                                <div class="anexo-info">
                                    Enviado em <?php echo htmlspecialchars($anexo['data_formatada']); ?>
                                    <?php if (!empty($anexo['tamanho_arquivo'])): ?>
                                        <br>
                                        <?php echo round($anexo['tamanho_arquivo'] / 1024) . ' KB'; ?>
                                    <?php endif; ?>
                                </div>
                                <a href="download_anexo.php?id=<?php echo $anexo['anexo_id']; ?>" class="anexo-download">
                                    <i class="fas fa-download"></i> Baixar arquivo
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-data-message">
                        <i class="far fa-file-alt"></i>
                        <h4>Sem anexos</h4>
                        <p>Não há arquivos anexados a esta solicitação.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Código para funcionamento das tabs
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    // Remove a classe active de todos os botões e conteúdos
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    // Adiciona a classe active ao botão clicado
                    this.classList.add('active');
                    
                    // Mostrar o conteúdo correspondente
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(tabId + '-tab').classList.add('active');
                });
            });
        });
    </script>
</body>
</html>