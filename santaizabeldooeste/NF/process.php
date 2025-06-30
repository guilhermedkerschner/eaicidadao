<?php
// Adiciona estas linhas no INÍCIO do process.php:
error_reporting(E_ALL);
ini_set('display_errors', 0); // Desliga para não quebrar o JSON
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

// Força o header JSON
header('Content-Type: application/json; charset=utf-8');

// Função para log seguro
function safeLog($message) {
    file_put_contents('debug_safe.log', date('[Y-m-d H:i:s] ') . $message . PHP_EOL, FILE_APPEND | LOCK_EX);
}

// Captura qualquer output antes do JSON
ob_start();

function processNFs() {
    safeLog("=== Iniciando processamento ===");
    
    try {
        // Verifica se Python está funcionando ANTES de processar arquivos
        $pythonCommands = ['python', 'py', 'python3'];
        $pythonCmd = null;
        
        foreach ($pythonCommands as $cmd) {
            $testOutput = shell_exec("$cmd --version 2>&1");
            safeLog("Testando $cmd: " . substr($testOutput, 0, 100));
            
            if ($testOutput && stripos($testOutput, 'python') !== false) {
                $pythonCmd = $cmd;
                safeLog("Python encontrado: $cmd");
                break;
            }
        }
        
        if (!$pythonCmd) {
            return [
                'success' => false, 
                'message' => 'Python não encontrado. Instale Python e tente novamente.',
                'debug' => 'Comandos testados: ' . implode(', ', $pythonCommands)
            ];
        }
        
        // Verifica se o script exists
        if (!file_exists('extract_nf.py')) {
            return [
                'success' => false,
                'message' => 'Script extract_nf.py não encontrado',
                'debug' => 'Diretório atual: ' . getcwd()
            ];
        }
        
        // Verifica arquivos enviados
        if (!isset($_FILES['pdfs']) || empty($_FILES['pdfs']['name'][0])) {
            return [
                'success' => false, 
                'message' => 'Nenhum arquivo foi enviado',
                'debug' => 'FILES: ' . json_encode($_FILES)
            ];
        }

        $uploadDir = 'uploads/';
        $downloadDir = 'downloads/';
        
        // Cria diretórios
        foreach ([$uploadDir, $downloadDir] as $dir) {
            if (!file_exists($dir)) {
                if (!mkdir($dir, 0755, true)) {
                    return [
                        'success' => false,
                        'message' => "Erro ao criar diretório: $dir"
                    ];
                }
                safeLog("Diretório criado: $dir");
            }
        }

        $sqls = [];
        $processed = 0;
        $errors = 0;
        $errorDetails = [];

        foreach ($_FILES['pdfs']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['pdfs']['error'][$key] === UPLOAD_ERR_OK) {
                $originalName = $_FILES['pdfs']['name'][$key];
                $uploadPath = $uploadDir . uniqid() . '_' . basename($originalName);
                
                safeLog("Processando: $originalName");
                
                if (move_uploaded_file($tmpName, $uploadPath)) {
                    // Testa bibliotecas Python
                    $libTest = shell_exec("$pythonCmd -c \"import pdfplumber, PyPDF2; print('OK')\" 2>&1");
                    safeLog("Teste bibliotecas: " . $libTest);
                    
                    if (stripos($libTest, 'OK') === false) {
                        $errorDetails[] = "Bibliotecas Python não instaladas: $libTest";
                        $errors++;
                        unlink($uploadPath);
                        continue;
                    }
                    
                    // Executa Python
                    $command = "$pythonCmd extract_nf_final.py " . escapeshellarg($uploadPath) . " 2>&1";
                    safeLog("Comando: $command");
                    
                    $output = shell_exec($command);
                    safeLog("Output: " . substr($output, 0, 500));
                    
                    if (!empty($output) && strpos($output, 'INSERT INTO') !== false) {
                        $sqls[] = trim($output);
                        $processed++;
                        safeLog("✓ Sucesso: $originalName");
                    } else {
                        $errors++;
                        $errorDetails[] = "Arquivo: $originalName - Output: " . substr($output, 0, 200);
                        safeLog("✗ Erro: $originalName");
                    }
                    
                    unlink($uploadPath);
                } else {
                    $errors++;
                    $errorDetails[] = "Erro no upload: $originalName";
                }
            }
        }

        // Salva SQLs
        $filename = 'nfs_' . date('Y-m-d_H-i-s') . '.sql';
        $filepath = $downloadDir . $filename;
        
        $sqlContent = '';
        if (!empty($sqls)) {
            $sqlContent = "-- Notas Fiscais - " . date('Y-m-d H:i:s') . "\n\n";
            $sqlContent .= implode("\n\n", $sqls);
            file_put_contents($filepath, $sqlContent);
        }

        return [
            'success' => true,
            'processed' => $processed,
            'total' => count($_FILES['pdfs']['tmp_name']),
            'errors' => $errors,
            'sqls' => array_slice($sqls, 0, 3),
            'filename' => $filename,
            'sqlContent' => $sqlContent,
            'errorDetails' => $errorDetails,
            'pythonCmd' => $pythonCmd
        ];

    } catch (Exception $e) {
        safeLog("ERRO GERAL: " . $e->getMessage());
        return [
            'success' => false, 
            'message' => $e->getMessage(),
            'debug' => $e->getTraceAsString()
        ];
    }
}

try {
    $result = processNFs();
    
    // Limpa qualquer output anterior
    ob_clean();
    
    // Retorna JSON
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Erro crítico: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

// Finaliza o buffer
ob_end_flush();
?>