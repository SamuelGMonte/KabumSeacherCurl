<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../Kabum/Functions.php';
require_once '../ML/FunctionsML.php';
require_once '../Csv2Excel.php';

class ScraperAPI {
    private $option;
    private $product_name;
    private $pag;
    private $max_price;
    private $min_price;
    private $csv2excel;
    private $filePath;
    private $fileName;
    private $response;
    private $logs = [];

    public function __construct($data) {
        $this->validateInput($data);
        $this->csv2excel = new Csv2Excel();
        $this->fileName = "resultado_" . str_replace([":", " "], ["-", " "], date_format(new DateTime(), "Y-m-d")) . ".xlsx";
    }

    function logMessage($message) {
        $this->logs[] = $message; 
    }

    private function validateInput($data) {
        if (!isset($data['option'], $data['product_name'], $data['pag'], $data['max_price'], $data['min_price'])) {
            echo json_encode(["error" => "Parâmetros inválidos"]);
            http_response_code(400);
            exit;
        }

        $this->option = $data['option'];
        $this->product_name = $data['product_name'];
        $this->pag = (int) $data['pag'];
        $this->max_price = (int) $data['max_price'];
        $this->min_price = (int) $data['min_price'];
    }

    public function executeScraper() {
        switch ($this->option) {
            case "1":
                $funcs = new Functions();
                $this->response = $funcs->execute_concurrent_requests(
                    "https://www.kabum.com.br/busca/", 
                    $this->product_name, 
                    $this->pag, 
                    $this->max_price, 
                    $this->min_price
                );
                $this->logMessage($this->response);
                $this->filePath = __DIR__ . "/Kabum/data/xlsx/";
                break;
            case "2":
                $funcs = new FunctionsML();
                $this->response = $funcs->execute_requests_ml(
                    "https://lista.mercadolivre.com.br/", 
                    $this->product_name, 
                    $this->pag, 
                    $this->max_price, 
                    $this->min_price
                );
                $this->logMessage($this->response);
                $this->filePath = __DIR__ . "/ML/data/xlsx/";
                break;
            default:
                echo json_encode(["error" => "Opção inválida"]);
                http_response_code(400);
                exit;
        }

        return $this;
    }

    public function convertToXlsx() {
        $this->csv2excel->convertCsvToXlsx($this->option, $this->filePath . $this->fileName);
        $this->logMessage($this->csv2excel->convertCsvToXlsx($this->option, $this->filePath . $this->fileName));
        return $this;
    }

    public function getResponse() {
        $response = [
            "status" => "success",
            "data" => []
        ];
        
        foreach ($this->logs as $log) {
            $response['data'][] = json_decode($log);
        }
        
        echo json_encode($response, JSON_PRETTY_PRINT);
    }
    
    
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $scraper = new ScraperAPI($input);
    $scraper->executeScraper()->convertToXlsx()->getResponse();
} else {
    echo json_encode(["error" => "Método não permitido"]);
    http_response_code(405);
}
