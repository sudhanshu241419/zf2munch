<?php

namespace User\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
class MunchadoCareer extends AbstractModel {

    public $id;
    public $platform;
    public $department;
    public $location;
    public $position;
    public $description;
    public $the_idel;
    public $what_you_will_do;
    public $what_you_will_need;
    public $additional_information_heading;
    public $additional_information;    
    public $updated_at;
    public $status;
    public $created_at;
    protected $_db_table_name = 'User\Model\DbTable\MunchadoCareerTable';
    protected $_primary_key = 'id';

    public function getDetails($options) {
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $response = $this->find($options)->toArray();
        return $response;
    }

    public function getDetailLists($options) {
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $result = $this->find($options)->toArray();
        $response['department'] = $this->prepairOpeningPosition($result);
        $locationWise = $this->getLocationLists($options['where']['platform']);
        $response = array_merge($response, $locationWise);
        return $response;
    }

    private function prepairOpeningPosition($result) {
        $response = [];
        $i = 0;
        foreach ($result as $key => $value) {

            if ($value['dept_id'] == 0) {
                $response[$i]['title'] = $value['department'];
                $response[$i]['id'] = $value['id'];
                $i++;
            }
        }
        foreach ($response as $k => $val) {
            $y = 0;

            foreach ($result as $key => $v) {

                if ($val['id'] == $v['dept_id']) {
                    $response[$k]['details'][$y]['id'] = $v['id'];
                    $response[$k]['details'][$y]['position'] = $v['position'];
                    $response[$k]['details'][$y]['location'] = $v['location'];
                    $y++;
                }
            }
        }

        foreach ($response as $ky => $va) {
            if (!isset($va['details'])) {
                $response[$ky]['details'] = array();
            }
        }
        return $response;
    }

    public function getLocationLists($plateform) {
        $options = array('columns' => array('id', 'location'), 'where' => array('status' => 1, 'platform' => $plateform), 'group' => "location", 'order' => 'location desc');
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $result = $this->find($options)->toArray();
        $response = $this->prepairLocationwisePosition($result, $plateform);
        return $response;
    }

    private function prepairLocationwisePosition($result, $plateform) {
        $locationList = [];
        $i = 0;

        foreach ($result as $key => $value) {
            $location = ($value['location']) ? true : false;
            if ($location) {
                $select = new Select();
                $select->from($this->getDbTable()->getTableName());
                $select->columns(array('id', 'dept_id', 'location', 'position'));
                $select->join(array(
                    'cd' => 'career_details'
                        ), 'career_details.dept_id = cd.id', array('department'
                        ), $select::JOIN_INNER);
                $select->where(array('career_details.status' => 1, 'career_details.platform' => $plateform, 'career_details.location' => $value['location']));
                $locationData = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
                $locationList['location'][$i]['id'] = $value['id'];
                $locationList['location'][$i]['title'] = $value['location'];
                $locationList['location'][$i]['details'] = $locationData;
                $i++;
            }
        }
        return $locationList;
    }

}
