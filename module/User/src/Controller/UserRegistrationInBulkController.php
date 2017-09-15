<?php
namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\UserFunctions;

class UserRegistrationInBulkController extends AbstractRestfulController {

    public function create($data) {
        $userFunctions = new UserFunctions ();
        return $userFunctions->registerUserInBulk($data);
        }
}
?>