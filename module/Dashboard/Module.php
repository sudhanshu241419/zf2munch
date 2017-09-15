<?php

namespace Dashboard;

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
            'dashboardAuth'
            ), 995);

        $sharedEvents->attach('Zend\Mvc\Controller\AbstractRestfulController', MvcEvent::EVENT_DISPATCH, array(
            $this,
            'getDashboardtoken'
            ), 996);
    }

    public function getDashboardtoken(MvcEvent $e) {        
        return StaticOptions::setDashboardToken($e);
    }

    public function dashboardAuth(MvcEvent $e) {
        $response = $e->getResponse();
        $sl = $e->getTarget()->getServiceLocator();
        $routeMatch = $e->getRouteMatch();
        $isPost = $e->getRequest()->isPost();
        $isOptions = $e->getRequest()->isOptions();
        $isGet = $e->getRequest()->isGet();
        $isDelete = $e->getRequest()->isDelete();
        //pr($_SERVER['HTTP_APP_VERSION'],1);
        //pr($e->getRequest(),1);
        $serverData = $e->getRequest()->getServer()->toArray();


        //pr($e->getRequest()->getPost()->toArray(),1); 
        /*
         * For Mob App
         * Register invalid token incase of old version of app if user is not login
         */
        $dashboardFunctions = new DashboardFunctions();
        $queryParams = $e->getRequest()->getQuery()->toArray();
        $isMobile = isset($queryParams['mob']) ? $queryParams['mob'] : false;
        if (!$isMobile) {
            $isMobile = true;
            $device = "web";
        } else {
            $device = "mobile";
        }
        $version = false;

        if (preg_match('/dashboard/', $serverData['REQUEST_URI'])) {

            $http_server = $e->getRequest()->getServer()->toArray();
            if (isset($http_server['HTTP_APP_VERSION'])) {
                $version = $e->getRequest()->getHeader('App-Version')->getFieldValue();
            }

            ######### Get Token ########

            $token = isset($queryParams['token']) ? $queryParams['token'] : false;

            if (!empty($e->getRequest()->getPost()->toArray())) {
                $postRequest = $e->getRequest()->getPost()->toArray();                
                $token = isset($postRequest['token']) ? $postRequest['token'] : false;
            }

            if (isset($serverData['HTTP_TOKEN']) && !empty($serverData['HTTP_TOKEN'])) {
                $token = isset($serverData['HTTP_TOKEN']) ? $serverData['HTTP_TOKEN'] : false;
            }


            if ($e->getRequest()->getHeader('Authorization')) {
                $authorization = $e->getRequest()->getHeader('Authorization')->getFieldValue();
                $authToken = explode(" ", trim($authorization));
                $token = isset($authToken[1]) ? $authToken[1] : false;                
            }

            if (!$version && $token) {
//              pr($e->getRequest());
//              pr($e->getRequest()->getPost()->toArray(),1);                 

                if (!$dashboardFunctions->findTokenFromRedis($token)) {
                    $tokenDetails = $dashboardFunctions->handleInvalidToken($token);
                }
            }


            ######################## End of register invalid token ############################

            /**
             * @todo: optimize this for common google urls
             */
            $isTokenRoute = $routeMatch->getMatchedRouteName() == 'api-token';
            if ($isTokenRoute && ($isPost || $isGet || $isDelete || $isOptions)) {
                return true;
            }
            $authenticator = new \Dashboard\Authenticators\Authenticate ();
            if (!$authenticator->authenticateRequest($e->getRequest(), $sl)) {
                if (!$version && $isMobile) {
                    return true;
                }
                $vars = array(
                    'error' => 'Invalid/Expired token'
                );
                $vars = StaticOptions::formatResponse($vars, 403, 'Invalid/Expired token', $device, false);
                $response = StaticOptions::getResponse($sl, $vars, 403);
                $e->stopPropagation();
                return $response;
            }
        }
    }

}
