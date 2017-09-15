<?php

namespace User\Controller;

use MCommons\StaticOptions;
use MCommons\Controller\AbstractRestfulController;

class MunchadoCareerController extends AbstractRestfulController {

    public function create($data) {
        
        if (!isset($data['email']) && empty($data['email'])) {
            throw new \Exception('Email not found', 404);
        }
        
        if(isset($data['referral']) && $data['referral']==1){
           return $this->sendReferralMail($data);
        }
            
        if (!isset($data['name']) && empty($data['name'])) {
            throw new \Exception('Name not found', 404);
        }
                        
        $request = new \Zend\Http\PhpEnvironment\Request();
        $files = $request->getFiles();
        //pr($files,1);
        $fileCount = count($files);
        $response = array();
        $attachment = false;

        if (!empty($files) && $fileCount > 0) {
            $response = StaticOptions::uploadCareerFile($files, APP_PUBLIC_PATH, CAREER);
            if(isset($response['status']) && !$response['status']){
                return $response;
            }
            $attachment = true;
        }

        $userFunctions = new \User\UserFunctions();
        $jobtitle = isset($data['jobtitle']) ? $data['jobtitle'] : "";
        $location = isset($data['location']) ? $data['location'] : "";
        $mailData['description'] = isset($data['description'])?$data['description']:"";
        $mailData['name'] = $data['name'];
        $mailData['email'] = $data['email'];
        $mailData['portfoliolink'] = isset($data['portfoliolink']) ? $data['portfoliolink'] : "";
        //$mailData['coverletter'] = $data['coverletter'];
        $mailData['template'] = 'munchado_career';
        $mailData['layout'] = 'email-layout/default_career';
        $mailData['jobTitle'] = $jobtitle;
        $mailData['subject'] = $jobtitle." for ".$location;
        
        $mailData['recievers'] = BRAVVURA_HR_EMAIL;
        $mailData['sender_name'] = "Bravvura";
        $mailData['layout'] = 'email-layout/default_career_bravvura';
        if (isset($data['fromSite']) && $data['fromSite'] == 'munchado') {
            $mailData['recievers'] = MUNCHADO_HR_EMAIL;
            $mailData['sender_name'] = "Munch Ado";
            $mailData['layout'] = 'email-layout/default_career';
        }

        $mailData['sender'] = 'notifications@munchado.com';

        $config = $this->getServiceLocator()->get('Config');
        $webUrl = PROTOCOL . $config['constants']['web_url'];
        $variables = array(
            'referral' => false,
            'username' => $data['name'],
            'hostname' => $webUrl,            
            'portfoliolink' => $mailData['portfoliolink'],
            'attachment' => $attachment,
            'attachment_file' => $response,
            'jobTitle' => $jobtitle,
            'description'=>$mailData['description'],
            'fromsite'=>$data['fromSite']
        );
        $mailData['variables'] = $variables;

        if ($userFunctions->sendCareerEmail($mailData)) {
            return array('status'=>  boolval(true),'message' => 'success');
        } else {
            return array('status'=>  boolval(false), 'message' => "fail");
        }
    }

    public function get($id) {
        if ($id) {
            $options = array('columns' => array('id', 'location', 'position', 'description', 'the_ideal', 'what_you_will_do', 'what_you_will_need', 'additional_information_heading', 'additional_information', 'created_at', 'updated_at', 'status'), 'where' => array('id' => $id, 'status' => 1));
            $munchadoCareer = new \User\Model\MunchadoCareer();
            $response = $munchadoCareer->getDetails($options);    
            if(isset($response[0])){
                return $response[0];
            }else{
                return $response = array();
            }
            
        } else {
            return array("message" => "Invalid detail id");
        }
    }

    public function getList() {
        $plateform = $this->getQueryParams('platform',false);        
        $options = array('columns' => array('id', 'dept_id', 'location', 'department','position'), 'where' => array('status' => 1,'platform'=>$plateform));
        $munchadoCareer = new \User\Model\MunchadoCareer();
        $response = $munchadoCareer->getDetailLists($options);
        return $response;
    }
    
    public function sendReferralMail($data){
        $userFunctions = new \User\UserFunctions();        
        $mailData['description'] = isset($data['description'])?$data['description']:"";      
        $mailData['template'] = 'munchado_career';
        $fromUser = isset($data['fromuser'])?$data['fromuser']:'';   
        $careerlink = isset($data['careerlink'])?$data['careerlink']:'http://bravvura.com/career';
        $mailData['recievers'] = $data['email'];
        
        $mailData['sender'] = 'notifications@munchado.com';

        $config = $this->getServiceLocator()->get('Config');        
        $webUrl = "http://bravvura.com";
        $mailData['layout'] = 'email-layout/default_career_bravvura'; 
        $mailData['subject'] = "Bravvura Career"; 
        $mailData['sender_name'] = "Bravvura";
        if(isset($data['fromSite'])&& $data['fromSite'] == "munchado"){
            $webUrl = PROTOCOL . $config['constants']['web_url'];
            $mailData['layout'] = 'email-layout/default_career';
            $mailData['subject'] = "Munch Ado Career"; 
            $mailData['sender_name'] = "Munch Ado";
            $careerlink = isset($data['careerlink'])?$data['careerlink']:'http://munchado.com/career';
        }
        $variables = array(
            'referral' => true,            
            'hostname' => $webUrl,
            'creerPageLink'=>$webUrl."/career",
            'attachment' => false,
            'fromuser'=>$fromUser,
            'touser'=>isset($data['touser'])?$data['touser']:'',
            'careerlink'=>$careerlink
        );
        $mailData['variables'] = $variables;

        if ($userFunctions->sendCareerEmail($mailData)) {
            return array('status'=>  boolval(true),'message' => 'success');
        } else {
            return array('status'=>  boolval(false),'message' => "fail");
        }
    }

}
