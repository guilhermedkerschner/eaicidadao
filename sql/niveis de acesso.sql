CREATE TABLE tb_niveis_acesso (
    nivel_id INT AUTO_INCREMENT PRIMARY KEY,
    nivel_nome VARCHAR(50) NOT NULL,
    nivel_descricao VARCHAR(255),
    nivel_permissoes JSON,
    nivel_status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    nivel_data_criacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    nivel_data_modificacao DATETIME ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO tb_niveis_acesso (nivel_nome, nivel_descricao, nivel_permissoes) VALUES
('Administrador', 'Acesso total ao sistema', '{"todos_modulos": true, "todos_privilegios": true}'),
('Gestor', 'Acesso gerencial aos módulos designados', '{"todos_modulos": false, "privilegios_padrão": ["visualizar", "criar", "editar", "aprovar"]}'),
('Colaborador', 'Acesso operacional básico', '{"todos_modulos": false, "privilegios_padrão": ["visualizar", "criar"]}'),
('Consulta', 'Acesso apenas para visualização', '{"todos_modulos": false, "privilegios_padrão": ["visualizar"]}');
