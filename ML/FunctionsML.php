<?php
require_once 'UtilML.php';

header('Content-type: text/html; charset=UTF-8');

class FunctionsML {
    private $agent = 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0';

    public $multiCurl;
    public $json_response;

    public function __construct() {
        $this->multiCurl = curl_multi_init();
        UtilML::initialize();
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

    public function __destruct() {
        curl_multi_close($this->multiCurl);
    }

    public function execute_requests_ml($url, $productName) {
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

        // var_dump($response);
        if($response) {
            $mainElem = UtilML::get_json_content_ml($response);
            $json_obj = json_decode($mainElem, true); 
            $totalPages = $json_obj['pageState']['initialState']['melidata_track']['event_data']['total'];

            file_put_contents("total_ml.json", json_encode($totalPages));
        }

    }
}

?>