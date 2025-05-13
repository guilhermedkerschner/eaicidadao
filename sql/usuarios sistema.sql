CREATE TABLE tb_usuarios_sistema (
    usuario_id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_nome VARCHAR(100) NOT NULL,
    usuario_email VARCHAR(100) NOT NULL UNIQUE,
    usuario_login VARCHAR(50) NOT NULL UNIQUE,
    usuario_senha VARCHAR(255) NOT NULL,
    usuario_cpf VARCHAR(14) UNIQUE,
    usuario_telefone VARCHAR(20),
    usuario_cargo VARCHAR(100),
    usuario_departamento VARCHAR(100),
    usuario_nivel_id INT,
    usuario_ultimo_acesso DATETIME,
    usuario_ultimo_ip VARCHAR(50),
    usuario_token_recuperacao VARCHAR(100),
    usuario_token_expiracao DATETIME,
    usuario_status ENUM('ativo', 'inativo', 'bloqueado', 'pendente') NOT NULL DEFAULT 'ativo',
    usuario_tentativas_login INT DEFAULT 0,
    usuario_data_criacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_data_modificacao DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_nivel_id) REFERENCES tb_niveis_acesso(nivel_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


INSERT INTO tb_usuarios_sistema (
    usuario_nome, 
    usuario_email, 
    usuario_login, 
    usuario_senha, 
    usuario_nivel_id, 
    usuario_cargo, 
    usuario_departamento
) VALUES (
    'Administrador do Sistema', 
    'admin@santaizabeloeste.pr.gov.br', 
    'admin', 
    '$2y$10$7b23hy/.AUUmasupATT50OLdZXIqEG343snirBGaqOhcksPWyXzmy',
    1, 
    'Administrador de Sistema', 
    'Tecnologia da Informação'
);