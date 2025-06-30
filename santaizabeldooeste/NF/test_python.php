<?php
echo "<h2>Teste de Diagnóstico</h2>";

// Testa se PHP consegue executar comandos
echo "<h3>1. Testando execução de comandos:</h3>";
$test = shell_exec('echo "PHP funcionando" 2>&1');
echo "Resultado: " . htmlspecialchars($test) . "<br>";

// Testa versões do Python
echo "<h3>2. Testando Python:</h3>";
$pythonCommands = ['python3', 'python', 'py'];

foreach ($pythonCommands as $cmd) {
    echo "Testando '$cmd':<br>";
    $output = shell_exec("$cmd --version 2>&1");
    echo "- Saída: " . htmlspecialchars($output) . "<br>";
    
    if ($output && stripos($output, 'python') !== false) {
        echo "- ✅ $cmd funciona!<br>";
        
        // Testa as bibliotecas
        echo "- Testando bibliotecas:<br>";
        $libTest = shell_exec("$cmd -c \"import pdfplumber; import PyPDF2; print('Bibliotecas OK')\" 2>&1");
        echo "- Bibliotecas: " . htmlspecialchars($libTest) . "<br>";
    } else {
        echo "- ❌ $cmd não funciona<br>";
    }
    echo "<br>";
}

// Testa se o arquivo existe
echo "<h3>3. Testando arquivos:</h3>";
$files = ['extract_nf.py', 'process.php', 'debug.log'];
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file existe<br>";
    } else {
        echo "❌ $file NÃO existe<br>";
    }
}

// Testa permissões de pastas
echo "<h3>4. Testando permissões:</h3>";
$dirs = ['uploads', 'downloads', '.'];
foreach ($dirs as $dir) {
    if (is_writable($dir)) {
        echo "✅ $dir tem permissão de escrita<br>";
    } else {
        echo "❌ $dir NÃO tem permissão de escrita<br>";
        if (!file_exists($dir)) {
            echo "  (pasta não existe)<br>";
        }
    }
}

// Mostra informações do servidor
echo "<h3>5. Informações do servidor:</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Sistema: " . php_uname() . "<br>";
echo "Diretório atual: " . getcwd() . "<br>";

// Testa se debug.log tem conteúdo
if (file_exists('debug.log')) {
    echo "<h3>6. Últimas linhas do debug.log:</h3>";
    $debugContent = file_get_contents('debug.log');
    echo "<pre>" . htmlspecialchars(substr($debugContent, -1000)) . "</pre>";
}
?>