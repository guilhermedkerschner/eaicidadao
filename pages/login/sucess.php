<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro Realizado - Eai Cidadão!</title>
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

        .success-container {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 90%;
            max-width: 500px;
            text-align: center;
        }

        .success-icon {
            background-color: #4CAF50;
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

        .success-title {
            color: #0d47a1;
            font-size: 1.8rem;
            margin-bottom: 15px;
        }

        .success-message {
            color: #333;
            font-size: 1.1rem;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .button {
            display: inline-block;
            background-color: #0d47a1;
            color: white;
            padding: 12px 25px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }

        .button:hover {
            background-color: #083378;
            transform: translateY(-2px);
        }

        .button i {
            margin-right: 8px;
        }

        .footer {
            margin-top: 30px;
            color: #666;
            font-size: 0.9rem;
        }

        @media (max-width: 576px) {
            .success-container {
                padding: 30px 20px;
            }
            
            .success-title {
                font-size: 1.5rem;
            }
            
            .success-message {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        <h1 class="success-title">Usuário cadastrado com sucesso!</h1>
        <p class="success-message">
            Seu cadastro foi realizado com sucesso no sistema "Eai Cidadão!". 
            Agora você pode acessar todos os serviços disponíveis para os cidadãos 
            de Santa Izabel do Oeste.
        </p>
        <a href="../login_cidadao.php" class="button">
            <i class="fas fa-sign-in-alt"></i>
            Fazer Login
        </a>
        <p class="footer">
            &copy; 2025 Prefeitura Municipal de Santa Izabel do Oeste
        </p>
    </div>
</body>
</html>