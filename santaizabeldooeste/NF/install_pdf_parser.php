<?php
// install_pdf_parser.php
echo "Baixando biblioteca PDF Parser...\n";

// Cria pasta vendor se não existir
if (!file_exists('vendor')) {
    mkdir('vendor', 0755, true);
}

// Baixa o arquivo ZIP da biblioteca
$url = 'https://github.com/smalot/pdfparser/archive/refs/heads/master.zip';
$zipFile = 'pdfparser.zip';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$zipContent = curl_exec($ch);
curl_close($ch);

if ($zipContent) {
    file_put_contents($zipFile, $zipContent);
    echo "Arquivo baixado com sucesso!\n";
    
    // Extrai o ZIP (método simples)
    $zip = new ZipArchive;
    if ($zip->open($zipFile) === TRUE) {
        $zip->extractTo('vendor/');
        $zip->close();
        echo "Biblioteca extraída!\n";
        unlink($zipFile);
    } else {
        echo "Erro ao extrair arquivo\n";
    }
} else {
    echo "Erro ao baixar biblioteca\n";
}

echo "Instalação concluída!\n";
?>