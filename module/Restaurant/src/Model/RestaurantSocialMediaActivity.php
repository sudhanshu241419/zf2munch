<?php

namespace Restaurant\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;


class RestaurantSocialMediaActivity extends AbstractModel {
	
	protected $_db_table_name = 'Restaurant\Model\DbTable\RestaurantSocialMediaTable';
	/**
     * Get restaurant social urls
     * @param int $rest_id
     * @return string
     */
    public function getResSocialUrls($rest_id = 0) {
        //$rest_id = -1;
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $data = current($this->find(array(
                'columns' => array('fb_like_url','foursquare_rating_url'),
                'where' => array('restaurant_id' => $rest_id)
            ))->toArray());
        return $data;
    }
    
    /**
     * Get restaurant one liner
     * @param int $rest_id
     * @return string
     */
    public function getResSocialOneLiner($rest_id = 0) {
        //$rest_id = -1;
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $data = current($this->find(array(
                'columns' => array('cuisine_one_liner','famous_dish_one_liner','ambience_one_liner','chef_feature_one_liner'),
                'where' => array('restaurant_id' => $rest_id)
            ))->toArray());
        return $data;
    }
}