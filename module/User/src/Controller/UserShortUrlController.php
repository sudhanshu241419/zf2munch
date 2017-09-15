<?php
namespace User\Controller;
use User\UserFunctions;
use MCommons\Controller\AbstractRestfulController;
class UserShortUrlController extends AbstractRestfulController {
    public function create($data) {
        if (isset($data['shorturl']) && $data['shorturl'] != '') {
            $login = 'rkhatak';
            $appkey = 'R_105007a96c804e8fa0c54afd79c365b5';
            $bitSortUrl = $this->get_bitly_short_url($data['shorturl'], $login, $appkey);
            $data['bitshorturl'] = trim($bitSortUrl);
        } else {
            $data['error'] = 'Url is required';
        }
        return $data;
    }
    public function get_bitly_short_url($url, $login, $appkey, $format = 'txt') {
        $connectURL = 'http://api.bit.ly/v3/shorten?login=' . $login . '&apiKey=' . $appkey . '&uri=' . urlencode($url) . '&format=' . $format;
        return $this->curl_get_result($connectURL);
    }
    public function curl_get_result($url) {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}