<?php

namespace Dashboard\Authenticators;

use Zend\Http\PhpEnvironment\Request;
use Zend\Json\Json;
use Zend\ServiceManager\ServiceLocatorInterface;
use MCommons\StaticOptions;
use Dashboard\Model\UserSession;

class Authenticate {

	protected $_request;
	protected $_serviceLocator;
	protected $_tokenValue;
	/**
	 *
	 * @var array
	 */
	protected $contentTypes = array (
			self::CONTENT_TYPE_JSON => array (
					'application/hal+json',
					'application/json' 
			) 
	);
	const CONTENT_TYPE_JSON = 'json';
	
	/**
	 *
	 * @var int From Zend\Json\Json
	 */
	protected $jsonDecodeType = Json::TYPE_ARRAY;
	public function authenticateRequest(Request $request, ServiceLocatorInterface $serviceLocator) {
		$this->_request = $request;
		$this->_serviceLocator = $serviceLocator;
		
		$this->_tokenValue = $this->getTokenValue ();
		if ($this->_request->getMethod () == "OPTIONS") {
			return true;
		}
		
		if ($this->isValidToken ()) {
			$this->updateTokenTimestamp ();
			return true;
		}
		$this->deleteToken ();
		return false;
	}
	protected function getTokenValue() {
		$config = $this->_serviceLocator->get ( 'Config' );
		$tokenText = isset ( $config ['api_standards'] ) && isset ( $config ['api_standards'] ['token_text'] ) ? $config ['api_standards'] ['token_text'] : 'token';
		$tokenValue = false;
		$route = StaticOptions::getServiceLocator()->get('router');
		$routeName = $route->match($this->_request)->getMatchedRouteName();
		if($routeName == "user-login-google"){
			 $encodedState = $this->_request->getQuery ( 'state', false );
			 $decodedState = base64_decode($encodedState);
			 $info = explode(":::",$decodedState);
			 if($info && isset($info[0])) {
			 	return $info[0];
			 }
		}
		if($routeName == "user-login-google-contact"){
			return $tokenValue =$this->_request->getQuery ( 'state', false );
		}
		if($routeName == "user-login-microsoft"){
			return $tokenValue =$this->_request->getQuery ( 'state', false );
		}
		/*if($routeName == "user-login-yahoo-contact"){
			return $tokenValue =$this->_request->getQuery ( 'state', false );
		}*/
		switch ($this->_request->getMethod ()) {
			case "GET" :
			case "DELETE" :
				$tokenValue = $this->_request->getQuery ( $tokenText, false );
				break;
			case "POST" :
				$postData = $this->getPostData ();
				if (isset ( $postData ) && isset ( $postData [$tokenText] ) && $postData [$tokenText]) {
					$tokenValue = $postData [$tokenText];
				} else {
					$tokenValue = false;
				}
				break;
			case "PUT" :
				// Parse the PUT content to extract the token
				$vars = $this->getPutData ();
				$tokenValue = isset ( $vars [$tokenText] ) ? $vars [$tokenText] : false;
				break;
			default :
				break;
		}
		return $tokenValue;
	}
	protected function isValidToken() {
		$tokenValue = $this->_tokenValue;
		if (! $tokenValue) {
			return false;
		}
		$redisCache = StaticOptions::getRedisCache();
		if($redisCache) {           
			$tokenDetails = false;
			if($redisCache->hasItem($tokenValue)) {
				$tokenDetailsData = $redisCache->getItem($tokenValue);
				$tokenDetails = new \Dashboard\Model\Token();
				$tokenDetails->exchangeArray($tokenDetailsData);
			}
		} else {           
			$authTable = $this->_serviceLocator->get ( 'Dashboard\Model\DbTable\AuthTable' );
			$authTable->setArrayObjectPrototype ( 'Dashboard\Model\Token' );
			$tokenDetails = $authTable->getReadGateway ()->select ( array (
					'token' => $tokenValue
			) )->current ();
		}
		if (! $tokenDetails) {
			return false;
		}
		
		$dashboardDetails = @unserialize ( $tokenDetails->dashboard_details );
		if (! $dashboardDetails) {
			$userDetails = array ();
		}
		$dashboardDetails ['dashboard_id'] = ( int ) $tokenDetails->dashboard_id;
//		$userSessionModel = new UserSession();
//		$userSessionModel->exchangeArray ( $tokenDetails->toArray () );
//		StaticOptions::setUserSession ( $userSessionModel );
		
		$ttl = ( int ) $tokenDetails->ttl;
		$lastUpdateTimestamp = ( int ) $tokenDetails->last_update_timestamp;
		
		// Add according to server time rather than city dependent time
		$currentTimestamp = ( int ) StaticOptions::getDateTime ()->getTimestamp ();
		if (($lastUpdateTimestamp + $ttl) < $currentTimestamp) {
			return false;
		}
		return true;
	}
	protected function updateTokenTimestamp() {
		$tokenValue = $this->_tokenValue;
		$redisCache = StaticOptions::getRedisCache();
		if($redisCache) {
			$tokenDetails = new \Dashboard\Model\Token();
			$token = $tokenDetails->findToken($this->_tokenValue);
			if(!$token) {
				$token = $tokenDetails;
			}           
			$tokenDetails->token = $tokenValue;
			$tokenDetails->last_update_timestamp = time ();
			$tokenDetails->save();
			return true;
		} 
		$authTable = $this->_serviceLocator->get ( 'Dashboard\Model\DbTable\AuthTable' );
		$authTable->setArrayObjectPrototype ( 'Dashboard\Model\Token' );
		$updated = $authTable->getWriteGateway ()->update ( array (
				'last_update_timestamp' => time () 
		), array (
				'token' => $this->_tokenValue 
		) );
		if (! $updated) {
			return false;
		}
		return true;
	}
	protected function deleteToken() {
		$authTable = $this->_serviceLocator->get ( 'Dashboard\Model\DbTable\AuthTable' );
		$authTable->setArrayObjectPrototype ( 'Dashboard\Model\Token' );
		$deleted = $authTable->getWriteGateway ()->delete ( array (
				'token' => $this->_tokenValue 
		) );
		if (! $deleted) {
			return false;
		}
		return true;
	}
	private function getPostData() {
		if ($this->requestHasContentType ( self::CONTENT_TYPE_JSON )) {
			$data = Json::decode ( $this->_request->getContent (), $this->jsonDecodeType );
		} else {
			$data = $this->_request->getPost ()->toArray ();
		}
		return $data;
	}
	private function requestHasContentType($contentType = '') {
		/**
		 * @var $headerContentType \Zend\Http\Header\ContentType
		 */
		$headerContentType = $this->_request->getHeaders ()->get ( 'content-type' );
		if (! $headerContentType) {
			$userAgent = $this->_request->getHeaders ()->get ( 'User-Agent' );
			if ($userAgent) {
				$userAgentData = $userAgent->getFieldValue ();
				if (preg_match ( '/(?i)msie [1-9]/', strtolower ( $userAgentData ) ) && $contentType == self::CONTENT_TYPE_JSON) {
					return true;
				}
			}
			return false;
		}
		
		$requestedContentType = $headerContentType->getFieldValue ();
		if (strstr ( $requestedContentType, ';' )) {
			$headerData = explode ( ';', $requestedContentType );
			$requestedContentType = array_shift ( $headerData );
		}
		$requestedContentType = trim ( $requestedContentType );
		if (array_key_exists ( $contentType, $this->contentTypes )) {
			foreach ( $this->contentTypes [$contentType] as $contentTypeValue ) {
				if (stripos ( $contentTypeValue, $requestedContentType ) === 0) {
					return true;
				}
			}
		}
		return false;
	}
	/**
	 * Process the raw body content from PUT request
	 *
	 * If the content-type indicates a JSON payload, the payload is immediately
	 * decoded and the data returned. Otherwise, the data is passed to
	 * parse_str(). If that function returns a single-member array with a key
	 * of "0", the method assumes that we have non-urlencoded content and
	 * returns the raw content; otherwise, the array created is returned.
	 *
	 * @param mixed $request        	
	 * @return object string array
	 */
	protected function getPutData() {
		$content = $this->_request->getContent ();
		// JSON content? decode and return it.
		if ($this->requestHasContentType ( self::CONTENT_TYPE_JSON )) {
			return Json::decode ( $content, $this->jsonDecodeType );
		}
		
		parse_str ( $content, $parsedParams );
		
		// If parse_str fails to decode, or we have a single element with key
		// 0, return the raw content.
		if (! is_array ( $parsedParams ) || (1 == count ( $parsedParams ) && isset ( $parsedParams [0] ))) {
			return $content;
		}
		return $parsedParams;
	}

}