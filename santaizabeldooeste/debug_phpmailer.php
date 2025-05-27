<?php
/**
 * DIAGNÓSTICO - Execute este arquivo primeiro para verificar os caminhos
 * Salve como: debug_phpmailer.php na raiz do projeto
 */

echo "<h1>🔍 Diagnóstico PHPMailer</h1>";

echo "<h2>📁 Verificação de Arquivos</h2>";

// Caminhos possíveis para o PHPMailer
$caminhos_phpmailer = [
    __DIR__ . '/phpmailer/src/PHPMailer.php',
    __DIR__ . '/phpmailer/PHPMailer.php',
    __DIR__ . '/PHPMailer/src/PHPMailer.php',
    __DIR__ . '/lib/phpmailer/src/PHPMailer.php',
    __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php'
];

echo "<h3>Verificando caminhos possíveis:</h3>";
echo "<ul>";

$caminho_correto = null;
foreach ($caminhos_phpmailer as $caminho) {
    $existe = file_exists($caminho);
    echo "<li>" . $caminho . " - " . ($existe ? "✅ ENCONTRADO" : "❌ Não encontrado") . "</li>";
    if ($existe && !$caminho_correto) {
        $caminho_correto = dirname($caminho);
    }
}
echo "</ul>";

if ($caminho_correto) {
    echo "<div style='background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 20px 0;'>";
    echo "<h3>✅ PHPMailer encontrado!</h3>";
    echo "<p><strong>Pasta correta:</strong> $caminho_correto</p>";
    echo "</div>";
    
    // Testar carregamento
    echo "<h3>🧪 Teste de Carregamento</h3>";
    try {
        require_once $caminho_correto . '/PHPMailer.php';
        require_once $caminho_correto . '/SMTP.php';
        require_once $caminho_correto . '/Exception.php';
        
        echo "<p style='color: green;'>✅ Arquivos carregados com sucesso!</p>";
        
        // Testar criação da classe
        $mail = new PHPMailer(true);
        echo "<p style='color: green;'>✅ Classe PHPMailer criada com sucesso!</p>";
        echo "<p><strong>Versão:</strong> " . PHPMailer::VERSION . "</p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Erro ao carregar: " . $e->getMessage() . "</p>";
    }
    
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 20px 0;'>";
    echo "<h3>❌ PHPMailer NÃO encontrado!</h3>";
    echo "<p>Você precisa baixar e extrair o PHPMailer primeiro.</p>";
    echo "</div>";
}

echo "<h2>📋 Estrutura Atual do Projeto</h2>";
echo "<h3>Pasta raiz (" . __DIR__ . "):</h3>";
echo "<ul>";
$arquivos = scandir(__DIR__);
foreach ($arquivos as $arquivo) {
    if ($arquivo != '.' && $arquivo != '..') {
        $tipo = is_dir(__DIR__ . '/' . $arquivo) ? '📁' : '📄';
        echo "<li>$tipo $arquivo</li>";
    }
}
echo "</ul>";

// Se existir pasta phpmailer, mostrar conteúdo
if (is_dir(__DIR__ . '/phpmailer')) {
    echo "<h3>Conteúdo da pasta phpmailer/:</h3>";
    echo "<ul>";
    $arquivos_phpmailer = scandir(__DIR__ . '/phpmailer');
    foreach ($arquivos_phpmailer as $arquivo) {
        if ($arquivo != '.' && $arquivo != '..') {
            $tipo = is_dir(__DIR__ . '/phpmailer/' . $arquivo) ? '📁' : '📄';
            echo "<li>$tipo $arquivo</li>";
            
            // Se for pasta src, mostrar conteúdo
            if ($arquivo == 'src' && is_dir(__DIR__ . '/phpmailer/src')) {
                echo "<ul>";
                $arquivos_src = scandir(__DIR__ . '/phpmailer/src');
                foreach ($arquivos_src as $arquivo_src) {
                    if ($arquivo_src != '.' && $arquivo_src != '..') {
                        echo "<li>📄 $arquivo_src</li>";
                    }
                }
                echo "</ul>";
            }
        }
    }
    echo "</ul>";
}

echo "<h2>💡 Próximos Passos</h2>";
if ($caminho_correto) {
    echo "<div style='background: #d1ecf1; padding: 15px; border-left: 4px solid #17a2b8;'>";
    echo "<h3>Atualize o EmailService.php:</h3>";
    echo "<p>Use este caminho nos require_once:</p>";
    echo "<pre style='background: #2d3748; color: #68d391; padding: 10px; border-radius: 4px;'>";
    echo "require_once '" . str_replace(__DIR__, '__DIR__', $caminho_correto) . "/PHPMailer.php';\n";
    echo "require_once '" . str_replace(__DIR__, '__DIR__', $caminho_correto) . "/SMTP.php';\n";
    echo "require_once '" . str_replace(__DIR__, '__DIR__', $caminho_correto) . "/Exception.php';";
    echo "</pre>";
    echo "</div>";
} else {
    echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107;'>";
    echo "<h3>Baixe o PHPMailer:</h3>";
    echo "<ol>";
    echo "<li>Acesse: <a href='https://github.com/PHPMailer/PHPMailer/archive/refs/heads/master.zip' target='_blank'>GitHub PHPMailer</a></li>";
    echo "<li>Baixe o arquivo ZIP</li>";
    echo "<li>Extraia na pasta do projeto</li>";
    echo "<li>Renomeie para 'phpmailer'</li>";
    echo "<li>Execute este diagnóstico novamente</li>";
    echo "</ol>";
    echo "</div>";
}
?>