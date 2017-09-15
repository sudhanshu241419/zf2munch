<?php

namespace User;

use Zend\Mvc\MvcEvent;
use MCommons\StaticOptions;

class Module extends \MCommons\Module {

    protected $_namespace = __NAMESPACE__;
    protected $_dir = __DIR__;

    public function onBootstrap($e) {
        /**
         * Get the module manager for the current Application
         *
         * @var \Zend\ModuleManager\ModuleManager $moduleManager
         */
        $moduleManager = $e->getApplication()->getServiceManager()->get('modulemanager');
        $sharedEvents = $moduleManager->getEventManager()->getSharedManager();

        /**
         * Auth Process after managing the error handler
         */
        $sharedEvents->attach('Zend\Mvc\Controller\AbstractRestfulController', MvcEvent::EVENT_DISPATCH, array(
            $this,
            'checkSession'
                ), 995);
    }

    public function checkSession($e) {
        $target = $e->getTarget();
        $class = new \ReflectionClass(get_class($e->getTarget()));
        if ($class->hasConstant("FORCE_LOGIN") && $target::FORCE_LOGIN) {
            if (!$e->getTarget()->getRequest()->isOptions()) {
                $session = StaticOptions::getUserSession();
                if (!$session->isLoggedIn()) {
                    throw new \Exception("User Not Logged-In", 403);
                }
            }
        }
    }

}
