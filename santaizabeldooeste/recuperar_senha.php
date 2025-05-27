<?php
session_start();

// Determinar o passo atual
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;

// Verificar se pode acessar os steps avançados
if ($step === 2 && !isset($_SESSION['email_recuperacao'])) {
    $step = 1;
} elseif ($step === 3 && !isset($_SESSION['token_valido'])) {
    $step = 1;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha - Eai Cidadão!</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/login-cidadao.css">
    <style>
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            position: relative;
        }
        
        .step-indicator::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 25%;
            right: 25%;
            height: 2px;
            background-color: #e0e0e0;
            z-index: 1;
        }
        
        .step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
            background: white;
            padding: 0 10px;
        }
        
        .step-circle {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #e0e0e0;
            color: #666;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 8px;
            transition: all 0.3s ease;
        }
        
        .step-item.active .step-circle {
            background-color: #2e7d32;
            color: white;
        }
        
        .step-item.completed .step-circle {
            background-color: #4caf50;
            color: white;
        }
        
        .step-text {
            font-size: 12px;
            color: #666;
            text-align: center;
        }
        
        .step-item.active .step-text {
            color: #2e7d32;
            font-weight: 600;
        }
        
        .token-input-group {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 20px 0;
        }
        
        .token-digit {
            width: 50px;
            height: 50px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            border: 2px solid #ddd;
            border-radius: 8px;
            outline: none;
            transition: border-color 0.3s;
        }
        
        .token-digit:focus {
            border-color: #2e7d32;
        }
        
        .resend-token {
            text-align: center;
            margin-top: 20px;
        }
        
        .resend-link {
            color: #2e7d32;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .resend-link:hover {
            text-decoration: underline;
        }
        
        .password-requirements {
            background-color: #f8f9fa;
            border-left: 4px solid #2e7d32;
            padding: 15px;
            margin: 15px 0;
            border-radius: 0 4px 4px 0;
        }
        
        .password-requirements h5 {
            margin: 0 0 10px 0;
            color: #2e7d32;
        }
        
        .password-requirements ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .password-requirements li {
            margin-bottom: 5px;
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
    <a href="index.php" class="back-link">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>

    <div class="container">
        <div class="header-container">
            <div class="municipality-logo">
                <img src="./img/logo_municipio.png" alt="Logo do Município">
            </div>
            <div class="title-container">
                <h1>Eai Cidadão!</h1>
                <h2 class="municipality-name">Recuperação de Senha</h2>
            </div>
        </div>

        <div class="divider"></div>

        <!-- Indicador de Passos -->
        <div class="step-indicator">
            <div class="step-item <?php echo $step >= 1 ? 'active' : ''; ?> <?php echo $step > 1 ? 'completed' : ''; ?>">
                <div class="step-circle">1</div>
                <div class="step-text">Email</div>
            </div>
            <div class="step-item <?php echo $step >= 2 ? 'active' : ''; ?> <?php echo $step > 2 ? 'completed' : ''; ?>">
                <div class="step-circle">2</div>
                <div class="step-text">Código</div>
            </div>
            <div class="step-item <?php echo $step >= 3 ? 'active' : ''; ?>">
                <div class="step-circle">3</div>
                <div class="step-text">Nova Senha</div>
            </div>
        </div>

        <!-- Mensagens de erro/sucesso -->
        <?php if (isset($_SESSION['erro_recuperacao'])): ?>
            <div class="alert-error">
                <?php 
                echo $_SESSION['erro_recuperacao']; 
                unset($_SESSION['erro_recuperacao']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['sucesso_recuperacao'])): ?>
            <div style="background-color: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #28a745;">
                <?php 
                echo $_SESSION['sucesso_recuperacao']; 
                unset($_SESSION['sucesso_recuperacao']);
                ?>
            </div>
        <?php endif; ?>

        <!-- STEP 1: Informar Email -->
        <?php if ($step === 1): ?>
            <form class="login-form" method="POST" action="./controller/processar_recuperacao_senha.php">
                <h3 style="text-align: center; margin-bottom: 20px; color: #333;">
                    <i class="fas fa-envelope"></i> Informe seu E-mail
                </h3>
                
                <p style="text-align: center; color: #666; margin-bottom: 25px;">
                    Digite o e-mail cadastrado em sua conta para receber o código de recuperação.
                </p>

                <div class="form-group">
                    <label for="email">E-mail</label>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" class="form-control" id="email" name="email" required 
                               placeholder="seu@email.com" autofocus>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-paper-plane"></i> Enviar Código
                </button>

                <div class="register-link">
                    Lembrou da senha? <a href="login_cidadao.php">Fazer Login</a>
                </div>
            </form>
        <?php endif; ?>

        <!-- STEP 2: Inserir Código -->
        <?php if ($step === 2): ?>
            <form class="login-form" method="POST" action="./controller/validar_token_senha.php">
                <input type="hidden" name="step" value="2">
                
                <h3 style="text-align: center; margin-bottom: 20px; color: #333;">
                    <i class="fas fa-key"></i> Digite o Código
                </h3>
                
                <p style="text-align: center; color: #666; margin-bottom: 25px;">
                    Enviamos um código de 6 dígitos para<br>
                    <strong><?php echo isset($_SESSION['email_recuperacao']) ? $_SESSION['email_recuperacao'] : ''; ?></strong>
                </p>

                <div class="form-group">
                    <label for="token">Código de Verificação</label>
                    <div class="token-input-group">
                        <input type="text" class="token-digit" maxlength="1" pattern="[0-9]" required>
                        <input type="text" class="token-digit" maxlength="1" pattern="[0-9]" required>
                        <input type="text" class="token-digit" maxlength="1" pattern="[0-9]" required>
                        <input type="text" class="token-digit" maxlength="1" pattern="[0-9]" required>
                        <input type="text" class="token-digit" maxlength="1" pattern="[0-9]" required>
                        <input type="text" class="token-digit" maxlength="1" pattern="[0-9]" required>
                    </div>
                    <input type="hidden" name="token" id="token-hidden">
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-check"></i> Verificar Código
                </button>

                <div class="resend-token">
                    <p style="color: #666; font-size: 0.9rem;">Não recebeu o código?</p>
                    <a href="./controller/processar_recuperacao_senha.php" class="resend-link" 
                       onclick="return confirm('Deseja solicitar um novo código?')">
                        <i class="fas fa-redo"></i> Reenviar Código
                    </a>
                </div>

                <div class="register-link">
                    <a href="recuperar_senha.php">← Voltar ao início</a>
                </div>
            </form>
        <?php endif; ?>

        <!-- STEP 3: Nova Senha -->
        <?php if ($step === 3): ?>
            <form class="login-form" method="POST" action="./controller/validar_token_senha.php">
                <input type="hidden" name="step" value="3">
                
                <h3 style="text-align: center; margin-bottom: 20px; color: #333;">
                    <i class="fas fa-lock"></i> Nova Senha
                </h3>
                
                <p style="text-align: center; color: #666; margin-bottom: 25px;">
                    Defina uma nova senha segura para sua conta.
                </p>

                <div class="password-requirements">
                    <h5><i class="fas fa-info-circle"></i> Sua senha deve conter:</h5>
                    <ul>
                        <li>Pelo menos 8 caracteres</li>
                        <li>Pelo menos uma letra</li>
                        <li>Pelo menos um número</li>
                    </ul>
                </div>

                <div class="form-group">
                    <label for="nova_senha">Nova Senha</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" class="form-control" id="nova_senha" name="nova_senha" required 
                               minlength="8" placeholder="Digite sua nova senha">
                        <button type="button" class="password-toggle" onclick="togglePassword('nova_senha')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirmar_senha">Confirmar Nova Senha</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" required 
                               minlength="8" placeholder="Digite novamente sua nova senha">
                        <button type="button" class="password-toggle" onclick="togglePassword('confirmar_senha')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-save"></i> Redefinir Senha
                </button>

                <div class="register-link">
                    <a href="login_cidadao.php">← Voltar ao Login</a>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script>
        // Função para alternar visibilidade da senha
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.parentElement.querySelector('.password-toggle i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Script para campos de token (apenas no step 2)
        <?php if ($step === 2): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const tokenInputs = document.querySelectorAll('.token-digit');
            const hiddenInput = document.getElementById('token-hidden');
            
            tokenInputs.forEach((input, index) => {
                input.addEventListener('input', function(e) {
                    // Permitir apenas números
                    this.value = this.value.replace(/[^0-9]/g, '');
                    
                    // Mover para o próximo campo
                    if (this.value && index < tokenInputs.length - 1) {
                        tokenInputs[index + 1].focus();
                    }
                    
                    // Atualizar campo hidden
                    updateHiddenToken();
                });
                
                input.addEventListener('keydown', function(e) {
                    // Backspace - voltar para campo anterior
                    if (e.key === 'Backspace' && !this.value && index > 0) {
                        tokenInputs[index - 1].focus();
                    }
                });
                
                input.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const paste = (e.clipboardData || window.clipboardData).getData('text');
                    const numbers = paste.replace(/[^0-9]/g, '').substring(0, 6);
                    
                    for (let i = 0; i < numbers.length && i < tokenInputs.length; i++) {
                        tokenInputs[i].value = numbers[i];
                    }
                    
                    updateHiddenToken();
                });
            });
            
            function updateHiddenToken() {
                hiddenInput.value = Array.from(tokenInputs).map(input => input.value).join('');
            }
        });
        <?php endif; ?>
        
        // Validação de senhas (apenas no step 3)
        <?php if ($step === 3): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const novaSenha = document.getElementById('nova_senha');
            const confirmarSenha = document.getElementById('confirmar_senha');
            
            function validarSenhas() {
                if (novaSenha.value && confirmarSenha.value) {
                    if (novaSenha.value !== confirmarSenha.value) {
                        confirmarSenha.setCustomValidity('As senhas não coincidem');
                    } else {
                        confirmarSenha.setCustomValidity('');
                    }
                }
            }
            
            novaSenha.addEventListener('input', validarSenhas);
            confirmarSenha.addEventListener('input', validarSenhas);
        });
        <?php endif; ?>
    </script>
</body>
</html>