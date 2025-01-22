<?php
require_once './Kabum/Functions.php';
require_once './ML/FunctionsML.php';
require_once 'Csv2Excel.php';

$filePath = __DIR__ . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "xlsx" . DIRECTORY_SEPARATOR;

echo "Opções de busca:" . PHP_EOL;
echo "1 - kabum.com.br" . PHP_EOL;
echo "2 - mercadolivre.com.br" . PHP_EOL;

$option = readline("Escolha uma opção: ");

if($option !== "1" && $option !== "2") {
    die("Opcao invalida");
}

$response = "";

$product_name = readline("Digite o produto a ser buscado: ");
$pag = (int) readline("Digite a quantidade de paginas a serem buscadas ");
$max_price = (int) readline("Digite o preco maximo do produto desejado ");
$min_price = (int) readline("Digite o preco minimo do produto desejado ");

$csv2excel = new Csv2Excel();

switch($option) {
    case "1":
        $funcs = new Functions();
        $response = $funcs->execute_concurrent_requests("https://www.kabum.com.br/busca/", $product_name, $pag, $max_price, $min_price);
        break;
    case "2":
        $funcs = new FunctionsML();
        $response = $funcs->execute_requests_ml("https://lista.mercadolivre.com.br/", $product_name);
        break;
    default:
        die("Opção inválida" . PHP_EOL);
}

print($response);

$fileName = readline("Nome do arquivo a ser salvo como XLSX: ");

$csv2excel->convertCsvToXlsx($filePath . $fileName . ".xlsx");





?>