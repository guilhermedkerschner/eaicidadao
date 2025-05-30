/**
 * Script AJAX para o formulário de Cadastro Habitacional
 * Sistema Eai Cidadão! - Prefeitura de Santa Izabel do Oeste
 * Versão Completa Atualizada com Ocultamento de Botão
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
                }, 8000); // Aumentado para 8 segundos para dar tempo de ler
            }
        }
    }
    
    // Função para ocultar permanentemente o botão de cadastrar
    function ocultarBotaoCadastrar() {
        const submitButton = document.getElementById('submit-button');
        const stepActions = submitButton.closest('.step-actions');
        
        if (submitButton) {
            // Ocultar o botão com animação suave
            submitButton.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            submitButton.style.opacity = '0';
            submitButton.style.transform = 'scale(0.8)';
            
            setTimeout(() => {
                submitButton.style.display = 'none';
            }, 500);
        }
        
        // Adicionar mensagem informativa no lugar do botão
        if (stepActions && !document.getElementById('cadastro-concluido-msg')) {
            const mensagemConcluida = document.createElement('div');
            mensagemConcluida.id = 'cadastro-concluido-msg';
            mensagemConcluida.className = 'cadastro-concluido';
            mensagemConcluida.innerHTML = `
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="success-text">
                    <h4>Cadastro Realizado com Sucesso!</h4>
                    <p>Seu cadastro foi enviado e está em análise. Use o botão abaixo para imprimir seu comprovante.</p>
                </div>
            `;
            stepActions.appendChild(mensagemConcluida);
        }
    }
    
    // Função para reabilitar o botão em caso de erro
    function reabilitarBotaoCadastrar(button, originalContent) {
        button.disabled = false;
        button.innerHTML = originalContent;
        button.style.opacity = '1';
        button.style.cursor = 'pointer';
    }
    
    // Função para mostrar o botão de impressão
    function mostrarBotaoImpressao(inscricaoId) {
        const buttonsContainer = document.querySelector('.buttons-container');
        if (buttonsContainer) {
            buttonsContainer.style.display = 'flex';
            
            const printButton = document.getElementById('print-button');
            if (printButton && inscricaoId) {
                // Armazenar ID da inscrição no botão
                printButton.setAttribute('data-inscricao-id', inscricaoId);
                
                // Configurar evento de clique
                printButton.onclick = function() {
                    const id = this.getAttribute('data-inscricao-id');
                    window.open(`social-relatorio-habitacao.php?id=${id}`, '_blank');
                };
                
                // Animar a entrada do botão
                printButton.style.opacity = '0';
                printButton.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    printButton.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    printButton.style.opacity = '1';
                    printButton.style.transform = 'translateY(0)';
                    
                    // Adicionar efeito de destaque
                    printButton.classList.add('highlight');
                    setTimeout(() => {
                        printButton.classList.remove('highlight');
                    }, 6000);
                }, 300);
            }
        }
    }
    
    // Função para desabilitar todo o formulário após sucesso
    function desabilitarFormulario() {
        // Desabilitar todos os inputs, selects e textareas
        const formElements = document.querySelectorAll('#habitacao-form input, #habitacao-form select, #habitacao-form textarea');
        formElements.forEach(element => {
            element.disabled = true;
            element.style.backgroundColor = '#f5f5f5';
            element.style.color = '#888';
        });
        
        // Desabilitar botões de navegação das etapas
        const navButtons = document.querySelectorAll('.btn-step-prev, .btn-step-next');
        navButtons.forEach(button => {
            button.disabled = true;
            button.style.opacity = '0.5';
            button.style.cursor = 'not-allowed';
        });
        
        // Desabilitar navegação por clique nas etapas
        const stepItems = document.querySelectorAll('.step-nav li');
        stepItems.forEach(item => {
            item.style.pointerEvents = 'none';
            item.style.opacity = '0.7';
            item.classList.add('disabled');
        });
        
        // Adicionar overlay visual ao formulário
        const formContainer = document.getElementById('habitacao-form');
        if (formContainer && !document.getElementById('form-overlay')) {
            const overlay = document.createElement('div');
            overlay.id = 'form-overlay';
            overlay.className = 'form-disabled-overlay';
            overlay.innerHTML = `
                <div class="overlay-message">
                    <i class="fas fa-lock"></i>
                    <span>Formulário protegido após cadastro bem-sucedido</span>
                </div>
            `;
            formContainer.style.position = 'relative';
            formContainer.appendChild(overlay);
        }
    }
    
    // Função para lidar com a submissão do formulário via AJAX (ATUALIZADA)
    function handleFormSubmit(e) {
        e.preventDefault();
        
        // Validação do formulário antes de enviar
        if (!validateForm()) {
            return false;
        }
        
        // Desabilitar o botão de submit imediatamente para evitar duplo envio
        const submitButton = document.getElementById('submit-button');
        const originalButtonContent = submitButton.innerHTML;
        
        // Alterar aparência do botão para indicar processamento
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
        submitButton.style.opacity = '0.6';
        submitButton.style.cursor = 'not-allowed';
        
        // Mostrar overlay de carregamento
        showLoading();
        
        // Criar FormData do formulário para enviar dados e arquivos
        const formData = new FormData(form);
        
        // Configurar a requisição AJAX
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '../controller/processar_habitacao.php', true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        // Configurar manipuladores de eventos
        xhr.onload = function() {
            hideLoading();
            
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    
                    if (response.status === 'success') {
                        // Sucesso - ocultar o botão permanentemente
                        ocultarBotaoCadastrar();
                        
                        // Mostrar mensagem de sucesso
                        showStatusMessage(response.message, 'success');
                        
                        // Mostrar botão de impressão do comprovante
                        mostrarBotaoImpressao(response.inscricao_id);
                        
                        // Desabilitar todo o formulário para evitar alterações
                        desabilitarFormulario();
                        
                        // Rolar para a mensagem de sucesso
                        setTimeout(() => {
                            document.getElementById('status-message').scrollIntoView({ 
                                behavior: 'smooth', 
                                block: 'center' 
                            });
                        }, 1000);
                        
                    } else {
                        // Erro - reabilitar o botão
                        reabilitarBotaoCadastrar(submitButton, originalButtonContent);
                        showStatusMessage(response.message, 'error');
                    }
                } catch (e) {
                    // Erro de parsing - reabilitar o botão
                    reabilitarBotaoCadastrar(submitButton, originalButtonContent);
                    showStatusMessage('Erro ao processar a resposta do servidor. Por favor, tente novamente.', 'error');
                    console.error('Erro ao analisar a resposta JSON:', e);
                    console.error('Resposta recebida:', xhr.responseText);
                }
            } else {
                // Erro HTTP - reabilitar o botão
                reabilitarBotaoCadastrar(submitButton, originalButtonContent);
                showStatusMessage('Erro de comunicação com o servidor. Por favor, tente novamente mais tarde.', 'error');
                console.error('Erro na requisição AJAX. Status:', xhr.status);
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
        const estadoCivil = document.getElementById('estado_civil').value;
        if (['CASADO(A)', 'UNIÃO ESTÁVEL/AMASIADO(A)'].includes(estadoCivil)) {
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
                            document.getElementById('rua').value = data.logradouro.toUpperCase();
                            document.getElementById('bairro').value = data.bairro.toUpperCase();
                            document.getElementById('cidade').value = data.localidade.toUpperCase();
                            
                            // Foco no campo de número
                            document.getElementById('numero').focus();
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
                        progressBar.style.width = `${progress}%`;
                        progressText.textContent = `${progress}%`;
                        
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
    
    console.log('Ajax habitação carregado com sucesso - versão atualizada com ocultamento de botão');
});