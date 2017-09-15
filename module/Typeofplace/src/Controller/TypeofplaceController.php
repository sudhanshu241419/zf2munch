<?php

namespace Typeofplace\Controller;

use MCommons\Controller\AbstractRestfulController;
use Typeofplace\TypeofplaceFunctions;
use Search\SearchFunctions;
use Solr\SearchUrlsMobile;
use Solr\SearchHelpers;
use Typeofplace\Model\Feature;
use Cuisine\Model\Cuisine;
use Cuisines\CuisineFunctions;

class TypeofplaceController extends AbstractRestfulController {

    public function getList() {
        
        $memCached = $this->getServiceLocator()->get('memcached');
        $FeatureModel = new Feature ();
        if($memCached->getItem('typeOfPlace') && False){ 
            return $memCached->getItem('typeOfPlace');
        }else { 
            $featureData = $FeatureModel->getFeature()->toArray();
            $data = TypeofplaceFunctions::featureData($featureData);
            $memCached->setItem('typeOfPlace',$data,0);
            return $data;
        }
        
    }

}
