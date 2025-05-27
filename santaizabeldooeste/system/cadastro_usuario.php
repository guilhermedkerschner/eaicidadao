<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Usuários - Sistema da Prefeitura</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --error-color: #e74c3c;
            --warning-color: #f39c12;
            --text-color: #333;
            --light-color: #ecf0f1;
            --sidebar-width: 250px;
            --header-height: 60px;
        }

        body {
            display: flex;
            min-height: 100vh;
            background-color: #f5f7fa;
        }

        /* Sidebar styles */
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--primary-color);
            color: white;
            position: fixed;
            height: 100%;
            left: 0;
            top: 0;
            z-index: 100;
            transition: all 0.3s;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            height: var(--header-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar-header h3 {
            font-size: 1.2rem;
            color: white;
        }

        .sidebar.collapsed {
            width: 70px;
        }

        .sidebar.collapsed .menu-text,
        .sidebar.collapsed .sidebar-header h3 {
            display: none;
        }

        .toggle-btn {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
        }

        /* Menu styles */
        .menu {
            list-style: none;
            padding: 10px 0;
        }

        .menu-item {
            position: relative;
        }

        .menu-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }

        .menu-link:hover, 
        .menu-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--secondary-color);
        }

        .menu-icon {
            margin-right: 10px;
            font-size: 18px;
            width: 25px;
            text-align: center;
        }

        /* Main content styles */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: margin-left 0.3s;
        }

        .main-content.expanded {
            margin-left: 70px;
        }

        .header {
            height: var(--header-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .page-title {
            margin-bottom: 20px;
            font-size: 1.8rem;
            color: var(--text-color);
        }

        .breadcrumb {
            margin-bottom: 20px;
            color: #666;
            font-size: 0.9rem;
        }

        .breadcrumb a {
            color: var(--secondary-color);
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        /* Form styles */
        .form-container {
            background-color: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            max-width: 800px;
        }

        .form-section {
            margin-bottom: 30px;
        }

        .form-section-title {
            font-size: 1.3rem;
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e0e0;
            display: flex;
            align-items: center;
        }

        .form-section-title i {
            margin-right: 10px;
            color: var(--secondary-color);
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px 20px;
        }

        .form-group {
            flex: 1;
            padding: 0 10px;
            min-width: 200px;
        }

        .form-group.full-width {
            flex: 0 0 100%;
        }

        .form-group.half-width {
            flex: 0 0 50%;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-color);
        }

        .form-group label.required::after {
            content: " *";
            color: var(--error-color);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: all 0.3s;
            background-color: white;
        }

        .form-control:focus {
            border-color: var(--secondary-color);
            outline: none;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }

        .form-control.is-invalid {
            border-color: var(--error-color);
            background-color: #ffeaea;
        }

        .form-control.is-valid {
            border-color: var(--success-color);
            background-color: #eafaf1;
        }

        .invalid-feedback {
            display: none;
            color: var(--error-color);
            font-size: 0.875rem;
            margin-top: 5px;
        }

        .form-control.is-invalid + .invalid-feedback {
            display: block;
        }

        .password-strength {
            margin-top: 5px;
            font-size: 0.875rem;
        }

        .strength-weak { color: var(--error-color); }
        .strength-medium { color: var(--warning-color); }
        .strength-strong { color: var(--success-color); }

        .password-toggle {
            position: relative;
        }

        .password-toggle .toggle-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            font-size: 16px;
        }

        /* Users table styles */
        .users-list-container {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 1.3rem;
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e0e0;
            display: flex;
            align-items: center;
        }

        .section-title i {
            margin-right: 10px;
            color: var(--secondary-color);
        }

        .table-container {
            overflow-x: auto;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .users-table th,
        .users-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        .users-table th {
            background-color: #f8f9fa;
            color: var(--primary-color);
            font-weight: 600;
            position: sticky;
            top: 0;
        }

        .users-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-ativo {
            background-color: #d4edda;
            color: #155724;
        }

        .status-inativo {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-suspenso {
            background-color: #fff3cd;
            color: #856404;
        }

        .nivel-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
        }

        .nivel-admin {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .nivel-funcionario {
            background-color: #d4edda;
            color: #155724;
        }

        .nivel-usuario {
            background-color: #e2e3e5;
            color: #383d41;
        }

        .action-btn {
            padding: 4px 8px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.75rem;
            margin-right: 5px;
            transition: all 0.3s;
        }

        .btn-edit {
            background-color: var(--secondary-color);
            color: white;
        }

        .btn-edit:hover {
            background-color: #2980b9;
        }

        .btn-delete {
            background-color: var(--error-color);
            color: white;
        }

        .btn-delete:hover {
            background-color: #c0392b;
        }

        .btn-toggle-status {
            background-color: var(--warning-color);
            color: white;
        }

        .btn-toggle-status:hover {
            background-color: #d68910;
        }

        .no-users {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            border: 1px solid transparent;
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .alert-error {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        .alert-warning {
            color: #856404;
            background-color: #fff3cd;
            border-color: #ffeaa7;
        }

        /* Button styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 20px;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 500;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            min-width: 120px;
        }

        .btn i {
            margin-right: 8px;
        }

        .btn-primary {
            background-color: var(--secondary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #545b62;
        }

        .btn-success {
            background-color: var(--success-color);
            color: white;
        }

        .btn-success:hover {
            background-color: #219a52;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }

        /* Loading animation */
        .loading {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .loading-content {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            text-align: center;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--secondary-color);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
            }

            .sidebar .menu-text,
            .sidebar .sidebar-header h3 {
                display: none;
            }

            .main-content {
                margin-left: 70px;
            }

            .form-group.half-width {
                flex: 0 0 100%;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Loading overlay -->
    <div class="loading" id="loading">
        <div class="loading-content">
            <div class="spinner"></div>
            <p>Processando...</p>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3>Prefeitura</h3>
            <button class="toggle-btn" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        
        <ul class="menu">
            <li class="menu-item">
                <a href="dashboard.php" class="menu-link">
                    <span class="menu-icon"><i class="fas fa-tachometer-alt"></i></span>
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="cadastro_usuario.php" class="menu-link active">
                    <span class="menu-icon"><i class="fas fa-user-plus"></i></span>
                    <span class="menu-text">Cadastrar Usuário</span>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="listar_usuarios.php" class="menu-link">
                    <span class="menu-icon"><i class="fas fa-users"></i></span>
                    <span class="menu-text">Listar Usuários</span>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <span class="menu-icon"><i class="fas fa-cog"></i></span>
                    <span class="menu-text">Configurações</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <div class="header">
            <h2>Sistema de Gerenciamento da Prefeitura</h2>
            <div class="user-info">
                <span>Administrador</span>
                <a href="../controller/logout.php" style="margin-left: 15px; color: var(--error-color);">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
        
        <div class="breadcrumb">
            <a href="dashboard.php">Dashboard</a> / <span>Cadastrar Usuário</span>
        </div>
        
        <h1 class="page-title">Cadastro de Usuário do Sistema</h1>
        
        <!-- Alert messages -->
        <div id="alertContainer"></div>
        
        <!-- Usuários Cadastrados -->
        <div class="users-list-container">
            <h3 class="section-title">
                <i class="fas fa-users"></i>
                Usuários Cadastrados
            </h3>
            <div class="table-container">
                <table class="users-table" id="usersTable">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Login</th>
                            <th>E-mail</th>
                            <th>Nível</th>
                            <th>Status</th>
                            <th>Último Acesso</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Os dados serão carregados aqui via JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="form-container">
            <form id="userForm" method="POST" action="./controller/processar_cadastro_usuario.php">
                
                <!-- Informações Pessoais -->
                <div class="form-section">
                    <h3 class="form-section-title">
                        <i class="fas fa-user"></i>
                        Informações Pessoais
                    </h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nome" class="required">Nome Completo</label>
                            <input type="text" class="form-control" id="nome" name="nome" required maxlength="100">
                            <div class="invalid-feedback">Por favor, informe o nome completo.</div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group half-width">
                            <label for="cpf" class="required">CPF</label>
                            <input type="text" class="form-control" id="cpf" name="cpf" required maxlength="14" placeholder="000.000.000-00">
                            <div class="invalid-feedback">Por favor, informe um CPF válido.</div>
                        </div>
                        
                        <div class="form-group half-width">
                            <label for="telefone">Telefone</label>
                            <input type="text" class="form-control" id="telefone" name="telefone" maxlength="15" placeholder="(00) 00000-0000">
                            <div class="invalid-feedback">Por favor, informe um telefone válido.</div>
                        </div>
                    </div>
                </div>

                <!-- Informações de Acesso -->
                <div class="form-section">
                    <h3 class="form-section-title">
                        <i class="fas fa-key"></i>
                        Informações de Acesso
                    </h3>
                    
                    <div class="form-row">
                        <div class="form-group half-width">
                            <label for="login" class="required">Login</label>
                            <input type="text" class="form-control" id="login" name="login" required maxlength="50" placeholder="Digite o login do usuário">
                            <div class="invalid-feedback">Por favor, informe um login válido.</div>
                        </div>
                        
                        <div class="form-group half-width">
                            <label for="email" class="required">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email" required maxlength="100">
                            <div class="invalid-feedback">Por favor, informe um e-mail válido.</div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group half-width">
                            <label for="senha" class="required">Senha</label>
                            <div class="password-toggle">
                                <input type="password" class="form-control" id="senha" name="senha" required minlength="8">
                                <button type="button" class="toggle-btn" onclick="togglePassword('senha')">
                                    <i class="fas fa-eye" id="senha-icon"></i>
                                </button>
                            </div>
                            <div class="password-strength" id="senha-strength"></div>
                            <div class="invalid-feedback">A senha deve ter pelo menos 8 caracteres.</div>
                        </div>
                        
                        <div class="form-group half-width">
                            <label for="confirmar_senha" class="required">Confirmar Senha</label>
                            <div class="password-toggle">
                                <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" required minlength="8">
                                <button type="button" class="toggle-btn" onclick="togglePassword('confirmar_senha')">
                                    <i class="fas fa-eye" id="confirmar_senha-icon"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback">As senhas não coincidem.</div>
                        </div>
                    </div>
                </div>

                <!-- Permissões -->
                <div class="form-section">
                    <h3 class="form-section-title">
                        <i class="fas fa-shield-alt"></i>
                        Permissões e Nível de Acesso
                    </h3>
                    
                    <div class="form-row">
                        <div class="form-group half-width">
                            <label for="nivel_acesso" class="required">Nível de Acesso</label>
                            <select class="form-control" id="nivel_acesso" name="nivel_acesso" required>
                                <option value="">Selecione...</option>
                                <option value="1">Administrador</option>
                                <option value="2">Gestor</option>
                                <option value="3">Funcionário</option>
                                <option value="4">Consulta</option>
                            </select>
                            <div class="invalid-feedback">Por favor, selecione um nível de acesso.</div>
                        </div>
                        
                        <div class="form-group half-width">
                            <label for="setor">Setor/Departamento</label>
                            <select class="form-control" id="setor" name="setor">
                                <option value="">Selecione...</option>
                                <option value="administracao">Administração</option>
                                <option value="agricultura">Agricultura</option>
                                <option value="assistencia_social">Assistência Social</option>
                                <option value="cultura_turismo">Cultura e Turismo</option>
                                <option value="educacao">Educação</option>
                                <option value="esporte">Esporte</option>
                                <option value="fazenda">Fazenda</option>
                                <option value="fiscalizacao">Fiscalização</option>
                                <option value="meio_ambiente">Meio Ambiente</option>
                                <option value="obras">Obras</option>
                                <option value="rodoviario">Rodoviário</option>
                                <option value="servicos_urbanos">Serviços Urbanos</option>
                                <option value="TI">Tecnologia da Informação</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="observacoes">Observações</label>
                            <textarea class="form-control" id="observacoes" name="observacoes" rows="3" maxlength="500" placeholder="Informações adicionais sobre o usuário..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="form-actions">
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i>
                        Cadastrar Usuário
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Toggle sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        }

        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + '-icon');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Show alert
        function showAlert(message, type = 'success') {
            const alertContainer = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'exclamation-triangle'}"></i>
                ${message}
            `;
            
            alertContainer.innerHTML = '';
            alertContainer.appendChild(alert);
            
            // Auto hide after 5 seconds
            setTimeout(() => {
                alert.remove();
            }, 5000);
        }

        // CPF mask and validation
        function applyCPFMask(input) {
            input.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                e.target.value = value;
            });
        }

        // Telefone mask
        function applyPhoneMask(input) {
            input.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length >= 11) {
                    value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
                } else if (value.length >= 7) {
                    value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
                } else if (value.length >= 3) {
                    value = value.replace(/(\d{2})(\d{0,5})/, '($1) $2');
                }
                e.target.value = value;
            });
        }

        // Validate CPF
        function validateCPF(cpf) {
            cpf = cpf.replace(/[^\d]/g, '');
            
            if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) {
                return false;
            }
            
            let sum = 0;
            for (let i = 0; i < 9; i++) {
                sum += parseInt(cpf.charAt(i)) * (10 - i);
            }
            
            let remainder = 11 - (sum % 11);
            let digit1 = remainder === 10 || remainder === 11 ? 0 : remainder;
            
            if (digit1 !== parseInt(cpf.charAt(9))) {
                return false;
            }
            
            sum = 0;
            for (let i = 0; i < 10; i++) {
                sum += parseInt(cpf.charAt(i)) * (11 - i);
            }
            
            remainder = 11 - (sum % 11);
            let digit2 = remainder === 10 || remainder === 11 ? 0 : remainder;
            
            return digit2 === parseInt(cpf.charAt(10));
        }

        // Password strength checker
        function checkPasswordStrength(password) {
            const strengthElement = document.getElementById('senha-strength');
            let strength = 0;
            let text = '';
            let className = '';
            
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            switch (strength) {
                case 0:
                case 1:
                case 2:
                    text = 'Senha fraca';
                    className = 'strength-weak';
                    break;
                case 3:
                case 4:
                    text = 'Senha média';
                    className = 'strength-medium';
                    break;
                case 5:
                    text = 'Senha forte';
                    className = 'strength-strong';
                    break;
            }
            
            strengthElement.textContent = text;
            strengthElement.className = `password-strength ${className}`;
        }

        // Form validation
        function validateForm() {
            let isValid = true;
            const form = document.getElementById('userForm');
            const formData = new FormData(form);
            
            // Reset validation
            document.querySelectorAll('.form-control').forEach(input => {
                input.classList.remove('is-invalid', 'is-valid');
            });
            
            // Validate required fields
            const requiredFields = form.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.add('is-valid');
                }
            });
            
            // Validate CPF
            const cpf = document.getElementById('cpf');
            if (cpf.value && !validateCPF(cpf.value)) {
                cpf.classList.add('is-invalid');
                isValid = false;
            }
            
            // Validate email
            const email = document.getElementById('email');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email.value && !emailRegex.test(email.value)) {
                email.classList.add('is-invalid');
                isValid = false;
            }
            
            // Validate password match
            const senha = document.getElementById('senha');
            const confirmarSenha = document.getElementById('confirmar_senha');
            if (senha.value !== confirmarSenha.value) {
                confirmarSenha.classList.add('is-invalid');
                isValid = false;
            }
            
            return isValid;
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Load users list
            loadUsersList();
            
            // Apply masks
            applyCPFMask(document.getElementById('cpf'));
            applyPhoneMask(document.getElementById('telefone'));
            
            // Login validation
            document.getElementById('login').addEventListener('input', function() {
                // Remove espaços e caracteres especiais, exceto underscore
                this.value = this.value.replace(/[^a-zA-Z0-9_]/g, '').toLowerCase();
            });
            
            // Password strength checker
            document.getElementById('senha').addEventListener('input', function() {
                checkPasswordStrength(this.value);
            });
            
            // Confirm password validation
            document.getElementById('confirmar_senha').addEventListener('input', function() {
                const senha = document.getElementById('senha').value;
                if (this.value && this.value !== senha) {
                    this.classList.add('is-invalid');
                } else if (this.value) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                }
            });
            
            // Form submission
            document.getElementById('userForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (validateForm()) {
                    // Show loading
                    document.getElementById('loading').style.display = 'flex';
                    
                    // Submit form via AJAX
                    const formData = new FormData(this);
                    
                    fetch('./controller/processar_cadastro_usuario.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Hide loading
                        document.getElementById('loading').style.display = 'none';
                        
                        if (data.success) {
                            showAlert(data.message, 'success');
                            // Reset form
                            this.reset();
                            document.querySelectorAll('.form-control').forEach(input => {
                                input.classList.remove('is-invalid', 'is-valid');
                            });
                            // Reload users list
                            loadUsersList();
                        } else {
                            showAlert(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        // Hide loading
                        document.getElementById('loading').style.display = 'none';
                        showAlert('Erro ao processar solicitação. Tente novamente.', 'error');
                        console.error('Error:', error);
                    });
                } else {
                    showAlert('Por favor, corrija os erros no formulário.', 'error');
                }
            });
        });

        // Load users list
        function loadUsersList() {
            fetch('listar_usuarios.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        populateUsersTable(data.users);
                    } else {
                        showAlert('Erro ao carregar lista de usuários: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error loading users:', error);
                    showAlert('Erro ao carregar lista de usuários.', 'error');
                });
        }

        // Populate users table
        function populateUsersTable(users) {
            const tbody = document.querySelector('#usersTable tbody');
            tbody.innerHTML = '';

            if (users.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="no-users">Nenhum usuário cadastrado</td></tr>';
                return;
            }

            users.forEach(user => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${user.nome}</td>
                    <td>${user.login}</td>
                    <td>${user.email}</td>
                    <td><span class="nivel-badge nivel-${user.nivel}">${user.nivel}</span></td>
                    <td><span class="status-badge status-${user.status}">${user.status}</span></td>
                    <td>${user.ultimo_acesso || 'Nunca'}</td>
                    <td>
                        <button class="action-btn btn-edit" onclick="editUser(${user.id})" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn btn-toggle-status" onclick="toggleUserStatus(${user.id}, '${user.status}')" title="Alterar Status">
                            <i class="fas fa-toggle-${user.status === 'ativo' ? 'on' : 'off'}"></i>
                        </button>
                        ${user.nivel !== 'admin' ? `<button class="action-btn btn-delete" onclick="deleteUser(${user.id})" title="Excluir"><i class="fas fa-trash"></i></button>` : ''}
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // Edit user function
        function editUser(userId) {
            showAlert('Funcionalidade de edição em desenvolvimento.', 'warning');
        }

        // Toggle user status
        function toggleUserStatus(userId, currentStatus) {
            const newStatus = currentStatus === 'ativo' ? 'inativo' : 'ativo';
            
            if (confirm(`Deseja ${newStatus === 'ativo' ? 'ativar' : 'inativar'} este usuário?`)) {
                fetch('alterar_status_usuario.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        user_id: userId,
                        status: newStatus
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        loadUsersList();
                    } else {
                        showAlert(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Erro ao alterar status do usuário.', 'error');
                });
            }
        }

        // Delete user function
        function deleteUser(userId) {
            if (confirm('Tem certeza que deseja excluir este usuário? Esta ação não pode ser desfeita.')) {
                fetch('excluir_usuario.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        user_id: userId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        loadUsersList();
                    } else {
                        showAlert(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Erro ao excluir usuário.', 'error');
                });
            }
        }
    </script>
</body>
</html>