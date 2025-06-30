#!/bin/bash
echo "Instalando dependências para o processador de NFs..."

# Instala Python e pip se não estiver instalado
if ! command -v python3 &> /dev/null; then
    echo "Instalando Python3..."
    apt-get update
    apt-get install -y python3 python3-pip
fi

# Instala bibliotecas Python necessárias
pip3 install pdfplumber PyPDF2

echo "Criando diretórios necessários..."
mkdir -p uploads downloads

echo "Configurando permissões..."
chmod 755 uploads downloads
chmod +x extract_nf.py

echo "Instalação concluída!"
echo "Acesse via navegador: http://localhost/path/to/index.php"