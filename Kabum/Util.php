<?php
require_once 'Functions.php';

class Util {
    public static $dom;

    public static function initialize() {
        self::$dom = new DOMDocument();
    }


    public static function get_json_content_kabum($ch) {
        
        if (empty($ch)) {
            json_encode(["error" => "true", "message" => "Empty content"]);
            return null; 
        }

        @self::$dom->loadHTML($ch);

        $mainElem = self::$dom->getElementById("__NEXT_DATA__");

        $output = self::$dom->saveHTML($mainElem);

        return $output;
    }

    public static function filter_json($json, $is_formatted) {
        if($is_formatted) {
            if (isset($json['props']['pageProps']['data'])) {
                foreach ($json['props']['pageProps']['data'] as $subKey => $subValue) {
                    if ($subKey == 'cookieIsMobile' || $subKey == 'categories') {
                        unset($json['props']['pageProps']['data'][$subKey]);
                    }
                }
            } else {
                json_encode(["status" => "error", "message" => "Key 'data' does not exist.<br>"]);
            }
        } else {
            $json = json_decode($json['props']['pageProps']['data'], true);
            if (isset($json)) {
                foreach ($json as $subKey => $subValue) {
                    if ($subKey == 'cookieIsMobile' || $subKey == 'categories') {
                        unset($json[$subKey]);
                    }
                }
            } else {
                json_encode(["status" => "error", "message" => "Key 'data' does not exist.<br>"]);
            }
        }

        return $json;
    }



   public static function get_products_details($json, $is_formatted) {
        if($is_formatted) {
            $data = $json['props']['pageProps']['data']['catalogServer']['data'];
            
            if (empty($data)) {
                die(json_encode(["error" => "true", "message" => "pagina nao existe"]));
            }
            
            $product_names = [];
            $product_codes = [];
            $prices = [];
            $quantity = [];
            
        
            if (isset($data)) {
                foreach($data as $dt) {
                    if (isset($dt['name'], $dt['code'], $dt['price'])) {
                        $product_names[] = $dt['name'];  
                        $product_codes[$dt['name']] = $dt['code']; 
                        $prices[] = $dt['price'];
                        $quantity = $dt['quantity'];
                    } 
                }
            }
        } 
        else {
            $data = $json['catalogServer']['data'];

            if (empty($data)) {
                die(json_encode(["error" => "true", "message" => "pagina nao existe"]));
            }
            
            $product_names = [];
            $product_codes = [];
            $prices = [];
            $quantity = [];
            
        
            if (isset($data)) {
                foreach($data as $dt) {
                    if (isset($dt['name'], $dt['code'], $dt['price'])) {
                        $product_names[] = $dt['name'];  
                        $product_codes[$dt['name']] = $dt['code']; 
                        $prices[] = $dt['price'];
                        $quantity = $dt['quantity'];
                    } 
                }
            }
        }
        

        return [
            'product_names' => $product_names,
            'product_codes' => $product_codes,
            'prices' => $prices,
            'quantity' => $quantity
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