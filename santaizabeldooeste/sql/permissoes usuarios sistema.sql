CREATE TABLE tb_permissoes_usuario (
    permissao_id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    modulo_id INT NOT NULL,
    permissao_visualizar BOOLEAN NOT NULL DEFAULT FALSE,
    permissao_criar BOOLEAN NOT NULL DEFAULT FALSE,
    permissao_editar BOOLEAN NOT NULL DEFAULT FALSE,
    permissao_excluir BOOLEAN NOT NULL DEFAULT FALSE,
    permissao_aprovar BOOLEAN NOT NULL DEFAULT FALSE,
    permissao_data_criacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    permissao_data_modificacao DATETIME ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES tb_usuarios_sistema(usuario_id) ON DELETE CASCADE,
    FOREIGN KEY (modulo_id) REFERENCES tb_modulos(modulo_id) ON DELETE CASCADE,
    UNIQUE KEY usuario_modulo (usuario_id, modulo_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;