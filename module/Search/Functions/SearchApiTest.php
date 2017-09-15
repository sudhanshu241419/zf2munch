<?php

namespace Search;

/**
 * Description of ApiTest
 *
 * @author dhirendra
 */
class SearchApiTest {
    
    private $host;
    
    public function __construct() {
        $this->host = $_SERVER['REQUEST_SCHEME'] . '://'.$_SERVER['SERVER_NAME'];
    }
    
    public function getSearchApiTestData($device){
        $start_time = microtime(true);
        $response['device'] = $device;
        switch ($device) {
            case 'web':
                $response['test_data'] = $this->getSearchApiTestDataWeb();
                break;
            case 'mob':
                $response['test_data'] = $this->getSearchApiTestDataMob();
                break;
            default:
                $response['error'] = 'device param must be web or mob';
                break;
        }
        $response['time_taken_milli'] = microtime(true) - $start_time;
        return $response;
    }

    private function getSearchApiTestDataWeb() {
        //$sst = ['all', 'deliver', 'takeout', 'dinein', 'reservation'];
        $sst = ['a', 'de', 't', 'di', 'r'];
        $ovt = ['r', 'f'];

        $at = ['c', 'n', 'z', 's']; //at
        $in = ['New York', 'Nomad', '10001', 'street address']; //av
        $at_ind = rand(0, 3);

        $debug = isset($_REQUEST['DeBuG']) ? $_REQUEST['DeBuG'] : '403';
        $req_params = array(
            'DeBuG=' . $debug,
            'sst=' . $sst[rand(0, 4)],
            'ovt=' . $ovt[rand(0, 1)],
            'at=' . $at[$at_ind],
            'in=' . urlencode($in[$at_ind]),
            'lat=40.74407',
            'lng=-73.98522',
            'time=' . rand(0, 2359),
            'date=' . date('Y-m-d'),
            'term=am',
            'page=' . rand(1, 2),
            'token=' . $_REQUEST['token']
        );

        $common_url = $this->host . '/wapi/search?' . implode('&', $req_params); //without request type
        //key is reqtype
        $search_urls = array(
            'rt_s' => $common_url . '&rt=s',
            'rt_getCuisineCount' => $common_url . '&rt=getCuisineCount',
            'rt_pickAnArea' => $common_url . '&rt=pickAnArea',
            'rt_getRestCount' => $common_url . '&rt=getRestCount',
            'rt_topLocation' => $common_url . '&rt=topLocation',
        );

        $testData = array();
        $testData['params'] = $req_params;
        foreach ($search_urls as $reqtype => $url) {
            $data = $this->getUrlDataGet($url);
            $testData['apis'][$reqtype] = array(
                'status_code' => $data['status_code'],
                'response' => $data['data'],
                'url' => $url,
            );
        }
        return $testData;
    }

    private function getSearchApiTestDataMob() {
        $tab = ['all', 'delivery', 'takeout', 'dinein', 'reservation'];
        $view_type = ['restaurant', 'food'];

        $at = ['city', 'nbd', 'zip', 'street']; //at
        $av = ['New+York', 'Nomad', '10001', 'street+address']; //av
        $at_ind = rand(0, 3);

        $req_params = array(
            'mob=true',
            'DeBuG=403',
            'at=' . $at[$at_ind],
            'av=' . urlencode($av[$at_ind]),
            'latlong=40.74407,-73.98522',
            'tab=' . $tab[rand(0, 4)],
            'view_type=' . $view_type[rand(0, 1)],
            'stime=' . rand(0, 2359),
            'sdate=' . date('Y-m-d'),
            'term=am',
            'start=0',
            'rows=1',
            'token=' . $_REQUEST['token']
        );

        $common_url = $this->host . '/api/search?' . implode('&', $req_params); //without request type
        //key is reqtype
        $search_urls = array(
            'search' => $common_url . '&reqtype=search',
            'counters' => $common_url . '&reqtype=counters',
            'landmarks' => $common_url . '&reqtype=landmarks',
            'suggest' => $common_url . '&reqtype=suggest',
            'totalrescount' => $common_url . '&reqtype=totalrescount',
            'friendsearch' => $common_url . '&reqtype=friendsearch'
        );

        $testData = array();
        $testData['params'] = $req_params;
        foreach ($search_urls as $reqtype => $url) {
            $data = $this->getUrlDataGet($url);
            $testData['apis']['reqtype_'.$reqtype] = array(
                'status_code' => $data['status_code'],
                'message' => $data['data']['message'],
                'response' => $data['data']['data'],
                'url' => $url,
            );
        }
        return $testData;
    }

    private function getUrlDataGet($url) {
        $response = array();
        $ch = curl_init();
        $curl_params = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => TRUE
        );
        curl_setopt_array($ch, $curl_params);
        $response['data'] = json_decode(curl_exec($ch), true);
        $response['status_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $response;
    }

}
