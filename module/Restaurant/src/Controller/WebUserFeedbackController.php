<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\UserFeedback;

class WebUserFeedbackController extends AbstractRestfulController {

    const FORCE_LOGIN = true;

    public function create($data) {
        $userId = $this->getUserSession()->getUserId();
        if (!$userId) {
            throw new \Exception('User id not found');
        }
        if (!isset($data ['review_id'])) {
            throw new \Exception('Review id is required');
        }
        if (!isset($data ['feedback'])) {
            throw new \Exception('Feedback is required');
        }
        $userFeedbackModel = new UserFeedback ();
        $userFeedbackModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $options = array(
            'columns' => array(
                'count' => new \Zend\Db\Sql\Expression('COUNT(*)')
            ),
            'where' => array(
                'review_id' => $data ['review_id'],
                'user_id' => $userId
            )
        );
        $count = $userFeedbackModel->find($options)->current()->getArrayCopy();
        if ($count ['count'] > 0) {
            return array(
                'success' => true
            );
        }
        $userFeedbackModel->review_id = $data ['review_id'];
        $userFeedbackModel->feedback = $data ['feedback'];
        $userFeedbackModel->user_id = $userId;
        $userFeedbackModel->addFeedback();
        return array(
            'success' => true
        );
    }

}
