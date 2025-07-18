<?php
// Inicia a sessão
session_start();

// Verifica se há mensagem de erro
$mensagem_erro = "";
if (isset($_SESSION['erro_cadastro'])) {
    $mensagem_erro = $_SESSION['erro_cadastro'];
    unset($_SESSION['erro_cadastro']); // Remove a mensagem após exibi-la
}

// Verifica se há mensagem de sucesso
$mensagem_sucesso = "";
if (isset($_SESSION['mensagem_sucesso'])) {
    $mensagem_sucesso = $_SESSION['mensagem_sucesso'];
    unset($_SESSION['mensagem_sucesso']); // Remove a mensagem após exibi-la
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
    <link rel="stylesheet" type="text/css" href="./css/style.css">
    <link rel="stylesheet" type="text/css" href="./css/login-cidadao.css">
    <link rel="stylesheet" type="text/css" href="./css/cad-cidadao.css">
    <link rel="stylesheet" type="text/css" href="./css/cadastro-cidadao-extras.css">
    <link rel="stylesheet" type="text/css" href="./css/foto-upload.css">
</head>

<body>
    <a href="login_cidadao.php" class="back-link">
        <i class="fas fa-arrow-left"></i>
        Voltar para login
    </a>

    <div class="container">
        <div class="header-container">
            <div class="municipality-logo">
                <img src="./img/logo_municipio.png" alt="Logo do Município">
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

        <?php if (!empty($mensagem_sucesso)): ?>
        <div class="alert-success">
            <?php echo $mensagem_sucesso; ?>
        </div>
        <?php endif; ?>

        <form class="cadastro-form" action="./controller/processar_cadastro_usuario.php" method="post" enctype="multipart/form-data">
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

            <!-- Nova seção para foto do usuário -->
            <h4 class="form-title">Foto do Usuário</h4>
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label for="foto_usuario" class="required-field">Foto/Selfie</label>
                        <div class="photo-upload-container">
                            <div class="photo-preview" id="photo-preview">
                                <i class="fas fa-camera"></i>
                                <span>Clique para adicionar sua foto</span>
                            </div>
                            <input type="file" id="foto_usuario" name="foto_usuario" accept="image/jpeg,image/jpg,image/png" required style="display: none;">
                            <div class="photo-controls">
                                <button type="button" id="btn-camera" class="btn-photo">
                                    <i class="fas fa-camera"></i> Tirar Foto
                                </button>
                                <button type="button" id="btn-galeria" class="btn-photo">
                                    <i class="fas fa-image"></i> Escolher da Galeria
                                </button>
                                <button type="button" id="btn-remover" class="btn-photo btn-remove" style="display: none;">
                                    <i class="fas fa-trash"></i> Remover
                                </button>
                            </div>
                        </div>
                        <small class="form-text">
                            Formatos aceitos: JPG, JPEG, PNG. Tamanho máximo: 5MB.<br>
                            A foto deve mostrar claramente seu rosto para identificação.
                        </small>
                    </div>
                </div>
            </div>

            <!-- Modal para captura de foto via webcam -->
            <div id="camera-modal" class="camera-modal" style="display: none;">
                <div class="camera-modal-content">
                    <div class="camera-header">
                        <h3>Tirar Foto</h3>
                        <button type="button" id="close-camera" class="close-btn">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="camera-body">
                        <video id="camera-video" autoplay playsinline></video>
                        <canvas id="camera-canvas" style="display: none;"></canvas>
                        <div class="camera-controls">
                            <button type="button" id="capture-photo" class="btn-capture">
                                <i class="fas fa-camera"></i> Capturar
                            </button>
                            <button type="button" id="retake-photo" class="btn-retake" style="display: none;">
                                <i class="fas fa-redo"></i> Tirar Novamente
                            </button>
                            <button type="button" id="confirm-photo" class="btn-confirm" style="display: none;">
                                <i class="fas fa-check"></i> Confirmar
                            </button>
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
                Já tem uma conta? <a href="login_cidadao.php" style="color: #2e7d32; text-decoration: none; font-weight: bold;">Faça login</a>
            </div>
        </form>
    </div>

    <!-- Carrega os arquivos JavaScript -->
    <script src="./js/cadastro-cidadao.js"></script>
    <script src="./js/foto-upload.js"></script>
</body>

</html>