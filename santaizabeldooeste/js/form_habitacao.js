/**
 * Script para gerenciar o formulário em etapas de Cadastro Habitacional
 * Sistema Eai Cidadão! - Prefeitura de Santa Izabel do Oeste
 */
document.addEventListener('DOMContentLoaded', function() {
    // Variáveis para controle do formulário
    const stepContents = document.querySelectorAll('.step-content');
    const stepItems = document.querySelectorAll('.step-nav li');
    const prevButtons = document.querySelectorAll('.btn-step-prev');
    const nextButtons = document.querySelectorAll('.btn-step-next');
    
    // Verificar se é uma página de sucesso antes de mostrar botão de impressão
    const urlParams = new URLSearchParams(window.location.search);
    const isSuccess = urlParams.get('success');
    const printButton = document.getElementById('print-button');
    
    if (printButton) {
        if (isSuccess === 'true' || isSuccess === '1') {
            // Mostrar botão apenas em caso de sucesso
            printButton.style.display = 'block';
            printButton.addEventListener('click', function() {
                const inscricaoId = this.getAttribute('data-inscricao-id') || urlParams.get('id');
                if (inscricaoId) {
                    window.open(`social-relatorio-habitacao.php?id=${inscricaoId}`);
                } else {
                    alert('ID da inscrição não encontrado.');
                }
            });
        } else {
            // Ocultar botão durante preenchimento
            printButton.style.display = 'none';
        }
    }
    
    // Função para exibir a etapa
    function showStep(stepNumber) {
        // Ocultar todas as etapas
        stepContents.forEach(step => {
            step.classList.remove('active');
        });
        
        // Mostrar a etapa atual
        document.getElementById('step-' + stepNumber).classList.add('active');
        
        // Atualizar navegação
        stepItems.forEach(item => {
            const itemStep = item.getAttribute('data-step');
            item.classList.remove('active');
            item.classList.remove('completed');
            
            if (itemStep == stepNumber) {
                item.classList.add('active');
            } else if (itemStep < stepNumber) {
                item.classList.add('completed');
            }
        });
        
        // Rolar para o topo
        window.scrollTo(0, 0);
    }
    
    // Função para validar campos da etapa - CORRIGIDA
    function validateStep(stepNumber) {
        const step = document.getElementById('step-' + stepNumber);
        const requiredInputs = step.querySelectorAll('input[required], select[required]');
        let isValid = true; // ← CORREÇÃO: Inicializar como true em vez de recursão
        
        requiredInputs.forEach(input => {
            // Verificar apenas elementos visíveis
            const isVisible = isElementVisible(input);
            
            if (isVisible && !input.value) {
                // Para checkbox, verificar se está marcado
                if (input.type === 'checkbox' && !input.checked) {
                    isValid = false;
                    input.classList.add('invalid');
                    
                    // Remover classe ao marcar
                    input.addEventListener('change', function() {
                        if (this.checked) {
                            this.classList.remove('invalid');
                        }
                    }, { once: true });
                } 
                // Para outros inputs, verificar valor
                else if (input.type !== 'checkbox') {
                    isValid = false;
                    input.classList.add('invalid');
                    
                    // Remover classe ao digitar
                    input.addEventListener('input', function() {
                        this.classList.remove('invalid');
                    }, { once: true });
                }
            } else {
                input.classList.remove('invalid');
            }
        });
        
        // Verificação adicional para o passo 7 - checkbox de autorização de crédito
        if (stepNumber === 7) {
            const autorizaCredito = document.getElementById('autoriza_credito');
            if (autorizaCredito && !autorizaCredito.checked) {
                isValid = false;
                autorizaCredito.classList.add('invalid');
                alert('É necessário autorizar a consulta de crédito para prosseguir.');
                
                // Destacar o checkbox com animação
                autorizaCredito.parentElement.classList.add('highlight-animation');
                setTimeout(() => {
                    autorizaCredito.parentElement.classList.remove('highlight-animation');
                }, 1500);
            }
        }
        
        if (!isValid) {
            alert('Por favor, preencha todos os campos obrigatórios.');
        }
        
        return isValid;
    }
    
    // Função para verificar se um elemento está visível
    function isElementVisible(element) {
        if (!element) return false;
        
        // Verificar se o próprio elemento está oculto
        if (element.style.display === 'none' || element.style.visibility === 'hidden') {
            return false;
        }
        
        // Verificar se algum ancestral está oculto
        let parent = element.parentElement;
        while (parent) {
            if (getComputedStyle(parent).display === 'none' || getComputedStyle(parent).visibility === 'hidden') {
                return false;
            }
            parent = parent.parentElement;
        }
        
        return true;
    }
    
    // Eventos para navegação das etapas
    stepItems.forEach(item => {
        item.addEventListener('click', function() {
            const clickedStep = parseInt(this.getAttribute('data-step'));
            const currentStep = parseInt(document.querySelector('.step-nav li.active').getAttribute('data-step'));
            
            // Validar etapas anteriores se estiver avançando
            if (clickedStep > currentStep) {
                let allValid = true;
                
                for (let i = 1; i < clickedStep; i++) {
                    if (!validateStep(i)) {
                        allValid = false;
                        showStep(i);
                        break;
                    }
                }
                
                if (allValid) {
                    showStep(clickedStep);
                }
            } else {
                showStep(clickedStep);
            }
        });
    });
    
    // Eventos para botões "Anterior"
    prevButtons.forEach(button => {
        button.addEventListener('click', function() {
            const prevStep = this.getAttribute('data-step');
            showStep(prevStep);
        });
    });
    
    // Eventos para botões "Próximo"
    nextButtons.forEach(button => {
        button.addEventListener('click', function() {
            const currentStep = parseInt(document.querySelector('.step-nav li.active').getAttribute('data-step'));
            const nextStep = this.getAttribute('data-step');
            
            if (validateStep(currentStep)) {
                showStep(nextStep);
            }
        });
    });
    
    // Converter texto para maiúsculas automaticamente
    const uppercaseInputs = document.querySelectorAll('.uppercase-input');
    uppercaseInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    });

    // Mostrar/esconder campo de nome social
    const nomeSocialSelect = document.getElementById('nome_social_opcao');
    if (nomeSocialSelect) {
        nomeSocialSelect.addEventListener('change', function() {
            const nomeSocialCampo = document.getElementById('nome_social_campo');
            if (this.value === 'SIM') {
                nomeSocialCampo.style.display = 'block';
                document.getElementById('nome_social').required = true;
            } else {
                nomeSocialCampo.style.display = 'none';
                document.getElementById('nome_social').required = false;
            }
        });
    }

    // Mostrar/esconder campos de cônjuge conforme o estado civil
    const estadoCivilSelect = document.getElementById('estado_civil');
    if (estadoCivilSelect) {
        estadoCivilSelect.addEventListener('change', function() {
            const conjugeCampos = document.getElementById('conjuge_campos');
            const viuvoDocCampo = document.getElementById('viuvo_doc_campo');
            
            // Verifica se o estado civil requer informações de cônjuge
            if (this.value === 'UNIÃO ESTÁVEL/AMASIADO(A)' || this.value === 'CASADO(A)') {
                conjugeCampos.style.display = 'block';
                document.getElementById('conjuge_nome').required = true;
                document.getElementById('conjuge_cpf').required = true;
                document.getElementById('conjuge_data_nascimento').required = true;
            } else {
                conjugeCampos.style.display = 'none';
                document.getElementById('conjuge_nome').required = false;
                document.getElementById('conjuge_cpf').required = false;
                document.getElementById('conjuge_data_nascimento').required = false;
            }
            
            // Verifica se é viúvo para exigir certidão de óbito
            if (this.value === 'VIÚVO(A)') {
                viuvoDocCampo.style.display = 'block';
                document.getElementById('viuvo_documento').required = true;
            } else {
                viuvoDocCampo.style.display = 'none';
                document.getElementById('viuvo_documento').required = false;
            }
        });
    }

    // Mostrar/esconder campo de comprovante de renda do cônjuge
    const conjugeRendaSelect = document.getElementById('conjuge_renda');
    if (conjugeRendaSelect) {
        conjugeRendaSelect.addEventListener('change', function() {
            const conjugeRendaDoc = document.getElementById('conjuge_renda_doc');
            if (this.value === 'SIM') {
                conjugeRendaDoc.style.display = 'block';
                document.getElementById('conjuge_comprovante_renda').required = true;
            } else {
                conjugeRendaDoc.style.display = 'none';
                document.getElementById('conjuge_comprovante_renda').required = false;
            }
        });
    }

    // Mostrar/esconder campo de detalhamento da deficiência física e laudo médico
    const deficienciaSelect = document.getElementById('deficiencia');
    if (deficienciaSelect) {
        deficienciaSelect.addEventListener('change', function() {
            const deficienciaFisicaCampo = document.getElementById('deficiencia_fisica_campo');
            const laudoDeficienciaCampo = document.getElementById('laudo_deficiencia_campo');
            
            if (this.value === 'FISICA') {
                deficienciaFisicaCampo.style.display = 'block';
                document.getElementById('deficiencia_fisica_detalhe').required = true;
            } else {
                deficienciaFisicaCampo.style.display = 'none';
                document.getElementById('deficiencia_fisica_detalhe').required = false;
            }
            
            // Mostrar campo de laudo para qualquer tipo de deficiência selecionada
            if (this.value !== 'NÃO') {
                laudoDeficienciaCampo.style.display = 'flex';
            } else {
                laudoDeficienciaCampo.style.display = 'none';
            }
        });
    }

    // Mostrar/esconder campo de valor do aluguel
    const situacaoPropriedadeSelect = document.getElementById('situacao_propriedade');
    if (situacaoPropriedadeSelect) {
        situacaoPropriedadeSelect.addEventListener('change', function() {
            const valorAluguelCampo = document.getElementById('valor_aluguel_campo');
            if (this.value === 'ALUGADA') {
                valorAluguelCampo.style.display = 'block';
                document.getElementById('valor_aluguel').required = true;
            } else {
                valorAluguelCampo.style.display = 'none';
                document.getElementById('valor_aluguel').required = false;
            }
        });
    }

    // Mostrar/esconder campos de emprego
    const situacaoTrabalhoSelect = document.getElementById('situacao_trabalho');
    if (situacaoTrabalhoSelect) {
        situacaoTrabalhoSelect.addEventListener('change', function() {
            const empregoCampos = document.getElementById('emprego_campos');
            const valorSelecionado = this.value;
            
            // Reset todos os campos primeiro
            resetarCampos();
            
            switch (valorSelecionado) {
                case 'EMPREGADO COM CARTEIRA ASSINADA':
                    mostrarTodosCampos();
                    break;

                case 'EMPREGADO SEM CARTEIRA ASSINADA':    
                case 'PENSIONISTA':
                case 'APOSENTADO':
                case 'AUTÔNOMO':
                    mostrarApenasCarteira();
                    break;
                    
                default:
                    // Desempregado ou outros - campos já foram resetados
                    empregoCampos.style.display = 'none';
                    break;
            }
            
            function resetarCampos() {
                empregoCampos.style.display = 'none';
                document.getElementById('profissao').required = false;
                document.getElementById('empregador').required = false;
                document.getElementById('cargo').required = false;
                document.getElementById('ramo_atividade').required = false;
                document.getElementById('tempo_servico').required = false;
                document.getElementById('carteira_trabalho').required = false;
            }
            
            function mostrarTodosCampos() {
                empregoCampos.style.display = 'block';
                document.getElementById('profissao').required = true;
                document.getElementById('empregador').required = true;
                document.getElementById('cargo').required = true;
                document.getElementById('ramo_atividade').required = true;
                document.getElementById('tempo_servico').required = true;
                document.getElementById('carteira_trabalho').required = true;
                
                // Mostra todos os campos
                document.getElementById('pro').style.display = 'block';
                document.getElementById('emp').style.display = 'block';
                document.getElementById('car').style.display = 'block';
                document.getElementById('ram').style.display = 'block';
                document.getElementById('tem').style.display = 'block';
            }
            
            function mostrarApenasCarteira() {
                empregoCampos.style.display = 'block';
                
                // Esconde campos desnecessários
                document.getElementById('pro').style.display = 'none';
                document.getElementById('emp').style.display = 'none';
                document.getElementById('car').style.display = 'none';
                document.getElementById('ram').style.display = 'none';
                document.getElementById('tem').style.display = 'none';
                
                // Apenas carteira é obrigatória
                document.getElementById('carteira_trabalho').required = true;
            }
        });
    }

    // Gerenciamento de dependentes
    const numDependentesSelect = document.getElementById('num_dependentes');
    const dependentesContainer = document.getElementById('dependentes_container');
    
    if (numDependentesSelect && dependentesContainer) {
        numDependentesSelect.addEventListener('change', function() {
            const numDependentes = parseInt(this.value);
            dependentesContainer.innerHTML = '';
            
            for (let i = 1; i <= numDependentes; i++) {
                const dependenteDiv = document.createElement('div');
                dependenteDiv.className = 'dependent-container';
                dependenteDiv.innerHTML = `
                    <h4 class="dependent-title"><i class="fas fa-user"></i> Dependente ${i}</h4>
                    
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="dependente_nome_${i}" class="required">Nome do Dependente</label>
                            <input type="text" class="form-control uppercase-input" id="dependente_nome_${i}" name="dependente_nome_${i}" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group third-width">
                            <label for="dependente_data_nascimento_${i}" class="required">Data de Nascimento</label>
                            <input type="date" class="form-control" id="dependente_data_nascimento_${i}" name="dependente_data_nascimento_${i}" required>
                        </div>
                        <div class="form-group third-width">
                            <label for="dependente_cpf_${i}">CPF</label>
                            <input type="text" class="form-control" id="dependente_cpf_${i}" name="dependente_cpf_${i}" maxlength="14" placeholder="000.000.000-00">
                        </div>
                        <div class="form-group third-width">
                            <label for="dependente_rg_${i}">RG</label>
                            <input type="text" class="form-control uppercase-input" id="dependente_rg_${i}" name="dependente_rg_${i}">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group third-width">
                            <label for="dependente_documentos_${i}">Anexar documento(s)</label>
                            <div class="file-input-container">
                                <input type="file" class="file-input" id="dependente_documentos_${i}" name="dependente_documentos_${i}" accept=".pdf,.jpg,.jpeg,.png" multiple>
                                <div class="upload-progress-container">
                                    <div class="upload-progress-bar"></div>
                                    <div class="upload-progress-text">0%</div>
                                </div>
                            </div>
                            <small class="form-text">Formatos aceitos: PDF, JPG, JPEG, PNG (Max: 5MB)</small>
                        </div>
                        <div class="form-group third-width">
                            <label for="dependente_deficiencia_${i}" class="required">Possui deficiência?</label>
                            <select class="form-control" id="dependente_deficiencia_${i}" name="dependente_deficiencia_${i}" required>
                                <option value="NÃO">NÃO</option>
                                <option value="SIM">SIM</option>
                            </select>
                        </div>
                        <div class="form-group third-width">
                            <label for="dependente_renda_${i}" class="required">Possui renda?</label>
                            <select class="form-control dependente-renda-select" id="dependente_renda_${i}" name="dependente_renda_${i}" data-dependente="${i}" required>
                                <option value="NÃO">NÃO</option>
                                <option value="SIM">SIM</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row dependente-renda-doc" id="dependente_renda_doc_${i}" style="display: none;">
                        <div class="form-group full-width">
                            <label for="dependente_comprovante_renda_${i}">Anexar comprovante de renda</label>
                            <div class="file-input-container">
                                <input type="file" class="file-input" id="dependente_comprovante_renda_${i}" name="dependente_comprovante_renda_${i}" accept=".pdf,.jpg,.jpeg,.png">
                                <div class="upload-progress-container">
                                    <div class="upload-progress-bar"></div>
                                    <div class="upload-progress-text">0%</div>
                                </div>
                            </div>
                            <small class="form-text">Formatos aceitos: PDF, JPG, JPEG, PNG (Max: 5MB)</small>
                        </div>
                    </div>
                `;
                
                dependentesContainer.appendChild(dependenteDiv);
            }
            
            // Após adicionar os campos dos dependentes, configurar os eventos
            
            // 1. Configurar eventos para mostrar/esconder comprovante de renda
            const rendaSelects = document.querySelectorAll('.dependente-renda-select');
            rendaSelects.forEach(select => {
                select.addEventListener('change', function() {
                    const dependenteNum = this.getAttribute('data-dependente');
                    const rendaDocDiv = document.getElementById(`dependente_renda_doc_${dependenteNum}`);
                    
                    if (this.value === 'SIM') {
                        rendaDocDiv.style.display = 'flex';
                        document.getElementById(`dependente_comprovante_renda_${dependenteNum}`).required = true;
                    } else {
                        rendaDocDiv.style.display = 'none';
                        document.getElementById(`dependente_comprovante_renda_${dependenteNum}`).required = false;
                    }
                });
            });
            
            // 2. Garantir que os eventos para uppercase também sejam adicionados aos novos campos
            const newUppercaseInputs = dependentesContainer.querySelectorAll('.uppercase-input');
            newUppercaseInputs.forEach(input => {
                input.addEventListener('input', function() {
                    this.value = this.value.toUpperCase();
                });
            });
            
            // 3. Aplicar máscaras para CPFs de dependentes
            const dependenteCPFs = document.querySelectorAll('[id^=dependente_cpf_]');
            dependenteCPFs.forEach(cpf => {
                aplicarMascara(cpf, '###.###.###-##');
            });
            
            // 4. Configurar barras de progresso para uploads
            const fileInputs = dependentesContainer.querySelectorAll('.file-input');
            fileInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const progressContainer = this.parentElement.querySelector('.upload-progress-container');
                    if (progressContainer) {
                        if (this.files.length > 0) {
                            progressContainer.style.display = 'block';
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
        });
    }
    
    // Máscaras para os campos
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
    
    // Aplicar máscaras nos campos principais
    const cpfInput = document.getElementById('cpf');
    if (cpfInput) {
        aplicarMascara(cpfInput, '###.###.###-##');
    }
    
    const cepInput = document.getElementById('cep');
    if (cepInput) {
        aplicarMascara(cepInput, '#####-###');
    }
    
    const telefoneInput = document.getElementById('telefone');
    if (telefoneInput) {
        aplicarMascara(telefoneInput, '(##) #####-####');
    }
    
    const celularInput = document.getElementById('celular');
    if (celularInput) {
        aplicarMascara(celularInput, '(##) #####-####');
    }
    
    const conjuge_cpfInput = document.getElementById('conjuge_cpf');
    if (conjuge_cpfInput) {
        aplicarMascara(conjuge_cpfInput, '###.###.###-##');
    }
    
    // Verificar se há mensagem de erro ou sucesso na URL e exibe
    const statusMsg = urlParams.get('msg');
    const statusType = urlParams.get('type') || 'info';
    
    if (statusMsg) {
        const statusMessage = document.getElementById('status-message');
        if (statusMessage) {
            statusMessage.textContent = decodeURIComponent(statusMsg);
            statusMessage.className = `status-message ${statusType}`;
            statusMessage.style.display = 'block';
            
            // Auto-fechar mensagens de sucesso após alguns segundos
            if (statusType === 'success') {
                setTimeout(() => {
                    statusMessage.style.display = 'none';
                }, 5000);
            }
        }
    }
    
    // Configuração especial para o checkbox de autorização de crédito
    const autorizaCreditoCheckbox = document.getElementById('autoriza_credito');
    if (autorizaCreditoCheckbox) {
        autorizaCreditoCheckbox.addEventListener('change', function() {
            if (this.checked) {
                this.classList.remove('invalid');
            }
        });
    }
    
    // ===== REDIRECIONAMENTO AUTOMÁTICO APÓS SUCESSO =====
    
    /**
     * Função para redirecionar automaticamente para o relatório após cadastro bem-sucedido
     */
    function handleSuccessfulSubmission() {
        const urlParams = new URLSearchParams(window.location.search);
        const isSuccess = urlParams.get('success');
        const inscricaoId = urlParams.get('id') || urlParams.get('inscricao_id');
        
        if (isSuccess === 'true' || isSuccess === '1') {
            // Ocultar o formulário e mostrar mensagem de sucesso
            hideFormShowSuccess();
            
            // Mostrar mensagem de sucesso com countdown
            showSuccessMessage(inscricaoId);
            
            // Redirecionar após 5 segundos
            setTimeout(() => {
                if (inscricaoId) {
                    window.location.href = `social-relatorio-habitacao.php?id=${inscricaoId}`;
                } else {
                    alert('Cadastro realizado com sucesso! ID da inscrição não encontrado.');
                    window.location.href = 'socialhabitacao.php';
                }
            }, 5000);
        }
    }
    
    /**
     * Ocultar formulário e mostrar container de sucesso
     */
    function hideFormShowSuccess() {
        // Ocultar o formulário principal
        const formContainer = document.querySelector('.form-container, .container > form, form');
        if (formContainer) {
            formContainer.style.display = 'none';
        }
        
        // Ocultar navegação de steps se existir
        const stepNav = document.querySelector('.step-nav');
        if (stepNav) {
            stepNav.style.display = 'none';
        }
    }
    
    /**
     * Mostrar mensagem de sucesso com countdown
     */
    function showSuccessMessage(inscricaoId) {
        // Mostrar e configurar o botão de impressão original da página (se existir)
        const originalPrintButton = document.getElementById('print-button');
        if (originalPrintButton && inscricaoId) {
            originalPrintButton.style.display = 'block';
            originalPrintButton.setAttribute('data-inscricao-id', inscricaoId);
        }
        
        // Criar container de sucesso
        const successContainer = document.createElement('div');
        successContainer.id = 'success-container';
        successContainer.innerHTML = `
            <div style="
                max-width: 600px;
                margin: 50px auto;
                padding: 40px;
                text-align: center;
                background: #fff;
                border-radius: 12px;
                box-shadow: 0 8px 25px rgba(0,0,0,0.15);
                border-top: 5px solid #4caf50;
            ">
                <div style="font-size: 4rem; color: #4caf50; margin-bottom: 20px;">
                    <i class="fas fa-check-circle"></i>
                </div>
                
                <h2 style="color: #2e7d32; margin-bottom: 15px; font-size: 2rem;">
                    Cadastro Realizado com Sucesso!
                </h2>
                
                <div style="
                    background: #e8f5e9;
                    padding: 20px;
                    border-radius: 8px;
                    margin: 25px 0;
                    border-left: 4px solid #4caf50;
                ">
                    <h3 style="color: #2e7d32; margin-bottom: 10px;">
                        <i class="fas fa-info-circle"></i> Informações do seu Cadastro
                    </h3>
                    <p style="margin: 5px 0;"><strong>ID da Inscrição:</strong> ${inscricaoId || 'Gerando...'}</p>
                    <p style="margin: 5px 0;"><strong>Data:</strong> ${new Date().toLocaleString('pt-BR')}</p>
                    <p style="font-size: 0.9rem; color: #666; margin-top: 10px;">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Guarde estas informações para consultas futuras
                    </p>
                </div>
                
                <div id="countdown-section" style="
                    font-size: 1.2rem;
                    color: #666;
                    margin: 25px 0;
                    padding: 15px;
                    background: #f8f9fa;
                    border-radius: 8px;
                ">
                    <i class="fas fa-clock"></i> 
                    Redirecionando automaticamente para o relatório em 
                    <strong><span id="countdown-number">5</span></strong> segundos...
                </div>
                
                <div style="margin-top: 30px;">
                    <button onclick="goToReportNow()" style="
                        background: #2e7d32;
                        color: white;
                        padding: 15px 30px;
                        border: none;
                        border-radius: 6px;
                        font-size: 1.1rem;
                        cursor: pointer;
                       margin: 10px;
                       transition: all 0.3s;
                       box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                   " onmouseover="this.style.background='#1b5e20'; this.style.transform='translateY(-2px)'" 
                      onmouseout="this.style.background='#2e7d32'; this.style.transform='translateY(0)'">
                       <i class="fas fa-file-pdf"></i> Ver Relatório Agora
                   </button>
                   
                   <button onclick="printReport('${inscricaoId}')" style="
                       background: #1976d2;
                       color: white;
                       padding: 15px 30px;
                       border: none;
                       border-radius: 6px;
                       font-size: 1.1rem;
                       cursor: pointer;
                       margin: 10px;
                       transition: all 0.3s;
                       box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                   " onmouseover="this.style.background='#1565c0'; this.style.transform='translateY(-2px)'" 
                      onmouseout="this.style.background='#1976d2'; this.style.transform='translateY(0)'">
                       <i class="fas fa-print"></i> Imprimir Relatório
                   </button>
                   
                   <button onclick="cancelRedirect()" style="
                       background: #666;
                       color: white;
                       padding: 15px 30px;
                       border: none;
                       border-radius: 6px;
                       font-size: 1rem;
                       cursor: pointer;
                       margin: 10px;
                       transition: all 0.3s;
                   " onmouseover="this.style.background='#555'" 
                      onmouseout="this.style.background='#666'">
                       <i class="fas fa-times"></i> Cancelar Redirecionamento
                   </button>
               </div>
               
               <div style="margin-top: 20px; font-size: 0.9rem; color: #777;">
                   <a href="socialhabitacao.php" style="color: #2e7d32; text-decoration: none;">
                       <i class="fas fa-plus"></i> Fazer Novo Cadastro
                   </a>
               </div>
           </div>
       `;
       
       // Adicionar ao body
       document.body.appendChild(successContainer);
       
       // Iniciar countdown
       startCountdown(inscricaoId);
       
       // Definir funções globais para os botões
       window.goToReportNow = function() {
           if (inscricaoId) {
               window.location.href = `social-relatorio-habitacao.php?id=${inscricaoId}`;
           } else {
               alert('ID da inscrição não encontrado.');
           }
       };
       
       window.cancelRedirect = function() {
           clearInterval(window.countdownInterval);
           document.getElementById('countdown-section').innerHTML = `
               <i class="fas fa-info-circle"></i> 
               Redirecionamento cancelado. Use os botões acima para navegar.
           `;
       };
       
       // Função para imprimir relatório
       window.printReport = function(inscricaoId) {
           if (inscricaoId) {
               window.open(`social-relatorio-habitacao.php?id=${inscricaoId}`);
           } else {
               alert('ID da inscrição não encontrado para impressão.');
           }
       };
   }
   
   /**
    * Iniciar countdown
    */
   function startCountdown(inscricaoId) {
       let timeLeft = 5;
       const countdownElement = document.getElementById('countdown-number');
       
       window.countdownInterval = setInterval(() => {
           timeLeft--;
           if (countdownElement) {
               countdownElement.textContent = timeLeft;
           }
           
           if (timeLeft <= 0) {
               clearInterval(window.countdownInterval);
               if (inscricaoId) {
                   window.location.href = `social-relatorio-habitacao.php?id=${inscricaoId}`;
               } else {
                   window.location.href = 'socialhabitacao.php';
               }
           }
       }, 1000);
   }
   
   // Executar verificação de sucesso
   handleSuccessfulSubmission();
});