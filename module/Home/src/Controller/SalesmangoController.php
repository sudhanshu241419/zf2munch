<?php

namespace Home\Controller;

use MCommons\Controller\AbstractRestfulController;
use MUtility\MunchLogger;
class SalesmangoController extends AbstractRestfulController {

    public function getList() {
        $response = array();
        $user = new \User\Model\User();
        $page = $this->getQueryParams('page',false);       
        
        $options = array(
            'columns' => array(
                'email'                
            ),
            
            'where' => array(
                '1' => 1,                
            ),
            //'limit'=>600
            
         );
        $user->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $userEmail = $user->find($options)->toArray();
        $totalRecord = count($userEmail);
        if($totalRecord > 300 && $totalRecord <= 600){
            $division = 2;
        }elseif($totalRecord > 600 && $totalRecord <= 1200){
            $division = 4;
        }elseif($totalRecord > 1200 && $totalRecord <= 2000){
            $division = 8;
        }elseif($totalRecord > 2000){
            $division = 15;
        }        
        $limit = ceil($totalRecord/$division);
        
        $offset = 0;
        if ($page > 0) {
            $page = ($page < 1) ? 1 : $page;
            $offset = ($page - 1) * ($limit);
        }
        //echo $offset;
        //echo $limit;
        $final = array_slice($userEmail, $offset, $limit);
        ///pr($final,1);
        $salesmango = new \Salesmanago();
        
        $response  = $salesmango->updateHostUrlTest($final);
        $response['total_records'] = $totalRecord;
        $response['offset'] = $offset;
        $response['limit']=$limit;
         
        return $response;
    }

}
