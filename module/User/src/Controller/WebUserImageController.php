<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\User;
use MCommons\StaticOptions;

class WebUserImageController extends AbstractRestfulController {

    public function getList() {
        $sender = "khgoswami@hungrybuzz.info";
        $sendername = "Krunal Goswami";
        $recievers = array(
            "khgoswami@hungrybuzz.info"
        );
        $template = 'email-template/user-registration';
        $layout = 'email-layout/default';
        $variables = array(
            'username' => "Krunal Goswami",
            'hostname' => WEB_URL
        );
        $subject = 'Welcome Friend!';
        $data = array(
            'sender' => $sender,
            'sendername' => $sendername,
            'receivers' => $recievers,
            'template' => $template,
            'layout' => $layout,
            'variables' => $variables,
            'subject' => $sender
        );
        StaticOptions::resquePush($data, "SendEmail");
        return array(
            "true"
        );
    }

}
