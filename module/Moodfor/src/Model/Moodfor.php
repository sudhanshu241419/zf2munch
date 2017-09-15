<?php

namespace Moodfor\Model;

use MCommons\Model\AbstractModel;
use Zend\Http\Client;
use Zend\Json\Json;
use MCommons\StaticOptions;
// use Solr\RestClient;
class Moodfor extends AbstractModel {
	public function moodForList(array $params = array()) {
		$httpClient = new Client ( StaticOptions::getSolrUrl () . 'hbautosuggest/hbsearch?' );
		$httpClient->setParameterGet ( $params );
		
		$response = $httpClient->dispatch ( $httpClient->getRequest () );
		
		if ($response->getStatusCode () == '200') {
			return Json::decode ( $response->getBody (), Json::TYPE_ARRAY );
		} else {
			throw new \Exception ( 'Your mood option is not found', 400 );
		}
	}
}