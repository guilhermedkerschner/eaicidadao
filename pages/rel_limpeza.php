<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Solicitação - Limpeza de Estradas - Eai Cidadão!</title>
    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #64b5f6 0%, #6aabec 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }

        .header {
            width: 100%;
            max-width: 1200px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .header-left {
            display: flex;
            align-items: center;
        }

        .municipality-logo {
            width: 80px;
            height: 80px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 20px;
        }

        .municipality-logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .title-container h1 {
            color: #0d47a1;
            font-weight: 600;
            font-size: 1.8rem;
        }

        .municipality-name {
            color: #000000;
            font-size: 1rem;
            text-transform: uppercase;
            font-weight: 700;
        }

        .header-right a {
            background-color: #0d47a1;
            color: #fff;
            padding: 8px 16px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }

        .header-right a i {
            margin-right: 8px;
        }

        .header-right a:hover {
            background-color: #083378;
            transform: translateY(-2px);
        }

        .container {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            padding: 30px;
            width: 100%;
            max-width: 1000px;
            z-index: 1;
            margin-bottom: 20px;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .breadcrumb a {
            color: #0d47a1;
            text-decoration: none;
            transition: color 0.3s;
        }

        .breadcrumb a:hover {
            color: #ff8f00;
            text-decoration: underline;
        }

        .breadcrumb .separator {
            margin: 0 8px;
            color: #666;
        }

        .breadcrumb .current {
            color: #666;
            font-weight: 500;
        }

        .report-header {
            background-color: #e8f5e9;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .report-title {
            color: #2e7d32;
            font-size: 1.4rem;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
        }

        .report-title i {
            margin-right: 10px;
        }

        .report-subtitle {
            color: #555;
            font-size: 1rem;
        }

        .report-protocol {
            background-color: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            min-width: 200px;
        }

        .protocol-number {
            font-size: 1.6rem;
            font-weight: 700;
            color: #2e7d32;
            margin-bottom: 5px;
        }

        .protocol-text {
            font-size: 0.9rem;
            color: #666;
        }

        .report-datetime {
            font-size: 0.9rem;
            color: #666;
            margin-top: 5px;
        }

        .report-section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 1.1rem;
            color: #2e7d32;
            padding-bottom: 8px;
            border-bottom: 1px solid #e0e0e0;
            margin-bottom: 15px;
        }

        .info-row {
            display: flex;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .info-group {
            flex: 1;
            min-width: 250px;
            margin-bottom: 15px;
            padding-right: 20px;
        }

        .info-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .info-value {
            color: #555;
            line-height: 1.5;
            word-break: break-word;
        }

        .status-box {
            background-color: #e8f5e9;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
        }

        .status-title {
            font-size: 1.1rem;
            color: #2e7d32;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }

        .status-title i {
            margin-right: 10px;
        }

        .status-info {
            color: #555;
            line-height: 1.5;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn i {
            margin-right: 8px;
        }

        .btn-primary {
            background-color: #2e7d32;
            color: white;
        }

        .btn-primary:hover {
            background-color: #1b5e20;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: #ff8f00;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #e65100;
            transform: translateY(-2px);
        }

        .btn-outline {
            background-color: transparent;
            color: #2e7d32;
            border: 1px solid #2e7d32;
        }

        .btn-outline:hover {
            background-color: #e8f5e9;
            transform: translateY(-2px);
        }

        footer {
            background-color: rgba(255, 255, 255, 0.95);
            width: 100%;
            max-width: 1200px;
            padding: 15px;
            text-align: center;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            font-size: 0.9rem;
            color: #555;
            margin-top: auto;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .header, footer, .breadcrumb, .action-buttons {
                display: none;
            }

            .container {
                box-shadow: none;
                width: 100%;
                max-width: 100%;
                padding: 0;
                margin: 0;
            }

            .report-header {
                border: 1px solid #ddd;
                background-color: #f9f9f9;
            }

            .section-title {
                color: #000;
            }
        }

        @media (max-width: 768px) {
            .report-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .report-protocol {
                width: 100%;
            }

            .info-group {
                width: 100%;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="header-left">
            <div class="municipality-logo">
                <!-- Substitua pelo caminho da sua logo -->
                <img src="../images/logo_municipio.png" alt="Logo do Município">
            </div>
            <div class="title-container">
                <h1>Eai Cidadão!</h1>
                <h2 class="municipality-name">Município de Santa Izabel do Oeste</h2>
            </div>
        </div>
        <div class="header-right">
            <a href="../index.php"><i class="fas fa-home"></i> Página Inicial</a>
        </div>
    </div>

    <div class="container">
        <div class="breadcrumb">
            <a href="index.php">Página Inicial</a>
            <span class="separator">›</span>
            <a href="../rodoviario.php">Setor Rodoviário</a>
            <span class="separator">›</span>
            <a href="formulario_limpeza.php">Solicitação de Limpeza de Estradas</a>
            <span class="separator">›</span>
            <span class="current">Relatório de Solicitação</span>
        </div>

        <div class="report-header">
            <div>
                <h2 class="report-title"><i class="fas fa-file-alt"></i> Relatório de Solicitação</h2>
                <p class="report-subtitle">Serviços de Limpeza de Estradas - Setor Rodoviário</p>
                <p class="report-datetime" id="reportDateTime"></p>
            </div>
            <div class="report-protocol">
                <div class="protocol-number" id="protocolNumber">-</div>
                <div class="protocol-text">Número de Protocolo</div>
            </div>
        </div>

        <div class="report-section">
            <h3 class="section-title">Dados do Solicitante</h3>
            <div class="info-row">
                <div class="info-group">
                    <div class="info-label">Nome Completo</div>
                    <div class="info-value" id="nome">-</div>
                </div>
                <div class="info-group">
                    <div class="info-label">CPF</div>
                    <div class="info-value" id="cpf">-</div>
                </div>
            </div>
            <div class="info-row">
                <div class="info-group">
                    <div class="info-label">Telefone</div>
                    <div class="info-value" id="telefone">-</div>
                </div>
                <div class="info-group">
                    <div class="info-label">E-mail</div>
                    <div class="info-value" id="email">-</div>
                </div>
            </div>
        </div>

        <div class="report-section">
            <h3 class="section-title">Dados da Solicitação</h3>
            <div class="info-row">
                <div class="info-group">
                    <div class="info-label">Tipo de Serviço</div>
                    <div class="info-value" id="tipoServico">-</div>
                </div>
                <div class="info-group">
                    <div class="info-label">Data e Hora da Solicitação</div>
                    <div class="info-value" id="dataSolicitacao">-</div>
                </div>
            </div>
            <div class="info-row">
                <div class="info-group">
                    <div class="info-label">Trecho da Estrada</div>
                    <div class="info-value" id="trecho">-</div>
                </div>
                <div class="info-group">
                    <div class="info-label">Extensão Aproximada</div>
                    <div class="info-value" id="extensao">-</div>
                </div>
            </div>
            <div class="info-row">
                <div class="info-group">
                    <div class="info-label">Ponto de Referência</div>
                    <div class="info-value" id="referencia">-</div>
                </div>
                <div class="info-group">
                    <div class="info-label">Coordenadas GPS</div>
                    <div class="info-value" id="gps">-</div>
                </div>
            </div>
            <div class="info-row">
                <div class="info-group">
                    <div class="info-label">Nível de Urgência</div>
                    <div class="info-value" id="urgencia">-</div>
                </div>
                <div class="info-group">
                    <div class="info-label">Fotos Anexadas</div>
                    <div class="info-value" id="fotos">-</div>
                </div>
            </div>
        </div>

        <div class="report-section">
            <h3 class="section-title">Descrição da Necessidade</h3>
            <div class="info-value" id="descricao">-</div>
        </div>

        <div class="report-section">
            <h3 class="section-title">Observações Adicionais</h3>
            <div class="info-value" id="observacoes">-</div>
        </div>

        <div class="status-box">
            <h3 class="status-title"><i class="fas fa-info-circle"></i> Informações Importantes</h3>
            <ul class="status-info">
                <li>Esta solicitação foi registrada em nosso sistema e será analisada pela equipe técnica.</li>
                <li>O prazo estimado para análise inicial é de até 3 dias úteis.</li>
                <li>Você receberá atualizações sobre o status da sua solicitação por e-mail e/ou telefone.</li>
                <li>Para consultar o andamento, utilize o número de protocolo no site ou entre em contato com o Setor Rodoviário.</li>
                <li>Em caso de dúvidas, entre em contato pelo telefone (46) 3552-1237 ou pelo e-mail rodoviario@santaizabel.pr.gov.br.</li>
            </ul>
        </div>
        
        <div id="email-status" style="display: none;" class="status-box" style="background-color: #e8f5e9; margin-top: 20px;">
            <h3 class="status-title"><i class="fas fa-envelope"></i> Confirmação de Envio por Email</h3>
            <p class="status-info">Uma cópia deste relatório foi enviada para o email: <span id="email-enviado">-</span></p>
        </div>

        <div class="action-buttons">
            <button class="btn btn-outline" onclick="window.print()">
                <i class="fas fa-print"></i> Imprimir Relatório
            </button>
            <button id="btn-enviar-email" class="btn btn-outline" style="display: none;" onclick="enviarEmailSimulado()">
                <i class="fas fa-envelope"></i> Reenviar por Email
            </button>
            <a href="../rodoviario.php" class="btn btn-secondary">
                <i class="fas fa-truck-moving"></i> Voltar ao Setor Rodoviário
            </a>
            <a href="acompanhamento.php" class="btn btn-primary">
                <i class="fas fa-tasks"></i> Acompanhar Solicitação
            </a>
        </div>
    </div>

    <footer>
        &copy; 2025 Prefeitura Municipal de Santa Izabel do Oeste. Todos os direitos reservados.
    </footer>

    <script>
        // Função para simular o envio de email
        function enviarEmailSimulado() {
            const email = document.getElementById('email').textContent;
            
            if (!email || email === 'Não informado') {
                alert('Não é possível enviar email. Endereço de email não informado.');
                return;
            }
            
            // Mostrar indicador de carregamento no botão
            const btnEnviarEmail = document.getElementById('btn-enviar-email');
            const originalButtonText = btnEnviarEmail.innerHTML;
            btnEnviarEmail.disabled = true;
            btnEnviarEmail.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
            
            // Simular tempo de processamento
            setTimeout(() => {
                alert(`Relatório reenviado com sucesso para o email: ${email}`);
                
                // Restaurar o botão
                btnEnviarEmail.disabled = false;
                btnEnviarEmail.innerHTML = originalButtonText;
            }, 1500);
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Recuperar os dados do localStorage
            const solicitacaoData = JSON.parse(localStorage.getItem('solicitacaoLimpeza'));
            
            if (solicitacaoData) {
                // Preencher os campos do relatório com os dados do localStorage
                document.getElementById('protocolNumber').textContent = solicitacaoData.protocolo;
                document.getElementById('reportDateTime').textContent = 'Gerado em: ' + solicitacaoData.data + ' às ' + solicitacaoData.hora;
                
                document.getElementById('nome').textContent = solicitacaoData.nome;
                document.getElementById('cpf').textContent = solicitacaoData.cpf;
                document.getElementById('telefone').textContent = solicitacaoData.telefone;
                document.getElementById('email').textContent = solicitacaoData.email;
                
                document.getElementById('tipoServico').textContent = solicitacaoData.tipoServico;
                document.getElementById('dataSolicitacao').textContent = solicitacaoData.data + ' às ' + solicitacaoData.hora;
                document.getElementById('trecho').textContent = solicitacaoData.trecho;
                document.getElementById('extensao').textContent = solicitacaoData.extensao;
                document.getElementById('referencia').textContent = solicitacaoData.referencia;
                document.getElementById('gps').textContent = solicitacaoData.gps;
                document.getElementById('urgencia').textContent = solicitacaoData.urgencia;
                document.getElementById('fotos').textContent = solicitacaoData.fotos;
                
                document.getElementById('descricao').textContent = solicitacaoData.descricao;
                document.getElementById('observacoes').textContent = solicitacaoData.observacoes;
                
                // Verificar se o usuário optou por receber o relatório por email
                if (solicitacaoData.enviarEmail && solicitacaoData.email && solicitacaoData.email !== 'Não informado') {
                    // Mostrar a mensagem de confirmação de envio
                    const emailStatus = document.getElementById('email-status');
                    emailStatus.style.display = 'block';
                    document.getElementById('email-enviado').textContent = solicitacaoData.email;
                    
                    // Habilitar o botão de reenvio
                    document.getElementById('btn-enviar-email').style.display = 'inline-flex';
                }
            } else {
                // Se não houver dados no localStorage, mostrar mensagem e redirecionar
                alert('Não foi possível recuperar os dados da solicitação. Você será redirecionado para a página inicial.');
                window.location.href = 'setor-rodoviario.html';
            }
        });
    </script>
</body>

</html>