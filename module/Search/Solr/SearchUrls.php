<?php

/**
 * Description of SearchUrls
 *
 * @author dhirendra
 */

namespace Solr;

use MCommons\StaticOptions;
use Solr\SearchHelpers;
use Solr\Common\AtAvFq;

class SearchUrls {
    
    /**
     *
     * @var Solr\Common\AtAvFq 
     */
    public $atavfq;
    
    /**
     * open view type
     * @var type 
     */
    public $view_type = 'restaurant';


    /** selected day {mo,tu,we,th,fr,sa,su}
     * @var string */
    public $day;
    
    /** order time (between 0 and 2359, 24 hours format)
     * @var integer */
    public $time;
    
    /** order time (between 0 and 2359, 24 hours format)
     * @var string */
    public $sort_by;
    
    /** order time (between 0 and 2359, 24 hours format)
     * @var string */
    public $sort_type;
    
    /**main q, covers free text, food, restaurant
     * @var string */
    public $search_q; 
    
    /**main q for serving ads
     * @var string */
    public $search_q_ads; 
    
    /**keyword from which ads were triggered
     * @var string */
    public $ad_keyword; 
    
    /** cuisines filters
     * @var string */
    public $search_cui;
    
    /**  type of place filters
     * @var string */
    public $search_top;
    
    /** price filter e.g. fq=r_price_num:[*+TO+4]
     * @var string */
    public $price_fq;
    
    /**
     * deals filter e.g. fq=has_deals:1
     * @var string
     */
    public $deals_fq = '';
    
    /**
     * covers 'aor' and 'acp' filters
     * @var string
     */
    public $etc_fq = '';
     
    /**
     * e.g. http://localhost:8983/solr/
     * @var string
     */
    public $solr_url = '';
    
    /**
     * solr res core search handler http://localhost:8983/solr/hbr/hbsearch?
     * @var string 
     */
    public $res_url = '';
    
    /**
     * solr food core search handler http://localhost:8983/solr/hbm/hbsearch?
     * @var string
     */
    public $food_url = '';
    
    /**
     * http://localhost:8983/solr/hbm/select?
     * @var type 
     */
    public $food_select_url = '';
    
    /**
     * http://localhost:8983/solr/hbr/hbauto?
     * @var string
     */
    public $ac_res_url = '';
    
    /**
     * http://localhost:8983/solr/hbm/hbauto?
     * @var string
     */
    public $ac_food_url = '';
    
    public static $cuisine_match_arr = array('cui', 'fav', 'pref');
    public static $feature_match_arr = array('amb', 'feat', 'top');

    function __construct() {
        $this->solr_url = StaticOptions::getSolrUrl();
        $this->res_url = $this->solr_url . "hbr/hbsearch?";
        $this->food_url = $this->solr_url . 'hbm/hbsearch?';
        $this->food_select_url = $this->solr_url . 'hbm/select?';
        $this->ac_res_url = $this->solr_url . 'hbr/hbauto?';
        $this->ac_food_url = $this->solr_url . 'hbm/hbauto?';
    }

    /**
     * Sets class variables like day, time, search_q, search_cui,search_top etc.
     * @param array $req
     */
    public function setClassVariables($req) {
        $this->atavfq = new AtAvFq($req, 'web');
        $this->day = $req['day'];
        $this->time = (int) $req['stime'];
        $this->view_type = $req['ovt'];
        $this->sort_by = $req['sort_by'];
        $this->sort_type = $req['sort_type'];
        $this->setQueryCuisinePlaces($req);
        $this->setQueryForAds($req);
        if ($req['price'] > 0) {
            $this->price_fq = SearchHelpers::getPriceFqFilter((int)$req['price']);
        }
        if ($req['deals'] > 0) {
            $this->deals_fq = SearchHelpers::getSolrDealsFq($req['sst']);
        }
        
        //aor=1 if Accept Online Orders
        if($req['aor'] > 0){
            $this->etc_fq .= '&fq=ordering_enabled:1';
        }
        
        //acp =1 if registered restaurants or with some tag
        if($req['acp'] > 0){
            //$this->etc_fq .= '&fq=(is_registered:1+OR+tags_fct:[*+TO+*])';
            $this->etc_fq .= '&fq=(is_registered:1)';
        }
    }

    private function getCommonUrl($req){
        $this->setClassVariables($req);
        if ($this->view_type == 'restaurant') {
            $baseUrl = $this->res_url;
        } else {
            $baseUrl = $this->food_url;
        }
        $solr_sv = 'start=' . $req['start'] . '&rows=' . $req['rows'] . '&pt=' . $this->atavfq->latlong;
        $solr_sv .= SearchHelpers::getSortByFilter($this->sort_by, $this->sort_type, $this->view_type, $req['at']); //sort by filter
        $query = $this->search_q .$this->search_cui . $this->search_top;
        return ($req['is_registered']==1)?$baseUrl . $solr_sv . $query."&fq=is_registered:1":$baseUrl . $solr_sv . $query;
    }

    private function getCommonUrlAds($req){
        $this->setClassVariables($req);
        if ($this->view_type == 'restaurant') {
            $baseUrl = $this->res_url;
        } else {
            $baseUrl = $this->food_url;
        }
        $solr_sv = 'fl=res_id,res_name,res_code&start=' . $req['start'] . '&rows=' . $req['rows'] . '&pt=' . $this->atavfq->latlong;
        $solr_sv .= SearchHelpers::getSortByFilter($this->sort_by, $this->sort_type, $this->view_type, $req['at']); //sort by filter
        $query = $this->search_q_ads . $this->search_cui . $this->search_top;
        return $baseUrl . $solr_sv . $query;
    }
    
    public function getDiscoverUrl($req) {
        $commonUrl = $this->getCommonUrl($req);
        $solrFq = $this->getDiscoverFqFilters($req); //all fq filters
        return $commonUrl.$solrFq;
    }
    
    public function getDiscoverUrlForAds($req) {
        $commonUrl = $this->getCommonUrlAds($req);
        $solrFq = $this->getDiscoverFqFilters($req); //all fq filters
        return $commonUrl.$solrFq;
    }
    
    public function getDeliverUrl($req) {
        $commonUrl = $this->getCommonUrl($req);
        $solrFq = $this->getDeliverFqFilters($req); //all fq filters
        return $commonUrl.$solrFq;
    }
    
    public function getDeliverUrlForAds($req) {
        $commonUrl = $this->getCommonUrlAds($req);
        $solrFq = $this->getDeliverFqFilters($req); //all fq filters
        return $commonUrl.$solrFq;
    }

    public function getTakeoutUrl($req) {
        $commonUrl = $this->getCommonUrl($req);
        $solrFq = $this->getTakeoutFqFilters($req); //all fq filters
        return $commonUrl.$solrFq;
    }

    public function getTakeoutUrlForAds($req) {
        $commonUrl = $this->getCommonUrlAds($req);
        $solrFq = $this->getTakeoutFqFilters($req); //all fq filters
        return $commonUrl.$solrFq;
    }

    public function getDineinUrl($req) {
        $commonUrl = $this->getCommonUrl($req);
        $solrFq = $this->getDineinFqFilters($req); //all fq filters
        return $commonUrl.$solrFq;;
    }

    public function getDineinUrlForAds($req) {
        $commonUrl = $this->getCommonUrlAds($req);
        $solrFq = $this->getDineinFqFilters($req); //all fq filters
        return $commonUrl.$solrFq;
    }

    public function getReservationUrl($req) {
        $commonUrl = $this->getCommonUrl($req);
        $solrFq = $this->getReserveFqFilters($req); //all fq filters
        return $commonUrl.$solrFq;
    }

    public function getReservationUrlForAds($req) {
        $commonUrl = $this->getCommonUrl($req);
        $solrFq = $this->getReserveFqFilters($req); //all fq filters
        return $commonUrl.$solrFq;
    }

    public function getSeoUrl($req) {
        //print_r($params);die;
        $baseUrl = $this->res_url;
        $start = isset($req['start']) ? $req['start'] : 0;
        $solr_sv = 'start=' . $start;
        $rows = isset($req['rows']) ? $req['rows'] : 100;
        $solr_sv .= '&rows=' . $rows;
        $solr_sv .= '&pt=' . $req['lat'] . ',' . $req['lng'];
        $solrFq = '&fq=city_id:' . $req['city_id'];
        return $baseUrl . $solr_sv . $solrFq;
    }
    
    //======================= END MAIN SEARCH DATA ==========================

    //================== FQ FILTERS ==================

    private function getCommonFqFilters($req){
        $commonFq = '';
        if ($this->view_type == 'food') {//donot show menu with zero price
            $commonFq .= '&fq=!(menu_price_num:0)';
        }
        $commonFq .= $this->price_fq; //price filter
        $commonFq .= $this->deals_fq; //deals filter
        $commonFq .= $this->etc_fq; //etc filter
        return $commonFq;
    }
    
    private function getDiscoverFqFilters($req) {
        $solrfq = $this->getCommonFqFilters($req);
        $solrfq .= SearchHelpers::$discover_global_fq;
        $solrfq .= $this->atavfq->getDiscoverAtAvFq($req);
        if( isset($req['oh']) && ( $req['oh'] == 'oa' || $req['oh'] == 'on' ) )
        {
            $solrfq .= SearchHelpers::getDiscoverTimeFq($this->day,  $this->time);//time filter
        }
        
        return $solrfq;
    }
    
    private function getDeliverFqFilters($req) {
        $solrfq = $this->getCommonFqFilters($req);
        $solrfq .= SearchHelpers::$deliver_global_fq;
        $solrfq .= $this->atavfq->getDeliverAtAvFq();
        $solrfq .= SearchHelpers::getDeliverTimeFq($this->day,  $this->time); //time filter
        return $solrfq;
    }

    private function getTakeoutFqFilters($req) {
        $solrfq = $this->getCommonFqFilters($req);
        $solrfq .= SearchHelpers::$takeout_global_fq;
        $solrfq .= $this->atavfq->getTakeoutAtAvFq($req);
        $solrfq .= SearchHelpers::getTakeoutTimeFq($this->day,  $this->time); //time filter
        return $solrfq;
    }

    private function getDineinFqFilters($req) {
        $solrfq = $this->getCommonFqFilters($req);
        $solrfq .= SearchHelpers::$dinein_global_fq;
        $solrfq .= $this->atavfq->getDineinAtAvFq();
        $solrfq .= SearchHelpers::getMealsFq($req['rrt']);
        $solrfq .= SearchHelpers::getDineinTimeFq($this->day);
        return $solrfq;
    }

    private function getReserveFqFilters($req) {
        $solrfq = $this->getCommonFqFilters($req);
        $solrfq .= SearchHelpers::$reserve_global_fq;
        $solrfq .= $this->atavfq->getReserveAtAvFq($req); //address_type, address_value filter
        //search_reservationtype
        $solrfq .= SearchHelpers::getMealsFq($req['rrt']);
        $solrfq .= SearchHelpers::getReservationTimeFq($this->day);
        return $solrfq;
    }

    private function setQueryCuisinePlaces($req) {
        //set main search query q
        if ($req['sq'] != '' && $req['sdt'] != '') {
            $this->search_q = $this->setSolrQ($req);
        }

        //set solr fq's
        $fqArr = explode('||', html_entity_decode($req['fq']));
        $fdtArr = explode('||', $req['fdt']);
        //print_r($dtArr);die;
        if (count($fqArr) != count($fdtArr)) {
            exit("incorrect fq fdt");
        }

        $search_cui = '';
        $search_top = '';
        
        $n = count($fqArr);
        $q_helper = array();
        for ($i = 0; $i < $n; $i++) 
        {
            if (in_array($fdtArr[$i], self::$cuisine_match_arr)) 
            {
                $q_helper['cuisine'][] = '"' . rawurlencode($fqArr[$i]) . '"';
            } 
            elseif (in_array($fdtArr[$i], self::$feature_match_arr)) 
            {
                $q_helper['feature'][] = '"' . rawurlencode($fqArr[$i]) . '"';
            }
        }

        if (isset($q_helper['feature'])) 
        {
            $search_top .= '&fq=feature_fct:(' . implode('+OR+', $q_helper['feature']) . ')';
        }

        if (isset($q_helper['cuisine'])) {
            if ($this->view_type == 'restaurant') 
            {
                $search_cui .= '&fq=cuisine_fct:(' . implode('+OR+', $q_helper['cuisine']) . ')';
            } 
            elseif ($this->view_type == 'food') 
            {
                $search_cui .= '&fq=menu_cuisine_fct:(' . implode('+OR+', $q_helper['cuisine']) . ')';
            }
        }
        
        $this->search_cui = $search_cui;
        $this->search_top = $search_top;
    }
    
    private function setQueryForAds($req) {
        if($req['sq'] != ''){
            $q = $req['sq'];
        } else {
            $q = $req['fq'];
        }
        $this->ad_keyword = rawurlencode(html_entity_decode($q));
        $this->search_q_ads = '&q=ad_keywords_fct:"'.$this->ad_keyword.'"';
    }

    //======================== PICK AN AREA =================================
    
    public function getPickAnAreaFq($req) {
        $this->setClassVariables($req);
        switch ($req ['sst']) {
            case 'all' :
                $fq = $this->getPaaDiscoverFq($req);
                break;
            case 'deliver' :
                $fq = $this->getPaaDeliverFq($req);
                break;
            case 'takeout' :
                $fq = $this->getPaaTakeoutFq($req);
                break;
            case 'dinein' :
                $fq = $this->getPaaDineinFq($req);
                break;
            case 'reservation' :
                $fq = $this->getPaaReserveFq($req);
                break;
        }
        return $fq;
    }

    private function getPaaCommonFq($req){
        $solrfq = $this->search_q . $this->search_cui . $this->search_top;
        $solrfq .= '&fq=city_id:' . $req['city_id'];
        $solrfq .= $this->price_fq;
        $solrfq .= $this->deals_fq; //deals filter
        $solrfq .= $this->etc_fq; //etc filter
        return $solrfq;
    }
    
    private function getPaaDiscoverFq($req) {
        $solrfq = $this->getPaaCommonFq($req);
        $solrfq .= SearchHelpers::$discover_global_fq;
        //$solrfq .= SearchHelpers::getDiscoverTimeFq();
        return $solrfq;
    }

    private function getPaaDeliverFq($req) {
        $solrfq = $this->getPaaCommonFq($req);
        $solrfq .= SearchHelpers::$deliver_global_fq;
        //$solrfq .= $this->getDeliverAtAvFq();
        $solrfq .= SearchHelpers::getDeliverTimeFq($this->day,  $this->time);
        return $solrfq;
    }

    private function getPaaTakeoutFq($req) {
        $solrfq = $this->getPaaCommonFq($req);
        $solrfq .= SearchHelpers::$takeout_global_fq;
        //$solrfq .= $this->getTakeoutAtAvFq();
        $solrfq .= SearchHelpers::getTakeoutTimeFq($this->day,  $this->time);
        return $solrfq;
    }

    private function getPaaDineinFq($req) {
        $solrfq = $this->getPaaCommonFq($req);
        $solrfq .= SearchHelpers::$dinein_global_fq;
        //$solrfq .= $this->getDineinAtAvFq();
        //search_reservationtype
        $solrfq .= SearchHelpers::getMealsFq($req['rrt']);
        $solrfq .= SearchHelpers::getDineinTimeFq($this->day);
        return $solrfq;
    }

    private function getPaaReserveFq($req) {
        $solrfq = $this->getPaaCommonFq($req);
        $solrfq .= SearchHelpers::$reserve_global_fq;
        //$solrfq .= $this->getReserveAtAvFq();
        //search_reservationtype
        $solrfq .= SearchHelpers::getMealsFq($req['rrt']);
        $solrfq .= SearchHelpers::getReservationTimeFq($this->day);
        return $solrfq;
    }

    //======================= FACET DATA ===============================
    
    public function getFacetData($req) {
        $this->setClassVariables($req);
        $url = '';
        switch ($req['sst']) {
            case 'deliver':
                $url = $this->getFacetDeliverUrl($req);
                break;
            case 'takeout':
                $url = $this->getFacetTakeoutUrl($req);
                break;
            case 'dinein':
                $url = $this->getFacetDineinUrl($req);
                break;
            case 'reservation':
                $url = $this->getFacetReservationUrl($req);
                break;
            case 'all':
                $url = $this->getFacetDiscoverUrl($req);
                break;
        }
        return array('ovt' => $this->view_type, 'url' => $url, 'cui' => $this->search_cui, 'top' => $this->search_top);
    }

    private function getFacetCommonUrl($params){
        if ($params['ovt'] == 'food') {
            $commonUrl = $this->food_url;
        } else {
            $commonUrl = $this->res_url;
        }
        return $commonUrl.'start=0&rows=0&pt=' . $params['lat'] . ',' . $params['lng'] .$this->search_q;
    }
    
    private function getFacetDeliverUrl($params = array()) {
        $commonUrl = $this->getFacetCommonUrl($params);
        //all fq filters
        $solrFq = $this->getDeliverFqFilters($params);
        return $commonUrl . $solrFq;
    }

    private function getFacetTakeoutUrl($params = array()) {
        $commonUrl = $this->getFacetCommonUrl($params);
        //all fq filters
        $solrFq = $this->getTakeoutFqFilters($params);
        return $commonUrl . $solrFq;
    }

    private function getFacetDineinUrl($params) {
        $commonUrl = $this->getFacetCommonUrl($params);
        //all fq filters
        $solrFq = $this->getDineinFqFilters($params);
        return $commonUrl . $solrFq;
    }

    private function getFacetReservationUrl($params) {
        $commonUrl = $this->getFacetCommonUrl($params);
        //all fq filters
        $solrFq = $this->getReserveFqFilters($params);
        return $commonUrl . $solrFq;
    }

    private function getFacetDiscoverUrl($params) {
        $commonUrl = $this->getFacetCommonUrl($params);
        //all fq filters
        $solrFq = $this->getDiscoverFqFilters($params);
        return $commonUrl . $solrFq;
    }

    //======================= AUTOSUGGESTION DATA =============================
    
    private function getAcCommonUrl($request){
        if ($request['ovt'] == 'restaurant') {
            $baseUrl = $this->ac_res_url;
        } else {
            $baseUrl = $this->ac_food_url;
        }
        return $baseUrl;
    }
    
    private function getAcCuiPart(){
        return 'start=0&rows=0&pt=' . $this->atavfq->latlong . $this->search_q . $this->search_top;
    }
    
    private function getAcTopPart(){
        return 'start=0&rows=0&pt=' . $this->atavfq->latlong . $this->search_q . $this->search_cui;
    }
    
    private function getAcNamePart($req){
        return 'pt=' . $this->atavfq->latlong . $this->search_q . $this->search_cui . $this->search_top . '&q=' . $req['term'];
    }

    private function getAcUrl($req, $solrFq){
        $baseUrl = $this->getAcCommonUrl($req);
        $cui_url = $baseUrl . $this->getAcCuiPart() . $solrFq;
        $top_url = $baseUrl . $this->getAcTopPart() . $solrFq;
        $name_url = $baseUrl . $this->getAcNamePart($req) . $solrFq;
        return array($cui_url, $top_url, $name_url);
    }

    public function getAcDeliverUrls($req = array()) {
        $solrFq = $this->getDeliverFqFilters($req);
        return $this->getAcUrl($req, $solrFq);
    }

    public function getAcTakeoutUrls($req = array()) {
        $solrFq = $this->getTakeoutFqFilters($req); //all fq filters
        return $this->getAcUrl($req, $solrFq);
    }

    public function getAcDineinUrls($req) {
        $solrFq = $this->getDineinFqFilters($req); //all fq filters
        return $this->getAcUrl($req, $solrFq);
    }

    public function getAcReservationUrls($req) {
        $solrFq = $this->getReserveFqFilters($req); //all fq filters
        return $this->getAcUrl($req, $solrFq);
    }

    public function getAcDiscoverUrls($req) {
        $solrFq = $this->getDiscoverFqFilters($req); //all fq filters
        return $this->getAcUrl($req, $solrFq);
    }
    
    private function setSolrQ($req) 
    {
        $solr_q = '';
        if($req['sq'][0] == "-")
        {
            $req['sq']  =   substr($req['sq'],1,strlen($req['sq'])-1);
        }    
        elseif($req['sq'][0] == '"' || $req['sq'][0] == "'")
        {
            if ($req['sq'][1] == "-")
            {
                $req['sq']  =    $req['sq'][0].substr($req['sq'],2,strlen($req['sq'])-1);
            }
        }
        
        if (in_array($req['sdt'], array('ft', 'food'))) 
        {
            $solr_q = "&q='" . rawurlencode($req['sq'])."'^5+OR+".rawurlencode($req['sq']);
        } 
        elseif ($req['sdt'] == 'r') 
        {
            $solr_q .= '&q=res_fct:"' . rawurlencode($req['sq']) . '"';
        } 
        else if (in_array($req['sdt'], self::$cuisine_match_arr)) 
        {
            if ($this->view_type == 'restaurant') 
            {
                $solr_q .= '&q=cuisine_fct:"' . rawurlencode($req['sq']) . '"';
            } 
            elseif ($this->view_type == 'food') 
            {
                $solr_q .= '&q=menu_cuisine_fct:"' . rawurlencode($req['sq']) . '"';
            }
        } 
        else if (in_array($req['sdt'], self::$feature_match_arr)) 
        {
            $solr_q .= '&q=feature_fct:"' . rawurlencode($req['sq']) . '"';
        } 
        else if (in_array($req['sdt'], array('tag'))) 
        {
            $solr_q .= '&q=tags_fct:"' . rawurlencode($req['sq']) . '"';
        }
        
        return $solr_q;
    }
    
    /* Below function has been added to give the count of selected filters  [ Athar: 28-08-2017 ]*/
    public function getFacetCountForSelectedFilters($req)
    {
        //set solr fq's
        $fqArr = explode('||', html_entity_decode($req['fq']));
        $fdtArr = explode('||', $req['fdt']);
        if (count($fqArr) != count($fdtArr)) {
            exit("incorrect fq fdt");
        }

        $search_facet   =   '';
        $n              =   count($fqArr);
        $q_helper       =   array();
        
        for ($i = 0; $i < $n; $i++) 
        {
            if (in_array($fdtArr[$i], self::$cuisine_match_arr)) 
            {
                if ($this->view_type == 'restaurant') 
                {
                    $search_facet .= '&facet.query={!key="'.$fqArr[$i].'"}cuisine_fct:"'.rawurlencode($fqArr[$i]).'"';
                } 
                elseif ($this->view_type == 'food') 
                {
                    $search_facet .= '&facet.query={!key="'.$fqArr[$i].'"}menu_cuisine_fct:"'.rawurlencode($fqArr[$i]).'"';
                }
            } 
            elseif (in_array($fdtArr[$i], self::$feature_match_arr)) 
            {
                $search_facet .= '&facet.query={!key="'.$fqArr[$i].'"}feature_fct:"'.rawurlencode($fqArr[$i]).'"';
            }
        }
        
        $tab = $req['sst'] ;
        if ( 'all' != strtolower($tab) )
        {
            if('takeout' == strtolower($tab))
            {
                $search_facet .= '&facet.query={!key="takeout"}res_takeout:1 AND r_menu_available:1 AND r_menu_without_price:0';    
            }
            else if('deliver' == strtolower($tab))
            {
                $search_facet .= '&facet.query={!key="delivery"}accept_cc_phone:1 AND res_delivery:1 AND r_menu_available:1 AND r_menu_without_price:0';    
            }
            else if('dinein' == strtolower($tab))
            {
                $search_facet .= '&facet.query={!key="dinein"}accept_cc_phone:1 AND res_dining:1';    
            }
            else if('reservation' == strtolower($tab))
            {
                $search_facet .= '&facet.query={!key="reservation"}res_reservations:1';    
            }
        }
        
        return $search_facet;
    }
}