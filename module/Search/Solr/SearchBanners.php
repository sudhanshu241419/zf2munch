<?php

namespace Solr;

use Solr\SearchUrlsMobile;
use Solr\SearchHelpers;
use MCommons\StaticOptions;
use Search\SearchFunctions;
use Restaurant\Model\RestaurantReview;
use Restaurant\Model\Menu;

/**
 * Description of SearchBanners
 *
 * @author arti
 */
class SearchBanners {

    const RES_DEFAULT_GALLERY_IMG = 'https://dc8l3mwto1qll.cloudfront.net/assets/img/gallery/no_image.jpg';
    const TAGGED_PAGE_SIZE = 10;
    const RETARGET_PAGE_SIZE = 10;
    const BANNERS_QUERIE_FQ = '&fq=(cuisine_fct:"burgers"+OR+cuisine_fct:"pizza"+OR+cuisine_fct:"sushi"+OR+cuisine_fct:"organic"+OR+cuisine_fct:"vegan"+OR+feature_fct:"cafe%20/%20coffee%20house"+OR+feature_fct:"sports%20bar"+OR+feature_fct:"bakery"+OR+feature_fct:"juice%20bar"+OR+feature_fct:"open%2024%20hours"+OR+feature_fct:"happy%20hour"+OR+feature_fct:"open%20late"+OR+feature_fct:"good%20for%20date"+OR+feature_fct:"wifi"+OR+r_price_num:1)';

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

    public function getTaggedData($req) {
        $retData = SearchHelpers::getDefaultApiResponseArr();
        $latlong = SearchHelpers::getLatlong($req['reqval']);
        $at = is_numeric($req['reqval']) ? 'exactzip' : 'exactnbd';
        // if q is set 5 results else 1 result each category.
        $rows = isset($req['q']) ? 5 : 1;

        $rawRequest = [
            'reqtype' => 'search',
            'city_id' => 18848,
            'at' => $at,
            'av' => $req['reqval'],
            'latlong' => $latlong['lat'] . ',' . $latlong['lng'], //'40.74407,-73.98522',
            'start' => 0,
            'rows' => $rows,
        ];
        $searchRequest = SearchFunctions::cleanMobileSearchParams($rawRequest);
        $sum = new SearchUrlsMobile();
        $unescapedurl = $sum->getDiscoverUrl($searchRequest);
        if (isset($req['signedup']) && $req['signedup'] == '1') {
            $unescapedurl .= '&fq=is_registered:1';
        } else { // pull signed up followed by other restaurants
            $unescapedurl .= '&sort=is_registered+desc,score+desc';
        }
        // key for collecting number of registered restaurants for this query.
        $unescapedurl .= '&facet=on&facet.query={!key=registered_count}is_registered:1';

        $restaurants = [];
        switch ($rows) {
            case 1:
                $helper = [];
                $tempDocs = [];
                $i = 1;
                foreach (self::$bannerQueries as $query => $filter) {
                    $rests = $this->getQueryRestaurants($unescapedurl . $filter, $query);
                    if ($rests) {
                        $helper[$i] = $rests['registered_count'];
                        $tempDocs[$i] = $rests['docs'][0];
                        $i++;
                    }
                }
                arsort($helper);
                foreach($helper as $i => $count){
                    $restaurants[] = $tempDocs[$i];
                }
                break;
            case 5:
                if(!isset(self::$bannerQueries[$req['q']])){
                    throw new \Exception('Illegal q .', 400);
                }
                $rests = $this->getQueryRestaurants($unescapedurl . self::$bannerQueries[$req['q']], $req['q']);
                if ($rests) {
                    $restaurants = $rests['docs'];
                }
                break;
            default:
                break;
        }
        $retData['data'] = $restaurants;
        
        return $retData;
    }

    private function getQueryRestaurants($url, $query) {
        $result = false;
        $output = SearchHelpers::getCurlUrlData($url);
        if ($output['status_code'] == 200) {
            $responseArr = json_decode($output['data'], true);
            if ($responseArr['response']['numFound'] > 0) {
                $result['registered_count'] = $responseArr['facet_counts']['facet_queries']['registered_count'];
                $docs = $responseArr['response']['docs'];
                foreach ($docs as $i => $doc) {
                    $this->updateTaggedDoc($doc, $query);
                    $doc['query_signedup_rest_count'] = $result['registered_count'];
                    $docs[$i] = $doc;
                }
                $result['docs'] = $docs;
            }
        }
        return $result;
    }

    private function updateTaggedDoc(&$doc, $q) {
        $image = preg_replace('/[-\s]+/', '_', strtolower($q)) . '.png';
        $doc['signup'] = $doc['is_registered'];
        $doc['query'] = $q;
        $doc['image'] = $image;
        $doc['image_sm'] = 'sm_' . $image;
        $doc['image_lg'] = 'lg_' . $image;
        if ($doc['res_primary_image'] != '') {
            $doc['image_path'] = SearchEtc::IMG_BASE_URL . strtolower($doc['res_code']) . '/' . $doc['res_primary_image'];
        } elseif ($doc['gallery_count'] > 0) {
            $doc['image_path'] = SearchEtc::IMG_BASE_URL . strtolower($doc['res_code']) . '/' . $doc['galleries'][0];
        } else {
            $doc['image_path'] = self::RES_DEFAULT_GALLERY_IMG;
        }
    }

    /**
     * Serves API: /wapi/search/banners?reqtype=retarget&reqval=10023&page=0&lat=40.7769059&lng=-73.980064&token=82e3ee130acf8a89b5de544d511f35a7
     * use lat & lng parameters instead of reqval
     * @param array $req desired paramters {lat, lng, order_type, at, reqval, count, page, signedup}
     * @return array
     */
    public function getRetargetData($req) {
        //pr($req,1);
        $retData = SearchHelpers::getDefaultApiResponseArr();
        $latlong = ['lat' => 0, 'lng' => 0];
        if(isset($req['lat']) && isset($req['lng']) && is_numeric($req['lat']) && is_numeric($req['lng']) ){
            $latlong['lat'] = $req['lat'];
            $latlong['lng'] = $req['lng'];
            $req['at'] = 'street';
        } elseif (isset($req['reqval'])) {
             $latlong = SearchHelpers::getLatlong($req['reqval']);
        }
        
        if(isset($req['at']) && $req['at'] == 'street' ){
            $at = 'street';//handle reqval = 'E 100th St, New York, NY 10029, USA';
        } else {
            $at = is_numeric($req['reqval']) ? 'exactzip' : 'exactnbd';
        }
        
        $rows = isset($req['page_size']) && ($req['page_size'] > 0) ? intval($req['page_size']) : self::RETARGET_PAGE_SIZE;
        $page = isset($req['page']) && ($req['page'] > 0) ? intval($req['page']) : 1;
        $start = ($page - 1) * $rows;
        
        $rawRequest = [
            'reqtype' => 'search',
            'city_id' => 18848,
            'at' => $at,
            'q' => isset($req['q']) ? $req['q'] : '',
            'dt' => 'ft',
            'av' => $req['reqval'],
            'latlong' => $latlong['lat'] . ',' . $latlong['lng'], //'40.74407,-73.98522',
            'start' => $start,
            'rows' => $rows,
        ];
        $searchRequest = SearchFunctions::cleanMobileSearchParams($rawRequest);
        
        //Request created on 11-Sep-2016
        //2. I am adding order_type=”delivery/takeout” parameter into URL so that I can get all restaurant which 
        //delivering/takeout nearby that Lat/long apart from the one you tried in the last order.
        //3. I am also adding “restId=60001” into URL so that we will know which restaurant need to exclude from API.
        $tab = 'all'; // all, delivery, takeout, reservation,dinein
        $sortBy = '';
        if (isset($req['order_type'])) {
            //@todo
            if($req['order_type'] == 'delivery'){
                $tab = 'delivery';
                $sortBy = '&sort=geodist()+asc';
            } elseif ($req['order_type'] == 'takeout'){
                $tab = 'takeout';
                $sortBy = '&sort=geodist()+asc';
            }
        }
        $sum = new SearchUrlsMobile();
        if($tab == 'delivery'){
            $unescapedurl = $sum->getDeliverUrl($searchRequest);
        } else if($tab == 'takeout'){
            $unescapedurl = $sum->getTakeoutUrl($searchRequest);
        } else {
           $unescapedurl = $sum->getDiscoverUrl($searchRequest); 
        }
        $unescapedurl .= $sortBy;
        
        if (isset($req['signedup']) && $req['signedup'] == '1') {
            $unescapedurl .= '&fq=is_registered:1';
        }
        
        if (isset($req['restId'])) {
            $unescapedurl .= '&fq=-res_id:"'.  urlencode($req['restId']) . '"';
        }
        
        
        $output = SearchHelpers::getCurlUrlData($unescapedurl);
        if ($output['status_code'] == 200) {
            $responseArr = json_decode($output['data'], true);
            $retData['data'] = $responseArr['response']['docs'];
            $image = preg_replace('/[-\s]+/', '_', strtolower($searchRequest['q'])) . '.png';
            foreach ($retData['data'] as $i => $doc) {
                $this->updateRetargetDoc($doc, $searchRequest['q'], $image);
                $retData['data'][$i] = $doc;
            }
            $numFound = $responseArr['response']['numFound'];
            $retData['total'] = $numFound;
            $retData['page_count'] = ceil($numFound / $rows);
        } else {
            $responseArr = isset($output['data']) ? json_decode($output['data'], true) : [];
            $error = isset($responseArr['error']) ? $responseArr['error']['msg'] : 'Unknown error.';
            SearchHelpers::updateBadApiResponseArr($retData, $error);
        }
        if (isset($req['DeBuG'])) {
            $retData['url'] = $unescapedurl . '&echoParams=explicit';
            $retData['params_used'] = $searchRequest;
        }
        return $retData;
    }

    private function updateRetargetDoc(&$doc, $q, $image) {
        $doc['query'] = $q;
        $doc['image'] = $image;
        $doc['image_sm'] = 'sm_' . $image;
        $doc['image_lg'] = 'lg_' . $image;
        if ($doc['res_primary_image'] != '') {
           $doc['image_path'] = SearchEtc::IMG_BASE_URL . strtolower($doc['res_code']) . '/' . $doc['res_primary_image']; 
        } elseif ($doc['gallery_count'] > 0) {
            $doc['image_path'] = SearchEtc::IMG_BASE_URL . strtolower($doc['res_code']) . '/' . $doc['galleries'][0];
        }  else {
            $doc['image_path'] = self::RES_DEFAULT_GALLERY_IMG;
        }
    }

    /**
     * Restaurant gallery api for banners
     * GET /wapi/search/banners?reqtype=gallery&reqval=60001&token=82e3ee130acf8a89b5de544d511f35a7
     * reqval is restaurant id
     * @param int $res_id
     * @return array gallery data
     */
    public function getGallaryData($res_id) {
        $retData = SearchHelpers::getDefaultApiResponseArr();
        $solr_url = StaticOptions::getSolrUrl();
        $req_url = $solr_url . 'hbr/select?fl=res_name,res_code,galleries&fq=res_id:' . $res_id;
        $output = SearchHelpers::getCurlUrlData($req_url);
        if ($output['status_code'] == 200) {
            $responseArr = json_decode($output['data'], true);
            if ($responseArr['response']['numFound'] > 0) {
                $retData['data'] = $responseArr['response']['docs'][0];
                $img_prefix = SearchEtc::IMG_BASE_URL . strtolower($retData['data']['res_code']) . '/';
                unset($retData['data']['res_code']);
                $retData['data']['image_path'] = $img_prefix;
            }
        } else {
            $responseArr = isset($output['data']) ? json_decode($output['data'], true) : [];
            $error = isset($responseArr['error']) ? $responseArr['error']['msg'] : 'Unknown error.';
            SearchHelpers::updateBadApiResponseArr($retData, $error);
        }
        $rr = new RestaurantReview();
        $reviewAndReviewer = $rr->getRestaurantPositiveReview($res_id);
        $reviews = [];
        $reviewers = [];
        foreach ($reviewAndReviewer as $row) {
            $reviews[] = $row['reviews'];
            $reviewers[] = $row['reviewer'];
        }
        $retData['data']['rest_review_comment'] = $reviews;
        $retData['data']['rest_reviewer_name'] = $reviewers;
        
        $resMenus = new Menu();
        $dishes = $resMenus->getRestaurantDishImages($res_id);
        $dishNames = [];
        $dishImages = [];
        foreach ($dishes as $dish) {
            $dishNames[] = $dish['dish_name'];
            $dishImages[] = $dish['dish_image'];
        }
        
        $retData['data']['rest_dish_img'] = $dishImages;//@todo max 10
        $retData['data']['rest_dish_name'] = $dishNames;//@todo max 10
        return $retData;
    }

}
