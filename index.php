<?php
require_once 'Functions.php';
require_once 'Csv2Excel.php';

$filePath = __DIR__ . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "xlsx" . DIRECTORY_SEPARATOR;

$a = readline("Digite o produto a ser buscado: ");
$pag = (int) readline("Digite a quantidade de paginas a serem buscadas ");
$max_price = (int) readline("Digite o preco maximo do produto desejado ");
$min_price = (int) readline("Digite o preco minimo do produto desejado ");

$funcs = new Functions();
$csv2excel = new Csv2Excel();

$response = $funcs->execute_concurrent_requests("https://www.kabum.com.br/busca/" . $a, $pag, $max_price, $min_price);
print($response);

$fileName = readline("Nome do arquivo a ser salvo como XLSX: ");

$csv2excel->convertCsvToXlsx($filePath . $fileName);

echo "Arquivo criado com o nome $fileName em: " . $filePath . $fileName . ".xlsx";



?>