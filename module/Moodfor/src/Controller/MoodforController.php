<?php

namespace Moodfor\Controller;

use MCommons\Controller\AbstractRestfulController;
use Moodfor\Model\Moodfor;

class MoodforController extends AbstractRestfulController {

    public function getList() {
        $params = array();
        $moodforModel = new Moodfor ();
        $params ['q'] = $this->getQueryParams('q');
        if (!$params ['q']) {
            throw new \Exception("Invalid Keyword");
        }

        if (strlen($params ['q']) < 2) {
            throw new \Exception("Keyword lenth is less than 2");
        }
        $params ['city_id'] = $this->getQueryParams('city_id');
        if (!$params ['city_id']) {
            throw new \Exception("Invalid City Id");
        }
        $params ['search_type'] = $this->getQueryParams('search_type', '');

        $response = $moodforModel->moodForList($params);

        $i = 0;
        foreach ($response ['response'] ['docs'] as $key => $val) {
            $data = array_intersect_key($val, array_flip(array(
                'res_name1',
                'data_type'
                    )));
            $resp [$i] = $data;
            $i ++;
        }

        return $resp;
    }

}
