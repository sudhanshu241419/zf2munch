<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use MCommons\CommonFunctions;

class WebReviewPreviewController extends AbstractRestfulController {
	const FORCE_LOGIN = true;
	public function getList() {
		$commonFunctions = new CommonFunctions();
		$response = $commonFunctions->getUserHistory();
		return $response;
	}
}