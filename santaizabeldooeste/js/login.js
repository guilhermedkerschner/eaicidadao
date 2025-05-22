/**
 * Script para gerenciar o processo de login
 */
document.addEventListener('DOMContentLoaded', function() {
    // Elementos do formulário
    const loginForm = document.querySelector('.login-form');
    
    // Verificar se o formulário existe na página
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            // Não vamos impedir o envio do formulário, mas faremos algumas validações
            
            // Obter valores dos campos
            const email = document.getElementById('login_email').value.trim();
            const senha = document.getElementById('login_password').value.trim();
            
            // Validar campos antes de enviar
            if (!validarCampos(email, senha, e)) {
                e.preventDefault(); // Impedir envio se validação falhar
            }
            
            // Se passar pela validação, o formulário será enviado normalmente
        });
    }
    
    // Link de recuperação de senha
    const recoveryLink = document.getElementById('recovery-pass');
    if (recoveryLink) {
        recoveryLink.addEventListener('click', function(e) {
            e.preventDefault();
            // Redirecionar para a página de recuperação de senha
            window.location.href = 'recuperar_senha.php';
        });
    }
    
    /**
     * Valida os campos do formulário
     * @param {string} email - Email do usuário
     * @param {string} senha - Senha do usuário
     * @param {Event} event - Evento de submit
     * @returns {boolean} - True se os campos são válidos
     */
    function validarCampos(email, senha, event) {
        // Validar email
        if (!email) {
            mostrarErro('Por favor, informe seu email.');
            focusElement('login_email');
            return false;
        }
        
        // Validar formato de email
        if (!validarEmail(email)) {
            mostrarErro('Por favor, informe um email válido.');
            focusElement('login_email');
            return false;
        }
        
        // Validar senha
        if (!senha) {
            mostrarErro('Por favor, informe sua senha.');
            focusElement('login_password');
            return false;
        }
        
        // Desabilitar botão para evitar múltiplos envios
        const btnLogin = document.getElementById('btn_login');
        if (btnLogin) {
            btnLogin.disabled = true;
            btnLogin.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
        }
        
        return true;
    }
    
    /**
     * Valida o formato do email
     * @param {string} email - Email para validar
     * @returns {boolean} - True se o formato for válido
     */
    function validarEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    /**
     * Mostra uma mensagem de erro
     * @param {string} mensagem - Mensagem de erro
     */
    function mostrarErro(mensagem) {
        // Verificar se já existe um elemento de alerta
        let alertElement = document.querySelector('.alert-error');
        
        // Se não existir, criar um novo
        if (!alertElement) {
            alertElement = document.createElement('div');
            alertElement.className = 'alert-error';
            
            // Inserir antes do formulário
            const form = document.querySelector('.login-form');
            form.parentNode.insertBefore(alertElement, form);
        }
        
        // Definir a mensagem
        alertElement.textContent = mensagem;
    }
    
    /**
     * Coloca o foco em um elemento
     * @param {string} elementId - ID do elemento
     */
    function focusElement(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.focus();
        }
    }
});