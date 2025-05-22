CREATE TABLE tb_sessoes_usuario (
    sessao_id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    sessao_token VARCHAR(255) NOT NULL,
    sessao_ip VARCHAR(50),
    sessao_user_agent VARCHAR(255),
    sessao_data_inicio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    sessao_data_expiracao DATETIME NOT NULL,
    sessao_data_ultima_atividade DATETIME,
    sessao_status ENUM('ativa', 'expirada', 'encerrada') NOT NULL DEFAULT 'ativa',
    FOREIGN KEY (usuario_id) REFERENCES tb_usuarios_sistema(usuario_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;