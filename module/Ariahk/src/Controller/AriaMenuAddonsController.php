<?php

namespace Ariahk\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\Menu;

class AriaMenuAddonsController extends AbstractRestfulController {

    public function get($id) {
        $menuModel = new Menu ();
        $menuModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $joins = array();
        $joins [] = array(
            'name' => array(
                'ma' => 'menu_addons'
            ),
            'on' => 'menus.id = ma.menu_id',
            'columns' => array(
                'addon_option',
                'addon_main_id' => 'id',
                'addon_id',
                'menu_price_id',
                'addon_price' => 'price',
                'addon_price_description' => 'price_description',
                'addon_description' => 'description',
                'selection_type'
            ),
            'type' => 'left'
        );
        $joins [] = array(
            'name' => array(
                'mas' => 'menu_addon_settings'
            ),
            'on' => new \Zend\Db\Sql\Expression('ma.addon_id = mas.addon_id AND mas.menu_id = menus.id'),
            'columns' => array(
                'item_limit',
                'enable_pricing_beyond'
            ),
            'type' => 'inner'
        );
        $joins [] = array(
            'name' => array(
                'mp' => 'menu_prices'
            ),
            'on' => 'ma.menu_price_id = mp.id',
            'columns' => array(
                'price',
                'menu_price_id' => 'id'
            ),
            'type' => 'left'
        );
        $joins [] = array(
            'name' => array(
                'a' => 'addons'
            ),
            'on' => 'a.id = ma.addon_id',
            'columns' => array(
                'addon_name'
            ),
            'type' => 'left'
        );
        $options = array(
            'columns' => array(
                'item_name',
                'item_desc'
            ),
            'where' => array(
                'menus.id' => $id,
                'menus.status' => 1,
                'ma.status' => 1,
                'a.status' => 1,
            ),
            'joins' => $joins
        );
        $response = $menuModel->find($options)->toArray();
        $refined = array();
        foreach ($response as $key => $value) {
            if (!isset($refined [$response [$key] ['menu_price_id']])) {
                $refined [$response [$key] ['menu_price_id']] = array();
            }
            if (!isset($refined [$response [$key] ['menu_price_id']] [$response [$key] ['addon_id']])) {
                $refined [$response [$key] ['menu_price_id']] [$response [$key] ['addon_id']] = array();
                $refined [$response [$key] ['menu_price_id']] [$response [$key] ['addon_id']] ['name'] = $response [$key] ['addon_name'];
                $refined [$response [$key] ['menu_price_id']] [$response [$key] ['addon_id']] ['addon_id'] = $response [$key] ['addon_id'];
            }
            $refined [$response [$key] ['menu_price_id']] [$response [$key] ['addon_id']]['selection_type'] = $response[$key]['selection_type'];
            $refined [$response [$key] ['menu_price_id']] [$response [$key] ['addon_id']]['item_limit'] = $response[$key]['item_limit'];
            $refined [$response [$key] ['menu_price_id']] [$response [$key] ['addon_id']]['enable_pricing_beyond'] = $response[$key]['enable_pricing_beyond'];
            if ($response[$key]['enable_pricing_beyond'] != '') {
                $refined [$response [$key] ['menu_price_id']] [$response [$key] ['addon_id']]['item_limit'] = '';
            }
            if (!isset($refined [$response [$key] ['menu_price_id']] [$response [$key] ['addon_id']] ['options'] [$response[$key]['addon_main_id']])) {
                $refined [$response [$key] ['menu_price_id']] [$response [$key] ['addon_id']] ['options'] [$response[$key]['addon_main_id']] = array(
                    'id' => $response[$key]['addon_main_id'],
                    'name' => $response [$key] ['addon_option'],
                    'addon_description' => $response [$key] ['addon_description'],
                    'price' => $response [$key] ['addon_price'],
                    'description' => $response [$key] ['addon_price_description'],
                );
            }
        }
        $final = array();
        foreach ($refined as $key => $value) {
            foreach ($value as $subKey => $subValue) {
                $value[$subKey]['options'] = array_values($value[$subKey]['options']);
            }
            $final [] = array(
                'menu_price_id' => $key,
                'addons' => array_values($value)
            );
        }
            return $final;
    }
}
