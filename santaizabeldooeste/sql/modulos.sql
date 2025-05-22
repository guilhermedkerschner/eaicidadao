CREATE TABLE tb_modulos (
    modulo_id INT AUTO_INCREMENT PRIMARY KEY,
    modulo_nome VARCHAR(50) NOT NULL,
    modulo_descricao VARCHAR(255),
    modulo_icone VARCHAR(50),
    modulo_status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    modulo_data_criacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modulo_data_modificacao DATETIME ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


INSERT INTO tb_modulos (modulo_nome, modulo_descricao, modulo_icone) VALUES
('Administração', 'Configurações gerais do sistema', 'fa-cog'),
('Agricultura', 'Gestão de serviços agrícolas', 'fa-leaf'),
('Assistência Social', 'Gestão de programas sociais', 'fa-hands-helping'),
('Educação', 'Gestão escolar e educacional', 'fa-graduation-cap'),
('Esporte', 'Gestão de eventos e atividades esportivas', 'fa-running'),
('Fazenda', 'Gestão financeira e orçamentária', 'fa-money-bill-wave'),
('Fiscalização', 'Gestão de fiscalizações e autuações', 'fa-search'),
('Meio Ambiente', 'Gestão ambiental e licenciamentos', 'fa-tree'),
('Obras', 'Gestão de obras e infraestrutura', 'fa-hard-hat'),
('Rodoviário', 'Gestão da frota e vias públicas', 'fa-truck'),
('Serviços Urbanos', 'Gestão de serviços urbanos', 'fa-city'),
('Cultura e Turismo', 'Gestão de eventos culturais e turísticos', 'fa-palette');