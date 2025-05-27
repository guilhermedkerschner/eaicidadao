<?php
/**
 * Arquivo de Teste do Sistema de Email
 * Execute: http://localhost/eaicidadao/santaizabeldooeste/test_email.php
 */

echo "<h1>🧪 Teste do Sistema de Email - Eai Cidadão!</h1>";

// Verificar se arquivos existem
echo "<h2>📋 1. Verificação de Arquivos</h2>";
echo "<ul>";

$arquivos_necessarios = [
    'phpmailer/src/PHPMailer.php' => 'PHPMailer Principal',
    'phpmailer/src/SMTP.php' => 'PHPMailer SMTP',
    'phpmailer/src/Exception.php' => 'PHPMailer Exception',
    'lib/EmailService.php' => 'Classe EmailService',
    'lib/email_config.php' => 'Configurações de Email'
];

$arquivos_ok = true;
foreach ($arquivos_necessarios as $arquivo => $descricao) {
    $existe = file_exists($arquivo);
    echo "<li>{$descricao}: " . ($existe ? "✅ Encontrado" : "❌ Não encontrado") . "</li>";
    if (!$existe) {
        $arquivos_ok = false;
    }
}
echo "</ul>";

if (!$arquivos_ok) {
    echo "<div style='background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 20px 0;'>";
    echo "<h3>❌ Arquivos em Falta!</h3>";
    echo "<p>Alguns arquivos necessários não foram encontrados. Verifique se:</p>";
    echo "<ul>";
    echo "<li>A pasta <code>phpmailer/</code> existe e contém a pasta <code>src/</code></li>";
    echo "<li>Os arquivos <code>EmailService.php</code> e <code>email_config.php</code> estão na pasta <code>lib/</code></li>";
    echo "</ul>";
    echo "</div>";
    exit;
}

echo "<div style='background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 20px 0;'>";
echo "<h3>✅ Todos os arquivos encontrados!</h3>";
echo "</div>";

// Testar carregamento do EmailService
echo "<h2>🔧 2. Teste de Carregamento</h2>";

try {
    require_once 'lib/EmailService.php';
    echo "<p style='color: green;'>✅ EmailService carregado com sucesso!</p>";
    
    // Testar criação da instância
    $emailService = new EmailService();
    echo "<p style='color: green;'>✅ Instância do EmailService criada com sucesso!</p>";
    
    echo "<h2>📧 3. Teste de Configuração (SEM ENVIO)</h2>";
    echo "<p>📍 <strong>Configurações atuais:</strong></p>";
    echo "<ul>";
    echo "<li><strong>Host SMTP:</strong> " . SMTP_HOST . "</li>";
    echo "<li><strong>Porta:</strong> " . SMTP_PORT . "</li>";
    echo "<li><strong>Segurança:</strong> " . SMTP_SECURE . "</li>";
    echo "<li><strong>De:</strong> " . SMTP_FROM_NAME . " &lt;" . SMTP_FROM_EMAIL . "&gt;</li>";
    echo "</ul>";
    
    echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0;'>";
    echo "<h3>⚠️ Para Teste de Envio Real:</h3>";
    echo "<p>1. Configure suas credenciais SMTP no arquivo <code>lib/email_config.php</code></p>";
    echo "<p>2. Descomente o código de teste abaixo</p>";
    echo "</div>";
    
    // TESTE DE ENVIO REAL (COMENTADO)
    
    echo "<h2>📨 4. Teste de Envio Real</h2>";
    
    $resultado = $emailService->enviarRecuperacaoSenha(
        'guilherme_kerschner@hotmail.com', // COLOQUE SEU EMAIL AQUI
        'Teste Sistema',
        '123456'
    );
    
    if ($resultado) {
        echo "<div style='background: #d4edda; padding: 15px; border-left: 4px solid #28a745;'>";
        echo "<h3>✅ Email enviado com sucesso!</h3>";
        echo "<p>Verifique sua caixa de entrada (e spam também).</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545;'>";
        echo "<h3>❌ Falha no envio do email</h3>";
        echo "<p>Verifique suas configurações SMTP e conexão com a internet.</p>";
        echo "</div>";
    }
    
    
    echo "<div style='background: #d1ecf1; padding: 15px; border-left: 4px solid #17a2b8; margin: 20px 0;'>";
    echo "<h3>🎉 Sistema Funcionando!</h3>";
    echo "<p>O sistema de email está configurado e pronto para uso!</p>";
    echo "<p><strong>Próximos passos:</strong></p>";
    echo "<ol>";
    echo "<li>Configure suas credenciais SMTP reais</li>";
    echo "<li>Descomente o teste de envio acima</li>";
    echo "<li>Integre com seu sistema de recuperação de senha</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545;'>";
    echo "<h3>❌ Erro no Sistema</h3>";
    echo "<p><strong>Erro:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . " (linha " . $e->getLine() . ")</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><em>Teste executado em: " . date('d/m/Y H:i:s') . "</em></p>";
?>