<?php
require_once 'Util.php';

ini_set("memory_limit", "512M");

header('Content-type: text/html; charset=UTF-8');
class Functions {
    private $agent = 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0';

    public $multiCurl;
    public $json_response = [];

    public function __construct() {
        $this->multiCurl = curl_multi_init();
        Util::initialize();
    }

    public function get_user_content($url) {
        $ch1 = curl_init();

        curl_setopt($ch1, CURLOPT_URL, $url);
        curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch1, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch1, CURLOPT_USERAGENT, $this->agent);

        curl_multi_add_handle($this->multiCurl, $ch1);

        return $ch1;
    }

    public function get_user_page($url) {
        $ch2 = curl_init();

        curl_setopt($ch2, CURLOPT_URL, $url);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch2, CURLOPT_USERAGENT, $this->agent);

        curl_multi_add_handle($this->multiCurl, $ch2);

        return $ch2;
    }

    public function __destruct() {
        curl_multi_close($this->multiCurl);
    }

    public function execute_concurrent_requests($url, $productName, $pages, $max_price, $min_price) {
        $curl_handles = [];
        $this->multiCurl = curl_multi_init();
        
        $url = $url . str_replace("+", "-", urlencode($productName));
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: */*',
            'Accept-Language: en-US,en;q=0.9',
            'Connection: keep-alive',
            'sec-ch-ua: "Google Chrome";v="117"',
            'sec-ch-ua-mobile: ?0',
            'sec-ch-ua-platform: "Windows"',
            'Sec-Fetch-Dest: empty',
            'Sec-Fetch-Mode: cors',
            'Sec-Fetch-Site: same-origin',
        ]);
        curl_setopt($ch, CURLOPT_COOKIEFILE, '');
        curl_setopt($ch, CURLOPT_COOKIEJAR, '');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->agent);

        $response = curl_exec($ch);
        curl_close($ch);
        
        if ($response) {
            $mainElem = Util::get_json_content_kabum($response);
            $json_obj = json_decode(preg_replace('/<[^>]*>/', '', $mainElem), true);
            $totalPages = $json_obj['props']['pageProps']['data']['catalogServer']['meta']['totalPagesCount'];
            file_put_contents("not-json.json", json_encode($json_obj));
            if($totalPages == null) {
                die(json_encode(["status" => "error", "message" => "Produto indisponivel ou fora de estoque"])); 
            }
            else if($pages < 0) {
                die(json_encode(["status" => "error", "message" => "Pagina inexistente"]));
            } 
            else if($pages > $totalPages) {
                $this->json_response = json_encode(["status" => "error", "message" => "numero de paginas: $pages maior que o total disponivel, efetuando buscas ate a pagina: $totalPages"]);
                $pages = $totalPages;
            }
        }

        for ($i = 1; $i <= $pages; $i++) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url . "?page_number=" . $i);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: */*',
                'Accept-Language: en-US,en;q=0.9',
                'Connection: keep-alive',
                'sec-ch-ua: "Google Chrome";v="117"',
                'sec-ch-ua-mobile: ?0',
                'sec-ch-ua-platform: "Windows"',
                'Sec-Fetch-Dest: empty',
                'Sec-Fetch-Mode: cors',
                'Sec-Fetch-Site: same-origin',
            ]);
            curl_setopt($ch, CURLOPT_COOKIEFILE, '');
            curl_setopt($ch, CURLOPT_COOKIEJAR, '');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($ch, CURLOPT_USERAGENT, $this->agent);
            
            curl_multi_add_handle($this->multiCurl, $ch);
            $curl_handles[] = $ch;
        }
        
        
        $running = null;
        do {
            curl_multi_exec($this->multiCurl, $running);
        } while ($running);
        
        
        $filtered_jsons = [];
        $all_product_details = [];
        
        foreach ($curl_handles as $index => $ch) {
            $response = curl_multi_getcontent($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($http_code == 404) {
                $this->json_response = json_encode(["error" => "true", "message" => "status 404"]);
                continue;
            } else if ($http_code == 200 && !empty($response)) {
                $mainElem = Util::get_json_content_kabum($response);
                $mainElem = preg_replace('/<[^>]*>/', '', $mainElem);
                $json_obj = json_decode($mainElem, true);
                $json_obj_filtered = Util::filter_json($json_obj);
                $filtered_jsons[] = $json_obj_filtered;
    
                $product_details = Util::get_products_details($json_obj_filtered);
                $all_product_details[] = $product_details;
            } 
            else {
                $this->json_response = json_encode(["error" => "true", "message" => "erro desconhecido index: $index"]);
                continue;
            }
    
            curl_multi_remove_handle($this->multiCurl, $ch);
            curl_close($ch);
        }
    
        $filePath = __DIR__ . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "csv" . DIRECTORY_SEPARATOR . "products.csv";

        $directoryPath = dirname($filePath);

        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0755, true);
        }

        if (!file_exists($filePath)) {
            $fileHandle = fopen($filePath, 'w');
            if ($fileHandle) {
                fclose($fileHandle);
                $this->json_response = json_encode(["status" => "success", "message" => "Arquivo criado com sucesso em: $filePath"]);
            } 
            else {
                die(json_encode(["status" => "error", "message" => "Erro ao criar o arquivo em: $filePath"]));
            }
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lines = array_unique($lines);
        $productsFile = fopen($filePath, 'w');

        $pageNumber = 0;
        $productFails = 0;
        $productNumber = 0;
        $headers = ['ID do produto', 'Nome do Produto', 'Preço (R$)', 'Disponiveis'];

        $uniqueEntries = array();
        if ($productsFile) {
            fputcsv($productsFile, $headers, ',', '"', '\\');
            foreach ($all_product_details as $details) {
                $pageNumber++;
                foreach ($details['product_names'] as $index => $name) {
                    $productNumber++;
                    $code = $details['product_codes'][$name] ?? null;
                    $price = $details['prices'][$index] ?? null;
                    $filtered_price = Util::filter_price($price, $max_price, $min_price);
                    $quantity = $details['quantity'];

                    $haystackLc = str_replace(' ', '', strtolower($name));
                    $productLc = str_replace(' ', '', strtolower($productName));
                    if ($filtered_price !== null && str_contains($haystackLc, $productLc)) {
                        if(!in_array($name, $uniqueEntries)) {
                            $line = [
                                'Id' => $code,
                                'Produto' => $name,
                                'Preco' => $filtered_price,
                                'Quantidade' => $quantity
                            ];
                            fputcsv($productsFile, $line, ',', '"', '\\');

                        }
                    } else {
                        $productFails++;
                    }
                }
                if($productFails == $productNumber) {
                    $this->json_response = json_encode(["status" => "error", "message" => "Nenhum produto com o valor especificado encontrado na pagina " . $pageNumber]);
                }
            }

            if($productFails == $productNumber) {
                die(json_encode(["status" => "error", "message" => "Nenhum produto encontrado, arquivo nao sera salvo."]));
            }
            
            fclose($productsFile);
            $this->json_response = json_encode(["status" => "success", "message" => "Produtos salvos no arquivo produtos.csv"]);
        } else {
            $this->json_response = json_encode(["status" => "error", "message" => "Erro ao abrir o arquivo para escrita"]);
        }
        
        return $this->json_response;
    }

}
?>