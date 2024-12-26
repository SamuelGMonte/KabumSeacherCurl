<?php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Csv2Excel {
    public $csvFile = __DIR__ . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "csv" . DIRECTORY_SEPARATOR . "products.csv";

    public function convertCsvToXlsx($xlsx) {
        $filePath = dirname($xlsx) . DIRECTORY_SEPARATOR;

        if (!file_exists($this->csvFile)) {
            die("Erro: O arquivo CSV não foi encontrado.\n");
        }
        
        $reader = new Csv();

        
        if (!is_dir($filePath)) {
            mkdir($filePath, 0755, true);
        }
        
        if(file_exists($xlsx . ".xlsx")) {
            $overwrite = readline("Arquivo ja existe, deseja sobrescrever? Y / N: ");
            switch(strtolower($overwrite)) {
                case 'y':
                    $spreadsheet = new Spreadsheet();
                    $writer = new Xlsx($spreadsheet);
                    $writer->save($xlsx . ".xlsx");

                    $fileName = basename($xlsx) . ".xlsx";
                    echo "Arquivo sobrescrito com o nome " . $fileName . " em: " . $filePath . $xlsx . ".xlsx";
                    return;

                case 'n':
                    echo "Operação cancelada. Nenhuma alteração foi feita.\n";
                    return;

                default:
                echo "Opcao invalida";
            }
        }
        
        $spreadsheet = $reader->load($this->csvFile); 
        $writer = new Xlsx($spreadsheet);
        $writer->save($xlsx . ".xlsx"); 

        $fileName = basename($xlsx);

        echo "Novo arquivo criado com o nome: $fileName em: $filePath\n";
    }
    

}





?>