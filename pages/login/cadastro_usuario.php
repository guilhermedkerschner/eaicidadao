<?php
// Inicia a sessão
session_start();

// Verifica se há mensagem de erro
$mensagem_erro = "";
if (isset($_SESSION['erro_cadastro'])) {
    $mensagem_erro = $_SESSION['erro_cadastro'];
    unset($_SESSION['erro_cadastro']); // Remove a mensagem após exibi-la
}

// Verifica se há dados preenchidos anteriormente (para manter após erro)
$nome = isset($_SESSION['dados_cadastro']['nome']) ? $_SESSION['dados_cadastro']['nome'] : '';
$cpf = isset($_SESSION['dados_cadastro']['cpf']) ? $_SESSION['dados_cadastro']['cpf'] : '';
$data_nascimento = isset($_SESSION['dados_cadastro']['data_nascimento']) ? $_SESSION['dados_cadastro']['data_nascimento'] : '';
$telefone = isset($_SESSION['dados_cadastro']['telefone']) ? $_SESSION['dados_cadastro']['telefone'] : '';
$email = isset($_SESSION['dados_cadastro']['email']) ? $_SESSION['dados_cadastro']['email'] : '';
$endereco = isset($_SESSION['dados_cadastro']['endereco']) ? $_SESSION['dados_cadastro']['endereco'] : '';
$numero = isset($_SESSION['dados_cadastro']['numero']) ? $_SESSION['dados_cadastro']['numero'] : '';
$bairro = isset($_SESSION['dados_cadastro']['bairro']) ? $_SESSION['dados_cadastro']['bairro'] : '';
$cidade = isset($_SESSION['dados_cadastro']['cidade']) ? $_SESSION['dados_cadastro']['cidade'] : 'Santa Izabel do Oeste'; // Valor padrão
$uf = isset($_SESSION['dados_cadastro']['uf']) ? $_SESSION['dados_cadastro']['uf'] : 'PR'; // Valor padrão
$cep = isset($_SESSION['dados_cadastro']['cep']) ? $_SESSION['dados_cadastro']['cep'] : '';

// Limpa os dados de sessão após recuperá-los
if (isset($_SESSION['dados_cadastro'])) {
    unset($_SESSION['dados_cadastro']);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro do Cidadão - Eai Cidadão!</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="../../css/style.css">
    <link rel="stylesheet" type="text/css" href="../../css/login-cidadao.css">
    <link rel="stylesheet" type="text/css" href="../../css/cad-cidadao.css">
</head>

<body>
    <!-- Botão para voltar -->
    <a href="../login_cidadao.php" class="back-link">
        <i class="fas fa-arrow-left"></i>
        Voltar para login
    </a>

    <div class="container">
        <div class="header-container">
            <div class="municipality-logo">
                <!-- Substitua pelo caminho da sua logo -->
                <img src="../../img/logo_municipio.png" alt="Logo do Município">
            </div>
            <div class="title-container">
                <h1>Eai Cidadão!</h1>
                <h2 class="municipality-name">Município de Santa Izabel do Oeste</h2>
            </div>
        </div>

        <div class="divider"></div>

        <h3 style="margin-bottom: 20px; color: #2e7d32;">Cadastro do Cidadão</h3>
        <p style="margin-bottom: 20px;">Preencha o formulário abaixo para criar sua conta no sistema. Os campos marcados com asterisco (*) são obrigatórios.</p>
        
        <?php if (!empty($mensagem_erro)): ?>
        <div class="alert-error">
            <?php echo $mensagem_erro; ?>
        </div>
        <?php endif; ?>

        <form class="cadastro-form" action="../../controler/processar_cadastro_usuario.php" method="post">
            <h4 class="form-title">Dados Pessoais</h4>
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label for="nome" class="required-field">Nome Completo</label>
                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <input style="text-transform: uppercase" type="text" id="nome" name="nome" class="form-control" value="<?php echo $nome; ?>" required>
                        </div>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label for="cpf" class="required-field">CPF</label>
                        <div class="input-group">
                            <i class="fas fa-id-card"></i>
                            <input type="text" id="cpf" name="cpf" class="form-control" placeholder="000.000.000-00" value="<?php echo $cpf; ?>" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label for="data_nascimento" class="required-field">Data de Nascimento</label>
                        <div class="input-group">
                            <i class="fas fa-calendar-alt"></i>
                            <input type="date" id="data_nascimento" name="data_nascimento" class="form-control" value="<?php echo $data_nascimento; ?>" required>
                        </div>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label for="telefone" class="required-field">Telefone</label>
                        <div class="input-group">
                            <i class="fas fa-phone"></i>
                            <input type="text" id="telefone" name="telefone" class="form-control" placeholder="(00) 00000-0000" value="<?php echo $telefone; ?>" required>
                        </div>
                    </div>
                </div>
            </div>

            <h4 class="form-title">Dados de Contato e Acesso</h4>
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label for="email" class="required-field">E-mail</label>
                        <div class="input-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo $email; ?>" required>
                        </div>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label for="confirma_email" class="required-field">Confirme o E-mail</label>
                        <div class="input-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="confirma_email" name="confirma_email" class="form-control" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label for="senha" class="required-field">Senha</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="senha" name="senha" class="form-control" required>
                            <button type="button" id="toggleSenha" class="password-toggle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <small>Mínimo de 8 caracteres, com pelo menos uma letra e um número</small>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label for="confirma_senha" class="required-field">Confirme a Senha</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="confirma_senha" name="confirma_senha" class="form-control" required>
                            <button type="button" id="toggleConfirmaSenha" class="password-toggle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <h4 class="form-title">Endereço</h4>
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label for="cep" class="required-field">CEP</label>
                        <div class="input-group">
                            <i class="fas fa-map-marker-alt"></i>
                            <input type="text" id="cep" name="cep" class="form-control" placeholder="00000-000" value="<?php echo $cep; ?>" required>
                        </div>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label for="endereco" class="required-field">Endereço</label>
                        <div class="input-group">
                            <i class="fas fa-road"></i>
                            <input style="text-transform: uppercase" type="text" id="endereco" name="endereco" class="form-control" value="<?php echo $endereco; ?>" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col" style="flex: 0 0 30%;">
                    <div class="form-group">
                        <label for="numero" class="required-field">Número</label>
                        <div class="input-group">
                            <i class="fas fa-home"></i>
                            <input type="text" id="numero" name="numero" class="form-control" value="<?php echo $numero; ?>" required>
                        </div>
                    </div>
                </div>
                <div class="form-col" style="flex: 0 0 70%;">
                    <div class="form-group">
                        <label for="complemento">Complemento</label>
                        <div class="input-group">
                            <i class="fas fa-info-circle"></i>
                            <input type="text" id="complemento" name="complemento" class="form-control" placeholder="Apto, bloco, etc. (opcional)">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label for="bairro" class="required-field">Bairro</label>
                        <div class="input-group">
                            <i class="fas fa-map"></i>
                            <input style="text-transform: uppercase" type="text" id="bairro" name="bairro" class="form-control" value="<?php echo $bairro; ?>" required>
                        </div>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label for="cidade" class="required-field">Cidade</label>
                        <div class="input-group">
                            <i class="fas fa-city"></i>
                            <input style="text-transform: uppercase" type="text" id="cidade" name="cidade" class="form-control" value="<?php echo $cidade; ?>" required>
                        </div>
                    </div>
                </div>
                <div class="form-col" style="flex: 0 0 20%;">
                    <div class="form-group">
                        <label for="uf" class="required-field">UF</label>
                        <div class="input-group">
                            <i class="fas fa-map-pin"></i>
                            <select id="uf" name="uf" class="form-control" required>
                                <option value="AC" <?php echo ($uf == 'AC') ? 'selected' : ''; ?>>AC</option>
                                <option value="AL" <?php echo ($uf == 'AL') ? 'selected' : ''; ?>>AL</option>
                                <option value="AM" <?php echo ($uf == 'AM') ? 'selected' : ''; ?>>AM</option>
                                <option value="AP" <?php echo ($uf == 'AP') ? 'selected' : ''; ?>>AP</option>
                                <option value="BA" <?php echo ($uf == 'BA') ? 'selected' : ''; ?>>BA</option>
                                <option value="CE" <?php echo ($uf == 'CE') ? 'selected' : ''; ?>>CE</option>
                                <option value="DF" <?php echo ($uf == 'DF') ? 'selected' : ''; ?>>DF</option>
                                <option value="ES" <?php echo ($uf == 'ES') ? 'selected' : ''; ?>>ES</option>
                                <option value="GO" <?php echo ($uf == 'GO') ? 'selected' : ''; ?>>GO</option>
                                <option value="MA" <?php echo ($uf == 'MA') ? 'selected' : ''; ?>>MA</option>
                                <option value="MG" <?php echo ($uf == 'MG') ? 'selected' : ''; ?>>MG</option>
                                <option value="MS" <?php echo ($uf == 'MS') ? 'selected' : ''; ?>>MS</option>
                                <option value="MT" <?php echo ($uf == 'MT') ? 'selected' : ''; ?>>MT</option>
                                <option value="PA" <?php echo ($uf == 'PA') ? 'selected' : ''; ?>>PA</option>
                                <option value="PB" <?php echo ($uf == 'PB') ? 'selected' : ''; ?>>PB</option>
                                <option value="PE" <?php echo ($uf == 'PE') ? 'selected' : ''; ?>>PE</option>
                                <option value="PI" <?php echo ($uf == 'PI') ? 'selected' : ''; ?>>PI</option>
                                <option value="PR" <?php echo ($uf == 'PR') ? 'selected' : ''; ?>>PR</option>
                                <option value="RJ" <?php echo ($uf == 'RJ') ? 'selected' : ''; ?>>RJ</option>
                                <option value="RN" <?php echo ($uf == 'RN') ? 'selected' : ''; ?>>RN</option>
                                <option value="RO" <?php echo ($uf == 'RO') ? 'selected' : ''; ?>>RO</option>
                                <option value="RR" <?php echo ($uf == 'RR') ? 'selected' : ''; ?>>RR</option>
                                <option value="RS" <?php echo ($uf == 'RS') ? 'selected' : ''; ?>>RS</option>
                                <option value="SC" <?php echo ($uf == 'SC') ? 'selected' : ''; ?>>SC</option>
                                <option value="SE" <?php echo ($uf == 'SE') ? 'selected' : ''; ?>>SE</option>
                                <option value="SP" <?php echo ($uf == 'SP') ? 'selected' : ''; ?>>SP</option>
                                <option value="TO" <?php echo ($uf == 'TO') ? 'selected' : ''; ?>>TO</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <h4 class="form-title">Termos e Condições</h4>
            
            <div class="terms-container">
                <p><strong>Termos de Uso e Política de Privacidade</strong></p>
                <p>Ao se cadastrar no sistema "Eai Cidadão!", você concorda com os seguintes termos:</p>
                <p>1. Seus dados serão armazenados de forma segura e utilizados exclusivamente para fins de prestação de serviços públicos do Município de Santa Izabel do Oeste.</p>
                <p>2. Suas informações poderão ser compartilhadas apenas entre os setores da Administração Municipal e quando necessário para atender suas solicitações.</p>
                <p>3. Você é responsável por manter sua senha em sigilo e por todas as atividades realizadas com sua conta.</p>
                <p>4. O sistema poderá enviar notificações sobre o andamento dos seus processos via e-mail e/ou SMS.</p>
                <p>5. O Município de Santa Izabel do Oeste se compromete a seguir a Lei Geral de Proteção de Dados (LGPD) no tratamento dos seus dados pessoais.</p>
                <p>6. Você pode solicitar a exclusão dos seus dados a qualquer momento, exceto quando existirem solicitações em andamento ou quando o armazenamento for exigido por lei.</p>
            </div>

            <div class="checkbox-container">
                <input type="checkbox" id="termos" name="termos" required>
                <label for="termos">Eu li e concordo com os termos de uso e política de privacidade</label>
            </div>

            <div class="checkbox-container">
                <input type="checkbox" id="newsletter" name="newsletter">
                <label for="newsletter">Desejo receber notificações sobre novos serviços e informações do município</label>
            </div>

            <button type="submit" class="btn-cadastrar">Cadastrar</button>

            <div style="text-align: center; margin-top: 20px;">
                Já tem uma conta? <a href="../login_cidadao.php" style="color: #2e7d32; text-decoration: none; font-weight: bold;">Faça login</a>
            </div>
        </form>
    </div>

    <!-- JavaScript para funcionalidade de mostrar/ocultar senha e validação do formulário -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Função para alternar visibilidade da senha
        function setupPasswordToggle(toggleId, passwordId) {
            const toggle = document.getElementById(toggleId);
            const password = document.getElementById(passwordId);
            
            toggle.addEventListener('click', function() {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                
                // Alterna o ícone
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        }
        
        // Setup para ambos os campos de senha
        setupPasswordToggle('toggleSenha', 'senha');
        setupPasswordToggle('toggleConfirmaSenha', 'confirma_senha');
        
        // Máscara para CPF
        const cpfInput = document.getElementById('cpf');
        cpfInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) {
                value = value.slice(0, 11);
            }
            
            if (value.length > 9) {
                value = value.replace(/^(\d{3})(\d{3})(\d{3})(\d{2}).*/, '$1.$2.$3-$4');
            } else if (value.length > 6) {
                value = value.replace(/^(\d{3})(\d{3})(\d{0,3}).*/, '$1.$2.$3');
            } else if (value.length > 3) {
                value = value.replace(/^(\d{3})(\d{0,3}).*/, '$1.$2');
            }
            
            e.target.value = value;
        });
        
        // Máscara para telefone
        const telefoneInput = document.getElementById('telefone');
        telefoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) {
                value = value.slice(0, 11);
            }
            
            if (value.length > 10) {
                value = value.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
            } else if (value.length > 6) {
                value = value.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
            } else if (value.length > 2) {
                value = value.replace(/^(\d{2})(\d{0,5}).*/, '($1) $2');
            }
            
            e.target.value = value;
        });
        
        // Máscara para CEP
        const cepInput = document.getElementById('cep');
        cepInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 8) {
                value = value.slice(0, 8);
            }
            
            if (value.length > 5) {
                value = value.replace(/^(\d{5})(\d{0,3}).*/, '$1-$2');
            }
            
            e.target.value = value;
        });
        
        // Busca de endereço por CEP
        cepInput.addEventListener('blur', function() {
            const cep = this.value.replace(/\D/g, '');
            
            if (cep.length !== 8) {
                return;
            }
            
            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(response => response.json())
                .then(data => {
                    if (!data.erro) {
                        document.getElementById('endereco').value = data.logradouro;
                        document.getElementById('bairro').value = data.bairro;
                        document.getElementById('cidade').value = data.localidade;
                        document.getElementById('uf').value = data.uf;
                    }
                })
                .catch(error => console.error('Erro ao buscar CEP:', error));
        });
        
        // Validação do formulário antes de enviar
        document.querySelector('.cadastro-form').addEventListener('submit', function(e) {
            const senha = document.getElementById('senha').value;
            const confirmaSenha = document.getElementById('confirma_senha').value;
            const email = document.getElementById('email').value;
            const confirmaEmail = document.getElementById('confirma_email').value;
            
            // Validação de senha
            if (senha.length < 8) {
                alert('A senha deve ter pelo menos 8 caracteres.');
                e.preventDefault();
                return;
            }
            
            if (!/[a-zA-Z]/.test(senha) || !/[0-9]/.test(senha)) {
                alert('A senha deve conter pelo menos uma letra e um número.');
                e.preventDefault();
                return;
            }
            
            // Confirmação de senha
            if (senha !== confirmaSenha) {
                alert('As senhas não coincidem.');
                e.preventDefault();
                return;
            }
            
            // Confirmação de e-mail
            if (email !== confirmaEmail) {
                alert('Os e-mails não coincidem.');
                e.preventDefault();
                return;
            }
        });
    });
    </script>
</body>

</html>