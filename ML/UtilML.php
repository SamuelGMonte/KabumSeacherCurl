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
            json_encode(["error" => "true", "message" => "Empty content"]);
            return null; 
        }

        @self::$dom->loadHTML($ch);
        
        $xpath = new DOMXPath(self::$dom);

        $mainElem = $xpath->query('//*[@id="__PRELOADED_STATE__"]');

        if($mainElem->length > 0) {
            $output = $mainElem->item(0)->nodeValue;
            $json_string = json_encode($output);
            // $optimizedJson = self::optimize_json_ml($output);
        }
        else {
            json_encode(["error" => "true", "message" => "JSON nao encontrado"]);
        }
        return $output;
    }


    public static function searchForKeyValuePair($array, $targetKey, $targetValue = null) {
        $result = []; 
    
        foreach ($array as $key => $value) {
            if ($targetValue === null) {
                if ($key === $targetKey) {
                    $result[] = $value; 
                }
            } else {
                if ($key === $targetKey && $value === $targetValue) {
                    return $value; 
                }
            }
    
            if (is_array($value) || is_object($value)) {
                $recursiveResult = self::searchForKeyValuePair((array)$value, $targetKey, $targetValue);
                if ($recursiveResult !== null) {
                    if ($targetValue === null) {
                        $result = array_merge($result, (array)$recursiveResult);
                    } else {
                        return $recursiveResult;
                    }
                }
            }
        }
    
        return $targetValue === null ? $result : null;
    }

    

    public static function get_products_details_ml($json) {
        
        $locationSearchInitialState = $json['pageStoreState']['locationSearch']['initialState']['results'];
        $pageStateInitialState = $json['pageState']['initialState'];

        $filtered_json = array_merge($locationSearchInitialState, $pageStateInitialState);

        if (empty($filtered_json)) {
            die(json_encode(["status" => "error", "message" => "pagina nao existe"]));
        }
        
        $product_names = [];
        $product_codes = [];
        $prices = [];

           
        $targetKey = "price";
        $result_price[] = self::searchForKeyValuePair($filtered_json, $targetKey);

        $product_details = $json['pageStoreState']['locationSearch']['initialState']['seo']['schema']['product_list'];
        foreach($product_details as $detail_key => $details) {
            $prices[] = $details['item_offered']['price'];
            $product_codes[] = $details['id'];
            $product_names[] = $details['name'];
        }

        return [
            'product_names' => $product_names,
            'product_codes' => $product_codes,
            'prices' => $prices,
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