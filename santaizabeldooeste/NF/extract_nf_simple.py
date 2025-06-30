#!/usr/bin/env python3
import sys
import os

def main():
    print("=== Teste do Script Python ===")
    print(f"Python version: {sys.version}")
    print(f"Argumentos: {sys.argv}")
    print(f"Diretório atual: {os.getcwd()}")
    
    if len(sys.argv) < 2:
        print("ERRO: Nenhum arquivo especificado")
        print("Uso: python extract_nf_simple.py <arquivo.pdf>")
        return
    
    pdf_file = sys.argv[1]
    print(f"Arquivo recebido: {pdf_file}")
    
    if not os.path.exists(pdf_file):
        print(f"ERRO: Arquivo {pdf_file} não existe")
        return
    
    print(f"Arquivo existe: {pdf_file}")
    print(f"Tamanho: {os.path.getsize(pdf_file)} bytes")
    
    # Testa as bibliotecas
    try:
        import pdfplumber
        print("✅ pdfplumber importado com sucesso")
    except ImportError as e:
        print(f"❌ Erro ao importar pdfplumber: {e}")
        return
    
    try:
        import PyPDF2
        print("✅ PyPDF2 importado com sucesso")
    except ImportError as e:
        print(f"❌ Erro ao importar PyPDF2: {e}")
    
    # Tenta ler o PDF
    try:
        with pdfplumber.open(pdf_file) as pdf:
            print(f"✅ PDF aberto com sucesso")
            print(f"Número de páginas: {len(pdf.pages)}")
            
            if len(pdf.pages) > 0:
                text = pdf.pages[0].extract_text()
                if text:
                    print(f"✅ Texto extraído: {len(text)} caracteres")
                    print("Primeiros 200 caracteres:")
                    print(text[:200])
                    
                    # Simula um SQL de teste
                    sql = "INSERT INTO notas_fiscais (numero_nota, data_nota, nome_razao_social, cnpj, tipo_produto_servico, produto, servico, valor_nota, tomador_nome, tomador_cpf, tomador_endereco, tomador_cep) VALUES ('TESTE', '2025-06-23', 'EMPRESA TESTE', '12345678000195', 'Produto', 'Produto de teste', NULL, 100.00, 'CLIENTE TESTE', '12345678901', 'RUA TESTE, 123', '12345-678');"
                    print("SQL de teste gerado:")
                    print(sql)
                else:
                    print("❌ Nenhum texto extraído")
            else:
                print("❌ PDF sem páginas")
                
    except Exception as e:
        print(f"❌ Erro ao processar PDF: {e}")
        import traceback
        traceback.print_exc()

if __name__ == "__main__":
    main()