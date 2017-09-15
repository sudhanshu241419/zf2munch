<?php

namespace Dashboard\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;

class RestaurantCuisine extends AbstractModel {

    public $id;
    protected $_db_table_name = 'Dashboard\Model\DbTable\RestaurantCuisineTable';

    public function get_cuisine_types_and_popular_foods_and_trends() {
        $output = array();
        $i = 0;
        $arrCuisineTypes = array();
        $arrCuisinesList = array();
        $arrPopularFoods = array();
        $arrTrends = array();
        $arrPopularCuisines = array();

        $cuisinesModel = new Cuisine(); //Cuisine::get_all_cuisine_by_type();
        $all_cuisines = $cuisinesModel->get_all_cuisine_by_type();
        //pr($all_cuisines,1);

        if ($all_cuisines) {

            foreach ($all_cuisines as $key => $val) {
                if (preg_match('/fast food/', strtolower($val['cuisine_type']))) {
                    array_push($arrPopularFoods, $val);
                    continue;
                } else if (preg_match('/trends/', strtolower($val['cuisine_type']))) {
                    array_push($arrTrends, $val);
                    continue;
                }

                if (preg_match('/ameri/', strtolower($val['cuisine_type']))) {
                    $val['cuisine_type'] = 'Americas';
                }
                if (!in_array($val['cuisine_type'], $arrCuisineTypes)) {
                    array_push($arrCuisineTypes, $val['cuisine_type']);
                    $output[$i]['type'] = $val['cuisine_type'];
                    $i++;
                }
                if (preg_match('/ameri/', strtolower($val['cuisine_type']))) {
                    $arrCuisinesList['Americas'][] = $val;
                } else {
                    $arrCuisinesList[$val['cuisine_type']][] = $val;
                }

                if ($val['priority'] != 0)
                    $arrPopularCuisines[] = $val;
            }
        }
        foreach ($arrPopularCuisines as $key => $row) {
            $pcuisine[$key] = $row['priority'];
        }
        if ($arrPopularCuisines) {
            array_multisort($pcuisine, SORT_ASC, $arrPopularCuisines);
        }
        $cuisines_with_type = array();
        $i = 0;
        $total_cuisines_with_type = 0;
        foreach ($arrCuisineTypes as $key => $val) {
            $cuisines_with_type[$i]['type'] = $val;
            $cuisines_with_type[$i]['cuisines'] = $arrCuisinesList[$val];
            $cuisines_count = count($arrCuisinesList[$val]);
            $cuisines_with_type[$i]['cuisines_count'] = $cuisines_count;
            $cuisines_with_type[$i]['sort_key'] = isset(Cuisine::$cusine_in_order[$cuisines_with_type[$i]['type']])?Cuisine::$cusine_in_order[$cuisines_with_type[$i]['type']]:"";
            $total_cuisines_with_type += $cuisines_count;
            $i++;
        }


        foreach ($cuisines_with_type as $key => $row) {
            $mid[$key] = $row['sort_key'];
        }
        // Sort the data with mid descending
        // Add $data as the last parameter, to sort by the common key
        if ($cuisines_with_type) {
            array_multisort($mid, SORT_ASC, $cuisines_with_type);
        }
        $total_popular_foods = count($arrPopularFoods);
        $total_trends = count($arrTrends);

        $data['total_cuisines_count'] = $total_cuisines_with_type + $total_popular_foods + $total_trends;

        $data['total_cuisines_with_type'] = $total_cuisines_with_type;
        $data['popular_cuisine'] = $arrPopularCuisines;
        $data['cuisines_with_type'] = $cuisines_with_type;

        $data['total_popular_foods'] = $total_popular_foods;
        $data['popular_foods'] = $arrPopularFoods;

        $data['total_trends'] = $total_trends;
        $data['trends'] = $arrTrends;
        return $data;
    }

    public function get_restaurant_cuisine_string($restaurant_id) {
        $cuisines = array();
        $cuisine = null;
        $cuisinesModel = new Cuisine(); //Cuisine::get_all_cuisine_by_type();
        $attributes = $cuisinesModel->getRestaurantCuisine($restaurant_id);
        //pr($attributes,1);

        if (!empty($attributes)) {
            foreach ($attributes as $key => $value) {
                $cuisines[] = self::to_utf8($value['cuisine']);
            }
            $cuisine = @implode(",", $cuisines);
        }

        return $cuisine;
    }

    public function get_restaurant_cuisines_id($restaurant_id) {
        $cuisines = array();
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'cuisine_id'
        ));
        $select->where(array(
            'restaurant_id' => $restaurant_id,
            'status' => '1'
        ));
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $record = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        if (!empty($record)) {
            foreach ($record as $key => $value) {
                $cuisines[] = $value['cuisine_id'];
            }
        }

        return $cuisines;
    }

    public function getRestaurantCuisines($condition) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array('*'));
        $where = new Where ();
        $where->equalTo('restaurant_id', $condition['restaurant_id']);
        $where->equalTo('cuisine_id', $condition['cuisine_id']);
        $where->equalTo('status', $condition['status']);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $record = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->current();
        if (!empty($record)) {
            return $record;
        }
    }

    public function update_restaurant_cuisine($cuisines, $restId) {
        $restModel = new Restaurant();
        $restDetail = $restModel->getRestaurantDetail($restId);
        if (!empty($cuisines)) {
            $this->update_restaurant_cuisines_status($restId);
            foreach ($cuisines as $value) {
                $condition = ['restaurant_id' => $restId, 'cuisine_id' => $value, 'status' => 0];
                $exists = $this->getRestaurantCuisines($condition);
                if ($exists) {
                    $id = $exists['id'];
                    $this->update($id, array('status' => '1'));
                } else {
                    $cuisineData['cuisine_id'] = $value;
                    $cuisineData['restaurant_id'] = $restId;
                    $cuisineData['status'] = '1';
                    $this->save($cuisineData);
                }
            }
            $indexingModel = new CmsSolrindexing();
            $indexingModel->solrIndexRestaurant($restId, $restDetail['rest_code']);
        }
        return ["status"=>"success"];
    }

    public function update($id = 0, $data) {
        $writeGateway = $this->getDbTable()->getWriteGateway();
        if ($id != 0) {
            $rowsAffected = $writeGateway->update($data, array(
                'id' => $id
            ));
        } else {
            $rowsAffected = $writeGateway->insert($data);
        }
        if ($rowsAffected) {
            return true;
        }
        return false;
    }

    public function save($data) {
        $writeGateway = $this->getDbTable()->getWriteGateway();
        try {
            $rowsAffected = $writeGateway->insert($data);
        } catch (\Exception $e) {
            \Zend\Debug\Debug::dump($e->__toString());
            exit;
        }
        if ($rowsAffected) {
            return true;
        }
        return false;
    }

    public function update_restaurant_cuisines_status($restId) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array('*'));
        $where = new Where ();
        $where->equalTo('restaurant_id', $restId);
        $where->equalTo('status', 1);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $record = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        if (!empty($record)) {
            foreach ($record as $value) {
                $this->update($value['id'], array('status' => '0'));
            }
        }
        return true;
    }

}
