CREATE TABLE tb_logs_acesso (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    log_ip VARCHAR(50),
    log_data_acesso DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    log_acao VARCHAR(100),
    log_modulo_id INT,
    log_detalhes TEXT,
    FOREIGN KEY (usuario_id) REFERENCES tb_usuarios_sistema(usuario_id) ON DELETE SET NULL,
    FOREIGN KEY (log_modulo_id) REFERENCES tb_modulos(modulo_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;