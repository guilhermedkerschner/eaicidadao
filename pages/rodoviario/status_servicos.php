<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Status de Solicitações - Eai Cidadão!</title>
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
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }

        .header {
            width: 100%;
            max-width: 1200px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .header-left {
            display: flex;
            align-items: center;
        }

        .municipality-logo {
            width: 80px;
            height: 80px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 20px;
        }

        .municipality-logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .title-container h1 {
            color: #0d47a1;
            font-weight: 600;
            font-size: 1.8rem;
        }

        .municipality-name {
            color: #000000;
            font-size: 1rem;
            text-transform: uppercase;
            font-weight: 700;
        }

        .header-right a {
            background-color: #0d47a1;
            color: #fff;
            padding: 8px 16px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }

        .header-right a i {
            margin-right: 8px;
        }

        .header-right a:hover {
            background-color: #083378;
            transform: translateY(-2px);
        }

        .container {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            padding: 30px;
            width: 100%;
            max-width: 1000px;
            z-index: 1;
            margin-bottom: 20px;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .breadcrumb a {
            color: #0d47a1;
            text-decoration: none;
            transition: color 0.3s;
        }

        .breadcrumb a:hover {
            color: #ff8f00;
            text-decoration: underline;
        }

        .breadcrumb .separator {
            margin: 0 8px;
            color: #666;
        }

        .breadcrumb .current {
            color: #666;
            font-weight: 500;
        }

        .section-title {
            color: #0d47a1;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e0e0;
            display: flex;
            align-items: center;
            font-size: 1.5rem;
        }

        .section-title i {
            margin-right: 10px;
            color: #ff8f00;
            font-size: 1.5rem;
        }

        .intro-text {
            margin-bottom: 30px;
            line-height: 1.6;
            color: #333;
        }

        .search-container {
            background-color: #f5f5f5;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
        }

        .search-tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }

        .search-tab {
            padding: 10px 20px;
            cursor: pointer;
            background-color: #e0e0e0;
            border-radius: 8px 8px 0 0;
            margin-right: 5px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .search-tab.active {
            background-color: #0d47a1;
            color: white;
        }

        .search-form {
            display: none;
        }

        .search-form.active {
            display: block;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: #ff8f00;
            outline: none;
            box-shadow: 0 0 0 2px rgba(255, 143, 0, 0.1);
        }

        .btn {
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn i {
            margin-right: 8px;
        }

        .btn-primary {
            background-color: #0d47a1;
            color: white;
        }

        .btn-primary:hover {
            background-color: #083378;
            transform: translateY(-2px);
        }

        footer {
            background-color: rgba(255, 255, 255, 0.95);
            width: 100%;
            max-width: 1200px;
            padding: 15px;
            text-align: center;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            font-size: 0.9rem;
            color: #555;
            margin-top: auto;
        }

        @media (max-width: 768px) {
            .search-tabs {
                flex-direction: column;
                border-bottom: none;
            }
            
            .search-tab {
                margin-right: 0;
                margin-bottom: 5px;
                border-radius: 8px;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="header-left">
            <div class="municipality-logo">
                <!-- Substitua pelo caminho da sua logo -->
                <img src="../images/logo_municipio.png" alt="Logo do Município">
            </div>
            <div class="title-container">
                <h1>Eai Cidadão!</h1>
                <h2 class="municipality-name">Município de Santa Izabel do Oeste</h2>
            </div>
        </div>
        <div class="header-right">
            <a href="../index.php"><i class="fas fa-home"></i> Página Inicial</a>
        </div>
    </div>

    <div class="container">
        <div class="breadcrumb">
            <a href="../index.php">Página Inicial</a>
            <span class="separator">›</span>
            <a href="../rodoviario.php">Setor Rodoviário</a>
            <span class="separator">›</span>
            <span class="current">Consulta de Status</span>
        </div>

        <h2 class="section-title"><i class="fas fa-search"></i> Consulta de Status de Solicitações</h2>

        <p class="intro-text">
            Utilize o formulário abaixo para verificar o status atual da sua solicitação de serviço junto ao Setor Rodoviário. 
            Você pode realizar a busca utilizando o número de protocolo fornecido no momento do cadastro ou através do CPF do solicitante.
        </p>

        <div class="search-container">
            <div class="search-tabs">
                <div id="tab-protocolo" class="search-tab active" onclick="changeTab('protocolo')">
                    <i class="fas fa-file-alt"></i> Buscar por Protocolo
                </div>
                <div id="tab-cpf" class="search-tab" onclick="changeTab('cpf')">
                    <i class="fas fa-id-card"></i> Buscar por CPF
                </div>
            </div>

            <div id="form-protocolo" class="search-form active">
                <form id="protocolo-form" action="rel_protocolo.php" method="get">
                    <div class="form-group">
                        <label for="protocolo">Número de Protocolo</label>
                        <input type="text" id="protocolo" name="protocolo" class="form-control" placeholder="Ex: PNT-1234 ou BOE-5678" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Consultar
                    </button>
                </form>
            </div>

            <div id="form-cpf" class="search-form">
                <form id="cpf-form" action="rel_cpf.php" method="get">
                    <div class="form-group">
                        <label for="cpf">CPF do Solicitante</label>
                        <input type="text" id="cpf" name="cpf" class="form-control" placeholder="000.000.000-00" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Consultar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <footer>
        &copy; 2025 Prefeitura Municipal de Santa Izabel do Oeste. Todos os direitos reservados.
    </footer>

    <script>
        // Alternar entre as abas de busca
        function changeTab(tab) {
            // Desativa todas as abas e formulários
            document.querySelectorAll('.search-tab').forEach(item => {
                item.classList.remove('active');
            });
            
            document.querySelectorAll('.search-form').forEach(item => {
                item.classList.remove('active');
            });
            
            // Ativa a aba e formulário selecionados
            document.getElementById('tab-' + tab).classList.add('active');
            document.getElementById('form-' + tab).classList.add('active');
        }
        
        // Formatação de CPF ao digitar
        document.addEventListener('DOMContentLoaded', function() {
            const cpfInput = document.getElementById('cpf');
            if (cpfInput) {
                cpfInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length > 11) {
                        value = value.substring(0, 11);
                    }
                    
                    // Formatar CPF (000.000.000-00)
                    if (value.length > 9) {
                        value = value.replace(/^(\d{3})(\d{3})(\d{3})(\d{2}).*/, '$1.$2.$3-$4');
                    } else if (value.length > 6) {
                        value = value.replace(/^(\d{3})(\d{3})(\d{3}).*/, '$1.$2.$3');
                    } else if (value.length > 3) {
                        value = value.replace(/^(\d{3})(\d{3}).*/, '$1.$2');
                    }
                    
                    e.target.value = value;
                });
            }
            
            // Formatação de protocolo ao digitar
            const protocoloInput = document.getElementById('protocolo');
            if (protocoloInput) {
                protocoloInput.addEventListener('input', function(e) {
                    let value = e.target.value.toUpperCase();
                    
                    // Limitar tamanho
                    if (value.length > 8) {
                        value = value.substring(0, 8);
                    }
                    
                    // Verificar se começa com prefixo válido
                    if (!value.startsWith('PNT-') && !value.startsWith('BOE-') && value.length >= 3) {
                        // Se tem 3 letras e um hífen, adicionar o hífen
                        if (/^[A-Z]{3}$/.test(value)) {
                            value += '-';
                        }
                        // Se não começa com prefixo mas tem 3 letras, converter para formato de protocolo
                        else if (value.length >= 3 && !value.includes('-')) {
                            const prefix = value.substring(0, 3);
                            const number = value.substring(3);
                            value = prefix + '-' + number;
                        }
                    }
                    
                    e.target.value = value;
                });
            }
        });
    </script>
</body>

</html>