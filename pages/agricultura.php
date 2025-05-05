
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setor de Agricultura - Eai Cidadão!</title>
    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="../css/agricultura.css">
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
                        <a href="../../app/usuario/perfil.php" class="dropdown-item">
                            <i class="fas fa-id-card"></i>
                            Meu Perfil
                        </a>
                        <a href="./login/logout.php" class="dropdown-item">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </a>
                    </div>
                </div>
            </div>
            <!-- Botão Voltar para Página Inicial -->
            <a href="../index.php" class="back-button">
                <i class="fas fa-home"></i> 
                Página Inicial
            </a>
        </div>
    </div>

    <div class="container">
        <h2 class="section-title"><i class="fas fa-tractor"></i> Secretaria Municipal de Expansão Econômica - Setor de Agricultura</h2>

        <p class="intro-text">
            A Secretaria municipal de Expansão Econômica - Setor de Agricultura do Município de Santa Izabel do Oeste tem como missão apoiar e fortalecer a produção agrícola local, oferecendo assistência técnica, programas de desenvolvimento rural e incentivos aos agricultores. Nosso objetivo é promover práticas sustentáveis, aumentar a produtividade e melhorar a qualidade de vida no campo. Selecione abaixo o serviço que você deseja acessar.
        </p>

        <div class="service-buttons">
            <a href="form_assistencia_tecnica.php" class="service-button">
                <i class="fas fa-tools"></i>
                <h3>Assistência Técnica</h3>
                <p>Orientação técnica para cultivos e criações</p>
            </a>

            <a href="form_promaq.php" class="service-button">
                <i class="fas fa-tractor"></i>
                <h3>PROMAQ</h3>
                <p>Agendamento de serviços de maquinário agrícola</p>
            </a>

            <a href="form_pnae.php" class="service-button">
                <i class="fas fa-apple-alt"></i>
                <h3>PNAE</h3>
                <p>Programa Nacional de Alimentação Escolar</p>
            </a>

            <a href="form_paa.php" class="service-button">
                <i class="fas fa-shopping-basket"></i>
                <h3>PAA</h3>
                <p>Programa de Aquisição de Alimentos</p>
            </a>

            <a href="form_capacitacao.php" class="service-button">
                <i class="fas fa-chalkboard-teacher"></i>
                <h3>Capacitação Rural</h3>
                <p>Cursos e treinamentos para agricultores</p>
            </a>

            <a href="form_feira_produtor.php" class="service-button">
                <i class="fas fa-store"></i>
                <h3>Feira do Produtor</h3>
                <p>Informações sobre a Feira do Produtor Rural</p>
            </a>
            
            <a href="cronograma_embalagens.php" class="service-button">
                <i class="fas fa-prescription-bottle"></i>
                <h3>Embalagens de Agrotóxicos</h3>
                <p>Cronograma de recolhimento de embalagens de agrotóxicos</p>
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
            <h3 class="contact-title"><i class="fas fa-address-card"></i> Contato do Setor de Agricultura</h3>
            
            <div class="contact-grid">
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div class="contact-text">
                            <h4>Endereço</h4>
                            <p>Rua Ipê, 456 - Centro<br>Santa Izabel do Oeste - PR<br>CEP: 85650-000</p>
                        </div>
                    </div>
                </div>
                
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <div class="contact-text">
                            <h4>Telefones</h4>
                            <p>(46) 3552-1345 (Secretaria)<br>(46) 3552-1346 (Assistência Técnica)</p>
                        </div>
                    </div>
                </div>
                
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <div class="contact-text">
                            <h4>E-mail</h4>
                            <p>agricultura@santaizabel.pr.gov.br<br>atecnica@santaizabel.pr.gov.br</p>
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
                        <td>07:30 às 11:30 | 13:00 às 17:00</td>
                    </tr>
                    <tr>
                        <td>Terça-feira</td>
                        <td>07:30 às 11:30 | 13:00 às 17:00</td>
                    </tr>
                    <tr>
                        <td>Quarta-feira</td>
                        <td>07:30 às 11:30 | 13:00 às 17:00</td>
                    </tr>
                    <tr>
                        <td>Quinta-feira</td>
                        <td>07:30 às 11:30 | 13:00 às 17:00</td>
                    </tr>
                    <tr>
                        <td>Sexta-feira</td>
                        <td>07:30 às 11:30 | 13:00 às 17:00</td>
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