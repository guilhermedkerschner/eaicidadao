<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="./images/logo_eai.ico" type="imagem/x-icon">
    <title>Eai Cidadão! - Município de Santa Izabel do Oeste</title>
    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="./css/index.css"/>
</head>

<body>
    <header class="header">
        <div class="header-left">
            <div class="municipality-logo">
                <img src="./img/logo_municipio.png" alt="Logo do Município">
            </div>
            <div class="title-container">
                <h1>Eai Cidadão!</h1>
                <h2 class="municipality-name">Município de Santa Izabel do Oeste</h2>
            </div>
        </div>

        <div class="login-area">
            <a href="./pages/login_cidadao.php" class="login-button user-login">
                <i class="fas fa-user"></i>
                Área do Cidadão
            </a>
            <a href="./pages/login.php" class="login-button admin-login">
                <i class="fas fa-lock"></i>
                Área Restrita
            </a>

            <!-- Menu do usuário quando está logado (oculto por padrão) -->
            <div class="user-logged-in">
                <div class="user-button">
                    <i class="fas fa-user-check"></i>
                    Olá, <span id="user-name">Nome do Usuário</span>
                </div>
                <div class="user-dropdown">
                    <a href="./app/usuario/perfil.php" class="dropdown-item">
                        <i class="fas fa-id-card"></i>
                        Meu Perfil
                    </a>
                    <a href="./processos/logout.php" class="dropdown-item">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Container principal com as opções de menu -->
    <div class="main-container">
        <!-- Mensagem de boas-vindas e instruções -->
        <div class="welcome-message">
            <h2>Bem-vindo ao Portal do Cidadão!</h2>
            <p>Aqui você pode acessar todos os serviços disponibilizados pela Prefeitura de Santa Izabel do Oeste.</p>
            <div class="instructions">
                <p><i class="fas fa-info-circle"></i> Para fazer uma solicitação, selecione uma das áreas abaixo e siga as instruções na página correspondente.</p>
                <p><i class="fas fa-exclamation-circle"></i> Caso precise de ajuda, entre em contato com nossa central de atendimento pelos contatos no rodapé desta página.</p>
            </div>
        </div>
        
        <div class="divider"></div>

        <div class="options">
            <!-- Opções de menu com ícones -->
            <a href="./pages/esporte/esporte.php" class="option-box">
                <i class="fas fa-volleyball-ball"></i>
                <h3>Esporte</h3>
            </a>
            <a href="./pages/assistencia-social/assistencia-social.php" class="option-box">
                <i class="fas fa-heart"></i>
                <h3>Assistência Social</h3>
            </a>
            <a href="./pages/agricultura/agricultura.php" class="option-box">
                <i class="fas fa-seedling"></i>
                <h3>Agricultura</h3>
            </a>
            <a href="./pages/rodoviario/rodoviario.php" class="option-box">
                <i class="fas fa-bus-alt"></i>
                <h3>Rodoviário</h3>
            </a>
            <a href="./pages/meio_ambiente/meio_ambiente.php" class="option-box">
                <i class="fas fa-tree"></i>
                <h3>Meio Ambiente</h3>
            </a>
            <a href="./pages/servicos_urbanos/servicos_urbanos.php" class="option-box">
                <i class="fas fa-building"></i>
                <h3>Serviços Urbanos</h3>
            </a>
            <a href="./pages/educacao/educacao.php" class="option-box">
                <i class="fas fa-book-open"></i>
                <h3>Educação</h3>
            </a>
            <a href="./pages/fiscalizacao/fiscalizacao.php" class="option-box">
                <i class="fas fa-search-dollar"></i>
                <h3>Fiscalização</h3>
            </a>
            <a href="./pages/obras/obras.php" class="option-box">
                <i class="fas fa-tools"></i>
                <h3>Obras</h3>
            </a>
            <a href="./pages/cultura_turismo/cultura_turismo.php" class="option-box">
                <i class="fas fa-monument"></i>
                <h3>Cultura e Turismo</h3>
            </a>
            <a href="./pages/fazenda/fazenda.php" class="option-box">
                <i class="fas fa-landmark"></i>
                <h3>Fazenda</h3>
            </a>
        </div>
    </div>

    <!-- Rodapé com informações de contato -->
    <footer class="footer">
        <div class="footer-content">
            <div class="contact-info">
                <h3>Contatos</h3>
                <div class="contact-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Rua Acácia, 1317 - Centro, Santa Izabel do Oeste - PR</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-phone"></i>
                    <span>(46) 3542-1360</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <span>prefsio@gmail.com</span>
                </div>
            </div>

            <div class="contact-info">
                <h3>Horário de Atendimento</h3>
                <div class="contact-item">
                    <i class="fas fa-clock"></i>
                    <span>Segunda a Sexta: 07:30 às 11:30 / 13:00 às 17:00</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Sábados, Domingos e Feriados: Fechado</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-info-circle"></i>
                    <span>CNPJ: 76.205.715/0001-42</span>
                </div>
            </div>

            <div class="copyright">
                &copy; 2025 Prefeitura Municipal de Santa Izabel do Oeste. Todos os direitos reservados.
            </div>
        </div>
    </footer>

    <!-- Script para exibir nome do usuário quando estiver logado (exemplo) -->
    <script>
        // Exemplo de verificação de login (substitua por sua lógica real)
        // Quando o usuário estiver logado, oculte os botões de login e mostre a mensagem de boas-vindas
        
        // Exemplo: se usuário estiver logado
        const isLoggedIn = false; // Mude para true para testar o estado de logado
        const userName = "João Silva"; // Nome do usuário logado
        
        document.addEventListener("DOMContentLoaded", function() {
            if (isLoggedIn) {
                // Oculta botões de login
                document.querySelectorAll('.login-button').forEach(btn => {
                    btn.style.display = 'none';
                });
                
                // Mostra mensagem de usuário logado
                const userLoggedElement = document.querySelector('.user-logged-in');
                userLoggedElement.style.display = 'flex';
                
                // Define o nome do usuário
                document.getElementById('user-name').textContent = userName;
            }
        });
    </script>
</body>

</html>