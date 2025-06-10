/**
 * Script AJAX para o formulário de Cadastro Habitacional
 * Sistema Eai Cidadão! - Prefeitura de Santa Izabel do Oeste
 * Versão Corrigida - GARANTINDO que o botão de impressão NUNCA seja bloqueado
 */
document.addEventListener('DOMContentLoaded', function() {
    // Referência ao formulário
    const form = document.getElementById('habitacao-form');
    
    // Referências aos elementos de feedback
    const loadingOverlay = document.getElementById('loading-overlay');
    const statusMessage = document.getElementById('status-message');
    
    // Função para mostrar loading
    function showLoading(message = 'Processando seu cadastro...') {
        if (loadingOverlay) {
            document.getElementById('loading-text').textContent = message;
            loadingOverlay.style.display = 'flex';
        }
    }
    
    // Função para esconder loading
    function hideLoading() {
        if (loadingOverlay) {
            loadingOverlay.style.display = 'none';
        }
    }
    
    // Função para mostrar mensagem de status
    function showStatusMessage(message, type = 'success') {
        if (statusMessage) {
            statusMessage.textContent = message;
            statusMessage.className = `status-message ${type}`;
            statusMessage.style.display = 'block';
            
            // Scroll até a mensagem
            statusMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Auto-ocultar após alguns segundos se for mensagem de sucesso
            if (type === 'success') {
                setTimeout(() => {
                    statusMessage.style.display = 'none';
                }, 8000);
            }
        }
    }
    
    // Função para reabilitar o botão em caso de erro
    function reabilitarBotaoCadastrar(button, originalContent) {
        if (button) {
            button.disabled = false;
            button.innerHTML = originalContent;
            button.style.opacity = '1';
            button.style.cursor = 'pointer';
        }
    }
    
    // Função CORRIGIDA para desabilitar formulário SEM AFETAR botão de impressão
    function desabilitarFormularioSeguro() {
        console.log('📝 Desabilitando formulário de forma SEGURA...');
        
        // APENAS desabilitar inputs do formulário (exceto botão de impressão)
        const formElements = form.querySelectorAll('input:not(#print-button), select, textarea');
        formElements.forEach(element => {
            // Pular elementos que não são do formulário principal
            if (element.id === 'print-button' || 
                element.closest('.buttons-container') ||
                element.classList.contains('btn-print')) {
                return; // NÃO mexer nesses elementos
            }
            
            element.disabled = true;
            element.style.backgroundColor = '#f8f8f8';
            element.style.color = '#999';
            element.style.opacity = '0.7';
        });
        
        // Desabilitar APENAS os botões de navegação das etapas (não outros botões)
        const navButtons = document.querySelectorAll('.btn-step-prev, .btn-step-next, #submit-button');
        navButtons.forEach(button => {
            if (button.id !== 'print-button') {
                button.disabled = true;
                button.style.opacity = '0.5';
                button.style.cursor = 'not-allowed';
            }
        });
        
        console.log('✅ Formulário desabilitado SEM afetar botão de impressão');
    }
    
    // Função NOVA para garantir que o botão de impressão NUNCA seja bloqueado
    function protegerBotaoImpressao(inscricaoId) {
        const printButton = document.getElementById('print-button');
        const buttonsContainer = document.querySelector('.buttons-container');
        
        if (printButton) {
            // Configurações de segurança máxima
            printButton.disabled = false;
            printButton.style.opacity = '1';
            printButton.style.cursor = 'pointer';
            printButton.style.pointerEvents = 'auto';
            printButton.style.position = 'relative';
            printButton.style.zIndex = '999999';
            printButton.style.display = 'inline-block';
            printButton.setAttribute('data-inscricao-id', inscricaoId);
            
            // Remover qualquer event listener antigo e adicionar novo
            const newPrintButton = printButton.cloneNode(true);
            printButton.parentNode.replaceChild(newPrintButton, printButton);
            
            // Configurar evento de forma definitiva
            newPrintButton.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                const id = this.getAttribute('data-inscricao-id');
                console.log('🖨️ BOTÃO DE IMPRESSÃO CLICADO - ID:', id);
                if (id) {
                    window.open(`social-relatorio-habitacao.php?id=${id}`, '_blank');
                } else {
                    alert('ID da inscrição não encontrado para impressão.');
                }
                return false;
            };
            
            // Garantir que o container também esteja livre
            if (buttonsContainer) {
                buttonsContainer.style.display = 'flex';
                buttonsContainer.style.position = 'relative';
                buttonsContainer.style.zIndex = '999998';
                buttonsContainer.style.pointerEvents = 'auto';
            }
            
            console.log('🛡️ Botão de impressão PROTEGIDO com ID:', inscricaoId);
        }
    }
    
    // Função para lidar com a submissão do formulário via AJAX
    function handleFormSubmit(e) {
        e.preventDefault();
        
        // Validação do formulário antes de enviar
        if (!validateForm()) {
            return false;
        }
        
        // Desabilitar o botão de submit imediatamente para evitar duplo envio
        const submitButton = document.getElementById('submit-button');
        const originalButtonContent = submitButton ? submitButton.innerHTML : '';
        
        // Alterar aparência do botão para indicar processamento
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
            submitButton.style.opacity = '0.6';
            submitButton.style.cursor = 'not-allowed';
        }
        
        // Mostrar overlay de carregamento
        showLoading();
        
        // Criar FormData do formulário para enviar dados e arquivos
        const formData = new FormData(form);
        
        // Configurar a requisição AJAX
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '../controller/processar_habitacao.php', true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        // Configurar manipulador de sucesso
        xhr.onload = function() {
            hideLoading();
            
            console.log('=== DEBUG AJAX RESPONSE ===');
            console.log('Status:', xhr.status);
            console.log('Response Text:', xhr.responseText);
            console.log('==============================');
            
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    
                    console.log('=== DEBUG PARSED RESPONSE ===');
                    console.log('Response Object:', response);
                    console.log('Status:', response.status);
                    console.log('================================');
                    
                    if (response.status === 'success') {
                        console.log('✅ Cadastro realizado com sucesso!');
                        console.log('Protocolo:', response.protocolo);
                        console.log('ID da Inscrição:', response.inscricao_id);
                        
                        // 1. PRIMEIRO: Proteger o botão de impressão IMEDIATAMENTE
                        protegerBotaoImpressao(response.inscricao_id);
                        
                        // 2. Ocultar APENAS o botão de cadastrar
                        if (submitButton) {
                            submitButton.style.display = 'none';
                        }
                        
                        // 3. Mostrar mensagem de sucesso
                        if (statusMessage) {
                            statusMessage.innerHTML = `
                                <div style="
                                    background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
                                    border: 2px solid #4caf50;
                                    border-radius: 12px;
                                    padding: 30px;
                                    text-align: center;
                                    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
                                    margin: 20px 0;
                                ">
                                    <div style="font-size: 4rem; color: #4caf50; margin-bottom: 20px;">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <h2 style="color: #2e7d32; margin-bottom: 15px;">
                                        Cadastro Realizado com Sucesso!
                                    </h2>
                                    <div style="
                                        background: rgba(46, 125, 50, 0.1);
                                        padding: 15px;
                                        border-radius: 8px;
                                        margin: 20px 0;
                                        border-left: 4px solid #4caf50;
                                    ">
                                        <p style="margin: 5px 0; font-size: 1.1rem;"><strong>Protocolo:</strong> ${response.protocolo}</p>
                                        <p style="margin: 5px 0; color: #666; font-size: 0.9rem;">
                                            <i class="fas fa-info-circle"></i> 
                                            Guarde estas informações para consultas futuras
                                        </p>
                                    </div>
                                    <p style="font-size: 1.1rem; color: #2e7d32; margin: 20px 0;">
                                        <strong>Use o botão "Imprimir Comprovante" abaixo!</strong>
                                    </p>
                                </div>
                            `;
                            statusMessage.className = 'status-message success';
                            statusMessage.style.display = 'block';
                            
                            // Rolar até a mensagem
                            statusMessage.scrollIntoView({ 
                                behavior: 'smooth', 
                                block: 'center' 
                            });
                        }
                        
                        // 4. Desabilitar formulário de forma SEGURA (sem afetar botão de impressão)
                        desabilitarFormularioSeguro();
                        
                        // 5. REFORÇAR proteção do botão após 1 segundo
                        setTimeout(() => {
                            protegerBotaoImpressao(response.inscricao_id);
                        }, 1000);
                        
                        // 6. REFORÇAR proteção do botão após 3 segundos
                        setTimeout(() => {
                            protegerBotaoImpressao(response.inscricao_id);
                        }, 3000);
                        
                        // 7. COMENTAR ou REMOVER redirecionamento automático para evitar interferência
                        /*
                        setTimeout(() => {
                            console.log('🔄 Redirecionando para o relatório...');
                            window.location.href = `social-relatorio-habitacao.php?id=${response.inscricao_id}`;
                        }, 3000);
                        */
                        
                        console.log('🎉 Processo de sucesso concluído - Botão de impressão LIVRE!');
                        
                    } else {
                        // Erro - reabilitar o botão
                        reabilitarBotaoCadastrar(submitButton, originalButtonContent);
                        showStatusMessage(response.message || 'Erro desconhecido', 'error');
                    }
                } catch (e) {
                    console.error('❌ Erro ao fazer parse do JSON:', e);
                    console.error('Response que causou erro:', xhr.responseText);
                    // Erro de parsing - reabilitar o botão
                    reabilitarBotaoCadastrar(submitButton, originalButtonContent);
                    showStatusMessage('Erro ao processar a resposta do servidor. Por favor, tente novamente.', 'error');
                }
            } else {
                console.error('❌ Status HTTP diferente de 200-299:', xhr.status);
                // Erro HTTP - reabilitar o botão
                reabilitarBotaoCadastrar(submitButton, originalButtonContent);
                showStatusMessage('Erro de comunicação com o servidor. Por favor, tente novamente mais tarde.', 'error');
            }
        };
        
        xhr.onerror = function() {
            hideLoading();
            // Erro de rede - reabilitar o botão
            reabilitarBotaoCadastrar(submitButton, originalButtonContent);
            showStatusMessage('Falha na comunicação com o servidor. Por favor, verifique sua conexão e tente novamente.', 'error');
            console.error('Falha na requisição AJAX.');
        };
        
        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable) {
                const percentComplete = Math.round((e.loaded / e.total) * 100);
                showLoading(`Enviando arquivos... ${percentComplete}%`);
            }
        };
        
        // Enviar os dados
        xhr.send(formData);
        
        return false;
    }
    
    // Função para validar o formulário
    function validateForm() {
        let formValido = true;
        let mensagensErro = [];
        
        // Validação de CPF
        function validarCPF(cpf) {
            cpf = cpf.replace(/[^\d]/g, '');
            
            if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) {
                return false;
            }
            
            // Validação do primeiro dígito verificador
            let soma = 0;
            for (let i = 0; i < 9; i++) {
                soma += parseInt(cpf.charAt(i)) * (10 - i);
            }
            
            let resto = 11 - (soma % 11);
            let digitoVerificador1 = resto === 10 || resto === 11 ? 0 : resto;
            
            if (digitoVerificador1 !== parseInt(cpf.charAt(9))) {
                return false;
            }
            
            // Validação do segundo dígito verificador
            soma = 0;
            for (let i = 0; i < 10; i++) {
                soma += parseInt(cpf.charAt(i)) * (11 - i);
            }
            
            resto = 11 - (soma % 11);
            let digitoVerificador2 = resto === 10 || resto === 11 ? 0 : resto;
            
            return digitoVerificador2 === parseInt(cpf.charAt(10));
        }
           
        // Validar CPF principal
        const cpfInput = document.getElementById('cpf');
        if (cpfInput && cpfInput.value.trim() !== '') {
            if (!validarCPF(cpfInput.value)) {
                formValido = false;
                mensagensErro.push('CPF do responsável inválido.');
            }
        }
        
        // Validar CPF do cônjuge, se aplicável
        const estadoCivil = document.getElementById('estado_civil');
        if (estadoCivil && ['CASADO(A)', 'UNIÃO ESTÁVEL/AMASIADO(A)'].includes(estadoCivil.value)) {
            const cpfConjuge = document.getElementById('conjuge_cpf');
            if (cpfConjuge && cpfConjuge.value.trim() !== '') {
                if (!validarCPF(cpfConjuge.value)) {
                    formValido = false;
                    mensagensErro.push('CPF do cônjuge inválido.');
                }
            }
        }
        
        // Validar tamanho dos arquivos (limite de 5MB por arquivo)
        const arquivos = document.querySelectorAll('input[type="file"]');
        const limite = 5 * 1024 * 1024; // 5MB em bytes
        
        arquivos.forEach(arquivo => {
            if (arquivo.files.length > 0) {
                for (let i = 0; i < arquivo.files.length; i++) {
                    if (arquivo.files[i].size > limite) {
                        formValido = false;
                        mensagensErro.push(`O arquivo "${arquivo.files[i].name}" excede o limite de 5MB.`);
                    }
                }
            }
        });
        
        // Validar campos obrigatórios
        const camposObrigatorios = document.querySelectorAll('[required]');
        camposObrigatorios.forEach(campo => {
            if (campo.style.display !== 'none' && campo.style.visibility !== 'hidden') {
                if (campo.type === 'checkbox' && !campo.checked) {
                    formValido = false;
                    const label = document.querySelector(`label[for="${campo.id}"]`);
                    const nomeCampo = label ? label.textContent.replace('*', '').trim() : campo.id;
                    mensagensErro.push(`É necessário marcar: "${nomeCampo}".`);
                    campo.classList.add('input-error');
                } else if (campo.type !== 'checkbox' && (campo.value.trim() === '' || (campo.tagName === 'SELECT' && campo.value === ''))) {
                    formValido = false;
                    const label = document.querySelector(`label[for="${campo.id}"]`);
                    const nomeCampo = label ? label.textContent.replace('*', '').trim() : campo.id;
                    mensagensErro.push(`O campo "${nomeCampo}" é obrigatório.`);
                    campo.classList.add('input-error');
                } else {
                    campo.classList.remove('input-error');
                }
            }
        });
        
        // Validação específica: checkbox de autorização de crédito
        const autorizaCredito = document.getElementById('autoriza_credito');
        if (autorizaCredito && !autorizaCredito.checked) {
            formValido = false;
            mensagensErro.push('É obrigatório autorizar a consulta de crédito para prosseguir.');
            autorizaCredito.classList.add('input-error');
        }
        
        // Se o formulário não for válido, mostrar os erros
        if (!formValido) {
            let mensagemErro = 'Por favor, corrija os seguintes erros antes de continuar:\n';
            mensagemErro += mensagensErro.map(msg => `• ${msg}`).join('\n');
            showStatusMessage(mensagemErro, 'error');
        }
        
        return formValido;
    }
    
    // Adicionar validação em tempo real para campos importantes
    const camposImportantes = ['cpf', 'email', 'celular', 'data_nascimento'];
    camposImportantes.forEach(campoId => {
        const campo = document.getElementById(campoId);
        if (campo) {
            campo.addEventListener('blur', function() {
                // Validação específica para cada tipo de campo
                let mensagemErro = '';
                
                if (campoId === 'cpf' && campo.value.trim() !== '') {
                    function validarCPF(cpf) {
                        cpf = cpf.replace(/[^\d]/g, '');
                        
                        if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) {
                            return false;
                        }
                        
                        let soma = 0;
                        for (let i = 0; i < 9; i++) {
                            soma += parseInt(cpf.charAt(i)) * (10 - i);
                        }
                        
                        let resto = 11 - (soma % 11);
                        let digitoVerificador1 = resto === 10 || resto === 11 ? 0 : resto;
                        
                        if (digitoVerificador1 !== parseInt(cpf.charAt(9))) {
                            return false;
                        }
                        
                        soma = 0;
                        for (let i = 0; i < 10; i++) {
                            soma += parseInt(cpf.charAt(i)) * (11 - i);
                        }
                        
                        resto = 11 - (soma % 11);
                        let digitoVerificador2 = resto === 10 || resto === 11 ? 0 : resto;
                        
                        return digitoVerificador2 === parseInt(cpf.charAt(10));
                    }
                    
                    if (!validarCPF(campo.value)) {
                        mensagemErro = 'CPF inválido. Verifique os números informados.';
                    }
                } else if (campoId === 'email' && campo.value.trim() !== '') {
                    const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!regexEmail.test(campo.value)) {
                        mensagemErro = 'E-mail inválido. Utilize um formato válido (exemplo@dominio.com).';
                    }
                } else if (campoId === 'data_nascimento' && campo.value.trim() !== '') {
                    const dataNascimento = new Date(campo.value);
                    const hoje = new Date();
                    let idade = hoje.getFullYear() - dataNascimento.getFullYear();
                    const mesAtual = hoje.getMonth();
                    const mesNascimento = dataNascimento.getMonth();
                    
                    if (mesAtual < mesNascimento || (mesAtual === mesNascimento && hoje.getDate() < dataNascimento.getDate())) {
                        idade--;
                    }
                    
                    if (idade < 18) {
                        mensagemErro = 'O responsável deve ter pelo menos 18 anos de idade.';
                    }
                }
                
                // Mostrar feedback ao usuário
                const feedbackElement = document.getElementById(`${campoId}-feedback`);
                if (feedbackElement) {
                    if (mensagemErro) {
                        feedbackElement.textContent = mensagemErro;
                        feedbackElement.style.display = 'block';
                        campo.classList.add('input-error');
                    } else {
                        feedbackElement.style.display = 'none';
                        campo.classList.remove('input-error');
                    }
                }
            });
        }
    });
    
    // Verificação em tempo real para CPF do cônjuge
    const cpfConjuge = document.getElementById('conjuge_cpf');
    if (cpfConjuge) {
        cpfConjuge.addEventListener('blur', function() {
            if (this.value.trim() !== '') {
                function validarCPF(cpf) {
                    cpf = cpf.replace(/[^\d]/g, '');
                    
                    if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) {
                        return false;
                    }
                    
                    let soma = 0;
                    for (let i = 0; i < 9; i++) {
                        soma += parseInt(cpf.charAt(i)) * (10 - i);
                    }
                    
                    let resto = 11 - (soma % 11);
                    let digitoVerificador1 = resto === 10 || resto === 11 ? 0 : resto;
                    
                    if (digitoVerificador1 !== parseInt(cpf.charAt(9))) {
                        return false;
                    }
                    
                    soma = 0;
                    for (let i = 0; i < 10; i++) {
                        soma += parseInt(cpf.charAt(i)) * (11 - i);
                    }
                    
                    resto = 11 - (soma % 11);
                    let digitoVerificador2 = resto === 10 || resto === 11 ? 0 : resto;
                    
                    return digitoVerificador2 === parseInt(cpf.charAt(10));
                }
                
                const feedbackElement = document.getElementById('conjuge_cpf-feedback');
                if (!validarCPF(this.value)) {
                    if (feedbackElement) {
                        feedbackElement.textContent = 'CPF inválido. Verifique os números informados.';
                        feedbackElement.style.display = 'block';
                    }
                    this.classList.add('input-error');
                } else {
                    if (feedbackElement) {
                        feedbackElement.style.display = 'none';
                    }
                    this.classList.remove('input-error');
                }
            }
        });
    }
    
    // Adicionar handler para submissão do formulário
    if (form) {
        form.addEventListener('submit', handleFormSubmit);
    }
    
    // Verificação de CEP com API ViaCEP
    const cepInput = document.getElementById('cep');
    if (cepInput) {
        cepInput.addEventListener('blur', function() {
            const cep = this.value.replace(/\D/g, '');
            if (cep.length === 8) {
                showLoading('Consultando CEP...');
                
                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                    .then(response => response.json())
                    .then(data => {
                        hideLoading();
                        
                        if (!data.erro) {
                            const ruaInput = document.getElementById('rua');
                            const bairroInput = document.getElementById('bairro');
                            const cidadeInput = document.getElementById('cidade');
                            const numeroInput = document.getElementById('numero');
                            
                            if (ruaInput) ruaInput.value = data.logradouro.toUpperCase();
                            if (bairroInput) bairroInput.value = data.bairro.toUpperCase();
                            if (cidadeInput) cidadeInput.value = data.localidade.toUpperCase();
                            
                            // Foco no campo de número
                            if (numeroInput) numeroInput.focus();
                        } else {
                            showStatusMessage('CEP não encontrado. Verifique o número informado.', 'warning');
                        }
                    })
                    .catch(error => {
                        hideLoading();
                        console.error('Erro ao consultar CEP:', error);
                    });
            }
        });
    }

    // Aplicar máscaras para os campos
    function aplicarMascara(campo, mascara) {
        campo.addEventListener('input', function(e) {
            let valor = e.target.value.replace(/\D/g, '');
            let novoValor = '';
            let indice = 0;
            
            for (let i = 0; i < mascara.length && indice < valor.length; i++) {
                if (mascara[i] === '#') {
                    novoValor += valor[indice++];
                } else {
                    novoValor += mascara[i];
                }
            }
            
            e.target.value = novoValor;
        });
    }
    
    // Aplicar máscara para o CPF
    const cpfInput = document.getElementById('cpf');
    if (cpfInput) {
        aplicarMascara(cpfInput, '###.###.###-##');
    }
    
    // Aplicar máscara para o CEP
    if (cepInput) {
        aplicarMascara(cepInput, '#####-###');
    }
    
    // Aplicar máscara para os telefones
    const telefoneInput = document.getElementById('telefone');
    if (telefoneInput) {
        aplicarMascara(telefoneInput, '(##) #####-####');
    }
    
    const celularInput = document.getElementById('celular');
    if (celularInput) {
        aplicarMascara(celularInput, '(##) #####-####');
    }
    
    // Máscara para valor monetário (aluguel)
    const valorAluguelInput = document.getElementById('valor_aluguel');
    if (valorAluguelInput) {
        valorAluguelInput.addEventListener('input', function(e) {
            let valor = e.target.value.replace(/\D/g, '');
            
            if (valor === '') {
                e.target.value = '';
                return;
            }
            
            valor = parseInt(valor) / 100;
            e.target.value = valor.toLocaleString('pt-BR', {
                style: 'currency',
                currency: 'BRL',
                minimumFractionDigits: 2
            });
        });
    }

    // Inicializar barras de progresso para uploads
    const fileInputs = document.querySelectorAll('.file-input');
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const progressContainer = this.parentElement.querySelector('.upload-progress-container');
            if (progressContainer) {
                if (this.files.length > 0) {
                    progressContainer.style.display = 'block';
                    // Simulação de upload (apenas visual, o upload real acontece no submit)
                    const progressBar = progressContainer.querySelector('.upload-progress-bar');
                    const progressText = progressContainer.querySelector('.upload-progress-text');
                    
                    let progress = 0;
                    const interval = setInterval(() => {
                        progress += 5;
                        if (progressBar) progressBar.style.width = `${progress}%`;
                        if (progressText) progressText.textContent = `${progress}%`;
                        
                        if (progress >= 100) {
                            clearInterval(interval);
                        }
                    }, 100);
                } else {
                    progressContainer.style.display = 'none';
                }
            }
        });
    });
    
    // Adicionar eventos para limpar erros quando o usuário interage com os campos
    document.querySelectorAll('#habitacao-form input, #habitacao-form select, #habitacao-form textarea').forEach(campo => {
        campo.addEventListener('input', function() {
            this.classList.remove('input-error');
        });
        
        campo.addEventListener('change', function() {
            this.classList.remove('input-error');
        });
   });
   
   // Adicionar CSS dinâmico para o efeito de destaque
   const style = document.createElement('style');
   style.textContent = `
       .highlight {
           animation: pulse-green 2s infinite;
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

       .input-error {
           border-color: #f44336 !important;
           box-shadow: 0 0 0 2px rgba(244, 67, 54, 0.2) !important;
       }

       .status-message.success {
           background-color: #e8f5e9;
           color: #2e7d32;
           border-left: 4px solid #4caf50;
       }

       .status-message.error {
           background-color: #ffebee;
           color: #c62828;
           border-left: 4px solid #f44336;
       }

       .status-message.warning {
           background-color: #fff8e1;
           color: #f57c00;
           border-left: 4px solid #ff9800;
       }

       /* PROTEÇÃO MÁXIMA PARA O BOTÃO DE IMPRESSÃO */
       .buttons-container {
           position: relative !important;
           z-index: 999999 !important;
           pointer-events: auto !important;
           display: flex !important;
       }

       #print-button {
           position: relative !important;
           z-index: 1000000 !important;
           pointer-events: auto !important;
           opacity: 1 !important;
           cursor: pointer !important;
           display: inline-block !important;
       }

       #print-button:not(:disabled) {
           opacity: 1 !important;
           cursor: pointer !important;
           pointer-events: auto !important;
           background-color: #28a745 !important;
           border-color: #28a745 !important;
       }

       #print-button:hover {
           background-color: #218838 !important;
           border-color: #1e7e34 !important;
           transform: translateY(-2px) !important;
       }

       /* Evitar que qualquer overlay bloqueie o botão */
       .form-overlay,
       .form-disabled-overlay,
       [id*="overlay"]:not(#loading-overlay) {
           z-index: 50000 !important;
       }

       .cadastro-concluido {
           background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
           border: 2px solid #4caf50;
           border-radius: 8px;
           padding: 20px;
           margin: 20px 0;
           box-shadow: 0 4px 12px rgba(0,0,0,0.1);
       }
   `;
   document.head.appendChild(style);
   
});

// ===== PROTEÇÃO GLOBAL CONTÍNUA (FORA do DOMContentLoaded) =====

// Função para proteger o botão de impressão globalmente
function protegerBotaoGlobal() {
   const printButton = document.getElementById('print-button');
   if (printButton) {
       printButton.style.pointerEvents = 'auto';
       printButton.style.opacity = '1';
       printButton.style.zIndex = '1000000';
       
       if (!printButton.onclick && printButton.getAttribute('data-inscricao-id')) {
           const inscricaoId = printButton.getAttribute('data-inscricao-id');
           printButton.onclick = function(e) {
               e.preventDefault();
               e.stopPropagation();
               console.log('🖨️ IMPRESSÃO GLOBAL - ID:', inscricaoId);
               window.open(`social-relatorio-habitacao.php?id=${inscricaoId}`, '_blank');
               return false;
           };
       }
       
       document.querySelectorAll('[style*="position: fixed"], [style*="position: absolute"]').forEach(element => {
           const zIndex = parseInt(element.style.zIndex || 0);
           if (zIndex > 900000 && zIndex < 1000000) {
               element.style.zIndex = '50000'; // Rebaixar z-index
           }
       });
   }
}

// Monitorar e proteger o botão periodicamente
setInterval(protegerBotaoGlobal, 2000);

// Proteger quando a página carrega completamente
window.addEventListener('load', protegerBotaoGlobal);

// Proteger quando há mudanças no DOM
const observer = new MutationObserver(function(mutations) {
   mutations.forEach(function(mutation) {
       if (mutation.type === 'childList') {
           const newElements = Array.from(mutation.addedNodes).filter(node => node.nodeType === 1);
           newElements.forEach(element => {
               if (element.style && element.style.position === 'fixed' || element.style.position === 'absolute') {
                   const zIndex = parseInt(element.style.zIndex || 0);
                   if (zIndex > 900000) {
                       console.log('🛡️ Elemento com z-index alto detectado, rebaixando...');
                       element.style.zIndex = '50000';
                   }
               }
           });
           
           protegerBotaoGlobal();
       }
   });
});

observer.observe(document.body, {
   childList: true,
   subtree: true
});
