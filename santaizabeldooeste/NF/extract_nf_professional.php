<?php
function extractNFDataProfessional($pdfPath) {
    // Múltiplos métodos de extração mais robustos
    $text = '';
    $methods = [
        'extractWithPdfToText',
        'extractWithAdvancedRegex',
        'extractWithBinaryParsing',
        'extractWithStreamAnalysis'
    ];
    
    foreach ($methods as $method) {
        $text = $method($pdfPath);
        if (strlen($text) > 200) {
            break;
        }
    }
    
    if (strlen($text) < 50) {
        return ['error' => 'PDF muito complexo - texto insuficiente extraído'];
    }
    
    // Salva texto extraído para debug
    file_put_contents('debug_text_' . time() . '.txt', $text);
    
    return extractDataWithPrecision($text);
}

function extractWithPdfToText($pdfPath) {
    // Tenta usar pdftotext se disponível
    $output = shell_exec("pdftotext \"$pdfPath\" - 2>&1");
    if ($output && !stripos($output, 'not found') && !stripos($output, 'não é reconhecido')) {
        return $output;
    }
    return '';
}

function extractWithAdvancedRegex($pdfPath) {
    $content = file_get_contents($pdfPath);
    if (!$content) return '';
    
    $text = '';
    
    // Método 1: Extrai strings entre parênteses com decodificação
    if (preg_match_all('/\(([^)]+)\)/', $content, $matches)) {
        foreach ($matches[1] as $match) {
            // Decodifica escape sequences comuns do PDF
            $decoded = str_replace(['\\(', '\\)', '\\\\', '\\n', '\\r'], ['(', ')', '\\', "\n", "\r"], $match);
            $text .= ' ' . $decoded;
        }
    }
    
    // Método 2: Extrai texto de arrays de strings
    if (preg_match_all('/\[([^\]]+)\]/', $content, $matches)) {
        foreach ($matches[1] as $match) {
            // Remove comandos de posicionamento
            $clean = preg_replace('/\-?\d+\.?\d*\s+/', ' ', $match);
            $clean = str_replace(['(', ')'], '', $clean);
            $text .= ' ' . $clean;
        }
    }
    
    return $text;
}

function extractWithBinaryParsing($pdfPath) {
    $content = file_get_contents($pdfPath);
    if (!$content) return '';
    
    $text = '';
    
    // Busca por objetos de texto no PDF
    if (preg_match_all('/(\d+\s+\d+\s+obj.*?)endobj/s', $content, $objects)) {
        foreach ($objects[1] as $obj) {
            // Verifica se é um objeto de texto
            if (strpos($obj, '/Type/Font') === false && strpos($obj, 'stream') !== false) {
                // Extrai stream
                if (preg_match('/stream\s*(.*?)\s*endstream/s', $obj, $streamMatch)) {
                    $stream = $streamMatch[1];
                    
                    // Tenta decodificar stream simples
                    $decoded = '';
                    for ($i = 0; $i < strlen($stream); $i++) {
                        $char = $stream[$i];
                        if (ord($char) >= 32 && ord($char) <= 126) {
                            $decoded .= $char;
                        } elseif (ord($char) == 10 || ord($char) == 13) {
                            $decoded .= ' ';
                        }
                    }
                    $text .= ' ' . $decoded;
                }
            }
        }
    }
    
    return $text;
}

function extractWithStreamAnalysis($pdfPath) {
    $content = file_get_contents($pdfPath);
    if (!$content) return '';
    
    // Busca especificamente por comandos de texto do PDF
    $textCommands = [];
    
    // BT ... ET (Begin Text ... End Text)
    if (preg_match_all('/BT\s*(.*?)\s*ET/s', $content, $matches)) {
        foreach ($matches[1] as $textBlock) {
            $textCommands[] = $textBlock;
        }
    }
    
    $extractedText = '';
    foreach ($textCommands as $block) {
        // Processa comandos Tj e TJ
        if (preg_match_all('/\((.*?)\)\s*T[jJ]/', $block, $textMatches)) {
            foreach ($textMatches[1] as $text) {
                $extractedText .= ' ' . $text;
            }
        }
        
        // Processa arrays de texto
        if (preg_match_all('/\[(.*?)\]\s*TJ/', $block, $arrayMatches)) {
            foreach ($arrayMatches[1] as $array) {
                $cleanArray = preg_replace('/\-?\d+\.?\d*\s+/', ' ', $array);
                $cleanArray = str_replace(['(', ')'], '', $cleanArray);
                $extractedText .= ' ' . $cleanArray;
            }
        }
    }
    
    return $extractedText;
}

function extractDataWithPrecision($text) {
    // Normaliza o texto
    $text = normalizeText($text);
    
    $data = [
        'numero_nota' => extractNumeroNotaPreciso($text),
        'data_nota' => extractDataPrecisa($text),
        'nome_razao_social' => extractRazaoSocialPrecisa($text),
        'cnpj' => extractCNPJPreciso($text),
        'cpf' => extractCPFPreciso($text),
        'valor_nota' => extractValorPreciso($text),
        'tomador_nome' => extractTomadorPreciso($text),
        'endereco' => extractEnderecoPreciso($text),
        'cep' => extractCEPPreciso($text),
        'tipo_produto_servico' => extractTipoPreciso($text),
        'produto' => extractProdutoServico($text, 'produto'),
        'servico' => extractProdutoServico($text, 'servico')
    ];
    
    return $data;
}

function normalizeText($text) {
    // Remove caracteres especiais preservando acentos
    $text = preg_replace('/[^\x20-\x7E\xC0-\xFF]/', ' ', $text);
    // Normaliza espaços
    $text = preg_replace('/\s+/', ' ', $text);
    return trim($text);
}

function extractNumeroNotaPreciso($text) {
    $patterns = [
        '/N[°º]?\s*[:\.]?\s*(\d{6,12})/i',
        '/NOTA\s+FISCAL.*?N[°º]?\s*[:\.]?\s*(\d{6,12})/i',
        '/NF-E.*?N[°º]?\s*[:\.]?\s*(\d{6,12})/i',
        '/DANFE.*?N[°º]?\s*[:\.]?\s*(\d{6,12})/i',
        '/SÉRIE.*?(\d{3,12})/i',
        '/NÚMERO.*?(\d{6,12})/i'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $text, $matches)) {
            return $matches[1];
        }
    }
    
    // Busca por sequências numéricas características de NF
    if (preg_match_all('/\b(\d{6,12})\b/', $text, $matches)) {
        foreach ($matches[1] as $num) {
            if (strlen($num) >= 6 && strlen($num) <= 12) {
                return $num;
            }
        }
    }
    
    return null;
}

function extractDataPrecisa($text) {
    $patterns = [
        '/DATA\s+(?:DA\s+)?EMISS[ÃA]O[:\s]*(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/i',
        '/EMISS[ÃA]O[:\s]*(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/i',
        '/DATA[:\s]*(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/i',
        '/(\d{1,2})[\/\-](\d{1,2})[\/\-](20\d{2})/i'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $text, $matches)) {
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $year = $matches[3];
            
            if ($month <= 12 && $day <= 31) {
                return "$year-$month-$day";
            }
        }
    }
    
    return null;
}

function extractRazaoSocialPrecisa($text) {
    $patterns = [
        // Empresas com LTDA
        '/([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõç\s&\.-]{8,80})\s+LTDA(?:\s+ME)?/i',
        // Empresas com S/A
        '/([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõç\s&\.-]{8,80})\s+S[\/]A/i',
        // Empresas com ME
        '/([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõç\s&\.-]{8,80})\s+ME\b/i',
        // Por contexto
        '/RAZ[ÃA]O\s+SOCIAL[:\s]*([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõç\s&\.-]+)/i',
        '/EMITENTE[:\s]*([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõç\s&\.-]+)/i',
        '/NOME[:\s]*([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõç\s&\.-]+)/i'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $text, $matches)) {
            $nome = trim($matches[1]);
            // Remove lixo comum
            $nome = preg_replace('/\s*CNPJ.*$/i', '', $nome);
            $nome = preg_replace('/\s*CPF.*$/i', '', $nome);
            if (strlen($nome) > 5 && strlen($nome) < 100) {
                return strtoupper($nome);
            }
        }
    }
    
    return null;
}

function extractCNPJPreciso($text) {
    if (preg_match('/(\d{2}\.?\d{3}\.?\d{3}[\/\\]?\d{4}[\-]?\d{2})/', $text, $matches)) {
        $cnpj = $matches[1];
        // Normaliza formato
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        if (strlen($cnpj) == 14) {
            return substr($cnpj, 0, 2) . '.' . substr($cnpj, 2, 3) . '.' . substr($cnpj, 5, 3) . '/' . substr($cnpj, 8, 4) . '-' . substr($cnpj, 12, 2);
        }
    }
    return null;
}

function extractCPFPreciso($text) {
    if (preg_match('/(?:CPF[:\s]*)?(\d{3}\.?\d{3}\.?\d{3}[\-]?\d{2})/', $text, $matches)) {
        $cpf = preg_replace('/[^0-9]/', '', $matches[1]);
        if (strlen($cpf) == 11) {
            return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
        }
    }
    return null;
}

function extractValorPreciso($text) {
    $patterns = [
        '/VALOR\s+TOTAL\s+(?:DA\s+)?NOTA[:\s]*R?\$?\s*([\d.,]+)/i',
        '/TOTAL\s+(?:DA\s+)?NOTA[:\s]*R?\$?\s*([\d.,]+)/i',
        '/VALOR\s+TOTAL[:\s]*R?\$?\s*([\d.,]+)/i',
        '/TOTAL[:\s]*R?\$?\s*([\d.,]+)/i',
        '/R\$\s*([\d.,]+)(?=\s|$)/i'
    ];
    
    $valores = [];
    foreach ($patterns as $pattern) {
        if (preg_match_all($pattern, $text, $matches)) {
            foreach ($matches[1] as $valor) {
                $valorLimpo = normalizeValor($valor);
                if ($valorLimpo > 0) {
                    $valores[] = $valorLimpo;
                }
            }
        }
    }
    
    if (!empty($valores)) {
        // Retorna o maior valor (provavelmente o total)
        return max($valores);
    }
    
    return null;
}

function normalizeValor($valor) {
    // Remove espaços
    $valor = trim($valor);
    
    // Se tem vírgula como decimal
    if (preg_match('/\d+,\d{2}$/', $valor)) {
        $valor = str_replace('.', '', $valor); // Remove pontos de milhares
        $valor = str_replace(',', '.', $valor); // Vírgula vira ponto decimal
    }
    
    return floatval($valor);
}

function extractTomadorPreciso($text) {
    $patterns = [
        '/DESTINAT[ÁA]RIO[\/\s]*REMETENTE[:\s]*([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõç\s]+)/i',
        '/TOMADOR[:\s]*([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõç\s]+)/i',
        '/CLIENTE[:\s]*([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõç\s]+)/i'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $text, $matches)) {
            $nome = trim($matches[1]);
            $nome = preg_replace('/\s*CNPJ.*$/i', '', $nome);
            $nome = preg_replace('/\s*CPF.*$/i', '', $nome);
            if (strlen($nome) > 3 && strlen($nome) < 100) {
                return strtoupper($nome);
            }
        }
    }
    
    return null;
}

function extractEnderecoPreciso($text) {
    $patterns = [
        '/ENDERE[ÇC]O[:\s]*([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõç\s,.\-0-9]+)/i',
        '/RUA[:\s]*([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõç\s,.\-0-9]+)/i',
        '/LINHA[:\s]*([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõç\s,.\-0-9]+)/i',
        '/AVENIDA[:\s]*([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõç\s,.\-0-9]+)/i'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $text, $matches)) {
            $endereco = trim($matches[1]);
            if (strlen($endereco) > 5 && strlen($endereco) < 200) {
                return $endereco;
            }
        }
    }
    
    return null;
}

function extractCEPPreciso($text) {
    if (preg_match('/CEP[:\s]*(\d{5}[\-.]?\d{3})/', $text, $matches)) {
        return $matches[1];
    }
    if (preg_match('/(\d{5}[\-.]?\d{3})/', $text, $matches)) {
        return $matches[1];
    }
    return null;
}

function extractTipoPreciso($text) {
    $textUpper = strtoupper($text);
    $serviceIndicators = [
        'SERVI[ÇC]O', 'PRESTA[ÇC][ÃA]O', 'M[ÃA]O\s+DE\s+OBRA', 
        'CONSULTORIA', 'NFS-E', 'ISS', 'ISSQN'
    ];
    
    foreach ($serviceIndicators as $indicator) {
        if (preg_match("/$indicator/", $textUpper)) {
            return 'Serviço';
        }
    }
    return 'Produto';
}

function extractProdutoServico($text, $tipo) {
    $patterns = [
        '/DESCRI[ÇC][ÃA]O[:\s]*([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõç\s,.\-0-9()\/]+)/i',
        '/PRODUTO[:\s]*([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõç\s,.\-0-9()\/]+)/i',
        '/SERVI[ÇC]O[:\s]*([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõç\s,.\-0-9()\/]+)/i'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $text, $matches)) {
            $descricao = trim($matches[1]);
            if (strlen($descricao) > 3 && strlen($descricao) < 500) {
                return $descricao;
            }
        }
    }
    
    return null;
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
    $sql .= sqlEscape($data['produto']) . ", ";
    $sql .= sqlEscape($data['servico']) . ", ";
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
        
        $data = extractNFDataProfessional($pdfPath);
        
        if (isset($data['error'])) {
            echo "ERRO: " . $data['error'];
            exit(1);
        }
        
        $sql = generateSQL($data);
        echo $sql;
        
    } else {
        echo "Uso: php extract_nf_professional.php <arquivo.pdf>";
    }
}
?>