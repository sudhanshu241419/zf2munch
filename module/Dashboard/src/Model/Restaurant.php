<?php

namespace Dashboard\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;

class Restaurant extends AbstractModel {

    public $id;
    public $rest_code;
    public $restaurant_name;
    public $description;
    public $address;
    public $street;
    public $city_id;
    public $zipcode;
    public $landmark;
    public $restaurant_image_name;
    public $accept_cc;
    public $accept_cc_phone;
    public $accept_dc;
    public $delivery;
    public $takeout;
    public $dining;
    public $reservations;
    public $menu_available;
    public $is_chain;
    public $latitude;
    public $longitude;
    public $neighborhood;
    public $nbd_latitude;
    public $nbd_longitude;
    public $closed;
    public $inactive;
    public $price;
    public $delivery_area;
    public $minimum_delivery;
    public $min_partysize;
    public $delivery_charge;
    public $sentiments;
    public $cash;
    public $delevery_charge_type;
    public $attire_desc;
    public $good_for_group_desc;
    public $facebook_url;
    public $twitter_url;
    public $gmail_url;
    public $pinterest_url;
    public $instagram_url;
    public $delivery_desc;
    public $notable_chef_desc;
    public $parking_desc;
    public $updated_on;
    public $total_seats;
    public $ratings;
    public $phone_no;
    public $email;
    public $mobile_no;
    public $source_url;
    public $phone_no2;
    public $fax;
    public $menu_without_price;
    public $allowed_zip;
    public $delivery_geo;
    public $order_pass_through;
    public $menu_sort_order;
    public $cod;
    public $restaurant_logo_name;
    public $host_name;
    protected $_db_table_name = 'Dashboard\Model\DbTable\RestaurantTable';

    public function findRestaurant(array $options = array()) {

        $restaurant = $this->find($options)->current();
        if (!$restaurant) {
            throw new \Exception("No Result Found");
        }
        $this->exchangeArray($restaurant->toArray());
        return $this;
    }

    public function getRestaurantDetails($restaurant_id = 0) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'id',
            'restaurant_name',
            'rest_code',
            'address',
            'zipcode',
            'restaurant_image_name','restaurant_logo_name',
            'phone_no', 'phone_no2', 'fax', 'facebook_url', 'twitter_url', 'gmail_url', 'pinterest_url','instagram_url',
            'latitude',
            'longitude', 'source_url'
        ));

        $select->join(array(
            'ra' => 'restaurant_accounts'
                ), 'restaurants.id=ra.restaurant_id', array(
            'user_name', 'email', 'memail', 'name', 'role', 'phone', 'updated_at' => 'created_on'
                ), $select::JOIN_INNER);

        $select->join(array(
            'c' => 'cities'
                ), 'c.id = restaurants.city_id', array(
            'city_name'
                ), $select::JOIN_INNER);

        $select->join(array(
            's' => 'states'
                ), 's.id = c.state_id', array(
            'state'
                ), $select::JOIN_INNER);

        $select->join(array(
            'co' => 'countries'
                ), 'co.id = c.country_id', array(
            'country_name'
                ), $select::JOIN_INNER);

        $select->where(array(
            'restaurants.id' => $restaurant_id
        ));
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $restaurantDetails = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->current();
        return $restaurantDetails->getArrayCopy();
    }

    public function getRestaurantShortAddress($restaurant_id = 0) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());

        $select->columns(array(
            'address'
        ));

        $select->join(array(
            'rl' => 'restaurants_location'
                ), 'rl.restaurant_id = restaurants.id', array(
            'miles' => 'max_delivery_distance'
                )
                , $select::JOIN_INNER);

        $select->where(array(
            'restaurants.id' => $restaurant_id
        ));

        $restaurantAddress = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->current();
        return $restaurantAddress->getArrayCopy();
    }

    public function isRestaurantExists($rest_id = 0) {
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $res = $this->find(array(
                    'columns' => array(
                        'total' => new Expression('COUNT(id)')
                    ),
                    'where' => array(
                        'id' => $rest_id
                    )
                ))->current()->getArrayCopy();
        return $res['total'];
    }

    public function findByRestaurantId(array $options = array()) {
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $restaurant = $this->find($options)->current();
        return $restaurant;
    }

    public function getRestaurantCode($ids) {
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'rest_code',
                )
        );
        $where = New Where();
        $where->in('id', $ids);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $points = $this->getDbTable()
                ->setArrayObjectPrototype('ArrayObject')
                ->getReadGateway()
                ->selectWith($select)
                ->toArray();

        return $points;
    }

    public function getRestaurantCountByCity($city_id) {
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $count = $this->find(array(
            'columns' => array(
                'total_count' => new Expression('COUNT(id)')
            ),
            'where' => array(
                'menu_available' => 1,
                'inactive' => 0,
                'closed' => 0,
                'city_id' => $city_id
            )
        ));
        $data = $count->current();
        if ($data) {
            $data = $data->getArrayCopy();
            $data = (int) $data['total_count'];
        } else {
            $data = 0;
        }
        return $data;
    }

    /**
     * Returns delivery_area,delivery_geo, lat,long of the restaurants
     * @param int $res_id
     * @return array
     */
    public function getRestaurantDeliveryData($res_id = 0) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'id',
            'city_id',
            'delivery',
            'latitude',
            'longitude',
            'delivery_area',
            'delivery_geo'
        ));

        $select->where(array(
            'id' => $res_id
        ));
        $data = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        if ($data) {
            return $data[0];
        } else {
            return false;
        }
    }

    public function isAcceptCcPhoneEnabled($rest_id = 0) {
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $res = $this->find(array(
                    'columns' => array('accept_cc_phone'),
                    'where' => array('id' => $rest_id)
                ))->toArray();
        if ((count($res) > 0) && ($res[0]['accept_cc_phone'] == 1)) {
            return true;
        }
        return false;
    }

    /**
     * Get restaurant primary image name
     * @param int $rest_id
     * @return string
     */
    public function getResPrimaryImgName($rest_id = 0) {
        //$rest_id = -1;
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $data = $this->find(array(
                    'columns' => array('restaurant_image_name'),
                    'where' => array('id' => $rest_id)
                ))->toArray();
        if (!empty($data) && strlen($data[0]['restaurant_image_name']) > 0) {
            return $data[0]['restaurant_image_name'];
        }
        return '';
    }

    public function getAllRestaurant($offset = 1, $limit = 10) {
        $options = array(
            'columns' => array(
                'id'
            ), 'offset' => $offset, 'limit' => $limit
        );
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $restaurants = $this->find($options)->toArray();
        return $restaurants;
    }

    public function getRestaurantCounts() {
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $count = $this->find(array(
            'columns' => array(
                'total_count' => new Expression('COUNT(id)')
            )
        ));
        $data = $count->current();
        if ($data) {
            $data = $data->getArrayCopy();
            $data = (int) $data['total_count'];
        } else {
            $data = 0;
        }
        return $data;
    }

    public function getDineAndMoreTaggedRestaurants($tagId) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'id',
            'restaurant_name',
            'address',
        ));
        $select->join(array(
            'rt' => 'restaurant_tags'
                ), 'rt.restaurant_id = restaurants.id', array(
            'tag_id'
                ), $select::JOIN_INNER);
        $select->where(array(
            'rt.status' => 1,
            'rt.tag_id' => $tagId
        ));
        $restaurantsList = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        return $restaurantsList;
    }

    public function dineAndMoreRestaurant($limit = FALSE, $order = FALSE, $restaurantIds = array()) {
        $tags = new \Restaurant\Model\Tags();
        $tagsDetails = $tags->getTagDetailByName("dine-more");
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'id',
            'restaurant_name',
            'res_code' => 'rest_code',
            'price',
            'allowed_zip',
            'restaurant_image_name',
            'minimum_delivery',
            'description',
            'city_id',
            'address',
            'zipcode',
            'has_delivery' => 'delivery',
            'has_takeout' => 'takeout',
            'has_dining' => 'dining',
            'has_menu' => 'menu_available',
            'has_reservation' => 'reservations',
            'price' => 'price',
            'delivery_area',
            'minimum_delivery',
            'delivery_charge',
            'latitude',
            'longitude',
            'accept_cc',
            'menu_without_price',
            'accept_cc_phone',
            'phone_no',
            'delivery_desc',
            'allowed_zip',
            'restaurant_image_name',
            'order_pass_through'
        ));

        $select->join(array(
            'rt' => 'restaurant_tags'
                ), 'restaurants.id = rt.restaurant_id', array(
            'tag_id', 'rest_short_url'
                ), $select::JOIN_INNER);

        $where = new Where();
        $where->NEST->equalTo('restaurants.closed', 0)->AND->equalTo('restaurants.inactive', 0)->AND->equalTo('rt.status', 1)->AND->equalTo('rt.tag_id', $tagsDetails[0]['tags_id'])->UNNEST;

        if (!empty($restaurantIds)) {
            $where->andPredicate(new \Zend\Db\Sql\Predicate\NotIn('restaurants.id ', $restaurantIds));
        }
        $select->where($where);
        if ($limit && $limit != 0) {
            $select->limit($limit);
        }
        $select->order(new \Zend\Db\Sql\Predicate\Expression('RAND()'));
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $userDineAndMoreRestaurant = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        return $userDineAndMoreRestaurant;
    }

    public function getFeaturedRestaurant($limit) {
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $joins = array();
        $joins [] = array(
            'name' => array(
                'ra' => 'restaurant_accounts'
            ),
            'on' => 'ra.restaurant_id = restaurants.id',
            'columns' => array(
                'account_status' => 'status'
            ),
            'type' => 'inner'
        );
        $options = array(
            'columns' => array(
                'id',
                'restaurant_name',
                'res_code' => 'rest_code',
                'price',
                'allowed_zip',
                'restaurant_image_name',
                'minimum_delivery',
                'description',
                'city_id',
                'address',
                'zipcode',
                'has_delivery' => 'delivery',
                'has_takeout' => 'takeout',
                'has_dining' => 'dining',
                'has_menu' => 'menu_available',
                'has_reservation' => 'reservations',
                'price' => 'price',
                'delivery_area',
                'minimum_delivery',
                'delivery_charge',
                'latitude',
                'longitude',
                'accept_cc',
                'menu_without_price',
                'accept_cc_phone',
                'phone_no',
                'delivery_desc',
                'allowed_zip',
                'restaurant_image_name',
                'order_pass_through'
            ),
            'where' => array(
                'restaurants.featured' => 1, 'restaurants.closed' => 0, 'restaurants.inactive' => 0
            ),
            'order' => new Expression('RAND()'),
            'limit' => $limit,
        );

        return $this->find($options)->toArray();
    }

    public function getAllRestaurantByCity($cityId, $tagId, $offset = 1, $limit = 10) {
        $joins = array();
        $joins [] = array(
            'name' => array(
                'rt' => 'restaurant_tags'
            ),
            'on' => new Expression("(restaurants.id = rt.restaurant_id AND rt.tag_id=$tagId AND rt.status=1)"),
            'columns' => array(
                'tag_id'
            ),
            'type' => 'left'
        );
        $options = array(
            'columns' => array(
                'id',
                'restaurant_name',
            ),
            'where' => array('city_id' => $cityId, 'closed' => 0, 'inactive' => 0),
            'joins' => $joins,
            'offset' => $offset, 'limit' => $limit
        );
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $restaurants = $this->find($options)->toArray();
        return $restaurants;
    }

    public function getRestaurantCountsByCity($cityId) {
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $count = $this->find(array(
            'columns' => array(
                'total_count' => new Expression('COUNT(id)')
            ),
            'where' => array('city_id' => $cityId, 'closed' => 0, 'inactive' => 0)
        ));
        $data = $count->current();
        if ($data) {
            $data = $data->getArrayCopy();
            $data = (int) $data['total_count'];
        } else {
            $data = 0;
        }
        return $data;
    }

    public function restaurantAddress($restaurantId) {
        $restaurantAddress = "";
        $joins = [];
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $joins [] = array(
            'name' => array(
                'c' => 'cities'
            ),
            'on' => 'c.id = restaurants.city_id',
            'columns' => array(
                'city' => 'city_name',
                'sales_tax'
            ),
            'type' => 'left'
        );
        $options = array(
            'columns' => array(
                'id',
                'name' => 'restaurant_name',
                'city_id',
                'address',
                'zipcode',
                'res_code' => 'rest_code',
            ),
            'joins' => $joins,
            'where' => array(
                'restaurants.id' => $restaurantId,
                'restaurants.inactive' => 0,
                'restaurants.closed' => 0
            )
        );

        $restaurantDetails = $this->find($options)->toArray();
        if ($restaurantDetails) {
            $restaurantAddress = $restaurantDetails[0]['address'] . ", " . $restaurantDetails[0]['city'] . ", " . $restaurantDetails[0]['zipcode'];
            $this->restaurantName = $restaurantDetails[0]['name'];
        }
        return $restaurantAddress;
    }

    public function getRestaurantTotalSeats($restId) {
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'total_seats'
        ));
        $where = new Where ();
        $where->equalTo('id', $restId);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $seats = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        if (!empty($seats)) {
            return $seats[0]['total_seats'];
        } else {
            return 0;
        }
    }

    public function getRestaurantDetail($restId) {
        $data = [];
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            '*'
        ));
        $where = new Where ();
        $where->equalTo('id', $restId);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $data = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->current();
        if (!empty($data)) {
            return $data;
        } else {
            return $data;
        }
    }

    public function update($id, $data) {
        $writeGateway = $this->getDbTable()->getWriteGateway();
        if ($id) {
            $rowsAffected = $writeGateway->update($data, array(
                'id' => $id
            ));
        }
        if ($rowsAffected) {
            return true;
        }
        return false;
    }

    public function updateRestaurantDetails($data, $restId) {
        $record = $this->getRestaurantDetail($restId);
        if (!empty($record)) {
            if ($data['name']) {
                $update['restaurant_name'] = $data['name'];
            }
            if ($data['address']) {
                $update['address'] = $data['address'];
            }
            if ($data['source_url']) {
                $update['source_url'] = $data['source_url'];
            }
            if ($data['phone']) {
                $update['phone_no'] = $data['phone'];
            }
            $update['phone_no2'] = $data['phone_alternate'];

            if ($data['email']) {
                $update['email'] = $data['email'];
            }
            if ($data['fax']) {
                $update['fax'] = $data['fax'];
            }
            if ($data['facebook']) {
                $update['facebook_url'] = $data['facebook'];
            }
            if ($data['twitter']) {
                $update['twitter_url'] = $data['twitter'];
            }
            if ($data['gmail_url']) {
                $update['gmail_url'] = $data['gmail_url'];
            }
            if ($data['pinterest_url']) {
                $update['pinterest_url'] = $data['pinterest_url'];
            }
            $update['updated_on'] = date("Y-m-d H:i:s");
            $this->update($record['id'], $update);
        }
        if ($record) {
            $indexingModel = new CmsSolrindexing();
            $indexingModel->solrIndexRestaurant($restId, $record['rest_code']);
            return ["status"=>"success"];
        } else {
            return ["status"=>"error"];
        }
    }

}
