<?php
require_once 'FunctionsML.php';

class UtilML {
    public static $dom;

    public static function initialize() {
        self::$dom = new DOMDocument();
    }


    public static function get_json_content_ml($ch) {
        $output = "";

        if (empty($ch)) {
            echo "Received empty content.<br>";
            return null; 
        }

        @self::$dom->loadHTML($ch);
        
        $xpath = new DOMXPath(self::$dom);

        $mainElem = $xpath->query('//*[@id="__PRELOADED_STATE__"]');

        if($mainElem->length > 0) {
            $output = $mainElem->item(0)->nodeValue;

            $optimizedData = self::optimize_json_ml($output);

            $json = json_encode($optimizedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); 

            file_put_contents("overflow2.json", $json);

        }
        else {
            echo "JSON nao encontrado." . PHP_EOL;
        }

        return $json;
    }


    public static function optimize_json_ml($json_string) {
        $json_obj = json_decode($json_string, true);
        return $json_obj['pageState']['initialState'];
    }


   public static function get_products_details_ml($json_string) {
        $json = json_decode($json_string, true);
        $data = $json['results'];
        
        if (empty($data)) {
            die(json_encode(["error" => "true", "message" => "pagina nao existe"]) . "\n");
        }
        
        $product_names = [];
        $product_codes = [];
        $prices = [];
        // $quantity = [];
        
        if (isset($data)) {
            foreach($data as $dt) {
                if(isset($dt['polycard'])) {
                    $usable_path = $dt['polycard']['components'];
                    foreach($usable_path as $formated) {
                        if (isset($formated['type']) && $formated['type'] === 'price') {
                            $prices[] = $formated['price']['current_price']['value'] ?? null; 
                        } else if($formated['type'] === 'title') {
                            $product_names[] = $formated['title']['text'];
                        }
                    }

                }

                // if (isset($usable_path['title'], $usable_path['price']['current_price']['value'])) {
                //     $product_names[] = $usable_path['title']['text'];  
                //     $product_codes[$usable_path['title']['text']] =  $dt['polycard']['metadata']['product_id']; 
                //     $prices[] = $usable_path['price']['current_price']['value'];
                //     // $quantity = $dt['quantity'];
                // } 
            }
        }
        

        return [
            'product_names' => $product_names,
            'product_codes' => $product_codes,
            'prices' => $prices,
            // 'quantity' => $quantity
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