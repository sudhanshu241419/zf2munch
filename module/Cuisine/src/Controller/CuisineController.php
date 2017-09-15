<?php

namespace Cuisine\Controller;

use MCommons\Controller\AbstractRestfulController;
use Cuisine\Model\Cuisine;
use Cuisines\CuisineFunctions;

class CuisineController extends AbstractRestfulController {

    public function getList() {
        try {
        $CuisineModel = new Cuisine ();

        $allCuisine = $CuisineModel->getAllCuisine(array(
            'columns' => array(
                'id',
                'name' => 'cuisine',
                'type' => 'cuisine_type',
                'priority'
            ),
            'where' => array(
                'status' => 1,
                'search_status' => 1
            )
        ));

        return $response = CuisineFunctions::getCuisineTypePopularFoodTrends($allCuisine, $this->isMobile());
        } catch (\Exception $e) {
           \MUtility\MunchLogger::writeLog($e, 1,'Something Went Wrong On Cuisine Api');
           throw new \Exception($e->getMessage(),400);
        }
    }

}
