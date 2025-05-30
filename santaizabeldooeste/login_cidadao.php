<?php
// Inicia a sessão
session_start();

if (isset($_SESSION['user_logado'])) {
    header("Location: index.php"); 
  }
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área do Cidadão - Eai Cidadão!</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="./css/style.css">
    <link rel="stylesheet" type="text/css" href="./css/login-cidadao.css">
</head>

<body>
    <!-- Botão para voltar -->
    <a href="index.php" class="back-link">
        <i class="fas fa-arrow-left"></i>
        Voltar
    </a>

    <div class="container">
        <div class="header-container">
            <div class="municipality-logo">
                <!-- Substitua pelo caminho da sua logo -->
                <img src="./img/logo_municipio.png" alt="Logo do Município">
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
        <?php if (isset($_SESSION['sucesso_login'])): ?>
            <div style="background-color: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #28a745;">
                <?php 
                echo $_SESSION['sucesso_login']; 
                unset($_SESSION['sucesso_login']);
                ?>
            </div>
        <?php endif; ?>

        <form class="login-form" action="./controller/processar_login_usuario.php" method="post">
            <div class="form-group">
                <label for="login_email">E-mail</label>
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="login_email" name="login_email" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label for="login_password">Senha</label>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="login_password" name="login_password" class="form-control" required>
                    <button type="button" id="togglePassword" class="password-toggle">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="forgot-password">
                <a id="recovery-pass" href="recuperar_senha.php">Esqueceu sua senha?</a>
            </div>

            <button type="submit" id="btn_login" class="btn-login">Entrar</button>

            <div class="register-link">
                Ainda não tem uma conta? <a href="cad_user.php">Cadastre-se</a>
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
    </script>
    <script src="./js/login.js"></script>
</body>

</html>