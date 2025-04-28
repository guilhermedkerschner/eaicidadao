<?php
// Inicia a sessão
session_start();

// Verifica se há mensagem de erro
$mensagem_erro = "";
if (isset($_SESSION['erro_login_usuario'])) {
    $mensagem_erro = $_SESSION['erro_login_usuario'];
    unset($_SESSION['erro_login_usuario']); // Remove a mensagem após exibi-la
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área do Cidadão - Eai Cidadão!</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <link rel="stylesheet" type="text/css" href="../css/login-cidadao.css">
</head>

<body>
    <!-- Botão para voltar -->
    <a href="../index.php" class="back-link">
        <i class="fas fa-arrow-left"></i>
        Voltar
    </a>

    <div class="container">
        <div class="header-container">
            <div class="municipality-logo">
                <!-- Substitua pelo caminho da sua logo -->
                <img src="../img/logo_municipio.png" alt="Logo do Município">
            </div>
            <div class="title-container">
                <h1>Eai Cidadão!</h1>
                <h2 class="municipality-name">Município de Santa Izabel do Oeste</h2>
            </div>
        </div>

        <div class="divider"></div>

        <h3 style="margin-bottom: 20px; color: #2e7d32;">Área do Cidadão</h3>
        
        <?php if (!empty($mensagem_erro)): ?>
        <div class="alert-error">
            <?php echo $mensagem_erro; ?>
        </div>
        <?php endif; ?>

        <form class="login-form" id="login-form" method="post">
            <div class="form-group">
                <label for="email">E-mail</label>
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="login_email" name="login_email" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Senha</label>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="login_password" name="login_password" class="form-control" required>
                    <button type="button" id="togglePassword" class="password-toggle">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="forgot-password">
                <a href="./login/recuperar_senha_usuario.php">Esqueceu sua senha?</a>
            </div>

            <button type="submit" id="btn_login" class="btn-login">Entrar</button>

            <div class="register-link">
                Ainda não tem uma conta? <a href="./login/cadastro_usuario.php">Cadastre-se</a>
            </div>
        </form>
    </div>

    <!-- JavaScript para funcionalidade de mostrar/ocultar senha -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('login_password');

        togglePassword.addEventListener('click', function() {
            // Alterna o tipo do campo entre 'password' e 'text'
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);

            // Alterna o ícone entre olho e olho riscado
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    });

    const loginForm = document.getElementById('login-form');

    loginForm.addEventListener('submit', function(e) {
        e.preventDefault(); // Impede o envio tradicional do formulário
        
        // Desabilita o botão de login para evitar múltiplos envios
        const btnLogin = document.getElementById('btn_login');
        btnLogin.disabled = true;
        btnLogin.textContent = 'Aguarde...';
        
        // Coleta os dados do formulário
        const email = document.getElementById('login_email').value.trim();
        const password = document.getElementById('login_password').value;
        
        // Validação básica no cliente
        if (!email || !password) {
            showError('Por favor, preencha todos os campos.');
            resetButton();
            return;
        }
        
        if (!validateEmail(email)) {
            showError('Por favor, insira um email válido.');
            resetButton();
            return;
        }
        
        // Cria um objeto FormData para enviar os dados
        const formData = new FormData();
        formData.append('login_email', email);
        formData.append('login_password', password);
        
        // Envio via fetch API
        fetch('./processos/processar_login_usuario.php',{
            method: 'POST',
            body: formData,
            credentials: 'same-origin' // Importante para manter a sessão
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na rede ou no servidor');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Login bem-sucedido
                window.location.href = data.redirect || './pages/perfil.php';
            } else {
                // Login falhou
                showError(data.message || 'Falha na autenticação. Verifique seus dados.');
                resetButton();
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            showError('Ocorreu um erro duraaaaante a autenticação. Tente novamente mais tarde.');
            resetButton();
        });
    });

    // Função para exibir mensagens de erro
    function showError(message) {
        // Verifica se já existe um elemento de alerta
        let alertElement = document.querySelector('.alert-error');
        
        if (!alertElement) {
            // Cria um novo elemento se não existir
            alertElement = document.createElement('div');
            alertElement.className = 'alert-error';
            const formElement = document.getElementById('login-form');
            formElement.parentNode.insertBefore(alertElement, formElement);
        }
        
        alertElement.textContent = message;
        alertElement.style.display = 'block';
        
        // Remove a mensagem após 5 segundos
        setTimeout(() => {
            alertElement.style.display = 'none';
        }, 5000);
    }
    
    // Função para resetar o botão
    function resetButton() {
        const btnLogin = document.getElementById('btn_login');
        btnLogin.disabled = false;
        btnLogin.textContent = 'Entrar';
    }
    
    // Função para validar email
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    </script>
</body>

</html>