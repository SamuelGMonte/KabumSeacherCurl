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
        
        if (file_exists($xlsx)) {
            echo "Arquivo já existe. Deseja sobrescrever? (Y/N): ";
            $overwrite = strtolower(trim(fgets(STDIN)));
            if ($overwrite === 'y') {
                $reader = new Csv();
                $spreadsheet = $reader->load($this->csvFile);
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
        
        $spreadsheet = $reader->load($this->csvFile); 
        $writer = new Xlsx($spreadsheet);
        $writer->save($xlsx); 

        $fileName = basename($xlsx);

        echo "Novo arquivo criado com o nome: $fileName em: $filePath\n";
    }
    

}





?>