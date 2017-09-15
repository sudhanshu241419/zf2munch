<?php

namespace Solr;

use Solr\SearchUrls;
use Solr\SearchHelpers;

// class testing
// Facets::howToUseThisClass();
class Facets {

    private $debug = 0;
    private static $resCuiFacetPart = '&facet=true&facet.mincount=1&facet.field={!key=cid}cuisine_id';
    private static $resTopFacetPart = '&facet=true&facet.mincount=1&facet.field={!key=fid}feature_id';
    private static $foodCuiFacetPart = '&facet=true&facet.mincount=1&facet.field={!key=cid}menu_cuisines_id';
    private static $foodTopFacetPart = '&facet=true&facet.mincount=1&facet.field={!key=fid}feature_id';

    public function returnFacetData($request) {
        //print_r($request);die;
        if (isset($request['DeBuG']) && $request['DeBuG'] == 404) {
            $this->debug = 1;
        }
        $searchUrl = new SearchUrls ();
        $facetData = $searchUrl->getFacetData($request); //contains vt, url, cui, top
        //pr($facetData,1);
        $data = $this->prepareFacetData($facetData);
        return $data;
    }

    private function prepareFacetData($facetData) {
        $resData = array();
        $this->setCuisineFacets($resData, $facetData);
        $this->setTopFacets($resData, $facetData);
        //print_r($resData);die;
        return $resData;
    }

    private function getCurlUrlData($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data ['result'] = curl_exec($ch);
        $data ['status_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $data;
    }

    private function setCuisineFacets(&$resData, $facetData) {
        if ($facetData['ovt'] == 'food') {
            $unescapedurl = $facetData['url'] . $facetData['top'] . self::$foodCuiFacetPart;
        } else {
            $unescapedurl = $facetData['url'] . $facetData['top'] . self::$resCuiFacetPart."&facet.field={!key=cname}res_cuisine";
        }
        $url = preg_replace('/\s+/', '%20', $unescapedurl);
        if ($this->debug) {
            $resData['url']['cui'] = SearchHelpers::getDebugUrl($url);
        }
        $output = $this->getCurlUrlData($url);
        if ($output ['status_code'] == 200) {
            $responseArr = json_decode($output ['result'], true);
            $tempfacetdata = $responseArr ['facet_counts'] ['facet_fields'];
            $rcif = $tempfacetdata ['cid'];
            $rcifn = $tempfacetdata ['cname'];
            $count = count($rcif);
            for ($i = 0; $i < $count; $i += 2) {
                $resData ['facet_data'] ['cuisine_id'] [$rcif[$i]] = $rcif [$i + 1]."##".$rcifn[$i];
            }
        }
    }

    private function setTopFacets(&$resData, $facetData) {
        if ($facetData['ovt'] == 'food') {
            $unescapedurl = $facetData['url'] . $facetData['cui'] . self::$foodTopFacetPart;
        } else {
            $unescapedurl = $facetData['url'] . $facetData['cui'] . self::$resTopFacetPart."&facet.field={!key=fname}feature_name";
        }
        $url = preg_replace('/\s+/', '%20', $unescapedurl);
        if ($this->debug) {
            $resData['url']['top'] = SearchHelpers::getDebugUrl($url);
        }
        $output = $this->getCurlUrlData($url);
        if ($output ['status_code'] == 200) {
            $responseArr = json_decode($output ['result'], true);
            $tempfacetdata = $responseArr ['facet_counts'] ['facet_fields'];
            $fnif = $tempfacetdata ['fid'];
            $fnifn = $tempfacetdata ['fname'];
            $count = count($fnif);
            for ($i = 0; $i < $count; $i += 2) {
                $resData ['facet_data'] ['feature_id'] [$fnif [$i]] = $fnif [$i + 1]."##".$fnifn[$i];;
            }
        }
    }

}

?>
