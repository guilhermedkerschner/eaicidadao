<?php
/**
 * Gerenciador de Menus e Permissões
 * Classe responsável por carregar, filtrar e gerar menus baseado nas permissões do usuário
 */

class MenuManager {
    private $modules;
    private $permissions;
    private $theme;
    private $departments;
    private $userSession;
    private $departmentColorIndex = 0;

    public function __construct($userSession = null) {
        $this->modules = require_once __DIR__ . '/../config/modules.php';
        $this->permissions = require_once __DIR__ . '/../config/permissions.php';
        $this->theme = require_once __DIR__ . '/../config/theme.php';
        $this->departments = require_once __DIR__ . '/../config/departments.php';
        $this->userSession = $userSession;
        
        // Atribuir cores automaticamente aos módulos departamentais
        $this->assignDepartmentColors();
    }

    /**
     * Atribui cores automaticamente aos módulos departamentais
     */
    private function assignDepartmentColors() {
        $departmentColorIndex = 1;
        
        foreach ($this->modules as $key => &$module) {
            if ($module['info']['category'] === 'department') {
                $colorKey = 'department_' . $departmentColorIndex;
                $module['info']['color'] = $this->theme['colors'][$colorKey] ?? $this->theme['colors']['department_1'];
                $departmentColorIndex++;
                
                // Reset se passar do limite de cores disponíveis
                if ($departmentColorIndex > 11) {
                    $departmentColorIndex = 1;
                }
            } else {
                // Atribuir cor baseada na categoria
                $category = $module['info']['category'];
                $module['info']['color'] = $this->theme['category_colors'][$category] ?? $this->theme['colors']['secondary'];
            }
        }
    }

    /**
     * Retorna todos os módulos disponíveis para o usuário atual
     */
    public function getAvailableModules() {
        if (!$this->userSession) {
            return [];
        }

        $availableModules = [];
        $userLevel = $this->userSession['usuario_nivel_id'] ?? 4;
        $userDepartment = strtoupper($this->userSession['usuario_departamento'] ?? '');
        $isAdmin = ($userLevel == 1);

        foreach ($this->modules as $key => $module) {
            // Verificar nível mínimo
            if ($userLevel > $module['access']['min_level']) {
                continue;
            }

            // Verificar departamento (admin tem acesso a tudo)
            if (!$isAdmin) {
                $allowedDepartments = $module['access']['departments'];
                if (!in_array('ALL', $allowedDepartments) && !in_array($userDepartment, $allowedDepartments)) {
                    continue;
                }
            }

            $availableModules[$key] = $module;
        }

        // Ordenar por ordem definida
        uasort($availableModules, function($a, $b) {
            return $a['info']['order'] <=> $b['info']['order'];
        });

        return $availableModules;
    }

    /**
     * Gera o HTML da sidebar com base nos módulos disponíveis
     */
    public function generateSidebar($currentPage = '') {
        $modules = $this->getAvailableModules();
        $isAdmin = ($this->userSession['usuario_nivel_id'] ?? 4) == 1;
        
        $html = '<ul class="menu">';
        
        // Agrupar módulos por categoria
        $groupedModules = $this->groupModulesByCategory($modules);
        
        foreach ($groupedModules as $category => $categoryModules) {
            if (!empty($categoryModules)) {
                // Adicionar separador e título da categoria (exceto para o primeiro)
                if ($category !== 'system') {
                    $html .= '<div class="menu-separator"></div>';
                    $html .= '<div class="menu-category">' . $this->getCategoryName($category) . '</div>';
                }
                
                foreach ($categoryModules as $key => $module) {
                    $html .= $this->generateMenuItemHTML($key, $module, $currentPage);
                }
            }
        }
        
        // Adicionar itens especiais do usuário
        $html .= '<div class="menu-separator"></div>';
        $html .= $this->generateUserMenuItems();
        
        $html .= '</ul>';
        
        return $html;
    }

    /**
     * Agrupa módulos por categoria
     */
    private function groupModulesByCategory($modules) {
        $grouped = [
            'system' => [],
            'admin' => [],
            'department' => [],
            'user' => []
        ];
        
        foreach ($modules as $key => $module) {
            $category = $module['info']['category'] ?? 'department';
            $grouped[$category][$key] = $module;
        }
        
        return $grouped;
    }

    /**
     * Retorna o nome da categoria para exibição
     */
    private function getCategoryName($category) {
        $names = [
            'admin' => 'Administração',
            'department' => 'Departamentos',
            'user' => 'Usuário',
            'report' => 'Relatórios',
            'integration' => 'Integrações'
        ];
        
        return $names[$category] ?? ucfirst($category);
    }

    /**
     * Gera HTML para um item de menu
     */
    private function generateMenuItemHTML($key, $module, $currentPage) {
        $info = $module['info'];
        $menu = $module['menu'];
        $isActive = $this->isModuleActive($key, $currentPage);
        $hasSubmenu = $menu['parent'] && !empty($menu['submenu']);
        
        $html = '<li class="menu-item' . ($isActive && $hasSubmenu ? ' open' : '') . '">';
        
        if ($hasSubmenu) {
            // Item com submenu
            $html .= '<a href="#" class="menu-link' . ($isActive ? ' active' : '') . '">';
            $html .= '<span class="menu-icon"><i class="' . $info['icon'] . '"></i></span>';
            $html .= '<span class="menu-text">' . $info['name'] . '</span>';
            $html .= '<span class="arrow"><i class="fas fa-chevron-right"></i></span>';
            $html .= '</a>';
            
            // Submenu
            $html .= '<ul class="submenu">';
            foreach ($menu['submenu'] as $subKey => $subItem) {
                $subFile = $subItem['files']['main'] ?? '';
                $subIsActive = $this->isPageActive($subFile, $currentPage);
                
                $html .= '<li>';
                $html .= '<a href="' . $subFile . '" class="submenu-link' . ($subIsActive ? ' active' : '') . '">';
                $html .= $subItem['name'];
                $html .= '</a>';
                $html .= '</li>';
            }
            $html .= '</ul>';
        } else {
            // Item simples
            $mainFile = $module['files']['main'] ?? '#';
            $html .= '<a href="' . $mainFile . '" class="menu-link' . ($isActive ? ' active' : '') . '">';
            $html .= '<span class="menu-icon"><i class="' . $info['icon'] . '"></i></span>';
            $html .= '<span class="menu-text">' . $info['name'] . '</span>';
            $html .= '</a>';
        }
        
        $html .= '</li>';
        
        return $html;
    }

    /**
     * Gera itens especiais do menu do usuário
     */
    private function generateUserMenuItems() {
        $html = '<li class="menu-item">';
        $html .= '<a href="../controller/logout_system.php" class="menu-link">';
        $html .= '<span class="menu-icon"><i class="fas fa-sign-out-alt"></i></span>';
        $html .= '<span class="menu-text">Sair</span>';
        $html .= '</a>';
        $html .= '</li>';
        
        return $html;
    }

    /**
     * Verifica se um módulo está ativo
     */
    private function isModuleActive($moduleKey, $currentPage) {
        $module = $this->modules[$moduleKey] ?? null;
        if (!$module) return false;
        
        // Verificar arquivo principal
        if (isset($module['files']['main']) && $this->isPageActive($module['files']['main'], $currentPage)) {
            return true;
        }
        
        // Verificar submenu
        if (isset($module['menu']['submenu'])) {
            foreach ($module['menu']['submenu'] as $subItem) {
                if (isset($subItem['files'])) {
                    foreach ($subItem['files'] as $file) {
                        if ($this->isPageActive($file, $currentPage)) {
                            return true;
                        }
                    }
                }
            }
        }
        
        return false;
    }

    /**
     * Verifica se uma página está ativa
     */
    private function isPageActive($file, $currentPage) {
        return basename($file) === basename($currentPage);
    }

    /**
     * Gera breadcrumb para a página atual
     */
    public function generateBreadcrumb($currentPage) {
        $breadcrumb = ['Dashboard' => 'dashboard.php'];
        
        foreach ($this->modules as $key => $module) {
            if ($this->isModuleActive($key, $currentPage)) {
                $breadcrumb[$module['info']['name']] = $module['files']['main'] ?? '#';
                
                // Verificar se é uma subpágina
                if (isset($module['menu']['submenu'])) {
                    foreach ($module['menu']['submenu'] as $subKey => $subItem) {
                        if (isset($subItem['files'])) {
                            foreach ($subItem['files'] as $file) {
                                if ($this->isPageActive($file, $currentPage)) {
                                    $breadcrumb[$subItem['name']] = $file;
                                    break 2;
                                }
                            }
                        }
                    }
                }
                break;
            }
        }
        
        return $breadcrumb;
    }

    /**
     * Retorna as cores do tema atual baseado no usuário
     */
    public function getThemeColors() {
        $isAdmin = ($this->userSession['usuario_nivel_id'] ?? 4) == 1;
        $userDepartment = strtoupper($this->userSession['usuario_departamento'] ?? '');
        
        if ($isAdmin) {
            return [
                'primary' => $this->theme['colors']['admin'],
                'title' => 'Administração Geral'
            ];
        } elseif (isset($this->departments[$userDepartment])) {
            return [
                'primary' => $this->departments[$userDepartment]['color'],
                'title' => $this->departments[$userDepartment]['name']
            ];
        }
        
        return [
            'primary' => $this->theme['colors']['secondary'],
            'title' => 'Sistema'
        ];
    }
}