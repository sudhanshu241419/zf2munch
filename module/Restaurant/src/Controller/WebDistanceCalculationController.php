<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use MCommons\StaticOptions;

class WebDistanceCalculationController extends AbstractRestfulController {

    public function get($id) {
        $q = urldecode($this->getQueryParams('q'));
        $resLat = $this->getQueryParams('res_lat');
        $resLong = $this->getQueryParams('res_long');
        $distance = array();
        if (isset($q) && isset($resLat) && isset($resLong)) {

            if (preg_match('/[-]/', $resLong)) {
                $resLong = explode("-", $resLong);
            } else {
                $resLong[1] = $resLong;
            }

            $latLag = explode("@", $q);
            $i = 0;

            foreach ($latLag as $key => $val) {
                $ltlg = explode(",", $val);
                if (preg_match('/[-]/', $ltlg[2])) {
                    $long = explode("-", $ltlg[2]);
                } else {
                    $long[1] = $ltlg[2];
                }

                $distanceCal = StaticOptions::latLogDistanceCalculation($ltlg[1], $long[1], $resLat, $resLong[1]);
                $distance[$i]['address_id'] = $ltlg[0];
                $distance[$i]['distance'] = $distanceCal;
                unset($ltlg);
                $i++;
            }
        }
        return $distance;
    }

}
