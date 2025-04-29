<?php

include_once '../database/conect.php';


$response = [];

$erro = [];
empty($_POST['email']) ? $erro[] = "Informe o E-mail" : "";
empty($_POST['password']) ? $erro[] = "Informe a senha" : "";

if(empty($erro)){
    $response = [
        'status' => false,
        'mensagem' => $erro,
    ];
}


echo json_encode(response);