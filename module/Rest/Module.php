<?php

namespace Rest;

use Zend\Mvc\MvcEvent;
use MCommons\StaticOptions;

/**
 * Module Management for rest api
 *
 * @author tirth
 * @namespace Rest
 */
class Module extends \MCommons\Module {

    protected $_namespace = __NAMESPACE__;
    protected $_dir = __DIR__;

    /**
     * Higher the priority in the events they are executed earlier
     * Negative priorites allowed
     *
     * @param MvcEvent $e        	
     */
    public function onBootstrap($e) {
        /**
         * Get the module manager for the current Application
         *
         * @var \Zend\ModuleManager\ModuleManager $moduleManager
         */
        $moduleManager = $e->getApplication()->getServiceManager()->get('modulemanager');

        /**
         * Get the set of shared events
         *
         * @var \Zend\EventManager\SharedEventManager $sharedEvents
         */
        $sharedEvents = $moduleManager->getEventManager()->getSharedManager();

        /**
         * Auth Process after managing the error handler
         */
        $sharedEvents->attach('Zend\Mvc\Controller\AbstractRestfulController', MvcEvent::EVENT_DISPATCH, array(
            $this,
            'authenticate'
                ), 995);

        /**
         * Add Service Locator to Static Options
         */
        $sharedEvents->attach('Zend\Mvc\Controller\AbstractRestfulController', MvcEvent::EVENT_DISPATCH, array(
            $this,
            'addServiceLocator'
                ), 996);
        
        $sharedEvents->attach('Zend\Mvc\Controller\AbstractRestfulController', MvcEvent::EVENT_DISPATCH, array(
            $this,
            'getUserAgent'
                ), 997);
        $sharedEvents->attach('Zend\Mvc\Controller\AbstractRestfulController', MvcEvent::EVENT_DISPATCH, array(
            $this,
            'postProcess'
                ), - 100);

        /**
         * Error Handling
         */
        $sharedEvents->attach('Zend\Mvc\Application', MvcEvent::EVENT_DISPATCH_ERROR, array(
            $this,
            'errorProcess'
                ), 998);

        /**
         * Initialize Constants before moving forward
         */
        $sharedEvents->attach('Zend\Mvc\Controller\AbstractRestfulController', MvcEvent::EVENT_DISPATCH, array(
            $this,
            'initConstants'
                ), 999);
    }

    /**
     * Adding service locator to the static options so that its accessible form anywhere
     *
     * @param MvcEvent $e        	
     */
    public function addServiceLocator(MvcEvent $e) {
        return StaticOptions::setServiceLocator($e->getTarget()->getServiceLocator());
    }

    /**
     * Before continuing with any API requests, please check if the token provided by
     * the user is authentic/expired or not
     *
     * @param MvcEvent $e        	
     * @return boolean
     */
    public function authenticate(MvcEvent $e) {
        $serverData = $e->getRequest()->getServer()->toArray();
        if (preg_match('/dashboard/', $serverData['REQUEST_URI'])) {
            return true;
        }
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
        $userFunction = new \User\UserFunctions();
        $queryParams = $e->getRequest()->getQuery()->toArray();
        $isMobile = isset($queryParams['mob']) ? $queryParams['mob'] : false;
//        
//        if(preg_match('/dashboard/',$serverData['REQUEST_URI']) && $isMobile){
//            return true;
//        }
        
        
        
        if(isset($queryParams['slrRestaurant']) && !empty(isset($queryParams['slrRestaurant']))){
            return true;
        }       
                 
        if (isset($queryParams['token']) && $queryParams['token'] == "munchsmsreg") {
           $http_server = $e->getRequest()->getServer()->toArray();

           ######### Get Token ########
           $token = isset($queryParams['token']) ? $queryParams['token'] : false;
           if ($token) {                
               $tokenDetails = $userFunction->findTokenFromRedis($token);
               if (!$tokenDetails) {
                   $userFunction->handleInvalidToken($token);
               }
           }
       }        
        
        $version = false;

        if ($isMobile) {
            $http_server = $e->getRequest()->getServer()->toArray();
            if (isset($http_server['HTTP_APP_VERSION'])) {
                $version = $e->getRequest()->getHeader('App-Version')->getFieldValue();
            }

            ######### Get Token ########
            $token = isset($queryParams['token']) ? $queryParams['token'] : false;
           
            if (!empty($e->getRequest()->getPost()->toArray())) {
                $requesD = $e->getRequest()->getPost()->toArray();
                $token = isset($requestD['token'])?$requestD['token']:'';
            }

            if ($e->getRequest()->getHeader('Authorization')) {
                $authorization = $e->getRequest()->getHeader('Authorization')->getFieldValue();
                $authToken = explode(" ", trim($authorization));
                $token = isset($authToken[1]) ? $authToken[1] : false;
            }
            
            if (!$version && $token) {
//              pr($e->getRequest());
//              pr($e->getRequest()->getPost()->toArray(),1);                 
                $tokenDetails = $userFunction->findTokenFromRedis($token);                               
                if (!$tokenDetails) {
                    $vars = array(
                       'error' => 'Invalid/Expired token'
                    );
                    $vars = StaticOptions::formatResponse($vars, 403, 'Invalid/Expired token', 'mobile', false);
                    $response = StaticOptions::getResponse($sl, $vars, 403);
                    $e->stopPropagation();
                    return $response;
//                    $userFunction->handleInvalidToken($token);
                }
            }
        }
           
        ######################## End of register invalid token ############################

        /**
         * @todo: optimize this for common google urls
         */
        $isTokenRoute = $routeMatch->getMatchedRouteName() == 'api-token' || $routeMatch->getMatchedRouteName() == 'web-api-token';
        if ($isTokenRoute && ($isPost || $isGet || $isDelete || $isOptions)) {
            return true;
        }
        $authenticator = new \Rest\Authenticators\Authenticate ();
        if (!$authenticator->authenticateRequest($e->getRequest(), $sl)) {
            if (!$version && $isMobile) {
                return true;
            }

            $vars = array(
                'error' => 'Invalid/Expired token'
            );
            $vars = StaticOptions::formatResponse($vars, 403, 'Invalid/Expired token', 'mobile', false);
            $response = StaticOptions::getResponse($sl, $vars, 403);
            $e->stopPropagation();
            return $response;
        }
    }

    /**
     *
     * @param MvcEvent $e        	
     * @return null \Zend\Http\PhpEnvironment\Response
     */
    public function postProcess(MvcEvent $e) {
        $formatter = StaticOptions::getFormatter();
        /**
         *
         * @var \Zend\Di\Di $di
         */
        $sl = $e->getTarget()->getServiceLocator();

        if ($formatter !== false) {
            if ($e->getResult() instanceof \Zend\View\Model\ViewModel) {
                if (is_array($e->getResult()->getVariables())) {
                    // Get the variables from
                    $vars = $e->getResult()->getVariables();
                } else {
                    $vars = null;
                }
            } else {
                $vars = $e->getResult();
            }

            $request = $sl->get('request');
            $requestType = (bool) $request->getQuery('mob', false) ? 'mobile' : 'web';
            $vars = StaticOptions::formatResponse($vars, 200, 'Success', $requestType);
            $response = StaticOptions::getResponse($sl, $vars, 200);

            return $response;
        }
        return false;
    }

    /**
     *
     * @todo This can be more optimized if we can set the value of Service locator
     *       before executing the code as we can use the
     *       StaticOptions::getResponse and StaticOptions::getFormatter functions for centralizing the code
     * @param MvcEvent $e        	
     * @return null \Zend\Http\PhpEnvironment\Response
     */
    public function errorProcess(MvcEvent $e) {
        /**
         *
         * @var \Zend\Di\Di $di
         */
        $sl = $e->getApplication()->getServiceManager();
        $di = $sl->get('di');

        $eventParams = $e->getParams();
        /**
         *
         * @var array $configuration
         */
        $configuration = $e->getApplication()->getConfig();

        $statusCode = \Zend\Http\PhpEnvironment\Response::STATUS_CODE_500;

        $vars = array();
        if (isset($eventParams ['exception'])) {
            /**
             *
             * @var \Exception $exception
             */
            $exception = $eventParams ['exception'];

            if ($configuration ['errors'] ['show_exceptions'] ['message']) {
                $vars ['error'] = $exception->getMessage();
            }
            if ($configuration ['errors'] ['show_exceptions'] ['trace']) {
                $vars ['error-trace'] = $exception->getTrace();
            }
            $statusCode = $exception->getCode() ? $exception->getCode() : $statusCode;
        }
        if (empty($vars)) {
            $vars ['error'] = 'Invalid request please check the api request';
        }

        /**
         *
         * @var PostProcessor\AbstractPostProcessor $postProcessor
         */
        $routeMatch = $sl->get('Application')->getMvcEvent()->getRouteMatch();
        $config = $sl->get('config');
        try {
            if (isset($config ['api_standards'])) {
                // Get api standards decided
                $apiStandards = $config ['api_standards'];

                // Get default formatter text or set it to "formatter"
                $formatterText = isset($apiStandards ['formatter_text']) ? $apiStandards ['formatter_text'] : "formatter";

                // Set default formatter type from api_standards or set it default to JSON
                $defaultFormatter = isset($apiStandards ['default_formatter']) ? $apiStandards ['default_formatter'] : "json";

                // Get the formatter from query
                $params = $sl->get('request')->getQuery()->getArrayCopy();
                $formatter = isset($params [$formatterText]) ? $params [$formatterText] : $defaultFormatter;
            } else {
                throw new \Exception("Invalid Parameters");
            }
        } catch (\Exception $ex) {
            // On any exception set the formatter to the json
            $formatter = "json";
        }

        if ($eventParams ['error'] === \Zend\Mvc\Application::ERROR_CONTROLLER_NOT_FOUND || $eventParams ['error'] === \Zend\Mvc\Application::ERROR_ROUTER_NO_MATCH) {
            $statusCode = \Zend\Http\PhpEnvironment\Response::STATUS_CODE_405;
            $e->getResponse()->setStatusCode($statusCode);
        } else {
            $e->getResponse()->setStatusCode($statusCode);
        }

        $request = $sl->get('request');
        $requestType = (bool) $request->getQuery('mob', false) ? 'mobile' : 'web';

        $vars = StaticOptions::formatResponse($vars, $statusCode, $vars ['error'], $requestType);

        $postProcessor = $di->get($formatter . "_processor", array(
            'vars' => $vars,
            'response' => $e->getResponse($statusCode)
        ));

        $postProcessor->process();

        $e->stopPropagation();

        return $postProcessor->getResponse();
    }

    public function initConstants(MvcEvent $e) {
        $sl = $e->getApplication()->getServiceManager();
        $config = $sl->get('Config');
        $constants = function ($search_term) use($config) {
            $currTarget = $config ['constants'];
            $kArr = explode(":", $search_term);

            foreach ($kArr as $key => $value) {
                if (isset($currTarget [$value])) {
                    $currTarget = $currTarget [$value];
                } else {
                    throw new \Exception('Invalid Configuration Path: ' . $search_term . ' in $config["constants"]');
                }
            }
            return $currTarget;
        };
        if (($konstants = realpath(BASE_DIR . DS . 'config' . DS . 'konstants.php')) !== false) {
            return require_once $konstants;
        }
        return false;
    }
    
    public function getUserAgent(MvcEvent $e){
       return StaticOptions::setUserAgent($e->getRequest()->getHeader('User-Agent'));        
    }  

}
