<?php
/**
 * Configuração dos Departamentos da Prefeitura
 * Prefeitura Municipal de Santa Izabel do Oeste
 * 
 * Define todos os departamentos, suas configurações, contatos e módulos associados
 */

return [
    // =====================================
    // ADMINISTRAÇÃO GERAL
    // =====================================
    'ALL' => [
        'name' => 'Administração Geral',
        'slug' => 'administracao_geral',
        'description' => 'Acesso irrestrito a todos os módulos do sistema',
        'short_name' => 'Admin',
        'color' => '#e74c3c',
        'icon' => 'fas fa-crown',
        'category' => 'admin',
        'modules' => ['ALL'], // Acesso a todos os módulos
        'active' => true,
        'order' => 0,
        'head_office' => true, // Indica se é sede/principal
        'contact' => [
            'responsible' => 'Administrador do Sistema',
            'phone' => '(45) 3000-0000',
            'email' => 'admin@santaizabeldooeste.pr.gov.br',
            'extension' => '1000',
            'address' => 'Gabinete do Prefeito'
        ],
        'settings' => [
            'can_create_users' => true,
            'can_modify_permissions' => true,
            'can_access_all_reports' => true,
            'can_configure_system' => true
        ]
    ],

    // =====================================
    // DEPARTAMENTOS EXECUTIVOS
    // =====================================
    'AGRICULTURA' => [
        'name' => 'Secretaria de Agricultura',
        'slug' => 'agricultura',
        'description' => 'Desenvolvimento rural, programas agrícolas e apoio aos produtores',
        'short_name' => 'Agricultura',
        'color' => '#27ae60',
        'icon' => 'fas fa-leaf',
        'category' => 'department',
        'modules' => ['agricultura', 'dashboard', 'profile'],
        'active' => true,
        'order' => 10,
        'head_office' => false,
        'contact' => [
            'responsible' => 'Secretário(a) de Agricultura',
            'phone' => '(45) 3000-0001',
            'email' => 'agricultura@santaizabeldooeste.pr.gov.br',
            'extension' => '1001',
            'address' => 'Rua da Agricultura, 123 - Centro'
        ],
        'sub_departments' => [
            'AGRICULTURA_PRODUCAO' => [
                'name' => 'Produção Rural',
                'responsible' => 'Coordenador de Produção',
                'modules' => ['agricultura']
            ],
            'AGRICULTURA_PECUARIA' => [
                'name' => 'Pecuária',
                'responsible' => 'Coordenador de Pecuária',
                'modules' => ['agricultura']
            ],
            'AGRICULTURA_COOPERATIVAS' => [
                'name' => 'Cooperativas',
                'responsible' => 'Coordenador de Cooperativas',
                'modules' => ['agricultura']
            ]
        ],
        'settings' => [
            'can_create_projects' => true,
            'can_approve_subsidies' => true,
            'can_issue_certificates' => true,
            'budget_limit' => 500000.00
        ],
        'working_hours' => [
            'start' => '07:30',
            'end' => '17:30',
            'lunch_start' => '12:00',
            'lunch_end' => '13:30'
        ]
    ],

    'ASSISTENCIA_SOCIAL' => [
        'name' => 'Secretaria de Assistência Social',
        'slug' => 'assistencia_social',
        'description' => 'Programas sociais, habitação e assistência às famílias',
        'short_name' => 'Assist. Social',
        'color' => '#e91e63',
        'icon' => 'fas fa-hands-helping',
        'category' => 'department',
        'modules' => ['assistencia_social', 'dashboard', 'profile'],
        'active' => true,
        'order' => 11,
        'head_office' => false,
        'contact' => [
            'responsible' => 'Secretário(a) de Assistência Social',
            'phone' => '(45) 3000-0002',
            'email' => 'assistencia@santaizabeldooeste.pr.gov.br',
            'extension' => '1002',
            'address' => 'Rua da Solidariedade, 456 - Centro'
        ],
        'sub_departments' => [
            'ASSISTENCIA_HABITACAO' => [
                'name' => 'Habitação',
                'responsible' => 'Coordenador de Habitação',
                'modules' => ['assistencia_social']
            ],
            'ASSISTENCIA_FAMILIA' => [
                'name' => 'Programa Família',
                'responsible' => 'Coordenador Família',
                'modules' => ['assistencia_social']
            ],
            'ASSISTENCIA_IDOSO' => [
                'name' => 'Assistência ao Idoso',
                'responsible' => 'Coordenador do Idoso',
                'modules' => ['assistencia_social']
            ],
            'ASSISTENCIA_CRIANCA' => [
                'name' => 'Assistência à Criança',
                'responsible' => 'Coordenador da Criança',
                'modules' => ['assistencia_social']
            ]
        ],
        'settings' => [
            'can_approve_benefits' => true,
            'can_manage_housing_programs' => true,
            'can_issue_social_certificates' => true,
            'max_benefit_value' => 5000.00
        ],
        'working_hours' => [
            'start' => '08:00',
            'end' => '17:00',
            'lunch_start' => '12:00',
            'lunch_end' => '13:00'
        ]
    ],

    'CULTURA_E_TURISMO' => [
        'name' => 'Secretaria de Cultura e Turismo',
        'slug' => 'cultura_turismo',
        'description' => 'Promoção cultural, eventos e desenvolvimento turístico',
        'short_name' => 'Cultura',
        'color' => '#9b59b6',
        'icon' => 'fas fa-palette',
        'category' => 'department',
        'modules' => ['cultura_turismo', 'dashboard', 'profile'],
        'active' => true,
        'order' => 12,
        'head_office' => false,
        'contact' => [
            'responsible' => 'Secretário(a) de Cultura e Turismo',
            'phone' => '(45) 3000-0003',
            'email' => 'cultura@santaizabeldooeste.pr.gov.br',
            'extension' => '1003',
            'address' => 'Casa de Cultura - Centro'
        ],
        'sub_departments' => [
            'CULTURA_EVENTOS' => [
                'name' => 'Eventos Culturais',
                'responsible' => 'Coordenador de Eventos',
                'modules' => ['cultura_turismo']
            ],
            'CULTURA_BIBLIOTECA' => [
                'name' => 'Biblioteca Municipal',
                'responsible' => 'Bibliotecário Chefe',
                'modules' => ['cultura_turismo']
            ],
            'TURISMO_PROMOCAO' => [
                'name' => 'Promoção Turística',
                'responsible' => 'Coordenador de Turismo',
                'modules' => ['cultura_turismo']
            ]
        ],
        'settings' => [
            'can_organize_events' => true,
            'can_manage_cultural_spaces' => true,
            'can_promote_tourism' => true,
            'event_budget_limit' => 100000.00
        ],
        'working_hours' => [
            'start' => '08:00',
            'end' => '17:00',
            'lunch_start' => '12:00',
            'lunch_end' => '13:00'
        ]
    ],

    'EDUCACAO' => [
        'name' => 'Secretaria de Educação',
        'slug' => 'educacao',
        'description' => 'Gestão escolar, professores, alunos e programas educacionais',
        'short_name' => 'Educação',
        'color' => '#3498db',
        'icon' => 'fas fa-graduation-cap',
        'category' => 'department',
        'modules' => ['educacao', 'dashboard', 'profile'],
        'active' => true,
        'order' => 13,
        'head_office' => false,
        'contact' => [
            'responsible' => 'Secretário(a) de Educação',
            'phone' => '(45) 3000-0004',
            'email' => 'educacao@santaizabeldooeste.pr.gov.br',
            'extension' => '1004',
            'address' => 'Centro de Educação Municipal'
        ],
        'sub_departments' => [
            'EDUCACAO_INFANTIL' => [
                'name' => 'Educação Infantil',
                'responsible' => 'Coordenador Ed. Infantil',
                'modules' => ['educacao']
            ],
            'EDUCACAO_FUNDAMENTAL' => [
                'name' => 'Ensino Fundamental',
                'responsible' => 'Coordenador Fundamental',
                'modules' => ['educacao']
            ],
            'EDUCACAO_ESPECIAL' => [
                'name' => 'Educação Especial',
                'responsible' => 'Coordenador Ed. Especial',
                'modules' => ['educacao']
            ],
            'EDUCACAO_TRANSPORTE' => [
                'name' => 'Transporte Escolar',
                'responsible' => 'Coordenador de Transporte',
                'modules' => ['educacao', 'rodoviario']
            ]
        ],
        'settings' => [
            'can_manage_schools' => true,
            'can_hire_teachers' => true,
            'can_manage_transport' => true,
            'can_approve_transfers' => true
        ],
        'working_hours' => [
            'start' => '07:00',
            'end' => '17:00',
            'lunch_start' => '12:00',
            'lunch_end' => '13:00'
        ]
    ],

    'ESPORTE' => [
        'name' => 'Secretaria de Esporte e Lazer',
        'slug' => 'esporte',
        'description' => 'Promoção esportiva, eventos e gestão de equipamentos',
        'short_name' => 'Esporte',
        'color' => '#f39c12',
        'icon' => 'fas fa-running',
        'category' => 'department',
        'modules' => ['esporte', 'dashboard', 'profile'],
        'active' => true,
        'order' => 14,
        'head_office' => false,
        'contact' => [
            'responsible' => 'Secretário(a) de Esporte',
            'phone' => '(45) 3000-0005',
            'email' => 'esporte@santaizabeldooeste.pr.gov.br',
            'extension' => '1005',
            'address' => 'Ginásio Municipal de Esportes'
        ],
        'sub_departments' => [
            'ESPORTE_COMPETITIVO' => [
                'name' => 'Esporte Competitivo',
                'responsible' => 'Coordenador de Competições',
                'modules' => ['esporte']
            ],
            'ESPORTE_LAZER' => [
                'name' => 'Esporte e Lazer',
                'responsible' => 'Coordenador de Lazer',
                'modules' => ['esporte']
            ],
            'ESPORTE_EQUIPAMENTOS' => [
                'name' => 'Equipamentos Esportivos',
                'responsible' => 'Coordenador de Equipamentos',
                'modules' => ['esporte']
            ]
        ],
        'settings' => [
            'can_organize_tournaments' => true,
            'can_manage_facilities' => true,
            'can_coordinate_teams' => true,
            'equipment_budget_limit' => 50000.00
        ],
        'working_hours' => [
            'start' => '08:00',
            'end' => '17:00',
            'lunch_start' => '12:00',
            'lunch_end' => '13:00'
        ]
    ],

    'FAZENDA' => [
        'name' => 'Secretaria da Fazenda',
        'slug' => 'fazenda',
        'description' => 'Gestão financeira, orçamento, receitas e despesas municipais',
        'short_name' => 'Fazenda',
        'color' => '#e67e22',
        'icon' => 'fas fa-money-bill-wave',
        'category' => 'department',
        'modules' => ['fazenda', 'dashboard', 'profile'],
        'active' => true,
        'order' => 15,
        'head_office' => false,
        'contact' => [
            'responsible' => 'Secretário(a) da Fazenda',
            'phone' => '(45) 3000-0006',
            'email' => 'fazenda@santaizabeldooeste.pr.gov.br',
            'extension' => '1006',
            'address' => 'Departamento de Finanças - Prefeitura'
        ],
        'sub_departments' => [
            'FAZENDA_ORCAMENTO' => [
                'name' => 'Orçamento',
                'responsible' => 'Coordenador de Orçamento',
                'modules' => ['fazenda']
            ],
            'FAZENDA_TRIBUTOS' => [
                'name' => 'Tributos',
                'responsible' => 'Coordenador de Tributos',
                'modules' => ['fazenda']
            ],
            'FAZENDA_CONTABILIDADE' => [
                'name' => 'Contabilidade',
                'responsible' => 'Contador Municipal',
                'modules' => ['fazenda']
            ],
            'FAZENDA_COMPRAS' => [
                'name' => 'Compras e Licitações',
                'responsible' => 'Coordenador de Compras',
                'modules' => ['fazenda']
            ]
        ],
        'settings' => [
            'can_manage_budget' => true,
            'can_approve_expenses' => true,
            'can_manage_taxes' => true,
            'requires_dual_approval' => true,
            'high_value_threshold' => 50000.00
        ],
        'working_hours' => [
            'start' => '08:00',
            'end' => '17:00',
            'lunch_start' => '12:00',
            'lunch_end' => '13:00'
        ],
        'security_level' => 'high' // Departamento crítico
    ],

    'FISCALIZACAO' => [
        'name' => 'Secretaria de Fiscalização',
        'slug' => 'fiscalizacao',
        'description' => 'Fiscalização municipal, denúncias e cumprimento de normas',
        'short_name' => 'Fiscalização',
        'color' => '#8e44ad',
        'icon' => 'fas fa-search',
        'category' => 'department',
        'modules' => ['fiscalizacao', 'dashboard', 'profile'],
        'active' => true,
        'order' => 16,
        'head_office' => false,
        'contact' => [
            'responsible' => 'Secretário(a) de Fiscalização',
            'phone' => '(45) 3000-0007',
            'email' => 'fiscalizacao@santaizabeldooeste.pr.gov.br',
            'extension' => '1007',
            'address' => 'Departamento de Fiscalização'
        ],
        'sub_departments' => [
            'FISCALIZACAO_URBANA' => [
                'name' => 'Fiscalização Urbana',
                'responsible' => 'Coordenador Urbano',
                'modules' => ['fiscalizacao']
            ],
            'FISCALIZACAO_RURAL' => [
                'name' => 'Fiscalização Rural',
                'responsible' => 'Coordenador Rural',
                'modules' => ['fiscalizacao']
            ],
            'FISCALIZACAO_POSTURAS' => [
                'name' => 'Posturas Municipais',
                'responsible' => 'Coordenador de Posturas',
                'modules' => ['fiscalizacao']
            ],
            'FISCALIZACAO_DENUNCIAS' => [
                'name' => 'Ouvidoria e Denúncias',
                'responsible' => 'Ouvidor Municipal',
                'modules' => ['fiscalizacao']
            ]
        ],
        'settings' => [
            'can_issue_fines' => true,
            'can_close_establishments' => true,
            'can_investigate_complaints' => true,
            'emergency_response' => true
        ],
        'working_hours' => [
            'start' => '08:00',
            'end' => '17:00',
            'lunch_start' => '12:00',
            'lunch_end' => '13:00',
            'emergency_24h' => true // Disponível 24h para emergências
        ]
    ],

    'MEIO_AMBIENTE' => [
        'name' => 'Secretaria de Meio Ambiente',
        'slug' => 'meio_ambiente',
        'description' => 'Preservação ambiental, licenciamentos e projetos sustentáveis',
        'short_name' => 'Meio Ambiente',
        'color' => '#16a085',
        'icon' => 'fas fa-tree',
        'category' => 'department',
        'modules' => ['meio_ambiente', 'dashboard', 'profile'],
        'active' => true,
        'order' => 17,
        'head_office' => false,
        'contact' => [
            'responsible' => 'Secretário(a) de Meio Ambiente',
            'phone' => '(45) 3000-0008',
            'email' => 'meioambiente@santaizabeldooeste.pr.gov.br',
            'extension' => '1008',
            'address' => 'Departamento de Meio Ambiente'
        ],
        'sub_departments' => [
            'AMBIENTE_LICENCIAMENTO' => [
                'name' => 'Licenciamento Ambiental',
                'responsible' => 'Coordenador de Licenciamento',
                'modules' => ['meio_ambiente']
            ],
            'AMBIENTE_FISCALIZACAO' => [
                'name' => 'Fiscalização Ambiental',
                'responsible' => 'Fiscal Ambiental',
                'modules' => ['meio_ambiente', 'fiscalizacao']
            ],
            'AMBIENTE_PROJETOS' => [
                'name' => 'Projetos Ambientais',
                'responsible' => 'Coordenador de Projetos',
                'modules' => ['meio_ambiente']
            ],
            'AMBIENTE_EDUCACAO' => [
                'name' => 'Educação Ambiental',
                'responsible' => 'Educador Ambiental',
                'modules' => ['meio_ambiente', 'educacao']
            ]
        ],
        'settings' => [
            'can_issue_licenses' => true,
            'can_create_environmental_projects' => true,
            'can_apply_environmental_fines' => true,
            'can_monitor_compliance' => true
        ],
        'working_hours' => [
            'start' => '08:00',
            'end' => '17:00',
            'lunch_start' => '12:00',
            'lunch_end' => '13:00'
        ]
    ],

    'OBRAS' => [
        'name' => 'Secretaria de Obras',
        'slug' => 'obras',
        'description' => 'Projetos, licitações e acompanhamento de obras públicas',
        'short_name' => 'Obras',
        'color' => '#d35400',
        'icon' => 'fas fa-hard-hat',
        'category' => 'department',
        'modules' => ['obras', 'dashboard', 'profile'],
        'active' => true,
        'order' => 18,
        'head_office' => false,
        'contact' => [
            'responsible' => 'Secretário(a) de Obras',
            'phone' => '(45) 3000-0009',
            'email' => 'obras@santaizabeldooeste.pr.gov.br',
            'extension' => '1009',
            'address' => 'Departamento de Obras Públicas'
        ],
        'sub_departments' => [
            'OBRAS_PROJETOS' => [
                'name' => 'Projetos de Obras',
                'responsible' => 'Engenheiro de Projetos',
                'modules' => ['obras']
            ],
            'OBRAS_EXECUCAO' => [
                'name' => 'Execução de Obras',
                'responsible' => 'Engenheiro de Obras',
                'modules' => ['obras']
            ],
            'OBRAS_LICITACAO' => [
                'name' => 'Licitações',
                'responsible' => 'Coordenador de Licitações',
                'modules' => ['obras', 'fazenda']
            ],
            'OBRAS_MANUTENCAO' => [
                'name' => 'Manutenção Predial',
                'responsible' => 'Coordenador de Manutenção',
                'modules' => ['obras']
            ]
        ],
        'settings' => [
            'can_manage_construction_projects' => true,
            'can_approve_project_changes' => true,
            'can_manage_contractors' => true,
            'requires_engineering_approval' => true,
            'safety_protocols_required' => true
        ],
        'working_hours' => [
            'start' => '07:00',
            'end' => '17:00',
            'lunch_start' => '12:00',
            'lunch_end' => '13:00'
        ]
    ],

    'RODOVIARIO' => [
        'name' => 'Secretaria Rodoviária',
        'slug' => 'rodoviario',
        'description' => 'Gestão de frota, manutenção viária e transporte público',
        'short_name' => 'Rodoviário',
        'color' => '#7f8c8d',
        'icon' => 'fas fa-truck',
        'category' => 'department',
        'modules' => ['rodoviario', 'dashboard', 'profile'],
        'active' => true,
        'order' => 19,
        'head_office' => false,
        'contact' => [
            'responsible' => 'Secretário(a) Rodoviário',
            'phone' => '(45) 3000-0010',
            'email' => 'rodoviario@santaizabeldooeste.pr.gov.br',
            'extension' => '1010',
            'address' => 'Garagem Municipal'
        ],
        'sub_departments' => [
            'RODOVIARIO_FROTA' => [
                'name' => 'Gestão de Frota',
                'responsible' => 'Coordenador de Frota',
                'modules' => ['rodoviario']
            ],
            'RODOVIARIO_MANUTENCAO' => [
                'name' => 'Manutenção Viária',
                'responsible' => 'Coordenador de Manutenção',
                'modules' => ['rodoviario']
            ],
            'RODOVIARIO_TRANSPORTE' => [
                'name' => 'Transporte Público',
                'responsible' => 'Coordenador de Transporte',
                'modules' => ['rodoviario']
            ]
        ],
        'settings' => [
            'can_manage_vehicle_fleet' => true,
            'can_schedule_maintenance' => true,
            'can_manage_drivers' => true,
            'fuel_budget_control' => true
        ],
        'working_hours' => [
            'start' => '06:00',
            'end' => '18:00',
            'lunch_start' => '12:00',
            'lunch_end' => '13:00',
            'shift_work' => true // Trabalho em turnos
        ]
    ],

    'SERVICOS_URBANOS' => [
        'name' => 'Secretaria de Serviços Urbanos',
        'slug' => 'servicos_urbanos',
        'description' => 'Limpeza urbana, coleta de lixo e manutenção da cidade',
        'short_name' => 'Serv. Urbanos',
        'color' => '#34495e',
        'icon' => 'fas fa-city',
        'category' => 'department',
        'modules' => ['servicos_urbanos', 'dashboard', 'profile'],
        'active' => true,
        'order' => 20,
        'head_office' => false,
        'contact' => [
            'responsible' => 'Secretário(a) de Serviços Urbanos',
            'phone' => '(45) 3000-0011',
            'email' => 'servicosurbanos@santaizabeldooeste.pr.gov.br',
            'extension' => '1011',
            'address' => 'Departamento de Serviços Urbanos'
        ],
        'sub_departments' => [
            'URBANOS_LIMPEZA' => [
                'name' => 'Limpeza Urbana',
                'responsible' => 'Coordenador de Limpeza',
                'modules' => ['servicos_urbanos']
            ],
            'URBANOS_COLETA' => [
                'name' => 'Coleta de Resíduos',
                'responsible' => 'Coordenador de Coleta',
                'modules' => ['servicos_urbanos']
            ],
            'URBANOS_JARDINS' => [
                'name' => 'Jardinagem e Paisagismo',
                'responsible' => 'Coordenador de Jardins',
                'modules' => ['servicos_urbanos']
            ],
            'URBANOS_ILUMINACAO' => [
                'name' => 'Iluminação Pública',
                'responsible' => 'Eletricista Chefe',
                'modules' => ['servicos_urbanos']
            ]
        ],
        'settings' => [
            'can_schedule_urban_services' => true,
            'can_manage_waste_collection' => true,
            'can_maintain_public_lighting' => true,
            'emergency_response_24h' => true
        ],
        'working_hours' => [
            'start' => '06:00',
            'end' => '18:00',
            'lunch_start' => '12:00',
            'lunch_end' => '13:00',
            'emergency_24h' => true // Emergências 24h
        ]
    ],

    // =====================================
    // DEPARTAMENTOS DE APOIO
    // =====================================
    'TECNOLOGIA' => [
        'name' => 'Departamento de Tecnologia da Informação',
        'slug' => 'tecnologia',
        'description' => 'Suporte técnico, infraestrutura e desenvolvimento de sistemas',
        'short_name' => 'TI',
        'color' => '#2c3e50',
        'icon' => 'fas fa-laptop-code',
        'category' => 'support',
        'modules' => ['admin', 'dashboard', 'profile'],
        'active' => true,
        'order' => 30,
        'head_office' => false,
        'contact' => [
            'responsible' => 'Coordenador de TI',
            'phone' => '(45) 3000-0012',
            'email' => 'ti@santaizabeldooeste.pr.gov.br',
            'extension' => '1012',
            'address' => 'Sala de TI - Prefeitura'
        ],
        'sub_departments' => [
            'TI_SUPORTE' => [
                'name' => 'Suporte Técnico',
                'responsible' => 'Técnico de Suporte',
                'modules' => ['admin']
            ],
            'TI_DESENVOLVIMENTO' => [
                'name' => 'Desenvolvimento',
                'responsible' => 'Desenvolvedor',
                'modules' => ['admin']
            ],
            'TI_INFRAESTRUTURA' => [
                'name' => 'Infraestrutura',
                'responsible' => 'Administrador de Redes',
                'modules' => ['admin']
            ]
        ],
        'settings' => [
            'can_manage_system' => true,
            'can_access_all_logs' => true,
            'can_modify_permissions' => true,
            'emergency_access' => true
        ],
        'working_hours' => [
            'start' => '08:00',
            'end' => '18:00',
            'lunch_start' => '12:00',
            'lunch_end' => '13:00',
            'on_call_24h' => true // Plantão 24h
        ],
        'security_level' => 'critical' // Acesso crítico ao sistema
    ],

    'JURIDICO' => [
        'name' => 'Departamento Jurídico',
        'slug' => 'juridico',
        'description' => 'Assessoria jurídica e processos legais',
        'short_name' => 'Jurídico',
        'color' => '#8b4513',
        'icon' => 'fas fa-balance-scale',
        'category' => 'support',
        'modules' => ['dashboard', 'profile'],
        'active' => true,
        'order' => 31,
        'head_office' => false,
        'contact' => [
            'responsible' => 'Procurador Municipal',
            'phone' => '(45) 3000-0013',
            'email' => 'juridico@santaizabeldooeste.pr.gov.br',
            'extension' => '1013',
            'address' => 'Procuradoria Municipal'
        ],
        'settings' => [
            'can_access_legal_documents' => true,
            'confidential_access' => true,
            'can_represent_municipality' => true,
            'can_draft_legislation' => true
        ],
        'working_hours' => [
            'start' => '08:00',
            'end' => '18:00',
            'lunch_start' => '12:00',
            'lunch_end' => '14:00'
        ],
        'security_level' => 'high' // Informações confidenciais
    ],

    'RECURSOS_HUMANOS' => [
        'name' => 'Departamento de Recursos Humanos',
        'slug' => 'recursos_humanos',
        'description' => 'Gestão de pessoal, folha de pagamento e benefícios',
        'short_name' => 'RH',
        'color' => '#c0392b',
        'icon' => 'fas fa-users',
        'category' => 'support',
        'modules' => ['dashboard', 'profile'],
        'active' => true,
        'order' => 32,
        'head_office' => false,
        'contact' => [
            'responsible' => 'Coordenador de RH',
            'phone' => '(45) 3000-0014',
            'email' => 'rh@santaizabeldooeste.pr.gov.br',
            'extension' => '1014',
            'address' => 'Departamento de RH - Prefeitura'
        ],
        'sub_departments' => [
            'RH_FOLHA' => [
                'name' => 'Folha de Pagamento',
                'responsible' => 'Coordenador de Folha',
                'modules' => ['fazenda']
            ],
            'RH_BENEFICIOS' => [
                'name' => 'Benefícios',
                'responsible' => 'Coordenador de Benefícios',
                'modules' => ['assistencia_social']
            ],
            'RH_RECRUTAMENTO' => [
                'name' => 'Recrutamento e Seleção',
                'responsible' => 'Analista de RH',
                'modules' => ['admin']
            ]
        ],
        'settings' => [
            'can_manage_payroll' => true,
            'can_access_employee_data' => true,
            'can_process_benefits' => true,
            'confidential_hr_data' => true
        ],
        'working_hours' => [
            'start' => '08:00',
            'end' => '17:00',
            'lunch_start' => '12:00',
            'lunch_end' => '13:00'
        ],
        'security_level' => 'high' // Dados pessoais dos funcionários
    ],

    // =====================================
    // CONFIGURAÇÕES GERAIS DOS DEPARTAMENTOS
    // =====================================
    '_department_settings' => [
        // Configurações globais que se aplicam a todos os departamentos
        'global_settings' => [
            'default_working_hours' => [
                'start' => '08:00',
                'end' => '17:00',
                'lunch_start' => '12:00',
                'lunch_end' => '13:00'
            ],
            'default_timezone' => 'America/Sao_Paulo',
            'default_language' => 'pt_BR',
            'emergency_contact' => '(45) 3000-0000',
            'main_address' => 'Prefeitura Municipal de Santa Izabel do Oeste'
        ],

        // Validações de departamento
        'validation_rules' => [
            'required_fields' => ['name', 'slug', 'description', 'color', 'icon', 'modules'],
            'unique_fields' => ['slug'],
            'slug_pattern' => '/^[a-z0-9_]+$/', // Apenas letras minúsculas, números e underscore
            'color_pattern' => '/^#[0-9A-Fa-f]{6}$/', // Formato hexadecimal
            'max_name_length' => 100,
            'max_description_length' => 255
        ],

        // Hierarquia de departamentos
        'hierarchy' => [
            'admin' => ['ALL', 'TECNOLOGIA'],
            'executive' => [
                'AGRICULTURA', 'ASSISTENCIA_SOCIAL', 'CULTURA_E_TURISMO', 
                'EDUCACAO', 'ESPORTE', 'FAZENDA', 'FISCALIZACAO', 
                'MEIO_AMBIENTE', 'OBRAS', 'RODOVIARIO', 'SERVICOS_URBANOS'
            ],
            'support' => ['JURIDICO', 'RECURSOS_HUMANOS'],
            'external' => [] // Para futuras integrações
        ],

        // Configurações de segurança por categoria
        'security_levels' => [
            'critical' => [
                'requires_2fa' => true,
                'session_timeout' => 1800, // 30 minutos
                'ip_restriction' => true,
                'audit_all_actions' => true
            ],
            'high' => [
                'requires_2fa' => false,
                'session_timeout' => 3600, // 1 hora
                'ip_restriction' => false,
                'audit_sensitive_actions' => true
            ],
            'normal' => [
                'requires_2fa' => false,
                'session_timeout' => 7200, // 2 horas
                'ip_restriction' => false,
                'audit_sensitive_actions' => false
            ]
        ],

        // Templates de permissões por categoria
        'permission_templates' => [
            'admin' => [
                'default_level' => 1,
                'default_permissions' => ['ALL']
            ],
            'department' => [
                'default_level' => 2,
                'default_permissions' => ['view', 'create', 'edit', 'manage', 'export']
            ],
            'support' => [
                'default_level' => 2,
                'default_permissions' => ['view', 'edit', 'export']
            ]
        ],

        // Configurações de notificação
        'notification_settings' => [
            'email_notifications' => true,
            'sms_notifications' => false,
            'system_notifications' => true,
            'notification_types' => [
                'new_user_registration',
                'permission_changes',
                'system_alerts',
                'department_updates'
            ]
        ],

        // Integrações externas
        'external_integrations' => [
            'enabled' => false,
            'available_integrations' => [
                'sicom' => [
                    'name' => 'SICOM - TCE-PR',
                    'description' => 'Sistema de Informações Contábeis e Fiscais do Setor Público Municipal',
                    'departments' => ['FAZENDA'],
                    'active' => false
                ],
                'siops' => [
                    'name' => 'SIOPS - Ministério da Saúde',
                    'description' => 'Sistema de Informações sobre Orçamentos Públicos em Saúde',
                    'departments' => ['FAZENDA'],
                    'active' => false
                ],
                'siope' => [
                    'name' => 'SIOPE - FNDE',
                    'description' => 'Sistema de Informações sobre Orçamentos Públicos em Educação',
                    'departments' => ['EDUCACAO', 'FAZENDA'],
                    'active' => false
                ]
            ]
        ]
    ],

    // =====================================
    // FUNÇÕES AUXILIARES PARA DEPARTAMENTOS
    // =====================================
    '_helper_functions' => [
        // Mapeamento de cores automáticas para novos departamentos
        'auto_colors' => [
            '#3498db', '#e74c3c', '#2ecc71', '#f39c12', '#9b59b6',
            '#1abc9c', '#34495e', '#e67e22', '#95a5a6', '#16a085',
            '#27ae60', '#2980b9', '#8e44ad', '#d35400', '#c0392b'
        ],

        // Ícones padrão por categoria
        'default_icons' => [
            'admin' => 'fas fa-crown',
            'department' => 'fas fa-building',
            'support' => 'fas fa-tools',
            'external' => 'fas fa-plug'
        ],

        // Slugs reservados (não podem ser usados)
        'reserved_slugs' => [
            'admin', 'system', 'api', 'dashboard', 'login', 'logout',
            'profile', 'settings', 'config', 'help', 'about', 'contact'
        ],

        // Departamentos que sempre devem estar ativos
        'required_departments' => ['ALL', 'TECNOLOGIA'],

        // Relacionamentos entre departamentos
        'department_relationships' => [
            'EDUCACAO' => ['RODOVIARIO'], // Educação usa transporte escolar
            'OBRAS' => ['FAZENDA'], // Obras depende do financeiro
            'MEIO_AMBIENTE' => ['FISCALIZACAO'], // Meio ambiente fiscaliza
            'ASSISTENCIA_SOCIAL' => ['FAZENDA'], // Assistência depende do orçamento
            'FAZENDA' => ['ALL'] // Fazenda interage com todos
        ]
    ]
];

/**
 * Funções auxiliares para trabalhar com departamentos
 */

/**
 * Retorna um departamento específico
 */
function getDepartment($slug) {
    $departments = require __DIR__ . '/departments.php';
    return $departments[$slug] ?? null;
}

/**
 * Retorna todos os departamentos ativos
 */
function getActiveDepartments() {
    $departments = require __DIR__ . '/departments.php';
    return array_filter($departments, function($dept, $key) {
        return !str_starts_with($key, '_') && ($dept['active'] ?? true);
    }, ARRAY_FILTER_USE_BOTH);
}

/**
 * Retorna departamentos por categoria
 */
function getDepartmentsByCategory($category) {
    $departments = getActiveDepartments();
    return array_filter($departments, function($dept) use ($category) {
        return ($dept['category'] ?? 'department') === $category;
    });
}

/**
 * Verifica se um usuário pode acessar um departamento
 */
function canAccessDepartment($userDepartment, $targetDepartment, $userLevel = 4) {
    // Administradores têm acesso a tudo
    if ($userLevel == 1 || $userDepartment === 'ALL') {
        return true;
    }
    
    // Usuário pode acessar seu próprio departamento
    if ($userDepartment === $targetDepartment) {
        return true;
    }
    
    // Verificar relacionamentos entre departamentos
    $departments = require __DIR__ . '/departments.php';
    $relationships = $departments['_helper_functions']['department_relationships'] ?? [];
    
    if (isset($relationships[$userDepartment])) {
        return in_array($targetDepartment, $relationships[$userDepartment]);
    }
    
    return false;
}

/**
 * Gera um slug único para um novo departamento
 */
function generateDepartmentSlug($name) {
    $departments = require __DIR__ . '/departments.php';
    $reserved = $departments['_helper_functions']['reserved_slugs'] ?? [];
    
    // Gerar slug base
    $slug = strtolower(trim($name));
    $slug = preg_replace('/[^a-z0-9\-]/', '_', $slug);
    $slug = preg_replace('/_+/', '_', $slug);
    $slug = trim($slug, '_');
    
    // Verificar se está reservado
    if (in_array($slug, $reserved)) {
        $slug .= '_dept';
    }
    
    // Verificar se já existe
    $counter = 1;
    $originalSlug = $slug;
    while (getDepartment($slug) !== null) {
        $slug = $originalSlug . '_' . $counter;
        $counter++;
    }
    
    return $slug;
}

/**
 * Atribui uma cor automática para um novo departamento
 */
function getNextAvailableColor() {
    $departments = require __DIR__ . '/departments.php';
    $autoColors = $departments['_helper_functions']['auto_colors'] ?? [];
    $usedColors = array_column(getActiveDepartments(), 'color');
    
    foreach ($autoColors as $color) {
        if (!in_array($color, $usedColors)) {
            return $color;
        }
    }
    
    // Se todas as cores estão em uso, retorna uma cor aleatória
    return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
}

/**
 * Valida os dados de um departamento
 */
function validateDepartmentData($data) {
    $departments = require __DIR__ . '/departments.php';
    $rules = $departments['_department_settings']['validation_rules'] ?? [];
    $errors = [];
    
    // Verificar campos obrigatórios
    foreach ($rules['required_fields'] ?? [] as $field) {
        if (empty($data[$field])) {
            $errors[] = "Campo '{$field}' é obrigatório.";
        }
    }
    
    // Verificar slug único
    if (!empty($data['slug']) && getDepartment($data['slug'])) {
        $errors[] = "Slug '{$data['slug']}' já está em uso.";
    }
    
    // Verificar padrão do slug
    if (!empty($data['slug']) && !preg_match($rules['slug_pattern'] ?? '/.*/', $data['slug'])) {
        $errors[] = "Slug deve conter apenas letras minúsculas, números e underscore.";
    }
    
    // Verificar padrão da cor
    if (!empty($data['color']) && !preg_match($rules['color_pattern'] ?? '/.*/', $data['color'])) {
        $errors[] = "Cor deve estar no formato hexadecimal (#RRGGBB).";
    }
    
    // Verificar comprimento do nome
    if (!empty($data['name']) && strlen($data['name']) > ($rules['max_name_length'] ?? 255)) {
        $errors[] = "Nome deve ter no máximo {$rules['max_name_length']} caracteres.";
    }
    
    // Verificar comprimento da descrição
    if (!empty($data['description']) && strlen($data['description']) > ($rules['max_description_length'] ?? 255)) {
        $errors[] = "Descrição deve ter no máximo {$rules['max_description_length']} caracteres.";
    }
    
    return $errors;
}

/**
 * Retorna as configurações de segurança para um departamento
 */
function getDepartmentSecuritySettings($departmentSlug) {
    $department = getDepartment($departmentSlug);
    if (!$department) {
        return null;
    }
    
    $securityLevel = $department['security_level'] ?? 'normal';
    $departments = require __DIR__ . '/departments.php';
    $securityLevels = $departments['_department_settings']['security_levels'] ?? [];
    
    return $securityLevels[$securityLevel] ?? $securityLevels['normal'];
}

/**
 * Retorna a hierarquia completa dos departamentos
 */
function getDepartmentHierarchy() {
    $departments = require __DIR__ . '/departments.php';
    return $departments['_department_settings']['hierarchy'] ?? [];
}
