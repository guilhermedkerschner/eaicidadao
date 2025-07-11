<?php
/**
 * EmailService.php - VERSÃO CORRIGIDA COM NAMESPACE
 * Arquivo: lib/EmailService.php
 */

// Incluir arquivos PHPMailer
require_once __DIR__ . '/../phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../phpmailer/src/SMTP.php';
require_once __DIR__ . '/../phpmailer/src/Exception.php';

// IMPORTANTE: Usar as classes do namespace PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Verificar se arquivo de configuração existe
if (!file_exists(__DIR__ . '/email_config.php')) {
    throw new Exception("email_config.php não encontrado!");
}
require_once __DIR__ . '/email_config.php';

class EmailService {
    private $mailer;
    
    public function __construct() {
        try {
            // Usar PHPMailer COM namespace
            $this->mailer = new PHPMailer(true);
            $this->configurarSMTP();
        } catch (Exception $e) {
            throw new Exception("Erro ao inicializar EmailService: " . $e->getMessage());
        }
    }
    
    /**
     * Configura as definições SMTP
     */
    private function configurarSMTP() {
        try {
            // Configurações do servidor
            $this->mailer->isSMTP();
            $this->mailer->Host = SMTP_HOST;
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = SMTP_USERNAME;
            $this->mailer->Password = SMTP_PASSWORD;
            $this->mailer->SMTPSecure = SMTP_SECURE;
            $this->mailer->Port = SMTP_PORT;
            
            // Configurações gerais
            $this->mailer->CharSet = EMAIL_CHARSET;
            $this->mailer->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            
            // Debug (apenas em desenvolvimento)
            if (defined('EMAIL_DEBUG') && EMAIL_DEBUG) {
                $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;
            }
            
        } catch (Exception $e) {
            error_log("Erro na configuração SMTP: " . $e->getMessage());
            throw new Exception("Erro na configuração do email: " . $e->getMessage());
        }
    }
    
    /**
     * Envia email de recuperação de senha
     */
    public function enviarRecuperacaoSenha($email, $nome, $token) {
        try {
            $this->mailer->clearAllRecipients();
            $this->mailer->addAddress($email, $nome);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Recuperação de Senha - Eai Cidadão!';
            
            // Gerar URL de recuperação
            $url_recuperacao = SITE_URL . "/recuperar_senha.php?token=" . $token;
            
            // Corpo do email
            $corpo = $this->gerarTemplateRecuperacao($nome, $token, $url_recuperacao);
            $this->mailer->Body = $corpo;
            
            // Versão texto simples
            $this->mailer->AltBody = $this->gerarTextoSimples($nome, $token);
            
            $resultado = $this->mailer->send();
            
            if ($resultado) {
                $this->logarEnvio($email, 'recuperacao_senha', 'sucesso');
                return true;
            } else {
                $this->logarEnvio($email, 'recuperacao_senha', 'falha');
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Erro ao enviar email de recuperação: " . $e->getMessage());
            $this->logarEnvio($email, 'recuperacao_senha', 'erro: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Envia email de confirmação de cadastro
     */
    public function enviarConfirmacaoCadastro($email, $nome) {
        try {
            $this->mailer->clearAllRecipients();
            $this->mailer->addAddress($email, $nome);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Bem-vindo ao Eai Cidadão! - Cadastro Realizado';
            
            $corpo = $this->gerarTemplateBoasVindas($nome);
            $this->mailer->Body = $corpo;
            
            $this->mailer->AltBody = "Olá {$nome}, seu cadastro no sistema Eai Cidadão! foi realizado com sucesso!";
            
            $resultado = $this->mailer->send();
            
            if ($resultado) {
                $this->logarEnvio($email, 'confirmacao_cadastro', 'sucesso');
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Erro ao enviar email de confirmação: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Envia notificação de status de solicitação
     */
    public function enviarNotificacaoStatus($email, $nome, $protocolo, $status, $observacoes = '') {
        try {
            $this->mailer->clearAllRecipients();
            $this->mailer->addAddress($email, $nome);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = "Atualização de Status - Protocolo {$protocolo}";
            
            $corpo = $this->gerarTemplateStatus($nome, $protocolo, $status, $observacoes);
            $this->mailer->Body = $corpo;
            
            $resultado = $this->mailer->send();
            
            if ($resultado) {
                $this->logarEnvio($email, 'notificacao_status', 'sucesso');
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Erro ao enviar notificação de status: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Gera template HTML para recuperação de senha
     */
    private function gerarTemplateRecuperacao($nome, $token, $url_recuperacao) {
        return '
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Recuperação de Senha</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #0d47a1; color: white; padding: 20px; text-align: center; }
                .content { background-color: #f9f9f9; padding: 30px; }
                .token-box { background-color: #e3f2fd; border: 2px dashed #2196f3; padding: 20px; text-align: center; margin: 20px 0; }
                .token { font-size: 24px; font-weight: bold; color: #0d47a1; letter-spacing: 3px; }
                .button { display: inline-block; background-color: #2e7d32; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { background-color: #f1f1f1; padding: 20px; text-align: center; font-size: 12px; color: #666; }
                .warning { background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🏛️ Eai Cidadão!</h1>
                    <p>' . PREFEITURA_NOME . '</p>
                </div>
                
                <div class="content">
                    <h2>Olá, ' . htmlspecialchars($nome) . '!</h2>
                    
                    <p>Recebemos uma solicitação para redefinir sua senha no sistema <strong>Eai Cidadão!</strong></p>
                    
                    <p>Seu código de verificação é:</p>
                    
                    <div class="token-box">
                        <div class="token">' . $token . '</div>
                    </div>
                                        
                    <div class="warning">
                        <strong>⚠️ Importante:</strong>
                        <ul>
                            <li>Este código é válido por <strong>15 minutos</strong></li>
                            <li>Use apenas se você solicitou a recuperação de senha</li>
                            <li>Não compartilhe este código com ninguém</li>
                            <li>Se você não solicitou, ignore este e-mail</li>
                        </ul>
                    </div>
                    
                    <p>Se você tiver dúvidas, entre em contato conosco através dos canais oficiais.</p>
                </div>
                
                <div class="footer">
                    <p><strong>' . PREFEITURA_NOME . '</strong></p>
                    <p>' . PREFEITURA_ENDERECO . '</p>
                    <p>Telefone: ' . PREFEITURA_TELEFONE . ' | E-mail: ' . PREFEITURA_EMAIL . '</p>
                    <p>Este é um e-mail automático, não responda a esta mensagem.</p>
                </div>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Gera template de boas-vindas
     */
    private function gerarTemplateBoasVindas($nome) {
        return '
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Bem-vindo ao Eai Cidadão!</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #2e7d32; color: white; padding: 20px; text-align: center; }
                .content { background-color: #f9f9f9; padding: 30px; }
                .footer { background-color: #f1f1f1; padding: 20px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🎉 Bem-vindo!</h1>
                    <p>Seu cadastro foi realizado com sucesso!</p>
                </div>
                
                <div class="content">
                    <h2>Olá, ' . htmlspecialchars($nome) . '!</h2>
                    <p>É com grande satisfação que damos as boas-vindas ao sistema <strong>Eai Cidadão!</strong></p>
                </div>
                
                <div class="footer">
                    <p><strong>' . PREFEITURA_NOME . '</strong></p>
                    <p>' . PREFEITURA_ENDERECO . '</p>
                </div>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Gera template para notificação de status
     */
    private function gerarTemplateStatus($nome, $protocolo, $status, $observacoes) {
        return '
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <title>Atualização de Status</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #0d47a1; color: white; padding: 20px; text-align: center; }
                .content { background-color: #f9f9f9; padding: 30px; }
                .status-box { background-color: #e3f2fd; border-left: 4px solid #2196f3; padding: 20px; margin: 20px 0; }
                .footer { background-color: #f1f1f1; padding: 20px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>📋 Atualização de Status</h1>
                    <p>Protocolo: ' . $protocolo . '</p>
                </div>
                
                <div class="content">
                    <h2>Olá, ' . htmlspecialchars($nome) . '!</h2>
                    
                    <div class="status-box">
                        <h3>Status Atual: ' . $status . '</h3>
                        ' . ($observacoes ? '<p><strong>Observações:</strong> ' . htmlspecialchars($observacoes) . '</p>' : '') . '
                    </div>
                </div>
                
                <div class="footer">
                    <p><strong>' . PREFEITURA_NOME . '</strong></p>
                </div>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Gera versão em texto simples
     */
    private function gerarTextoSimples($nome, $token) {
        return "
            Olá, {$nome}!

            Recebemos uma solicitação para redefinir sua senha no sistema Eai Cidadão!

            Seu código de verificação é: {$token}

            Este código é válido por 15 minutos.

            Atenciosamente,
            " . PREFEITURA_NOME;
    }
    
    function gerarTemplateConfirmacaoCadastro($dadosUsuario, $protocolo) {
        $template = '
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Confirmação de Cadastro - Programa Habitacional</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; }
                .header { background-color: #0d47a1; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .protocolo { background-color: #e3f2fd; padding: 15px; border-left: 4px solid #0d47a1; margin: 20px 0; }
                .protocolo-numero { font-size: 1.5em; font-weight: bold; color: #0d47a1; }
                .info-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
                .info-table th, .info-table td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
                .info-table th { background-color: #f5f5f5; font-weight: bold; }
                .footer { background-color: #f8f9fa; padding: 15px; text-align: center; font-size: 0.9em; color: #666; }
                .alert { background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 15px 0; border-radius: 4px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Confirmação de Cadastro</h1>
                    <p>Programa Habitacional - HABITASIO</p>
                    <p>Prefeitura Municipal de Santa Izabel do Oeste</p>
                </div>
                
                <div class="content">
                    <p>Olá, <strong>' . htmlspecialchars($dadosUsuario['nome']) . '</strong>,</p>
                    
                    <p>Seu cadastro no Programa Habitacional foi realizado com sucesso!</p>
                    
                    <div class="protocolo">
                        <p><strong>Número do Protocolo:</strong></p>
                        <div class="protocolo-numero">' . htmlspecialchars($protocolo) . '</div>
                    </div>
                    
                    <h3>Dados do Cadastro:</h3>
                    <table class="info-table">
                        <tr><th>Nome Completo:</th><td>' . htmlspecialchars($dadosUsuario['nome']) . '</td></tr>
                        <tr><th>CPF:</th><td>' . htmlspecialchars($dadosUsuario['cpf']) . '</td></tr>
                        <tr><th>Email:</th><td>' . htmlspecialchars($dadosUsuario['email']) . '</td></tr>
                        <tr><th>Celular:</th><td>' . htmlspecialchars($dadosUsuario['celular']) . '</td></tr>
                        <tr><th>Programa de Interesse:</th><td>' . htmlspecialchars($dadosUsuario['programa_interesse']) . '</td></tr>
                        <tr><th>Data do Cadastro:</th><td>' . date('d/m/Y H:i:s') . '</td></tr>
                        <tr><th>Status:</th><td><strong>PENDENTE DE ANÁLISE</strong></td></tr>
                    </table>
                    
                    <div class="alert">
                        <h4>📋 Próximos Passos:</h4>
                        <ul>
                            <li>Seu cadastro será analisado pela equipe da Secretaria de Assistência Social</li>
                            <li>Você será contatado caso seja necessário complementar alguma informação</li>
                            <li>Para acompanhar o status, acesse o portal Eai Cidadão! com seu protocolo</li>
                            <li>Guarde este email e o número do protocolo para suas consultas</li>
                        </ul>
                    </div>
                    
                    <h4>📞 Contato da Secretaria de Assistência Social:</h4>
                    <p><strong>Endereço:</strong> Rua Jacarandá, 620 - Centro, Santa Izabel do Oeste - PR</p>
                    <p><strong>Telefones:</strong> (46) 3552-1512 (Secretaria) | (46) 3552-1513 (CRAS)</p>
                    <p><strong>Email:</strong> social@santaizabeldooeste.pr.gov.br</p>
                    <p><strong>Horário:</strong> Segunda a Sexta, das 08:00 às 11:30 e das 13:00 às 17:00</p>
                </div>
                
                <div class="footer">
                    <p>Este é um email automático, não responda a esta mensagem.</p>
                    <p>Para dúvidas, entre em contato através dos canais oficiais da Secretaria de Assistência Social.</p>
                    <p>&copy; 2025 Prefeitura Municipal de Santa Izabel do Oeste - Sistema Eai Cidadão!</p>
                </div>
            </div>
        </body>
        </html>';
        
        return $template;
    }


    /**
     * Registra logs de envio de email
     */
    private function logarEnvio($email, $tipo, $status) {
        $log_dir = __DIR__ . '/../logs/';
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        $log = date('Y-m-d H:i:s') . " - Email: {$email} | Tipo: {$tipo} | Status: {$status}" . PHP_EOL;
        file_put_contents($log_dir . 'email_log.txt', $log, FILE_APPEND | LOCK_EX);
    }
}
?>