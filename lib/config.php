<?php
/*
**********************************************************************
    CONFIG.PHP - PARAMETRIZAÇÃO DA APLICAÇÃO - EAI CIDADAO
**********************************************************************
*/

// Iniciando a Sessão em toda aplicação
session_start();

// Configuração de Timezone e Hora do Server
date_default_timezone_set("America/Sao_paulo");

//Chama de conexão com o Banco
require '../database/conect.php';


//Configuração do Servidor de Email

