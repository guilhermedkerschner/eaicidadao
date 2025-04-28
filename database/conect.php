<?php
/*
***********************************************************************

    CONECT.PHP - PARAMETRIZAÇÃO DE CONEXÃO COM O BANCO DE DADOS

************-Copyright (c) 2025, Guilherme Dal Molin-******************
*/
$db = new mysqli("localhost","root","Sys2025%Sio@#98","db_eaicidadao");

if($db->connect_errno){
    echo "Erro Banco de Dados: {$db->connect_error}";
    exit();
} //echo"Conectado";
?>