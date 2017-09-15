<?php
namespace User\Controller;
use MCommons\StaticOptions;
use MCommons\Controller\AbstractRestfulController;
use User\UserFunctions;
use User\Model\AppReleasedCampaign;
class AppSendSmsMailController extends AbstractRestfulController {
    public function create($data) {
        try {
        $appReleasedModel = new AppReleasedCampaign ();
        $user_function = new UserFunctions ();
        $sl = $this->getServiceLocator();
        $config = $sl->get('Config');
        $webUrl = PROTOCOL . $config['constants']['web_url'];
        $phone = (isset($data['phone']))?$data['phone']:'';
        $email = (isset($data['email']))?$data['email']:'';
        $insert_data = array('phone'=>$phone, 'email'=>$email);
        $appReleasedModel->insert($insert_data);
        //Send SMS
        if (!empty($data['phone'])){
             $userSmsData =array();
             $specChar = $config ['constants']['special_character'];
             $userSmsData['user_mob_no'] = $data ['phone'];  
             $userSmsData['message'] = "Get food everywhere in NYC and enter a new phase of your food life with the Munch Ado app https://itunes.apple.com/us/app/munch-ado/id1024973395?mt=8";
             StaticOptions::sendSmsClickaTell($userSmsData);   
         }
        //Send Mail
        if (!empty($data['email'])){
            $emailData = array(
                'receiver' => array($data ['email']),
                'variables' => array('hostname' => $webUrl),
                'subject' => 'The Munch Ado App is Here!',
                'template' => 'app_released',
                'layout' => 'email-layout/default_app'
            );
            $user_function->sendMails($emailData);
        }
        return array ('success' => 'true');
            
        } catch (\Exception $e) {
           \MUtility\MunchLogger::writeLog($e, 1,'Something Went Wrong On AppSendSmsMail Api');
           throw new \Exception($e->getMessage(),400);
        }
    }
}