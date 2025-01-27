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
            die("Erro: O arquivo CSV não foi encontrado.\n");
        }
        
        $reader = new Csv();

        
        if (!is_dir(dirname($xlsx))) {
            mkdir(dirname($xlsx), 0755, true);
        }
        
        if (file_exists($xlsx)) {
            echo "Arquivo já existe. Deseja sobrescrever? (Y/N): ";
            $overwrite = strtolower(trim(fgets(STDIN)));
            if ($overwrite === 'y') {
                $reader = new Csv();
                $spreadsheet = $reader->load($csvFile);
                $writer = new Xlsx($spreadsheet);
                $writer->save($xlsx); 
                
                echo "Arquivo sobrescrito com sucesso: $xlsx\n";
            } elseif ($overwrite === 'n') {
                echo "Operação cancelada.\n";
            } else {
                echo "Opção inválida.\n";
            }
            return;
        }
        
        $spreadsheet = $reader->load($csvFile); 
        $writer = new Xlsx($spreadsheet);
        $writer->save($xlsx); 

        $fileName = basename($xlsx);

        echo "Novo arquivo criado com o nome: $fileName em: $xlsx\n";
    }
    

}





?>