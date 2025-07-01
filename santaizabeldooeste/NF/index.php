<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processador de Notas Fiscais</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .upload-area { border: 2px dashed #ccc; padding: 20px; text-align: center; margin: 20px 0; }
        .upload-area.dragover { border-color: #007cba; background-color: #f0f8ff; }
        .results { margin-top: 20px; }
        .sql-output { background: #f5f5f5; padding: 15px; border-radius: 5px; font-family: monospace; white-space: pre-wrap; }
        .progress { width: 100%; background-color: #f0f0f0; border-radius: 10px; margin: 10px 0; }
        .progress-bar { width: 0%; height: 20px; background-color: #4CAF50; border-radius: 10px; text-align: center; line-height: 20px; color: white; }
        .btn { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .btn:hover { background: #005a8b; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Processador de Notas Fiscais</h1>
        
        <form id="uploadForm" method="post" enctype="multipart/form-data">
            <div class="upload-area" id="uploadArea">
                <p>Arraste os arquivos PDF aqui ou clique para selecionar</p>
                <input type="file" name="pdfs[]" id="pdfFiles" multiple accept=".pdf" style="display: none;">
                <button type="button" class="btn" onclick="document.getElementById('pdfFiles').click()">
                    Selecionar PDFs
                </button>
            </div>
            
            <div id="fileList"></div>
            
            <button type="submit" class="btn" id="processBtn" style="display: none;">
                Processar Notas Fiscais
            </button>
        </form>

        <div class="progress" id="progressContainer" style="display: none;">
            <div class="progress-bar" id="progressBar">0%</div>
        </div>

        <div class="results" id="results"></div>
    </div>

    <script>
        // Drag and drop functionality
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('pdfFiles');
        const fileList = document.getElementById('fileList');
        const processBtn = document.getElementById('processBtn');

        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            fileInput.files = e.dataTransfer.files;
            updateFileList();
        });

        fileInput.addEventListener('change', updateFileList);

        function updateFileList() {
            const files = fileInput.files;
            fileList.innerHTML = '';
            
            if (files.length > 0) {
                const list = document.createElement('ul');
                for (let file of files) {
                    const item = document.createElement('li');
                    item.textContent = file.name;
                    list.appendChild(item);
                }
                fileList.appendChild(list);
                processBtn.style.display = 'block';
            } else {
                processBtn.style.display = 'none';
            }
        }

        // Form submission
            document.getElementById('uploadForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const progressContainer = document.getElementById('progressContainer');
                const progressBar = document.getElementById('progressBar');
                const results = document.getElementById('results');
                
                progressContainer.style.display = 'block';
                results.innerHTML = '';
                
                document.getElementById('uploadForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (fileInput.files.length === 0) {
                alert('Por favor, selecione pelo menos um arquivo PDF.');
                return;
            }

            console.log('Iniciando processamento de', fileInput.files.length, 'arquivos');

            // Desabilita botão e mostra loading
            processBtn.disabled = true;
            processBtn.innerHTML = '<span class="loading"></span> Processando...';
            
            // Mostra barra de progresso
            progressContainer.classList.remove('hidden');
            progressBar.style.width = '10%';
            progressBar.textContent = 'Iniciando...';
            
            // Limpa resultados anteriores
            results.innerHTML = '';

            const formData = new FormData();
            for (let file of fileInput.files) {
                formData.append('pdfs[]', file);
                console.log('Adicionado arquivo:', file.name);
            }

            try {
                // Simula progresso
                let progress = 10;
                const progressInterval = setInterval(() => {
                    if (progress < 90) {
                        progress += Math.random() * 20;
                        progressBar.style.width = progress + '%';
                        progressBar.textContent = Math.round(progress) + '%';
                    }
                }, 500);

                console.log('Enviando requisição para process.php...');
                
                const response = await fetch('process.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text()) // Mudei de .json() para .text()
                .then(responseText => {
                    console.log('Resposta completa do servidor:', responseText);
                    
                    // Tenta fazer parse do JSON
                    try {
                        const data = JSON.parse(responseText);
                        processedData = data;
                        displayResults(data);
                    } catch (error) {
                        // Se não conseguir fazer parse, mostra o erro HTML
                        results.innerHTML = `
                            <div class="results error">
                                <h3>❌ Erro no Servidor</h3>
                                <h4>Resposta do servidor:</h4>
                                <pre style="background: #f8f8f8; padding: 10px; overflow-x: auto; font-size: 12px;">${responseText}</pre>
                            </div>
                        `;
                    }
                })

                console.log('Resposta recebida:', response.status, response.statusText);

                clearInterval(progressInterval);
                progressBar.style.width = '100%';
                progressBar.textContent = '100%';

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const responseText = await response.text();
                console.log('Resposta completa:', responseText);

                let data;
                try {
                    data = JSON.parse(responseText);
                    console.log('JSON parseado:', data);
                } catch (parseError) {
                    console.error('Erro ao fazer parse do JSON:', parseError);
                    throw new Error('Resposta inválida do servidor: ' + responseText.substring(0, 200));
                }

                processedData = data;
                displayResults(data);

            } catch (error) {
                console.error('Erro completo:', error);
                results.innerHTML = `
                    <div class="results error">
                        <h3>❌ Erro no Processamento</h3>
                        <p><strong>Erro:</strong> ${error.message}</p>
                        <p><strong>Possíveis causas:</strong></p>
                        <ul>
                            <li>Python não está instalado ou não está no PATH</li>
                            <li>Bibliotecas Python não instaladas (pdfplumber, PyPDF2)</li>
                            <li>Arquivo extract_nf.py não encontrado</li>
                            <li>Permissões de pasta</li>
                        </ul>
                        <p><strong>Para diagnosticar:</strong> Abra o Console do navegador (F12) e veja os logs</p>
                    </div>
                `;
            } finally {
                // Reabilita botão
                processBtn.disabled = false;
                processBtn.innerHTML = '⚡ Processar Notas Fiscais';
            }
        });

        // Função para download seguro via JavaScript
        function downloadSQL(filename, content) {
            const blob = new Blob([content], { type: 'application/sql' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        }
    </script>
</body>
</html>