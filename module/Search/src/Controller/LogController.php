<?php

namespace Search\Controller;

use MCommons\Controller\AbstractRestfulController;

class LogController extends AbstractRestfulController {

    public function create($data) {
        $response = array('message' => 'invalid request');
        if (!isset($data['device']) || !isset($data['message']) || !isset($data['url'])) {
            return $response;
        }
        switch ($data['device']) {
            case 'mob':
                $response = $this->writeMobApiLog($data);
                break;
            default:
                break;
        }
        return $response;
        
    }

    private function writeMobApiLog($data) {
        $dataArray = array(
            'level' => 1,
            'message' => json_encode(array('device' => $data['device'], 'message' => $data['message'])),
            'origin' => $data['url'],
        );
        $response = \MUtility\MunchLogger::writeToDb($dataArray);

        return array('message' => ($response) ? 'success' : 'failed');
    }

}
