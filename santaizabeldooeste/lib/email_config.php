<?php
/**
 * Configuração de Email - Sistema Eai Cidadão!
 * Arquivo: lib/email_config.php
 */

// Configurações do servidor de email
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 465); // 587 para TLS, 465 pour SSL
define('SMTP_SECURE', 'ssl'); // 'tls' ou 'ssl'
define('SMTP_USERNAME', 'atendimento@eaicidadao.com.br'); 
define('SMTP_PASSWORD', 'Sys2025%Sio@#98'); 
define('SMTP_FROM_EMAIL', 'atendimento@eaicidadao.com.br');
define('SMTP_FROM_NAME', 'Eai Cidadão!');

// Configurações gerais
define('EMAIL_DEBUG', false); // true para debug, false para produção
define('EMAIL_CHARSET', 'UTF-8');

// URLs do sistema
define('SITE_URL', 'http:eaicidadao.com.br'); // Ajuste conforme seu domínio
define('LOGO_URL', SITE_URL . '/img/logo_municipio.png');

// Informações da Eai Cidadão!
define('SISTEMA_NOME', 'Eai Cidadão!');
define('SISTEMA_ENDERECO', 'Rua Acácia, 1317 - Centro, Santa Izabel do Oeste - PR');
define('SISTEMA_TELEFONE', '(46) 99901-6831');
define('SISTEMA_EMAIL', 'atendimento@eaicidadao.com.br');
define('SISTEMA_SITE', 'https://eaicidadao.com.br');


// Informações da prefeitura Santa Izabel do Oeste
define('PREFEITURA_NOME', 'Prefeitura Municipal de Santa Izabel do Oeste');
define('PREFEITURA_ENDERECO', 'Rua Acácia, 1317 - Centro, Santa Izabel do Oeste - PR');
define('PREFEITURA_TELEFONE', '(46) 3542-1360');
define('PREFEITURA_EMAIL', 'prefsio@gmail.com');
define('PREFEITURA_SITE', 'https://santaizabeldooeste.atende.net');
?>