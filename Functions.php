<?php
require_once 'Util.php';

header('Content-type: text/html; charset=UTF-8');
class Functions {
    private $agent = 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0';

    public $multiCurl;

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

    public function execute_concurrent_requests($url, $pages, $max_price, $min_price) {
        $curl_handles = [];
        $this->multiCurl = curl_multi_init();
    
        for ($i = 1; $i <= $pages; $i++) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url . "?page_number=" . $i);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($ch, CURLOPT_USERAGENT, $this->agent);
            curl_multi_add_handle($this->multiCurl, $ch);
            $curl_handles[] = $ch;
        }

    
        do {
            $status = curl_multi_exec($this->multiCurl, $active);
            if ($status == CURLM_OK && $active) {
                curl_multi_select($this->multiCurl);
            }
        } while ($status == CURLM_OK && $active);
        
    
        $filtered_jsons = [];
        $all_product_details = [];
    
        foreach ($curl_handles as $index => $ch) {
            $response = curl_multi_getcontent($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
            if ($http_code == 404) {
                die(json_encode(["error" => "true", "message" => "pagina nao existe"]));
            } else if ($http_code == 200 && !empty($response)) {
                $mainElem = Util::get_json_content($response);
                $mainElem = preg_replace('/<[^>]*>/', '', $mainElem);
                $json_obj = json_decode($mainElem, true);
                $json_obj_filtered = Util::filter_json($json_obj);
                $filtered_jsons[] = $json_obj_filtered;
    
                $product_details = Util::get_products_details($json_obj_filtered);
                $all_product_details[] = $product_details;
            } else {
                die(json_encode(["error" => "true", "message" => "pagina nao existe"]));
            }
    
            curl_multi_remove_handle($this->multiCurl, $ch);
            curl_close($ch);
        }
    
        $productsFile = fopen("products.txt", 'a');
        if ($productsFile) {
            foreach ($all_product_details as $details) {
                foreach ($details['product_names'] as $index => $name) {
                    $code = $details['product_codes'][$name] ?? 'Unknown Code';
                    $price = $details['prices'][$index] ?? 'Unknown Price';
                    $filtered_price = Util::filter_price($price, $max_price, $min_price);
                    if ($filtered_price === NULL) {
                        die(json_encode(["status" => "error", "message" => "Intervalo de valor nao encontrado"]) . "\n");
                    }
                    fwrite($productsFile, "Id: $code, Produto: $name, Preco: $filtered_price" . PHP_EOL);
                }
            }
            fclose($productsFile);
            echo "Produtos salvos no arquivo produtos.txt" . PHP_EOL;
        } else {
            echo "Error: Erro ao salvar o arquivo" . PHP_EOL;
        }
    
        array_unique(file("products.txt"));
    
        return $filtered_jsons;
    }
    

    
    
}
?>