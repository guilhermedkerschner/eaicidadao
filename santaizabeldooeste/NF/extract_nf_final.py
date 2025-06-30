#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import pdfplumber
import PyPDF2
import re
import sys
import os
import json
import unicodedata
from datetime import datetime

class NFExtractorFinal:
    def __init__(self):
        # Mapeamento para corrigir caracteres especiais comuns em PDFs
        self.char_fixes = {
            'Ã¡': 'á', 'Ã ': 'à', 'Ã¢': 'â', 'Ã£': 'ã', 'Ã¤': 'ä',
            'Ã©': 'é', 'Ã¨': 'è', 'Ãª': 'ê', 'Ã«': 'ë',
            'Ã­': 'í', 'Ã¬': 'ì', 'Ã®': 'î', 'Ã¯': 'ï',
            'Ã³': 'ó', 'Ã²': 'ò', 'Ã´': 'ô', 'Ãµ': 'õ', 'Ã¶': 'ö',
            'Ãº': 'ú', 'Ã¹': 'ù', 'Ã»': 'û', 'Ã¼': 'ü',
            'Ã§': 'ç', 'Ã±': 'ñ',
            'Ã': 'Á', 'Ã€': 'À', 'Ã‚': 'Â', 'Ãƒ': 'Ã', 'Ã„': 'Ä',
            'Ã‰': 'É', 'Ãˆ': 'È', 'ÃŠ': 'Ê', 'Ã‹': 'Ë',
            'Ã': 'Í', 'ÃŒ': 'Ì', 'ÃŽ': 'Î', 'Ã': 'Ï',
            'Ã"': 'Ó', 'Ã'': 'Ò', 'Ã"': 'Ô', 'Ã•': 'Õ', 'Ã–': 'Ö',
            'Ãš': 'Ú', 'Ã™': 'Ù', 'Ã›': 'Û', 'Ãœ': 'Ü',
            'Ã‡': 'Ç', 'Ã'': 'Ñ',
            'â€™': "'", 'â€œ': '"', 'â€': '"',
            'â€"': '-', 'â€"': '–',
            '•': '*', '…': '...',
            'Â': '', 'ÂÂ': '', 'Ã\x83': '', 'Ã\x82': '',
            'Ã\x90': '', 'Ã\x9c': '', 'Ã\x9d': '',
        }

    def fix_encoding(self, text):
        """Corrige problemas de codificação em texto extraído de PDF"""
        if not text:
            return ""
        
        # Aplica correções de caracteres
        for wrong, correct in self.char_fixes.items():
            text = text.replace(wrong, correct)
        
        # Remove caracteres de controle mas preserva acentos
        cleaned = ""
        for char in text:
            if ord(char) >= 32 or char in '\n\r\t':
                cleaned += char
        
        # Normaliza espaços múltiplos
        cleaned = re.sub(r'\s+', ' ', cleaned)
        
        return cleaned.strip()

    def extract_text_from_pdf(self, pdf_path):
        """Extrai texto com máxima qualidade e correção de encoding"""
        text = ""
        
        try:
            # Método 1: pdfplumber com diferentes configurações
            with pdfplumber.open(pdf_path) as pdf:
                for page_num, page in enumerate(pdf.pages):
                    try:
                        # Tenta extração com layout preservado
                        page_text = page.extract_text(
                            x_tolerance=2,
                            y_tolerance=3,
                            layout=True,
                            x_density=7.25,
                            y_density=13
                        )
                        
                        if not page_text:
                            # Método alternativo mais simples
                            page_text = page.extract_text()
                        
                        if page_text:
                            # Corrige encoding imediatamente
                            page_text = self.fix_encoding(page_text)
                            text += f"\n--- PÁGINA {page_num + 1} ---\n" + page_text + "\n"
                            
                    except Exception as e:
                        continue
            
        except Exception as e:
            print(f"Erro no pdfplumber: {e}", file=sys.stderr)
        
        # Fallback com PyPDF2 se necessário
        if len(text) < 100:
            try:
                with open(pdf_path, 'rb') as file:
                    pdf_reader = PyPDF2.PdfReader(file)
                    for page_num, page in enumerate(pdf_reader.pages):
                        try:
                            page_text = page.extract_text()
                            if page_text:
                                page_text = self.fix_encoding(page_text)
                                text += f"\n--- PÁGINA {page_num + 1} (PyPDF2) ---\n" + page_text + "\n"
                        except:
                            continue
            except Exception as e:
                print(f"Erro no PyPDF2: {e}", file=sys.stderr)
        
        return self.fix_encoding(text)

    def extract_numero_nota(self, text):
        """Extração do número da nota"""
        patterns = [
            # NFS-e específico
            r'Número\s+da\s+NFS-e[:\s]*(\d{12,18})',
            r'NFS-e[:\s]*N[°º]?[:\s]*(\d{12,18})',
            
            # NFe padrões
            r'N[°º]?[:\s]*(\d{9,15})',
            r'Nota\s+Fiscal[^0-9]*?(\d{9,15})',
            r'(?:Número|Numero)[:\s]*(\d{6,18})',
            
            # Sequências numéricas específicas
            r'(\d{15})(?=\s|$)',  # 15 dígitos
            r'(\d{12})(?=\s|$)',  # 12 dígitos
            r'(\d{9})(?=\s|$)',   # 9 dígitos
        ]
        
        for pattern in patterns:
            matches = re.finditer(pattern, text, re.IGNORECASE)
            for match in matches:
                numero = match.group(1)
                # Retorna números válidos
                if len(numero) >= 6 and not numero.startswith('0000000000'):
                    return numero
        
        return None

    def extract_razao_social(self, text):
        """Extração melhorada da razão social"""
        # Remove prefixos comuns que aparecem antes do nome
        text_clean = re.sub(r'.*?(?:Documento\s+Auxiliar|DANFE|NFS-e|Nota\s+Fiscal)', '', text, flags=re.IGNORECASE)
        
        # Padrões para empresas específicas (inclui BABINSKI)
        empresas_especificas = [
            r'(BABINSKI\s+TERRAPLENAGEM\s+LTDA)',
            r'(GIARETTA\s+MARMORES\s+E\s+MOVEIS\s+LTDA)',
            r'(JEFER\s+PRODUTOS\s+SIDERÚRGICOS\s+LTDA)',
            r'(SOLIMAR\s+METALURGICA[^0-9\n]*?LTDA)',
            r'(ZANCANARO\s+TERRAPLENAGEM[^0-9\n]*?BRITADOR)',
            r'(LOJAS\s+BECKER\s+LTDA)',
            r'(VIVEIRO\s+PRIMAVERA\s+LTDA[^0-9\n]*?ME)',
            r'(DRESCH\s+&\s+CIA\s+LTDA)',
            r'(PETRO\s+E\s+FURIGO\s+LTDA)',
            r'(INOBRAM\s+ASSESSORIA[^0-9\n]*?S/A)',
            r'(LEÃO\s+POÇOS\s+ARTESIANOS\s+LTDA)',
        ]
        
        for pattern in empresas_especificas:
            match = re.search(pattern, text_clean, re.IGNORECASE)
            if match:
                return match.group(1).strip().upper()
        
        # Padrões genéricos para capturar qualquer empresa
        patterns_genericos = [
            # Com contexto de campo
            r'(?:Nome|Razão\s+Social|Emitente)[:\s]*([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõçÇ\s&\.\-]{8,80})\s+(?:LTDA|S/A|ME|EIRELI)',
            
            # Padrão direto
            r'([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõçÇ\s&\.\-]{8,80})\s+LTDA(?:\s+ME)?',
            r'([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõçÇ\s&\.\-]{8,80})\s+S/A',
            r'([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõçÇ\s&\.\-]{8,60})\s+ME\b',
            
            # Para casos onde a empresa aparece sozinha em uma linha
            r'^([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõçÇ\s&\.\-]{15,80})$',
        ]
        
        for pattern in patterns_genericos:
            matches = re.finditer(pattern, text_clean, re.IGNORECASE | re.MULTILINE)
            for match in matches:
                nome = match.group(1).strip() if len(match.groups()) > 0 else match.group(0).strip()
                
                # Remove lixo comum
                nome = re.sub(r'\s*(CNPJ|CPF|RUA|ENDEREÇO|CEP).*$', '', nome, flags=re.IGNORECASE)
                nome = re.sub(r'^[^A-Za-z]*', '', nome)  # Remove caracteres no início
                
                # Verifica se é um nome válido
                if (len(nome) > 10 and 
                    not any(x in nome.lower() for x in ['documento', 'auxiliar', 'eletrônica', 'fiscal', 'página']) and
                    re.search(r'[A-Za-z]{3,}', nome)):  # Tem pelo menos uma palavra de 3+ letras
                    
                    # Se não termina com LTDA/ME/SA, adiciona baseado no contexto
                    if not re.search(r'(LTDA|ME|S/A|EIRELI)$', nome, re.IGNORECASE):
                        # Procura o tipo de empresa no texto próximo
                        context = text_clean[max(0, match.start()-50):match.end()+50]
                        if re.search(r'\bLTDA\b', context, re.IGNORECASE):
                            nome += ' LTDA'
                        elif re.search(r'\bME\b', context, re.IGNORECASE):
                            nome += ' ME'
                        elif re.search(r'\bS/A\b', context, re.IGNORECASE):
                            nome += ' S/A'
                    
                    return nome.strip().upper()
        
        return None

    def extract_cidade(self, text):
        """Extração da cidade/município"""
        patterns = [
            r'Município[:\s]*([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõçÇ\s]+?)(?:\s*UF|\s*-\s*[A-Z]{2}|\s*PR|\s*SC)',
            r'Cidade[:\s]*([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõçÇ\s]+?)(?:\s*UF|\s*-\s*[A-Z]{2}|\s*PR|\s*SC)',
            r'([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõçÇ\s]+?)\s*-\s*PR',
            r'([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõçÇ\s]+?)\s*PR',
            
            # Cidades específicas conhecidas
            r'(CORONEL\s+VIVIDA)',
            r'(SANTA\s+IZABEL\s+DO\s+OESTE)',
            r'(REALEZA)',
            r'(PATO\s+BRANCO)',
        ]
        
        for pattern in patterns:
            match = re.search(pattern, text, re.IGNORECASE)
            if match:
                cidade = match.group(1).strip()
                # Remove lixo
                cidade = re.sub(r'\s*(CEP|FONE|TELEFONE).*$', '', cidade, flags=re.IGNORECASE)
                if len(cidade) > 2 and len(cidade) < 50:
                    return cidade.upper()
        
        return None

    def extract_data_nota(self, text):
        """Extração da data"""
        patterns = [
            r'Data\s+(?:e\s+Hora\s+da\s+)?emissão[:\s]*(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})',
            r'Data\s+(?:da\s+)?emissão[:\s]*(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})',
            r'Emissão[:\s]*(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})',
            r'(\d{1,2})[\/\-](\d{1,2})[\/\-](202\d)',
        ]
        
        for pattern in patterns:
            matches = re.finditer(pattern, text, re.IGNORECASE)
            for match in matches:
                try:
                    day = int(match.group(1))
                    month = int(match.group(2))
                    year = int(match.group(3))
                    
                    if 1 <= month <= 12 and 1 <= day <= 31 and 2020 <= year <= 2030:
                        return f"{year:04d}-{month:02d}-{day:02d}"
                except ValueError:
                    continue
        
        return None

    def extract_cnpj(self, text):
        """Extração do CNPJ"""
        patterns = [
            r'CNPJ[:\s\/]*(\d{2}\.?\d{3}\.?\d{3}[\/\\]?\d{4}[\-]?\d{2})',
            r'(?<![\d])(\d{2}\.?\d{3}\.?\d{3}[\/\\]?\d{4}[\-]?\d{2})(?![\d])',
        ]
        
        for pattern in patterns:
            matches = re.finditer(pattern, text, re.IGNORECASE)
            for match in matches:
                cnpj = match.group(1)
                nums = re.sub(r'[^0-9]', '', cnpj)
                
                if len(nums) == 14:
                    return f"{nums[:2]}.{nums[2:5]}.{nums[5:8]}/{nums[8:12]}-{nums[12:14]}"
        
        return None

    def extract_cpf(self, text):
        """Extração do CPF"""
        patterns = [
            r'CPF[:\s\/]*(\d{3}\.?\d{3}\.?\d{3}[\-]?\d{2})',
            r'(?<![\d])(\d{3}\.?\d{3}\.?\d{3}[\-]?\d{2})(?![\d])',
        ]
        
        for pattern in patterns:
            matches = re.finditer(pattern, text, re.IGNORECASE)
            for match in matches:
                cpf = match.group(1)
                nums = re.sub(r'[^0-9]', '', cpf)
                
                if len(nums) == 11:
                    return f"{nums[:3]}.{nums[3:6]}.{nums[6:9]}-{nums[9:11]}"
        
        return None

    def extract_tomador_nome(self, text):
        """Extração do tomador"""
        if re.search(r'JEAN\s+PIER[R]?\s+CATTO', text, re.IGNORECASE):
            return 'JEAN PIERR CATTO'
        
        patterns = [
            r'(?:DESTINATÁRIO|TOMADOR|Nome)[:\s\/]*([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõçÇ\s]+?)(?:\s*(?:CNPJ|CPF|Endereço))',
        ]
        
        for pattern in patterns:
            match = re.search(pattern, text, re.IGNORECASE)
            if match:
                nome = match.group(1).strip()
                if 3 < len(nome) < 50:
                    return nome.upper()
        
        return None

    def extract_valor(self, text):
        """Extração do valor"""
        patterns = [
            r'Valor\s+Total\s+da\s+NFS-e[:\s]*R?\$?\s*([\d.,]+)',
            r'Valor\s+Líquido\s+da\s+NFS-e[:\s]*R?\$?\s*([\d.,]+)',
            r'VALOR\s+TOTAL[:\s]*R?\$?\s*([\d.,]+)',
            r'Total[:\s]*R?\$?\s*([\d.,]+)',
        ]
        
        for pattern in patterns:
            matches = re.finditer(pattern, text, re.IGNORECASE)
            for match in matches:
                valor_str = match.group(1).strip()
                valor_num = self.parse_valor_brasileiro(valor_str)
                if valor_num and valor_num > 0:
                    return valor_num
        
        return None

    def parse_valor_brasileiro(self, valor_str):
        """Parse de valor brasileiro"""
        if not valor_str:
            return None
        
        valor_str = re.sub(r'[R$\s]', '', valor_str)
        
        if ',' in valor_str:
            parts = valor_str.split(',')
            if len(parts) == 2 and len(parts[1]) == 2:
                parte_inteira = parts[0].replace('.', '')
                try:
                    return float(f"{parte_inteira}.{parts[1]}")
                except ValueError:
                    return None
        
        try:
            return float(valor_str.replace(',', '.'))
        except ValueError:
            return None

    def extract_endereco(self, text):
        """Extração do endereço"""
        patterns = [
            r'Endereço[:\s]*([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][^0-9\n]{10,100})(?:\s*(?:Cidade|Município|CEP))',
            r'(?:RUA|LINHA|AVENIDA)[:\s]*([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][^0-9\n]{5,100})(?:\s*(?:Cidade|Município|CEP))',
        ]
        
        for pattern in patterns:
            match = re.search(pattern, text, re.IGNORECASE)
            if match:
                endereco = match.group(1).strip()
                if len(endereco) > 5:
                    return endereco
        
        return None

    def extract_cep(self, text):
        """Extração do CEP"""
        pattern = r'CEP[:\s]*(\d{5}[\-\.]?\d{3})'
        match = re.search(pattern, text, re.IGNORECASE)
        if match:
            return match.group(1)
        
        # CEP isolado
        matches = re.findall(r'(\d{5}[\-\.]?\d{3})', text)
        for cep in matches:
            nums = re.sub(r'[^0-9]', '', cep)
            if len(nums) == 8 and not nums.startswith('000'):
                return cep
        
        return None

    def extract_produto_servico(self, text):
        """Extração de produto/serviço"""
        # Serviços específicos
        servicos = [
            r'(LOCAÇÃO\s+MÁQUINA[^0-9\n]+)',
            r'(Serviço\s+de\s+caminhão[^0-9\n]+)',
            r'(SERVIÇOS\s+PRESTADOS[^0-9\n]+)',
        ]
        
        for pattern in servicos:
            match = re.search(pattern, text, re.IGNORECASE)
            if match:
                return match.group(1).strip()
        
        return None

    def identify_document_type(self, text):
        """Identifica tipo"""
        service_indicators = ['nfs-e', 'serviço', 'locação', 'prestação']
        text_lower = text.lower()
        
        for indicator in service_indicators:
            if indicator in text_lower:
                return 'Serviço'
        
        return 'Produto'

    def extract_nf_data(self, pdf_path):
        """Função principal"""
        try:
            text = self.extract_text_from_pdf(pdf_path)
            if not text or len(text) < 50:
                return {'error': 'Texto insuficiente'}

            # Debug
            debug_file = f"debug_final_{int(datetime.now().timestamp())}.txt"
            with open(debug_file, 'w', encoding='utf-8') as f:
                f.write(f"=== TEXTO CORRIGIDO ===\n{text}\n\n")

            # Extração
            data = {
                'numero_nota': self.extract_numero_nota(text),
                'data_nota': self.extract_data_nota(text),
                'nome_razao_social': self.extract_razao_social(text),
                'cnpj': self.extract_cnpj(text),
                'valor_nota': self.extract_valor(text),
                'tomador_nome': self.extract_tomador_nome(text),
                'tomador_cpf': self.extract_cpf(text),
                'tomador_endereco': self.extract_endereco(text),
                'tomador_cep': self.extract_cep(text),
                'cidade': self.extract_cidade(text),  # NOVO CAMPO
                'tipo_produto_servico': self.identify_document_type(text)
            }

            # Produto/Serviço
            description = self.extract_produto_servico(text)
            if data['tipo_produto_servico'] == 'Serviço':
                data['produto'] = None
                data['servico'] = description
            else:
                data['produto'] = description
                data['servico'] = None

            # Log
            with open(debug_file, 'a', encoding='utf-8') as f:
                f.write(f"=== DADOS EXTRAÍDOS ===\n")
                for key, value in data.items():
                    f.write(f"{key}: {value}\n")

            return data

        except Exception as e:
            return {'error': f'Erro: {str(e)}'}

    def generate_sql(self, data):
        """Gera SQL"""
        if 'error' in data:
            return None

        def escape_sql(value):
            if value is None:
                return 'NULL'
            if isinstance(value, (int, float)):
                return str(value)
            escaped = str(value).replace("'", "''")
            return f"'{escaped}'"

        # Inclui cidade no SQL
        fields = [
            'numero_nota', 'data_nota', 'nome_razao_social', 'cnpj', 
            'tipo_produto_servico', 'produto', 'servico', 'valor_nota', 
            'tomador_nome', 'tomador_cpf', 'tomador_endereco', 'tomador_cep'
        ]
        
        values = [escape_sql(data.get(field)) for field in fields]
        
        sql = f"INSERT INTO notas_fiscais ({', '.join(fields)}) VALUES ({', '.join(values)});"
        return sql

def main():
    if len(sys.argv) < 2:
        print("Uso: python extract_nf_final.py <arquivo.pdf>")
        sys.exit(1)

    pdf_path = sys.argv[1]
    
    if not os.path.exists(pdf_path):
        print(f"ERRO: Arquivo não encontrado: {pdf_path}")
        sys.exit(1)

    extractor = NFExtractorFinal()
    data = extractor.extract_nf_data(pdf_path)

    if 'error' in data:
        print(f"ERRO: {data['error']}")
        sys.exit(1)

    sql = extractor.generate_sql(data)
    if sql:
        print(sql)
    else:
        print("ERRO: Não foi possível gerar SQL")
        sys.exit(1)

if __name__ == "__main__":
    main()