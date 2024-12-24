<?php
require 'Functions.php';

$funcs = new Functions();

$a = readline("Digite o produto a ser buscado: ");
$pag = (int) readline("Digite a quantidade de paginas a serem buscadas ");
$max_price = (int) readline("Digite o preco maximo do produto desejado ");
$min_price = (int) readline("Digite o preco minimo do produto desejado ");

// $response = $funcs->get_user_content("https://www.kabum.com.br/busca/" . $a, $pag);
$response2 = $funcs->execute_concurrent_requests("https://www.kabum.com.br/busca/" . $a, $pag, $max_price, $min_price);




?>