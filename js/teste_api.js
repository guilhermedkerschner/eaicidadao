// Arquivo para testar a comunicação com a API

document.addEventListener('DOMContentLoaded', function() {
    console.log('Testando comunicação com a API...');
    
    // Cria um elemento para exibir o resultado
    const resultElement = document.createElement('div');
    resultElement.id = 'api-result';
    document.body.appendChild(resultElement);
    
    // Testa a API
    fetch('./teste_api.php')
        .then(response => {
            console.log('Status da resposta:', response.status);
            console.log('Headers da resposta:', response.headers);
            return response.json();
        })
        .then(data => {
            console.log('Resposta da API:', data);
            resultElement.innerHTML = `
                <h2>Teste da API - Sucesso!</h2>
                <pre>${JSON.stringify(data, null, 2)}</pre>
            `;
            resultElement.style.color = 'green';
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
            resultElement.innerHTML = `
                <h2>Teste da API - Erro!</h2>
                <pre>${error}</pre>
            `;
            resultElement.style.color = 'red';
        });
        
    // Testa o processamento de login
    console.log('Testando processa_login.php...');
    
    fetch('./processa/processa_login.php', {
        method: 'POST',
        body: new FormData()
    })
    .then(response => {
        console.log('Status da resposta processa_login:', response.status);
        console.log('Headers da resposta processa_login:', response.headers);
        return response.text(); // Usar text() em vez de json() para ver o que está sendo retornado
    })
    .then(data => {
        console.log('Resposta do processa_login:', data);
        
        try {
            // Tenta converter para JSON
            const jsonData = JSON.parse(data);
            
            // Cria elemento para exibir resultado
            const loginResultElement = document.createElement('div');
            loginResultElement.id = 'login-api-result';
            document.body.appendChild(loginResultElement);
            
            loginResultElement.innerHTML = `
                <h2>Teste do Processamento de Login - Sucesso!</h2>
                <pre>${JSON.stringify(jsonData, null, 2)}</pre>
            `;
            loginResultElement.style.color = 'green';
        } catch (e) {
            // Se não for JSON válido, exibe o HTML retornado
            const loginResultElement = document.createElement('div');
            loginResultElement.id = 'login-api-result';
            document.body.appendChild(loginResultElement);
            
            loginResultElement.innerHTML = `
                <h2>Teste do Processamento de Login - Erro!</h2>
                <p>A resposta não é um JSON válido:</p>
                <textarea style="width: 100%; height: 200px;">${data}</textarea>
            `;
            loginResultElement.style.color = 'red';
        }
    })
    .catch(error => {
        console.error('Erro na requisição de login:', error);
        
        const loginResultElement = document.createElement('div');
        loginResultElement.id = 'login-api-result';
        document.body.appendChild(loginResultElement);
        
        loginResultElement.innerHTML = `
            <h2>Teste do Processamento de Login - Erro!</h2>
            <pre>${error}</pre>
        `;
        loginResultElement.style.color = 'red';
    });
});