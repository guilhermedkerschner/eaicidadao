#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import pdfplumber
import PyPDF2
import re
import sys
import os
import json
from datetime import datetime

class NFExtractorPreciso:
    def __init__(self):
        self.patterns = {
            'numero_nota': [
                # Baseado nos PDFs reais que você enviou
                r'N[°º]?\s*[:\.]?\s*(\d{9,12})',  # Números longos como 000000198
                r'NF-e\s*N[°º]?\s*[:\.]?\s*(\d{6,12})',
                r'NOTA\s+FISCAL.*?(\d{9,12})',
                r'DANFE.*?N[°º]?\s*(\d{6,12})',
                r'SÉRIE.*?(\d{3,12})',
                r'(?:NÚMERO|Nº).*?(\d{6,12})',
                r'(\d{9})(?=\s|$)',  # 9 dígitos isolados
                r'00000(\d{4,6})',   # Padrão 000000XXX
            ],
            'data_nota': [
                r'DATA\s+(?:DA\s+)?EMISS[ÃA]O[:\s]*(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})',
                r'EMISS[ÃA]O[:\s]*(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})',
                r'(\d{2})[\/\-](\d{2})[\/\-](202\d)',  # Foca em 2020+
                r'DATA[:\s]*(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})',
            ],
            'cnpj': [
                r'CNPJ[:\s\/]*(\d{2}\.?\d{3}\.?\d{3}[\/\\]?\d{4}[\-]?\d{2})',
                r'(\d{2}\.?\d{3}\.?\d{3}[\/\\]?\d{4}[\-]?\d{2})',
                r'(\d{14})',  # CNPJ sem formatação
            ],
            'cpf': [
                r'CPF[:\s\/]*(\d{3}\.?\d{3}\.?\d{3}[\-]?\d{2})',
                r'(\d{3}\.?\d{3}\.?\d{3}[\-]?\d{2})',
                r'(\d{11})',  # CPF sem formatação
            ],
            'razao_social': [
                # Padrões baseados nos seus PDFs
                r'(GIARETTA\s+MARMORES\s+E\s+MOVEIS\s+LTDA)',
                r'(JEFER\s+PRODUTOS\s+SIDERÚRGICOS\s+LTDA)',
                r'(F\.\s+ZANCANARO\s+TERRAPLENAGEM\s+[-\s]*BRITADOR)',
                r'(SOLIMAR\s+METALURGICA[^0-9\n]+LTDA)',
                r'(LOJAS\s+BECKER\s+LTDA)',
                r'(VIVEIRO\s+PRIMAVERA\s+LTDA\s+[-\s]*ME)',
                r'(DRESCH\s+&\s+CIA\s+LTDA)',
                r'(PETRO\s+E\s+FURIGO\s+LTDA)',
                r'([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõç\s&\.-]{10,80})\s+LTDA(?:\s+ME)?',
                r'([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõç\s&\.-]{10,80})\s+S[\/]A',
                r'([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõç\s&\.-]{10,80})\s+ME\b',
                r'RAZ[ÃA]O\s+SOCIAL[:\s]*([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõç\s&\.-]+)',
            ],
            'tomador_nome': [
                # Baseado nos seus PDFs onde sempre é JEAN PIERR CATTO
                r'(JEAN\s+PIER[R]?\s+CATTO)',
                r'(GRANJA\s+CATTO[^0-9\n]+)',
                r'DESTINAT[ÁA]RIO[\/\s]*(?:REMETENTE)?[:\s]*([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõç\s]+?)(?:\s*CNPJ|\s*CPF|\s*ENDERE)',
                r'TOMADOR[:\s]*([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõç\s]+?)(?:\s*CNPJ|\s*CPF|\s*ENDERE)',
                r'NOME[:\s\/]*RAZ[ÃA]O\s+SOCIAL[:\s]*([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõç\s]+?)(?:\s*CNPJ|\s*CPF)',
            ],
            'valor_total': [
                r'VALOR\s+TOTAL\s+(?:DA\s+)?NOTA[:\s]*R?\$?\s*([\d.,]+)',
                r'TOTAL\s+(?:DA\s+)?NOTA[:\s]*R?\$?\s*([\d.,]+)',
                r'VALOR\s+TOTAL[:\s]*R?\$?\s*([\d.,]+)',
                r'TOTAL[:\s]*R?\$?\s*([\d.,]+)',
                r'Valor\s+Total[:\s]*R?\$?\s*([\d.,]+)',
                r'R\$\s*([\d.,]+)(?=\s*$)',  # R$ no final da linha
            ],
            'endereco': [
                # Padrões específicos dos seus PDFs
                r'(LINHA\s+[A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõç\s,.\-0-9]+)',
                r'ENDERE[ÇC]O[:\s]*([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõç\s,.\-0-9]+?)(?:\s*BAIRRO|\s*MUNIC|\s*CEP)',
                r'RUA[:\s]*([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõç\s,.\-0-9]+?)(?:\s*BAIRRO|\s*MUNIC|\s*CEP)',
            ],
            'cep': [
                r'CEP[:\s]*(\d{5}[\-.]?\d{3})',
                r'(\d{5}[\-.]?\d{3})',
                r'85\.?550[\-.]?000',  # CEP específico dos seus PDFs
            ],
            'produto_servico': [
                # Padrões específicos dos produtos/serviços dos seus PDFs
                r'(SOLEIRA\s+DE\s+GRANITO[^0-9\n]+)',
                r'(PIA\s+DE\s+GRANITO[^0-9\n]+)',
                r'(PINGADEIRA\s+DE\s+GRANITO[^0-9\n]+)',
                r'(TLH\s+GL[^0-9\n]+)',
                r'(PEDRA\s+BRITADA[^0-9\n]+)',
                r'(CIMENTO[^0-9\n]+)',
                r'(JANELA[^0-9\n]+)',
                r'(SISTEMA\s+DE\s+CONTROLE[^0-9\n]+)',
                r'(Serviço\s+de\s+caminhão[^0-9\n]+)',
                r'(Execução[^0-9\n]+obras[^0-9\n]+)',
                r'DESCRI[ÇC][ÃA]O[:\s]*([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][^0-9]{15,200})',
                r'PRODUTO[:\s]*([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][^0-9]{10,200})',
                r'SERVI[ÇC]O[:\s]*([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][^0-9]{10,200})',
            ]
        }

    def extract_text_from_pdf(self, pdf_path):
        """Extrai texto do PDF usando múltiplos métodos"""
        text = ""
        
        try:
            # Método 1: pdfplumber (mais preciso)
            with pdfplumber.open(pdf_path) as pdf:
                for page in pdf.pages:
                    page_text = page.extract_text()
                    if page_text:
                        text += page_text + "\n"
            
            if len(text) > 100:
                return text
                
        except Exception as e:
            print(f"Erro no pdfplumber: {e}", file=sys.stderr)
        
        try:
            # Método 2: PyPDF2 (fallback)
            with open(pdf_path, 'rb') as file:
                pdf_reader = PyPDF2.PdfReader(file)
                for page in pdf_reader.pages:
                    page_text = page.extract_text()
                    if page_text:
                        text += page_text + "\n"
                        
        except Exception as e:
            print(f"Erro no PyPDF2: {e}", file=sys.stderr)
        
        return text

    def extract_field(self, text, field_name):
        """Extrai um campo específico usando regex"""
        if field_name not in self.patterns:
            return None
        
        # Lista para coletar todos os matches
        all_matches = []
        
        for pattern in self.patterns[field_name]:
            try:
                matches = re.finditer(pattern, text, re.IGNORECASE | re.MULTILINE)
                for match in matches:
                    if field_name == 'data_nota' and len(match.groups()) >= 3:
                        # Para datas, formatar corretamente
                        day = match.group(1).zfill(2)
                        month = match.group(2).zfill(2)
                        year = match.group(3)
                        if int(month) <= 12 and int(day) <= 31:
                            all_matches.append(f"{year}-{month}-{day}")
                    elif match.group(1):
                        all_matches.append(match.group(1).strip())
            except Exception as e:
                continue
        
        if all_matches:
            # Para alguns campos, retorna o match mais longo (mais específico)
            if field_name in ['razao_social', 'tomador_nome', 'endereco', 'produto_servico']:
                return max(all_matches, key=len)
            # Para outros, retorna o primeiro match válido
            return all_matches[0]
        
        return None

    def extract_numero_nota_inteligente(self, text):
        """Extração inteligente do número da nota"""
        # Primeiro tenta padrões específicos
        patterns = [
            r'N[°º]?\s*[:\.]?\s*(\d{9})',  # 9 dígitos
            r'(\d{9})(?=\s|$)',  # 9 dígitos isolados
            r'00000(\d{4})',     # Padrão 000000XXX
            r'SÉRIE.*?(\d{3,6})', # Série
        ]
        
        for pattern in patterns:
            match = re.search(pattern, text, re.IGNORECASE)
            if match:
                return match.group(1) if '00000' not in pattern else '00000' + match.group(1)
        
        # Se não encontrar, pega qualquer sequência de 6+ dígitos
        match = re.search(r'(\d{6,12})', text)
        if match:
            return match.group(1)
        
        return None

    def extract_razao_social_inteligente(self, text):
        """Extração inteligente da razão social"""
        # Empresas conhecidas dos seus PDFs
        empresas_conhecidas = [
            r'GIARETTA\s+MARMORES\s+E\s+MOVEIS\s+LTDA',
            r'JEFER\s+PRODUTOS\s+SIDERÚRGICOS\s+LTDA',
            r'F\.\s+ZANCANARO\s+TERRAPLENAGEM\s+[-\s]*BRITADOR',
            r'SOLIMAR\s+METALURGICA[^0-9\n]+LTDA',
            r'LOJAS\s+BECKER\s+LTDA',
            r'VIVEIRO\s+PRIMAVERA\s+LTDA\s+[-\s]*ME',
            r'DRESCH\s+&\s+CIA\s+LTDA',
            r'PETRO\s+E\s+FURIGO\s+LTDA',
            r'INOBRAM\s+ASSESSORIA[^0-9\n]+S\/A',
            r'LEÃO\s+POÇOS\s+ARTESIANOS\s+LTDA',
            r'BRUNA\s+G\s+BOBINSKI\s+LTDA\s+ME',
        ]
        
        for empresa in empresas_conhecidas:
            match = re.search(empresa, text, re.IGNORECASE)
            if match:
                return match.group(0)
        
        # Padrão genérico melhorado
        patterns = [
            r'([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõç\s&\.-]{8,60})\s+LTDA(?:\s+ME)?',
            r'([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõç\s&\.-]{8,60})\s+S[\/]A',
            r'([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõç\s&\.-]{8,60})\s+ME\b',
        ]
        
        for pattern in patterns:
            match = re.search(pattern, text, re.IGNORECASE)
            if match:
                nome = match.group(1).strip()
                # Remove lixo comum
                nome = re.sub(r'\s*(CNPJ|CPF|RUA|ENDERE).*$', '', nome, flags=re.IGNORECASE)
                if len(nome) > 8:
                    return nome + ' ' + match.group(0).split()[-1]  # Adiciona LTDA/ME/S/A
        
        return None

    def extract_tomador_inteligente(self, text):
        """Extração inteligente do tomador"""
        # Nome específico dos seus PDFs
        if re.search(r'JEAN\s+PIER[R]?\s+CATTO', text, re.IGNORECASE):
            return 'JEAN PIERR CATTO'
        
        if re.search(r'GRANJA\s+CATTO', text, re.IGNORECASE):
            match = re.search(r'GRANJA\s+CATTO[^0-9\n]+', text, re.IGNORECASE)
            if match:
                return match.group(0).strip()
        
        # Padrão genérico
        patterns = [
            r'DESTINAT[ÁA]RIO[\/\s]*(?:REMETENTE)?[:\s]*([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõç\s]+?)(?:\s*CNPJ|\s*CPF|\s*ENDERE)',
            r'TOMADOR[:\s]*([A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇ][A-ZÁÉÍÓÚÂÊÎÔÛÀÈÌÒÙÃÕÇa-záéíóúâêîôûàèìòùãõç\s]+?)(?:\s*CNPJ|\s*CPF|\s*ENDERE)',
        ]
        
        for pattern in patterns:
            match = re.search(pattern, text, re.IGNORECASE)
            if match:
                nome = match.group(1).strip()
                if len(nome) > 3:
                    return nome
        
        return None

    def extract_produto_servico_inteligente(self, text):
        """Extração inteligente de produtos/serviços"""
        # Produtos específicos dos seus PDFs
        produtos_conhecidos = [
            r'SOLEIRA\s+DE\s+GRANITO[^0-9\n]+',
            r'PIA\s+DE\s+GRANITO[^0-9\n]+',
            r'PINGADEIRA\s+DE\s+GRANITO[^0-9\n]+',
            r'TLH\s+GL[^0-9\n]+',
            r'PEDRA\s+BRITADA[^0-9\n]+',
            r'CIMENTO[^0-9\n]+',
            r'JANELA[^0-9\n]+',
            r'DIVISOR\s+DE\s+SOLO',
            r'SISTEMA\s+DE\s+CONTROLE[^0-9\n]+',
        ]
        
        for produto in produtos_conhecidos:
            match = re.search(produto, text, re.IGNORECASE)
            if match:
                return match.group(0).strip()
        
        # Serviços específicos
        servicos_conhecidos = [
            r'Serviço\s+de\s+caminhão[^0-9\n]+',
            r'Execução[^0-9\n]+obras[^0-9\n]+',
            r'SERVIÇOS\s+PRESTADOS[^0-9\n]+',
            r'MÃO\s+DE\s+OBRA[^0-9\n]+',
        ]
        
        for servico in servicos_conhecidos:
            match = re.search(servico, text, re.IGNORECASE)
            if match:
                return match.group(0).strip()
        
        return None

    def clean_value(self, value, field_type='text'):
        """Limpa e formata valores"""
        if not value:
            return None
        
        if field_type == 'money':
            # Remove R$, espaços e converte para float
            value = re.sub(r'[R$\s]', '', value)
            # Se tem vírgula como decimal
            if ',' in value and value.count(',') == 1:
                parts = value.split(',')
                if len(parts[1]) == 2:  # Decimal com 2 casas
                    # Remove pontos de milhares da parte inteira
                    parts[0] = parts[0].replace('.', '')
                    value = parts[0] + '.' + parts[1]
            else:
                value = value.replace(',', '.')
            
            try:
                return float(value)
            except:
                return None
        
        elif field_type == 'cnpj':
            # Normaliza CNPJ
            nums = re.sub(r'[^0-9]', '', value)
            if len(nums) == 14:
                return f"{nums[:2]}.{nums[2:5]}.{nums[5:8]}/{nums[8:12]}-{nums[12:14]}"
            return value
        
        elif field_type == 'cpf':
            # Normaliza CPF
            nums = re.sub(r'[^0-9]', '', value)
            if len(nums) == 11:
                return f"{nums[:3]}.{nums[3:6]}.{nums[6:9]}-{nums[9:11]}"
            return value
        
        else:
            return value.strip()

    def identify_document_type(self, text):
        """Identifica se é produto ou serviço"""
        service_keywords = ['serviço', 'servico', 'prestação', 'mão de obra', 'construção', 'nfs-e', 'iss', 'caminhão', 'munck']
        
        text_lower = text.lower()
        
        for keyword in service_keywords:
            if keyword in text_lower:
                return 'Serviço'
        
        return 'Produto'

    def extract_nf_data(self, pdf_path):
        """Função principal para extrair dados da NF"""
        try:
            text = self.extract_text_from_pdf(pdf_path)
            if not text or len(text) < 50:
                return {'error': 'Não foi possível extrair texto suficiente do PDF'}

            # Salva texto extraído para debug
            debug_file = f"debug_text_{int(datetime.now().timestamp())}.txt"
            with open(debug_file, 'w', encoding='utf-8') as f:
                f.write(text)

            # Extrai campos com métodos inteligentes
            data = {
                'numero_nota': self.extract_numero_nota_inteligente(text),
                'data_nota': self.extract_field(text, 'data_nota'),
                'nome_razao_social': self.extract_razao_social_inteligente(text),
                'cnpj': self.clean_value(self.extract_field(text, 'cnpj'), 'cnpj'),
                'valor_nota': self.clean_value(self.extract_field(text, 'valor_total'), 'money'),
                'tomador_nome': self.extract_tomador_inteligente(text),
                'tomador_cpf': self.clean_value(self.extract_field(text, 'cpf'), 'cpf'),
                'tomador_endereco': self.extract_field(text, 'endereco'),
                'tomador_cep': self.extract_field(text, 'cep'),
                'tipo_produto_servico': self.identify_document_type(text)
            }

            # Extrai produto/serviço
            description = self.extract_produto_servico_inteligente(text)
            if data['tipo_produto_servico'] == 'Serviço':
                data['produto'] = None
                data['servico'] = description
            else:
                data['produto'] = description
                data['servico'] = None

            return data

        except Exception as e:
            return {'error': f'Erro ao processar PDF: {str(e)}'}

    def generate_sql(self, data):
        """Gera SQL INSERT"""
        if 'error' in data:
            return None

        # Escapa aspas simples
        def escape_sql(value):
            if value is None:
                return 'NULL'
            if isinstance(value, (int, float)):
                return str(value)
            return f"'{str(value).replace(chr(39), chr(39)+chr(39))}'"

        sql = f"""INSERT INTO notas_fiscais (numero_nota, data_nota, nome_razao_social, cnpj, tipo_produto_servico, produto, servico, valor_nota, tomador_nome, tomador_cpf, tomador_endereco, tomador_cep) VALUES ({escape_sql(data['numero_nota'])}, {escape_sql(data['data_nota'])}, {escape_sql(data['nome_razao_social'])}, {escape_sql(data['cnpj'])}, {escape_sql(data['tipo_produto_servico'])}, {escape_sql(data['produto'])}, {escape_sql(data['servico'])}, {escape_sql(data['valor_nota'])}, {escape_sql(data['tomador_nome'])}, {escape_sql(data['tomador_cpf'])}, {escape_sql(data['tomador_endereco'])}, {escape_sql(data['tomador_cep'])});"""
        
        return sql

def main():
    if len(sys.argv) < 2:
        print("Uso: python extract_nf.py <caminho_pdf>")
        sys.exit(1)

    pdf_path = sys.argv[1]
    
    if not os.path.exists(pdf_path):
        print(f"ERRO: Arquivo {pdf_path} não encontrado")
        sys.exit(1)

    extractor = NFExtractorPreciso()
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