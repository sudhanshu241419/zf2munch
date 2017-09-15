<?php

use Auth\Model\UserSession;

class ClickaTell {
  private $ToAdress;
  private $SmsText;

    public function sendSms($clickatellConfig,$userMobNo=false,$SmsText=false)
    {
       $smsresponse = false;
       if($userMobNo && $SmsText)
       {
            $SmsText = urlencode($SmsText);
            //auth call
            $url_auth = $clickatellConfig['cat_auth_url']."?user=".$clickatellConfig['cat_ac_username']."&password=".$clickatellConfig['cat_ac_password']."&api_id=".$clickatellConfig['cat_api_id'];
            $ret_auth = $this->sendRequest($url_auth);// do auth call
            $sess = explode(":",$ret_auth); // explode Auth response. 
            if ($sess[0] == "OK") {
                $sess_id = trim($sess[1]); // remove any whitespace
                $url_send = $clickatellConfig['cat_sendmsg_url']."?session_id=".$sess_id."&to=".$userMobNo."&text=".$SmsText."&mo=1&from=".$clickatellConfig['cat_from'];
                 $ret_send = $this->sendRequest($url_send);// do sendmsg call
                $send = explode(":",$ret_send);
                if ($send[0] == "ID") {
                    $smsresponse = true;
                } 
            } 
        }
        return $smsresponse;
    }

    public function sendRequest($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

}