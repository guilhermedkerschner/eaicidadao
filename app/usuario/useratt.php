<?php
// Inicia a sessão
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['user_logado']) || $_SESSION['user_logado'] !== true) {
    // Se não estiver logado, redireciona para a página de login
    header("Location: ../../login_cidadao.php");
    exit();
}

// Obtém informações do usuário da sessão
$usuario_id = $_SESSION['user_id'];
$nome_usuario = $_SESSION['user_nome'] ?? '';
$cpf_usuario = $_SESSION['user_cpf'] ?? '';
$email_usuario = $_SESSION['user_email'] ?? '';
$contato_usuario = $_SESSION['user_contato'] ?? '';
$data_nasc_usuario = $_SESSION['user_data_nasc'] ?? '';
$endereco_usuario = $_SESSION['user_endereco'] ?? '';
$numero_usuario = $_SESSION['user_numero'] ?? '';
$bairro_usuario = $_SESSION['user_bairro'] ?? '';
$cidade_usuario = $_SESSION['user_cidade'] ?? '';
$cep_usuario = $_SESSION['user_cep'] ?? '';
$complemento_usuario = $_SESSION['user_complemento'] ?? '';
$uf_usuario = $_SESSION['user_estado'] ?? 'PR'; // Definir PR como padrão se não existir

// Formatar data de nascimento para exibição (se necessário)
$data_nasc_formatada = !empty($data_nasc_usuario) ? date('d/m/Y', strtotime($data_nasc_usuario)) : '';

// Mensagens de feedback
$mensagem = "";
$tipo_mensagem = "";

if (isset($_SESSION['perfil_msg']) && isset($_SESSION['perfil_tipo'])) {
    $mensagem = $_SESSION['perfil_msg'];
    $tipo_mensagem = $_SESSION['perfil_tipo'];
    // Limpa as mensagens da sessão após exibi-las
    unset($_SESSION['perfil_msg']);
    unset($_SESSION['perfil_tipo']);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - Eai Cidadão!</title>
    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- CSS personalizado -->
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .container {
            max-width: 800px;
            padding: 30px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            color: #2e7d32;
            text-decoration: none;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .back-link i {
            margin-right: 8px;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .form-section {
            background-color: #fff;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .form-section h3 {
            color: #2e7d32;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            align-items: center;
        }

        .form-section h3 i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px 20px;
        }

        .form-group {
            flex: 1 0 200px;
            margin: 0 10px 15px;
        }

        .form-group.full-width {
            flex: 0 0 calc(100% - 20px);
        }

        .form-group.half-width {
            flex: 0 0 calc(50% - 20px);
        }

        .form-group.third-width {
            flex: 0 0 calc(33.333% - 20px);
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .form-group .disabled-field {
            background-color: #f5f5f5;
            color: #666;
            cursor: not-allowed;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: #2e7d32;
            outline: none;
            box-shadow: 0 0 0 2px rgba(46, 125, 50, 0.2);
        }

        .btn-primary {
            background-color: #2e7d32;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background-color: #1b5e20;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .password-feedback {
            font-size: 0.9rem;
            margin-top: 5px;
        }

        .password-strength {
            margin-top: 8px;
            height: 6px;
            border-radius: 5px;
            background-color: #e0e0e0;
        }

        .password-strength div {
            height: 100%;
            border-radius: 5px;
            transition: width 0.3s;
        }

        .weak {
            background-color: #f44336;
            width: 30%;
        }

        .medium {
            background-color: #ffc107;
            width: 60%;
        }

        .strong {
            background-color: #4caf50;
            width: 100%;
        }

        .password-tips {
            font-size: 0.85rem;
            color: #666;
            margin-top: 8px;
        }

        .form-text {
            font-size: 0.85rem;
            color: #666;
            margin-top: 5px;
        }

        .form-footer {
            display: flex;
            justify-content: flex-end;
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .page-title {
            color: #2e7d32;
            font-size: 1.8rem;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
        }

        .page-title i {
            margin-right: 10px;
        }

        .uppercase-input {
            text-transform: uppercase;
        }

        @media (max-width: 768px) {
            .form-group.half-width,
            .form-group.third-width {
                flex: 0 0 calc(100% - 20px);
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header-container">
            <div class="municipality-logo">
                <img src="../../img/logo_municipio.png" alt="Logo do Município">
            </div>
            <div class="title-container">
                <h1>Eai Cidadão!</h1>
                <h2 class="municipality-name">Município de Santa Izabel do Oeste</h2>
            </div>
        </div>

        <div class="divider"></div>

        <a href="perfil.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Voltar para Meu Perfil
        </a>

        <?php if (!empty($mensagem)): ?>
            <div class="alert alert-<?php echo $tipo_mensagem === 'success' ? 'success' : 'danger'; ?>">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <h2 class="page-title"><i class="fas fa-user-edit"></i> Editar Perfil</h2>

        <!-- Formulário para atualizar informações pessoais -->
        <form id="form-dados-pessoais" action="../../controller/atualizar_perfil.php" method="post">
            <input type="hidden" name="form_type" value="dados_pessoais">
            <input type="hidden" name="nome" value="<?php echo htmlspecialchars($nome_usuario); ?>">
            
            <div class="form-section">
                <h3><i class="fas fa-user"></i> Dados Pessoais</h3>
                
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="nome_display">Nome Completo</label>
                        <input type="text" class="form-control disabled-field" id="nome_display" value="<?php echo htmlspecialchars($nome_usuario); ?>" readonly>
                        <div class="form-text">O nome completo não pode ser alterado.</div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group half-width">
                        <label for="cpf">CPF</label>
                        <input type="text" class="form-control disabled-field" id="cpf" value="<?php echo htmlspecialchars($cpf_usuario); ?>" readonly>
                        <div class="form-text">O CPF não pode ser alterado.</div>
                    </div>
                    
                    <div class="form-group half-width">
                        <label for="data_nascimento">Data de Nascimento</label>
                        <input type="text" class="form-control disabled-field" id="data_nascimento" value="<?php echo htmlspecialchars($data_nasc_formatada); ?>" readonly>
                        <div class="form-text">A data de nascimento não pode ser alterada.</div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group half-width">
                        <label for="email">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email_usuario); ?>" required>
                    </div>
                    
                    <div class="form-group half-width">
                        <label for="contato">Telefone/Celular</label>
                        <input type="text" class="form-control" id="contato" name="contato" value="<?php echo htmlspecialchars($contato_usuario); ?>" required>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h3><i class="fas fa-home"></i> Endereço</h3>
                
                <div class="form-row">
                    <div class="form-group third-width">
                        <label for="cep">CEP</label>
                        <input type="text" class="form-control" id="cep" name="cep" value="<?php echo htmlspecialchars($cep_usuario); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group" style="flex: 0 0 calc(70% - 20px);">
                        <label for="endereco">Rua</label>
                        <input type="text" class="form-control uppercase-input" id="endereco" name="endereco" value="<?php echo htmlspecialchars($endereco_usuario); ?>" required>
                    </div>
                    
                    <div class="form-group" style="flex: 0 0 calc(30% - 20px);">
                        <label for="numero">Número</label>
                        <input type="text" class="form-control" id="numero" name="numero" value="<?php echo htmlspecialchars($numero_usuario); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group half-width">
                        <label for="complemento">Complemento</label>
                        <input type="text" class="form-control uppercase-input" id="complemento" name="complemento" value="<?php echo htmlspecialchars($complemento_usuario); ?>">
                    </div>
                    
                    <div class="form-group half-width">
                        <label for="bairro">Bairro</label>
                        <input type="text" class="form-control uppercase-input" id="bairro" name="bairro" value="<?php echo htmlspecialchars($bairro_usuario); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group" style="flex: 0 0 calc(70% - 20px);">
                        <label for="cidade">Cidade</label>
                        <input type="text" class="form-control uppercase-input" id="cidade" name="cidade" value="<?php echo htmlspecialchars($cidade_usuario); ?>" required>
                    </div>
                    
                    <div class="form-group" style="flex: 0 0 calc(30% - 20px);">
                        <label for="uf">Estado</label>
                        <select class="form-control" id="uf" name="uf" required>
                            <option value="AC" <?php echo ($uf_usuario == 'AC') ? 'selected' : ''; ?>>AC</option>
                            <option value="AL" <?php echo ($uf_usuario == 'AL') ? 'selected' : ''; ?>>AL</option>
                            <option value="AP" <?php echo ($uf_usuario == 'AP') ? 'selected' : ''; ?>>AP</option>
                            <option value="AM" <?php echo ($uf_usuario == 'AM') ? 'selected' : ''; ?>>AM</option>
                            <option value="BA" <?php echo ($uf_usuario == 'BA') ? 'selected' : ''; ?>>BA</option>
                            <option value="CE" <?php echo ($uf_usuario == 'CE') ? 'selected' : ''; ?>>CE</option>
                            <option value="DF" <?php echo ($uf_usuario == 'DF') ? 'selected' : ''; ?>>DF</option>
                            <option value="ES" <?php echo ($uf_usuario == 'ES') ? 'selected' : ''; ?>>ES</option>
                            <option value="GO" <?php echo ($uf_usuario == 'GO') ? 'selected' : ''; ?>>GO</option>
                            <option value="MA" <?php echo ($uf_usuario == 'MA') ? 'selected' : ''; ?>>MA</option>
                            <option value="MT" <?php echo ($uf_usuario == 'MT') ? 'selected' : ''; ?>>MT</option>
                            <option value="MS" <?php echo ($uf_usuario == 'MS') ? 'selected' : ''; ?>>MS</option>
                            <option value="MG" <?php echo ($uf_usuario == 'MG') ? 'selected' : ''; ?>>MG</option>
                            <option value="PA" <?php echo ($uf_usuario == 'PA') ? 'selected' : ''; ?>>PA</option>
                            <option value="PB" <?php echo ($uf_usuario == 'PB') ? 'selected' : ''; ?>>PB</option>
                            <option value="PR" <?php echo ($uf_usuario == 'PR') ? 'selected' : ''; ?>>PR</option>
                            <option value="PE" <?php echo ($uf_usuario == 'PE') ? 'selected' : ''; ?>>PE</option>
                            <option value="PI" <?php echo ($uf_usuario == 'PI') ? 'selected' : ''; ?>>PI</option>
                            <option value="RJ" <?php echo ($uf_usuario == 'RJ') ? 'selected' : ''; ?>>RJ</option>
                            <option value="RN" <?php echo ($uf_usuario == 'RN') ? 'selected' : ''; ?>>RN</option>
                            <option value="RS" <?php echo ($uf_usuario == 'RS') ? 'selected' : ''; ?>>RS</option>
                            <option value="RO" <?php echo ($uf_usuario == 'RO') ? 'selected' : ''; ?>>RO</option>
                            <option value="RR" <?php echo ($uf_usuario == 'RR') ? 'selected' : ''; ?>>RR</option>
                            <option value="SC" <?php echo ($uf_usuario == 'SC') ? 'selected' : ''; ?>>SC</option>
                            <option value="SP" <?php echo ($uf_usuario == 'SP') ? 'selected' : ''; ?>>SP</option>
                            <option value="SE" <?php echo ($uf_usuario == 'SE') ? 'selected' : ''; ?>>SE</option>
                            <option value="TO" <?php echo ($uf_usuario == 'TO') ? 'selected' : ''; ?>>TO</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-footer">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Salvar Alterações
                    </button>
                </div>
            </div>
        </form>
        
        <!-- Formulário para alterar senha -->
        <form id="form-alterar-senha" action="../../controller/atualizar_perfil.php" method="post">
            <input type="hidden" name="form_type" value="alterar_senha">
            
            <div class="form-section">
                <h3><i class="fas fa-lock"></i> Alterar Senha</h3>
                
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="senha_atual">Senha Atual</label>
                        <input type="password" class="form-control" id="senha_atual" name="senha_atual" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group half-width">
                        <label for="nova_senha">Nova Senha</label>
                        <input type="password" class="form-control" id="nova_senha" name="nova_senha" required>
                        <div class="password-strength">
                            <div id="strength-bar"></div>
                        </div>
                        <div id="password-feedback" class="password-feedback"></div>
                        <div class="password-tips">
                            A senha deve ter pelo menos 8 caracteres, contendo letras e números.
                        </div>
                    </div>
                    
                    <div class="form-group half-width">
                        <label for="confirma_senha">Confirmar Nova Senha</label>
                        <input type="password" class="form-control" id="confirma_senha" name="confirma_senha" required>
                        <div id="password-match" class="password-feedback"></div>
                    </div>
                </div>
                
                <div class="form-footer">
                    <button type="submit" class="btn-primary" id="btn-alterar-senha">
                        <i class="fas fa-key"></i> Alterar Senha
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Configurar inputs para ficar em maiúsculo
            const uppercaseInputs = document.querySelectorAll('.uppercase-input');
            uppercaseInputs.forEach(input => {
                // Converter valor existente para maiúsculo
                input.value = input.value.toUpperCase();
                
                // Adicionar evento para manter em maiúsculo durante digitação
                input.addEventListener('input', function() {
                    this.value = this.value.toUpperCase();
                });
            });
            
            // Função para formatar o telefone
            const formataTelefone = (telefone) => {
                telefone = telefone.replace(/\D/g, '');
                if (telefone.length === 11) {
                    return telefone.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
                } else if (telefone.length === 10) {
                    return telefone.replace(/^(\d{2})(\d{4})(\d{4})$/, '($1) $2-$3');
                }
                return telefone;
            };

            // Função para formatar o CEP
            const formataCEP = (cep) => {
                cep = cep.replace(/\D/g, '');
                return cep.replace(/^(\d{5})(\d{3})$/, '$1-$2');
            };

            // Aplicar formatação ao telefone
            const inputTelefone = document.getElementById('contato');
            if (inputTelefone) {
                // Formatar valor existente
                inputTelefone.value = formataTelefone(inputTelefone.value);
                
                // Evento para manter formatação durante digitação
                inputTelefone.addEventListener('input', function() {
                    this.value = formataTelefone(this.value);
                });
            }

            // Aplicar formatação ao CEP
            const inputCEP = document.getElementById('cep');
            if (inputCEP) {
                // Formatar valor existente
                inputCEP.value = formataCEP(inputCEP.value);
                
                // Evento para manter formatação durante digitação
                inputCEP.addEventListener('input', function() {
                    this.value = formataCEP(this.value);
                });
                
                // Evento para buscar endereço ao sair do campo CEP
                inputCEP.addEventListener('blur', function() {
                    const cep = this.value.replace(/\D/g, '');
                    
                    if (cep.length === 8) {
                        fetch(`https://viacep.com.br/ws/${cep}/json/`)
                            .then(response => response.json())
                            .then(data => {
                                if (!data.erro) {
                                    document.getElementById('endereco').value = data.logradouro.toUpperCase();
                                    document.getElementById('bairro').value = data.bairro.toUpperCase();
                                    document.getElementById('cidade').value = data.localidade.toUpperCase();
                                    document.getElementById('uf').value = data.uf;
                                    document.getElementById('numero').focus();
                                }
                            })
                            .catch(error => console.error('Erro ao buscar CEP:', error));
                    }
                });
            }

            // Validação e feedback da força da senha
            const novaSenhaInput = document.getElementById('nova_senha');
            const confirmaSenhaInput = document.getElementById('confirma_senha');
            const strengthBar = document.getElementById('strength-bar');
            const passwordFeedback = document.getElementById('password-feedback');
            const passwordMatch = document.getElementById('password-match');
            const btnAlterarSenha = document.getElementById('btn-alterar-senha');
            
            if (novaSenhaInput && confirmaSenhaInput) {
                // Verificar força da senha
                novaSenhaInput.addEventListener('input', function() {
                    const senha = this.value;
                    let strength = 0;
                    let feedback = '';
                    
                    // Remover classes anteriores
                    strengthBar.className = '';
                    
                    if (senha.length === 0) {
                        passwordFeedback.textContent = '';
                        strengthBar.style.width = '0';
                        return;
                    }
                    
                    // Critérios de força
                    if (senha.length >= 8) strength += 1;
                    if (senha.match(/[a-z]+/)) strength += 1;
                    if (senha.match(/[A-Z]+/)) strength += 1;
                    if (senha.match(/[0-9]+/)) strength += 1;
                    if (senha.match(/[^a-zA-Z0-9]+/)) strength += 1;
                    
                    // Feedback com base na força
                    switch (strength) {
                        case 0:
                        case 1:
                            strengthBar.className = 'weak';
                            feedback = 'Senha fraca';
                            passwordFeedback.style.color = '#f44336';
                            break;
                        case 2:
                        case 3:
                            strengthBar.className = 'medium';
                            feedback = 'Senha média';
                            passwordFeedback.style.color = '#ffc107';
                            break;
                        case 4:
                        case 5:
                            strengthBar.className = 'strong';
                            feedback = 'Senha forte';
                            passwordFeedback.style.color = '#4caf50';
                            break;
                    }
                    
                    passwordFeedback.textContent = feedback;
                    
                    // Verificar correspondência de senhas se ambos os campos tiverem conteúdo
                    if (confirmaSenhaInput.value) {
                        verificarSenhasIguais();
                    }
                });
                
                // Verificar se senhas são iguais
                function verificarSenhasIguais() {
                    if (novaSenhaInput.value === confirmaSenhaInput.value) {
                        passwordMatch.textContent = 'Senhas correspondem';
                        passwordMatch.style.color = '#4caf50';
                        btnAlterarSenha.disabled = false;
                    } else {
                        passwordMatch.textContent = 'Senhas não correspondem';
                        passwordMatch.style.color = '#f44336';
                        btnAlterarSenha.disabled = true;
                    }
                }
                
                confirmaSenhaInput.addEventListener('input', verificarSenhasIguais);
            }
            
            // Validação do formulário antes de enviar
            const formDadosPessoais = document.getElementById('form-dados-pessoais');
            if (formDadosPessoais) {
                formDadosPessoais.addEventListener('submit', function(e) {
                    const email = document.getElementById('email').value;
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    
                    if (!emailRegex.test(email)) {
                        e.preventDefault();
                        alert('Por favor, informe um endereço de e-mail válido.');
                        return false;
                    }
                    
                    return true;
                });
            }
            
            // Validação do formulário de senha antes de enviar
            const formAlterarSenha = document.getElementById('form-alterar-senha');
            if (formAlterarSenha) {
                formAlterarSenha.addEventListener('submit', function(e) {
                    const novaSenha = novaSenhaInput.value;
                    const confirmaSenha = confirmaSenhaInput.value;
                    
                    // Verificar se a nova senha atende aos requisitos mínimos
                    if (novaSenha.length < 8) {
                        e.preventDefault();
                        alert('A nova senha deve ter pelo menos 8 caracteres.');
                        return false;
                    }
                    
                    if (!novaSenha.match(/[a-zA-Z]/) || !novaSenha.match(/[0-9]/)) {
                        e.preventDefault();
                        alert('A nova senha deve conter pelo menos uma letra e um número.');
                        return false;
                    }
                    
                    // Verificar se as senhas correspondem
                    if (novaSenha !== confirmaSenha) {
                        e.preventDefault();
                        alert('As senhas não correspondem.');
                        return false;
                    }
                    
                    return true;
                });
            }
        });
    </script>
</body>

</html>