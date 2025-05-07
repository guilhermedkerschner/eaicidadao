<?php
// Inicia a sessão
session_start();

// Verifica se existe uma mensagem de erro
$mensagem = isset($_GET['msg']) ? $_GET['msg'] : 'Ocorreu um erro inesperado. Por favor, tente novamente.';
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro - Eai Cidadão!</title>
    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="./css/socialhabitacao.css">
    <style>
        .error-container {
            max-width: 600px;
            margin: 50px auto;
            text-align: center;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        
        .error-icon {
            font-size: 60px;
            color: #f44336;
            margin-bottom: 20px;
        }
        
        .error-title {
            font-size: 24px;
            color: #f44336;
            margin-bottom: 15px;
        }
        
        .error-message {
            font-size: 16px;
            color: #555;
            margin-bottom: 25px;
            line-height: 1.5;
        }
        
        .btn-return {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        
        .btn-return:hover {
            background-color: #45a049;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="header-left">
            <div class="municipality-logo">
                <img src="../img/logo_municipio.png" alt="Logo do Município">
            </div>
            <div class="title-container">
                <h1>Eai Cidadão!</h1>
                <h2 class="municipality-name">Município de Santa Izabel do Oeste</h2>
            </div>
        </div>
    </div>

    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <h2 class="error-title">Oops! Ocorreu um erro</h2>
        <p class="error-message"><?php echo $mensagem; ?></p>
        
        <a href="./" class="btn-return">
            <i class="fas fa-home"></i> Voltar para página inicial
        </a>
    </div>

    <footer>
        &copy; 2025 Prefeitura Municipal de Santa Izabel do Oeste. Todos os direitos reservados.
    </footer>
</body>

</html>