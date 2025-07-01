<?php
require_once 'vendor/autoload.php'; // Se usar Composer
// OU baixe a biblioteca manualmente

function extractNFDataPHP($pdfPath) {
    try {
        // Usa a biblioteca Smalot\PdfParser (PHP puro)
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($pdfPath);
        $text = $pdf->getText();
        
        // Patterns de extração
        $patterns = [
            'numero_nota' => '/N[°º]\s*(\d+)/i',
            'data_nota' => '/(\d{2}[\/\-]\d{2}[\/\-]\d{4})/',
            'cnpj' => '/(\d{2}\.\d{3}\.\d{3}\/\d{4}\-\d{2})/',
            'valor_total' => '/(?:total|valor).*?R\$\s*([\d.,]+)/i',
            'razao_social' => '/(?:razão social|nome).*?([A-Z][A-Z\s&.-]+)/i'
        ];
        
        $data = [];
        foreach ($patterns as $field => $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $data[$field] = trim($matches[1]);
            }
        }
        
        return $data;
        
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

// Uso
if (isset($argv[1])) {
    $data = extractNFDataPHP($argv[1]);
    if (isset($data['error'])) {
        echo "ERRO: " . $data['error'];
    } else {
        // Gera SQL
        $sql = "INSERT INTO notas_fiscais (numero_nota, data_nota, nome_razao_social, cnpj, valor_nota) VALUES ('" . 
               ($data['numero_nota'] ?? 'NULL') . "', '" . 
               ($data['data_nota'] ?? 'NULL') . "', '" . 
               ($data['razao_social'] ?? 'NULL') . "', '" . 
               ($data['cnpj'] ?? 'NULL') . "', '" . 
               ($data['valor_total'] ?? 'NULL') . "');";
        echo $sql;
    }
}
?>