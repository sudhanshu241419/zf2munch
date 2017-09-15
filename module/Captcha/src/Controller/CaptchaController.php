<?php

namespace Captcha\Controller;

use MCommons\Controller\AbstractRestfulController;
use MCommons\StaticOptions;

class CaptchaController extends AbstractRestfulController {
	public function getList() {
		$session = $this->getUserSession ();
		$captcha_array = StaticOptions::$captcha_array;
		$captcha_key = array_rand ( $captcha_array );
		$value = $captcha_array [$captcha_key];
		$output ['message'] = $value ['captcha_title'];
		
		$session->setUserDetail ( 'captcha_key', $value ['captcha_key'] );
		$session->save ();
		
		foreach ( $captcha_array as $key => $captcha ) {
			if ($captcha_key == $key) {
				$captcha_array [$key] ['is_allowed'] = true;
			} else {
				$captcha_array [$key] ['is_allowed'] = false;
			}
		}
		
		$output ['data'] = $captcha_array;
		
		return $output;
	}
}
	