<?php
session_start();
// Verifica se o usuário está logado
$usuario_logado = isset($_SESSION['user_logado']) && $_SESSION['user_logado'] === true;
$nome_usuario = isset($_SESSION['user_nome']) ? $_SESSION['user_nome'] : '';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setor de Assistência Social - Eai Cidadão!</title>
    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="../css/social.css">
</head>

<body>
    <div class="header">
        <div class="header-left">
            <div class="municipality-logo">
                <!-- Substitua pelo caminho da sua logo -->
                <img src="../img/logo_municipio.png" alt="Logo do Município">
            </div>
            <div class="title-container">
                <h1>Eai Cidadão!</h1>
                <h2 class="municipality-name">Município de Santa Izabel do Oeste</h2>
            </div>
        </div>
        <div class="header-buttons">
            <!-- Área de login -->
            <div class="login-area">
                <!-- Botões de login (visíveis quando usuário não está logado) -->
                <a href="../login_cidadao.php" class="login-button user-login">
                    <i class="fas fa-user"></i>
                    Área do Cidadão
                </a>
                <a href="../login.php" class="login-button admin-login">
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
                        <a href="../app/usuario/perfil.php" class="dropdown-item">
                            <i class="fas fa-id-card"></i>
                            Meu Perfil
                        </a>
                        <a href="../controller/logout.php" class="dropdown-item">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </a>
                    </div>
                </div>
            </div>
            <!-- Botão Voltar para Página Inicial -->
            <a href="../../index.php" class="back-button">
                <i class="fas fa-home"></i> 
                Página Inicial
            </a>
        </div>
    </div>

    <div class="container">
        <h2 class="section-title"><i class="fas fa-hands-helping"></i> Setor de Assistência Social</h2>

        <p class="intro-text">
            O Setor de Assistência Social do Município de Santa Izabel do Oeste é responsável por garantir a proteção social aos cidadãos, ou seja, apoio a indivíduos, famílias e à comunidade no enfrentamento de suas dificuldades, por meio de serviços, benefícios, programas e projetos. Nosso objetivo é promover a inclusão social, a qualidade de vida e a dignidade humana. Selecione abaixo o serviço que você deseja acessar.
        </p>

        <div class="service-buttons">
            <a href="form_cras.php" class="service-button">
                <i class="fas fa-home"></i>
                <h3>CRAS</h3>
                <p>Centro de Referência de Assistência Social</p>
            </a>

            <a href="form_creas.php" class="service-button">
                <i class="fas fa-shield-alt"></i>
                <h3>CREAS</h3>
                <p>Centro de Referência Especializado de Assistência Social</p>
            </a>

            <a href="socialhabitacao.php" class="service-button">
                <i class="fas fa-building"></i>
                <h3>Habitação</h3>
                <p>Programas habitacionais e moradias populares</p>
            </a>

            <a href="form_idosos.php" class="service-button">
                <i class="fas fa-user-friends"></i>
                <h3>Serviços para Idosos</h3>
                <p>Programas e serviços de atenção à pessoa idosa</p>
            </a>

            <a href="form_criancas.php" class="service-button">
                <i class="fas fa-child"></i>
                <h3>Crianças e Adolescentes</h3>
                <p>Proteção e atendimento a crianças e adolescentes</p>
            </a>

        </div>

        <a href="status_servicos.php" class="service-button" style="width: 100%; max-width: 600px; margin: 0 auto 30px auto; background-color: #0d47a1; color: white; display: flex; flex-direction: row; align-items: center; justify-content: flex-start; padding: 20px 25px; text-align: left;">
            <i class="fas fa-tasks" style="color: white; font-size: 2rem; margin-right: 20px; margin-bottom: 0;"></i>
            <div style="width: 1px; height: 40px; background-color: rgba(255, 255, 255, 0.5); margin-right: 20px;"></div>
            <div>
                <h3 style="color: white; margin-bottom: 5px; font-size: 1.3rem;">Consultar Status de Solicitações</h3>
                <p style="color: #e0e0e0; font-size: 1rem;">Acompanhe o andamento das suas solicitações</p>
            </div>
        </a>

        <div class="contact-section">
            <h3 class="contact-title"><i class="fas fa-address-card"></i> Contato do Setor de Assistência Social</h3>
            
            <div class="contact-grid">
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div class="contact-text">
                            <h4>Endereço</h4>
                            <p>Rua Jacarandá, 620 - Centro<br>Santa Izabel do Oeste - PR<br>CEP: 85650-000</p>
                        </div>
                    </div>
                </div>
                
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <div class="contact-text">
                            <h4>Telefones</h4>
                            <p>(46) 3552-1512 (Secretaria)<br>(46) 3552-1513 (CRAS)</p>
                        </div>
                    </div>
                </div>
                
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <div class="contact-text">
                            <h4>E-mail</h4>
                            <p>social@santaizabel.pr.gov.br<br>cras@santaizabel.pr.gov.br</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="opening-hours">
                <h4><i class="fas fa-clock"></i> Horário de Atendimento</h4>
                <table class="hours-table">
                    <tr>
                        <th>Dia</th>
                        <th>Horário</th>
                    </tr>
                    <tr>
                        <td>Segunda-feira</td>
                        <td>08:00 às 11:30 | 13:00 às 17:00</td>
                    </tr>
                    <tr>
                        <td>Terça-feira</td>
                        <td>08:00 às 11:30 | 13:00 às 17:00</td>
                    </tr>
                    <tr>
                        <td>Quarta-feira</td>
                        <td>08:00 às 11:30 | 13:00 às 17:00</td>
                    </tr>
                    <tr>
                        <td>Quinta-feira</td>
                        <td>08:00 às 11:30 | 13:00 às 17:00</td>
                    </tr>
                    <tr>
                        <td>Sexta-feira</td>
                        <td>08:00 às 11:30 | 13:00 às 17:00</td>
                    </tr>
                    <tr>
                        <td>Sábado, Domingo e Feriados</td>
                        <td>Fechado</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <footer>
        &copy; 2025 Prefeitura Municipal de Santa Izabel do Oeste. Todos os direitos reservados.
    </footer>

    <!-- Script para exibir nome do usuário quando estiver logado (exemplo) -->
    <script>
    // Usando as variáveis PHP diretamente no JavaScript
    const isLoggedIn = <?php echo $usuario_logado ? 'true' : 'false'; ?>;
    const userName = "<?php echo addslashes($nome_usuario); ?>";
    
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
        } else {
            // Garante que os botões de login estejam visíveis
            document.querySelectorAll('.login-button').forEach(btn => {
                btn.style.display = 'flex';
            });
            
            // Garante que a área de usuário logado esteja oculta
            const userLoggedElement = document.querySelector('.user-logged-in');
            userLoggedElement.style.display = 'none';
        }
    });
    
    // Adiciona funcionalidade ao dropdown do usuário
    document.addEventListener("DOMContentLoaded", function() {
        const userButton = document.querySelector('.user-button');
        const userDropdown = document.querySelector('.user-dropdown');
        
        if (userButton) {
            userButton.addEventListener('click', function() {
                userDropdown.classList.toggle('show');
            });
            
            // Fecha o dropdown quando clicar fora dele
            document.addEventListener('click', function(event) {
                if (!userButton.contains(event.target) && !userDropdown.contains(event.target)) {
                    userDropdown.classList.remove('show');
                }
            });
        }
    });
</script>
</body>

</html>