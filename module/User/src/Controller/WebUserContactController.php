<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\UserFunctions;
use User\SocialmediaOauthConnect;
use User\Model\UserImportedContactList;
use Zend\Session\Container;
use ZendOAuth;
use Zend\Http\Request;

class WebUserContactController extends AbstractRestfulController {

    const FORCE_LOGIN = true;

    public function get($id) {
        $newdata = array();
        $userFunctions = new UserFunctions();
        $userImportContactModel = new UserImportedContactList();
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        $userId = $session->getUserId();
        $locationData = $session->getUserDetail('selected_location');
        $currentDate = $userFunctions->userCityTimeZone($locationData);
        $type = $this->getQueryParams('type');
        $fetch = $this->getQueryParams('fetch');

        if ($type == 'gmail' && $fetch == 'no') {
            $oauth = $this->gmailOauth($session);
            $oauth->Authorize();
        } elseif ($id == 'googleauthenticate') {
            return $this->getGmailContactList($session, $currentDate);
        } elseif ($type == 'hotmail' && $fetch == 'no') {
            $oauth = $this->hotmailOauth($session);
            $oauth->Authorize();
            exit();
        } elseif ($id == 'microsoftauthenticate') {
            return $this->getHotmailContactList($session, $currentDate);
        } elseif ($type == 'yahoo' && $fetch == 'no') {
            return $this->yahooAuthenticate($session);
        } elseif ($id == 'yahootauthenticate') {
            $this->getYahooData($session->token);
            exit();
            // return $this->getYahoomailContactList($session, $currentDate);
        } elseif ($type == 'gmail' && $fetch == 'yes') {
            $user = $userImportContactModel->getUser(array(
                'columns' => array(
                    'contact_list'
                ),
                'where' => array(
                    'user_id' => $userId,
                    'contact_source' => 'gmail'
                )
            ));
            if ($user['contact_list'] != 'null') {
                $contactList = $user->getArrayCopy();
                $list = $contactList['contact_list'];
                $newdata = json_decode($list, true);
                return $newdata;
            } else {
                return array();
            }
        } elseif ($type == 'hotmail' && $fetch == 'yes') {
            $user = $userImportContactModel->getUser(array(
                'columns' => array(
                    'contact_list'
                ),
                'where' => array(
                    'user_id' => $userId,
                    'contact_source' => 'hotmail'
                )
            ));

            if ($user['contact_list'] != 'null') {
                $contactList = $user->getArrayCopy();
                $list = $contactList['contact_list'];
                $newdata = json_decode($list, true);
                return $newdata;
            } else {
                return array();
            }
        } elseif ($type == 'yahoo' && $fetch == 'yes') {
            $user = $userImportContactModel->getUser(array(
                'columns' => array(
                    'contact_list'
                ),
                'where' => array(
                    'user_id' => $userId,
                    'contact_source' => 'yahoo'
                )
            ));
            if ($user['contact_list'] != 'null') {
                $contactList = $user->getArrayCopy();
                $list = $contactList['contact_list'];
                $newdata = json_decode($list, true);
                return $newdata;
            } else {
                return array();
            }
        }
    }

    /**
     * Gmail Authenticate
     *
     * @param unknown $session            
     * @return \User\SocialmediaOauthConnect
     */
    public function gmailOauth($session) {
        $config = $this->getServiceLocator()->get('Config');
        $oauth = new SocialmediaOauthConnect();
        $oauth->provider = "Google";
        $oauth->client_id = $config['constants']['google+']['client_id'];
        $oauth->client_secret = $config['constants']['google+']['client_secret'];
        $oauth->scope = $config['constants']['google+']['gmail_scope'];
        $oauth->redirect_uri = PROTOCOL . $config['constants']['google+']['contact_redirect_uri'];
        $oauth->state = $session->token;
        $oauth->Initialize();
        return $oauth;
    }

    /**
     * Hotmail Authenticate
     *
     * @param unknown $session            
     * @return \User\SocialmediaOauthConnect
     */
    public function hotmailOauth($session) {
        $config = $this->getServiceLocator()->get('Config');
        $oauth = new SocialmediaOauthConnect();
        $oauth->provider = "Microsoft";
        $oauth->client_id = $config['constants']['hotmail']['client_id'];
        $oauth->client_secret = $config['constants']['hotmail']['client_secret'];
        $oauth->scope = $config['constants']['hotmail']['scope'];
        $oauth->redirect_uri = PROTOCOL . $config['constants']['hotmail']['redirect_uri'];
        $oauth->state = $session->token;
        $oauth->Initialize();
        return $oauth;
    }

    /**
     * Get Gmail Contact List And Store In Database
     *
     * @param unknown $session            
     * @param unknown $currentDate            
     */
    public function getGmailContactList($session, $currentDate) {
        $config = $this->getServiceLocator()->get('Config');
        $webUrl = PROTOCOL . $config['constants']['web_url'];
        $oauth = $this->gmailOauth($session);
        $oauth->code = $_REQUEST["code"];
        $getData = json_decode($oauth->getUserProfileGmail());
        $gmailContactlist = array();
        $i=0;
        foreach ($getData->feed->entry as $key => $val) {
           $arr = (array) $val;
           $email ='';
           $tilte = '' ;
            foreach ($arr as $inner_key => $inner_val) {                
                if ($inner_key == 'gd$email') {
                    $email = $inner_val[0]->address ;                     
                }
                if ($inner_key == 'title') {
                    $arrTtl = (array) $inner_val;
                    $tilte = $arrTtl['$t'] ;                   
                }              
            }
            if($email!='')
            $gmailContactlist[$email]=$tilte ;

            $i++;
        }          
        $contactArray = Array();
        if(count($gmailContactlist)>0){
           
            foreach ($gmailContactlist as $key => $value) {
                if (!empty($value)) {
                    $contactArray[] = array(
                        'email' => $key,
                        'name' => $value
                    );
                }
            }
        }        
        $userId = $session->getUserId();
        $userImportContactModel = new UserImportedContactList();
        $user = $userImportContactModel->getUser(array(
            'column' => array(
                'id',
                'user_id'
            ),
            'where' => array(
                'user_id' => $userId,
                'contact_source' => 'gmail'
            )
        ));

        $contactList = json_encode($contactArray);
        if (!empty($user) && $user != null) {
            $contact = $userImportContactModel->updateUserContactList($userId, $contactList, 'gmail', $currentDate);
        } else {
            $contact = $userImportContactModel->addUserContactList($userId, $contactList, 'gmail', $currentDate);
        }
        echo '<script>     
        window.location.href ="' . $webUrl . '/addfriends/gmail";
                      </script>';
        die();
        exit();
    }

    /**
     * Get Hotmail Contact List And Store In Database
     *
     * @param unknown $session            
     * @param unknown $currentDate            
     */
    public function getHotmailContactList($session, $currentDate) {
        $config = $this->getServiceLocator()->get('Config');
        $webUrl = PROTOCOL . $config['constants']['web_url'];
        $oauth = $this->hotmailOauth($session);
        $oauth->code = $_REQUEST["code"];
        $getData = json_decode($oauth->getUserProfile());
        $data = $getData->data;
        $contactArray = array();
        foreach ($data as $key => $value) {
            $data1 = (array) $value;

            if ($value->emails->preferred) {
                $name = $value->name;
                $myemail = $value->emails->preferred;
                // $msn_list[$myemail] = $name;

                $contactArray[] = array(
                    'email' => $myemail,
                    'name' => $name
                );
            }
        }

        $userId = $session->getUserId();
        $userImportContactModel = new UserImportedContactList();
        $user = $userImportContactModel->getUser(array(
            'column' => array(
                'id',
                'user_id'
            ),
            'where' => array(
                'user_id' => $userId,
                'contact_source' => 'hotmail'
            )
        ));

        if (!empty($user) && $user != null) {
            $contactList = json_encode($contactArray);
            $contact = $userImportContactModel->updateUserContactList($userId, $contactList, 'hotmail', $currentDate);
        } else {
            $contactList = json_encode(array_unique($contactArray));
            $contact = $userImportContactModel->addUserContactList($userId, $contactList, 'hotmail', $currentDate);
        }
        echo '<script>     
        window.location.href = "' . $webUrl . '/addfriends/hotmail";
                      </script>';

        exit();
    }

}
