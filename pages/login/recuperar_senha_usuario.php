<?php
// Inicia a sessão
session_start();

// Verifica se há mensagem de erro ou sucesso
$mensagem_erro = "";
$mensagem_sucesso = "";

if (isset($_SESSION['erro_recuperacao'])) {
    $mensagem_erro = $_SESSION['erro_recuperacao'];
    unset($_SESSION['erro_recuperacao']); // Remove a mensagem após exibi-la
}

if (isset($_SESSION['sucesso_recuperacao'])) {
    $mensagem_sucesso = $_SESSION['sucesso_recuperacao'];
    unset($_SESSION['sucesso_recuperacao']); // Remove a mensagem após exibi-la
}

// Define o passo atual do processo de recuperação
$passo = isset($_SESSION['passo_recuperacao']) ? $_SESSION['passo_recuperacao'] : 1;
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperação de Senha - Eai Cidadão!</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="../../css/style.css">
    <link rel="stylesheet" type="text/css" href="../../css/login-cidadao.css">
    <style>
        .container {
            max-width: 500px;
            padding: 30px;
        }
        
        .steps-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }
        
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 33%;
            position: relative;
            z-index: 2;
        }
        
        .step-number {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: #e0e0e0;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #757575;
            font-weight: bold;
            margin-bottom: 8px;
            transition: all 0.3s ease;
        }
        
        .step-text {
            font-size: 0.9rem;
            color: #757575;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .step.active .step-number {
            background-color: #2e7d32;
            color: white;
        }
        
        .step.active .step-text {
            color: #2e7d32;
            font-weight: 600;
        }
        
        .step.completed .step-number {
            background-color: #2e7d32;
            color: white;
        }
        
        .step.completed .step-number::after {
            content: '\f00c';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
        }
        
        .step-line {
            position: absolute;
            top: 17px;
            left: 16%;
            right: 16%;
            height: 2px;
            background-color: #e0e0e0;
            z-index: 1;
        }
        
        .step-line-progress {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            background-color: #2e7d32;
            transition: width 0.3s ease;
        }
        
        .alert-error {
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            background-color: #ffebee;
            color: #c62828;
            border-left: 4px solid #c62828;
        }
        
        .alert-success {
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            background-color: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #2e7d32;
        }
        
        .form-subtitle {
            color: #555;
            margin-bottom: 20px;
            text-align: center;
            line-height: 1.5;
        }
        
        .btn-submit {
            background-color: #2e7d32;
            color: white;
            border: none;
            padding: 12px 0;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
            font-size: 1rem;
            transition: background-color 0.3s;
            margin-top: 10px;
        }
        
        .btn-submit:hover {
            background-color: #1b5e20;
        }
        
        .divider {
            margin: 25px 0;
        }
        
        .token-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 20px 0;
        }
        
        .token-input {
            width: 45px;
            height: 55px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            border: 2px solid #ccc;
            border-radius: 8px;
        }
        
        .token-input:focus {
            border-color: #2e7d32;
            outline: none;
        }
        
        .resend-link {
            text-align: center;
            margin-top: 15px;
        }
        
        .resend-link a {
            color: #2e7d32;
            text-decoration: none;
            font-weight: 500;
        }
        
        .resend-link a:hover {
            text-decoration: underline;
        }
        
        .password-requirements {
            margin: 15px 0;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        
        .password-requirements p {
            margin-bottom: 5px;
            color: #555;
        }
        
        .requirement {
            display: flex;
            align-items: center;
            margin: 5px 0;
            color: #757575;
        }
        
        .requirement i {
            margin-right: 8px;
            font-size: 14px;
        }
        
        .requirement.valid {
            color: #2e7d32;
        }
        
        .requirement.invalid {
            color: #c62828;
        }
        
        #timer {
            font-weight: bold;
            color: #0d47a1;
        }
    </style>
</head>

<body>
    <!-- Botão para voltar -->
    <a href="../login_cidadao.php" class="back-link">
        <i class="fas fa-arrow-left"></i>
        Voltar para login
    </a>

    <div class="container">
        <div class="header-container">
            <div class="municipality-logo">
                <!-- Substitua pelo caminho da sua logo -->
                <img src="../../img/logo_municipio.png" alt="Logo do Município">
            </div>
            <div class="title-container">
                <h1>Eai Cidadão!</h1>
                <h2 class="municipality-name">Município de Santa Izabel do Oeste</h2>
            </div>
        </div>

        <div class="divider"></div>

        <h3 style="margin-bottom: 20px; color: #0d47a1; text-align: center;">Recuperação de Senha</h3>
        
        <!-- Indicador de passos -->
        <div class="steps-container">
            <div class="step <?php echo ($passo >= 1) ? 'active' : ''; ?> <?php echo ($passo > 1) ? 'completed' : ''; ?>">
                <div class="step-number"><?php echo ($passo > 1) ? '' : '1'; ?></div>
                <div class="step-text">Identificação</div>
            </div>
            <div class="step <?php echo ($passo >= 2) ? 'active' : ''; ?> <?php echo ($passo > 2) ? 'completed' : ''; ?>">
                <div class="step-number"><?php echo ($passo > 2) ? '' : '2'; ?></div>
                <div class="step-text">Verificação</div>
            </div>
            <div class="step <?php echo ($passo >= 3) ? 'active' : ''; ?> <?php echo ($passo > 3) ? 'completed' : ''; ?>">
                <div class="step-number"><?php echo ($passo > 3) ? '' : '3'; ?></div>
                <div class="step-text">Nova Senha</div>
            </div>
            
            <div class="step-line">
                <div class="step-line-progress" style="width: <?php 
                    if ($passo == 1) echo '0%';
                    else if ($passo == 2) echo '50%';
                    else echo '100%';
                ?>"></div>
            </div>
        </div>
        
        <?php if (!empty($mensagem_erro)): ?>
        <div class="alert-error">
            <?php echo $mensagem_erro; ?>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($mensagem_sucesso)): ?>
        <div class="alert-success">
            <?php echo $mensagem_sucesso; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($passo == 1): // Passo 1: Identificação do usuário ?>
            <p class="form-subtitle">Informe seu CPF ou e-mail cadastrado para iniciar o processo de recuperação de senha.</p>
            
            <form action="processar_recuperacao.php" method="post">
                <input type="hidden" name="passo" value="1">
                
                <div class="form-group">
                    <label for="identificacao">CPF ou E-mail</label>
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" id="identificacao" name="identificacao" class="form-control" placeholder="Digite seu CPF ou e-mail" required>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">Continuar</button>
            </form>
            
        <?php elseif ($passo == 2): // Passo 2: Verificação do código ?>
            <p class="form-subtitle">
                Enviamos um código de verificação para o e-mail <strong><?php echo isset($_SESSION['email_recuperacao']) ? $_SESSION['email_recuperacao'] : ''; ?></strong>. 
                Digite o código de 6 dígitos abaixo.
            </p>
            
            <form action="processar_recuperacao.php" method="post">
                <input type="hidden" name="passo" value="2">
                
                <div class="token-container">
                    <input type="text" class="token-input" name="token[]" maxlength="1" pattern="[0-9]" inputmode="numeric" required autofocus>
                    <input type="text" class="token-input" name="token[]" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" class="token-input" name="token[]" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" class="token-input" name="token[]" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" class="token-input" name="token[]" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" class="token-input" name="token[]" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                </div>
                
                <p style="text-align: center; margin: 10px 0;">
                    O código expira em <span id="timer">05:00</span>
                </p>
                
                <button type="submit" class="btn-submit">Verificar Código</button>
                
                <div class="resend-link">
                    Não recebeu o código? <a href="processar_recuperacao.php?resend=1">Reenviar</a>
                </div>
            </form>
            
        <?php elseif ($passo == 3): // Passo 3: Definição da nova senha ?>
            <p class="form-subtitle">
                Crie sua nova senha. Escolha uma senha forte que você não use em outros sites.
            </p>
            
            <form action="processar_recuperacao.php" method="post" id="formNovaSenha">
                <input type="hidden" name="passo" value="3">
                
                <div class="form-group">
                    <label for="nova_senha">Nova senha</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="nova_senha" name="nova_senha" class="form-control" required>
                        <button type="button" id="toggleSenha" class="password-toggle">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="password-requirements">
                    <p>A senha deve atender aos seguintes requisitos:</p>
                    <div class="requirement" id="req-length">
                        <i class="fas fa-times-circle"></i> Pelo menos 8 caracteres
                    </div>
                    <div class="requirement" id="req-letter">
                        <i class="fas fa-times-circle"></i> Pelo menos uma letra
                    </div>
                    <div class="requirement" id="req-number">
                        <i class="fas fa-times-circle"></i> Pelo menos um número
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirma_senha">Confirme a nova senha</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirma_senha" name="confirma_senha" class="form-control" required>
                        <button type="button" id="toggleConfirmaSenha" class="password-toggle">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">Redefinir Senha</button>
            </form>
        <?php endif; ?>
    </div>

    <!-- JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($passo == 2): // Script específico para o passo 2 - Verificação do código ?>
        // Manipulação dos inputs de token
        const tokenInputs = document.querySelectorAll('.token-input');
        
        tokenInputs.forEach((input, index) => {
            // Auto-focus para o próximo input após digitar
            input.addEventListener('input', function() {
                if (this.value.length === 1 && index < tokenInputs.length - 1) {
                    tokenInputs[index + 1].focus();
                }
            });
            
            // Permitir backspace para voltar ao input anterior
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && this.value.length === 0 && index > 0) {
                    tokenInputs[index - 1].focus();
                }
            });
            
            // Garantir que apenas números sejam aceitos
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        });
        
        // Timer de expiração do código
        let timeLeft = 5 * 60; // 5 minutos em segundos
        const timerElement = document.getElementById('timer');
        
        const countdown = setInterval(() => {
            const minutes = Math.floor(timeLeft / 60);
            let seconds = timeLeft % 60;
            seconds = seconds < 10 ? '0' + seconds : seconds;
            
            timerElement.textContent = `${minutes}:${seconds}`;
            
            if (timeLeft <= 0) {
                clearInterval(countdown);
                timerElement.textContent = '00:00';
                alert('O código expirou. Por favor, solicite um novo código.');
            }
            
            timeLeft--;
        }, 1000);
        <?php endif; ?>
        
        <?php if ($passo == 3): // Script específico para o passo 3 - Nova senha ?>
        // Toggle de visibilidade das senhas
        function setupPasswordToggle(toggleId, passwordId) {
            const toggle = document.getElementById(toggleId);
            const password = document.getElementById(passwordId);
            
            toggle.addEventListener('click', function() {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                
                // Alterna o ícone
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        }
        
        setupPasswordToggle('toggleSenha', 'nova_senha');
        setupPasswordToggle('toggleConfirmaSenha', 'confirma_senha');
        
        // Validação em tempo real dos requisitos da senha
        const senhaInput = document.getElementById('nova_senha');
        const confirmaSenhaInput = document.getElementById('confirma_senha');
        const reqLength = document.getElementById('req-length');
        const reqLetter = document.getElementById('req-letter');
        const reqNumber = document.getElementById('req-number');
        
        function updateRequirements() {
            const senha = senhaInput.value;
            
            // Requisito de comprimento
            if (senha.length >= 8) {
                reqLength.classList.add('valid');
                reqLength.classList.remove('invalid');
                reqLength.querySelector('i').className = 'fas fa-check-circle';
            } else {
                reqLength.classList.add('invalid');
                reqLength.classList.remove('valid');
                reqLength.querySelector('i').className = 'fas fa-times-circle';
            }
            
            // Requisito de letra
            if (/[a-zA-Z]/.test(senha)) {
                reqLetter.classList.add('valid');
                reqLetter.classList.remove('invalid');
                reqLetter.querySelector('i').className = 'fas fa-check-circle';
            } else {
                reqLetter.classList.add('invalid');
                reqLetter.classList.remove('valid');
                reqLetter.querySelector('i').className = 'fas fa-times-circle';
            }
            
            // Requisito de número
            if (/[0-9]/.test(senha)) {
                reqNumber.classList.add('valid');
                reqNumber.classList.remove('invalid');
                reqNumber.querySelector('i').className = 'fas fa-check-circle';
            } else {
                reqNumber.classList.add('invalid');
                reqNumber.classList.remove('valid');
                reqNumber.querySelector('i').className = 'fas fa-times-circle';
            }
        }
        
        senhaInput.addEventListener('input', updateRequirements);
        
        // Validação do formulário antes de enviar
        document.getElementById('formNovaSenha').addEventListener('submit', function(e) {
            const senha = senhaInput.value;
            const confirmaSenha = confirmaSenhaInput.value;
            
            // Verifica se todos os requisitos foram atendidos
            if (senha.length < 8 || !/[a-zA-Z]/.test(senha) || !/[0-9]/.test(senha)) {
                alert('Sua senha não atende a todos os requisitos de segurança.');
                e.preventDefault();
                return;
            }
            
            // Verifica se as senhas coincidem
            if (senha !== confirmaSenha) {
                alert('As senhas não coincidem.');
                e.preventDefault();
                return;
            }
        });
        <?php endif; ?>
    });
    </script>
</body>

</html>