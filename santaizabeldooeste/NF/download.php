<?php
if (isset($_GET['file'])) {
    $filename = basename($_GET['file']);
    $filepath = 'downloads/' . $filename;
    
    if (file_exists($filepath)) {
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
    } else {
        echo "Arquivo não encontrado.";
    }
} else {
    echo "Parâmetro de arquivo não especificado.";
}
?>