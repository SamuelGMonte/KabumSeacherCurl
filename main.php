<?php
require_once './Kabum/Functions.php';
require_once './ML/FunctionsML.php';
require_once 'Csv2Excel.php';

echo "---------------------------------------------" . PHP_EOL;

echo "Opções de busca:" . PHP_EOL;
echo "1 - kabum.com.br" . PHP_EOL;
echo "2 - mercadolivre.com.br" . PHP_EOL;

echo "---------------------------------------------" . PHP_EOL;

$option = readline("Escolha uma opção: ");

if($option !== "1" && $option !== "2") {
    die("Opcao invalida");
}

$response = "";

$product_name = (string) readline("Digite o produto a ser buscado: ");
while(is_numeric($product_name)) {
    $product_name = readline("Por favor, insira um nome valido: ");
}
while($product_name == null) {
    $product_name = readline("Produto nao pode ser vazio: ");
}

$pag = readline("Digite a quantidade de paginas a serem buscadas: ");
while($pag == null || !is_numeric($pag) || !ctype_digit($pag)) {
    $pag = readline("Pagina invalida, selecione um numero inteiro novamente: ");
}
$pag = (int) $pag;

$max_price = readline("Digite o preco maximo do produto desejado: ");
while($max_price == null || !ctype_digit($max_price)) {
    $max_price = readline("Preco maximo nao pode ser vazio: ");
}
$max_price = (float) $max_price;

$min_price = (float) readline("Digite o preco minimo do produto desejado, nulo para 0: ");
while(!is_numeric($min_price)) {
    $min_price = (int) readline("Por favor, insira uma valor numerico");
}
$min_price = (float) $min_price;

while($product_name == null) {
    $min_price = 0;
}

$csv2excel = new Csv2Excel();

switch($option) {
    case "1":
        $funcs = new Functions();
        $response = $funcs->execute_concurrent_requests("https://www.kabum.com.br/busca/", $product_name, $pag, $max_price, $min_price);
        $filePath = __DIR__ . DIRECTORY_SEPARATOR  . "Kabum" . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "xlsx" . DIRECTORY_SEPARATOR;
        break;
    case "2":
        $funcs = new FunctionsML();
        $response = $funcs->execute_requests_ml("https://lista.mercadolivre.com.br/", $product_name, $pag, $max_price, $min_price);
        $filePath = __DIR__ . DIRECTORY_SEPARATOR  . "ML" . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "xlsx" . DIRECTORY_SEPARATOR;
        break;
    default:
        die("Opção inválida" . PHP_EOL);
}

print($response) . PHP_EOL;

$fileName = readline("Nome do arquivo a ser salvo como XLSX: ") . ".xlsx";

$csv2excel->convertCsvToXlsx($option, $filePath . $fileName);

?>