<?php
if (extension_loaded('gd')) {
    echo "✅ GD Extension está habilitada!";
    print_r(gd_info());
} else {
    echo "❌ GD Extension NÃO está habilitada!";
}
?>