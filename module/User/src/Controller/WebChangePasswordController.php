<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Form\ChangePasswordForm;
use User\FormFilter\ChangePasswordFormFilter;
use User\Model\User;

class WebChangePasswordController extends AbstractRestfulController {

    const FORCE_LOGIN = true;

    public function update($id, $data) {
        $form = new ChangePasswordForm ();
        $filter = new ChangePasswordFormFilter ();
        $data = array(
            'current_password' => isset($data ['current_password']) ? md5($data ['current_password']) : '',
            'new_password' => isset($data ['new_password']) ? $data ['new_password'] : '',
            'new_password_confirm' => isset($data ['new_password_confirm']) ? $data ['new_password_confirm'] : ''
        );
        $form->setData($data);
        $form->setInputFilter($filter->getInputFilter());
        if ($form->isValid()) {
            $userModel = new User ();
            $data = array(
                'password' => md5($data ['new_password'])
            );
            $session = $this->getUserSession();
            $userModel->id = $session->getUserId();
            $userModel->update($data);
            return array('success' => 'true');
        } else {
            $error = array_pop(array_values(array_pop($form->getMessages())));
            throw new \Exception($error);
            //return array('error' => $error);
        }
    }

}
