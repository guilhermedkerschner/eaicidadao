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
            
            fetch('process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                progressBar.style.width = '100%';
                progressBar.textContent = '100%';
                
                if (data.success) {
                    results.innerHTML = `
                        <h3 class="success">Processamento Concluído!</h3>
                        <p>Processadas: ${data.processed}/${data.total} notas fiscais</p>
                        <p>Erros: ${data.errors}</p>
                        <h4>SQLs Gerados:</h4>
                        <div class="sql-output">${data.sqls.join('\n\n')}</div>
                        <a href="download.php?file=${data.filename}" class="btn">Download SQL</a>
                    `;
                } else {
                    results.innerHTML = `<p class="error">Erro: ${data.message}</p>`;
                }
            })
            .catch(error => {
                results.innerHTML = `<p class="error">Erro na requisição: ${error}</p>`;
            });
        });
    </script>
</body>
</html>