<?php

namespace Typeofplace\Controller;

use MCommons\Controller\AbstractRestfulController;
use Typeofplace\Model\Feature;
use Typeofplace\TypeofplaceFunctions;
use MCommons\Caching;

class WebTypeofplaceController extends AbstractRestfulController {

	public function getList() {
		//$memCached = new Caching();
                $memCached = $this->getServiceLocator()->get('memcached');
		$FeatureModel = new Feature ();
		if($memCached->getItem('webTypeOfPlace')){
			return $memCached->getItem('webTypeOfPlace');
		}else {
			$featureData = $FeatureModel->getFeature ()->toArray ();
			
			$data = TypeofplaceFunctions::webFeatureData ( $featureData );
			$memCached->setItem('webTypeOfPlace',$data,0);
			return $data;
		}
		
	}
}

