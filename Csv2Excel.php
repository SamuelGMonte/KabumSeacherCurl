<?php

require 'vendor/autoload.php';


use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Csv2Excel {
    public function convertCsvToXlsx($website_option, $xlsx) {
        switch($website_option) {
            case "1":
                $csvFile = __DIR__ . DIRECTORY_SEPARATOR  . "Kabum" . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "csv" . DIRECTORY_SEPARATOR . "products.csv";
                break;
            case "2":
                $csvFile = __DIR__ . DIRECTORY_SEPARATOR  . "ML" . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "csv" . DIRECTORY_SEPARATOR . "products.csv";
                break;
        }
    
        if (!file_exists($csvFile)) {
            die(json_encode(["status" => "error", "message" => "Erro: O arquivo CSV não foi encontrado"]));
        }
    
        $reader = new Csv();
        $spreadsheet = $reader->load($csvFile); 
        $writer = new Xlsx($spreadsheet);
        
        // Instead of saving the file, send it as a download
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header('Content-Disposition: attachment; filename="resultado.xlsx"');
        header("Cache-Control: max-age=0");
    
        // Send output to browser
        $writer->save('php://output');
        
        exit;
    }

}


?>