<?php
// Inicia a sessão
session_start();

// Verifica se há mensagem de erro
$mensagem_erro = "";
if (isset($_SESSION['erro_login'])) {
    $mensagem_erro = $_SESSION['erro_login'];
    unset($_SESSION['erro_login']); // Remove a mensagem após exibi-la
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Eai Cidadão!</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/login-restrict.css">
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

        <h3 style="margin-bottom: 20px; color: #0d47a1;">Área Restrita</h3>
        <!-- Adicione este código HTML logo após o título "Área Restrita" -->
        <?php if (!empty($mensagem_erro)): ?>
        <div class="alert-error">
            <?php echo $mensagem_erro; ?>
        </div>
        <?php endif; ?>

        <form class="login-form2" action="processar_login.php" method="post">
            <div class="form-group">
                <label for="username">Nome de usuário ou E-mail</label>
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" id="username" name="usuario" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Senha</label>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" class="form-control" required>
                    <button type="button" id="togglePassword" class="password-toggle">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="forgot-password">
                <a href="./login/recuperar_senha_usuario.php">Esqueceu sua senha?</a>
            </div>

            <button type="submit" class="btn-login">Entrar</button>
        </form>
    </div>

    <!-- JavaScript para funcionalidade de mostrar/ocultar senha -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');

        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);

            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    });
    </script>



</body>

</html>