<?php
// Inicia a sessão
session_start();

if (!isset($_SESSION['user_logado'])) {
    header("Location: ../acessdenied.php"); 
    exit;
}

// Tratar mensagens de erro da sessão
if (isset($_SESSION['erro_habitacao'])) {
    $erro_msg = $_SESSION['erro_habitacao'];
    unset($_SESSION['erro_habitacao']); // Limpar após exibir
}

// Tratar mensagens de sucesso da sessão  
if (isset($_SESSION['sucesso_habitacao'])) {
    $sucesso_msg = $_SESSION['sucesso_habitacao'];
    unset($_SESSION['sucesso_habitacao']); // Limpar após exibir
}

// Tratar parâmetros de erro da URL
if (isset($_GET['error']) && $_GET['error'] === 'true') {
    if (isset($_GET['msg'])) {
        $erro_msg = urldecode($_GET['msg']);
    } else {
        $erro_msg = 'Ocorreu um erro inesperado. Tente novamente.';
    }
}

// Tratar parâmetros de sucesso da URL (JavaScript já trata, mas por segurança)
if (isset($_GET['success']) && $_GET['success'] === 'true') {
    if (isset($_GET['protocolo'])) {
        $protocolo = urldecode($_GET['protocolo']);
        $sucesso_msg = "Cadastro realizado com sucesso! Protocolo: {$protocolo}";
    } else {
        $sucesso_msg = 'Cadastro realizado com sucesso!';
    }
}

// Campos do usuário a serem usados no formulário
$nome = isset($_SESSION['user_nome']) ? $_SESSION['user_nome']:'';
$cpf = isset($_SESSION['user_cpf']) ? $_SESSION['user_cpf']:'';
$email = isset($_SESSION['user_email']) ? $_SESSION['user_email']:'';
$celular = isset($_SESSION['user_contato']) ? $_SESSION['user_contato']:'';
$endereco = isset($_SESSION['user_endereco']) ? $_SESSION['user_endereco']:'';
$numero = isset($_SESSION['user_numero']) ? $_SESSION['user_numero']:'';
$complemento = isset($_SESSION['user_complemento']) ? $_SESSION['user_complemento']:'';
$bairro = isset($_SESSION['user_bairro']) ? $_SESSION['user_bairro']:'';
$cidade = isset($_SESSION['user_cidade']) ? $_SESSION['user_cidade']:'';
$cep = isset($_SESSION['user_cep']) ? $_SESSION['user_cep']:'';
$data_nascimento = isset($_SESSION['user_data_nasc']) ? $_SESSION['user_data_nasc']:'';

// Verificar se o usuário já possui cadastro habitacional
$cadastro_existente = null;
$pode_cadastrar = true;
$mensagem_aviso = '';

try {
    require_once '../database/conect.php';
    
    $stmt_check = $conn->prepare("
        SELECT 
            cad_social_id,
            cad_social_protocolo, 
            cad_social_status, 
            cad_social_data_cadastro,
            DATEDIFF(NOW(), cad_social_data_cadastro) as dias_desde_cadastro
        FROM tb_cad_social 
        WHERE cad_usu_id = :usuario_id
        ORDER BY cad_social_data_cadastro DESC
        LIMIT 1
    ");
    $stmt_check->bindParam(':usuario_id', $_SESSION['user_id']);
    $stmt_check->execute();
    
    if ($stmt_check->rowCount() > 0) {
        $cadastro_existente = $stmt_check->fetch();
        $status = $cadastro_existente['cad_social_status'];
        $dias_desde = intval($cadastro_existente['dias_desde_cadastro']);
        $data_cadastro = date('d/m/Y', strtotime($cadastro_existente['cad_social_data_cadastro']));
        
        // Verificar se pode fazer novo cadastro
        switch ($status) {
            case 'PENDENTE DE ANÁLISE':
            case 'EM ANÁLISE':
            case 'EM ANÁLISE FINANCEIRA':
            case 'FINANCEIRO APROVADO':
            case 'EM FASE DE SELEÇÃO':
                $pode_cadastrar = false;
                $mensagem_aviso = "
                    <div class='alert alert-info' style='margin: 20px 0; padding: 15px; background-color: #d1ecf1; border: 1px solid #bee5eb; border-radius: 4px; border-left: 4px solid #17a2b8;'>
                        <h4 style='margin-top: 0; color: #0c5460;'><i class='fas fa-info-circle'></i> Cadastro em Andamento</h4>
                        <p style='margin-bottom: 10px;'><strong>Você já possui um cadastro em andamento no Programa Habitacional.</strong></p>
                        <p style='margin-bottom: 8px;'><strong>📋 Protocolo:</strong> {$cadastro_existente['cad_social_protocolo']}</p>
                        <p style='margin-bottom: 8px;'><strong>📊 Status:</strong> {$status}</p>
                        <p style='margin-bottom: 8px;'><strong>📅 Data do Cadastro:</strong> {$data_cadastro}</p>
                        <hr style='margin: 15px 0;'>
                        <p style='margin-bottom: 8px;'><strong>📞 Para acompanhar:</strong></p>
                        <p style='margin-bottom: 5px;'>• Telefone: (46) 98832-3832</p>
                        <p style='margin-bottom: 5px;'>• Email: assistenciasocial.sio@gmail.com</p>
                        <p style='margin-bottom: 0;'>• Ou consulte o status das suas solicitações no portal</p>
                    </div>
                ";
                break;
                
            case 'CONTEMPLADO':
                $pode_cadastrar = false;
                $mensagem_aviso = "
                    <div class='alert alert-success' style='margin: 20px 0; padding: 15px; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; border-left: 4px solid #28a745;'>
                        <h4 style='margin-top: 0; color: #155724;'><i class='fas fa-check-circle'></i> Parabéns! Você foi Contemplado!</h4>
                        <p style='margin-bottom: 10px;'><strong>Você já foi contemplado no Programa Habitacional.</strong></p>
                        <p style='margin-bottom: 8px;'><strong>📋 Protocolo:</strong> {$cadastro_existente['cad_social_protocolo']}</p>
                        <p style='margin-bottom: 8px;'><strong>📊 Status:</strong> {$status}</p>
                        <p style='margin-bottom: 8px;'><strong>📅 Data do Cadastro:</strong> {$data_cadastro}</p>
                        <hr style='margin: 15px 0;'>
                        <p style='margin-bottom: 0;'>Para mais informações, entre em contato com a Secretaria de Assistência Social.</p>
                    </div>
                ";
                break;
                
            case 'FINANCEIRO REPROVADO':
                $dias_necessarios = 30;
                if ($dias_desde < $dias_necessarios) {
                    $pode_cadastrar = false;
                    $dias_restantes = $dias_necessarios - $dias_desde;
                    $data_liberacao = date('d/m/Y', strtotime("+{$dias_restantes} days"));
                    
                    $mensagem_aviso = "
                        <div class='alert alert-warning' style='margin: 20px 0; padding: 15px; background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; border-left: 4px solid #ffc107;'>
                            <h4 style='margin-top: 0; color: #856404;'><i class='fas fa-clock'></i> Aguarde para Novo Cadastro</h4>
                            <p style='margin-bottom: 10px;'><strong>Seu último cadastro foi reprovado. Você deve aguardar para fazer um novo.</strong></p>
                            <p style='margin-bottom: 8px;'><strong>📋 Protocolo Anterior:</strong> {$cadastro_existente['cad_social_protocolo']}</p>
                            <p style='margin-bottom: 8px;'><strong>📅 Data do Cadastro:</strong> {$data_cadastro}</p>
                            <p style='margin-bottom: 8px;'><strong>⏳ Tempo Restante:</strong> {$dias_restantes} dias</p>
                            <p style='margin-bottom: 8px;'><strong>🗓️ Liberado em:</strong> {$data_liberacao}</p>
                            <hr style='margin: 15px 0;'>
                            <p style='margin-bottom: 0;'>Para esclarecimentos sobre a negativa, entre em contato com a Secretaria de Assistência Social.</p>
                        </div>
                    ";
                } else {
                    $mensagem_aviso = "
                        <div class='alert alert-info' style='margin: 20px 0; padding: 15px; background-color: #d1ecf1; border: 1px solid #bee5eb; border-radius: 4px; border-left: 4px solid #17a2b8;'>
                            <h4 style='margin-top: 0; color: #0c5460;'><i class='fas fa-info-circle'></i> Novo Cadastro Liberado</h4>
                            <p style='margin-bottom: 10px;'>Você pode realizar um novo cadastro no Programa Habitacional.</p>
                            <p style='margin-bottom: 8px;'><strong>📋 Cadastro Anterior:</strong> {$cadastro_existente['cad_social_protocolo']} (Status: {$status})</p>
                            <p style='margin-bottom: 0;'>Preencha o formulário abaixo para fazer sua nova solicitação.</p>
                        </div>
                    ";
                }
                break;
                
            case 'CADASTRO REPROVADO':
                $dias_necessarios = 30;
                if ($dias_desde < $dias_necessarios) {
                    $pode_cadastrar = false;
                    $dias_restantes = $dias_necessarios - $dias_desde;
                    $data_liberacao = date('d/m/Y', strtotime("+{$dias_restantes} days"));
                    
                    $mensagem_aviso = "
                        <div class='alert alert-warning' style='margin: 20px 0; padding: 15px; background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; border-left: 4px solid #ffc107;'>
                            <h4 style='margin-top: 0; color: #856404;'><i class='fas fa-clock'></i> Aguarde para Novo Cadastro</h4>
                            <p style='margin-bottom: 10px;'><strong>Seu último cadastro foi reprovado. Você deve aguardar para fazer um novo.</strong></p>
                            <p style='margin-bottom: 8px;'><strong>📋 Protocolo Anterior:</strong> {$cadastro_existente['cad_social_protocolo']}</p>
                            <p style='margin-bottom: 8px;'><strong>📅 Data do Cancelamento:</strong> {$data_cadastro}</p>
                            <p style='margin-bottom: 8px;'><strong>⏳ Tempo Restante:</strong> {$dias_restantes} dias</p>
                            <p style='margin-bottom: 8px;'><strong>🗓️ Liberado em:</strong> {$data_liberacao}</p>
                            <hr style='margin: 15px 0;'>
                            <p style='margin-bottom: 0;'>Para dúvidas, entre em contato com a Secretaria de Assistência Social.</p>
                        </div>
                    ";
                } else {
                    $mensagem_aviso = "
                        <div class='alert alert-info' style='margin: 20px 0; padding: 15px; background-color: #d1ecf1; border: 1px solid #bee5eb; border-radius: 4px; border-left: 4px solid #17a2b8;'>
                            <h4 style='margin-top: 0; color: #0c5460;'><i class='fas fa-info-circle'></i> Novo Cadastro Liberado</h4>
                            <p style='margin-bottom: 10px;'>Você pode realizar um novo cadastro no Programa Habitacional.</p>
                            <p style='margin-bottom: 8px;'><strong>📋 Cadastro Anterior:</strong> {$cadastro_existente['cad_social_protocolo']} (Status: {$status})</p>
                            <p style='margin-bottom: 0;'>Preencha o formulário abaixo para fazer sua nova solicitação.</p>
                        </div>
                    ";
                }
                break;
                              
            default:
                // Para outros status, mostrar info mas permitir cadastro
                $mensagem_aviso = "
                    <div class='alert alert-info' style='margin: 20px 0; padding: 15px; background-color: #d1ecf1; border: 1px solid #bee5eb; border-radius: 4px; border-left: 4px solid #17a2b8;'>
                        <h4 style='margin-top: 0; color: #0c5460;'><i class='fas fa-info-circle'></i> Cadastro Anterior Encontrado</h4>
                        <p style='margin-bottom: 8px;'><strong>📋 Protocolo:</strong> {$cadastro_existente['cad_social_protocolo']}</p>
                        <p style='margin-bottom: 8px;'><strong>📊 Status:</strong> {$status}</p>
                        <p style='margin-bottom: 8px;'><strong>📅 Data:</strong> {$data_cadastro}</p>
                        <p style='margin-bottom: 0;'>Você pode realizar um novo cadastro se necessário.</p>
                    </div>
                ";
                break;
        }
    }
    
} catch (Exception $e) {
    // Erro silencioso, não impede o carregamento da página
    error_log("Erro ao verificar cadastro anterior: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self' 'unsafe-inline' 'unsafe-eval' https:;">
    <meta name="referrer" content="same-origin">
    <title>Cadastro Habitacional - Eai Cidadão!</title>
    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="../css/socialhabitacao.css">
    <link rel="stylesheet" type="text/css" href="../css/ajax_style.css">
    <style>
        /* Estilos específicos para o formulário em etapas */
        .step-content {
            display: none;
        }
        
        .step-content.active {
            display: block;
            animation: fadeEffect 0.5s;
        }
        
        @keyframes fadeEffect {
            from {opacity: 0;}
            to {opacity: 1;}
        }
        
        .step-nav {
            display: flex;
            justify-content: space-between;
            list-style-type: none;
            padding: 0;
            margin: 0 0 30px 0;
            position: relative;
        }
        
        .step-nav::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 4px;
            background: #e0e0e0;
            z-index: 1;
        }
        
        .step-nav li {
            position: relative;
            z-index: 2;
            text-align: center;
            flex: 1;
        }
        
        .step-circle {
            display: flex;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #e0e0e0;
            color: #555;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin: 0 auto 10px;
            position: relative;
            z-index: 2;
            transition: all 0.3s;
        }
        
        .step-text {
            font-size: 14px;
            color: #555;
            transition: all 0.3s;
        }
        
        .step-nav li.active .step-circle {
            background: #0d47a1;
            color: white;
        }
        
        .step-nav li.active .step-text {
            color: #0d47a1;
            font-weight: 600;
        }
        
        .step-nav li.completed .step-circle {
            background: #4caf50;
            color: white;
        }
        
        .step-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }
        
        .step-actions button {
            min-width: 120px;
        }
        
        .btn-step-prev {
            background-color: #f5f5f5;
            color: #333;
            border: 1px solid #ccc;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-step-prev:hover {
            background-color: #e0e0e0;
        }
        
        .btn-step-next {
            background-color: #0d47a1;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-step-next:hover {
            background-color: #083378;
        }
        
        .btn-step-prev i, .btn-step-next i {
            margin: 0 8px;
        }
        
        .invalid {
            border: 1px solid #e91e63 !important;
            background-color: #fff8f8 !important;
        }
        
        /* Estilos para formulário desabilitado */
        .form-disabled {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .form-disabled::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.7);
            z-index: 1000;
        }
        
        @media (max-width: 768px) {
            .step-text {
                display: none;
            }
            
            .step-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .step-actions button {
                width: 100%;
            }
        }

        .alert {
            position: relative;
            animation: slideInDown 0.5s ease-out;
        }
        
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert i {
            opacity: 0.8;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        }
        
        /* Estilo para status message do JavaScript */
        .status-message {
            margin-bottom: 25px;
            padding: 15px 20px;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .status-message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-left: 4px solid #28a745;
        }
        
        .status-message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-left: 4px solid #dc3545;
        }
        
        .status-message.info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
            border-left: 4px solid #17a2b8;
        }
        #print-button {
            position: relative !important;
            z-index: 999999 !important;
            pointer-events: auto !important;
            opacity: 1 !important;
            cursor: pointer !important;
            display: inline-block !important;
        }

        #print-button:not(:disabled) {
            pointer-events: auto !important;
            opacity: 1 !important;
            cursor: pointer !important;
        }

        /* Container de botões sempre visível */
        .buttons-container {
            position: relative !important;
            z-index: 999998 !important;
            pointer-events: auto !important;
            display: flex !important;
        }

        /* Remover qualquer overlay que possa estar bloqueando */
        .form-disabled-overlay,
        .form-overlay,
        [class*="overlay"][style*="position: fixed"],
        [class*="overlay"][style*="position: absolute"] {
            display: none !important;
            pointer-events: none !important;
        }

        /* Garantir que elementos com z-index muito alto não bloqueiem */
        *[style*="z-index: 9999"] {
            pointer-events: none !important;
        }

        /* Exceto o botão de impressão */
        #print-button[style*="z-index: 9999"] {
            pointer-events: auto !important;
        }

        /* Destacar o botão de impressão */
        .highlight {
            animation: pulse-green 2s infinite !important;
        }

        @keyframes pulse-green {
            0% { 
                transform: scale(1.1);
                box-shadow: 0 4px 15px rgba(46, 125, 50, 0.3);
            }
            50% { 
                transform: scale(1.15);
                box-shadow: 0 6px 20px rgba(46, 125, 50, 0.5);
            }
            100% { 
                transform: scale(1.1);
                box-shadow: 0 4px 15px rgba(46, 125, 50, 0.3);
            }
        }

        /* Garantir que formulário desabilitado não bloqueia botões */
        #habitacao-form[disabled] .buttons-container,
        #habitacao-form[disabled] #print-button {
            pointer-events: auto !important;
            opacity: 1 !important;
        }
    </style>
</head>

<body>
    <div id="loading-overlay" class="loading-overlay">
        <div class="loading-spinner"></div>
        <div id="loading-text" class="loading-text">Processando seu cadastro...</div>
    </div>
    <div class="header">
        <div class="header-left">
            <div class="municipality-logo">
                <!-- Substitua pelo caminho da sua logo -->
                <img src="../img/logo_municipio.png" alt="Logo do Município">
            </div>
            <div class="title-container">
                <h1>Eai Cidadão!</h1>
                <h2 class="municipality-name">Município de Santa Izabel do Oeste</h2>
            </div>
        </div>
        <div class="header-buttons">
            <!-- Botão Voltar para Página de Assistência Social -->
            <a href="social.php" class="back-button">
                <i class="fas fa-arrow-left"></i> 
                Voltar para Assistência Social
            </a>
        </div>
    </div>

    <div class="container">
        <div id="status-message" class="status-message"></div>
        <h2 class="section-title"><i class="fas fa-building"></i> Cadastro para Programas Habitacionais</h2>
        
        <!-- Mostrar aviso se houver cadastro existente -->
        <?php if (!empty($mensagem_aviso)): ?>
            <?php echo $mensagem_aviso; ?>
        <?php endif; ?>
        
        <?php if ($pode_cadastrar): ?>
        <!-- Navegação em etapas -->
        <ul class="step-nav">
            <li class="active" data-step="1">
                <div class="step-circle">1</div>
                <div class="step-text">Responsável Familiar</div>
            </li>
            <li data-step="2">
                <div class="step-circle">2</div>
                <div class="step-text">Composição Familiar</div>
            </li>
            <li data-step="3">
                <div class="step-circle">3</div>
                <div class="step-text">Filiação</div>
            </li>
            <li data-step="4">
                <div class="step-circle">4</div>
                <div class="step-text">Situação Trabalhista</div>
            </li>
            <li data-step="5">
                <div class="step-circle">5</div>
                <div class="step-text">Endereço</div>
            </li>
            <li data-step="6">
                <div class="step-circle">6</div>
                <div class="step-text">Contato</div>
            </li>
            <li data-step="7">
                <div class="step-circle">7</div>
                <div class="step-text">Interesse</div>
            </li>
        </ul>
        <?php if (!empty($erro_msg)): ?>
        <div class="alert alert-danger" style="
            margin-bottom: 25px; 
            padding: 15px 20px; 
            background-color: #f8d7da; 
            color: #721c24; 
            border: 1px solid #f5c6cb; 
            border-left: 4px solid #dc3545;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        ">
            <div style="display: flex; align-items: center;">
                <i class="fas fa-exclamation-triangle" style="margin-right: 10px; font-size: 1.2rem;"></i>
                <div>
                    <strong>Erro no Cadastro:</strong><br>
                    <?php echo htmlspecialchars($erro_msg); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Mensagens de Sucesso (caso JavaScript não funcione) -->
        <?php if (!empty($sucesso_msg) && !isset($_GET['success'])): ?>
        <div class="alert alert-success" style="
            margin-bottom: 25px; 
            padding: 15px 20px; 
            background-color: #d4edda; 
            color: #155724; 
            border: 1px solid #c3e6cb; 
            border-left: 4px solid #28a745;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        ">
            <div style="display: flex; align-items: center;">
                <i class="fas fa-check-circle" style="margin-right: 10px; font-size: 1.2rem;"></i>
                <div>
                    <?php echo htmlspecialchars($sucesso_msg); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Status Message Container (para JavaScript) -->
        <div id="status-message" style="display: none;"></div>
        
        <form id="habitacao-form" method="post" action="../controller/processar_habitacao.php" enctype="multipart/form-data">
            
            <!-- STEP 1: INFORMAÇÕES DO RESPONSÁVEL FAMILIAR -->
            <div class="step-content active" id="step-1">
                <div class="form-section">
                    <h3 class="form-section-title">Informações do Responsável Familiar</h3>
                    
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="nome" class="required">Nome completo</label>
                            <input type="text" class="form-control uppercase-input" id="nome" name="nome" value="<?php echo $nome; ?>" required readonly>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group third-width">
                            <label for="cpf" class="required">CPF</label>
                            <div id="cpf-feedback" class="field-feedback"></div>
                            <input type="text" class="form-control" id="cpf" name="cpf" value="<?php echo $cpf; ?>" required maxlength="14" placeholder="000.000.000-00" readonly>
                        </div>
                        <div class="form-group third-width">
                            <label for="cpf_documento" class="required">Anexar documento (CPF)</label>
                            <div class="file-input-container">
                                <input type="file" class="file-input" id="cpf_documento" name="cpf_documento" accept=".pdf,.jpg,.jpeg,.png" required>
                                <div class="upload-progress-container">
                                    <div class="upload-progress-bar"></div>
                                    <div class="upload-progress-text">0%</div>
                                </div>
                            </div>
                            <small class="form-text">Formatos aceitos: PDF, JPG, JPEG, PNG (Max: 5MB)</small>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group third-width">
                            <label for="rg">RG (opcional)</label>
                            <input type="text" class="form-control uppercase-input" id="rg" name="rg">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group third-width">
                            <label for="nacionalidade" class="required">Nacionalidade</label>
                            <input type="text" class="form-control uppercase-input" id="nacionalidade" name="nacionalidade" value="BRASIL" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group third-width">
                            <label for="nome_social_opcao" class="required">Possui nome social?</label>
                            <select class="form-control" id="nome_social_opcao" name="nome_social_opcao" required>
                                <option value="NÃO">NÃO</option>
                                <option value="SIM">SIM</option>
                            </select>
                        </div>
                        <div class="form-group third-width" id="nome_social_campo" style="display: none;">
                            <label for="nome_social">Nome Social</label>
                            <input type="text" class="form-control uppercase-input" id="nome_social" name="nome_social">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group third-width">
                            <label for="genero" class="required">Gênero</label>
                            <select class="form-control" id="genero" name="genero" required>
                                <option value="">Selecione</option>
                                <option value="MASCULINO">MASCULINO</option>
                                <option value="FEMININO">FEMININO</option>
                                <option value="OUTRO">OUTRO</option>
                            </select>
                        </div>
                        <div class="form-group third-width">
                            <label for="data_nascimento" class="required">Data de Nascimento</label>
                            <div id="data_nascimento-feedback" class="field-feedback"></div>
                            <input type="date" class="form-control" id="data_nascimento" name="data_nascimento" value="<?php echo $data_nascimento; ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group third-width">
                            <label for="raca" class="required">Raça</label>
                            <select class="form-control" id="raca" name="raca" required>
                                <option value="">Selecione</option>
                                <option value="BRANCA">BRANCA</option>
                                <option value="PRETA">PRETA</option>
                                <option value="PARDA">PARDA</option>
                                <option value="AMARELA">AMARELA</option>
                                <option value="INDÍGENA">INDÍGENA</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group third-width">
                            <label for="cad_unico">Nº do Cad Único</label>
                            <input type="text" class="form-control" id="cad_unico" name="cad_unico">
                        </div>
                        <div class="form-group third-width">
                            <label for="nis">NIS</label>
                            <input type="text" class="form-control" id="nis" name="nis">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group third-width">
                            <label for="escolaridade" class="required">Escolaridade</label>
                            <select class="form-control" id="escolaridade" name="escolaridade" required>
                                <option value="">Selecione</option>
                                <option value="ANALFABETO">ANALFABETO</option>
                                <option value="ALFABETIZADO">ALFABETIZADO</option>
                                <option value="FUNDAMENTAL INCOMPLETO">FUNDAMENTAL INCOMPLETO</option>
                                <option value="FUNDAMENTAL COMPLETO">FUNDAMENTAL COMPLETO</option>
                                <option value="MÉDIO INCOMPLETO">MÉDIO INCOMPLETO</option>
                                <option value="MÉDIO COMPLETO">MÉDIO COMPLETO</option>
                                <option value="SUPERIOR INCOMPLETO">SUPERIOR INCOMPLETO</option>
                                <option value="SUPERIOR COMPLETO">SUPERIOR COMPLETO</option>
                                <option value="PÓS-GRADUAÇÃO">PÓS-GRADUAÇÃO</option>
                                <option value="MESTRADO">MESTRADO</option>
                                <option value="DOUTORADO">DOUTORADO</option>
                            </select>
                        </div>
                        <div class="form-group third-width">
                            <label for="escolaridade_documento">Anexar comprovante de escolaridade</label>
                            <div class="file-input-container">
                                <input type="file" class="file-input" id="escolaridade_documento" name="escolaridade_documento" accept=".pdf,.jpg,.jpeg,.png">
                                <div class="upload-progress-container">
                                    <div class="upload-progress-bar"></div>
                                    <div class="upload-progress-text">0%</div>
                                </div>
                            </div>
                            <small class="form-text">Formatos aceitos: PDF, JPG, JPEG, PNG (Max: 5MB)</small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group third-width">
                            <label for="estado_civil" class="required">Estado Civil</label>
                            <select class="form-control" id="estado_civil" name="estado_civil" required>
                                <option value="">Selecione</option>
                                <option value="SOLTEIRO(A)">SOLTEIRO(A)</option>
                                <option value="CASADO(A)">CASADO(A)</option>
                                <option value="DIVORCIADO(A)">DIVORCIADO(A)</option>
                                <option value="VIÚVO(A)">VIÚVO(A)</option>
                                <option value="UNIÃO ESTÁVEL/AMASIADO(A)">UNIÃO ESTÁVEL/AMASIADO(A)</option>
                                <option value="SEPARADO(A)">SEPARADO(A)</option>
                            </select>
                        </div>
                        <div class="form-group third-width" id="viuvo_doc_campo" style="display: none;">
                            <label for="viuvo_documento">Anexar certidão de óbito</label>
                            <div class="file-input-container">
                                <input type="file" class="file-input" id="viuvo_documento" name="viuvo_documento" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                            <small class="form-text">Formatos aceitos: PDF, JPG, JPEG, PNG (Max: 5MB)</small>
                        </div>
                    </div>

                    <!-- Campos para cônjuge (aparecem apenas quando União Estável/Casado é selecionado) -->
                    <div id="conjuge_campos" class="dependent-fields">
                        <h4 class="dependent-title"><i class="fas fa-user-friends"></i> Dados do Cônjuge/Companheiro(a)</h4>
                        
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="conjuge_nome" class="required">Nome completo do cônjuge</label>
                                <input type="text" class="form-control uppercase-input" id="conjuge_nome" name="conjuge_nome">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group third-width">
                                <label for="conjuge_cpf" class="required">CPF do cônjuge</label>
                                <input type="text" class="form-control" id="conjuge_cpf" name="conjuge_cpf" maxlength="14" placeholder="000.000.000-00">
                            </div>
                            <div class="form-group third-width">
                                <label for="conjuge_rg">RG do cônjuge</label>
                                <input type="text" class="form-control uppercase-input" id="conjuge_rg" name="conjuge_rg">
                            </div>
                            <div class="form-group third-width">
                                <label for="conjuge_data_nascimento" class="required">Data de Nascimento</label>
                                <input type="date" class="form-control" id="conjuge_data_nascimento" name="conjuge_data_nascimento">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group half-width">
                                <label for="conjuge_renda" class="required">Possui renda?</label>
                                <select class="form-control" id="conjuge_renda" name="conjuge_renda">
                                    <option value="NÃO">NÃO</option>
                                    <option value="SIM">SIM</option>
                                </select>
                            </div>
                            <div class="form-group half-width" id="conjuge_renda_doc" style="display: none;">
                                <label for="conjuge_comprovante_renda">Anexar comprovante de renda</label>
                                <div class="file-input-container">
                                    <input type="file" class="file-input" id="conjuge_comprovante_renda" name="conjuge_comprovante_renda" accept=".pdf,.jpg,.jpeg,.png">
                                </div>
                                <small class="form-text">Formatos aceitos: PDF, JPG, JPEG, PNG (Max: 5MB)</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group half-width">
                            <label for="deficiencia" class="required">Possui deficiência?</label>
                            <select class="form-control" id="deficiencia" name="deficiencia" required>
                                <option value="NÃO">NÃO</option>
                                <option value="AUDITIVA-SURDEZ">AUDITIVA-SURDEZ</option>
                                <option value="AUDITIVA-MUDEZ">AUDITIVA-MUDEZ</option>
                                <option value="CADEIRANTE">CADEIRANTE</option>
                                <option value="FISICA">FISICA</option>
                                <option value="INTELECTUAL">INTELECTUAL</option>
                                <option value="NANISMO">NANISMO</option>
                                <option value="VISUAL">VISUAL</option>
                                <option value="TEA (TRANST. ESPECTRO AUTISTA)">TEA (TRANST. ESPECTRO AUTISTA)</option>
                            </select>
                        </div>
                        <div class="form-group half-width" id="deficiencia_fisica_campo" style="display: none;">
                            <label for="deficiencia_fisica_detalhe" class="required">Especifique a deficiência física</label>
                            <input type="text" class="form-control uppercase-input" id="deficiencia_fisica_detalhe" name="deficiencia_fisica_detalhe">
                        </div>
                    </div>
                    
                    <div class="form-row" id="laudo_deficiencia_campo" style="display: none;">
                        <div class="form-group full-width">
                            <label for="laudo_deficiencia">Anexar laudo médico da deficiência</label>
                            <div class="file-input-container">
                                <input type="file" class="file-input" id="laudo_deficiencia" name="laudo_deficiencia" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                            <small class="form-text">Formatos aceitos: PDF, JPG, JPEG, PNG (Max: 5MB)</small>
                        </div>
                    </div>
                </div>
                
                <div class="step-actions">
                    <div></div> <!-- Espaço em branco para alinhamento -->
                    <button type="button" class="btn-step-next" data-step="2">Próximo <i class="fas fa-arrow-right"></i></button>
                </div>
            </div>

            <!-- STEP 2: COMPOSIÇÃO FAMILIAR -->
            <div class="step-content" id="step-2">
                <div class="form-section">
                    <h3 class="form-section-title">Composição Familiar</h3>
                    
                    <div class="form-row">
                        <div class="form-group half-width">
                            <label for="num_dependentes" class="required">Número de Dependentes</label>
                            <select class="form-control" id="num_dependentes" name="num_dependentes" required>
                                <option value="0">0</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                            </select>
                        </div>
                    </div>

                    <!-- Campos para dependentes (aparecem conforme número selecionado) -->
                    <div id="dependentes_container">
                        <!-- Os campos de dependentes serão adicionados dinamicamente pelo JavaScript -->
                    </div>
                </div>
                
                <div class="step-actions">
                    <button type="button" class="btn-step-prev" data-step="1"><i class="fas fa-arrow-left"></i> Anterior</button>
                    <button type="button" class="btn-step-next" data-step="3">Próximo <i class="fas fa-arrow-right"></i></button>
                </div>
            </div>

            <!-- STEP 3: FILIAÇÃO -->
            <div class="step-content" id="step-3">
                <div class="form-section">
                    <h3 class="form-section-title">Filiação</h3>
                    
                    <div class="form-row">
                        <div class="form-group half-width">
                            <label for="nome_mae" class="required">Nome completo da Mãe</label>
                            <input type="text" class="form-control uppercase-input" id="nome_mae" name="nome_mae" required>
                        </div>
                        <div class="form-group half-width">
                            <label for="nome_pai">Nome completo do Pai</label>
                            <input type="text" class="form-control uppercase-input" id="nome_pai" name="nome_pai">
                        </div>
                    </div>
                </div>
                
                <div class="step-actions">
                    <button type="button" class="btn-step-prev" data-step="2"><i class="fas fa-arrow-left"></i> Anterior</button>
                    <button type="button" class="btn-step-next" data-step="4">Próximo <i class="fas fa-arrow-right"></i></button>
                </div>
            </div>

            <!-- STEP 4: SITUAÇÃO TRABALHISTA -->
            <div class="step-content" id="step-4">
                <div class="form-section">
                    <h3 class="form-section-title">Situação Trabalhista</h3>
                    
                    <div class="form-row">
                        <div class="form-group half-width">
                            <label for="situacao_trabalho" class="required">Situação</label>
                            <select class="form-control" id="situacao_trabalho" name="situacao_trabalho" required>
                                <option value="">Selecione</option>
                                <option value="DESEMPREGADO">DESEMPREGADO</option>
                                <option value="AUTÔNOMO">AUTÔNOMO</option>
                                <option value="EMPREGADO COM CARTEIRA ASSINADA">EMPREGADO COM CARTEIRA ASSINADA</option>
                                <option value="EMPREGADO SEM CARTEIRA ASSINADA">EMPREGADO SEM CARTEIRA ASSINADA</option>
                                <option value="APOSENTADO">APOSENTADO</option>
                                <option value="PENSIONISTA">PENSIONISTA</option>
                            </select>
                        </div>
                    </div>

                    <!-- Campos para empregados (aparecem apenas quando Empregado é selecionado) -->
                    <div id="emprego_campos" class="dependent-fields">
                        <div class="form-row" id="pro">
                            <div class="form-group third-width">
                                <label for="profissao" class="required">Profissão</label>
                                <input type="text" class="form-control uppercase-input" id="profissao" name="profissao">
                            </div>
                            <div class="form-group third-width" id="emp">
                                <label for="empregador" class="required">Empregador</label>
                                <input type="text" class="form-control uppercase-input" id="empregador" name="empregador">
                            </div>
                            <div class="form-group third-width" id="car">
                                <label for="cargo" class="required">Cargo</label>
                                <input type="text" class="form-control uppercase-input" id="cargo" name="cargo">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group third-width" id="ram">
                                <label for="ramo_atividade" class="required">Ramo de atividade</label>
                                <input type="text" class="form-control uppercase-input" id="ramo_atividade" name="ramo_atividade">
                            </div>
                            <div class="form-group third-width" id="tem">
                                <label for="tempo_servico" class="required">Tempo de Serviço</label>
                                <input type="text" class="form-control" id="tempo_servico" name="tempo_servico" placeholder="Ex: 2 anos e 6 meses">
                            </div>
                            <div class="form-group third-width" id="anex">
                                <label for="carteira_trabalho" class="required">Anexar Comprovante de Renda</label>
                                <div class="file-input-container">
                                    <input type="file" class="file-input" id="carteira_trabalho" name="carteira_trabalho" accept=".pdf,.jpg,.jpeg,.png">
                                    <div class="upload-progress-container">
                                        <div class="upload-progress-bar"></div>
                                        <div class="upload-progress-text">0%</div>
                                    </div>
                                </div>
                                <small class="form-text">Formatos aceitos: PDF, JPG, JPEG, PNG (Max: 5MB)</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="step-actions">
                    <button type="button" class="btn-step-prev" data-step="3"><i class="fas fa-arrow-left"></i> Anterior</button>
                    <button type="button" class="btn-step-next" data-step="5">Próximo <i class="fas fa-arrow-right"></i></button>
                </div>
            </div>

            <!-- STEP 5: ENDEREÇO -->
            <div class="step-content" id="step-5">
                <div class="form-section">
                    <h3 class="form-section-title">Endereço</h3>
                    
                    <div class="form-row">
                        <div class="form-group third-width">
                            <label for="tipo_moradia" class="required">Moradia</label>
                            <select class="form-control" id="tipo_moradia" name="tipo_moradia" required>
                                <option value="">Selecione</option>
                                <option value="CASA">CASA</option>
                                <option value="APARTAMENTO">APARTAMENTO</option>
                                <option value="KITNET">KITNET</option>
                                <option value="CÔMODO">CÔMODO</option>
                                <option value="OUTRO">OUTRO</option>
                            </select>
                        </div>
                        <div class="form-group third-width">
                            <label for="situacao_propriedade" class="required">Situação da propriedade</label>
                            <select class="form-control" id="situacao_propriedade" name="situacao_propriedade" required>
                                <option value="">Selecione</option>
                                <option value="PRÓPRIA COM TITULARIDADE">PRÓPRIA COM TITULARIDADE</option>
                                <option value="PRÓPRIA SEM TITULARIDADE">PRÓPRIA SEM TITULARIDADE</option>
                                <option value="ALUGADA">ALUGADA</option>
                                <option value="CEDIDA">CEDIDA</option>
                                <option value="FINANCIADA">FINANCIADA</option>
                                <option value="OCUPAÇÃO">OCUPAÇÃO</option>
                            </select>
                        </div>
                        <div class="form-group third-width" id="valor_aluguel_campo" style="display: none;">
                            <label for="valor_aluguel" class="required">Valor do Aluguel</label>
                            <input type="text" class="form-control" id="valor_aluguel" name="valor_aluguel" placeholder="R$ 0,00">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group half-width">
                            <label for="rua" class="required">Rua</label>
                            <input type="text" class="form-control uppercase-input" id="rua" name="rua" value="<?php echo $endereco; ?>" required>
                        </div>
                        <div class="form-group quarter-width">
                            <label for="numero" class="required">Número</label>
                            <input type="text" class="form-control" id="numero" name="numero" value="<?php echo $numero; ?>" required>
                        </div>
                        <div class="form-group quarter-width">
                            <label for="complemento">Complemento</label>
                            <input type="text" class="form-control uppercase-input" id="complemento" name="complemento" value="<?php echo $complemento; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group third-width">
                            <label for="bairro" class="required">Bairro</label>
                            <input type="text" class="form-control uppercase-input" id="bairro" name="bairro" value="<?php echo $bairro; ?>" required>
                        </div>
                        <div class="form-group third-width">
                            <label for="cidade" class="required">Cidade</label>
                            <input type="text" class="form-control uppercase-input" id="cidade" name="cidade" value="<?php echo $cidade; ?>" required>
                        </div>
                        <div class="form-group third-width">
                            <label for="cep" class="required">CEP</label>
                            <input type="text" class="form-control" id="cep" name="cep" value="<?php echo $cep; ?>" required maxlength="9" placeholder="00000-000">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="ponto_referencia">Ponto de Referência</label>
                            <input type="text" class="form-control uppercase-input" id="ponto_referencia" name="ponto_referencia">
                        </div>
                    </div>
                </div>
                
                <div class="step-actions">
                    <button type="button" class="btn-step-prev" data-step="4"><i class="fas fa-arrow-left"></i> Anterior</button>
                    <button type="button" class="btn-step-next" data-step="6">Próximo <i class="fas fa-arrow-right"></i></button>
                </div>
            </div>

            <!-- STEP 6: CONTATO -->
            <div class="step-content" id="step-6">
                <div class="form-section">
                    <h3 class="form-section-title">Contato</h3>
                    
                    <div class="form-row">
                        <div class="form-group third-width">
                            <label for="telefone">Telefone</label>
                            <input type="text" class="form-control" id="telefone" name="telefone" value="" placeholder="(00) 00000-0000">
                        </div>
                        <div class="form-group third-width">
                            <label for="celular" class="required">Celular</label>
                            <input type="text" class="form-control" id="celular" name="celular" value="<?php echo $celular; ?>" required placeholder="(00) 00000-0000">
                        </div>
                        <div class="form-group third-width">
                            <label for="email" class="required">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo $email; ?>" required readonly>
                        </div>
                    </div>
                </div>
                
                <div class="step-actions">
                    <button type="button" class="btn-step-prev" data-step="5"><i class="fas fa-arrow-left"></i> Anterior</button>
                    <button type="button" class="btn-step-next" data-step="7">Próximo <i class="fas fa-arrow-right"></i></button>
                </div>
            </div>

            <!-- STEP 7: INTERESSE -->
            <div class="step-content" id="step-7">
                <div class="form-section">
                    <h3 class="form-section-title">Interesse</h3>
                    
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="programa_interesse" class="required">Interesse por</label>
                            <select class="form-control" id="programa_interesse" name="programa_interesse" required>
                                <option value="">Selecione</option>
                                <option value="HABITASIO">HABITASIO</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group full-width">
                            <div style="display: flex; align-items: flex-start; margin-top: 15px; background-color: #f9f9f9; padding: 15px; border-radius: 8px; border-left: 3px solid #0d47a1;">
                                <input type="checkbox" id="autoriza_credito" name="autoriza_credito" style="margin-right: 10px; margin-top: 3px;" required>
                                <label for="autoriza_credito" class="required">
Autorizo a instituição responsável pela execução do Programa Habitacional HABITASIO a acessar minhas informações financeiras e de crédito, incluindo, mas não se limitando, aos dados constantes nos sistemas de proteção ao crédito como SERASA, SPC e outros bancos de dados creditícios, bem como no Sistema de Informações de Crédito (SCR) do Banco Central do Brasil, exclusivamente para fins de validação e credenciamento, como etapa preliminar necessária à minha participação no processo de seleção e/ou sorteio para aquisição de unidade habitacional.<br>

Estou ciente de que esta autorização se destina exclusivamente à análise cadastral e de crédito, como condição inicial para participação no processo, não configurando, sob nenhuma hipótese, direito adquirido à aprovação definitiva, tampouco garantia de contemplação ou aquisição do imóvel.<br>

Tenho ciência, ainda, de que a validação de crédito é apenas a primeira etapa do processo, sendo que eventuais aprovações posteriores dependerão do atendimento aos demais requisitos previstos no regulamento do programa, bem como da disponibilidade de unidades habitacionais.<br>

Por fim, declaro estar ciente de que esta autorização não representa violação ao sigilo bancário ou creditício, uma vez que se dá mediante meu consentimento expresso e específico para esta finalidade.</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group full-width">
                            <div style="display: flex; align-items: flex-start; margin-top: 15px;">
                                <input type="checkbox" id="autoriza_email" name="autoriza_email" style="margin-right: 10px; margin-top: 3px;">
                                <label for="autoriza_email">Autorizo receber informações sobre os programas habitacionais por e-mail e por telefone/WhatsApp.</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="step-actions">
                    <button type="button" class="btn-step-prev" data-step="6"><i class="fas fa-arrow-left"></i> Anterior</button>
                    <button type="submit" class="btn-primary" id="submit-button">
                        <i class="fas fa-save"></i> Enviar Cadastro
                    </button>
                </div>
            </div>

        </form>
        
        <?php else: ?>
        <div style="text-align: center; padding: 40px 20px;">
            <p style="font-size: 1.1rem; margin-bottom: 20px;">
                <i class="fas fa-info-circle" style="color: #0d47a1; font-size: 1.5rem; margin-right: 10px;"></i>
                O formulário de cadastro não está disponível no momento devido ao status do seu cadastro anterior.
            </p>
            <a href="social.php" class="btn-primary" style="display: inline-flex; align-items: center; text-decoration: none;">
                <i class="fas fa-arrow-left" style="margin-right: 10px;"></i>
                Voltar para Assistência Social
            </a>
        </div>
        <?php endif; ?>
    </div>

    <footer>
        &copy; 2025 Prefeitura Municipal de Santa Izabel do Oeste. Todos os direitos reservados.
    </footer>

    <script src="../js/form_habitacao.js"></script>
    <script src="../js/ajax_habitacao.js"></script>
</body>

</html>