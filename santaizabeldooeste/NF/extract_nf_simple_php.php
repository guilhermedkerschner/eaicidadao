<?php
function extractNFDataBasic($pdfPath) {
    // Múltiplos métodos de extração
    $methods = [
        'method1' => 'extractTextMethod1',
        'method2' => 'extractTextMethod2', 
        'method3' => 'extractTextMethod3',
        'method4' => 'extractTextMethod4'
    ];
    
    $text = '';
    $usedMethod = '';
    
    foreach ($methods as $methodName => $methodFunc) {
        $text = $methodFunc($pdfPath);
        if (!empty($text) && strlen($text) > 100) {
            $usedMethod = $methodName;
            break;
        }
    }
    
    if (empty($text)) {
        return ['error' => 'Não foi possível extrair texto do PDF com nenhum método'];
    }
    
    // Log do texto extraído para debug
    file_put_contents('extracted_text.log', "=== $usedMethod ===\n" . substr($text, 0, 1000) . "\n\n", FILE_APPEND);
    
    $data = extractDataFromText($text);
    $data['method_used'] = $usedMethod;
    $data['text_length'] = strlen($text);
    
    return $data;
}

// Método 1: Extração básica com regex melhorada
function extractTextMethod1($pdfPath) {
    $content = file_get_contents($pdfPath);
    if (!$content) return '';
    
    // Remove cabeçalho binário
    if (strpos($content, '%PDF') !== false) {
        $content = substr($content, strpos($content, '%PDF'));
    }
    
    $text = '';
    
    // Busca por strings entre parênteses (método comum no PDF)
    if (preg_match_all('/\(([^)]*)\)/', $content, $matches)) {
        $text = implode(' ', $matches[1]);
    }
    
    // Busca por strings entre colchetes
    if (strlen($text) < 100) {
        if (preg_match_all('/\[([^\]]*)\]/', $content, $matches)) {
            $text .= ' ' . implode(' ', $matches[1]);
        }
    }
    
    return $text;
}

// Método 2: Extração por streams de texto
function extractTextMethod2($pdfPath) {
    $content = file_get_contents($pdfPath);
    if (!$content) return '';
    
    $text = '';
    
    // Procura por objetos de texto
    if (preg_match_all('/BT\s*(.*?)\s*ET/s', $content, $matches)) {
        foreach ($matches[1] as $textBlock) {
            // Remove comandos PDF e mantém só texto
            $cleanText = preg_replace('/\/\w+\s+\d+(\.\d+)?\s+/', '', $textBlock);
            $cleanText = preg_replace('/\d+(\.\d+)?\s+\d+(\.\d+)?\s+Td\s*/', ' ', $cleanText);
            $cleanText = preg_replace('/\d+(\.\d+)?\s+TL\s*/', ' ', $cleanText);
            $text .= ' ' . $cleanText;
        }
    }
    
    return $text;
}

// Método 3: Extração de caracteres imprimíveis
function extractTextMethod3($pdfPath) {
    $content = file_get_contents($pdfPath);
    if (!$content) return '';
    
    // Remove caracteres binários e mantém só caracteres legíveis
    $text = preg_replace('/[^\x20-\x7E\x0A\x0D]/', ' ', $content);
    
    // Remove comandos PDF comuns
    $text = preg_replace('/\/[A-Za-z]+\s*/', ' ', $text);
    $text = preg_replace('/\d+\s+\d+\s+obj\s*/', ' ', $text);
    $text = preg_replace('/endobj\s*/', ' ', $text);
    $text = preg_replace('/stream\s*/', ' ', $text);
    $text = preg_replace('/endstream\s*/', ' ', $text);
    
    // Limpa espaços múltiplos
    $text = preg_replace('/\s+/', ' ', $text);
    
    return $text;
}

// Método 4: Busca por padrões específicos de NF-e
function extractTextMethod4($pdfPath) {
    $content = file_get_contents($pdfPath);
    if (!$content) return '';
    
    $text = '';
    
    // Busca especificamente por padrões de NF-e
    $patterns = [
        '/DANFE.*?ELETRONICA/s',
        '/NOTA FISCAL.*?ELETRONICA/s',
        '/DESTINATARIO.*?EMISSAO/s',
        '/CNPJ.*?\d{2}\.\d{3}\.\d{3}\/\d{4}\-\d{2}/s'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match_all($pattern, $content, $matches)) {
            foreach ($matches[0] as $match) {
                $cleanMatch = preg_replace('/[^\x20-\x7E\x0A\x0D]/', ' ', $match);
                $text .= ' ' . $cleanMatch;
            }
        }
    }
    
    return $text;
}

function extractDataFromText($text) {
    // Converte para maiúsculo para facilitar busca
    $textUpper = strtoupper($text);
    
    $data = [
        'numero_nota' => extractNumeroNota($text),
        'data_nota' => extractData($text),
        'nome_razao_social' => extractRazaoSocial($text),
        'cnpj' => extractCNPJ($text),
        'cpf' => extractCPF($text),
        'valor_nota' => extractValor($text),
        'tomador_nome' => extractTomador($text),
        'endereco' => extractEndereco($text),
        'cep' => extractCEP($text),
        'tipo_produto_servico' => extractTipo($text)
    ];
    
    return $data;
}

function extractNumeroNota($text) {
    $patterns = [
        '/N[°º][:\s]*(\d+)/i',
        '/NOTA[:\s]*(\d{3,10})/i',
        '/NF-E[:\s]*(\d+)/i',
        '/NUMERO[:\s]*(\d+)/i',
        '/(\d{6,10})/', // Números longos que podem ser nota
        '/SERIE.*?(\d+)/i'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $text, $matches)) {
            return $matches[1];
        }
    }
    return 'EXTRAIDO_' . time(); // Gera um número se não encontrar
}

function extractData($text) {
    $patterns = [
        '/(\d{2})[\/\-](\d{2})[\/\-](\d{4})/',
        '/(\d{4})[\/\-](\d{2})[\/\-](\d{2})/',
        '/DATA.*?(\d{2})[\/\-](\d{2})[\/\-](\d{4})/i',
        '/EMISSAO.*?(\d{2})[\/\-](\d{2})[\/\-](\d{4})/i'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $text, $matches)) {
            if (strlen($matches[1]) == 4) {
                // Formato YYYY-MM-DD
                return $matches[1] . '-' . $matches[2] . '-' . $matches[3];
            } else {
                // Formato DD-MM-YYYY
                return $matches[3] . '-' . $matches[2] . '-' . $matches[1];
            }
        }
    }
    return date('Y-m-d'); // Data atual se não encontrar
}

function extractCNPJ($text) {
    if (preg_match('/(\d{2}\.?\d{3}\.?\d{3}[\/\\]?\d{4}[\-]?\d{2})/', $text, $matches)) {
        return $matches[1];
    }
    return null;
}

function extractCPF($text) {
    if (preg_match('/(\d{3}\.?\d{3}\.?\d{3}[\-]?\d{2})/', $text, $matches)) {
        $nums = preg_replace('/[^0-9]/', '', $matches[1]);
        if (strlen($nums) == 11) {
            return $matches[1];
        }
    }
    return null;
}

function extractValor($text) {
    $patterns = [
        '/TOTAL.*?R?\$?\s*([\d.,]+)/i',
        '/VALOR.*?NOTA.*?R?\$?\s*([\d.,]+)/i',
        '/R\$\s*([\d.,]+)/i',
        '/([\d]+[.,]\d{2})(?=\s|$)/', // Padrão de valor com 2 decimais
        '/(\d{1,3}(?:\.\d{3})*,\d{2})/' // Formato brasileiro
    ];
    
    $valores = [];
    foreach ($patterns as $pattern) {
        if (preg_match_all($pattern, $text, $matches)) {
            foreach ($matches[1] as $valor) {
                // Converte para formato decimal
                $valorLimpo = str_replace('.', '', $valor); // Remove milhares
                $valorLimpo = str_replace(',', '.', $valorLimpo); // Vírgula vira ponto
                $valorNum = floatval($valorLimpo);
                if ($valorNum > 0 && $valorNum < 1000000) { // Valores razoáveis
                    $valores[] = $valorNum;
                }
            }
        }
    }
    
    if (!empty($valores)) {
        // Retorna o maior valor encontrado (provavelmente o total)
        return max($valores);
    }
    
    return 1.00; // Valor padrão se não encontrar
}

function extractRazaoSocial($text) {
    $patterns = [
        '/([A-Z][A-Z\s&.-]{5,50}\s+LTDA)/i',
        '/([A-Z][A-Z\s&.-]{5,50}\s+S[\/\\]A)/i',
        '/([A-Z][A-Z\s&.-]{5,50}\s+ME)/i',
        '/([A-Z][A-Z\s&.-]{5,50}\s+EIRELI)/i',
        '/RAZAO.*?SOCIAL[:\s]*([A-Z][A-Z\s&.-]+)/i',
        '/EMITENTE[:\s]*([A-Z][A-Z\s&.-]+)/i'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $text, $matches)) {
            $nome = trim($matches[1]);
            if (strlen($nome) > 5 && strlen($nome) < 100) {
                return $nome;
            }
        }
    }
    return 'EMPRESA NAO IDENTIFICADA';
}

function extractTomador($text) {
    $patterns = [
        '/DESTINATARIO[:\s]*([A-Z][A-Z\s]{5,50})/i',
        '/TOMADOR[:\s]*([A-Z][A-Z\s]{5,50})/i',
        '/CLIENTE[:\s]*([A-Z][A-Z\s]{5,50})/i',
        '/REMETENTE[:\s]*([A-Z][A-Z\s]{5,50})/i'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $text, $matches)) {
            $nome = trim($matches[1]);
            if (strlen($nome) > 5 && strlen($nome) < 100) {
                return $nome;
            }
        }
    }
    return 'CLIENTE NAO IDENTIFICADO';
}

function extractEndereco($text) {
    $patterns = [
        '/ENDERECO[:\s]*([A-Z][A-Za-z\s,.\-0-9]{10,100})/i',
        '/RUA[:\s]*([A-Z][A-Za-z\s,.\-0-9]{5,100})/i',
        '/LINHA[:\s]*([A-Z][A-Za-z\s,.\-0-9]{5,100})/i',
        '/AV[A-Z]*[:\s]*([A-Z][A-Za-z\s,.\-0-9]{5,100})/i'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $text, $matches)) {
            return trim($matches[1]);
        }
    }
    return null;
}

function extractCEP($text) {
    if (preg_match('/(\d{5}[\-.]?\d{3})/', $text, $matches)) {
        return $matches[1];
    }
    return null;
}

function extractTipo($text) {
    $textUpper = strtoupper($text);
    $serviceWords = ['SERVICO', 'PRESTACAO', 'MAO DE OBRA', 'CONSULTORIA', 'NFS-E', 'ISS'];
    
    foreach ($serviceWords as $word) {
        if (strpos($textUpper, $word) !== false) {
            return 'Serviço';
        }
    }
    return 'Produto';
}

function generateSQL($data) {
    function sqlEscape($value) {
        if ($value === null || $value === '') {
            return 'NULL';
        }
        return "'" . addslashes($value) . "'";
    }
    
    $sql = "INSERT INTO notas_fiscais (numero_nota, data_nota, nome_razao_social, cnpj, tipo_produto_servico, produto, servico, valor_nota, tomador_nome, tomador_cpf, tomador_endereco, tomador_cep) VALUES (";
    
    $sql .= sqlEscape($data['numero_nota']) . ", ";
    $sql .= sqlEscape($data['data_nota']) . ", ";
    $sql .= sqlEscape($data['nome_razao_social']) . ", ";
    $sql .= sqlEscape($data['cnpj']) . ", ";
    $sql .= sqlEscape($data['tipo_produto_servico']) . ", ";
    $sql .= "NULL, "; // produto
    $sql .= "NULL, "; // servico  
    $sql .= ($data['valor_nota'] ? $data['valor_nota'] : 'NULL') . ", ";
    $sql .= sqlEscape($data['tomador_nome']) . ", ";
    $sql .= sqlEscape($data['cpf']) . ", ";
    $sql .= sqlEscape($data['endereco']) . ", ";
    $sql .= sqlEscape($data['cep']);
    
    $sql .= ");";
    
    return $sql;
}

// Execução principal
if (php_sapi_name() === 'cli') {
    if (isset($argv[1])) {
        $pdfPath = $argv[1];
        
        if (!file_exists($pdfPath)) {
            echo "ERRO: Arquivo não encontrado: $pdfPath";
            exit(1);
        }
        
        $data = extractNFDataBasic($pdfPath);
        
        if (isset($data['error'])) {
            echo "ERRO: " . $data['error'];
            exit(1);
        }
        
        // Agora aceita qualquer extração, mesmo que mínima
        $sql = generateSQL($data);
        echo $sql;
        
        // Log para debug
        file_put_contents('extraction_log.txt', 
            "Arquivo: $pdfPath\n" . 
            "Método: " . ($data['method_used'] ?? 'desconhecido') . "\n" .
            "Tamanho texto: " . ($data['text_length'] ?? 0) . "\n" .
            "Dados: " . json_encode($data) . "\n\n", 
            FILE_APPEND
        );
        
    } else {
        echo "Uso: php extract_nf_simple_php.php <arquivo.pdf>";
    }
} else {
    echo "<h2>Extrator de NF - Teste</h2>";
    if (isset($_GET['test'])) {
        $testText = "DANFE NOTA FISCAL ELETRONICA 12345 DATA 15/06/2025 EMPRESA TESTE LTDA CNPJ 12.345.678/0001-90 DESTINATARIO CLIENTE TESTE VALOR TOTAL R$ 1.500,00";
        $data = extractDataFromText($testText);
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        echo "<p>SQL: " . generateSQL($data) . "</p>";
    } else {
        echo "<a href='?test=1'>Executar Teste</a>";
    }
}
?>