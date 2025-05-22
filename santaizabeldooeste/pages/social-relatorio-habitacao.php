<?php
// Inicia a sessão
session_start();

if (!isset($_SESSION['user_logado'])) {
    header("Location: ../acessdenied.php"); 
    exit;
}

// Verifica se foi passado um ID de inscrição
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: socialnotsubscribe.php");
    exit;
}

$inscricao_id = $_GET['id'];

$inscricao = [
    'id' => $inscricao_id,
    'data_inscricao' => date('Y-m-d H:i:s'),
    'protocolo' => $_SESSION['user_prot_hab'],
    'status' => 'PENDENTE DE ANÁLISE',
    'nome' => $_SESSION['user_nome'] ?? 'Nome do Responsável',
    'cpf' => $_SESSION['user_cpf'] ?? '000.000.000-00',
    'email' => $_SESSION['user_email'] ?? 'email@exemplo.com',
    'celular' => $_SESSION['user_contato'] ?? '(00) 00000-0000',
    'endereco' => $_SESSION['user_endereco'] ?? 'Endereço',
    'numero' => $_SESSION['user_numero'] ?? '000',
    'complemento' => $_SESSION['user_complemento'] ?? '',
    'bairro' => $_SESSION['user_bairro'] ?? 'Bairro',
    'cidade' => $_SESSION['user_cidade'] ?? 'Santa Izabel do Oeste',
    'cep' => $_SESSION['user_cep'] ?? '00000-000',
    'programa_interesse' => 'HABITASIO',
    'data_nascimento' => date('d/m/Y', strtotime($_SESSION['user_data_nasc'] ?? '2000-01-01')),
];

// Função para formatar datas
function formatarData($data) {
    return date('d/m/Y H:i:s', strtotime($data));
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprovante de Inscrição - Programa Habitacional</title>
    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="../css/socialhabitacao.css">
    <style>
        /* Estilos específicos para o comprovante */
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white;
                font-size: 12pt;
            }
            .container {
                box-shadow: none;
                padding: 0;
                width: 100%;
                max-width: 100%;
            }
            .inscricao-header {
                background-color: #f0f0f0 !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            .footer-info {
                background-color: #f0f0f0 !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
                position: fixed;
                bottom: 0;
                width: 100%;
            }
        }

        .comprovante-container {
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #fff;
            margin-bottom: 20px;
        }

        .inscricao-header {
            background-color: #f0f0f0;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        .inscricao-header h2 {
            margin: 0;
            color: #0d47a1;
            font-size: 1.4rem;
        }

        .protocolo-container {
            text-align: center;
            margin: 20px 0;
            padding: 10px;
            border: 2px dashed #0d47a1;
            border-radius: 8px;
        }

        .protocolo-numero {
            font-size: 1.8rem;
            font-weight: bold;
            color: #0d47a1;
            letter-spacing: 1px;
        }

        .status-tag {
            display: inline-block;
            padding: 6px 12px;
            background-color: #ff9800;
            color: white;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9rem;
            margin-top: 8px;
        }

        .info-section {
            margin-bottom: 20px;
        }

        .info-section h3 {
            color: #0d47a1;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 8px;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }

        .info-row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }

        .info-label {
            font-weight: bold;
            width: 200px;
            color: #555;
        }

        .info-value {
            flex: 1;
        }

        .data-inscricao {
            text-align: right;
            font-style: italic;
            color: #666;
            margin-top: 10px;
        }

        .qrcode-container {
            text-align: center;
            margin: 30px 0;
        }

        .qrcode {
            width: 150px;
            height: 150px;
            background-color: #f0f0f0;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qrcode img {
            max-width: 100%;
            max-height: 100%;
        }

        .footer-info {
            background-color: #f0f0f0;
            padding: 15px;
            border-radius: 8px;
            margin-top: 30px;
            font-size: 0.9rem;
            color: #555;
            text-align: center;
        }

        .attention-text {
            color: #e91e63;
            font-weight: bold;
        }

        .button-container {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
        }

        .action-button {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .action-button i {
            margin-right: 8px;
        }

        .print-btn {
            background-color: #2e7d32;
            color: white;
            border: none;
        }

        .print-btn:hover {
            background-color: #1b5e20;
        }

        .back-btn {
            background-color: #0d47a1;
            color: white;
            border: none;
        }

        .back-btn:hover {
            background-color: #083378;
        }
    </style>
</head>
<body>
    <div class="header no-print">
        <div class="header-left">
            <div class="municipality-logo">
                <img src="../img/logo_municipio.png" alt="Logo do Município">
            </div>
            <div class="title-container">
                <h1>Eai Cidadão!</h1>
                <h2 class="municipality-name">Município de Santa Izabel do Oeste</h2>
            </div>
        </div>
        <div class="header-buttons">
            <a href="social.php" class="back-button">
                <i class="fas fa-arrow-left"></i> 
                Voltar para Assistência Social
            </a>
        </div>
    </div>

    <div class="container">
        <h2 class="section-title no-print"><i class="fas fa-file-alt"></i> Comprovante de Inscrição</h2>
        
        <div class="comprovante-container">
            <div class="inscricao-header">
                <h2>COMPROVANTE DE INSCRIÇÃO - PROGRAMA HABITACIONAL</h2>
            </div>
            
            <div class="protocolo-container">
                <div>PROTOCOLO</div>
                <div class="protocolo-numero"><?php echo $inscricao['protocolo']; ?></div>
                <div class="status-tag"><?php echo $inscricao['status']; ?></div>
            </div>
            
            <div class="info-section">
                <h3><i class="fas fa-user"></i> Dados do Responsável</h3>
                
                <div class="info-row">
                    <div class="info-label">Nome Completo:</div>
                    <div class="info-value"><?php echo $inscricao['nome']; ?></div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">CPF:</div>
                    <div class="info-value"><?php echo $inscricao['cpf']; ?></div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Data de Nascimento:</div>
                    <div class="info-value"><?php echo $inscricao['data_nascimento']; ?></div>
                </div>
            </div>
            
            <div class="info-section">
                <h3><i class="fas fa-home"></i> Endereço</h3>
                
                <div class="info-row">
                    <div class="info-label">Logradouro:</div>
                    <div class="info-value"><?php echo $inscricao['endereco']; ?>, <?php echo $inscricao['numero']; ?> 
                    <?php echo (!empty($inscricao['complemento'])) ? $inscricao['complemento'] : ''; ?></div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Bairro:</div>
                    <div class="info-value"><?php echo $inscricao['bairro']; ?></div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Cidade/CEP:</div>
                    <div class="info-value"><?php echo $inscricao['cidade']; ?> - <?php echo $inscricao['cep']; ?></div>
                </div>
            </div>
            
            <div class="info-section">
                <h3><i class="fas fa-phone-alt"></i> Contato</h3>
                
                <div class="info-row">
                    <div class="info-label">Celular:</div>
                    <div class="info-value"><?php echo $inscricao['celular']; ?></div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">E-mail:</div>
                    <div class="info-value"><?php echo $inscricao['email']; ?></div>
                </div>
            </div>
            
            <div class="info-section">
                <h3><i class="fas fa-building"></i> Programa Habitacional</h3>
                
                <div class="info-row">
                    <div class="info-label">Programa de Interesse:</div>
                    <div class="info-value"><?php echo $inscricao['programa_interesse']; ?></div>
                </div>
            </div>
            
            
            <div class="data-inscricao">
                Data de Inscrição: <?php echo formatarData($inscricao['data_inscricao']); ?>
            </div>
            
            <div class="footer-info">
                <p><span class="attention-text">ATENÇÃO:</span> Este comprovante é o documento oficial da sua inscrição. Guarde-o com você.</p>
                <p>Para verificar o status da sua inscrição, acesse o portal do Eai Cidadão! ou entre em contato através do telefone ()Colocar telefone assistencia.</p>
                <p>Endereço: Rua Angico, Nº 731, Bairro Santo Antônio - Santa Izabel do Oeste - PR</p>
                <p>Atendimento: Segunda a Sexta, das 8h às 17h</p>
            </div>
        </div>
        
        <div class="button-container no-print">
            <button class="action-button print-btn" onclick="window.print();">
                <i class="fas fa-print"></i> Imprimir Comprovante
            </button>
            <a href="social.php" class="action-button back-btn">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <footer class="no-print">
        &copy; 2025 Prefeitura Municipal de Santa Izabel do Oeste. Todos os direitos reservados.
    </footer>
</body>
</html>