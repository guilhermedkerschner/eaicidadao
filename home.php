<?php
// index.php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EAI CIDADÃO! - Portal de Serviços Digitais para Municípios</title>
    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #64b5f6 0%, #6aabec 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 900px;
            overflow: hidden;
        }

        .header {
            background-color: #0d47a1;
            padding: 20px;
            text-align: center;
            color: white;
        }

        .header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .header p {
            color: #b3e5fc;
            font-size: 1rem;
        }

        .content {
            padding: 30px;
        }

        .welcome-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }

        @media (min-width: 768px) {
            .welcome-section {
                flex-direction: row;
                text-align: left;
            }
        }

        .logo-container {
            width: 120px;
            height: 120px;
            background-color: #f5f5f5;
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            flex-shrink: 0;
        }

        .logo-container img {
            max-width: 100%;
            max-height: 100%;
        }

        .welcome-text {
            flex-grow: 1;
        }

        .welcome-text h2 {
            color: #0d47a1;
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .welcome-text p {
            color: #555;
            line-height: 1.5;
            margin-bottom: 20px;
        }

        .select-container {
            position: relative;
            margin-top: 10px;
        }

        .select-label {
            display: block;
            font-weight: 500;
            margin-bottom: 8px;
            color: #333;
        }

        .select-box {
            width: 100%;
            padding: 12px 15px;
            background-color: white;
            border: 1px solid #ccc;
            border-radius: 8px;
            cursor: pointer;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .select-box:hover {
            border-color: #2196f3;
        }

        .select-text {
            display: flex;
            align-items: center;
            color: #666;
        }

        .selected-option {
            display: flex;
            align-items: center;
        }

        .selected-option img {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: contain;
            background-color: #f5f5f5;
        }

        .select-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            background-color: white;
            border: 1px solid #ccc;
            border-top: none;
            border-radius: 0 0 8px 8px;
            z-index: 10;
            max-height: 200px;
            overflow-y: auto;
            display: none;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
        }

        .select-option {
            padding: 12px 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .select-option:hover {
            background-color: #f5f5f5;
        }

        .select-option.active {
            background-color: #e3f2fd;
        }

        .option-info {
            display: flex;
            align-items: center;
        }

        .option-info img {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: contain;
            background-color: #f5f5f5;
        }

        .disabled-option {
            padding: 12px 15px;
            color: #aaa;
            font-style: italic;
            border-top: 1px solid #eee;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            margin-top: 30px;
        }

        .btn {
            width: 100%;
            max-width: 300px;
            padding: 14px 20px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .btn i {
            margin-right: 8px;
        }

        .btn-citizen {
            background-color: #2e7d32;
            color: white;
        }

        .btn-citizen:hover:not(:disabled) {
            background-color: #1b5e20;
        }

        .btn-services {
            background-color: #0d47a1;
            color: white;
        }

        .btn-services:hover:not(:disabled) {
            background-color: #083378;
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            color: #777;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>EAI CIDADÃO!</h1>
            <p>Portal de Serviços Digitais para Municípios</p>
        </div>

        <div class="content">
            <div class="welcome-section">
                <div class="logo-container">
                    <img src="img/logo_eai.ico" alt="Logo do Sistema">
                </div>
                <div class="welcome-text">
                    <h2>Bem-vindo ao Portal de Serviços</h2>
                    <p>Escolha seu município para acessar os serviços disponíveis.</p>

                    <div class="select-container">
                        <label class="select-label">
                            <i class="fas fa-map-marker-alt"></i> 
                            Selecione seu município:
                        </label>
                        <div class="select-box" id="municipioSeletor">
                            <div class="select-text" id="selectText">Selecione o município</div>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="select-dropdown" id="municipioDropdown">
                            <div class="select-option" data-value="Santa Izabel do Oeste - PR" onclick="selecionarMunicipio(this)">
                                <div class="option-info">
                                    <img src="img/logo_sio.png" alt="Logo de Santa Izabel do Oeste">
                                    <span>Santa Izabel do Oeste - PR</span>
                                </div>
                                <i class="fas fa-check" style="color: #4caf50; display: none;"></i>
                            </div>
                            <div class="select-option" data-value="Ampére - PR" onclick="selecionarMunicipio(this)">
                                <div class="option-info">
                                    <img src="img/logo_ampere.png" alt="Logo de Ampére">
                                    <span>Ampére - PR</span>
                                </div>
                                <i class="fas fa-check" style="color: #4caf50; display: none;"></i>
                            </div>
                            <div class="select-option" data-value="Realeza - PR" onclick="selecionarMunicipio(this)">
                                <div class="option-info">
                                    <img src="img/logo_realeza.png" alt="Logo de Realeza">
                                    <span>Realeza - PR</span>
                                </div>
                                <i class="fas fa-check" style="color: #4caf50; display: none;"></i>
                            </div>
                            <div class="disabled-option">
                                Outros municípios em breve...
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="action-buttons">
                <button class="btn btn-services" id="btnServicos" disabled>
                    <i class="fas fa-arrow-right"></i> Acessar Serviços
                </button>
            </div>

            <div class="footer">
                <p>© 2025 Prefeitura Municipal de Santa Izabel do Oeste. Todos os direitos reservados.</p>
                <p>CNPJ: 76.205.715/0001-42</p>
            </div>
        </div>
    </div>

    <script>
        // Variável para controlar o estado do seletor
        let municipioSelecionado = false;
        let municipioAtual = '';
        const seletor = document.getElementById('municipioSeletor');
        const dropdown = document.getElementById('municipioDropdown');
        const selectText = document.getElementById('selectText');
        const btnServicos = document.getElementById('btnServicos');

        // Função para abrir/fechar o dropdown
        seletor.addEventListener('click', function() {
            if (dropdown.style.display === 'block') {
                dropdown.style.display = 'none';
            } else {
                dropdown.style.display = 'block';
            }
        });

        // Fechar o dropdown quando clicar fora dele
        document.addEventListener('click', function(event) {
            if (!seletor.contains(event.target)) {
                dropdown.style.display = 'none';
            }
        });

        // Função para selecionar um município
        function selecionarMunicipio(element) {
            const municipio = element.getAttribute('data-value');
            const checkIcon = element.querySelector('.fa-check');
            const selectOptions = document.querySelectorAll('.select-option');
            
            // Remover a seleção anterior
            selectOptions.forEach(option => {
                option.classList.remove('active');
                option.querySelector('.fa-check').style.display = 'none';
            });
            
            // Marcar o município selecionado
            element.classList.add('active');
            checkIcon.style.display = 'inline-block';
            
            // Obter o caminho da imagem correta com base no município selecionado
            let logoPath = "img/logo_sio.png"; // Logo padrão para Santa Izabel
            
            if (municipio === "Ampére - PR") {
                logoPath = "img/logo_ampere.png";
            } else if (municipio === "Realeza - PR") {
                logoPath = "img/logo_realeza.png";
            }
            
            // Atualizar o texto do seletor
            selectText.innerHTML = `
                <div class="selected-option">
                    <img src="${logoPath}" alt="Logo de ${municipio}">
                    <span>${municipio}</span>
                </div>
            `;
            
            // Habilitar os botões
            btnServicos.disabled = false;
            
            // Atualizar a variável de controle
            municipioSelecionado = true;
            municipioAtual = municipio;
            
            // Fechar o dropdown
            dropdown.style.display = 'none';
        }

        // Configurar redirecionamento do botão
        btnServicos.addEventListener('click', function() {
            if (municipioSelecionado) {
                // Redirecionar para páginas diferentes com base no município selecionado
                if (municipioAtual === "Santa Izabel do Oeste - PR") {
                    window.location.href = 'santaizabeldooeste/index.php';
                } else if (municipioAtual === "Ampére - PR") {
                    window.location.href = 'ampere/index.php';
                } else if (municipioAtual === "Realeza - PR") {
                    window.location.href = 'realeza/index.php';
                }
            }
        });
    </script>
</body>
</html>