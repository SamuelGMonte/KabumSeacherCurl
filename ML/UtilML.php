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


    // public static function optimize_json_ml($json_string) {
    //     $json_obj = json_decode($json_string, true);
    //     $optimized_json_array = [];
    //     if (isset($json_obj['pageState']['initialState']['results'])) {
    //         $optimized_json_array['pageState']['initialState']['results'] = $json_obj['pageState']['initialState']['results'];
    //     }
    
    //     if (isset($json_obj['pageStoreState']['locationSearch']['initialState']['results'])) {
    //         $optimized_json_array['pageStoreState']['locationSearch']['initialState']['results'] = $json_obj['pageStoreState']['locationSearch']['initialState']['results'];
    //     }
    
    //     if (isset($json_obj['pageStoreState']['locationSearch']['initialState']['pagination']['page_count'])) {
    //         $optimized_json_array['pageStoreState']['locationSearch']['initialState']['pagination']['page_count'] = $json_obj['pageStoreState']['locationSearch']['initialState']['pagination']['page_count'];
    //     }
    
    //     if (isset($json_obj['pageState']['initialState']['pagination']['next_page'])) {
    //         $optimized_json_array['pageState']['initialState']['pagination']['next_page'] = $json_obj['pageState']['initialState']['pagination']['next_page'];
    //     }

    //     if (isset($json_obj['pageState']['initialState']['results']['polycard']['components'])) {
    //         $optimized_json_array['pageState']['initialState']['results']['polycard']['components'] = $json_obj['pageState']['initialState']['results']['polycard']['components'];
    //     }

    //     if (isset($json_obj['pageStoreState']['locationSearch']['initialState']['results']['polycard']['components'])) {
    //         $optimized_json_array['pageStoreState']['locationSearch']['initialState']['results']['polycard']['components'] = $json_obj['pageStoreState']['locationSearch']['initialState']['results']['polycard']['components'];
    //     }

    //     return json_encode($optimized_json_array);
    // }
    

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
        // $quantity = [];
        file_put_contents("teste2.json", json_encode($filtered_json));

           
        $targetKey = "price";
        $result_price[] = self::searchForKeyValuePair($filtered_json, $targetKey);

        foreach ($result_price as $res_key_price => $res_price) {
            $current_price = self::searchForKeyValuePair($res_price, "current_price");
        
            if (is_array($current_price)) {
                foreach ($current_price as $nested_array) {
                    if (isset($nested_array['value'])) {
                        $prices[] = $nested_array['value'];
                    }
                }
            }
        }


        $targetKey = "title";
        $name_result[] = self::searchForKeyValuePair($filtered_json, $targetKey);

        foreach ($name_result as $name_key => $name) {
            if (is_array($name)) {
                foreach($name as $nm) {
                    if (isset($nm['text'])) {
                        $product_names[] = $nm['text'];
                    }
                    
                }
            }
            // $product_names = $name[$name_key]['text'];
        }

        // var_dump($product_names);

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