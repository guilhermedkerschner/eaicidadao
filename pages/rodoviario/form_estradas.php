<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitação de Serviços de Estradas - Eai Cidadão!</title>
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
            max-width: 1200px;
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

        .section-title {
            color: #0d47a1;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e0e0;
            display: flex;
            align-items: center;
            font-size: 1.5rem;
        }

        .section-title i {
            margin-right: 10px;
            color: #ff8f00;
            font-size: 1.5rem;
        }

        .intro-text {
            margin-bottom: 30px;
            line-height: 1.6;
            color: #333;
        }

        .service-description {
            background-color: #f8f9fa;
            border-left: 4px solid #ff8f00;
            padding: 20px;
            border-radius: 0 8px 8px 0;
            margin-bottom: 30px;
        }

        .service-description h3 {
            color: #ff8f00;
            margin-bottom: 15px;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
        }

        .service-description h3 i {
            margin-right: 10px;
        }

        .service-description p {
            color: #555;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .service-description ul {
            margin-left: 20px;
            margin-bottom: 0;
            color: #555;
        }

        .service-description li {
            margin-bottom: 8px;
        }

        .form-section {
            margin-top: 30px;
        }

        .form-title {
            font-size: 1.3rem;
            color: #0d47a1;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: #ff8f00;
            outline: none;
            box-shadow: 0 0 0 2px rgba(255, 143, 0, 0.1);
        }

        .form-text {
            font-size: 0.85rem;
            color: #666;
            margin-top: 5px;
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 0;
        }

        .form-row .form-group {
            flex: 1;
            min-width: 0;
        }

        .form-section-title {
            font-size: 1.1rem;
            color: #0d47a1;
            margin: 30px 0 20px 0;
            padding-bottom: 8px;
            border-bottom: 1px dashed #ddd;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
        }

        .btn-primary {
            background-color: #ff8f00;
            color: white;
        }

        .btn-primary:hover {
            background-color: #e65100;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: #e0e0e0;
            color: #333;
        }

        .btn-secondary:hover {
            background-color: #bdbdbd;
        }

        .form-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
        }

        .required-field::after {
            content: "*";
            color: #c62828;
            margin-left: 4px;
        }

        .form-note {
            background-color: #e8f5e9;
            border-left: 4px solid #4caf50;
            padding: 15px;
            border-radius: 0 4px 4px 0;
            margin: 20px 0;
        }

        .form-note p {
            color: #333;
            margin: 0;
            font-size: 0.9rem;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-left: 10px;
        }

        .status-info {
            background-color: #e3f2fd;
            color: #0d47a1;
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

        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .header {
                flex-direction: column;
                text-align: center;
            }
            
            .header-left {
                margin-bottom: 15px;
                flex-direction: column;
            }
            
            .municipality-logo {
                margin-right: 0;
                margin-bottom: 10px;
            }
            
            .title-container {
                text-align: center;
            }

            .form-buttons {
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
                <img src="../../img/logo_municipio.png" alt="Logo do Município">
            </div>
            <div class="title-container">
                <h1>Eai Cidadão!</h1>
                <h2 class="municipality-name">Município de Santa Izabel do Oeste</h2>
            </div>
        </div>
        <div class="header-right">
            <a href="../../index.php"><i class="fas fa-home"></i> Página Inicial</a>
        </div>
    </div>

    <div class="container">
        <div class="breadcrumb">
            <a href="../../index.php">Página Inicial</a>
            <span class="separator">›</span>
            <a href="rodoviario.php">Setor Rodoviário</a>
            <span class="separator">›</span>
            <span class="current">Solicitação de Serviços de Estradas</span>
        </div>

        <h2 class="section-title"><i class="fas fa-road"></i> Solicitação de Serviços de Estradas</h2>

        <div class="service-description">
            <h3><i class="fas fa-info-circle"></i> O que são serviços de Estradas?</h3>
            <p>A manutenção adequada das estradas rurais e vicinais é essencial para garantir o escoamento da produção agrícola e o acesso às comunidades rurais. Estradas em boas condições contribuem para a segurança dos usuários e para o desenvolvimento econômico do município.</p>
            
            <p>A Secretaria de Obras realiza os seguintes serviços relacionados a estradas:</p>
            <ul>
                <li><strong>Patrolamento:</strong> Nivelamento da superfície da estrada para corrigir irregularidades</li>
                <li><strong>Recuperação de trechos danificados:</strong> Reparos em pontos críticos afetados por erosão ou desgaste excessivo</li>
                <li><strong>Alargamento de vias:</strong> Ampliação da largura da pista para melhorar a trafegabilidade</li>
                <li><strong>Controle de erosão:</strong> Medidas para prevenir e corrigir problemas de erosão nas margens e na própria pista</li>
            </ul>
        </div>

        <div class="form-section">
            <h3 class="form-title">Formulário de Solicitação</h3>
            
            <div class="form-note">
                <p><i class="fas fa-exclamation-circle"></i> Os campos marcados com asterisco (*) são de preenchimento obrigatório.</p>
            </div>
            
            <form id="estradasForm">
                <h4 class="form-section-title">Dados do Solicitante</h4>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nome" class="required-field">Nome Completo</label>
                        <input type="text" id="nome" name="nome" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="cpf" class="required-field">CPF</label>
                        <input type="text" id="cpf" name="cpf" class="form-control" placeholder="000.000.000-00" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="telefone" class="required-field">Telefone</label>
                        <input type="tel" id="telefone" name="telefone" class="form-control" placeholder="(00) 00000-0000" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="exemplo@email.com">
                        <small class="form-text">Utilizaremos o e-mail para enviar atualizações sobre sua solicitação.</small>
                    </div>
                </div>
                
                <h4 class="form-section-title">Dados da Solicitação</h4>
                
                <div class="form-group">
                    <label for="tipo-servico" class="required-field">Tipo de Serviço</label>
                    <select id="tipo-servico" name="tipo-servico" class="form-control" required>
                        <option value="">-- Selecione o tipo de serviço --</option>
                        <option value="patrolamento">Patrolamento</option>
                        <option value="recuperacao">Recuperação de trecho danificado</option>
                        <option value="alargamento">Alargamento de via</option>
                        <option value="erosao">Controle de erosão</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="trecho" class="required-field">Trecho da Estrada</label>
                    <input type="text" id="trecho" name="trecho" class="form-control" placeholder="Ex: Estrada Santa Izabel-Linha Gaúcha, próximo ao Km 5" required>
                    <small class="form-text">Informe a localização mais exata possível do trecho que necessita do serviço.</small>
                </div>
                
                <div class="form-group">
                    <label for="extensao" class="required-field">Extensão Aproximada (metros)</label>
                    <input type="number" id="extensao" name="extensao" class="form-control" min="1" required>
                    <small class="form-text">Informe a extensão aproximada do trecho em metros.</small>
                </div>
                
                <div class="form-group">
                    <label for="referencia">Ponto de Referência</label>
                    <input type="text" id="referencia" name="referencia" class="form-control" placeholder="Ex: Próximo à propriedade do Sr. João / Entre a ponte e o entroncamento da Linha São Pedro">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="gps">Coordenadas GPS</label>
                        <div style="display: flex; gap: 10px;">
                            <input type="text" id="gps" name="gps" class="form-control" placeholder="Ex: -25.123456, -53.123456" style="flex: 1;">
                            <button type="button" id="btn-obter-localizacao" class="btn btn-primary" onclick="obterLocalizacao()" style="white-space: nowrap;">
                                <i class="fas fa-map-marker-alt"></i> Obter Localização
                            </button>
                        </div>
                        <small class="form-text">Clique no botão para capturar automaticamente sua localização atual.</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="descricao" class="required-field">Descrição do Problema</label>
                    <textarea id="descricao" name="descricao" class="form-control" rows="5" placeholder="Descreva detalhadamente o problema e como ele está afetando a trafegabilidade da estrada..." required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="urgencia" class="required-field">Nível de Urgência</label>
                    <select id="urgencia" name="urgencia" class="form-control" required>
                        <option value="">-- Selecione o nível de urgência --</option>
                        <option value="baixa">Baixa - Manutenção preventiva</option>
                        <option value="media">Média - Problemas visíveis, mas ainda transitável</option>
                        <option value="alta">Alta - Difícil transitar, risco de acidentes</option>
                        <option value="critica">Crítica - Intransitável ou com risco iminente</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="fotos">Anexar Fotos</label>
                    <input type="file" id="fotos" name="fotos" class="form-control" accept="image/*" multiple>
                    <small class="form-text">Você pode selecionar até 3 imagens (máximo 5MB cada) que mostrem o problema.</small>
                </div>
                
                <div class="form-group">
                    <label for="observacoes">Observações Adicionais</label>
                    <textarea id="observacoes" name="observacoes" class="form-control" rows="3" placeholder="Informações complementares que possam ajudar na avaliação..."></textarea>
                </div>
                
                <div class="form-group" style="display: flex; align-items: center;">
                    <input type="checkbox" id="enviar-email" name="enviar-email" style="margin-right: 10px;" checked>
                    <label for="enviar-email">Enviar uma cópia deste relatório para meu email</label>
                </div>
                
                <div class="form-buttons">
                    <a href="../rodoviario.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Enviar Solicitação</button>
                </div>
            </form>
        </div>
    </div>

    <footer>
        &copy; 2025 Prefeitura Municipal de Santa Izabel do Oeste. Todos os direitos reservados.
    </footer>

    <script>
        // Função para obter localização GPS
        function obterLocalizacao() {
            const gpsInput = document.getElementById('gps');
            const statusElement = document.createElement('small');
            statusElement.style.display = 'block';
            statusElement.style.marginTop = '5px';
            
            // Remover mensagens de status anteriores
            const existingStatus = gpsInput.parentNode.querySelector('.location-status');
            if (existingStatus) {
                existingStatus.remove();
            }
            
            statusElement.className = 'location-status';
            statusElement.textContent = 'Obtendo localização...';
            statusElement.style.color = '#0d47a1';
            gpsInput.parentNode.appendChild(statusElement);
            
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const latitude = position.coords.latitude;
                        const longitude = position.coords.longitude;
                        gpsInput.value = latitude.toFixed(6) + ', ' + longitude.toFixed(6);
                        statusElement.textContent = 'Localização obtida com sucesso!';
                        statusElement.style.color = '#2e7d32';
                        
                        // Remover a mensagem após 3 segundos
                        setTimeout(function() {
                            statusElement.remove();
                        }, 3000);
                    },
                    function(error) {
                        console.error("Erro ao obter localização:", error);
                        statusElement.textContent = 'Erro ao obter localização. Por favor, tente novamente ou insira manualmente.';
                        statusElement.style.color = '#c62828';
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    }
                );
            } else {
                statusElement.textContent = 'Geolocalização não é suportada pelo seu navegador.';
                statusElement.style.color = '#c62828';
            }
        }
        
        // Associar a função ao botão de obter localização
        document.getElementById('btn-obter-localizacao').addEventListener('click', obterLocalizacao);
        
        // Validação para o número de fotos
        document.getElementById('fotos').addEventListener('change', function() {
            const files = this.files;
            let errorMessage = '';
            
            // Remover mensagem de erro anterior
            const existingError = this.parentNode.querySelector('.file-error');
            if (existingError) {
                existingError.remove();
            }
            
            // Verificar número de arquivos
            if (files.length > 3) {
                errorMessage = 'Você só pode anexar no máximo 3 imagens.';
            } else if (files.length > 0) {
                // Verificar tamanho de cada arquivo
                for (let i = 0; i < files.length; i++) {
                    const fileSize = files[i].size / 1024 / 1024; // em MB
                    if (fileSize > 5) {
                        errorMessage = 'O arquivo ' + files[i].name + ' excede o limite de 5MB.';
                        break;
                    }
                    
                    // Verificar tipo de arquivo
                    const fileType = files[i].type;
                    if (!fileType.startsWith('image/')) {
                        errorMessage = 'O arquivo ' + files[i].name + ' não é uma imagem válida.';
                        break;
                    }
                }
            }
            
            // Exibir mensagem de erro se necessário
            if (errorMessage) {
                const errorElement = document.createElement('small');
                errorElement.className = 'file-error form-text';
                errorElement.style.color = '#c62828';
                errorElement.textContent = errorMessage;
                this.parentNode.appendChild(errorElement);
                this.value = ''; // Limpar a seleção
            } else if (files.length > 0) {
                // Exibir mensagem de sucesso
                const successElement = document.createElement('small');
                successElement.className = 'file-error form-text';
                successElement.style.color = '#2e7d32';
                successElement.textContent = files.length + ' arquivo(s) selecionado(s) com sucesso.';
                this.parentNode.appendChild(successElement);
                
                // Remover mensagem após 3 segundos
                setTimeout(() => {
                    successElement.remove();
                }, 3000);
            }
        });
        
        // Manipulação do envio do formulário
        document.getElementById('estradasForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Mostrar indicador de carregamento
            const submitButton = this.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
            
            try {
                // Criar FormData com todos os campos do formulário
                const formData = new FormData(this);
                
                // Adicionar o estado do checkbox de envio por email
                formData.append('enviarEmail', document.getElementById('enviar-email').checked);
                
                // Enviar os dados para o servidor
                const response = await fetch('/api/solicitacoes/estradas', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Armazenar dados da solicitação para exibição na página de relatório
                    localStorage.setItem('solicitacaoEstradas', JSON.stringify({
                        protocolo: result.protocolo,
                        data: new Date().toLocaleDateString('pt-BR'),
                        hora: new Date().toLocaleTimeString('pt-BR'),
                        nome: document.getElementById('nome').value,
                        cpf: document.getElementById('cpf').value,
                        telefone: document.getElementById('telefone').value,
                        email: document.getElementById('email').value || 'Não informado',
                        tipoServico: document.getElementById('tipo-servico').options[document.getElementById('tipo-servico').selectedIndex].text,
                        trecho: document.getElementById('trecho').value,
                        extensao: document.getElementById('extensao').value + ' metros',
                        referencia: document.getElementById('referencia').value || 'Não informado',
                        gps: document.getElementById('gps').value || 'Não informado',
                        urgencia: document.getElementById('urgencia').options[document.getElementById('urgencia').selectedIndex].text,
                        descricao: document.getElementById('descricao').value,
                        observacoes: document.getElementById('observacoes').value || 'Não informado',
                        fotos: document.getElementById('fotos').files.length + ' foto(s) anexada(s)',
                        enviarEmail: document.getElementById('enviar-email').checked
                    }));
                    
                    // Redirecionar para a página de relatório
                    window.location.href = 'relatorio-estradas.html';
                } else {
                    throw new Error(result.message || 'Erro ao enviar solicitação');
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Ocorreu um erro ao enviar a solicitação: ' + error.message);
                
                // Restaurar o botão
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            }
        });
    </script>
</body>

</html>