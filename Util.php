<?php
require_once 'Functions.php';

class Util {
    public static $dom;

    public static function initialize() {
        self::$dom = new DOMDocument();
    }


    public static function get_json_content($ch) {
        
        if (empty($ch)) {
            echo "Received empty content.<br>";
            return null; 
        }

        @self::$dom->loadHTML($ch);
        

        $mainElem = self::$dom->getElementById("__NEXT_DATA__");

        $output = self::$dom->saveHTML($mainElem);

        return $output;
    }

    public static function filter_json($json) {
        

        if (isset($json['props']['pageProps']['data'])) {
            foreach ($json['props']['pageProps']['data'] as $subKey => $subValue) {
                if ($subKey == 'cookieIsMobile' || $subKey == 'categories') {
                    unset($json['props']['pageProps']['data'][$subKey]);
                }
            }
        } else {
            // Handle the case where 'data' is not set or is null
            echo "Key 'data' does not exist.<br>";
        }
        return $json;
    }

    public static function get_item_count($json) {
        $totalItemsCount = $json['props']['pageProps']['data']['catalogServer']['meta']['totalItemsCount'];
        $totalPagesCount = $json['props']['pageProps']['data']['catalogServer']['meta']['totalPagesCount'] ?? null;

        $response = "";
        if ($totalItemsCount && $totalPagesCount) {
            $response = $totalItemsCount . " resultados disponíveis em " 
                    . $totalPagesCount . " páginas.";
        } else {
            $response = "Informações insuficientes para exibir resultados.";
        }

        return $response;
    }

   public static function get_products_details($json) {
        $data = $json['props']['pageProps']['data']['catalogServer']['data'];
        
        if (empty($data)) {
            die(json_encode(["error" => "true", "message" => "pagina nao existe"]));
        }
        
        $product_names = [];
        $product_codes = [];
        $prices = [];
        
    
        if (isset($data)) {
            foreach($data as $dt) {
                if (isset($dt['name'], $dt['code'], $dt['price'])) {
                    $product_names[] = $dt['name'];  
                    $product_codes[$dt['name']] = $dt['code']; 
                    $prices[] = $dt['price'];
                } 
            }
        }
        

        return [
            'product_names' => $product_names,
            'product_codes' => $product_codes,
            'prices' => $prices
        ];
    }
    
    public static function check_size($json, $pages) {
        $totalItemsCount = $json['props']['pageProps']['data']['catalogServer']['meta']['totalItemsCount'];
        
        if($pages < 0 || $pages > $totalItemsCount) {
            die(json_encode(["status" => "error", "message" => "Pagina inexistente"]));
        }

    }

    public static function filter_price($price, $max_price, $min_price) {
        if (is_numeric($price) && is_numeric($max_price) && is_numeric($min_price)) {
            if ($price < $min_price || $price > $max_price) {
                return null;
            }
        }
        return $price;
    }
   
}

?>