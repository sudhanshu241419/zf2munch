<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\User;

class WebUserInstructionController extends AbstractRestfulController {

    public function getList() {
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        if ($isLoggedIn) {
            $user_id = $session->getUserId();
        } else {
            throw new \Exception('User unavailable', 400);
        }
        $addressModel = new User ();
        $addressModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $options = array(
            'columns' => array(
                'id',
                'delivery_instructions',
                'takeout_instructions'
            ),
            'where' => array(
                'id' => $user_id
            )
        );
        $response = $addressModel->find($options)->current()->getArrayCopy();
        $response['delivery_instructions']='';
        $response['takeout_instructions']='';
        return $response;
    }

    public function update($id, $data) {
        if ($this->getUserSession()->isLoggedIn()) {
            $user_id = $this->getUserSession()->user_id;
            $userModel = new User ();
            $userModel->id = $user_id;
            $instructions = array();
            $instructions ['delivery_instructions'] = isset($data ['delivery_instructions']) ? $data ['delivery_instructions'] : '';
            $instructions ['takeout_instructions'] = isset($data ['takeout_instructions']) ? $data ['takeout_instructions'] : '';
            $userModel->update($instructions);
            return $instructions;
        } else {
            throw new \Exception('No Active Login Found');
        }
    }

}
