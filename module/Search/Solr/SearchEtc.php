<?php

namespace Solr;

use Solr\SearchUrlsMobile;
use Solr\SearchHelpers;

/**
 * Description of SearchEtc
 *
 * @author dhirendra
 */
class SearchEtc {
    
/*
earch queries				closest tags		Description/ tagging type

6. Burger Joints			Burger				Favorites
1. Pizzerias				Pizza				Favorites
10. Sublime Sushi			Sushi				Favorites
  
2. Organic Restaurants		Organic				Preferences
3. Vegan Spots				vegan				Preferences

5. Cafés					Café				Type of Place
9. Sports Bars				Sports Bars			Type of place
14. Tasty Bakery			Bakery				Type of Place
15. Juice Bars				Juice Bar			Type of Place

12. 24 Hour Spots			Open 24 hours		Features
4. Happy Hour Drinks		Happy Hour			Features (It is not drink specific )
11. Late Night Eats			Open late			Features
16. Prix-Fixe Menus			Prix-Fixe			Features
7. Romantic Eateries		Romantic			Features
13. Wi-Fi Enabled			Wifi				Features
 
8. Budget Friendly Eats		$					Restaurant Price

*/
 
    public static  $bannerOriginalOrder = array(
        1 => 'Pizzerias',
        2 => 'Organic',
        3 => 'Vegan Spots',
        4 => 'Happy Hour Drinks',
        5 => 'Cafe',
        6 => 'Burger Joints',
        7 => 'Romantic',
        8 => 'Budget Friendly Eats',
        9 => 'Sports Bars',
        10 => 'Sublime Sushi',
        11 => 'Late Night Eats',
        12 => '24 Hour Spots',
        13 => 'Wi-Fi Enabled',
        14 => 'Tasty Bakery',
        15 => 'Juice Bars'
    );
    
    public static $bannerQueries = array(
        'Burger Joints' => '&fq=cuisine_fct:"burgers"',
        'Pizzerias' => '&fq=cuisine_fct:"pizza"',
        'Sublime Sushi' => '&fq=cuisine_fct:"sushi"',
        'Organic' => '&fq=cuisine_fct:"organic"',
        'Vegan Spots' => '&fq=cuisine_fct:"vegan"',
        'Cafe' => '&fq=feature_fct:"cafe%20/%20coffee%20house"',
        'Sports Bars' => '&fq=feature_fct:"sports%20bar"',
        'Tasty Bakery' => '&fq=feature_fct:"bakery"',
        'Juice Bars' => '&fq=feature_fct:"juice%20bar"',
        '24 Hour Spots' => '&fq=feature_fct:"open%2024%20hours"',
        'Happy Hour Drinks' => '&fq=feature_fct:"happy%20hour"',
        'Late Night Eats' => '&fq=feature_fct:"open%20late"',
        'Romantic' => '&fq=feature_fct:"good%20for%20date"',
        'Wi-Fi Enabled' => '&fq=feature_fct:"wifi"',
        'Budget Friendly Eats' => '&fq=r_price_num:1'
    );

    const IMG_BASE_URL = 'https://dc8l3mwto1qll.cloudfront.net/assets/munch_images/';

    public function getFeaturedData($request){
        $response['status'] = 'success';
        $sum = new SearchUrlsMobile();
        $unescapedurl = $sum->getDiscoverUrl($request);
        foreach (self::$bannerQueries as $q => $fq){
            $url = $unescapedurl . $fq;
            $image = preg_replace('/[-\s]+/', '_', strtolower($q)).'.png';
            $docs[] = $this->getData($url, $q, $image, 1);
        }
        $response['data'] = $docs;
        return $response;
    }


    public function getTaggedData($request, $tag){
        $response['status'] = 'success';
        if(!isset(self::$bannerQueries[$tag])){
            return array('status' => 'fail', 'data' => [], 'error' => 'invlid tag');
        }
        $sum = new SearchUrlsMobile();
        $unescapedurl = $sum->getDiscoverUrl($request);
        $url = $unescapedurl . self::$bannerQueries[$tag];
        $image = preg_replace('/[-\s]+/', '_', strtolower($tag)).'.png';
        $response['data'] = $this->getData($url, $tag, $image, 5);
        return $response;
    }
    
    private function getData($url, $q, $image, $count = 1){
        $url = preg_replace('/&fq={!geofilt}&d=1.6/', '', $url);//comment this line in future
        $output = SearchHelpers::getCurlUrlData($url);
        if($output['status_code'] != 200){
           return array();
        }
        $jsonData = json_decode($output['data'], true);
        
        // return only 1 doc for featured api
        if ($count == 1) {
            if ($jsonData['response']['numFound'] > 0) {
                $doc = $jsonData['response']['docs'][0];
                $this->updateDoc($doc, $q, $image);
                return $doc;
            } else {
                return [];
            }
        }

        //for tagged api return 5 results
        $response = [];
        foreach ($jsonData['response']['docs'] as $i => $doc){
            $this->updateDoc($doc, $q, $image);
            $response[$i] = $doc;
        }        
        return $response;
    }

    private function updateDoc(&$doc, $q, $image){
        $doc['query'] = $q;
        $doc['image'] = $image;
        $doc['image_sm'] = 'sm_' . $image;
        $doc['image_lg'] = 'lg_' . $image;
        $doc['image_path'] = self::IMG_BASE_URL.  strtolower($doc['res_code'].'/' . $doc['res_primary_image']);
    }
}
