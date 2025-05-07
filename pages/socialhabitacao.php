<?php
// Inicia a sessão
session_start();

if (!isset($_SESSION['user_logado'])) {
    header("Location: ../acessdenied.php"); 
  }

// Campos do usuário a serem usados no formulário
$nome = isset($_SESSION['user_nome']) ? $_SESSION['user_nome']:'';
$cpf = isset($_SESSION['user_cpf']) ? $_SESSION['user_cpf']:'';
$email = isset($_SESSION['user_email']) ? $_SESSION['user_email']:'';
$celular = isset($_SESSION['user_contato']) ? $_SESSION['user_contato']:'';
$endereco = isset($_SESSION['user_endereco']) ? $_SESSION['user_endereco']:'';
$numero = isset($_SESSION['user_numero']) ? $_SESSION['user_numero']:'';
$complemento = isset($_SESSION['user_complemento']) ? $_SESSION['user_complemento']:'';
$bairro = isset($_SESSION['user_bairro']) ? $_SESSION['user_bairro']:'';
$cidade = isset($_SESSION['user_cidade']) ? $_SESSION['user_cidade']:'';
$cep = isset($_SESSION['user_cep']) ? $_SESSION['user_cep']:'';
$data_nascimento = isset($_SESSION['user_data_nasc']) ? $_SESSION['user_data_nasc']:'';
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro Habitacional - Eai Cidadão!</title>
    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="../css/socialhabitacao.css">
    <link rel="stylesheet" type="text/css" href="../css/ajax_style.css">
    <style>
        /* Estilos específicos para o formulário em etapas */
        .step-content {
            display: none;
        }
        
        .step-content.active {
            display: block;
            animation: fadeEffect 0.5s;
        }
        
        @keyframes fadeEffect {
            from {opacity: 0;}
            to {opacity: 1;}
        }
        
        .step-nav {
            display: flex;
            justify-content: space-between;
            list-style-type: none;
            padding: 0;
            margin: 0 0 30px 0;
            position: relative;
        }
        
        .step-nav::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 4px;
            background: #e0e0e0;
            z-index: 1;
        }
        
        .step-nav li {
            position: relative;
            z-index: 2;
            text-align: center;
            flex: 1;
        }
        
        .step-circle {
            display: flex;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #e0e0e0;
            color: #555;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin: 0 auto 10px;
            position: relative;
            z-index: 2;
            transition: all 0.3s;
        }
        
        .step-text {
            font-size: 14px;
            color: #555;
            transition: all 0.3s;
        }
        
        .step-nav li.active .step-circle {
            background: #0d47a1;
            color: white;
        }
        
        .step-nav li.active .step-text {
            color: #0d47a1;
            font-weight: 600;
        }
        
        .step-nav li.completed .step-circle {
            background: #4caf50;
            color: white;
        }
        
        .step-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }
        
        .step-actions button {
            min-width: 120px;
        }
        
        .btn-step-prev {
            background-color: #f5f5f5;
            color: #333;
            border: 1px solid #ccc;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-step-prev:hover {
            background-color: #e0e0e0;
        }
        
        .btn-step-next {
            background-color: #0d47a1;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-step-next:hover {
            background-color: #083378;
        }
        
        .btn-step-prev i, .btn-step-next i {
            margin: 0 8px;
        }
        
        .invalid {
            border: 1px solid #e91e63 !important;
            background-color: #fff8f8 !important;
        }
        
        @media (max-width: 768px) {
            .step-text {
                display: none;
            }
            
            .step-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .step-actions button {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div id="loading-overlay" class="loading-overlay">
        <div class="loading-spinner"></div>
        <div id="loading-text" class="loading-text">Processando seu cadastro...</div>
    </div>
    <div class="header">
        <div class="header-left">
            <div class="municipality-logo">
                <!-- Substitua pelo caminho da sua logo -->
                <img src="../img/logo_municipio.png" alt="Logo do Município">
            </div>
            <div class="title-container">
                <h1>Eai Cidadão!</h1>
                <h2 class="municipality-name">Município de Santa Izabel do Oeste</h2>
            </div>
        </div>
        <div class="header-buttons">
            <!-- Botão Voltar para Página de Assistência Social -->
            <a href="social.php" class="back-button">
                <i class="fas fa-arrow-left"></i> 
                Voltar para Assistência Social
            </a>
        </div>
    </div>

    <div class="container">
        <div id="status-message" class="status-message"></div>
        <h2 class="section-title"><i class="fas fa-building"></i> Cadastro para Programas Habitacionais</h2>
        
        <!-- Navegação em etapas -->
        <ul class="step-nav">
            <li class="active" data-step="1">
                <div class="step-circle">1</div>
                <div class="step-text">Responsável Familiar</div>
            </li>
            <li data-step="2">
                <div class="step-circle">2</div>
                <div class="step-text">Composição Familiar</div>
            </li>
            <li data-step="3">
                <div class="step-circle">3</div>
                <div class="step-text">Filiação</div>
            </li>
            <li data-step="4">
                <div class="step-circle">4</div>
                <div class="step-text">Situação Trabalhista</div>
            </li>
            <li data-step="5">
                <div class="step-circle">5</div>
                <div class="step-text">Endereço</div>
            </li>
            <li data-step="6">
                <div class="step-circle">6</div>
                <div class="step-text">Contato</div>
            </li>
            <li data-step="7">
                <div class="step-circle">7</div>
                <div class="step-text">Interesse</div>
            </li>
        </ul>

        <form id="habitacao-form" method="post" action="./controller/processar_habitacao.php" enctype="multipart/form-data">
            
            <!-- STEP 1: INFORMAÇÕES DO RESPONSÁVEL FAMILIAR -->
            <div class="step-content active" id="step-1">
                <div class="form-section">
                    <h3 class="form-section-title">Informações do Responsável Familiar</h3>
                    
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="nome" class="required">Nome completo</label>
                            <input type="text" class="form-control uppercase-input" id="nome" name="nome" value="<?php echo $nome; ?>" required readonly>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group third-width">
                            <label for="cpf" class="required">CPF</label>
                            <div id="cpf-feedback" class="field-feedback"></div>
                            <input type="text" class="form-control" id="cpf" name="cpf" value="<?php echo $cpf; ?>" required maxlength="14" placeholder="000.000.000-00" readonly>
                        </div>
                        <div class="form-group third-width">
                            <label for="cpf_documento" class="required">Anexar documento (CPF)</label>
                            <div class="file-input-container">
                                <input type="file" class="file-input" id="cpf_documento" name="cpf_documento" accept=".pdf,.jpg,.jpeg,.png" required>
                                <div class="upload-progress-container">
                                    <div class="upload-progress-bar"></div>
                                    <div class="upload-progress-text">0%</div>
                                </div>
                            </div>
                            <small class="form-text">Formatos aceitos: PDF, JPG, JPEG, PNG (Max: 5MB)</small>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group third-width">
                            <label for="rg">RG (opcional)</label>
                            <input type="text" class="form-control uppercase-input" id="rg" name="rg">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group third-width">
                            <label for="nacionalidade" class="required">Nacionalidade</label>
                            <input type="text" class="form-control uppercase-input" id="nacionalidade" name="nacionalidade" value="BRASIL" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group third-width">
                            <label for="nome_social_opcao" class="required">Possui nome social?</label>
                            <select class="form-control" id="nome_social_opcao" name="nome_social_opcao" required>
                                <option value="NÃO">NÃO</option>
                                <option value="SIM">SIM</option>
                            </select>
                        </div>
                        <div class="form-group third-width" id="nome_social_campo" style="display: none;">
                            <label for="nome_social">Nome Social</label>
                            <input type="text" class="form-control uppercase-input" id="nome_social" name="nome_social">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group third-width">
                            <label for="genero" class="required">Gênero</label>
                            <select class="form-control" id="genero" name="genero" required>
                                <option value="">Selecione</option>
                                <option value="MASCULINO">MASCULINO</option>
                                <option value="FEMININO">FEMININO</option>
                                <option value="OUTRO">OUTRO</option>
                            </select>
                        </div>
                        <div class="form-group third-width">
                            <label for="data_nascimento" class="required">Data de Nascimento</label>
                            <div id="data_nascimento-feedback" class="field-feedback"></div>
                            <input type="date" class="form-control" id="data_nascimento" name="data_nascimento" value="<?php echo $data_nascimento; ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group third-width">
                            <label for="raca" class="required">Raça</label>
                            <select class="form-control" id="raca" name="raca" required>
                                <option value="">Selecione</option>
                                <option value="BRANCA">BRANCA</option>
                                <option value="PRETA">PRETA</option>
                                <option value="PARDA">PARDA</option>
                                <option value="AMARELA">AMARELA</option>
                                <option value="INDÍGENA">INDÍGENA</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group third-width">
                            <label for="cad_unico">Nº do Cad Único</label>
                            <input type="text" class="form-control" id="cad_unico" name="cad_unico">
                        </div>
                        <div class="form-group third-width">
                            <label for="nis">NIS</label>
                            <input type="text" class="form-control" id="nis" name="nis">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group third-width">
                            <label for="escolaridade" class="required">Escolaridade</label>
                            <select class="form-control" id="escolaridade" name="escolaridade" required>
                                <option value="">Selecione</option>
                                <option value="ANALFABETO">ANALFABETO</option>
                                <option value="ALFABETIZADO">ALFABETIZADO</option>
                                <option value="FUNDAMENTAL INCOMPLETO">FUNDAMENTAL INCOMPLETO</option>
                                <option value="FUNDAMENTAL COMPLETO">FUNDAMENTAL COMPLETO</option>
                                <option value="MÉDIO INCOMPLETO">MÉDIO INCOMPLETO</option>
                                <option value="MÉDIO COMPLETO">MÉDIO COMPLETO</option>
                                <option value="SUPERIOR INCOMPLETO">SUPERIOR INCOMPLETO</option>
                                <option value="SUPERIOR COMPLETO">SUPERIOR COMPLETO</option>
                                <option value="PÓS-GRADUAÇÃO">PÓS-GRADUAÇÃO</option>
                                <option value="MESTRADO">MESTRADO</option>
                                <option value="DOUTORADO">DOUTORADO</option>
                            </select>
                        </div>
                        <div class="form-group third-width">
                            <label for="escolaridade_documento">Anexar comprovante de escolaridade</label>
                            <div class="file-input-container">
                                <input type="file" class="file-input" id="escolaridade_documento" name="escolaridade_documento" accept=".pdf,.jpg,.jpeg,.png">
                                <div class="upload-progress-container">
                                    <div class="upload-progress-bar"></div>
                                    <div class="upload-progress-text">0%</div>
                                </div>
                            </div>
                            <small class="form-text">Formatos aceitos: PDF, JPG, JPEG, PNG (Max: 5MB)</small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group third-width">
                            <label for="estado_civil" class="required">Estado Civil</label>
                            <select class="form-control" id="estado_civil" name="estado_civil" required>
                                <option value="">Selecione</option>
                                <option value="SOLTEIRO(A)">SOLTEIRO(A)</option>
                                <option value="CASADO(A)">CASADO(A)</option>
                                <option value="DIVORCIADO(A)">DIVORCIADO(A)</option>
                                <option value="VIÚVO(A)">VIÚVO(A)</option>
                                <option value="UNIÃO ESTÁVEL/AMASIADO(A)">UNIÃO ESTÁVEL/AMASIADO(A)</option>
                                <option value="SEPARADO(A)">SEPARADO(A)</option>
                            </select>
                        </div>
                        <div class="form-group third-width" id="viuvo_doc_campo" style="display: none;">
                            <label for="viuvo_documento">Anexar certidão de óbito</label>
                            <div class="file-input-container">
                                <input type="file" class="file-input" id="viuvo_documento" name="viuvo_documento" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                            <small class="form-text">Formatos aceitos: PDF, JPG, JPEG, PNG (Max: 5MB)</small>
                        </div>
                    </div>

                    <!-- Campos para cônjuge (aparecem apenas quando União Estável/Casado é selecionado) -->
                    <div id="conjuge_campos" class="dependent-fields">
                        <h4 class="dependent-title"><i class="fas fa-user-friends"></i> Dados do Cônjuge/Companheiro(a)</h4>
                        
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="conjuge_nome" class="required">Nome completo do cônjuge</label>
                                <input type="text" class="form-control uppercase-input" id="conjuge_nome" name="conjuge_nome">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group third-width">
                                <label for="conjuge_cpf" class="required">CPF do cônjuge</label>
                                <input type="text" class="form-control" id="conjuge_cpf" name="conjuge_cpf" maxlength="14" placeholder="000.000.000-00">
                            </div>
                            <div class="form-group third-width">
                                <label for="conjuge_rg">RG do cônjuge</label>
                                <input type="text" class="form-control uppercase-input" id="conjuge_rg" name="conjuge_rg">
                            </div>
                            <div class="form-group third-width">
                                <label for="conjuge_data_nascimento" class="required">Data de Nascimento</label>
                                <input type="date" class="form-control" id="conjuge_data_nascimento" name="conjuge_data_nascimento">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group half-width">
                                <label for="conjuge_renda" class="required">Possui renda?</label>
                                <select class="form-control" id="conjuge_renda" name="conjuge_renda">
                                    <option value="NÃO">NÃO</option>
                                    <option value="SIM">SIM</option>
                                </select>
                            </div>
                            <div class="form-group half-width" id="conjuge_renda_doc" style="display: none;">
                                <label for="conjuge_comprovante_renda">Anexar comprovante de renda</label>
                                <div class="file-input-container">
                                    <input type="file" class="file-input" id="conjuge_comprovante_renda" name="conjuge_comprovante_renda" accept=".pdf,.jpg,.jpeg,.png">
                                </div>
                                <small class="form-text">Formatos aceitos: PDF, JPG, JPEG, PNG (Max: 5MB)</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group half-width">
                            <label for="deficiencia" class="required">Possui deficiência?</label>
                            <select class="form-control" id="deficiencia" name="deficiencia" required>
                                <option value="NÃO">NÃO</option>
                                <option value="AUDITIVA-SURDEZ">AUDITIVA-SURDEZ</option>
                                <option value="AUDITIVA-MUDEZ">AUDITIVA-MUDEZ</option>
                                <option value="CADEIRANTE">CADEIRANTE</option>
                                <option value="FISICA">FISICA</option>
                                <option value="INTELECTUAL">INTELECTUAL</option>
                                <option value="NANISMO">NANISMO</option>
                                <option value="VISUAL">VISUAL</option>
                                <option value="TEA (TRANST. ESPECTRO AUTISTA)">TEA (TRANST. ESPECTRO AUTISTA)</option>
                            </select>
                        </div>
                        <div class="form-group half-width" id="deficiencia_fisica_campo" style="display: none;">
                            <label for="deficiencia_fisica_detalhe" class="required">Especifique a deficiência física</label>
                            <input type="text" class="form-control uppercase-input" id="deficiencia_fisica_detalhe" name="deficiencia_fisica_detalhe">
                        </div>
                    </div>
                    
                    <div class="form-row" id="laudo_deficiencia_campo" style="display: none;">
                        <div class="form-group full-width">
                            <label for="laudo_deficiencia">Anexar laudo médico da deficiência</label>
                            <div class="file-input-container">
                                <input type="file" class="file-input" id="laudo_deficiencia" name="laudo_deficiencia" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                            <small class="form-text">Formatos aceitos: PDF, JPG, JPEG, PNG (Max: 5MB)</small>
                        </div>
                    </div>
                </div>
                
                <div class="step-actions">
                    <div></div> <!-- Espaço em branco para alinhamento -->
                    <button type="button" class="btn-step-next" data-step="2">Próximo <i class="fas fa-arrow-right"></i></button>
                </div>
            </div>

            <!-- STEP 2: COMPOSIÇÃO FAMILIAR -->
            <div class="step-content" id="step-2">
                <div class="form-section">
                    <h3 class="form-section-title">Composição Familiar</h3>
                    
                    <div class="form-row">
                        <div class="form-group half-width">
                            <label for="num_dependentes" class="required">Número de Dependentes</label>
                            <select class="form-control" id="num_dependentes" name="num_dependentes" required>
                                <option value="0">0</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                            </select>
                        </div>
                    </div>

                    <!-- Campos para dependentes (aparecem conforme número selecionado) -->
                    <div id="dependentes_container">
                        <!-- Os campos de dependentes serão adicionados dinamicamente pelo JavaScript -->
                    </div>
                </div>
                
                <div class="step-actions">
                    <button type="button" class="btn-step-prev" data-step="1"><i class="fas fa-arrow-left"></i> Anterior</button>
                    <button type="button" class="btn-step-next" data-step="3">Próximo <i class="fas fa-arrow-right"></i></button>
                </div>
            </div>

            <!-- STEP 3: FILIAÇÃO -->
            <div class="step-content" id="step-3">
                <div class="form-section">
                    <h3 class="form-section-title">Filiação</h3>
                    
                    <div class="form-row">
                        <div class="form-group half-width">
                            <label for="nome_mae" class="required">Nome completo da Mãe</label>
                            <input type="text" class="form-control uppercase-input" id="nome_mae" name="nome_mae" required>
                        </div>
                        <div class="form-group half-width">
                            <label for="nome_pai">Nome completo do Pai</label>
                            <input type="text" class="form-control uppercase-input" id="nome_pai" name="nome_pai">
                        </div>
                    </div>
                </div>
                
                <div class="step-actions">
                    <button type="button" class="btn-step-prev" data-step="2"><i class="fas fa-arrow-left"></i> Anterior</button>
                    <button type="button" class="btn-step-next" data-step="4">Próximo <i class="fas fa-arrow-right"></i></button>
                </div>
            </div>

            <!-- STEP 4: SITUAÇÃO TRABALHISTA -->
            <div class="step-content" id="step-4">
                <div class="form-section">
                    <h3 class="form-section-title">Situação Trabalhista</h3>
                    
                    <div class="form-row">
                        <div class="form-group half-width">
                            <label for="situacao_trabalho" class="required">Situação</label>
                            <select class="form-control" id="situacao_trabalho" name="situacao_trabalho" required>
                                <option value="">Selecione</option>
                                <option value="DESEMPREGADO">DESEMPREGADO</option>
                                <option value="AUTÔNOMO">AUTÔNOMO</option>
                                <option value="EMPREGADO COM CARTEIRA ASSINADA">EMPREGADO COM CARTEIRA ASSINADA</option>
                                <option value="EMPREGADO SEM CARTEIRA ASSINADA">EMPREGADO SEM CARTEIRA ASSINADA</option>
                                <option value="APOSENTADO">APOSENTADO</option>
                                <option value="PENSIONISTA">PENSIONISTA</option>
                                <option value="EMPRESÁRIO">EMPRESÁRIO</option>
                            </select>
                        </div>
                    </div>

                    <!-- Campos para empregados (aparecem apenas quando Empregado é selecionado) -->
                    <div id="emprego_campos" class="dependent-fields">
                        <div class="form-row">
                            <div class="form-group third-width">
                                <label for="profissao" class="required">Profissão</label>
                                <input type="text" class="form-control uppercase-input" id="profissao" name="profissao">
                            </div>
                            <div class="form-group third-width">
                                <label for="empregador" class="required">Empregador</label>
                                <input type="text" class="form-control uppercase-input" id="empregador" name="empregador">
                            </div>
                            <div class="form-group third-width">
                                <label for="cargo" class="required">Cargo</label>
                                <input type="text" class="form-control uppercase-input" id="cargo" name="cargo">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group third-width">
                                <label for="ramo_atividade" class="required">Ramo de atividade</label>
                                <input type="text" class="form-control uppercase-input" id="ramo_atividade" name="ramo_atividade">
                            </div>
                            <div class="form-group third-width">
                                <label for="tempo_servico" class="required">Tempo de Serviço</label>
                                <input type="text" class="form-control" id="tempo_servico" name="tempo_servico" placeholder="Ex: 2 anos e 6 meses">
                            </div>
                            <div class="form-group third-width">
                                <label for="carteira_trabalho" class="required">Anexar carteira de trabalho</label>
                                <div class="file-input-container">
                                    <input type="file" class="file-input" id="carteira_trabalho" name="carteira_trabalho" accept=".pdf,.jpg,.jpeg,.png">
                                    <div class="upload-progress-container">
                                        <div class="upload-progress-bar"></div>
                                        <div class="upload-progress-text">0%</div>
                                    </div>
                                </div>
                                <small class="form-text">Formatos aceitos: PDF, JPG, JPEG, PNG (Max: 5MB)</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="step-actions">
                    <button type="button" class="btn-step-prev" data-step="3"><i class="fas fa-arrow-left"></i> Anterior</button>
                    <button type="button" class="btn-step-next" data-step="5">Próximo <i class="fas fa-arrow-right"></i></button>
                </div>
            </div>

            <!-- STEP 5: ENDEREÇO -->
            <div class="step-content" id="step-5">
                <div class="form-section">
                    <h3 class="form-section-title">Endereço</h3>
                    
                    <div class="form-row">
                        <div class="form-group third-width">
                            <label for="tipo_moradia" class="required">Moradia</label>
                            <select class="form-control" id="tipo_moradia" name="tipo_moradia" required>
                                <option value="">Selecione</option>
                                <option value="CASA">CASA</option>
                                <option value="APARTAMENTO">APARTAMENTO</option>
                                <option value="KITNET">KITNET</option>
                                <option value="CÔMODO">CÔMODO</option>
                                <option value="OUTRO">OUTRO</option>
                            </select>
                        </div>
                        <div class="form-group third-width">
                            <label for="situacao_propriedade" class="required">Situação da propriedade</label>
                            <select class="form-control" id="situacao_propriedade" name="situacao_propriedade" required>
                                <option value="">Selecione</option>
                                <option value="PRÓPRIA COM TITULARIDADE">PRÓPRIA COM TITULARIDADE</option>
                                <option value="PRÓPRIA SEM TITULARIDADE">PRÓPRIA SEM TITULARIDADE</option>
                                <option value="ALUGADA">ALUGADA</option>
                                <option value="CEDIDA">CEDIDA</option>
                                <option value="FINANCIADA">FINANCIADA</option>
                                <option value="OCUPAÇÃO">OCUPAÇÃO</option>
                            </select>
                        </div>
                        <div class="form-group third-width" id="valor_aluguel_campo" style="display: none;">
                            <label for="valor_aluguel" class="required">Valor do Aluguel</label>
                            <input type="text" class="form-control" id="valor_aluguel" name="valor_aluguel" placeholder="R$ 0,00">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group half-width">
                            <label for="rua" class="required">Rua</label>
                            <input type="text" class="form-control uppercase-input" id="rua" name="rua" value="<?php echo $endereco; ?>" required>
                        </div>
                        <div class="form-group quarter-width">
                            <label for="numero" class="required">Número</label>
                            <input type="text" class="form-control" id="numero" name="numero" value="<?php echo $numero; ?>" required>
                        </div>
                        <div class="form-group quarter-width">
                            <label for="complemento">Complemento</label>
                            <input type="text" class="form-control uppercase-input" id="complemento" name="complemento" value="<?php echo $complemento; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group third-width">
                            <label for="bairro" class="required">Bairro</label>
                            <input type="text" class="form-control uppercase-input" id="bairro" name="bairro" value="<?php echo $bairro; ?>" required>
                        </div>
                        <div class="form-group third-width">
                            <label for="cidade" class="required">Cidade</label>
                            <input type="text" class="form-control uppercase-input" id="cidade" name="cidade" value="<?php echo $cidade; ?>" required>
                        </div>
                        <div class="form-group third-width">
                            <label for="cep" class="required">CEP</label>
                            <input type="text" class="form-control" id="cep" name="cep" value="<?php echo $cep; ?>" required maxlength="9" placeholder="00000-000">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="ponto_referencia">Ponto de Referência</label>
                            <input type="text" class="form-control uppercase-input" id="ponto_referencia" name="ponto_referencia">
                        </div>
                    </div>
                </div>
                
                <div class="step-actions">
                    <button type="button" class="btn-step-prev" data-step="4"><i class="fas fa-arrow-left"></i> Anterior</button>
                    <button type="button" class="btn-step-next" data-step="6">Próximo <i class="fas fa-arrow-right"></i></button>
                </div>
            </div>

            <!-- STEP 6: CONTATO -->
            <div class="step-content" id="step-6">
                <div class="form-section">
                    <h3 class="form-section-title">Contato</h3>
                    
                    <div class="form-row">
                        <div class="form-group third-width">
                            <label for="telefone">Telefone</label>
                            <input type="text" class="form-control" id="telefone" name="telefone" value="" placeholder="(00) 00000-0000">
                        </div>
                        <div class="form-group third-width">
                            <label for="celular" class="required">Celular</label>
                            <input type="text" class="form-control" id="celular" name="celular" value="<?php echo $celular; ?>" required placeholder="(00) 00000-0000">
                        </div>
                        <div class="form-group third-width">
                            <label for="email" class="required">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo $email; ?>" required readonly>
                        </div>
                    </div>
                </div>
                
                <div class="step-actions">
                    <button type="button" class="btn-step-prev" data-step="5"><i class="fas fa-arrow-left"></i> Anterior</button>
                    <button type="button" class="btn-step-next" data-step="7">Próximo <i class="fas fa-arrow-right"></i></button>
                </div>
            </div>

            <!-- STEP 7: INTERESSE -->
            <div class="step-content" id="step-7">
                <div class="form-section">
                    <h3 class="form-section-title">Interesse</h3>
                    
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="programa_interesse" class="required">Interesse por</label>
                            <select class="form-control" id="programa_interesse" name="programa_interesse" required>
                                <option value="">Selecione</option>
                                <option value="HABITASIO">HABITASIO</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group full-width">
                            <div style="display: flex; align-items: flex-start; margin-top: 15px;">
                                <input type="checkbox" id="autoriza_email" name="autoriza_email" style="margin-right: 10px; margin-top: 3px;">
                                <label for="autoriza_email">Autorizo receber informações sobre os programas habitacionais por e-mail e por telefone/WhatsApp.</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="step-actions">
                    <button type="button" class="btn-step-prev" data-step="6"><i class="fas fa-arrow-left"></i> Anterior</button>
                    <button type="submit" class="btn-primary" id="submit-button">
                        <i class="fas fa-save"></i> Enviar Cadastro
                    </button>
                </div>
            </div>

            <div class="buttons-container" style="display: none;">
                <button type="button" class="print-button" id="print-button">
                    <i class="fas fa-print"></i> Imprimir Comprovante
                </button>
            </div>
        </form>
    </div>

    <footer>
        &copy; 2025 Prefeitura Municipal de Santa Izabel do Oeste. Todos os direitos reservados.
    </footer>


    <script src="../js/ajax_habitacao.js"></script>
    <script>
        // Script de inicialização adicional
        document.addEventListener('DOMContentLoaded', function() {
            // Verifica se há mensagem de erro ou sucesso na URL e exibe
            const urlParams = new URLSearchParams(window.location.search);
            const statusMsg = urlParams.get('msg');
            const statusType = urlParams.get('type') || 'info';
            
            if (statusMsg) {
                const statusMessage = document.getElementById('status-message');
                if (statusMessage) {
                    statusMessage.textContent = decodeURIComponent(statusMsg);
                    statusMessage.className = `status-message ${statusType}`;
                    statusMessage.style.display = 'block';
                    
                    // Auto-fechar mensagens de sucesso após alguns segundos
                    if (statusType === 'success') {
                        setTimeout(() => {
                            statusMessage.style.display = 'none';
                        }, 5000);
                    }
                }
            }
            
            // Inicialização de tooltips de ajuda
            const helpTooltips = document.querySelectorAll('.form-help-tooltip');
            helpTooltips.forEach(tooltip => {
                tooltip.innerHTML = `
                    <i class="fas fa-question-circle tooltip-icon"></i>
                    <span class="tooltip-text">${tooltip.getAttribute('data-help')}</span>
                `;
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Variáveis para controle do formulário
            const stepContents = document.querySelectorAll('.step-content');
            const stepItems = document.querySelectorAll('.step-nav li');
            const prevButtons = document.querySelectorAll('.btn-step-prev');
            const nextButtons = document.querySelectorAll('.btn-step-next');
            
            // Função para exibir a etapa
            function showStep(stepNumber) {
                // Ocultar todas as etapas
                stepContents.forEach(step => {
                    step.classList.remove('active');
                });
                
                // Mostrar a etapa atual
                document.getElementById('step-' + stepNumber).classList.add('active');
                
                // Atualizar navegação
                stepItems.forEach(item => {
                    const itemStep = item.getAttribute('data-step');
                    item.classList.remove('active');
                    item.classList.remove('completed');
                    
                    if (itemStep == stepNumber) {
                        item.classList.add('active');
                    } else if (itemStep < stepNumber) {
                        item.classList.add('completed');
                    }
                });
                
                // Rolar para o topo
                window.scrollTo(0, 0);
            }
            
            // Função para validar campos da etapa
            function validateStep(stepNumber) {
                const step = document.getElementById('step-' + stepNumber);
                const requiredInputs = step.querySelectorAll('input[required], select[required]');
                let isValid = true;
                
                requiredInputs.forEach(input => {
                    if (!input.value) {
                        isValid = false;
                        input.classList.add('invalid');
                        
                        // Remover classe ao digitar
                        input.addEventListener('input', function() {
                            this.classList.remove('invalid');
                        }, { once: true });
                    } else {
                        input.classList.remove('invalid');
                    }
                });
                
                if (!isValid) {
                    alert('Por favor, preencha todos os campos obrigatórios.');
                }
                
                return isValid;
            }
            
            // Eventos para navegação das etapas
            stepItems.forEach(item => {
                item.addEventListener('click', function() {
                    const clickedStep = parseInt(this.getAttribute('data-step'));
                    const currentStep = parseInt(document.querySelector('.step-nav li.active').getAttribute('data-step'));
                    
                    // Validar etapas anteriores se estiver avançando
                    if (clickedStep > currentStep) {
                        let allValid = true;
                        
                        for (let i = 1; i < clickedStep; i++) {
                            if (!validateStep(i)) {
                                allValid = false;
                                showStep(i);
                                break;
                            }
                        }
                        
                        if (allValid) {
                            showStep(clickedStep);
                        }
                    } else {
                        showStep(clickedStep);
                    }
                });
            });
            
            // Eventos para botões "Anterior"
            prevButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const prevStep = this.getAttribute('data-step');
                    showStep(prevStep);
                });
            });
            
            // Eventos para botões "Próximo"
            nextButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const currentStep = parseInt(document.querySelector('.step-nav li.active').getAttribute('data-step'));
                    const nextStep = this.getAttribute('data-step');
                    
                    if (validateStep(currentStep)) {
                        showStep(nextStep);
                    }
                });
            });
            
            // Evento para envio do formulário
            document.getElementById('habitacao-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Validar a última etapa
                if (validateStep(7)) {
                    // Simular envio bem-sucedido
                    alert('Cadastro realizado com sucesso!');
                    
                    // Mostrar botão de impressão do comprovante
                    document.querySelector('.buttons-container').style.display = 'flex';
                }
            });
            
            // Converter texto para maiúsculas automaticamente
            const uppercaseInputs = document.querySelectorAll('.uppercase-input');
            uppercaseInputs.forEach(input => {
                input.addEventListener('input', function() {
                    this.value = this.value.toUpperCase();
                });
            });

            // Mostrar/esconder campo de nome social
            document.getElementById('nome_social_opcao').addEventListener('change', function() {
                const nomeSocialCampo = document.getElementById('nome_social_campo');
                if (this.value === 'SIM') {
                    nomeSocialCampo.style.display = 'block';
                    document.getElementById('nome_social').required = true;
                } else {
                    nomeSocialCampo.style.display = 'none';
                    document.getElementById('nome_social').required = false;
                }
            });

            // Mostrar/esconder campos de cônjuge conforme o estado civil
            document.getElementById('estado_civil').addEventListener('change', function() {
                const conjugeCampos = document.getElementById('conjuge_campos');
                const viuvoDocCampo = document.getElementById('viuvo_doc_campo');
                
                // Verifica se o estado civil requer informações de cônjuge
                if (this.value === 'UNIÃO ESTÁVEL/AMASIADO(A)' || this.value === 'CASADO(A)') {
                    conjugeCampos.style.display = 'block';
                    document.getElementById('conjuge_nome').required = true;
                    document.getElementById('conjuge_cpf').required = true;
                    document.getElementById('conjuge_data_nascimento').required = true;
                } else {
                    conjugeCampos.style.display = 'none';
                    document.getElementById('conjuge_nome').required = false;
                    document.getElementById('conjuge_cpf').required = false;
                    document.getElementById('conjuge_data_nascimento').required = false;
                }
                
                // Verifica se é viúvo para exigir certidão de óbito
                if (this.value === 'VIÚVO(A)') {
                    viuvoDocCampo.style.display = 'block';
                    document.getElementById('viuvo_documento').required = true;
                } else {
                    viuvoDocCampo.style.display = 'none';
                    document.getElementById('viuvo_documento').required = false;
                }
            });

            // Mostrar/esconder campo de comprovante de renda do cônjuge
            document.getElementById('conjuge_renda').addEventListener('change', function() {
                const conjugeRendaDoc = document.getElementById('conjuge_renda_doc');
                if (this.value === 'SIM') {
                    conjugeRendaDoc.style.display = 'block';
                    document.getElementById('conjuge_comprovante_renda').required = true;
                } else {
                    conjugeRendaDoc.style.display = 'none';
                    document.getElementById('conjuge_comprovante_renda').required = false;
                }
            });

            // Mostrar/esconder campo de detalhamento da deficiência física e laudo médico
            document.getElementById('deficiencia').addEventListener('change', function() {
                const deficienciaFisicaCampo = document.getElementById('deficiencia_fisica_campo');
                const laudoDeficienciaCampo = document.getElementById('laudo_deficiencia_campo');
                
                if (this.value === 'FISICA') {
                    deficienciaFisicaCampo.style.display = 'block';
                    document.getElementById('deficiencia_fisica_detalhe').required = true;
                } else {
                    deficienciaFisicaCampo.style.display = 'none';
                    document.getElementById('deficiencia_fisica_detalhe').required = false;
                }
                
                // Mostrar campo de laudo para qualquer tipo de deficiência selecionada
                if (this.value !== 'NÃO') {
                    laudoDeficienciaCampo.style.display = 'flex';
                } else {
                    laudoDeficienciaCampo.style.display = 'none';
                }
            });

            // Mostrar/esconder campo de valor do aluguel
            document.getElementById('situacao_propriedade').addEventListener('change', function() {
                const valorAluguelCampo = document.getElementById('valor_aluguel_campo');
                if (this.value === 'ALUGADA') {
                    valorAluguelCampo.style.display = 'block';
                    document.getElementById('valor_aluguel').required = true;
                } else {
                    valorAluguelCampo.style.display = 'none';
                    document.getElementById('valor_aluguel').required = false;
                }
            });

            // Mostrar/esconder campos de emprego
            document.getElementById('situacao_trabalho').addEventListener('change', function() {
                const empregoCampos = document.getElementById('emprego_campos');
                const empregado = ['EMPREGADO COM CARTEIRA ASSINADA'].includes(this.value);
                
                if (empregado) {
                    empregoCampos.style.display = 'block';
                    document.getElementById('profissao').required = true;
                    document.getElementById('empregador').required = true;
                    document.getElementById('cargo').required = true;
                    document.getElementById('ramo_atividade').required = true;
                    document.getElementById('tempo_servico').required = true;
                    if (this.value === 'EMPREGADO COM CARTEIRA ASSINADA') {
                        document.getElementById('carteira_trabalho').required = true;
                    }
                } else {
                    empregoCampos.style.display = 'none';
                    document.getElementById('profissao').required = false;
                    document.getElementById('empregador').required = false;
                    document.getElementById('cargo').required = false;
                    document.getElementById('ramo_atividade').required = false;
                    document.getElementById('tempo_servico').required = false;
                    document.getElementById('carteira_trabalho').required = false;
                }
            });

            // Gerenciamento de dependentes
            const numDependentesSelect = document.getElementById('num_dependentes');
            const dependentesContainer = document.getElementById('dependentes_container');
            
            numDependentesSelect.addEventListener('change', function() {
                const numDependentes = parseInt(this.value);
                dependentesContainer.innerHTML = '';
                
                for (let i = 1; i <= numDependentes; i++) {
                    const dependenteDiv = document.createElement('div');
                    dependenteDiv.className = 'dependent-container';
                    dependenteDiv.innerHTML = `
                        <h4 class="dependent-title"><i class="fas fa-user"></i> Dependente ${i}</h4>
                        
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="dependente_nome_${i}" class="required">Nome do Dependente</label>
                                <input type="text" class="form-control uppercase-input" id="dependente_nome_${i}" name="dependente_nome_${i}" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group third-width">
                                <label for="dependente_data_nascimento_${i}" class="required">Data de Nascimento</label>
                                <input type="date" class="form-control" id="dependente_data_nascimento_${i}" name="dependente_data_nascimento_${i}" required>
                            </div>
                            <div class="form-group third-width">
                                <label for="dependente_cpf_${i}">CPF</label>
                                <input type="text" class="form-control" id="dependente_cpf_${i}" name="dependente_cpf_${i}" maxlength="14" placeholder="000.000.000-00">
                            </div>
                            <div class="form-group third-width">
                                <label for="dependente_rg_${i}">RG</label>
                                <input type="text" class="form-control uppercase-input" id="dependente_rg_${i}" name="dependente_rg_${i}">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group third-width">
                                <label for="dependente_documentos_${i}">Anexar documento(s)</label>
                                <div class="file-input-container">
                                    <input type="file" class="file-input" id="dependente_documentos_${i}" name="dependente_documentos_${i}" accept=".pdf,.jpg,.jpeg,.png" multiple>
                                </div>
                                <small class="form-text">Formatos aceitos: PDF, JPG, JPEG, PNG (Max: 5MB)</small>
                            </div>
                            <div class="form-group third-width">
                                <label for="dependente_deficiencia_${i}" class="required">Possui deficiência?</label>
                                <select class="form-control" id="dependente_deficiencia_${i}" name="dependente_deficiencia_${i}" required>
                                    <option value="NÃO">NÃO</option>
                                    <option value="SIM">SIM</option>
                                </select>
                            </div>
                            <div class="form-group third-width">
                                <label for="dependente_renda_${i}" class="required">Possui renda?</label>
                                <select class="form-control dependente-renda-select" id="dependente_renda_${i}" name="dependente_renda_${i}" data-dependente="${i}" required>
                                    <option value="NÃO">NÃO</option>
                                    <option value="SIM">SIM</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row dependente-renda-doc" id="dependente_renda_doc_${i}" style="display: none;">
                            <div class="form-group full-width">
                                <label for="dependente_comprovante_renda_${i}">Anexar comprovante de renda</label>
                                <div class="file-input-container">
                                    <input type="file" class="file-input" id="dependente_comprovante_renda_${i}" name="dependente_comprovante_renda_${i}" accept=".pdf,.jpg,.jpeg,.png">
                                </div>
                                <small class="form-text">Formatos aceitos: PDF, JPG, JPEG, PNG (Max: 5MB)</small>
                            </div>
                        </div>
                    `;
                    
                    dependentesContainer.appendChild(dependenteDiv);
                }
                
                // Após adicionar os campos dos dependentes, configurar os eventos para mostrar/esconder comprovante de renda
                const rendaSelects = document.querySelectorAll('.dependente-renda-select');
                rendaSelects.forEach(select => {
                    select.addEventListener('change', function() {
                        const dependenteNum = this.getAttribute('data-dependente');
                        const rendaDocDiv = document.getElementById(`dependente_renda_doc_${dependenteNum}`);
                        
                        if (this.value === 'SIM') {
                            rendaDocDiv.style.display = 'flex';
                            document.getElementById(`dependente_comprovante_renda_${dependenteNum}`).required = true;
                        } else {
                            rendaDocDiv.style.display = 'none';
                            document.getElementById(`dependente_comprovante_renda_${dependenteNum}`).required = false;
                        }
                    });
                });
                
                // Garantir que os eventos para uppercase também sejam adicionados aos novos campos
                const newUppercaseInputs = dependentesContainer.querySelectorAll('.uppercase-input');
                newUppercaseInputs.forEach(input => {
                    input.addEventListener('input', function() {
                        this.value = this.value.toUpperCase();
                    });
                });
                
                // Aplicar máscaras para CPFs de dependentes dinamicamente adicionados
                const dependenteCPFs = document.querySelectorAll('[id^=dependente_cpf_]');
                dependenteCPFs.forEach(cpf => {
                    aplicarMascara(cpf, '###.###.###-##');
                });
            });

            // Máscaras para os campos
            function aplicarMascara(campo, mascara) {
                campo.addEventListener('input', function(e) {
                    let valor = e.target.value.replace(/\D/g, '');
                    let novoValor = '';
                    let indice = 0;
                    
                    for (let i = 0; i < mascara.length && indice < valor.length; i++) {
                        if (mascara[i] === '#') {
                            novoValor += valor[indice++];
                        } else {
                            novoValor += mascara[i];
                        }
                    }
                    
                    e.target.value = novoValor;
                });
            }
            
            // Aplicar máscara para o CPF
            aplicarMascara(document.getElementById('cpf'), '###.###.###-##');
            
            // Aplicar máscara para o CEP
            aplicarMascara(document.getElementById('cep'), '#####-###');
            
            // Aplicar máscara para os telefones
            aplicarMascara(document.getElementById('telefone'), '(##) #####-####');
            aplicarMascara(document.getElementById('celular'), '(##) #####-####');
            
            // Máscara para valor monetário (aluguel)
            document.getElementById('valor_aluguel').addEventListener('input', function(e) {
                let valor = e.target.value.replace(/\D/g, '');
                
                if (valor === '') {
                    e.target.value = '';
                    return;
                }
                
                valor = parseInt(valor) / 100;
                e.target.value = valor.toLocaleString('pt-BR', {
                    style: 'currency',
                    currency: 'BRL',
                    minimumFractionDigits: 2
                });
            });

            // Configuração do botão de impressão
            document.getElementById('print-button').addEventListener('click', function() {
                window.open('social-relatorio-habitacao.php?id=123');
            });
        });
    </script>
</body>

</html>