<?php
/**
 * Configuração Central dos Módulos do Sistema
 * Prefeitura Municipal de Santa Izabel do Oeste
 * 
 * COMO ADICIONAR UM NOVO MÓDULO:
 * 1. Copie a estrutura de um módulo existente similar
 * 2. Defina um 'key' único para o módulo
 * 3. Configure as informações básicas em 'info'
 * 4. Defina os acessos em 'access'
 * 5. Configure o menu em 'menu'
 * 6. A cor será atribuída automaticamente pela categoria
 */

// Carregar configurações de tema
$theme = require_once 'theme.php';

return [
    // =====================================
    // MÓDULOS DO SISTEMA
    // =====================================
    'dashboard' => [
        'info' => [
            'name' => 'Dashboard',
            'description' => 'Painel principal do sistema',
            'icon' => 'fas fa-tachometer-alt',
            'category' => 'system',
            'order' => 0
        ],
        'access' => [
            'departments' => ['ALL'],
            'min_level' => 4,
            'permissions' => ['view']
        ],
        'menu' => [
            'parent' => false,
            'show_in_sidebar' => true,
            'submenu' => []
        ],
        'files' => [
            'main' => 'dashboard.php'
        ]
    ],

    // =====================================
    // MÓDULOS ADMINISTRATIVOS
    // =====================================
    'admin' => [
        'info' => [
            'name' => 'Administração',
            'description' => 'Administração geral do sistema',
            'icon' => 'fas fa-shield-alt',
            'category' => 'admin',
            'order' => 1
        ],
        'access' => [
            'departments' => ['ALL'],
            'min_level' => 1, // Apenas administradores
            'permissions' => ['view', 'create', 'edit', 'delete', 'manage']
        ],
        'menu' => [
            'parent' => true,
            'show_in_sidebar' => true,
            'submenu' => [
                'users' => [
                    'name' => 'Gerenciar Usuários',
                    'icon' => 'fas fa-users-cog',
                    'files' => [
                        'list' => 'lista_usuarios.php',
                        'add' => 'adicionar_usuario.php',
                        'permissions' => 'permissoes.php'
                    ]
                ],
                'reports' => [
                    'name' => 'Relatórios Gerais',
                    'icon' => 'fas fa-chart-pie',
                    'files' => [
                        'main' => 'relatorios_gerais.php'
                    ]
                ],
                'system' => [
                    'name' => 'Configurações',
                    'icon' => 'fas fa-cogs',
                    'files' => [
                        'main' => 'configuracoes.php'
                    ]
                ]
            ]
        ]
    ],

    // =====================================
    // MÓDULOS DEPARTAMENTAIS
    // (As cores serão atribuídas automaticamente em sequência)
    // =====================================
    'agricultura' => [
        'info' => [
            'name' => 'Agricultura',
            'description' => 'Gestão de projetos e programas agrícolas',
            'icon' => 'fas fa-leaf',
            'category' => 'department',
            'order' => 10
        ],
        'access' => [
            'departments' => ['AGRICULTURA', 'ALL'],
            'min_level' => 2,
            'permissions' => ['view', 'create', 'edit', 'delete', 'manage']
        ],
        'menu' => [
            'parent' => true,
            'show_in_sidebar' => true,
            'submenu' => [
                'projects' => [
                    'name' => 'Projetos',
                    'icon' => 'fas fa-seedling',
                    'files' => ['main' => 'agricultura_projetos.php']
                ],
                'programs' => [
                    'name' => 'Programas',
                    'icon' => 'fas fa-tractor',
                    'files' => ['main' => 'agricultura_programas.php']
                ],
                'reports' => [
                    'name' => 'Relatórios',
                    'icon' => 'fas fa-chart-line',
                    'files' => ['main' => 'agricultura_relatorios.php']
                ]
            ]
        ]
    ],

    'assistencia_social' => [
        'info' => [
            'name' => 'Assistência Social',
            'description' => 'Programas sociais e habitacionais',
            'icon' => 'fas fa-hands-helping',
            'category' => 'department',
            'order' => 11
        ],
        'access' => [
            'departments' => ['ASSISTENCIA_SOCIAL', 'ALL'],
            'min_level' => 2,
            'permissions' => ['view', 'create', 'edit', 'delete', 'manage']
        ],
        'menu' => [
            'parent' => true,
            'show_in_sidebar' => true,
            'submenu' => [
                'attendance' => [
                    'name' => 'Atendimentos',
                    'icon' => 'fas fa-user-friends',
                    'files' => ['main' => 'assistencia_atendimentos.php']
                ],
                'benefits' => [
                    'name' => 'Benefícios',
                    'icon' => 'fas fa-hand-holding-heart',
                    'files' => ['main' => 'assistencia_beneficios.php']
                ],
                'housing' => [
                    'name' => 'Programas Habitacionais',
                    'icon' => 'fas fa-home',
                    'files' => [
                        'main' => 'assistencia_habitacao.php',
                        'view' => 'visualizar_cadastro_habitacao.php'
                    ]
                ],
                'reports' => [
                    'name' => 'Relatórios',
                    'icon' => 'fas fa-chart-bar',
                    'files' => ['main' => 'assistencia_relatorios.php']
                ]
            ]
        ]
    ],

    'educacao' => [
        'info' => [
            'name' => 'Educação',
            'description' => 'Gestão escolar e educacional',
            'icon' => 'fas fa-graduation-cap',
            'category' => 'department',
            'order' => 12
        ],
        'access' => [
            'departments' => ['EDUCACAO', 'ALL'],
            'min_level' => 2,
            'permissions' => ['view', 'create', 'edit', 'delete', 'manage']
        ],
        'menu' => [
            'parent' => true,
            'show_in_sidebar' => true,
            'submenu' => [
                'schools' => [
                    'name' => 'Escolas',
                    'icon' => 'fas fa-school',
                    'files' => ['main' => 'educacao_escolas.php']
                ],
                'teachers' => [
                    'name' => 'Professores',
                    'icon' => 'fas fa-chalkboard-teacher',
                    'files' => ['main' => 'educacao_professores.php']
                ],
                'students' => [
                    'name' => 'Alunos',
                    'icon' => 'fas fa-user-graduate',
                    'files' => ['main' => 'educacao_alunos.php']
                ],
                'reports' => [
                    'name' => 'Relatórios',
                    'icon' => 'fas fa-chart-line',
                    'files' => ['main' => 'educacao_relatorios.php']
                ]
            ]
        ]
    ],

    'fazenda' => [
        'info' => [
            'name' => 'Fazenda',
            'description' => 'Gestão financeira e orçamentária',
            'icon' => 'fas fa-money-bill-wave',
            'category' => 'department',
            'order' => 13
        ],
        'access' => [
            'departments' => ['FAZENDA', 'ALL'],
            'min_level' => 2,
            'permissions' => ['view', 'create', 'edit', 'delete', 'manage']
        ],
        'menu' => [
            'parent' => true,
            'show_in_sidebar' => true,
            'submenu' => [
                'budget' => [
                    'name' => 'Orçamento',
                    'icon' => 'fas fa-calculator',
                    'files' => ['main' => 'fazenda_orcamento.php']
                ],
                'revenue' => [
                    'name' => 'Receitas',
                    'icon' => 'fas fa-coins',
                    'files' => ['main' => 'fazenda_receitas.php']
                ],
                'expenses' => [
                    'name' => 'Despesas',
                    'icon' => 'fas fa-credit-card',
                    'files' => ['main' => 'fazenda_despesas.php']
                ],
                'reports' => [
                    'name' => 'Relatórios',
                    'icon' => 'fas fa-chart-pie',
                    'files' => ['main' => 'fazenda_relatorios.php']
                ]
            ]
        ]
    ],

    // =====================================
    // EXEMPLO DE COMO ADICIONAR UM NOVO MÓDULO
    // =====================================
    /*
    'novo_modulo' => [
        'info' => [
            'name' => 'Nome do Novo Módulo',
            'description' => 'Descrição do que o módulo faz',
            'icon' => 'fas fa-icone-escolhido',
            'category' => 'department', // ou 'admin', 'system', 'user', etc.
            'order' => 50 // ordem de exibição
        ],
        'access' => [
            'departments' => ['NOVO_DEPARTAMENTO', 'ALL'], // ou departamentos específicos
            'min_level' => 2, // nível mínimo de acesso
            'permissions' => ['view', 'create', 'edit', 'delete', 'manage']
        ],
        'menu' => [
            'parent' => true, // true se tem submenu, false se é item único
            'show_in_sidebar' => true,
            'submenu' => [
                'funcionalidade1' => [
                    'name' => 'Nome da Funcionalidade',
                    'icon' => 'fas fa-icone',
                    'files' => ['main' => 'arquivo_principal.php']
                ],
                // Adicione mais funcionalidades conforme necessário
            ]
        ]
    ],
    */

    // =====================================
    // MÓDULOS DE USUÁRIO
    // =====================================
    'profile' => [
        'info' => [
            'name' => 'Meu Perfil',
            'description' => 'Configurações pessoais do usuário',
            'icon' => 'fas fa-user-cog',
            'category' => 'user',
            'order' => 100
        ],
        'access' => [
            'departments' => ['ALL'],
            'min_level' => 4,
            'permissions' => ['view', 'edit']
        ],
        'menu' => [
            'parent' => false,
            'show_in_sidebar' => true,
            'submenu' => []
        ],
        'files' => [
            'main' => 'perfil.php'
        ]
    ]
];