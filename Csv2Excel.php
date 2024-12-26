<?php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Csv2Excel {
    public $filePath = __DIR__ . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "xlsx" . DIRECTORY_SEPARATOR;
    public $csvFile = __DIR__ . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "csv" . DIRECTORY_SEPARATOR . "products.csv";

    public function convertCsvToXlsx($xlsxName) {
        if (!file_exists($this->csvFile)) {
            die("Erro: O arquivo CSV não foi encontrado.\n");
        }


        if (!is_dir($this->filePath)) {
            mkdir($this->filePath, 0755, true);
        }


        $reader = new Csv();
        $spreadsheet = $reader->load($this->csvFile); 
        
        var_dump($xlsxName);
        $writer = new Xlsx($spreadsheet);
        $writer->save($xlsxName . ".xlsx"); 
    }
    

}





?>