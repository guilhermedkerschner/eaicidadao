<?php
/**
 * Funções auxiliares para gerenciamento de fotos de usuários
 * Arquivo: lib/FotoUtils.php
 * Sistema Eai Cidadão! - Prefeitura de Santa Izabel do Oeste
 */

class FotoUtils {
    
    private static $diretorioUpload = '../uploads/fotos_usuarios/';
    private static $tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png'];
    private static $tamanhoMaximo = 2097152; // 2MB
    private static $dimensoesMaximas = ['width' => 400, 'height' => 400];
    
    /**
     * Obter o caminho da foto do usuário
     * @param string $nomeArquivo Nome do arquivo da foto
     * @return string Caminho completo da foto
     */
    public static function getCaminhoFoto($nomeArquivo) {
        if (empty($nomeArquivo)) {
            return null;
        }
        
        return self::$diretorioUpload . $nomeArquivo;
    }
    
    /**
     * Obter URL da foto para exibição no browser
     * @param string $nomeArquivo Nome do arquivo da foto
     * @return string URL da foto
     */
    public static function getUrlFoto($nomeArquivo) {
        if (empty($nomeArquivo)) {
            return null;
        }
        
        return './uploads/fotos_usuarios/' . $nomeArquivo;
    }
    
    /**
     * Verificar se uma foto existe
     * @param string $nomeArquivo Nome do arquivo da foto
     * @return bool True se a foto existe
     */
    public static function fotoExiste($nomeArquivo) {
        if (empty($nomeArquivo)) {
            return false;
        }
        
        $caminho = self::getCaminhoFoto($nomeArquivo);
        return file_exists($caminho);
    }
    
    /**
     * Remover foto do servidor
     * @param string $nomeArquivo Nome do arquivo da foto
     * @return bool True se removido com sucesso
     */
    public static function removerFoto($nomeArquivo) {
        if (empty($nomeArquivo)) {
            return false;
        }
        
        $caminho = self::getCaminhoFoto($nomeArquivo);
        
        if (file_exists($caminho)) {
            return unlink($caminho);
        }
        
        return true; // Se não existe, considerar como removido
    }
    
    /**
     * Gerar thumbnail da foto
     * @param string $nomeArquivo Nome do arquivo da foto
     * @param int $largura Largura do thumbnail
     * @param int $altura Altura do thumbnail
     * @return string Nome do arquivo do thumbnail ou false em caso de erro
     */
    public static function gerarThumbnail($nomeArquivo, $largura = 150, $altura = 150) {
        if (empty($nomeArquivo)) {
            return false;
        }
        
        $caminhoOriginal = self::getCaminhoFoto($nomeArquivo);
        
        if (!file_exists($caminhoOriginal)) {
            return false;
        }
        
        // Gerar nome do thumbnail
        $info = pathinfo($nomeArquivo);
        $nomeThumbnail = $info['filename'] . '_thumb.' . $info['extension'];
        $caminhoThumbnail = self::$diretorioUpload . $nomeThumbnail;
        
        // Se o thumbnail já existe, retornar
        if (file_exists($caminhoThumbnail)) {
            return $nomeThumbnail;
        }
        
        // Obter tipo de imagem
        $tipoImagem = mime_content_type($caminhoOriginal);
        
        // Criar imagem original
        switch ($tipoImagem) {
            case 'image/jpeg':
            case 'image/jpg':
                $imagemOriginal = imagecreatefromjpeg($caminhoOriginal);
                break;
            case 'image/png':
                $imagemOriginal = imagecreatefrompng($caminhoOriginal);
                break;
            default:
                return false;
        }
        
        if (!$imagemOriginal) {
            return false;
        }
        
        // Obter dimensões originais
        list($larguraOriginal, $alturaOriginal) = getimagesize($caminhoOriginal);
        
        // Calcular dimensões do thumbnail mantendo proporção
        $ratio = min($largura / $larguraOriginal, $altura / $alturaOriginal);
        $novaLargura = round($larguraOriginal * $ratio);
        $novaAltura = round($alturaOriginal * $ratio);
        
        // Criar thumbnail
        $thumbnail = imagecreatetruecolor($novaLargura, $novaAltura);
        
        // Para PNG, preservar transparência
        if ($tipoImagem === 'image/png') {
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
            $transparente = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
            imagefill($thumbnail, 0, 0, $transparente);
        }
        
        // Redimensionar
        imagecopyresampled(
            $thumbnail, $imagemOriginal,
            0, 0, 0, 0,
            $novaLargura, $novaAltura,
            $larguraOriginal, $alturaOriginal
        );
        
        // Salvar thumbnail
        $resultado = false;
        switch ($tipoImagem) {
            case 'image/jpeg':
            case 'image/jpg':
                $resultado = imagejpeg($thumbnail, $caminhoThumbnail, 85);
                break;
            case 'image/png':
                $resultado = imagepng($thumbnail, $caminhoThumbnail, 6);
                break;
        }
        
        // Limpar memória
        imagedestroy($imagemOriginal);
        imagedestroy($thumbnail);
        
        return $resultado ? $nomeThumbnail : false;
    }
    
    /**
     * Validar arquivo de foto
     * @param array $arquivo Array $_FILES do arquivo
     * @return array Array com 'valid' (bool) e 'message' (string)
     */
    public static function validarArquivo($arquivo) {
        // Verificar se há arquivo
        if (!isset($arquivo) || !isset($arquivo['tmp_name']) || empty($arquivo['tmp_name'])) {
            return ['valid' => false, 'message' => 'Nenhum arquivo enviado.'];
        }
        
        // Verificar erro de upload
        if ($arquivo['error'] !== UPLOAD_ERR_OK) {
            $message = self::getUploadErrorMessage($arquivo['error']);
            return ['valid' => false, 'message' => $message];
        }
        
        // Verificar tipo de arquivo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $tipoArquivo = finfo_file($finfo, $arquivo['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($tipoArquivo, self::$tiposPermitidos)) {
            return ['valid' => false, 'message' => 'Tipo de arquivo não permitido. Apenas JPG, JPEG e PNG são aceitos.'];
        }
        
        // Verificar tamanho
        if ($arquivo['size'] > self::$tamanhoMaximo) {
            $tamanhoMB = round(self::$tamanhoMaximo / 1024 / 1024, 1);
            return ['valid' => false, 'message' => "Arquivo muito grande. Tamanho máximo: {$tamanhoMB}MB."];
        }
        
        // Verificar se é realmente uma imagem
        $imagemInfo = getimagesize($arquivo['tmp_name']);
        if ($imagemInfo === false) {
            return ['valid' => false, 'message' => 'Arquivo inválido ou corrompido.'];
        }
        
        return ['valid' => true, 'message' => 'Arquivo válido.'];
    }
    
    /**
     * Obter informações da foto
     * @param string $nomeArquivo Nome do arquivo da foto
     * @return array|false Array com informações da foto ou false
     */
    public static function getInfoFoto($nomeArquivo) {
        if (empty($nomeArquivo)) {
            return false;
        }
        
        $caminho = self::getCaminhoFoto($nomeArquivo);
        
        if (!file_exists($caminho)) {
            return false;
        }
        
        $info = getimagesize($caminho);
        $fileInfo = stat($caminho);
        
        return [
            'largura' => $info[0],
            'altura' => $info[1],
            'tipo' => $info['mime'],
            'tamanho' => $fileInfo['size'],
            'data_modificacao' => $fileInfo['mtime']
        ];
    }
    
    /**
     * Listar fotos antigas para limpeza
     * @param int $diasAntigos Número de dias para considerar como antigo
     * @return array Array com nomes dos arquivos antigos
     */
    public static function listarFotosAntigas($diasAntigos = 30) {
        $fotosAntigas = [];
        $tempoLimite = time() - ($diasAntigos * 24 * 60 * 60);
        
        if (!is_dir(self::$diretorioUpload)) {
            return $fotosAntigas;
        }
        
        $arquivos = scandir(self::$diretorioUpload);
        
        foreach ($arquivos as $arquivo) {
            if (in_array($arquivo, ['.', '..', 'index.php', '.htaccess'])) {
                continue;
            }
            
            $caminhoCompleto = self::$diretorioUpload . $arquivo;
            
            if (is_file($caminhoCompleto) && filemtime($caminhoCompleto) < $tempoLimite) {
                $fotosAntigas[] = $arquivo;
            }
        }
        
        return $fotosAntigas;
    }
    
    /**
     * Limpar fotos não utilizadas
     * @param PDO $conn Conexão com o banco de dados
     * @return int Número de arquivos removidos
     */
    public static function limparFotosNaoUtilizadas($conn) {
        $removidos = 0;
        
        if (!is_dir(self::$diretorioUpload)) {
            return $removidos;
        }
        
        // Obter todas as fotos do diretório
        $arquivos = scandir(self::$diretorioUpload);
        $fotosNoDiretorio = [];
        
        foreach ($arquivos as $arquivo) {
            if (in_array($arquivo, ['.', '..', 'index.php', '.htaccess'])) {
                continue;
            }
            
            if (is_file(self::$diretorioUpload . $arquivo)) {
                $fotosNoDiretorio[] = $arquivo;
            }
        }
        
        if (empty($fotosNoDiretorio)) {
            return $removidos;
        }
        
        // Obter fotos que estão sendo utilizadas no banco
        $placeholders = str_repeat('?,', count($fotosNoDiretorio) - 1) . '?';
        $sql = "SELECT cad_usu_foto FROM tb_cad_usuarios WHERE cad_usu_foto IN ($placeholders) AND cad_usu_foto IS NOT NULL";
        
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute($fotosNoDiretorio);
            $fotosUsadas = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Encontrar fotos não utilizadas
            $fotosNaoUsadas = array_diff($fotosNoDiretorio, $fotosUsadas);
            
            // Remover fotos não utilizadas
            foreach ($fotosNaoUsadas as $foto) {
                if (self::removerFoto($foto)) {
                    $removidos++;
                }
            }
            
        } catch (PDOException $e) {
            error_log('Erro ao limpar fotos não utilizadas: ' . $e->getMessage());
        }
        
        return $removidos;
    }
    
    /**
     * Obter mensagem de erro de upload
     * @param int $codigo Código de erro do upload
     * @return string Mensagem de erro
     */
    private static function getUploadErrorMessage($codigo) {
        switch ($codigo) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return "Arquivo muito grande.";
            case UPLOAD_ERR_PARTIAL:
                return "Upload incompleto.";
            case UPLOAD_ERR_NO_FILE:
                return "Nenhum arquivo enviado.";
            case UPLOAD_ERR_NO_TMP_DIR:
                return "Diretório temporário não encontrado.";
            case UPLOAD_ERR_CANT_WRITE:
                return "Falha ao escrever arquivo.";
            case UPLOAD_ERR_EXTENSION:
                return "Upload bloqueado por extensão.";
            default:
                return "Erro desconhecido no upload.";
        }
    }
}

/**
 * Função para exibir foto do usuário com fallback
 * @param string $nomeArquivo Nome do arquivo da foto
 * @param string $alt Texto alternativo
 * @param string $classe Classes CSS adicionais
 * @return string HTML da imagem
 */
function exibirFotoUsuario($nomeArquivo, $alt = 'Foto do usuário', $classe = '') {
    if (!empty($nomeArquivo) && FotoUtils::fotoExiste($nomeArquivo)) {
        $url = FotoUtils::getUrlFoto($nomeArquivo);
        return "<img src=\"{$url}\" alt=\"{$alt}\" class=\"foto-usuario {$classe}\">";
    } else {
        // Foto padrão/placeholder
        return "<div class=\"foto-placeholder {$classe}\">
                    <i class=\"fas fa-user\"></i>
                </div>";
    }
}
?>