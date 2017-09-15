<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;

class WebCaptchaController extends AbstractRestfulController {

    public static $captcha = array(
        'chicken',
        'pizza',
        'burger',
        'sushi'
    );

    public function getList() {
        // $captchaKey = array_rand(self::$captcha);
        $captchaKey =rand(100, 999);
        //$this->getUserSession()->setUserDetail('captcha-value', self::$captcha[$captchaKey]);]
        $this->getUserSession()->setUserDetail('captcha-value', $captchaKey);
        $this->getUserSession()->save();
        //return array('captcha_item' => self::$captcha[$captchaKey]);
        return array('captcha_item' => $captchaKey);
    }

}
