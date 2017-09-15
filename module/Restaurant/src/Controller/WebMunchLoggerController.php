<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use MUtility\MunchLogger;

class WebMunchLoggerController extends AbstractRestfulController {

    public function create($data) {

        if ($data) {
            $result = MunchLogger::writeLog(new \Exception('sudhanshu-exception'), 1, 'testing errors');
            print_R($result);
        } else {
            throw new \Exception('log data is not valid');
        }
    }

}
