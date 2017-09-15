<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\Menu;
use Restaurant\RestaurantDetailsFunctions;
use MCommons\Caching;
use Cuisine\Model\Cuisine;
use Restaurant\Model\Restaurant;
class MenuNewController extends AbstractRestfulController {
    /*
     * this function will get menu details of restaurant
     */
    public $offset=0;
    public $limit=500;
    public $compare=500;
    public function get($restaurant_id = 0) {
        $config = $this->getServiceLocator()->get('Config');
        $imageBaseUrl=$config['constants']['protocol'].'://'.$config['constants']['imagehost'].'munch_images/';
        $menuModel = new Menu ();
        $restaurantModel=new Restaurant();
        $restaurant=$restaurantModel->getAllRestaurant($this->offset,$this->limit);
        
        //write menu data in menu.xml
        
        $dom=new \DOMDocument();
        $menu=$dom->createElement("Menu");
        if(count($restaurant)> 0 && !empty($restaurant)){
            
        foreach($restaurant as $resKey=>$resVal){
            
        RestaurantDetailsFunctions::$_bookmark_types = $menuModel->bookmark_types;
        RestaurantDetailsFunctions::$_isMobile = $this->isMobile();
            $response = $menuModel->restaurantMenuesNew(array(
                    'columns' => array(
                        'restaurant_id' => $resVal['id']
                    )
                ))->toArray();
            if (!empty($response)) {
                $response = RestaurantDetailsFunctions::createWebNestedMenu($response, $restaurant_id);
                $response = RestaurantDetailsFunctions::knowLastLeaf($response);
                $response = RestaurantDetailsFunctions::formatResponse($response);
            } 
            if(!empty($response) && count($response)>0){
               $this->menuSpecificDeal($response,$restaurant_id,$imageBaseUrl,$dom,$menu);
            }
            if(count($restaurant)-1==$resKey){
                $this->offset=$this->compare+1;
                $this->compare=$this->compare+500;
            }
            
        }
        
        }
        $dom->appendChild($menu);

        $dom->save("./menu.xml");
    }

    public function menuSpecificDeal($response,$restaurant_id,$imageBaseUrl,$dom,$menu) { //pr($response,1);
                if(!empty($response) && count($response)>0){
                    $cou=0;
                    foreach($response as $main=>$m){
                        if(!empty($m['sub_categories']) && count($m['sub_categories'])>0){
                                foreach($m['sub_categories'] as $sub=>$subM){
                                    if (!empty($subM['category_items']) && count($subM['category_items']) > 0) {
                                        foreach ($subM['category_items'] as $itemKey => $item) { $cou++;
                                        $cuis=$this->getMenuCuisines($item['cuisines_id']);
                                        $resUrl=WEB_HOST_URL.'restaurants/'.$item['restaurant_name'].'/'.$restaurant_id.'/menu';
                                        $itemImage=($item['item_image_url']!='' && $item['item_image_url']!=NULL)?$imageBaseUrl.strtolower($item['rest_code']).'/'.$item['item_image_url']:'';
                                        
                                        $published=$dom->CreateElement("item");
                                        
                                        $res[$cou]['ID']=$dom->CreateElement("ID");
                                        $res[$cou]['Name']=$dom->CreateElement("Name");
                                        $res[$cou]['Description']=$dom->CreateElement("Description");
                                        $res[$cou]['Brand']=$dom->CreateElement("Brand");
                                        $res[$cou]['RestaurantName']=$dom->CreateElement("RestaurantName");
                                        $res[$cou]['Category']=$dom->CreateElement("Category");
                                        $res[$cou]['Subcategory1']=$dom->CreateElement("Subcategory1");
                                        $res[$cou]['RestaurantUrl']=$dom->CreateElement("RestaurantUrl");
                                        $res[$cou]['Price']=$dom->CreateElement("Price");
                                        $res[$cou]['GraphicsUrl']=$dom->CreateElement("GraphicsUrl");
                                        $res[$cou]['Cuisine']=$dom->CreateElement("Cuisine");
                                        $res[$cou]['ProductUrl']=$dom->CreateElement("ProductUrl");
                                        
                                        $item_id=$dom->createTextNode($item['item_id']);
                                        $item_name=$dom->createTextNode($item['item_name']);
                                        $item_desc=$dom->createTextNode($item['item_desc']);
                                        $brand=$dom->createTextNode('MunchAdo');
                                        $restaurant_name=$dom->createTextNode($item['restaurant_name']);
                                        $category_name=$dom->createTextNode($m['category_name']);
                                        $sub_category_name=$dom->createTextNode($subM['category_name']);
                                        $restaurantUrl=$dom->createTextNode($resUrl);
                                        $prc=(isset($item['prices'][0]['value']) && $item['prices'][0]['value'] >0)?$item['prices'][0]['value']:0;
                                        $price=$dom->createTextNode($prc);
                                        $image=$dom->createTextNode($itemImage);
                                        $cuisine=$dom->createTextNode($cuis);
                                        $productUrl=$dom->createTextNode('');
                                        
                                        
                                        $res[$cou]['ID']->appendChild($item_id);
                                        $res[$cou]['Name']->appendChild($item_name);
                                        $res[$cou]['Description']->appendChild($item_desc);
                                        $res[$cou]['Brand']->appendChild($brand);
                                        $res[$cou]['RestaurantName']->appendChild($restaurant_name);
                                        $res[$cou]['Category']->appendChild($category_name);
                                        $res[$cou]['Subcategory1']->appendChild($sub_category_name);
                                        $res[$cou]['RestaurantUrl']->appendChild($restaurantUrl);
                                        $res[$cou]['Price']->appendChild($price);
                                        $res[$cou]['GraphicsUrl']->appendChild($image);
                                        $res[$cou]['Cuisine']->appendChild($cuisine);
                                        $res[$cou]['ProductUrl']->appendChild($productUrl);
                                        
                                        $published->appendChild($res[$cou]['ID']);
                                        $published->appendChild($res[$cou]['Name']);
                                        $published->appendChild($res[$cou]['Description']);
                                        $published->appendChild($res[$cou]['Brand']);
                                        $published->appendChild($res[$cou]['RestaurantName']);
                                        $published->appendChild($res[$cou]['Category']);
                                        $published->appendChild($res[$cou]['Subcategory1']);
                                        $published->appendChild($res[$cou]['RestaurantUrl']);
                                        $published->appendChild($res[$cou]['Price']);
                                        $published->appendChild($res[$cou]['GraphicsUrl']);
                                        $published->appendChild($res[$cou]['Cuisine']);
                                        $published->appendChild($res[$cou]['ProductUrl']);
                                        
                                        $menu->appendChild($published);
                                        
//                                        $res[$cou]['ID']=$item['item_id'];     
//                                        $res[$cou]['Name']=$item['item_name'];
//                                        $res[$cou]['Description']=$item['item_desc'];
//                                        $res[$cou]['Brand']='MunchAdo';
//                                        $res[$cou]['RestaurantName']=$item['restaurant_name'];
//                                        $res[$cou]['Category']=$m['category_name'];
//                                        $res[$cou]['Subcategory1']=$subM['category_name'];
//                                        $res[$cou]['RestaurantUrl']=WEB_HOST_URL.'restaurants/'.$item['restaurant_name'].'/'.$restaurant_id.'/menu';
//                                        $res[$cou]['Price']=$item['prices'][0]['value'];
//                                        $res[$cou]['GraphicsUrl']=($item['item_image_url']!='' && $item['item_image_url']!=NULL)?$imageBaseUrl.strtolower($item['rest_code']).'/'.$item['item_image_url']:'';
//                                        $res[$cou]['Cuisine']=$this->getMenuCuisines($item['cuisines_id']);
//                                        $res[$cou]['ProductUrl']='';
                                        }
                                    }
                                }
                          
                        }
                        if (!empty($m['category_items']) && count($m['category_items']) > 0) {
                                        foreach ($m['category_items'] as $itemKeyM => $itemM) { $cou++;
                                        $cuis=$this->getMenuCuisines($itemM['cuisines_id']);
                                        $resUrl=WEB_HOST_URL.'restaurants/'.$itemM['restaurant_name'].'/'.$restaurant_id.'/menu';
                                        $itemImage=($itemM['item_image_url']!='' && $itemM['item_image_url']!=NULL)?$imageBaseUrl.strtolower($itemM['rest_code']).'/'.$itemM['item_image_url']:'';
                                        
                                        $published=$dom->CreateElement("item");
                                        
                                        $res[$cou]['ID']=$dom->CreateElement("ID");
                                        $res[$cou]['Name']=$dom->CreateElement("Name");
                                        $res[$cou]['Description']=$dom->CreateElement("Description");
                                        $res[$cou]['Brand']=$dom->CreateElement("Brand");
                                        $res[$cou]['RestaurantName']=$dom->CreateElement("RestaurantName");
                                        $res[$cou]['Category']=$dom->CreateElement("Category");
                                        $res[$cou]['Subcategory1']=$dom->CreateElement("Subcategory1");
                                        $res[$cou]['RestaurantUrl']=$dom->CreateElement("RestaurantUrl");
                                        $res[$cou]['Price']=$dom->CreateElement("Price");
                                        $res[$cou]['GraphicsUrl']=$dom->CreateElement("GraphicsUrl");
                                        $res[$cou]['Cuisine']=$dom->CreateElement("Cuisine");
                                        $res[$cou]['ProductUrl']=$dom->CreateElement("ProductUrl");
                                        
                                        $item_id=$dom->createTextNode($itemM['item_id']);
                                        $item_name=$dom->createTextNode($itemM['item_name']);
                                        $item_desc=$dom->createTextNode($itemM['item_desc']);
                                        $brand=$dom->createTextNode('MunchAdo');
                                        $restaurant_name=$dom->createTextNode($itemM['restaurant_name']);
                                        $category_name=$dom->createTextNode($m['category_name']);
                                        $sub_category_name=$dom->createTextNode('');
                                        $restaurantUrl=$dom->createTextNode($resUrl);
                                        $prc=(isset($itemM['prices'][0]['value']) && $itemM['prices'][0]['value'] >0)?$itemM['prices'][0]['value']:0;
                                        $price=$dom->createTextNode($prc);
                                        $image=$dom->createTextNode($itemImage);
                                        $cuisine=$dom->createTextNode($cuis);
                                        $productUrl=$dom->createTextNode('');
                                        
                                        
                                        $res[$cou]['ID']->appendChild($item_id);
                                        $res[$cou]['Name']->appendChild($item_name);
                                        $res[$cou]['Description']->appendChild($item_desc);
                                        $res[$cou]['Brand']->appendChild($brand);
                                        $res[$cou]['RestaurantName']->appendChild($restaurant_name);
                                        $res[$cou]['Category']->appendChild($category_name);
                                        $res[$cou]['Subcategory1']->appendChild($sub_category_name);
                                        $res[$cou]['RestaurantUrl']->appendChild($restaurantUrl);
                                        $res[$cou]['Price']->appendChild($price);
                                        $res[$cou]['GraphicsUrl']->appendChild($image);
                                        $res[$cou]['Cuisine']->appendChild($cuisine);
                                        $res[$cou]['ProductUrl']->appendChild($productUrl);
                                        
                                        $published->appendChild($res[$cou]['ID']);
                                        $published->appendChild($res[$cou]['Name']);
                                        $published->appendChild($res[$cou]['Description']);
                                        $published->appendChild($res[$cou]['Brand']);
                                        $published->appendChild($res[$cou]['RestaurantName']);
                                        $published->appendChild($res[$cou]['Category']);
                                        $published->appendChild($res[$cou]['Subcategory1']);
                                        $published->appendChild($res[$cou]['RestaurantUrl']);
                                        $published->appendChild($res[$cou]['Price']);
                                        $published->appendChild($res[$cou]['GraphicsUrl']);
                                        $published->appendChild($res[$cou]['Cuisine']);
                                        $published->appendChild($res[$cou]['ProductUrl']);
                                        
                                        $menu->appendChild($published);
                                        
//                                        $res[$cou]['ID']=$itemM['item_id'];     
//                                        $res[$cou]['Name']=$itemM['item_name'];
//                                        $res[$cou]['Description']=$itemM['item_desc'];
//                                        $res[$cou]['Brand']='MunchAdo';
//                                        $res[$cou]['RestaurantName']=$itemM['restaurant_name'];
//                                        $res[$cou]['Category']=$m['category_name'];
//                                        $res[$cou]['Subcategory1']='';
//                                        $res[$cou]['RestaurantUrl']=WEB_HOST_URL.'restaurants/'.$itemM['restaurant_name'].'/'.$restaurant_id.'/menu';
//                                        $res[$cou]['Price']=$itemM['prices'][0]['value'];
//                                        $res[$cou]['GraphicsUrl']=($itemM['item_image_url']!='' && $itemM['item_image_url']!=NULL)?$imageBaseUrl.strtolower($itemM['rest_code']).'/'.$itemM['item_image_url']:'';
//                                        $res[$cou]['Cuisine']=$this->getMenuCuisines($itemM['cuisines_id']);
//                                        $res[$cou]['ProductUrl']='';
                                        
                                        }
                                    }
                    }
                }
                //return $res;
}

public function getMenuCuisines($cuisinesId=false){
    $cuisines='';
    
    if(isset($cuisinesId) && !empty($cuisinesId) && $cuisinesId!=''){
    $CuisineModel=new Cuisine();
    $expCuis=explode(',',$cuisinesId);
    if(count($expCuis) > 0 && $expCuis!=''){
        $count=0;
        foreach($expCuis as $key=>$valCuis){
            if($valCuis!=''){ $count++;
                if($count>1){
                   $cuisines.=','.$CuisineModel->getCuisine($valCuis); 
                }else{
                    $cuisines.=$CuisineModel->getCuisine($valCuis); 
                }
                
            }
        }
    }
    return $cuisines;
    }else{
    return $cuisines;    
    }
}

}
