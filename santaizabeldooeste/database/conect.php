<?php

# ***********************************************************************
# 
#    CONECT.PHP - PARAMETRIZAÇÃO DE CONEXÃO COM O BANCO DE DADOS
#            Copyright (c) 2025, Guilherme Dal Molin
#
# ***********************************************************************

date_default_timezone_set("America/Sao_paulo");


// Informações de conexão com o banco de dados
$host = 'localhost';
$dbname = 'db_eaicidadao';
$username = 'root';             // Altere para o seu usuário do banco de dados
$password = 'Sys2025%Sio@#98';  // Altere para sua senha do banco de dados

// Tenta realizar a conexão com o banco de dados
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Se chegou até aqui, a conexão foi estabelecida com sucesso
    } catch (PDOException $e) {
    // Em caso de erro na conexão
    error_log('Erro na conexão com o banco de dados: ' . $e->getMessage());
    die("Não foi possível conectar ao banco de dados. Por favor, tente novamente mais tarde.");
}
?>