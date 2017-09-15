<?php

namespace Home\Controller;

use MCommons\Controller\AbstractRestfulController;

class AppConfigController extends AbstractRestfulController {
    public function getList() {
        $response['dm_referral_image_url'] = DM_REFERRAL_IMAGE;
        $response['munchado_referral_image_url'] = MUNCHADO_REFERRAL_IMAGE;
        return $response;
    }
}

 