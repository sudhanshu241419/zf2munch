<?php

namespace Dashboard;

use MCommons\StaticOptions;

class DashboardFunctions {

    public $dashboardId;
    public $restaurantId;
    public $cityId;
    public $timezone;
    public $token = false;
    public $location = array();
    protected $_redisCache = false;
    public $dashboardDetails = array();
    public $userId;
    public $deliveryTime;
    public $order_amount;
    public $order_type;
    public $restaurant_name;
    public $isFirstOrder = false;
    public $refId = null;
    public $_allowedImageTypes = array(
        'gif' => 'image/gif',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'bmp' => 'image/bmp',
        'jpg' => 'image/jpg',
        'pdf' => 'application/pdf'
        );

    public function __construct() {
        if (!$this->token) {
            $this->token = StaticOptions::getDashboardToken();
        }
        $this->_redisCache = StaticOptions::getRedisCache();
    }

    public function createQueue($data, $class) {
        \MCommons\StaticOptions::resquePush($data, $class);
    }

    public function saveDashboard($dashboardDetails) {
        
        if ($this->_redisCache && $this->token) {
            if ($this->_redisCache->hasItem($this->token)) {
                $data = $this->_redisCache->getItem($this->token);
                if ($data['dashboard_id'] == "") {
                    $this->_redisCache->setItem($this->token, $dashboardDetails);
                    return true;
                } elseif ($data['dashboard_id'] == $dashboardDetails['dashboard_id']) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } elseif ($this->token) {
            return $this->prepairToken($dashboardDetails);
        }
    }

    public function getDashboardDetail() { 
        if ($this->token) {                  
           $dashboardDetails = $this->findTokenFromRedis($this->token);
           if(!$dashboardDetails['dashboard_id']){
               throw new \Exception("Credential not found", 400);
           }
           $this->dashboardId = $dashboardDetails['dashboard_id'];
           if(isset($dashboardDetails['dashboard_details'])){
               $this->dashboardDetails = @unserialize($dashboardDetails['dashboard_details']);
           }else{
               $this->dashboardDetails = $dashboardDetails;
           }
           
           //pr($this->dashboardDetails,1);
           $this->restaurantId = $this->dashboardDetails['dashboard_restaurant_id'];
           $this->location = array(
               'timezone'=>$this->dashboardDetails['timezone'],
               'city_id'=> $this->dashboardDetails['city_id'],
               'city_name'=>$this->dashboardDetails['city_name'],
               'state_code'=>$this->dashboardDetails['state_code']
            );
           return true;
        } else {
            return false;
        }
    }

    public function logoutDashboard() {
        $tokenModel = new Model\Token();
        $tokenModel->token = $this->token;
        $tokenModel->delete();
        return true;
    }

    public function isDashboardLogin() {
        if ($this->getDashboardDetail()) {
            return true;
        }
        return false;
    }

    public function getRestaurantId() {
        $this->getDashboardDetail();
        return (int) $this->restaurantId;
    }

    public function getDashboardId() {
        $this->getDashboardDetail();
        return (int) $this->dashboardId;
    }

    public function getLocation() {
        $this->getDashboardDetail();
        return $this->location;
    }

    public function CityTimeZone() {
        $stateCode = isset($this->locationData ['state_code']) ? $this->locationData ['state_code'] : 'NY';
        $cityDateTime = StaticOptions::getRelativeCityDateTime(array(
                    'state_code' => $stateCode
        ));
        return $cityDateTime->format("Y-m-d H:i:s");
    }

    public function to_utf8($in) {
        if (is_array($in)) {
            foreach ($in as $key => $value) {
                $out [$this->to_utf8($key)] = $this->to_utf8($value);
            }
        } elseif (is_string($in)) {
            if (mb_detect_encoding($in) != "UTF-8")
                return utf8_encode($in);
            else
                return $in;
        } else {
            return $in;
        }
        return $out;
    }

    public function findTokenFromRedis($token) {
        $tokenModel = new \Dashboard\Model\Token();
        $tokenModel->getDbTable()->setArrayObjectPrototype('Dashboard\Model\Token');
        return $tokenModel->findToken($token);
    }

    public function handleInvalidToken($token) {
        $tokenModel = new Model\Token();
        $tokenData = $this->findTokenDetail($token, $tokenModel);
        if ($tokenData) {
            //$tokenModel->id = $tokenData[0]['id'];
            return true;
        } else {
            throw new \Exception("Invalid token", 400);
        }
        $this->prepairToken();
        $tokenModel->save();
    }

    private function findTokenDetail($token, $tokenModel) {
        if ($this->_redisCache) {
            if ($this->_redisCache->hasItem($token)) {
                $data = $this->_redisCache->getItem($token);
                if (empty($data)) {
                    return true;
                }
            } else {
                return false;
            }
        }
        $options = array(
            'columns' => array(
                'id',
                'dashboard_details'
            ),
            'where' => array(
                'token' => $token
            ),
        );

        $tokenModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        return $tokenModel->find($options)->toArray();
    }
    
    public function prepairToken($dashboardDetails = false){
        $tokenModel = new Model\Token();
        if(!$dashboardDetails){
            $dashboardData = [];
            $tokenModel->dashboard_id = '';
            $tokenModel->token = $token;
            $tokenModel->ttl = 315360000;
            $tokenModel->created_at = date('Y-m-d H:i');
            $tokenModel->dashboard_details = @serialize($dashboardData);
            $tokenModel->last_update_timestamp = time();
        }else{
            $tokenModel->dashboard_id = $dashboardDetails['dashboard_id'];
            $tokenModel->token = $this->token;
            $tokenModel->ttl = 315360000;
            $tokenModel->created_at = date('Y-m-d H:i');
            $tokenModel->dashboard_details = @serialize($dashboardDetails);
            $tokenModel->last_update_timestamp = time();
        }        
        
        $options = array(
            'columns' => array(
                'id',
                'dashboard_details',
                'dashboard_id'
            ),
            'where' => array(
                'token' => $this->token
            ),
        );
        $tokenModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $dtoken = $tokenModel->find($options)->toArray();

        if ($dtoken[0]['dashboard_id'] == "") {
            $tokenModel->id = $dtoken[0]['id'];            
            $tokenModel->save();
            return true;
        } elseif ($dtoken[0]['dashboard_id'] == $dashboardDetails['dashboard_id']) {
            return true;
        } 
        return false;       
    }
    public function findImageUrlNormal($image, $userId = null) {
        if ($image == '' || $image == NULL) {
            return WEB_URL . 'img/no_img.jpg';
        }

        if (preg_match('/http/', strtolower($image))) {
            return $image;
        }

        if (@getimagesize(APP_PUBLIC_PATH . USER_IMAGE_UPLOAD . "profile/" . $userId . DS . $image) !== false) {
            return WEB_URL . USER_IMAGE_UPLOAD . "profile" . DS . $userId . DS . $image;
        }

        return WEB_URL . 'img/no_img.jpg';
    }
    
     public function sendMails($data, $sender = array()) {
        $senderEmail = DASHBOARD_EMAIL_FROM;
        if($data['variables']['host_name']==PROTOCOL.SITE_URL || $data['variables']['host_name']=="iphone" || $data['variables']['host_name']=="android" ){
            $senderName = "Munch Ado";
        }else{
            $senderName = $data['variables']['restaurant_name'];
        }
        $recievers = array(
            $data ['to']
        );
        $template = "email-template/" . $data ['template_name'];
        $layout = (isset($data ['layout'])) ? $data ['layout'] : 'email-layout/default_new';

        $subject = $data ['subject'];
        $resquedata = array(
            'sender' => $senderEmail,
            'sendername' => $senderName,
            'variables' => $data ['variables'],
            'receivers' => $recievers,
            'template' => $template,
            'layout' => $layout,
            'subject' => $subject
        ); 
        StaticOptions::resquePush($resquedata, 'SendEmail');
        // StaticOptions::sendMail ( $sender, $sendername, $recievers, $template, $layout, $data ['variables'], $data ['subject'] );
    }
    public static function formatPhone($num) {
        $num = preg_replace('/[^0-9]/', '', $num);

        $len = strlen($num);
        if ($len == 7)
            $num = preg_replace('/([0-9]{3})([0-9]{4})/', '$1-$2', $num);
        elseif ($len == 10)
            $num = preg_replace('/([0-9]{3})([0-9]{3})([0-9]{4})/', '$1-$2-$3', $num);

        return $num;
    }
    
    public function awardPointForOrder() {
        $points = [];
        $message = '';
        $type = 'order';
        if ($this->order_amount >= 50) {            
            $point = 100 + (2 * $this->order_amount);            
            $message = "You earned ".floor($point)." points with your ".$this->order_type." order from " .$this->restaurant_name."!";
            if ($this->earlyBirdSpecial()) {                
                $message = "Bonus points for you at ".$this->restaurant_name." for placing an order during your first 30 days with Dine & More";
                $point = $point + 100;               
            }
        } else {
            $point = 2 * $this->order_amount;
            $message = "You earned ".floor($point)." points with your ".$this->order_type." order from " .$this->restaurant_name."!";
        }
        if ($this->isFirstOrder) {
            $points = $this->getAllocatedPoints("awardsfirstorder");
            $point = $points['points'] + $point;            
            $message = "You placed your first " . $this->restaurant_name . " Dine & More online order!";
        }

        $point = floor($point);
        $points['message'] = $message;
        $points['points'] = $point;
        return $points;
    }
    
    public function earlyBirdSpecial() {
        $restaurantServer = new Model\RestaurantServer();
        $restaurantServer->user_id = $this->userId;
        $restaurantServer->restaurant_id = $this->restaurantId;
        $restServerDetails = $restaurantServer->findExistingUser();

        if ($this->deliveryTime && !empty($restServerDetails)) {
            $registrationDate = $restServerDetails[0]['date'];
            $date1 = new \DateTime($registrationDate);
            $date2 = new \DateTime($this->deliveryTime);
            $interval = $date1->diff($date2);
            //pr($interval->days,1);
            if ($interval->days <= EARLY_BIRD_SPECIAL_DAYS) {
                return true;
            }
        }
        return false;
    }
    
    public function getAllocatedPoints($key) {
        $pointsModel = new Model\PointSourceDetails();
        $points = $pointsModel->getPointsOnCssKey($key);
        return $points;
    }
    
    public function givePoints($points) {
      
        $userPointsModel = new Model\UserPoint();
      
        $data = array(
        'user_id' => $this->userId,
        'point_source' => isset($points ['id'])?$points ['id']:"",
        'points' => $points ['points'],
        'created_at' => $this->CityTimeZone(),
        'status' => 1,
        'points_descriptions' => $points ['message'],
        'ref_id' => $this->refId,
        'restaurant_id' => ($this->restaurantId) ? $this->restaurantId : 0
        );      

      $userPointsModel->createPointDetail($data);
      $userModel = new Model\User();
      $currentPoints = $userModel->countUserPoints($this->userId);
      if (!empty($currentPoints)) {
          $totalPoints = $currentPoints [0] ['points'] + $points ['points'];
      } else {
          $totalPoints = $points ['points'];
      }
      $userModel->updateUserPoint($this->userId, $totalPoints);
  }
   /**
     * Encrypts plaintext using aes algorithm
     * @author dsyzug
     * @param string $plaintext plain text
     * @return mixed <b>string</b> encrypted-text  or <b>false</b> on failure
     */
    public function aesEncrypt($plaintext) {
        try {
            $aes_params = StaticOptions::getAesOptions();
            //256-bit $key which is a SHA256 hash of $salt and $password.
            $key = hash('SHA256', $aes_params['aes_salt'] . $aes_params['aes_pass'], true);
            //$iv and $iv_base64.  Use a block size of 128 bits (AES compliant) and CBC mode. (Note: ECB mode is inadequate as IV is not used.)
            srand();
            if (function_exists('mcrypt_create_iv')) {
                $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), MCRYPT_RAND);
                if (strlen($iv_base64 = rtrim(base64_encode($iv), '=')) != 22) {
                    return false;
                }
                // Encrypt $decrypted and an MD5 of $decrypted using $key.  MD5 is fine to use here because it's just to verify successful decryption.
                $encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $plaintext . md5($plaintext), MCRYPT_MODE_CBC, $iv));
                return $iv_base64 . $encrypted;
            } else {
                throw new \Exception('Something Went Wrong On encryption card details');
            }
        } catch (\Exception $e) {
            \MUtility\MunchLogger::writeLog($e, 1, 'Something Went Wrong To Encryption Card Details');
            throw new \Exception($e->getMessage(), 400);
        }
    }
    
    /**
     * Decrypts ciphertext using aes algorithm
     * @author dsyzug
     * @param string $encryptedtext plain text
     * @return mixed <b>string</b> decrypted-text or <b>false</b> on failure
     */
    public function aesDecrypt($encryptedtext) {
        $aes_params = StaticOptions::getAesOptions();
        //256-bit $key which is a SHA256 hash of $salt and $password.
        $key = hash('SHA256', $aes_params['aes_salt'] . $aes_params['aes_pass'], true);
        // Retrieve $iv which is the first 22 characters plus ==, base64_decoded.
        $iv = base64_decode(substr($encryptedtext, 0, 22) . '==');
        // Remove $iv from $encrypted.
        $encryptedtext = substr($encryptedtext, 22);
        // Decrypt the data.  rtrim won't corrupt the data because the last 32 characters are the md5 hash; thus any \0 character has to be padding.
        $plaintext = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, base64_decode($encryptedtext), MCRYPT_MODE_CBC, $iv), "\0\4");
        // Retrieve $hash which is the last 32 characters of $decrypted.
        $hash = substr($plaintext, -32);
        // Remove the last 32 characters from $decrypted.
        $plaintext = substr($plaintext, 0, -32);
        //Integrity check.
        if (md5($plaintext) != $hash) {
            return false; //data corrupted, or the password/salt was incorrect
        }
        return $plaintext;
    }
    
    public function uploadBase64Image($base64_string = "", $basePath = "", $destinationDir = "") {
        if ($base64_string == "") {
            throw new \Exception("Image data dose not exits", 400);
        }
        // fetches image mime type from base64 string
        $pos = strpos($base64_string, ';'); 
        $type = explode(':', substr($base64_string, 0, $pos));
        if(isset($type[1])){
            $extension = array_search($type[1], $this->_allowedImageTypes);
        }
        // check if image is valid
        if ($extension == "") {
            throw new \Exception("Invalid image.", 400);
        }      
        
        // creates dir if dosenot exists
        if ($destinationDir != "") {
            $directories = explode(DS, $destinationDir);
            $newpath = $basePath;            
            foreach ($directories as $key => $dir) {
                if($dir!=''){
                    $newpath .= $dir . DS;                
                }
                if (!file_exists($newpath)) {
                  mkdir($newpath, 0777, true);                        
                }
                
            }
        }
              
        
        // fetches image mime type from base64 string
        $pos = strpos($base64_string, ';'); 
        $type = explode(':', substr($base64_string, 0, $pos));
        $extension = array_search($type[1], $this->_allowedImageTypes);
        if ($extension == "") {
            throw new \Exception("Invalid image.", 400);
        }
        // fetches actual image data
        $base64_new_string = explode(",", $base64_string);
        $base64_string = $base64_new_string[1];
        // if base path dosenot exists, it uses temp system directory path
        if ($basePath == "") {
            $outputPath = ini_get('upload_tmp_dir');
            if (!$outputPath || $outputPath == "") {
                $outputPath = sys_get_temp_dir();
            }
            if (!$outputPath || $outputPath == "") {
                throw new \Exception("Invalid Temporary Path to Upload Files");
            }
        } else {
            // uses user defained path
            $outputPath = $basePath . $destinationDir;
            $returnPath = WEB_URL . $destinationDir;
        }
        $uniqueId = uniqid();
        $output_file = $outputPath . DIRECTORY_SEPARATOR . $uniqueId ."." . $extension;
        $return_file = $returnPath . $uniqueId . "." . $extension;
        // open file
        $ifp = @fopen($output_file, "wb");
        if (!$ifp) {
            throw new \Exception("Cannot open file for writing data");
        }
        // write file
        fwrite($ifp, base64_decode($base64_string));
        // close file
        fclose($ifp);  
        return ($return_file);
    }
    public function sendB2BMails($data, $sender = array()) {
        $senderEmail = DASHBOARD_EMAIL_FROM;
        $senderName = "Munch Ado";
        $recievers = array(array(
            $data ['to'],
            $data ['cc'],
            $data ['cc1'],
            $data ['cc2'],
            $data ['cc3'],
       ));
        $template = "email-template/" . $data ['template_name'];
        $layout = (isset($data ['layout'])) ? $data ['layout'] : 'email-layout/default_b2b';

        $subject = $data ['subject'];
        $resquedata = array(
            'sender' => $senderEmail,
            'sendername' => $senderName,
            'variables' => $data ['variables'],
            'receivers' => $recievers,
            'template' => $template,
            'layout' => $layout,
            'subject' => $subject
        );  
        StaticOptions::resquePush($resquedata, 'SendEmail');
        // StaticOptions::sendMail ( $sender, $sendername, $recievers, $template, $layout, $data ['variables'], $data ['subject'] );
    } 
    public function sendB2BPDF($data, $sender = array()) {
        $senderEmail = DASHBOARD_EMAIL_FROM;
        $senderName = "Munch Ado";
        $recievers = array(
            $data ['to'],
            $data ['cc'],
            $data ['cc1'],
            $data ['cc2'],
            $data ['cc3'],
       );
        $template = "email-template/" . $data ['template_name_pdf'];
        $layout = (isset($data ['layout'])) ? $data ['layout'] : 'email-layout/default_b2b';

        $subject = $data ['subject'];
        $resquedata = array(
            'sender' => $senderEmail,
            'sendername' => $senderName,
            'variables' => $data ['variables'],
            'receivers' => $recievers,
            'template' => $template,
            'layout' => $layout,
            'subject' => $subject
        );  
        return StaticOptions::resquePDF($resquedata, 'SendEmail');
        // StaticOptions::sendMail ( $sender, $sendername, $recievers, $template, $layout, $data ['variables'], $data ['subject'] );
    } 
    
    public function isPreOrder($orderDate,$deliveryDate){
         $orderTimeStamp = strtotime($orderDate);
         $deliveryTimeStamp = strtotime($deliveryDate);
         $preOrderTime = 90;
         $diffDateTime = ($deliveryTimeStamp - $orderTimeStamp)/60;        
        if ($diffDateTime > $preOrderTime) {
            return true;
        } else {
            return false;
        }
    }
}