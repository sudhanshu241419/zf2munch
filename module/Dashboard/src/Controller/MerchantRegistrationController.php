<?php

namespace Dashboard\Controller;

use MCommons\Controller\AbstractRestfulController;
use Dashboard\DashboardFunctions;
use mpdf;

class MerchantRegistrationController extends AbstractRestfulController {
    public $cardDetails = [];
    public function create($data){ 
        $merchant = new \Dashboard\Model\MerchantRegistration();        
        $dashboardFunctions = new DashboardFunctions();
        $state = new \Dashboard\Model\State(); 
        
     
        if(!isset($data['name_restaurant']) && empty($data['name_restaurant'])){
            throw new \Exception('Restaurant name is required',400);
        }
        
        if(!isset($data['email']) && empty($data['email'])){
            throw new \Exception('Restaurant email is required',400);
        }
        if(!isset($data['state']) && empty($data['state'])){
            throw new \Exception('State is required',400);
        }
        $options = array('columns'=>array('state_code'),'where'=>array('id'=>$data['state']));
        $stateDetails = $state->find($options)->toArray();
        
        $dashboardFunctions->location['state_code'] = $stateDetails[0]['state_code'];
        $currentDate = $dashboardFunctions->CityTimeZone();
        
        $merchant->restaurant_name = $data['name_restaurant'];
        $restname = preg_replace('/\s+/', '', $data['name_restaurant']);
        $restname = preg_replace('/[^A-Za-z0-9\.]/', '', $restname);
       
        $merchant->city_id = '';       
        $merchant->email = $data['email'];
        $merchant->mobile = isset($data['mobile'])?$data['mobile']:'';       
        $merchant->phone = isset($data['phone'])?$data['phone']:'';
        $merchant->role = isset($data['role'])?$data['role']:'';
        $merchant->status = 0;
        $merchant->title_name = isset($data['titleselect'])?$data['titleselect']:'';
        $merchant->created_on = $currentDate;
        $merchant->updated_at = $currentDate;
        $merchant->username = strtolower($restname) . rand(1000, 9999);      
        $merchant->filled_by = isset($data['user'])?$data['user']:'';
        
        $merchant->street_address = isset($data['street_address']) ? addslashes($data['street_address']) : '';
        $merchant->street_address2 = isset($data['street_address1']) ? addslashes( $data['street_address1']) : '';
        $merchant->city = isset($data['city']) ? $data['city'] : '';
        $merchant->state = isset($data['state']) ? $data['state'] : '';
        $merchant->zipcode = isset($data['zipcode']) ? $data['zipcode'] : '';
        $merchant->web_url = isset($data['web']) ? $data['web'] : '';
        $merchant->phone1 = isset($data['phone1']) ? $data['phone1'] : '';
        $merchant->phone2 = isset($data['phone2']) ? $data['phone2'] : '';
        $merchant->cell_phone = isset($data['cellphone']) ? $data['cellphone'] : '';
        $merchant->fax = isset($data['fax']) ? $data['fax'] : '';
        $merchant->owner_name = isset($data['ownername']) ? $data['ownername'] : '';
        if ($data['titleselect'] == 'others') {
            $merchant->title_name = isset($data['txtothers'])?$data['txtothers']:'';
        } else {
            $merchant->title_name = $data['titleselect'];
        }
         
        $merchant->sales_region = isset($data['region']) ? $data['region'] : ''; // 1=> Manhattan, 2=>Brooklyn, 3=>Queens, 4=>Bronx;
        $merchant->contact1 = isset($data['ownercontact']) ? $data['ownercontact'] : '';
        $merchant->contact2 = isset($data['ownercontact1']) ? $data['ownercontact1'] : '';
        $merchant->contact3 = isset($data['ownercontact2']) ? $data['ownercontact2'] : '';
        $merchant->account_manager = isset($data['accountmanager']) ? $data['accountmanager'] : '';
        $merchant->rest_instructions = isset($data['instructions']) ? $data['instructions'] : '';
        $merchant->associatename = isset($data['associatename']) ? $data['associatename'] : '';
        $merchant->associateemail = isset($data['associateemail']) ? $data['associateemail'] : '';
        $merchant->onlinegrowth = isset($data['programvalue']) ? $data['programvalue'] : '';
        $merchant->chkdelivery = isset($data['chkdelivery']) ? $data['chkdelivery'] : '';
        $merchant->chktakeout = isset($data['chktakeout']) ? $data['chktakeout'] : '';
        $merchant->chkreservations = isset($data['chkreservations']) ? $data['chkreservations'] : '';
        $merchant->chkprepaidres = isset($data['chkprepaidres']) ? $data['chkprepaidres'] : '';
        $merchant->fees_waived_off = isset($data['feewaived']) ? $data['feewaived'] : '';
        $merchant->delivery_by_ma = isset($data['chkmunchdevlivery']) ? $data['chkmunchdevlivery'] : '';
        $merchant->pmodeemail = isset($data['pmodeEmail']) ? $data['pmodeEmail'] : '';
        $merchant->pmodephone = isset($data['pmodePhone']) ? $data['pmodePhone'] : '';
        $merchant->pmodefax = isset($data['pmodeFax']) ? $data['pmodeFax'] : '';
        if ($data['programvalue'] == 'freelistingonMA') {
            $data['filedata'] = '';
            $merchant->chkdelivery = '';
           $merchant->chktakeout = '';
            $merchant->chkreservations = '';
        }
       
        $merchant->menu2 = isset($data['urlmenu']) ? html_entity_decode($data['urlmenu']) : '';
        $merchant->accept_cc_card_phone = 0;    
        $merchant->hoo_week = isset($data['timepicHOWeek']) ? $data['timepicHOWeek'] : '';
        $merchant->hoo_sat = isset($data['timepicHOSat']) ? $data['timepicHOSat'] : '';
        $merchant->hoo_sun = isset($data['timepicHOSun']) ? $data['timepicHOSun'] : '';
        $merchant->dh_week = isset($data['timepicDHWeek']) ? $data['timepicDHWeek'] : '';
        $merchant->dh_sat = isset($data['timepicDHSat']) ? $data['timepicDHSat'] : '';
        $merchant->dh_sun = isset($data['timepicDHSun']) ? $data['timepicDHSun'] : '';
        $merchant->delivery_area = isset($data['deliveryArea']) ? $data['deliveryArea'] : '';
        $merchant->min_delivery_amt = isset($data['minDeliveryAmt']) ? $data['minDeliveryAmt'] : '';
        $merchant->delivery_fee_type = isset($data['deliveryfee']) ? $data['deliveryfee'] : '';
        $merchant->ecom_price = isset($data['mrpprice']) ? $data['mrpprice'] : '';
        
        if (isset($data['fixdelivery']) && $data['fixdelivery']!='') {
            $merchant->delivery_fee_mode = 'Flat';
            $merchant->delivery_fee = $data['fixdelivery'];
        } else if (isset($data['percdelivery']) && $data['percdelivery']!='') {
            $merchant->delivery_fee_mode = 'Percentage';
            $merchant->delivery_fee = $data['percdelivery'];
        }
        $merchant->delivey_instrucation = isset($data['delivfeeinstr']) ? $data['delivfeeinstr'] : '';
        if (isset($data['radioPayMethod'])) {
            if ($data['radioPayMethod'] == "ACH") {
                $merchant->payment_method = 'A';
                $merchant->bank_account_no = isset($data['BankAcNo']) ? $data['BankAcNo'] : '';
                $merchant->rounting_no_ach = isset($data['RoutingNo']) ? $data['RoutingNo'] : '0';
                $merchant->payee_name_check = isset($data['payeeNameACH']) ? $data['payeeNameACH'] : '';
            } else {
                $merchant->payment_method = 'C';
                $merchant->payee_name_check = isset($data['payeeName']) ? $data['payeeName'] : '';
            }
        } 
        
        $merchant->loyaltyduration = isset($data['duration_lpchk']) ? $data['duration_lpchk'] : '';
        $merchant->loyaltypay = isset($data['radioPayMethod_lp']) ? $data['radioPayMethod_lp'] : '';
        if($merchant->loyaltypay=='ACH_LP'){
            $merchant->loyaltypay ='ACH';
        }
        if($merchant->loyaltypay=='Cheque_LP'){
            $merchant->loyaltypay ='Check';
        }
        $merchant->ownercell = isset($data['ownercell']) ? $data['ownercell'] : '';
        $merchant->owneremail = isset($data['owneremail']) ? $data['owneremail'] : '';
        $merchant->payment_mode =isset($data['radioPayMethod_lp']) ? $data['radioPayMethod_lp'] : '';    
         if (($data['programvalue'] <> "freelistingonMA") ) {
          $amount = isset($data['pricevalue'])? $data['pricevalue'] : 0; //900; 
                $amountAfterDis = isset($data['totalpayment'])? $data['totalpayment'] : 0; //900; 
                $amount=($amountAfterDis>0)?$amountAfterDis:$amount;
                $discount=isset($data['discountvalue'])? $data['discountvalue'] : 0; //900; 
                $merchant->amount = $amount; 
                $merchant->discount=$discount;
            if ($data['radioPayMethod_lp'] === "Card") {
                $merchant->name_oncard = isset($data['cardname_Loyalty']) ? $data['cardname_Loyalty'] : '';
                $merchant->cardtype = isset($data['cval_Loyalty']) ? $data['cval_Loyalty'] : '';
                $card_details = isset($data ['cardno_Loyalty']) ? $dashboardFunctions->aesEncrypt($data ['cardno_Loyalty'] . "-" . $data ['cvv_Loyalty']) : '';
                $merchant->cardno = $card_details;
                $merchant->exp_month = isset($data['month_Loyalty']) ? $data['month_Loyalty'] : '';
                $merchant->exp_year = isset($data['year_Loyalty']) ? $data['year_Loyalty'] : '';
                $merchant->accept_cc_card_phone = 1;
                $merchant->payment_mode ="CC";
                $this->cardDetails = array(
                    'number' => $data ['cardno_Loyalty'],
                    'exp_month' =>$merchant->exp_month,
                    'exp_year' => $merchant->exp_year,
                    'name' => $merchant->name_oncard,
                    'cvc' => $data ['cvv_Loyalty'],
                    'address_zip' => $merchant->zipcode,
                );
              
            $paymentResponse = $this->chargeCustomer($amount); 
            $merchant->stripe_token = $paymentResponse['id'];
            $merchant->stripe_card_id = $paymentResponse['card']['id']; 
           }
        }
        try {
            
            //vd($merchant->create(), 1); 
           $merchant->menu = $this->uploadMenuFiles($data); 
            //pr($merchant,1);
           $mailData = $this->send_b2b_register_confirmation_mail($merchant); 
          if($mailData['template_name_pdf']==B2B_SINGUP_CRM_FREELISTING_PDF){
             $merchant->agreement_copy=""; 
          }else{
             $varPdfData= $dashboardFunctions->sendB2BPDF($mailData);
           //pr($varPdfData,1);
           $varmPDF=new mpdf('c', 'A4');
           $varmPDF->WriteHTML($varPdfData);
           
           $pdfLocation=getcwd().'/public/'.CAREER.'agreements';
           $pdfName=$restname.'_'. strtotime($merchant->created_on).'_sow.pdf';
           if(!is_dir($pdfLocation)){
             mkdir($pdfLocation, 0777, true);  
           }
           $varmPDF->Output($pdfLocation.'/'.$pdfName, 'F');
           $merchant->agreement_copy=$pdfName; 
          }
           
           // die('test5');
          //pr($merchant,1);
           $merchant->create();
            if ($merchant->id) {
                $dashboardFunctions->sendB2BMails($mailData);
               return array('success');
            }
           
        } catch (Exception $e) {
            Logging::set_log("MerchantRegistration: EXCEPTION " . $e->getMessage());
            $this->respond(array("errorText" => $e->getMessage()));
        }
    }
    
    public function chargeCustomer($amount=0) {
        try {
            $stripe = new \MStripe();          
            $response = $stripe->chargeCard($this->cardDetails, $amount);
            return $response;
        } catch (Exception $e) {
            \MUtility\MunchLogger::writeLog(new \Exception('Merchant Payment Response'),4,"MerchantRegistration: Payment Exception " . $e->getMessage());
            $this->respond(array("errorText" => $e->getMessage()));
        }
    }
    
    public function getList() {
      $searchtext =$this->getQueryParams('q',false); 
      $sl = $this->getServiceLocator();
      $config = $sl->get('Config');
      $solrUrl = $config['constants']['solr']['protocol'].$config['constants']['solr']['host'].":".$config['constants']['solr']['port']."/".$config['constants']['solr']['context'];
      //pr($solrUrl.'hbr/select?wt=json&city_id=18848&rows=5&df=res_eng&fl=*&q='.urlencode($searchtext),1);
      $data = file_get_contents($solrUrl.'/hbr/select?wt=json&city_id=18848&rows=5&df=res_eng&fl=*&q='.urlencode($searchtext));
      $restaurants = json_decode($data);
      $restaurant_name= '';
      $unique_name = array();
      $unique_names = array();
        if($restaurants->response->numFound >0){
            foreach ($restaurants->response->docs as $value) {
                
                $restaurant_id= $value->res_id;    
                $restaurant_name= $value->res_name;
                $restaurant_neighbourhood= $value->res_neighborhood;
                $unique_name= WEB_URL.'restaurants/'.$restaurant_name.'/'.$restaurant_id;
                $value->restaurant_link = $unique_name;
                $unique_names[]= (array)$value;
            } 
            //print_r($unique_names);
          return $unique_names;
      }
     
      return array("message"=>"fail");

    }
    
    public function update($id, $data) {
        pr($data['filename'],1);
        $dbUploadDir = APP_PUBLIC_PATH.'assets/db_images';
        if (!is_dir($dbUploadDir)) {
            if (@!mkdir($dbUploadDir, 0777, true)) {
                die('Failed to create folder...' . $dbUploadDir);
            }
            chmod($dbUploadDir, 0777);
        }
        $output_dir = $dbUploadDir . '/';
        if (isset($_FILES["myfile"])) {
            $ret = array();
            $error = $_FILES["myfile"]["error"];
            {
                if (!is_array($_FILES["myfile"]['name'])) { //single file
                    $fileName = rand() . '_' . $_FILES["myfile"]["name"];
                    move_uploaded_file($_FILES["myfile"]["tmp_name"], $output_dir . $fileName);
                    //echo "<br> Error: ".$_FILES["myfile"]["error"];
                    $ret[$fileName] = $output_dir . $fileName;
                } else {
                    $fileCount = count($_FILES["myfile"]['name']);
                    for ($i = 0; $i < $fileCount; $i++) {
                        $fileName = rand() . '_' . $_FILES["myfile"]["name"][$i];
                        $ret[$fileName] = $output_dir . $fileName;
                        move_uploaded_file($_FILES["myfile"]["tmp_name"][$i], $output_dir . $fileName);
                    }
                }
            }             
            $this->respond($ret);
        }
    }

    public function delete() {
        $output_dir = APP_PUBLIC_PATH.'assets/db_images';
        if (isset($_POST["op"]) && $_POST["op"] == "delete" && isset($_POST['name'])) {
            $fileName = $_POST['name'];
            $fileName = str_replace("..", ".", $fileName); //required. if somebody is trying parent folder files 
            $filePath = $output_dir . $fileName;
            if (file_exists($filePath)) {
                ///unlink($filePath);
            }
            echo "Deleted File " . $fileName . "<br>";
        }
        $this->respond($fileName);
    }
    
    public function send_b2b_register_confirmation_mail($data) {
        if (empty($data->email))
            return;
        if (!empty($data->street_address2)) {
            $address = $data->street_address . ", " . $data->street_address2 ;
        } else {
            $address = $data->street_address;
        }
        $city_state ="";
        if(!empty($data->city)){
            $city_state =$data->city . ", NY, " . $data->zipcode;
        }
        $ach_method = "mabiz-uncheck-img.gif";
        $bank_account_no ="";
        $rounting_no_ach ="";
        $payee_name_ach="";
      if ($data->payment_method == 'A') {
            $ach_method = 'mabiz-check-img.gif';
            $bank_account_no = $data->bank_account_no;
            $rounting_no_ach = $data->rounting_no_ach;
            $payee_name_ach = $data->payee_name_check;
        } 
        
        $check_method = 'mabiz-uncheck-img.gif';
        $payee_name_check ="";
        if ($data->payment_method == 'C') {
            $check_method = 'mabiz-check-img.gif';
            $payee_name_check = $data->payee_name_check;
        }        
        $chkdelivery = "mabiz-uncheck-img.gif";
        if (!empty($data->chkdelivery)){
            $chkdelivery = 'mabiz-check-img.gif';
        }
        $chktakeout = "mabiz-uncheck-img.gif";
        if (!empty($data->chktakeout)){
            $chktakeout = 'mabiz-check-img.gif';
        }
        $chkreservations = "mabiz-uncheck-img.gif";
        if (!empty($data->chkreservations)){
            $chkreservations = 'mabiz-check-img.gif';
        }
        
        $chkprepaidres = "mabiz-uncheck-img.gif";
        if (!empty($data->chkprepaidres)){
            $chkprepaidres = 'mabiz-check-img.gif';
        }
        $delivery_by_ma = "mabiz-uncheck-img.gif";
        if (!empty($data->delivery_by_ma == 'YES')){
            $delivery_by_ma = 'mabiz-check-img.gif';
        }
                
        $pmodeemail = "mabiz-uncheck-img.gif";
        if (!empty($data->pmodeemail)){
            $pmodeemail = "mabiz-check-img.gif";
        }
        
        $pmodefax = "mabiz-uncheck-img.gif";
        if (!empty($data->pmodefax)){
            $pmodefax = "mabiz-check-img.gif";
        }
        
        $pmodephone = "mabiz-uncheck-img.gif";
        if (!empty($data->pmodephone)){
            $pmodephone = "mabiz-check-img.gif";
        }
        $associateemail="";
        if(!empty($data->associateemail)){
            $associateemail = $data->associateemail;
        }
        $owneremail ="";
        if(!empty($data->owneremail)){
            $owneremail = $data->owneremail;
        }
        if ((APPLICATION_ENV == 'demo') || (APPLICATION_ENV == 'qa') || (APPLICATION_ENV == 'qc') || (APPLICATION_ENV == 'local') || (APPLICATION_ENV == 'db-staging')|| (APPLICATION_ENV == 'staging')) {
           $sales_region="";
                if(!empty($data->sales_region)){
                    $sales_region = "ssingh@aydigital.com";
                }             
             $mail_sent = 'psharma@bravvura.in'; $cc = 'ssingh@aydigital.com'; $cc1 = $sales_region; $cc2 = $associateemail; $cc3 = $owneremail;
        } else {
           $sales_region="";
                if($data->sales_region == 'Manhattan'){    $sales_region = "Manhattan@munchado.biz";
                }elseif($data->sales_region == 'Brooklyn'){ $sales_region = "Brooklyn@munchado.biz";
                }elseif($data->sales_region == 'Queens'){   $sales_region = "Queens@munchado.biz";
                }elseif($data->sales_region == 'Bronx'){   $sales_region = "Bronx@munchado.biz";
                } 
           $mail_sent = 'newcontracts@munchado.biz'; $cc = 'CRM@munchado.in'; $cc1 = $sales_region; $cc2 = $associateemail; $cc3 = $owneremail;
        }
        
        $delivery_pacage = "mabiz-crosscheck-img.gif";

        if (!empty($data->package)&&($data->package == 'A')) {
            $delivery_pacage = "mabiz-check-img.gif";
        }
        $del_resv_pacage = "mabiz-crosscheck-img.gif";
        if (!empty($data->package)&&($data->package == 'PP')) {
            $del_resv_pacage = "mabiz-check-img.gif";
        }  
        //pr($data->onlinegrowth,1);
        $serviceType = "mabiz-crosscheck-img.gif";
        if($data->onlinegrowth == 'munchadoprogram'){
            $serviceType = "mabiz-check-img.gif";
            $subject = SUBJECT_B2B_SINGUP_CRM.'- The Munch Ado Restaurant Program';
            $template_name = B2B_SINGUP_CRM_LOYALTY;
            $template_name_pdf = B2B_SINGUP_CRM_LOYALTY_PDF;
        }else if($data->onlinegrowth == 'marketingprogram'){
            $serviceType = "mabiz-check-img.gif";
            $subject = SUBJECT_B2B_SINGUP_CRM.'- The Munch Ado Marketing Program';
            $template_name = B2B_SINGUP_CRM_MARKETING;
            $template_name_pdf = B2B_SINGUP_CRM_MARKETING_PDF;
        }else if ($data->onlinegrowth == 'ecommprogram') {
            $serviceType = "mabiz-check-img.gif";
            $subject = SUBJECT_B2B_SINGUP_CRM.'- The Munch Ado eCommerce Program';
            if($data->ecom_price =='99'){
                $template_name = B2B_SINGUP_CRM_ECOMMERCE_99;
                $template_name_pdf = B2B_SINGUP_CRM_ECOMMERCE_99_PDF;
            }else{
                $template_name = B2B_SINGUP_CRM_ECOMMERCE_199; 
                $template_name_pdf = B2B_SINGUP_CRM_ECOMMERCE_199_PDF;
            }
        }else if($data->onlinegrowth == 'socialprogram'){
            $serviceType = "mabiz-check-img.gif";
            $subject = SUBJECT_B2B_SINGUP_CRM.'- The Munch Ado Social Media Program';
            $template_name = B2B_SINGUP_CRM_SOCIAL_MEDIA;
            $template_name_pdf = B2B_SINGUP_CRM_SOCIAL_MEDIA_PDF;
        }else if($data->onlinegrowth == 'ecommprogrammarketingprogram'){
            $serviceType = "mabiz-check-img.gif";
            $subject = SUBJECT_B2B_SINGUP_CRM.'- The Munch Ado eCommerce And Marketing Program';
            if($data->ecom_price=='99'){
                $template_name = B2B_SINGUP_CRM_ECOMMERCE_99_MARKETING;  
                $template_name_pdf = B2B_SINGUP_CRM_ECOMMERCE_99_MARKETING_PDF;
            }else{
                $template_name = B2B_SINGUP_CRM_ECOMMERCE_199_MARKETING;   
                $template_name_pdf = B2B_SINGUP_CRM_ECOMMERCE_199_MARKETING_PDF;
            }
        }else if($data->onlinegrowth == 'ecommprogramsocialprogram'){
            $serviceType = "mabiz-check-img.gif";
            $subject = SUBJECT_B2B_SINGUP_CRM.'- The Munch Ado eCommerce And Social Media Program';
            if($data->ecom_price =='99'){
                $template_name = B2B_SINGUP_CRM_ECOMMERCE_99_SOCIAL; 
                $template_name_pdf = B2B_SINGUP_CRM_ECOMMERCE_99_SOCIAL_PDF;
            }else{
                $template_name = B2B_SINGUP_CRM_ECOMMERCE_199_SOCIAL; 
                $template_name_pdf = B2B_SINGUP_CRM_ECOMMERCE_199_SOCIAL_PDF;
            }
        }else if($data->onlinegrowth == 'marketingprogramsocialprogram'){
            $serviceType = "mabiz-check-img.gif";
            $subject = SUBJECT_B2B_SINGUP_CRM.'- The Munch Ado Marketing And Social Media Program';
            $template_name = B2B_SINGUP_CRM_MARKETING_SOCIAL_MEDIA;
            $template_name_pdf = B2B_SINGUP_CRM_MARKETING_SOCIAL_MEDIA_PDF;
        }else if($data->onlinegrowth == 'freelistingonMA'){
            $serviceType = "mabiz-check-img.gif";
            $subject = SUBJECT_B2B_SINGUP_CRM.'- Free Profile on Munch Ado';
            $template_name = B2B_SINGUP_CRM_FREELISTING;
            $template_name_pdf = B2B_SINGUP_CRM_FREELISTING_PDF;
        }
        
        $deliveryfee="";
        if($data->delivery_fee_mode == 'Percentage'){
            $deliveryfee = $data->delivery_fee."%";
        } else if ($data->delivery_fee_mode == 'Flat') {
            $deliveryfee = "$".$data->delivery_fee;
        }
      
        $min_delivery_amt="";
        if (!empty($data->min_delivery_amt)||($data->min_delivery_amt > 0)) {
            $min_delivery_amt = "$".$data->min_delivery_amt;
        }
        
        $paycheck="mabiz-crosscheck-img.gif";
        $paycard="mabiz-crosscheck-img.gif";
        if($data->payment_mode =='paycheck'){
            $paycheck = "mabiz-check-img.gif";
        }else if($data->payment_mode == 'paycard'){
            $paycard = "mabiz-check-img.gif";
        }
        
        $sixmonth="mabiz-crosscheck-img.gif";
        $twmonth="mabiz-crosscheck-img.gif";
        if ($data->loyaltyduration == '6') {
            $sixmonth = "mabiz-check-img.gif";
        }else if($data->loyaltyduration == '12'){
            $twmonth = "mabiz-check-img.gif";
        }
        
        $lpchemode="mabiz-crosscheck-img.gif";
        $lpachmode="mabiz-crosscheck-img.gif";
        $lpcardmode = "mabiz-crosscheck-img.gif";
        if ($data->loyaltypay == 'Cheque') {
            $lpchemode = "mabiz-check-img.gif";
        }else if($data->loyaltypay == 'ACH'){
            $lpachmode = "mabiz-check-img.gif";
        }else if($data->loyaltypay == 'Card'){
            $lpcardmode = "mabiz-check-img.gif";
        }
        $paymentMode = $data->loyaltypay ;
        $attachs='';
        if($data->menu!=''||$data->menu > 0){
           $attach=explode(',',$data->menu); 
           if(count($attach)>0){
               foreach($attach as $key=>$v){
                   
                   $attachs.='<a href="'.B2B_IMAGE_PATH.$v.'" style="font-size:11px;font-family:Arial;line-height:1.5;color:#000000">'.B2B_IMAGE_PATH.$v.'</a><br/>';
                   //$attachs[]=;
               }
               $attachs.='<br/>';
           }
        } else if($data->menu2!=''){
            
            $attachs = $data->menu2;
        }
        $attach_images =$attachs;// implode("\n\t", $attachs);
        $created_on = date('Y-m-d');
        
        $variables = array(
            'site_url' => DASHBOARD_URL,
            'template_img_path' => MAIL_IMAGE_PATH,
            'restaurant_name' => $data->restaurant_name,
            'username' => $data->username,
            'cellphone' => $data->cell_phone,
            'address' => $address,
            'city_state' =>$city_state,
            'website' => $data->web_url,
            'email' => $data->email,
            'phone' => $data->phone,
            'phone1' => $data->phone1,
            'phone2' => $data->phone2,
            'associatename' => $data->associatename,
            'associateemail' => $data->associateemail,
            'ach_method' => $ach_method,
            'bank_account_no' => $bank_account_no,
            'rounting_no_ach' => $rounting_no_ach,
            'fax' => $data->fax,
            'owner_title' => $data->title_name,
            'associateregion' => $data->sales_region,
            'owner_name' => $data->owner_name,
            'owneremail' => $owneremail,
            'ownercell' => $data->ownercell,
            'contact1' => $data->contact1,
            'contact2' => $data->contact2,
            'check_method' => $check_method,
            'payee_name_check' => $payee_name_check,
            'payee_name_ach'  => $payee_name_ach,
            'chkdelivery' => $chkdelivery,
            'chktakeout' => $chktakeout,
            'chkreservations' => $chkreservations,
            'chkprepaidres' => $chkprepaidres,
            'delivery_by_ma' => $delivery_by_ma,
            'pmodeemail' => $pmodeemail,
            'pmodefax' => $pmodefax,
            'pmodephone' => $pmodephone,
            'hoo_week' => $data->hoo_week,
            'hoo_sat' => $data->hoo_sat,
            'hoo_sun' => $data->hoo_sun,
            'dh_week' => $data->dh_week,
            'dh_sat' => $data->dh_sat,
            'dh_sun' => $data->dh_sun,
            'delivery_area' => $data->delivery_area,
            'min_delivery_amt' => $min_delivery_amt,
            'delivery_pacage' =>$delivery_pacage,
            'del_resv_pacage' => $del_resv_pacage, 
            'delivery_fee' => $deliveryfee,
            'delivey_instrucation' => $data->delivey_instrucation,
            'rest_instructions' => $data->rest_instructions,
            'servicetype' => $serviceType,
            'sixmonth'=>$sixmonth,
            'twmonth'=>$twmonth,
            'checkmode'=>$lpchemode,
            'achmode'=>$lpachmode,
            'cardmode' => $lpcardmode,
            'paycheck' => $paycheck,
            'paycard' => $paycard,
            'subject' => $subject,
            'created_on'=>$created_on,
            'attachment_path'=>$attach_images,
            'paymentMode'=>$paymentMode
        );
        $maildata = array(
                'to' => $mail_sent,
                'cc'=>$cc,
                'cc1'=>$cc1,
                'cc2'=>$cc2,
                'cc3'=> $cc3,
                'from' => DASHBOARD_EMAIL_FROM,
                'template_name' => $template_name,
                 'template_name_pdf'=>$template_name_pdf,
                'subject' => $subject,
                'variables' => $variables
            );
        //pr($maildata,1);
        return $maildata;
    }
    public function uploadMenuFiles($data) {
        $dashboardFucntions = new DashboardFunctions();
        $files = isset($data ['fileupload']) ? $data ['fileupload'] : false;
        $imageName = "";
        $counter=0;
        if($files){
        foreach ($files as $value) { $counter++;
            if (isset($value) && $value && !empty($value)) {
                $menuImage = $dashboardFucntions->uploadBase64Image($value, APP_PUBLIC_PATH, B2B_IMAGE_UPLOAD . 'db_images' . DS);
                if (empty($menuImage)) {
                    throw new \Exception('image is not valid');
                }
                if(count($files)==$counter){
                $imageName.= array_pop(explode('/', $menuImage));
                }else{
                $imageName.= array_pop(explode('/', $menuImage)).',';    
                }
            }
        }
        }
        return $imageName;
    }

}