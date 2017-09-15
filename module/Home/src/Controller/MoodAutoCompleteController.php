<?php

namespace Home\Controller;

use MCommons\Controller\AbstractRestfulController;
use Solr\AutoComplete;
use Search\SearchFunctions;

class MoodAutoCompleteController extends AbstractRestfulController {

    public function getList() {
        $rawInput = SearchFunctions::mapRequestKeys($this->params()->fromQuery());
        if (!isset($rawInput['term']) || strlen($rawInput['term']) < 2) {
            return array();
        }        
        $user_loc = $this->getUserSession()->getUserDetail('selected_location', array());
        $rawInput['city_id'] = isset($user_loc ['city_id']) ? intval($user_loc ['city_id']) : 18848;
        $rawInput['city_name'] = isset($user_loc['city_name']) ? $user_loc['city_name'] : 'New York';
        
        $request = SearchFunctions::cleanWebSearchParams($rawInput);

        $ac = new AutoComplete();
        $autocompleteArr = $ac->getAutocomplete($request);
        return isset($autocompleteArr ['data']) ? $autocompleteArr ['data'] : array();
    }

}
