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

        .redirect-message {
            color: #0d47a1;
            font-size: 1.2rem;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .counter {
            display: inline-block;
            background-color: #0d47a1;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            line-height: 40px;
            font-size: 1.3rem;
            font-weight: bold;
            margin-top: 5px;
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
    <script>
        // Função para redirecionar após contagem regressiva
        window.onload = function() {
            let count = 5;
            const counterElement = document.getElementById('counter');
            
            // Atualiza o contador a cada segundo
            const interval = setInterval(function() {
                count--;
                counterElement.innerText = count;
                
                // Quando o contador chegar a zero, redirecionar
                if (count <= 0) {
                    clearInterval(interval);
                    window.location.href = "./login_cidadao.php";
                }
            }, 1000);
        };
    </script>
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
        <p class="redirect-message">
            Você será redirecionado em breve, aguarde
        </p>
        <div class="counter" id="counter">5</div>
        <p class="footer">
            &copy; 2025 Prefeitura Municipal de Santa Izabel do Oeste
        </p>
    </div>
</body>
</html>