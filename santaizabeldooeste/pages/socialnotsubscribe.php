<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerta de Login - Eai Cidadão!</title>
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

        .alert-container {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 90%;
            max-width: 500px;
            text-align: center;
        }

        .alert-icon {
            background-color: #FF9800;
            color: white;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 20px;
            font-size: 40px;
        }

        .alert-title {
            color: #0d47a1;
            font-size: 1.8rem;
            margin-bottom: 15px;
        }

        .alert-message {
            color: #333;
            font-size: 1.1rem;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .login-button {
            background-color: #0d47a1;
            color: white;
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-block;
            text-decoration: none;
            margin-top: 10px;
        }

        .login-button:hover {
            background-color: #0a3880;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .login-button i {
            margin-right: 8px;
        }

        .footer {
            margin-top: 30px;
            color: #666;
            font-size: 0.9rem;
        }

        @media (max-width: 576px) {
            .alert-container {
                padding: 30px 20px;
            }
            
            .alert-title {
                font-size: 1.5rem;
            }
            
            .alert-message {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="alert-container">
        <div class="alert-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h1 class="alert-title">Inscrição não encontrada</h1>
        <p class="alert-message">
            É necessário realizar a inscrição no Programa de Habitação de Santa Izabel do Oeste, 
            para prosseguir. Por favor, Realize sua inscrição para continuar.
        </p>
        <a href="socialhabitacao.php" class="login-button">
            <i class="fas fa-sign-in-alt"></i> Ir para Inscrição
        </a>
        <p class="footer">
            &copy; 2025 Prefeitura Municipal de Santa Izabel do Oeste
        </p>
    </div>
</body>
</html>