<?php

namespace Bookmark\Controller;

use MCommons\Controller\AbstractRestfulController;
use Bookmark\Model\FoodBookmark;

class ListOfPersonLoveItemController extends AbstractRestfulController {
	public function get($menuId =0) {
		
		$bookmarkModel = new FoodBookmark ();
		$bookmarkModel->type = 'lo';
		if($menuId){
			$personListOfLoveitItem = $bookmarkModel->getPersonListOfLoveitItem($menuId);
		}else{
			throw new \Exception ( "Menu id is required", 405 );
		}
		
		if (! $personListOfLoveitItem) {
			throw new \Exception ( "There is no any person love it item", 405 );
		}
		//print_r($personListOfLoveitItem);
		return $personListOfLoveitItem;
	}
}
