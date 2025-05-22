<?php
/**
 * Footer component
 * Sistema Eai Cidadão! - Prefeitura de Santa Izabel do Oeste
 */

// Base URL - ajuste conforme a estrutura do seu projeto
$base_url = isset($base_url) ? $base_url : "..";

// Definir caminho para o JS
$additional_scripts = isset($js_includes) ? $js_includes : [];
?>

    <footer>
        &copy; <?php echo date('Y'); ?> Prefeitura Municipal de Santa Izabel do Oeste. Todos os direitos reservados.
    </footer>

    <!-- Scripts base -->
    <script src="<?php echo $base_url; ?>/assets/js/main.js"></script>
    
    <!-- Scripts específicos adicionais -->
    <?php foreach ($additional_scripts as $script): ?>
    <script src="<?php echo $base_url; ?>/assets/js/<?php echo $script; ?>"></script>
    <?php endforeach; ?>

    <!-- Script para funcionalidade do dropdown do usuário -->
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const userButton = document.querySelector('.user-button');
        const userDropdown = document.querySelector('.user-dropdown');
        
        if (userButton && userDropdown) {
            userButton.addEventListener('click', function() {
                userDropdown.classList.toggle('show');
            });
            
            // Fecha o dropdown quando clicar fora dele
            document.addEventListener('click', function(event) {
                if (!userButton.contains(event.target) && !userDropdown.contains(event.target)) {
                    userDropdown.classList.remove('show');
                }
            });
        }
    });
    </script>
</body>
</html>