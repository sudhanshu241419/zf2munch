<?php

namespace Dashboard\Controller;

use MCommons\Controller\AbstractRestfulController;
use Dashboard\DashboardFunctions;

class ReportController extends AbstractRestfulController {

    public function getList() {
        $dashboardFunctions = new DashboardFunctions();
        $dashboardFunctions->getDashboardDetail(); //restaurant Details
        $currentDate = $dashboardFunctions->CityTimeZone();
        $restaurantId = $dashboardFunctions->restaurantId;
        //type should be in (overview,visit,order,engagement)
        $varReportType = $this->params('type');
        $start_date = $this->params('date');
        $end_date = $this->params('enddate');
        $filter = $this->params('filter');
        $currentDate = strtotime('-2 day', strtotime($currentDate));
        $currentDate = date('Y-m-d h:i:s', $currentDate);
        
        $startRetaurantCreateDate = strtotime(date('Y-m-d', strtotime($dashboardFunctions->dashboardDetails['created_on'])));
        $endNowDate = strtotime(date('Y-m-d', strtotime($currentDate)));
        $days_between = ceil(abs($endNowDate - $startRetaurantCreateDate) / 86400);

        $dateRefineToSearch = $this->getRefineDate($currentDate, $start_date, $end_date, $filter, $days_between);
        error_reporting(~E_NOTICE);
        
        $sl = $this->getServiceLocator();
        $config = $sl->get('Config');
        $mongoClient = new \MongoClient($config['mongo']['host']);
        $mongoDb = new \MongoDB($mongoClient, $config['mongo']['database']);
        $collectionName = 'reports';
        if (!$mongoDb->$collectionName) {
            $collection = $mongoDb->createCollection($collectionName);
        }
        $collection = $mongoDb->createCollection($collectionName);
        if ($dashboardFunctions->restaurantId) {
            $data = array();
            switch ($varReportType) {
                case 'overview':
                    $data = $this->overview($dashboardFunctions->restaurantId, $dateRefineToSearch, $collection);
                    break;
                case 'visit':
                    $data = $this->visit($currentDate, $dashboardFunctions->restaurantId, $dateRefineToSearch, $collection);
                    break;
                case 'order':
                    $data = $this->order($currentDate, $dashboardFunctions->restaurantId, $dateRefineToSearch, $collection);
                    break;
                case 'engagment':
                    $data = $this->engagment($currentDate, $dashboardFunctions->restaurantId, $dateRefineToSearch, $collection);
                    break;
                case 'downloadpdf':
                    $data = $this->downloadpdf($currentDate, $dashboardFunctions->restaurantId, $dateRefineToSearch, $collection);
                    break;
            }
        }
        return $data;
    }

    public function getRefineDate($currentDate = "", $start_date = "", $end_date = "", $filter = "", $days_between = 0) {
        if (($start_date == 'sdate' || $end_date == 'edate') && ($filter == 'filter' || $filter == 'week')) {

            $end_date = date('Y-m-d', strtotime($currentDate));
            $newdate = strtotime('-1 week', strtotime($end_date));
            $lastweeknewdate = strtotime('-2 week', strtotime($end_date));
            $lastweek_start_date = date('Y-m-d', $lastweeknewdate);
            $lastweek_end_date = date('Y-m-d', $newdate);
            $lastweek_end_date = strtotime('-1 day', strtotime($lastweek_end_date));
            $lastweek_end_date = date('Y-m-d', $lastweek_end_date);
            $start_date = date('Y-m-d', $newdate);
            $frequencydiffDate = 7;
        } else if ($filter == 'month') {

            $end_date = date('Y-m-d', strtotime($currentDate));
            $newdate = strtotime('-1 month', strtotime($end_date));
            $lastweeknewdate = strtotime('-2 month', strtotime($end_date));
            $lastweek_start_date = date('Y-m-d', $lastweeknewdate);
            $lastweek_end_date = date('Y-m-d', $newdate);
            $start_date = date('Y-m-d', $newdate);
            $frequencydiffDate = 30;
        } else if ($filter == 'all') {

            $end_date = '';
            $newdate = '';
            $lastweeknewdate = '';
            $lastweek_start_date = '';
            $lastweek_end_date = '';
            $start_date = '';
            $frequencydiffDate = $days_between;
        } else {
            $start_date = $this->filter_input('date');
            $end_date = $this->filter_input('enddate');
        }

        return array('start_date' => $start_date, 'end_date' => $end_date, 'last_start_date' => $lastweek_start_date, 'last_end_date' => $lastweek_end_date, 'restaurantTillDays' => $frequencydiffDate);
    }

    public function overview($restaurant_id, $dateRefineToSearch, $collection) {
        if ($dateRefineToSearch['start_date'] != '' && $dateRefineToSearch['end_date'] != '') {
            return $this->overviewWeekMonthData($dateRefineToSearch, $restaurant_id, $collection);
        } else {
            return $this->overviewAllData($restaurant_id, $collection);
        }
    }

    public function overviewWeekMonthData($dateRefineToSearch, $restaurant_id, $collection) {
        $condition = array('date' => $dateRefineToSearch['end_date'], 'restaurant_id' => (int) $restaurant_id);
        $currentDateData = $collection->find($condition);
        $reportCurrentTodayDate = iterator_to_array($currentDateData, FALSE);
        if (count($reportCurrentTodayDate) == 0) {
            throw new \Exception('Data not found.');
        }

        $conditionStartDate = array('date' => $dateRefineToSearch['start_date'], 'restaurant_id' => (int) $restaurant_id);
        $conditionLastweekEndDate = array('date' => $dateRefineToSearch['last_end_date'], 'restaurant_id' => (int) $restaurant_id);
        $conditionLastweekStartDate = array('date' => $dateRefineToSearch['last_start_date'], 'restaurant_id' => (int) $restaurant_id);
         //pr($condition);
       // pr($conditionStartDate,1);
//        pr($conditionLastweekEndDate);
//        pr($conditionLastweekStartDate,1);
        $lastStartDate = $collection->find($conditionStartDate);
        $reportCurrentWeekendDate = iterator_to_array($lastStartDate, FALSE);
        $todayReport = $reportCurrentTodayDate[0]['reports'];
        $LastReport = $reportCurrentWeekendDate[0]['reports'];
        //pr($todayReport['social']['ga']);
        //pr($LastReport['social']['ga']);
        //get current week data

        $overViewData['total_orders'] = (int) $todayReport['munchado']['orders']['order_alltime']['total_orders'] - (int) $LastReport['munchado']['orders']['order_alltime']['total_orders'];
        $overViewData['ga_users'] = (int) $todayReport['social']['ga']['users'] - (int) $LastReport['social']['ga']['users'];
        $overViewData['people_reached'] = (int) $todayReport['social']['engagement']['people_reached'] - (int) $LastReport['social']['engagement']['people_reached'];
        $overViewData['visit'] = (int) $todayReport['social']['ga']['visit'] - (int) $LastReport['social']['ga']['visit'];
        $overViewData['visit_new_customer'] = (int) $todayReport['social']['ga']['new_customer'] - (int) $LastReport['social']['ga']['new_customer'];
        $overViewData['visit_returing_customer'] = (int) $todayReport['social']['ga']['returing_customer'] - (int) $LastReport['social']['ga']['returing_customer'];
        $overViewData['pageviews'] = (int) $todayReport['social']['ga']['page_views'] - (int) $LastReport['social']['ga']['page_views'];
        
        //pr($overViewData,1);
        if($overViewData['visit']>0){
        $overViewData['page_per_visit'] = number_format($overViewData['pageviews'] / $overViewData['visit'], 2);
        }else{
            $overViewData['page_per_visit']=0;
        }
        $overViewData['message_sent'] = (int) $todayReport['munchado']['emails']['message_sent'] - (int) $LastReport['munchado']['emails']['message_sent'];
        $overViewData['twitter_followers'] = (int) ($todayReport['social']['twitter']['followers']) - (int) ($LastReport['social']['twitter']['followers']);
        $overViewData['instagram_followers'] = (int) ($todayReport['social']['instagram']['followers']) - (int) ($LastReport['social']['instagram']['followers']);
        $overViewData['fb_followers'] = (int) ($todayReport['social']['facebook']['followers']) - (int) ($LastReport['social']['facebook']['followers']);
        $overViewData['followers'] = $overViewData['instagram_followers'] + $overViewData['fb_followers'];
        $overViewData['fb_review_comment'] = (int) ($todayReport['social']['facebook']['comments']) - (int) ($LastReport['social']['facebook']['comments']);
        $overViewData['insta_review_comment'] = (int) ($todayReport['social']['instagram']['comments']) - (int) ($LastReport['social']['instagram']['comments']);
        $overViewData['review_comment'] = (int) ($todayReport['munchado']['review']['review_alltime']['total_reviews']) - (int) ($LastReport['munchado']['review']['review_alltime']['total_reviews']);
        $overViewData['review_comment'] = (int) $overViewData['fb_review_comment'] + $overViewData['review_comment'] + $overViewData['insta_review_comment'];
        $overViewData['avg_time_profile'] = $todayReport['social']['ga']['avg_time_profile'];
        $overViewData['total_revenue'] =$todayReport['munchado']['orders']['order_alltime']['total_revenue'] - $LastReport['munchado']['orders']['order_alltime']['total_revenue'];
        if($overViewData['total_orders']>0){
            $overViewData['avg_order_val'] = number_format($overViewData['total_revenue'] / $overViewData['total_orders'], 2);
            $overViewData['item_per_order'] = number_format($todayReport['munchado']['orders']['order_alltime']['avg_item'],2);
            $overViewData['order_conversion'] = number_format($overViewData['total_orders'] / $overViewData['ga_users'] * 100, 2);
        }else{
            $overViewData['avg_order_val'] = 0;
            $overViewData['item_per_order'] = 0;
            $overViewData['order_conversion'] = 0;
        }
        $overViewData['order_new_customers'] = (int) $todayReport['munchado']['orders']['order_alltime']['new_customers'] - $LastReport['munchado']['orders']['order_alltime']['new_customers'];
        $overViewData['order_returning_customers'] = (int) $todayReport['munchado']['orders']['order_alltime']['returning_customers'] - $LastReport['munchado']['orders']['order_alltime']['returning_customers'];
        $overViewData['email_sent'] = (int) ($todayReport['munchado']['emails']['total_mails_sent']) - (int) ($LastReport['munchado']['emails']['total_mails_sent']);

        //assign 0 if value gets in less then 0
        foreach ($overViewData as $key => $val) {
            if ($val < 0) {
                $overViewData[$key] = 0;
            }
        }
        // get last week data
        $conditionLastweekEndDate = array('date' => $dateRefineToSearch['last_end_date'], 'restaurant_id' => (int) $restaurant_id);
        //pr($conditionLastweekEndDate,1);
        $lastweekEndDate = $collection->find($conditionLastweekEndDate);
        $reportLastweekEndDate = iterator_to_array($lastweekEndDate, FALSE);

        $conditionLastweekStartDate = array('date' => $dateRefineToSearch['last_start_date'], 'restaurant_id' => (int) $restaurant_id);
        $lastweekStartDate = $collection->find($conditionLastweekStartDate);
        $reportLastweekStartDate = iterator_to_array($lastweekStartDate, FALSE);

        $todayReport = $reportLastweekEndDate[0]['reports'];
        $LastReport = $reportLastweekStartDate[0]['reports'];

        $lastOverviewData['total_orders'] = (int) $todayReport['munchado']['orders']['order_alltime']['total_orders'] - (int) $LastReport['munchado']['orders']['order_alltime']['total_orders'];
        $lastOverviewData['ga_users'] = (int) $todayReport['social']['ga']['users'] - (int) $LastReport['social']['ga']['users'];
        $lastOverviewData['people_reached'] = (int) $todayReport['social']['engagement']['people_reached'] - (int) $LastReport['social']['engagement']['people_reached'];
        $lastOverviewData['visit'] = (int) $todayReport['social']['ga']['visit'] - (int) $LastReport['social']['ga']['visit'];

        $lastOverviewData['visit_new_customer'] = (int) $todayReport['social']['ga']['new_customer'] - (int) $LastReport['social']['ga']['new_customer'];
        $lastOverviewData['visit_returing_customer'] = (int) $todayReport['social']['ga']['returing_customer'] - (int) $LastReport['social']['ga']['returing_customer'];
        $lastOverviewData['pageviews'] = (int) $todayReport['social']['ga']['page_views'] - (int) $LastReport['social']['ga']['page_views'];
        
        if ($lastOverviewData['visit'] > 0) {
            $lastOverviewData['page_per_visit'] = number_format($lastOverviewData['pageviews'] / $lastOverviewData['visit'], 2);
        } else {
            $lastOverviewData['page_per_visit'] = 0;
        }
        $lastOverviewData['message_sent'] = (int) $todayReport['munchado']['emails']['message_sent'] - (int) $LastReport['munchado']['emails']['message_sent'];
        $lastOverviewData['twitter_followers'] = (int) ($todayReport['social']['twitter']['followers']) - (int) ($LastReport['social']['twitter']['followers']);
        $lastOverviewData['instagram_followers'] = (int) ($todayReport['social']['instagram']['followers']) - (int) ($LastReport['social']['instagram']['followers']);
        $lastOverviewData['fb_followers'] = (int) ($todayReport['social']['facebook']['followers']) - (int) ($LastReport['social']['facebook']['followers']);
        $lastOverviewData['followers'] = (int) $lastOverviewData['instagram_followers'] + $lastOverviewData['fb_followers'];
        $lastOverviewData['fb_review_comment'] = (int) ($todayReport['social']['facebook']['comments']) - (int) ($LastReport['social']['facebook']['comments']);
        $lastOverviewData['review_comment'] = (int) ($todayReport['munchado']['review']['review_alltime']['total_reviews']) - (int) ($LastReport['munchado']['review']['review_alltime']['total_reviews']);
        $lastOverviewData['insta_review_comment'] = (int) ($todayReport['social']['instagram']['comments']) - (int) ($LastReport['social']['instagram']['comments']);
        $lastOverviewData['review_comment'] = $lastOverviewData['review_comment'] + $lastOverviewData['fb_review_comment'] + $lastOverviewData['insta_review_comment'];

        $lastOverviewData['avg_time_profile'] = $todayReport['social']['ga']['avg_time_profile'];
        $lastOverviewData['total_revenue'] = $todayReport['munchado']['orders']['order_alltime']['total_revenue'] - $LastReport['munchado']['orders']['order_alltime']['total_revenue'];
        if ($lastOverviewData['total_orders'] > 0) {
            $lastOverviewData['avg_order_val'] = number_format($lastOverviewData['total_revenue'] / $lastOverviewData['total_orders'], 2);
        } else {
            $lastOverviewData['avg_order_val'] = $lastOverviewData['total_revenue'];
        }
        $lastOverviewData['item_per_order'] = number_format($todayReport['munchado']['orders']['order_alltime']['avg_item'],2);
        if ($lastOverviewData['ga_users'] > 0) {
            $lastOverviewData['order_conversion'] = number_format($lastOverviewData['total_orders'] / $lastOverviewData['ga_users'] * 100, 2);
        } else {
            $lastOverviewData['order_conversion'] = 0;
        }

        $lastOverviewData['order_new_customers'] = (int) $todayReport['munchado']['orders']['order_alltime']['new_customers'] - (int) $LastReport['munchado']['orders']['order_alltime']['new_customers'];
        $lastOverviewData['order_returning_customers'] = (int) $todayReport['munchado']['orders']['order_alltime']['returning_customers'] - (int) $LastReport['munchado']['orders']['order_alltime']['returning_customers'];
        $lastOverviewData['email_sent'] = (int) ($todayReport['munchado']['emails']['total_mails_sent']) - (int) ($LastReport['munchado']['emails']['total_mails_sent']);

        $overViewData['orderUpDown'] = self::getPercentVal($overViewData['total_orders'], $lastOverviewData['total_orders']);
        $overViewData['revenueUpDown'] = self::getPercentVal($overViewData['total_revenue'], $lastOverviewData['total_revenue']);
        $overViewData['peopleUpDown'] = self::getPercentVal($overViewData['people_reached'], $lastOverviewData['people_reached']);
        $overViewData['orderConversionUpDown'] = self::getPercentVal($overViewData['order_conversion'], $lastOverviewData['order_conversion']);
        $overViewData['gaUserUpDown'] = self::getPercentVal($overViewData['ga_users'], $lastOverviewData['ga_users']);
        $overViewData['visitUpDown'] = self::getPercentVal($overViewData['visit'], $lastOverviewData['visit']);
        $overViewData['pagepervisitUpDown'] = self::getPercentVal($overViewData['page_per_visit'], $lastOverviewData['page_per_visit']);
        $overViewData['pageviewsUpDown'] = self::getPercentVal($overViewData['pageviews'], $lastOverviewData['pageviews']);
        $overViewData['visitnewcustomerUpDown'] = self::getPercentVal($overViewData['visit_new_customer'], $lastOverviewData['visit_new_customer']);
        $overViewData['visitrepeatcustomerUpDown'] = self::getPercentVal($overViewData['visit_returing_customer'], $lastOverviewData['visit_returing_customer']);
        $overViewData['messagesentUpDown'] = self::getPercentVal($overViewData['message_sent'], $lastOverviewData['message_sent']);
        $overViewData['followersUpDown'] = self::getPercentVal($overViewData['followers'], $lastOverviewData['followers']);
        $overViewData['reviewUpDown'] = self::getPercentVal($overViewData['review_comment'], $lastOverviewData['review_comment']);
        $overViewData['avgTimePageUpDown'] = self::getPercentVal($overViewData['avg_time_profile'], $lastOverviewData['avg_time_profile']);
        $overViewData['avgordervalUpDown'] = self::getPercentVal($overViewData['avg_order_val'], $lastOverviewData['avg_order_val']);
        $overViewData['itemperorderUpDown'] = self::getPercentVal($overViewData['item_per_order'], $lastOverviewData['item_per_order']);
        $overViewData['orderNewuserUpDown'] = self::getPercentVal($overViewData['order_new_customers'], $lastOverviewData['order_new_customers']);
        $overViewData['orderrepeatuserUpDown'] = self::getPercentVal($overViewData['order_returning_customers'], $lastOverviewData['order_returning_customers']);
        $overViewData['emailsentUpDown'] = self::getPercentVal($overViewData['email_sent'], $lastOverviewData['email_sent']);
        return $overViewData;
    }

    public function overviewAllData($restaurant_id, $collection) {
        $condition = array('restaurant_id' => (int) $restaurant_id);
        
        $currentDateData = $collection->find($condition)->sort(array('date' =>-1));
        $reportCurrentTodayDate = iterator_to_array($currentDateData, FALSE);
        if (count($reportCurrentTodayDate) == 0) {
            throw new \Exception('Data not found.');
        }

        $todayReport = $reportCurrentTodayDate[0]['reports'];
        // pr($todayReport,1);
        $overViewData['total_orders'] = (int) $todayReport['munchado']['orders']['order_alltime']['total_orders'];
        $overViewData['ga_users'] = (int) $todayReport['social']['ga']['users'];
        $overViewData['people_reached'] = 0;
        $overViewData['visit'] = (int) $todayReport['social']['ga']['visit'];

        $overViewData['visit_new_customer'] = (int) $todayReport['social']['ga']['new_customer'];
        $overViewData['visit_returing_customer'] = (int) $todayReport['social']['ga']['returing_customer'];
        $overViewData['pageviews'] = (int) $todayReport['social']['ga']['page_views'];
       
        //pr($overViewData,1);
        if($overViewData['visit']>0){
        $overViewData['page_per_visit'] = number_format($overViewData['pageviews'] / $overViewData['visit'], 2);
        }else{
            $overViewData['page_per_visit']=0;
        }
        $overViewData['message_sent'] = (int) $todayReport['munchado']['emails']['message_sent'];
        $overViewData['twitter_followers'] = (int) ($todayReport['social']['twitter']['followers']);
        $overViewData['instagram_followers'] = (int) ($todayReport['social']['instagram']['followers']);
        $overViewData['followers'] = (int) $overViewData['twitter_followers'] + $overViewData['instagram_followers'];
        $overViewData['fb_review_comment'] = (int) ($todayReport['social']['facebook']['comments']);
        $overViewData['insta_review_comment'] = (int) ($todayReport['social']['instagram']['comments']);
        $overViewData['review_comment'] = (int) ($todayReport['munchado']['review']['review_alltime']['total_reviews']);
        $overViewData['review_comment'] = $overViewData['review_comment'] + $overViewData['fb_review_comment'] + $overViewData['insta_review_comment'];
        $overViewData['avg_time_profile'] = $todayReport['social']['ga']['avg_time_profile'];
        $overViewData['total_revenue'] =$todayReport['munchado']['orders']['order_alltime']['total_revenue'];
        $overViewData['avg_order_val'] = number_format($overViewData['total_revenue'] / $overViewData['total_orders'], 2);
        $overViewData['item_per_order'] = number_format($todayReport['munchado']['orders']['order_alltime']['avg_item'],2);
        $overViewData['order_conversion'] = number_format($overViewData['total_orders'] / $overViewData['ga_users'] * 100, 2);
        $overViewData['order_new_customers'] = $todayReport['munchado']['orders']['order_alltime']['new_customers'];
        $overViewData['order_returning_customers'] = $todayReport['munchado']['orders']['order_alltime']['returning_customers'];
        $overViewData['email_sent'] = (int) ($todayReport['munchado']['emails']['total_mails_sent']);

        $overViewData['orderUpDown'] = "down_0";
        $overViewData['revenueUpDown'] = "down_0";
        $overViewData['peopleUpDown'] = "down_0";
        $overViewData['orderConversionUpDown'] = "down_0";
        $overViewData['gaUserUpDown'] = "down_0";
        $overViewData['visitUpDown'] = "down_0";
        $overViewData['pagepervisitUpDown'] = "down_0";
        $overViewData['pageviewsUpDown'] = "down_0";
        $overViewData['visitnewcustomerUpDown'] = "down_0";
        $overViewData['visitrepeatcustomerUpDown'] = "down_0";
        $overViewData['messagesentUpDown'] = "down_0";
        $overViewData['followersUpDown'] = "down_0";
        $overViewData['reviewUpDown'] = "down_0";
        $overViewData['avgTimePageUpDown'] = "down_0";
        $overViewData['avgordervalUpDown'] = "down_0";
        $overViewData['itemperorderUpDown'] = "down_0";
        $overViewData['orderNewuserUpDown'] = "down_0";
        $overViewData['orderrepeatuserUpDown'] = "down_0";
        $overViewData['emailsentUpDown'] = "down_0";
        return $overViewData;
    }

    public function visit($currentDate, $restaurant_id, $dateRefineToSearch, $collection) {
        if ($dateRefineToSearch['start_date'] != '' && $dateRefineToSearch['end_date'] != '') {
            return $this->visitWeekMonthData($dateRefineToSearch, $restaurant_id, $collection);
        } else {
            return $this->visitAllData($currentDate, $restaurant_id, $collection);
        }
    }

    public function visitWeekMonthData($dateRefineToSearch, $restaurant_id, $collection) {
        $condition = array('date' => $dateRefineToSearch['end_date'], 'restaurant_id' => (int) $restaurant_id);
        $currentDateData = $collection->find($condition);
        $reportCurrentTodayDate = iterator_to_array($currentDateData, FALSE);
        if (count($reportCurrentTodayDate) == 0) {
            throw new \Exception('Data not found.');
        }

        $conditionStartDate = array('date' => $dateRefineToSearch['start_date'], 'restaurant_id' => (int) $restaurant_id);
        $lastStartDate = $collection->find($conditionStartDate);
        $reportCurrentWeekendDate = iterator_to_array($lastStartDate, FALSE);
        $todayReport = $reportCurrentTodayDate[0]['reports'];
        $LastReport = $reportCurrentWeekendDate[0]['reports'];


        $chartCondition = array('date' => array('$gte' => $dateRefineToSearch['start_date'], '$lte' => $dateRefineToSearch['end_date']), 'restaurant_id' => (int) $restaurant_id);

        $chartData = $collection->find($chartCondition, array('date' => 1, 'reports.social.ga' => 1))->sort(array('date' => 1));
        $reportChartData = iterator_to_array($chartData, FALSE);
        //pr($reportChartData,1);

        $varChartRecord = array();
        $varPattranCounter = 0;
        if (count($reportChartData) > 0) {
            foreach ($reportChartData as $key => $vl) {
                foreach ($vl['reports']['social']['ga']['traffic_array'] as $key => $v) {
                    $varChartRecord[$varPattranCounter]['date'] = date("M d", strtotime($v['date']));
                    $varChartRecord[$varPattranCounter]['mob'] = $v['mobile'];
                    $varChartRecord[$varPattranCounter]['web'] = $v['website'];
                    $varPattranCounter++;
                }
            }
//            $newvarChartRecord = array();
//            if (count($varChartRecord) <= 20) {
//                $lessTwCounter = 0;
//                foreach ($varChartRecord as $key => $v) {
//                    $newvarChartRecord[$lessTwCounter]['date'] = $v['date'];
//                    $newvarChartRecord[$lessTwCounter]['mob'] = $v['mob'];
//                    $newvarChartRecord[$lessTwCounter]['web'] = $v['web'];
//                    $lessTwCounter++;
//                }
//            } else if (count($varChartRecord) <= 90) {
//                $lessNintyCounter = 0;
//                foreach ($varChartRecord as $key => $v) {
//                    if ($key % 4 == 0) {
//                        $newvarChartRecord[$lessNintyCounter]['date'] = $v['date'];
//                        $newvarChartRecord[$lessNintyCounter]['mob'] = $v['mob'];
//                        $newvarChartRecord[$lessNintyCounter]['web'] = $v['web'];
//                        $lessNintyCounter++;
//                    }
//                }
//            } else if (count($varChartRecord) <= 180) {
//                $lessoneatiCounter = 0;
//                foreach ($varChartRecord as $key => $v) {
//                    if ($key % 15 == 0) {
//                        $newvarChartRecord[$lessoneatiCounter]['date'] = $v['date'];
//                        $newvarChartRecord[$lessoneatiCounter]['mob'] = $v['mob'];
//                        $newvarChartRecord[$lessoneatiCounter]['web'] = $v['web'];
//                        $lessoneatiCounter++;
//                    }
//                }
//            } else {
//                $lessoneatiCounter = 0;
//                foreach ($varChartRecord as $key => $v) {
//                    if ($key % 30 == 0) {
//                        $newvarChartRecord[$lessoneatiCounter]['date'] = $v['date'];
//                        $newvarChartRecord[$lessoneatiCounter]['mob'] = $v['mob'];
//                        $newvarChartRecord[$lessoneatiCounter]['web'] = $v['web'];
//                        $lessoneatiCounter++;
//                    }
//                }
//            }
            //$varChartRecord = $newvarChartRecord;
        }



        $overViewData['visit'] = (int) $todayReport['social']['ga']['visit'] - (int) $LastReport['social']['ga']['visit'];
        $overViewData['ga_user'] = (int) $todayReport['social']['ga']['users'] - (int) $LastReport['social']['ga']['users'];
        $overViewData['pageviews'] = (int) $todayReport['social']['ga']['page_views'] - (int) $LastReport['social']['ga']['page_views'];
        if($overViewData['visit']>0){
        $overViewData['page_per_visit'] = number_format($overViewData['pageviews'] / $overViewData['visit'], 2);
        }else{
            $overViewData['page_per_visit']=0;
        }
        $overViewData['visit_new_customer'] = (int) $todayReport['social']['ga']['new_customer'] - (int) $LastReport['social']['ga']['new_customer'];
        $overViewData['visit_returing_customer'] = (int) $todayReport['social']['ga']['returing_customer'] - (int) $LastReport['social']['ga']['returing_customer'];
        $overViewData['direct_traffic'] = (int) $todayReport['social']['ga']['direct'] - (int) $LastReport['social']['ga']['direct'];
        $overViewData['referral'] = (int) $todayReport['social']['ga']['referral'] - (int) $LastReport['social']['ga']['referral'];
        $overViewData['web_traffic'] = (int) $todayReport['social']['ga']['web_traffic'] - (int) $LastReport['social']['ga']['web_traffic'];
        $overViewData['mob_traffic'] = (int) $todayReport['social']['ga']['mob_traffic'] - (int) $LastReport['social']['ga']['mob_traffic'];
        $overViewData['display_add'] = (int) $todayReport['social']['ga']['display_add'] - (int) $LastReport['social']['ga']['display_add'];
        $overViewData['display_email'] = (int) $todayReport['social']['ga']['emails'] - (int) $LastReport['social']['ga']['emails'];
        $overViewData['social_media'] = (int) $todayReport['social']['ga']['social_media'] - (int) $LastReport['social']['ga']['social_media'];
        $overViewData['others'] = (int) $todayReport['social']['ga']['others'] - (int) $LastReport['social']['ga']['others'];
        $overViewData['overview_page'] = (int) $todayReport['social']['ga']['overview_page'] - (int) $LastReport['social']['ga']['overview_page'];
        $overViewData['menu_page'] = (int) $todayReport['social']['ga']['menu_page'] - (int) $LastReport['social']['ga']['menu_page'];
        $overViewData['story_page'] = (int) $todayReport['social']['ga']['story_page'] - (int) $LastReport['social']['ga']['story_page'];
        $overViewData['gallery_page'] = (int) $todayReport['social']['ga']['gallery_page'] - (int) $LastReport['social']['ga']['gallery_page'];
        $overViewData['review_page'] = (int) $todayReport['social']['ga']['review_page'] - (int) $LastReport['social']['ga']['review_page'];
        $overViewData['checkout_page'] = (int) $todayReport['social']['ga']['checkout_page'] - (int) $LastReport['social']['ga']['checkout_page'];
        $overViewData['dine_more_page'] = (int) $todayReport['social']['ga']['dine_more'] - (int) $LastReport['social']['ga']['dine_more'];
        $overViewData['popular_pages'] = [];
       // if ($overViewData['visit'] > 0) {
            foreach ($overViewData as $key => $val) {
                if ($val < 0) {
                    $overViewData[$key] = 0;
                }
            }
            $otherVisit =(int) $overViewData['dine_more_page']+(int) $overViewData['gallery_page'] +(int) $overViewData['menu_page'] + (int)$overViewData['review_page'] + (int)$overViewData['checkout_page'];
//            pr($overViewData);
//            pr($otherVisit,1);
            $overViewData['overview_percent'] = number_format($overViewData['overview_page'] / $otherVisit * 100,2);
            $overViewData['menu_percent'] = number_format($overViewData['menu_page'] / $otherVisit * 100,2);
            //$overViewData['story_percent'] = number_format($overViewData['story_page'] / $overViewData['pageviews'] * 100);
            $overViewData['gallery_percent'] = number_format($overViewData['gallery_page'] / $otherVisit * 100,2);
            $overViewData['review_percent'] = number_format($overViewData['review_page'] / $otherVisit * 100,2);
            $overViewData['checkout_percent'] = number_format($overViewData['checkout_page'] / $otherVisit * 100,2);
            $overViewData['dine_more_persent'] = number_format($overViewData['dine_more_page'] / $otherVisit * 100,2);
            
            $overViewData['new_percent'] = number_format($overViewData['visit_new_customer'] / $overViewData['ga_user'] * 100);
            $overViewData['returning_percent'] = number_format($overViewData['visit_returing_customer'] / $overViewData['ga_user'] * 100);
            
           
            $overViewData['other_page'] = (int) $overViewData['pageviews'] - $otherVisit;
            $overViewData['other_page_persent'] = number_format($overViewData['other_page'] / $overViewData['pageviews'] * 100);
            $overViewData['other_page_persent']=($overViewData['other_page_persent']>100)?100:$overViewData['other_page_persent'];
            $overViewData['traffic_array'] = $varChartRecord;
//            $overViewData['popular_pages'][0]['page'] = 'Overview';
//            $overViewData['popular_pages'][0]['count'] = $overViewData['overview_page'];
//            $overViewData['popular_pages'][0]['percent'] = $overViewData['overview_percent'];
            $overViewData['popular_pages'][0]['page'] = 'Menu';
            $overViewData['popular_pages'][0]['count'] = $overViewData['menu_page'];
            $overViewData['popular_pages'][0]['percent'] = $overViewData['menu_percent'];
//            $overViewData['popular_pages'][2]['page'] = 'story';
//            $overViewData['popular_pages'][2]['count'] = $overViewData['story_page'];
//            $overViewData['popular_pages'][2]['percent'] = $overViewData['story_percent'];
            
            $overViewData['popular_pages'][1]['page'] = 'Review';
            $overViewData['popular_pages'][1]['count'] = $overViewData['review_page'];
            $overViewData['popular_pages'][1]['percent'] = $overViewData['review_percent'];
            $overViewData['popular_pages'][2]['page'] = 'Checkout';
            $overViewData['popular_pages'][2]['count'] = $overViewData['checkout_page'];
            $overViewData['popular_pages'][2]['percent'] = $overViewData['checkout_percent'];
//            $overViewData['popular_pages'][6]['page'] = 'other';
//            $overViewData['popular_pages'][6]['count'] = $overViewData['other_page'];
//            $overViewData['popular_pages'][6]['percent'] = $overViewData['other_page_persent'];
            $overViewData['popular_pages'][3]['page'] = 'Dine & More';
            $overViewData['popular_pages'][3]['count'] = $overViewData['dine_more_page'];
            $overViewData['popular_pages'][3]['percent'] = $overViewData['dine_more_persent'];
            $overViewData['popular_pages'][4]['page'] = 'Gallery';
            $overViewData['popular_pages'][4]['count'] = $overViewData['gallery_page'];
            $overViewData['popular_pages'][4]['percent'] = $overViewData['gallery_percent'];
       // }
        $active = [];
        if (count($overViewData['popular_pages']) > 0) {
            foreach ($overViewData['popular_pages'] as $key => $values) {
                $active[$key] = $values['count'];
            }
        }
        array_multisort($active, SORT_DESC, $overViewData['popular_pages']);
        foreach ($overViewData as $key => $val) {
            if ($val < 0) {
                $overViewData[$key] = 0;
            }
        }

        // get last date all data
        $conditionLastweekEndDate = array('date' => $dateRefineToSearch['last_end_date'], 'restaurant_id' => (int) $restaurant_id);
        $lastweekEndDate = $collection->find($conditionLastweekEndDate);
        $reportLastweekEndDate = iterator_to_array($lastweekEndDate, FALSE);

        // get last week all data
        $conditionLastweekStartDate = array('date' => $dateRefineToSearch['last_start_date'], 'restaurant_id' => (int) $restaurant_id);
        $lastweekStartDate = $collection->find($conditionLastweekStartDate);
        $reportLastweekStartDate = iterator_to_array($lastweekStartDate, FALSE);
        $todayReport = $reportLastweekEndDate[0]['reports'];
        $LastReport = $reportLastweekStartDate[0]['reports'];

        $lastOverviewData['visit'] = (int) $todayReport['social']['ga']['visit'] - (int) $LastReport['social']['ga']['visit'];
        $lastOverviewData['pageviews'] = (int) $todayReport['social']['ga']['page_views'] - (int) $LastReport['social']['ga']['page_views'];
        if ($lastOverviewData['visit'] > 0) {
            $lastOverviewData['page_per_visit'] = number_format($lastOverviewData['pageviews'] / $lastOverviewData['visit'], 2);
        } else {
            $lastOverviewData['page_per_visit'] = 0;
        }
        $lastOverviewData['visit_new_customer'] = (int) $todayReport['social']['ga']['new_customer'] - (int) $LastReport['social']['ga']['new_customer'];
        $lastOverviewData['visit_returing_customer'] = (int) $todayReport['social']['ga']['returing_customer'] - (int) $LastReport['social']['ga']['returing_customer'];


        $overViewData['visitUpDown'] = self::getPercentVal($overViewData['visit'], $lastOverviewData['visit']);
        $overViewData['pagePerVisitUpDown'] = self::getPercentVal($overViewData['page_per_visit'], $lastOverviewData['page_per_visit']);
        $overViewData['pageViewsUpDown'] = self::getPercentVal($overViewData['pageviews'], $lastOverviewData['pageviews']);
        $overViewData['newCustomerUpDown'] = self::getPercentVal($overViewData['visit_new_customer'], $lastOverviewData['visit_new_customer']);
        $overViewData['repeatCustomerUpDown'] = self::getPercentVal($overViewData['visit_returing_customer'], $lastOverviewData['visit_returing_customer']);
        return $overViewData;
    }

    public function visitAllData($currentDate, $restaurant_id, $collection) {
        $condition = array('restaurant_id' => (int) $restaurant_id);
        $currentDateData = $collection->find($condition)->sort(array('date' =>-1));;
        $reportCurrentTodayDate = iterator_to_array($currentDateData, FALSE);
        if (count($reportCurrentTodayDate) == 0) {
            throw new \Exception('Data not found.');
        }
        $todayReport = $reportCurrentTodayDate[0]['reports'];
        $overViewData['visit'] = (int) $todayReport['social']['ga']['visit'];
        $overViewData['ga_users'] = (int) $todayReport['social']['ga']['users'];
        $overViewData['pageviews'] = (int) $todayReport['social']['ga']['page_views'];
        
        $overViewData['visit_new_customer'] = (int) $todayReport['social']['ga']['new_customer'];
        $overViewData['visit_returing_customer'] = (int) $todayReport['social']['ga']['returing_customer'];
        $overViewData['direct_traffic'] = (int) $todayReport['social']['ga']['direct'];
        $overViewData['referral'] = (int) $todayReport['social']['ga']['referral'];
        $overViewData['web_traffic'] = (int) $todayReport['social']['ga']['web_traffic'];
        $overViewData['mob_traffic'] = (int) $todayReport['social']['ga']['mob_traffic'];
        $overViewData['display_add'] = (int) $todayReport['social']['ga']['display_add'];
        $overViewData['display_email'] = (int) $todayReport['social']['ga']['emails'];
        $overViewData['social_media'] = (int) $todayReport['social']['ga']['social_media'];
        $overViewData['others'] = (int) $todayReport['social']['ga']['others'];
        $overViewData['overview_page'] = (int) $todayReport['social']['ga']['overview_page'];
        $overViewData['menu_page'] = (int) $todayReport['social']['ga']['menu_page'];
        $overViewData['story_page'] = (int) $todayReport['social']['ga']['story_page'];
        $overViewData['gallery_page'] = (int) $todayReport['social']['ga']['gallery_page'];
        $overViewData['review_page'] = (int) $todayReport['social']['ga']['review_page'];
        $overViewData['checkout_page'] = (int) $todayReport['social']['ga']['checkout_page'];
        $overViewData['dine_more_page'] = (int) $todayReport['social']['ga']['dine_more'];
        $otherVisit =(int) $overViewData['dine_more_page']+(int) $overViewData['gallery_page'] +(int) $overViewData['menu_page'] + (int)$overViewData['review_page'] + (int)$overViewData['checkout_page'];
        $overViewData['overview_percent'] = number_format($overViewData['overview_page'] / $otherVisit * 100,2);
        $overViewData['menu_percent'] = number_format($overViewData['menu_page'] / $otherVisit * 100,2);
        $overViewData['review_percent'] = number_format($overViewData['review_page'] / $otherVisit * 100,2);
        $overViewData['checkout_percent'] = number_format($overViewData['checkout_page'] / $otherVisit * 100,2);
        $overViewData['dine_more_persent'] = number_format($overViewData['dine_more_page'] / $otherVisit * 100,2);
        $overViewData['gallery_percent'] = number_format($overViewData['gallery_page'] / $otherVisit * 100,2);
        $overViewData['new_percent'] = number_format($overViewData['visit_new_customer'] / $overViewData['ga_users'] * 100);
        $overViewData['returning_percent'] = number_format($overViewData['visit_returing_customer'] / $overViewData['ga_users'] * 100);
       
        
       if($overViewData['visit']>0){
        $overViewData['page_per_visit'] = number_format($overViewData['pageviews'] / $overViewData['visit'], 2);
       }else{
           $overViewData['page_per_visit']=0;
       }
        $overViewData['other_page'] = (int) $overViewData['pageviews'] - $otherVisit;
        $overViewData['other_page_persent'] = number_format($overViewData['other_page'] / $overViewData['pageviews'] * 100);
        $overViewData['other_page_persent']=($overViewData['other_page_persent']>100)?100:$overViewData['other_page_persent'];
//        $overViewData['popular_pages'][0]['page'] = 'Overview';
//        $overViewData['popular_pages'][0]['count'] = $overViewData['overview_page'];
//        $overViewData['popular_pages'][0]['percent'] = $overViewData['overview_percent'];
        $overViewData['popular_pages'][0]['page'] = 'Menu';
        $overViewData['popular_pages'][0]['count'] = $overViewData['menu_page'];
        $overViewData['popular_pages'][0]['percent'] = $overViewData['menu_percent'];
//        $overViewData['popular_pages'][2]['page'] = 'story';
//        $overViewData['popular_pages'][2]['count'] = $overViewData['story_page'];
//        $overViewData['popular_pages'][2]['percent'] = $overViewData['story_percent'];
       
        $overViewData['popular_pages'][1]['page'] = 'Review';
        $overViewData['popular_pages'][1]['count'] = $overViewData['review_page'];
        $overViewData['popular_pages'][1]['percent'] = $overViewData['review_percent'];
        $overViewData['popular_pages'][2]['page'] = 'Checkout';
        $overViewData['popular_pages'][2]['count'] = $overViewData['checkout_page'];
        $overViewData['popular_pages'][2]['percent'] = $overViewData['checkout_percent'];
//        $overViewData['popular_pages'][6]['page'] = 'other';
//        $overViewData['popular_pages'][6]['count'] = $overViewData['other_page'];
//        $overViewData['popular_pages'][6]['percent'] = $overViewData['other_page_persent'];
         $overViewData['popular_pages'][3]['page'] = 'Dine & More';
        $overViewData['popular_pages'][3]['count'] = $overViewData['dine_more_page'];
        $overViewData['popular_pages'][3]['percent'] = $overViewData['dine_more_persent'];
         $overViewData['popular_pages'][4]['page'] = 'Gallery';
        $overViewData['popular_pages'][4]['count'] = $overViewData['gallery_page'];
        $overViewData['popular_pages'][4]['percent'] = $overViewData['gallery_percent'];

        $active = [];
        if (count($overViewData['popular_pages']) > 0) {
            foreach ($overViewData['popular_pages'] as $key => $values) {
                $active[$key] = $values['count'];
            }
        }
        array_multisort($active, SORT_DESC, $overViewData['popular_pages']);

        $end_date = date('Y-m-d', strtotime($currentDate));
        $newdate = strtotime('-12 month', strtotime($end_date));
        $start_date = date('Y-m-d', $newdate);
        $chartCondition = array('date' => array('$gte' => $start_date, '$lte' => $end_date), 'restaurant_id' => (int) $restaurant_id);
        $chartData = $collection->find($chartCondition, array('date' => 1, 'reports.social.ga' => 1))->sort(array('date' => 1));
        $reportChartData = iterator_to_array($chartData, FALSE);
        $varChartRecord = array();
        $varPattranCounter = 0;
        if (count($reportChartData) > 0) {

            foreach ($reportChartData as $key => $vl) {
                foreach ($vl['reports']['social']['ga']['traffic_array'] as $key => $v) {
                    $varChartRecord[$varPattranCounter]['date'] = date("M d", strtotime($v['date']));
                    $varChartRecord[$varPattranCounter]['mob'] = $v['mobile'];
                    $varChartRecord[$varPattranCounter]['web'] = $v['website'];
                    $varPattranCounter++;
                }
            }
            
            $newvarChartRecord = array();
            if (count($varChartRecord) <= 20) {
                $lessTwCounter = 0;
                foreach ($varChartRecord as $key => $v) {
                    $newvarChartRecord[$lessTwCounter]['date'] = $v['date'];
                    $newvarChartRecord[$lessTwCounter]['mob'] = $v['mob'];
                    $newvarChartRecord[$lessTwCounter]['web'] = $v['web'];
                    $lessTwCounter++;
                }
            } else if (count($varChartRecord) <= 90) {
                $lessNintyCounter = 0;
                $mb=0;
                $wb=0;
                foreach ($varChartRecord as $key => $v) {
                    $mb+=(int) $v['mob'];
                    $wb+=(int) $v['web'];
                    if ($key % 4 == 0) {
                        $newvarChartRecord[$lessNintyCounter]['date'] = $v['date'];
                        $newvarChartRecord[$lessNintyCounter]['mob'] =$mb;
                        $newvarChartRecord[$lessNintyCounter]['web'] = $wb;
                        $lessNintyCounter++;
                    }
                }
            } else if (count($varChartRecord) <= 180) {
                $lessoneatiCounter = 0;
                $mb=0;
                $wb=0;
                foreach ($varChartRecord as $key => $v) {
                    $mb+=(int) $v['mob'];
                    $wb+=(int) $v['web'];
                    if ($key % 15 == 0) {
                        $newvarChartRecord[$lessoneatiCounter]['date'] = $v['date'];
                        $newvarChartRecord[$lessoneatiCounter]['mob'] = $mb;
                        $newvarChartRecord[$lessoneatiCounter]['web'] = $wb;
                        $lessoneatiCounter++;
                    }
                }
            } else {
                $lessoneatiCounter = 0;
                $mb=0;
                $wb=0;
                foreach ($varChartRecord as $key => $v) {
                    $mb+=(int) $v['mob'];
                    $wb+=(int) $v['web'];
                    if ($key % 30 == 0) {
                        $newvarChartRecord[$lessoneatiCounter]['date'] = $v['date'];
                        $newvarChartRecord[$lessoneatiCounter]['mob'] = $mb;
                        $newvarChartRecord[$lessoneatiCounter]['web'] = $wb;
                        $lessoneatiCounter++;
                    }
                }
            }
            $varChartRecord = $newvarChartRecord;
        }

        $overViewData['traffic_array'] = $varChartRecord;
        $overViewData['visitUpDown'] = "down_0";
        $overViewData['pagePerVisitUpDown'] = "down_0";
        $overViewData['pageViewsUpDown'] = "down_0";
        $overViewData['newCustomerUpDown'] = "down_0";
        $overViewData['repeatCustomerUpDown'] = "down_0";
        foreach ($overViewData as $key => $val) {
            if ($val < 0) {
                $overViewData[$key] = 0;
            }
        }
        return $overViewData;
    }

    public function order($currentDate, $restaurant_id, $dateRefineToSearch, $collection) {
       
         if ($dateRefineToSearch['start_date'] != '' && $dateRefineToSearch['end_date'] != '') {
             
            return $this->orderWeekMonthData($dateRefineToSearch, $restaurant_id, $collection);
        } else {
           
            return $this->orderAllData($currentDate, $restaurant_id, $collection);
        }
    }

    public function orderWeekMonthData($dateRefineToSearch, $restaurant_id, $collection){
        
       $condition = array('date' => $dateRefineToSearch['end_date'], 'restaurant_id' => (int) $restaurant_id);
        $currentDateData = $collection->find($condition);
        $reportCurrentTodayDate = iterator_to_array($currentDateData, FALSE);
        if (count($reportCurrentTodayDate) == 0) {
            throw new \Exception('Data not found.');
        }

        $conditionStartDate = array('date' => $dateRefineToSearch['start_date'], 'restaurant_id' => (int) $restaurant_id);
        $lastStartDate = $collection->find($conditionStartDate);
        $reportCurrentWeekendDate = iterator_to_array($lastStartDate, FALSE);
        $todayReport = $reportCurrentTodayDate[0]['reports'];
        $LastReport = $reportCurrentWeekendDate[0]['reports'];
        
        $chartCondition = array('date' => array('$gte' => $dateRefineToSearch['start_date'], '$lte' => $dateRefineToSearch['end_date']), 'restaurant_id' =>(int) $restaurant_id);
      
                    
                    $chartData = $collection->find($chartCondition, array('date' => 1, 'reports.munchado.orders' => 1))->sort(array('date' => 1));
                    $reportChartData = iterator_to_array($chartData, FALSE);
                   
                    
                    $varChartRecord = array();
                    $varPattranCounter=0;
                    if (count($reportChartData) > 0) { 
                        foreach ($reportChartData as $key => $vl) { 
                                   $varChartRecord[$varPattranCounter]['date'] = date("M d", strtotime($vl['date']));
                                   $varChartRecord[$varPattranCounter]['ordersCount'] = count($vl['reports']['munchado']['orders']['order_data']); 
                                   $varPattranCounter++;
                        }
//                        $newvarChartRecord=array();
//                       if(count($varChartRecord) <= 20){
//                                $lessTwCounter=0;    
//                                foreach($varChartRecord as $key=>$v){
//                                   $newvarChartRecord[$lessTwCounter]['date'] = $v['date'];
//                                   $newvarChartRecord[$lessTwCounter]['ordersCount'] = $v['ordersCount']; 
//                                   $lessTwCounter++; 
//                                }
//                                }else if(count($varChartRecord) <= 90){
//                                    $lessNintyCounter=0; 
//                                   foreach($varChartRecord as $key=>$v){
//                                     if($key%4==0){  
//                                        $newvarChartRecord[$lessNintyCounter]['date'] =$v['date'];
//                                        $newvarChartRecord[$lessNintyCounter]['ordersCount'] = $v['ordersCount']; 
//                                        $lessNintyCounter++;
//                                     }
//                                }
//                                }else if(count($varChartRecord) <= 180){
//                                    $lessoneatiCounter=0; 
//                                   foreach($varChartRecord as $key=>$v){
//                                     if($key%15==0){  
//                                        $newvarChartRecord[$lessoneatiCounter]['date'] =$v['date'];
//                                        $newvarChartRecord[$lessoneatiCounter]['ordersCount'] = $v['ordersCount']; 
//                                        $lessoneatiCounter++;
//                                     }
//                                }
//                                }else{
//                                  $lessoneatiCounter=0; 
//                                   foreach($varChartRecord as $key=>$v){
//                                     if($key%30==0){  
//                                       $newvarChartRecord[$lessoneatiCounter]['date'] =$v['date'];
//                                        $newvarChartRecord[$lessoneatiCounter]['ordersCount'] = $v['ordersCount'];  
//                                        $lessoneatiCounter++;
//                                     }
//                                }  
//                                }
                    }
                   // $varChartRecord=$newvarChartRecord;
                 $overViewData['ga_users'] =(int) $todayReport['social']['ga']['users'] - (int) $LastReport['social']['ga']['users'];
                $overViewData['total_customers'] = (int) $todayReport['munchado']['orders']['order_alltime']['total_customers'] - (int) $LastReport['munchado']['orders']['order_alltime']['total_customers'];
                $overViewData['total_orders'] = (int) $todayReport['munchado']['orders']['order_alltime']['total_orders'] - (int) $LastReport['munchado']['orders']['order_alltime']['total_orders'];
                $overViewData['order_new_customers'] =(int) $todayReport['munchado']['orders']['order_alltime']['new_customers'] - (int) $LastReport['munchado']['orders']['order_alltime']['new_customers'];
                $overViewData['order_returning_customers'] =(int) $todayReport['munchado']['orders']['order_alltime']['returning_customers'] - (int) $LastReport['munchado']['orders']['order_alltime']['returning_customers'];
                $overViewData['total_revenue'] = $todayReport['munchado']['orders']['order_alltime']['total_revenue'] - $LastReport['munchado']['orders']['order_alltime']['total_revenue'];
                if($overViewData['total_orders']>0){
                $overViewData['avg_order_val'] = number_format($overViewData['total_revenue'] / $overViewData['total_orders'], 2);
                }else{
                $overViewData['avg_order_val'] =0;
                }
                if($overViewData['ga_users']>0){
                $overViewData['order_conversion'] = number_format($overViewData['total_orders']/$overViewData['ga_users'] * 100, 2);
                }else{
                $overViewData['order_conversion'] =0;
                }
                $overViewData['item_per_order'] = number_format($todayReport['munchado']['orders']['order_alltime']['avg_item'],2);
                $overViewData['abandant_cart'] = (int) $todayReport['munchado']['abandant_cart']['total'] - (int) $LastReport['munchado']['abandant_cart']['total'];
                $overViewData['order_delivery'] =(int) $todayReport['munchado']['orders']['order_alltime']['delivery'] -(int) $LastReport['munchado']['orders']['order_alltime']['delivery'];
                $overViewData['order_takeout'] =(int) $todayReport['munchado']['orders']['order_alltime']['takeout'] -(int) $LastReport['munchado']['orders']['order_alltime']['takeout'];
                $overViewData['total_members'] =(int) $todayReport['munchado']['dineandmore']['dinemore_alltime']['total_members'] -(int) $LastReport['munchado']['dineandmore']['dinemore_alltime']['total_members'];
                $overViewData['total_orders_members'] =(int) $todayReport['munchado']['dineandmore']['dinemore_alltime']['total_orders_members'] -(int) $LastReport['munchado']['dineandmore']['dinemore_alltime']['total_orders_members'];
                $overViewData['total_orders_normal_users'] =(int) $todayReport['munchado']['dineandmore']['dinemore_alltime']['total_orders_normal_users'] -(int) $LastReport['munchado']['dineandmore']['dinemore_alltime']['total_orders_normal_users'];
                
                $overViewData['reservation_seats'] = (int) $todayReport['munchado']['reservations']['reservation_alltime']['total_seat'] - (int) $LastReport['munchado']['reservations']['reservation_alltime']['total_seat'];
                $overViewData['order_pattern'] = $varChartRecord;
                $todayReport['munchado']['orders']['most_popular_items']= array_slice($todayReport['munchado']['orders']['most_popular_items'],0,15);
                if (count($todayReport['munchado']['orders']['most_popular_items']) > 0) {
                    $totalSoldItem=0;
                    foreach ($todayReport['munchado']['orders']['most_popular_items'] as $key => $popItems) {
                    $totalSoldItem+=$popItems['total_items'];    
                    }
                    foreach ($todayReport['munchado']['orders']['most_popular_items'] as $key => $popItems) {
                        if($todayReport['munchado']['orders']['order_alltime']['total_orders']>0){
                        $todayReport['munchado']['orders']['most_popular_items'][$key]['percent'] = number_format($popItems['total_items'] / $totalSoldItem * 100,2);
                        }else{
                        $todayReport['munchado']['orders']['most_popular_items'][$key]['percent'] =0;    
                        }
                        $todayReport['munchado']['orders']['most_popular_items'][$key]['item'] =  html_entity_decode($popItems['item']);
                    }
                }
                $overViewData['most_popular_items'] = $todayReport['munchado']['orders']['most_popular_items'];

                if ($overViewData['total_customers'] > 0) {
            $overViewData['chart_new_customers'] = number_format($overViewData['order_new_customers'] / $overViewData['total_customers'] * 100);
            $overViewData['chart_returning_customers'] = number_format($overViewData['order_returning_customers'] / $overViewData['total_customers'] * 100);
        } else {
            $overViewData['chart_new_customers'] = 0;
            $overViewData['chart_returning_customers'] = 0;
        }
               if($overViewData['total_orders']>0){
        $overViewData['chart_takeout'] = number_format($overViewData['order_takeout'] / $overViewData['total_orders'] * 100);
        $overViewData['chart_delivery'] = number_format($overViewData['order_delivery'] / $overViewData['total_orders'] * 100);
        }else{
        $overViewData['chart_takeout'] = 0;
        $overViewData['chart_delivery'] = 0;
        }
                if($overViewData['total_members']>0){
        $overViewData['chart_normal_user'] = number_format($overViewData['total_members'] / $overViewData['total_orders_normal_users'] * 100);
        $overViewData['chart_member'] = number_format($overViewData['total_members'] / $overViewData['total_orders_members'] * 100);
        }else{
         $overViewData['chart_normal_user'] = 0;
        $overViewData['chart_member'] = 0;   
        }
                $overViewData['order_new_customers'] =(int) $todayReport['munchado']['orders']['order_alltime']['new_customers'] -(int) $LastReport['munchado']['orders']['order_alltime']['new_customers'];
                $overViewData['order_returning_customers'] =(int) $todayReport['munchado']['orders']['order_alltime']['returning_customers'] -(int) $LastReport['munchado']['orders']['order_alltime']['returning_customers'];
                foreach($overViewData as $key=>$val){ 
                    if($val<0){
                      $overViewData[$key]=0;  
                    }
                } 
        // get last week all data    
        $conditionLastweekEndDate = array('date' => $dateRefineToSearch['last_end_date'], 'restaurant_id' =>(int) $restaurant_id);
        $lastweekEndDate = $collection->find($conditionLastweekEndDate);
        $reportLastweekEndDate = iterator_to_array($lastweekEndDate, FALSE);

        
        $conditionLastweekStartDate = array('date' => $dateRefineToSearch['last_start_date'], 'restaurant_id' =>(int) $restaurant_id);
        $lastweekStartDate = $collection->find($conditionLastweekStartDate);
        $reportLastweekStartDate = iterator_to_array($lastweekStartDate, FALSE);

        $todayReport = $reportLastweekEndDate[0]['reports'];
        $LastReport = $reportLastweekStartDate[0]['reports'];
        $lastOverviewData['ga_users'] = (int) $todayReport['social']['ga']['users'] - (int) $LastReport['social']['ga']['users'];
        $lastOverviewData['total_customers'] = (int) $todayReport['munchado']['orders']['order_alltime']['total_customers'] - (int) $LastReport['munchado']['orders']['order_alltime']['total_customers'];
        $lastOverviewData['total_orders'] = (int) $todayReport['munchado']['orders']['order_alltime']['total_orders'] - (int) $LastReport['munchado']['orders']['order_alltime']['total_orders'];
        $lastOverviewData['order_new_customers'] =(int) $todayReport['munchado']['orders']['order_alltime']['new_customers'] -(int) $LastReport['munchado']['orders']['order_alltime']['new_customers'];
        $lastOverviewData['order_returning_customers'] =(int) $todayReport['munchado']['orders']['order_alltime']['returning_customers'] -(int) $LastReport['munchado']['orders']['order_alltime']['returning_customers'];
        $lastOverviewData['total_revenue'] = $todayReport['munchado']['orders']['order_alltime']['total_revenue'] - $LastReport['munchado']['orders']['order_alltime']['total_revenue'];
        if($lastOverviewData['total_orders']>0){
        $lastOverviewData['avg_order_val'] = number_format($lastOverviewData['total_revenue'] / $lastOverviewData['total_orders'], 2);
        }else{
        $lastOverviewData['avg_order_val'] =0;    
        }
        if($lastOverviewData['ga_users']>0){
         $lastOverviewData['order_conversion'] = number_format($lastOverviewData['total_orders'] / $lastOverviewData['ga_users'] * 100, 2);   
        }else{
         $lastOverviewData['order_conversion'] = 0;
        }
        
        $lastOverviewData['item_per_order'] =  number_format($todayReport['munchado']['orders']['order_alltime']['avg_item'],2);
        $lastOverviewData['abandant_cart'] = (int) $todayReport['munchado']['abandant_cart']['total'] - (int) $LastReport['munchado']['abandant_cart']['total'];
        $lastOverviewData['reservation_seats'] = (int) $todayReport['munchado']['reservations']['reservation_alltime']['total_seat'] - (int) $LastReport['munchado']['reservations']['reservation_alltime']['total_seat'];
        
        $overViewData['orderUpDown'] = self::getPercentVal($overViewData['total_orders'], $lastOverviewData['total_orders']);
        $overViewData['revenueUpDown'] = self::getPercentVal($overViewData['total_revenue'], $lastOverviewData['total_revenue']);
        $overViewData['orderNewuserUpDown'] = self::getPercentVal($overViewData['order_new_customers'], $lastOverviewData['order_new_customers']);
        $overViewData['orderRepeatCustomerUpDown'] = self::getPercentVal($overViewData['order_returning_customers'], $lastOverviewData['order_returning_customers']);
        $overViewData['avgOrderValUpDown'] = self::getPercentVal($overViewData['avg_order_val'], $lastOverviewData['avg_order_val']);
        $overViewData['orderConversionUpDown'] = self::getPercentVal($overViewData['order_conversion'], $lastOverviewData['order_conversion']);
        $overViewData['itemPerOrderUpDown'] = self::getPercentVal($overViewData['item_per_order'], $lastOverviewData['item_per_order']);
        $overViewData['abandantCartUpDown'] = self::getPercentVal($overViewData['abandant_cart'], $lastOverviewData['abandant_cart']);
        $overViewData['seatsUpDown'] = self::getPercentVal($overViewData['reservation_seats'], $lastOverviewData['reservation_seats']); 
        return $overViewData;
        
    }

    public function orderAllData($currentDate, $restaurant_id, $collection){
        $condition = array('restaurant_id' => (int) $restaurant_id);
        
        $currentDateData = $collection->find($condition)->sort(array('date' =>-1));
        $reportCurrentTodayDate = iterator_to_array($currentDateData, FALSE);
        if (count($reportCurrentTodayDate) == 0) {
            throw new \Exception('Data not found.');
        }
        //pr($reportCurrentTodayDate,1);
        $todayReport = $reportCurrentTodayDate[0]['reports'];
        $overViewData['ga_users'] = (int) $todayReport['social']['ga']['users'];
        $overViewData['total_customers'] = (int) $todayReport['munchado']['orders']['order_alltime']['total_customers'];
        $overViewData['total_orders'] = (int) $todayReport['munchado']['orders']['order_alltime']['total_orders'];
        $overViewData['order_new_customers'] =(int) $todayReport['munchado']['orders']['order_alltime']['new_customers'];
        $overViewData['order_returning_customers'] =(int) $todayReport['munchado']['orders']['order_alltime']['returning_customers'];
        $overViewData['total_revenue'] = $todayReport['munchado']['orders']['order_alltime']['total_revenue'];
        if($overViewData['total_orders']>0){
        $overViewData['avg_order_val'] = number_format($overViewData['total_revenue'] / $overViewData['total_orders'], 2);
        }else{
        $overViewData['avg_order_val'] = 0;    
        }
        if($overViewData['ga_users']>0){
        $overViewData['order_conversion'] = number_format($overViewData['total_orders']/$overViewData['ga_users'] * 100, 2);
        }else{
        $overViewData['order_conversion'] =0;
        }
        $overViewData['item_per_order'] = number_format($todayReport['munchado']['orders']['order_alltime']['avg_item'],2);
        $overViewData['abandant_cart'] = (int) $todayReport['munchado']['abandant_cart']['total'];
        $overViewData['order_delivery'] = (int) $todayReport['munchado']['orders']['order_alltime']['delivery'];
        $overViewData['order_takeout'] = (int) $todayReport['munchado']['orders']['order_alltime']['takeout'];
        $overViewData['total_members'] = $todayReport['munchado']['dineandmore']['dinemore_alltime']['total_members'];
        $overViewData['total_orders_members'] = (int) $todayReport['munchado']['dineandmore']['dinemore_alltime']['total_orders_members'];
        $overViewData['total_orders_normal_users'] =(int) $todayReport['munchado']['dineandmore']['dinemore_alltime']['total_orders_normal_users'];
        $overViewData['success_orders'] = (int) $todayReport['munchado']['orders']['order_alltime']['success_orders'];
        if($overViewData['total_customers']>0){
        $overViewData['chart_new_customers'] = number_format($overViewData['order_new_customers'] / $overViewData['total_customers'] * 100);
        $overViewData['chart_returning_customers'] = number_format($overViewData['order_returning_customers'] / $overViewData['total_customers'] * 100);
        }else{
        $overViewData['chart_new_customers'] = 0;
        $overViewData['chart_returning_customers'] = 0;    
        }
        if($overViewData['total_orders']>0){
        $overViewData['chart_takeout'] = number_format($overViewData['order_takeout'] / $overViewData['total_orders'] * 100);
        $overViewData['chart_delivery'] = number_format($overViewData['order_delivery'] / $overViewData['total_orders'] * 100);
        }else{
        $overViewData['chart_takeout'] = 0;
        $overViewData['chart_delivery'] = 0;
        }
        if($overViewData['total_members']>0){
        $overViewData['chart_normal_user'] = number_format($overViewData['total_members'] / $overViewData['total_orders_normal_users'] * 100);
        $overViewData['chart_member'] = number_format($overViewData['total_members'] / $overViewData['total_orders_members'] * 100);
        }else{
         $overViewData['chart_normal_user'] = 0;
        $overViewData['chart_member'] = 0;   
        }
        $overViewData['reservation_seats'] = (int) $todayReport['munchado']['reservations']['reservation_alltime']['total_seat'];
        $overViewData['orderUpDown'] = "down_0";
        $overViewData['revenueUpDown'] = "down_0";
        $overViewData['orderNewuserUpDown'] = "down_0";
        $overViewData['orderRepeatCustomerUpDown'] = "down_0";
        $overViewData['avgOrderValUpDown'] = "down_0";
        $overViewData['orderConversionUpDown'] = "down_0";
        $overViewData['itemPerOrderUpDown'] = "down_0";
        $overViewData['abandantCartUpDown'] = "down_0";
        $overViewData['seatsUpDown']="down_0";

        $end_date = date('Y-m-d',  strtotime($currentDate));
        $newdate = strtotime('-12 month', strtotime($end_date));
        $start_date = date('Y-m-d', $newdate);
        $chartCondition = array('date' => array('$gte' => $start_date, '$lte' => $end_date), 'restaurant_id' =>(int) $restaurant_id);
        $chartData = $collection->find($chartCondition, array('date' => 1, 'reports.munchado.orders' => 1))->sort(array('date' => 1));
        $reportChartData = iterator_to_array($chartData, FALSE);
        
        $varChartRecord = array();
                    $varPattranCounter=0;
                    if (count($reportChartData) > 0) { 
                        foreach ($reportChartData as $key => $vl) { 
                                   $varChartRecord[$varPattranCounter]['date'] = date("M d", strtotime($vl['date']));
                                   $varChartRecord[$varPattranCounter]['ordersCount'] = count($vl['reports']['munchado']['orders']['order_data']); 
                                   $varPattranCounter++;
                        }
//                        $newvarChartRecord=array();
//                       if(count($varChartRecord) <= 20){
//                                $lessTwCounter=0;    
//                                foreach($varChartRecord as $key=>$v){
//                                   $newvarChartRecord[$lessTwCounter]['date'] = $v['date'];
//                                   $newvarChartRecord[$lessTwCounter]['ordersCount'] = $v['ordersCount']; 
//                                   $lessTwCounter++; 
//                                }
//                                }else if(count($varChartRecord) <= 90){
//                                    $lessNintyCounter=0; 
//                                   foreach($varChartRecord as $key=>$v){
//                                     if($key%4==0){  
//                                        $newvarChartRecord[$lessNintyCounter]['date'] =$v['date'];
//                                        $newvarChartRecord[$lessNintyCounter]['ordersCount'] = $v['ordersCount']; 
//                                        $lessNintyCounter++;
//                                     }
//                                }
//                                }else if(count($varChartRecord) <= 180){
//                                    $lessoneatiCounter=0; 
//                                   foreach($varChartRecord as $key=>$v){
//                                     if($key%15==0){  
//                                        $newvarChartRecord[$lessoneatiCounter]['date'] =$v['date'];
//                                        $newvarChartRecord[$lessoneatiCounter]['ordersCount'] = $v['ordersCount']; 
//                                        $lessoneatiCounter++;
//                                     }
//                                }
//                                }else{
//                                  $lessoneatiCounter=0; 
//                                   foreach($varChartRecord as $key=>$v){
//                                     if($key%30==0){  
//                                       $newvarChartRecord[$lessoneatiCounter]['date'] =$v['date'];
//                                        $newvarChartRecord[$lessoneatiCounter]['ordersCount'] = $v['ordersCount'];  
//                                        $lessoneatiCounter++;
//                                     }
//                                }  
//                                }
                    }
                    $varChartRecord=$varChartRecord;
        $overViewData['order_pattern'] = $varChartRecord;
        $todayReport['munchado']['orders']['most_popular_items']= array_slice($todayReport['munchado']['orders']['most_popular_items'],0,15);
        if (count($todayReport['munchado']['orders']['most_popular_items']) > 0) {
            $totalSoldItem=0;
                    foreach ($todayReport['munchado']['orders']['most_popular_items'] as $key => $popItems) {
                    $totalSoldItem+=$popItems['total_items'];    
                    }
            foreach ($todayReport['munchado']['orders']['most_popular_items'] as $key => $popItems) {
                if($todayReport['munchado']['orders']['order_alltime']['total_orders']>0){
                $todayReport['munchado']['orders']['most_popular_items'][$key]['percent'] = number_format($popItems['total_items'] / $totalSoldItem * 100,2);
                }else{
                $todayReport['munchado']['orders']['most_popular_items'][$key]['percent'] =0;    
                }
                $todayReport['munchado']['orders']['most_popular_items'][$key]['item'] =  html_entity_decode($popItems['item']);
            }
        }
        $overViewData['most_popular_items'] = $todayReport['munchado']['orders']['most_popular_items'];
        return $overViewData;
    }

    public function engagment($currentDate, $restaurant_id, $dateRefineToSearch, $collection) {
        if ($dateRefineToSearch['start_date'] != '' && $dateRefineToSearch['end_date'] != '') {
            return $this->engagmentWeekMonthData($dateRefineToSearch, $restaurant_id, $collection);
        } else {
            return $this->engagmentAllData($currentDate, $restaurant_id, $collection);
        }
    }

    public function engagmentWeekMonthData($dateRefineToSearch, $restaurant_id, $collection){
      $condition = array('date' => $dateRefineToSearch['end_date'], 'restaurant_id' => (int) $restaurant_id);
        $currentDateData = $collection->find($condition);
        $reportCurrentTodayDate = iterator_to_array($currentDateData, FALSE);
        if (count($reportCurrentTodayDate) == 0) {
            throw new \Exception('Data not found.');
        }

        $conditionStartDate = array('date' => $dateRefineToSearch['start_date'], 'restaurant_id' => (int) $restaurant_id);
        $lastStartDate = $collection->find($conditionStartDate);
        $reportCurrentWeekendDate = iterator_to_array($lastStartDate, FALSE);
        $todayReport = $reportCurrentTodayDate[0]['reports'];
        $LastReport = $reportCurrentWeekendDate[0]['reports']; 
        
         $conditionStartDate = array('date' => $dateRefineToSearch['end_date'], 'restaurant_id' =>(int) $restaurant_id);
        
        $chartData = $collection->find($conditionStartDate, array('date' => 1, 'reports.social.facebook.most_popular_content' => 1))->sort(array('date' => 1));
        $reportChartData = iterator_to_array($chartData, FALSE);
        $populatContent=array(); 
        if(count($reportChartData)> 0 && !empty($reportChartData)){
            foreach($reportChartData as $key=>$vl){
                foreach($vl['reports']['social']['facebook']['most_popular_content'] as $key1=>$vl){
                  $populatContent[]=  $vl;
                  $populatContent[$key1]['date_time']=gmdate("d F Y", strtotime($vl['date_time']));
                }
                
            }
        }
        
       
        $conditionStartDate = array('date' => $dateRefineToSearch['end_date'], 'restaurant_id' =>(int) $restaurant_id);
        
        $chartData = $collection->find($conditionStartDate, array('date' => 1, 'reports.social.instagram.most_popular_content' => 1))->sort(array('date' => 1));
        $reportChartData = iterator_to_array($chartData, FALSE);
        $populatInsagramContent=array(); 
        if(count($reportChartData)> 0 && !empty($reportChartData)){
            foreach($reportChartData as $key=>$vl){
                if(isset($vl['reports']['social']['instagram']['most_popular_content']) && count($vl['reports']['social']['instagram']['most_popular_content']) >0 && !empty($vl['reports']['social']['instagram']['most_popular_content'])){
                    foreach($vl['reports']['social']['instagram']['most_popular_content'] as $key=>$vl){
                      $populatInsagramContent[]=  $vl;
                    }
                }
            }
        }
        
        
        $populatContent=  array_merge($populatContent,$populatInsagramContent);
        
        $overViewData['message_sent'] = (int) $todayReport['munchado']['emails']['message_sent'] - (int) $LastReport['munchado']['emails']['message_sent'];
                $overViewData['people_reached'] = 0;
                $overViewData['twitter_followers'] = (int) ($todayReport['social']['twitter']['followers']) - (int) ($LastReport['social']['twitter']['followers']);
                $overViewData['instagram_followers'] = (int) ($todayReport['social']['instagram']['followers']) - (int) ($LastReport['social']['instagram']['followers']);
                $overViewData['fb_followers'] = (int) ($todayReport['social']['facebook']['followers']) - (int) ($LastReport['social']['facebook']['followers']);
                $overViewData['followers']=$overViewData['instagram_followers']+$overViewData['fb_followers'];
                $overViewData['avg_time_profile'] = $todayReport['social']['ga']['avg_time_profile'];
                $overViewData['fb_people_reached'] = (int) ($todayReport['social']['facebook']['people_reached']) - (int) ($LastReport['social']['facebook']['people_reached']);
                $overViewData['fb_review_comment'] = (int) ($todayReport['social']['facebook']['comments']) - (int) ($LastReport['social']['facebook']['comments']);
                $overViewData['review'] =(int) ($todayReport['munchado']['review']['review_alltime']['total_reviews']) - (int) ($LastReport['munchado']['review']['review_alltime']['total_reviews']);
                $overViewData['instagram_comment'] = (int) ($todayReport['social']['instagram']['comments']) - (int) ($LastReport['social']['instagram']['comments']);
                $overViewData['review']= (int) $overViewData['fb_review_comment']+$overViewData['review']+$overViewData['instagram_comment'];
                $overViewData['fb_message'] = (int) ($todayReport['social']['facebook']['messages']) - (int) ($LastReport['social']['facebook']['messages']);
                $overViewData['fb_most_comment'] = (int) ($todayReport['social']['facebook']['most_commented']) - (int) ($LastReport['social']['facebook']['most_commented']);
                $overViewData['fb_most_like'] = (int) ($todayReport['social']['facebook']['most_liked']) - (int) ($LastReport['social']['facebook']['most_liked']);
                $overViewData['fb_rating'] = (int) ($todayReport['social']['facebook']['rating']) - (int) ($LastReport['social']['facebook']['rating']);
                $overViewData['instagram_people_reached'] = (int) ($todayReport['social']['instagram']['people_reached']) - (int) ($LastReport['social']['instagram']['people_reached']);
                $overViewData['instagram_most_comment'] =(int) ($todayReport['social']['instagram']['most_commented']) - (int) ($LastReport['social']['instagram']['most_commented']);
                $overViewData['instagram_most_like'] = (int) ($todayReport['social']['instagram']['most_liked']) - (int) ($LastReport['social']['instagram']['most_liked']);
                $overViewData['email_sent'] = (int) ($todayReport['munchado']['emails']['total_mails_sent']) - (int) ($LastReport['munchado']['emails']['total_mails_sent']);
                $overViewData['email_open'] = (int) ($todayReport['munchado']['emails']['total_mails_opened']) - (int) ($LastReport['munchado']['emails']['total_mails_opened']);
                $overViewData['email_click'] = (int) ($todayReport['munchado']['emails']['total_mails_clicked']) - (int) ($LastReport['munchado']['emails']['total_mails_clicked']);
                $overViewData['popularcontent'] = $populatContent;
                
                foreach($overViewData as $key=>$val){ 
                    if($val<0){
                      $overViewData[$key]=0;  
                    }
                } 
                
                // get last date all data
         
        $conditionLastweekEndDate = array('date' => $dateRefineToSearch['last_end_date'], 'restaurant_id' =>(int) $restaurant_id);
        $lastweekEndDate = $collection->find($conditionLastweekEndDate);
        $reportLastweekEndDate = iterator_to_array($lastweekEndDate, FALSE);

        // get last week all data
        $conditionLastweekStartDate = array('date' => $dateRefineToSearch['last_start_date'], 'restaurant_id' =>(int) $restaurant_id);
        $lastweekStartDate = $collection->find($conditionLastweekStartDate);
        $reportLastweekStartDate = iterator_to_array($lastweekStartDate, FALSE);

        $todayReport = $reportLastweekEndDate[0]['reports'];
        $LastReport = $reportLastweekStartDate[0]['reports'];
        $lastOverviewData['message_sent'] = (int) $todayReport['munchado']['emails']['message_sent'] - (int) $LastReport['munchado']['emails']['message_sent'];
        $lastOverviewData['people_reached'] = 0;
        $lastOverviewData['twitter_followers'] = (int) ($todayReport['social']['twitter']['followers']) - (int) ($LastReport['social']['twitter']['followers']);
        $lastOverviewData['instagram_followers'] = (int) ($todayReport['social']['instagram']['followers']) - (int) ($LastReport['social']['instagram']['followers']);
        $lastOverviewData['fb_followers'] = (int) ($todayReport['social']['facebook']['followers']) - (int) ($LastReport['social']['facebook']['followers']);
        $lastOverviewData['followers'] = (int) $lastOverviewData['fb_followers'] + $lastOverviewData['instagram_followers'];
        $lastOverviewData['review'] = (int) ($todayReport['munchado']['review']['review_alltime']['total_reviews']) - (int) ($LastReport['munchado']['review']['review_alltime']['total_reviews']);
        $lastOverviewData['avg_time_profile'] = $todayReport['social']['ga']['avg_time_profile'];
        $lastOverviewData['fb_people_reached'] = (int) ($todayReport['social']['facebook']['people_reached']) - (int) ($LastReport['social']['facebook']['people_reached']);
        $lastOverviewData['fb_review_comment'] = (int) ($todayReport['social']['facebook']['comments']) - (int) ($LastReport['social']['facebook']['comments']);
        $lastOverviewData['fb_message'] = (int) ($todayReport['social']['facebook']['messages']) - (int) ($LastReport['social']['facebook']['messages']);
        $lastOverviewData['fb_most_comment'] = (int) ($todayReport['social']['facebook']['most_commented']) - (int) ($LastReport['social']['facebook']['most_commented']);
        $lastOverviewData['fb_most_like'] = (int) ($todayReport['social']['facebook']['most_liked']) - (int) ($LastReport['social']['facebook']['most_liked']);
        $lastOverviewData['instagram_followers'] = (int) ($todayReport['social']['instagram']['followers']) - (int) ($LastReport['social']['instagram']['followers']);
        $lastOverviewData['instagram_people_reached'] = (int) ($todayReport['social']['instagram']['people_reached']) - (int) ($LastReport['social']['instagram']['people_reached']);
        $lastOverviewData['instagram_comment'] = (int) ($todayReport['social']['instagram']['comments']) - (int) ($LastReport['social']['instagram']['comments']);
        $lastOverviewData['review'] =$lastOverviewData['instagram_comment']+$lastOverviewData['fb_review_comment']+$lastOverviewData['review'];
        $lastOverviewData['instagram_most_comment'] = (int) ($todayReport['social']['instagram']['most_commented']) - (int) ($LastReport['social']['instagram']['most_commented']);
        $lastOverviewData['instagram_most_like'] = (int) ($todayReport['social']['instagram']['most_liked']) - (int) ($LastReport['social']['instagram']['most_liked']);
        $lastOverviewData['email_sent'] = (int) ($todayReport['munchado']['emails']['total_mails_sent']) - (int) ($LastReport['munchado']['emails']['total_mails_sent']);
        $lastOverviewData['email_open'] = (int) ($todayReport['munchado']['emails']['total_mails_opened']) - (int) ($LastReport['munchado']['emails']['total_mails_opened']);
        $lastOverviewData['email_click'] = (int) ($todayReport['munchado']['emails']['total_mails_clicked']) - (int) ($LastReport['munchado']['emails']['total_mails_clicked']);
        $overViewData['messagesentUpDown'] = self::getPercentVal($overViewData['message_sent'], $lastOverviewData['message_sent']);
        $overViewData['peoplereachedUpDown'] = self::getPercentVal($overViewData['people_reached'], $lastOverviewData['people_reached']);
        $overViewData['followersUpDown'] = self::getPercentVal($overViewData['followers'], $lastOverviewData['followers']);
        $overViewData['reviewUpDown'] = self::getPercentVal($overViewData['review'], $lastOverviewData['review']);
        $overViewData['avgtimeprofileUpDown'] = self::getPercentVal($overViewData['avg_time_profile'], $lastOverviewData['avg_time_profile']);
        $overViewData['fbfollowersUpDown'] = self::getPercentVal($overViewData['fb_followers'], $lastOverviewData['fb_followers']);
        $overViewData['fbpeoplereachedUpDown'] = self::getPercentVal($overViewData['fb_people_reached'], $lastOverviewData['fb_people_reached']);
        $overViewData['fbreviewcommentUpDown'] = self::getPercentVal($overViewData['fb_review_comment'], $lastOverviewData['fb_review_comment']);
        $overViewData['fbmessageUpDown'] = self::getPercentVal($overViewData['fb_message'], $lastOverviewData['fb_message']);
        $overViewData['fbmostcommentUpDown'] = self::getPercentVal($overViewData['fb_most_comment'], $lastOverviewData['fb_most_comment']);
        $overViewData['fbmostlikeUpDown'] = self::getPercentVal($overViewData['fb_most_like'], $lastOverviewData['fb_most_like']);
        $overViewData['instagramfollowersUpDown'] = self::getPercentVal($overViewData['instagram_followers'], $lastOverviewData['instagram_followers']);
        $overViewData['instagrampeoplereachedUpDown'] = self::getPercentVal($overViewData['instagram_people_reached'], $lastOverviewData['instagram_people_reached']);
        $overViewData['instagramcommentUpDown'] = self::getPercentVal($overViewData['instagram_comment'], $lastOverviewData['instagram_comment']);
        $overViewData['instagrammostcommentUpDown'] = self::getPercentVal($overViewData['instagram_most_comment'], $lastOverviewData['instagram_most_comment']);
        $overViewData['instagrammostlikeUpDown'] = self::getPercentVal($overViewData['instagram_most_like'], $lastOverviewData['instagram_most_like']);
        $overViewData['emailsentUpDown'] = self::getPercentVal($overViewData['email_sent'], $lastOverviewData['email_sent']);
        $overViewData['emailopenUpDown'] = self::getPercentVal($overViewData['email_open'], $lastOverviewData['email_open']);
        $overViewData['emailclickUpDown'] = self::getPercentVal($overViewData['email_click'], $lastOverviewData['email_click']);
        return $overViewData;        
    }

    public function engagmentAllData($currentDate, $restaurant_id, $collection){
       $condition = array('restaurant_id' => (int) $restaurant_id);
        $currentDateData = $collection->find($condition)->sort(array('date' =>-1));
        $reportCurrentTodayDate = iterator_to_array($currentDateData, FALSE);
        if (count($reportCurrentTodayDate) == 0) {
            throw new \Exception('Data not found.');
        }
        //pr($reportCurrentTodayDate,1);
        $todayReport = $reportCurrentTodayDate[0]['reports'];  
        $overViewData['message_sent'] = (int) $todayReport['munchado']['emails']['message_sent'];
                $overViewData['people_reached'] = (int) $todayReport['social']['engagement']['people_reached'];
                $overViewData['twitter_followers'] = (int) ($todayReport['social']['twitter']['followers']);
                $overViewData['instagram_followers'] = (int) ($todayReport['social']['instagram']['followers']);
                $overViewData['fb_followers'] = (int) ($todayReport['social']['facebook']['followers']);
                $overViewData['followers'] =$overViewData['instagram_followers']+$overViewData['fb_followers'];
                $overViewData['fb_review_comment'] = (int) ($todayReport['social']['facebook']['comments']);
                $overViewData['instagram_comment'] = (int) ($todayReport['social']['instagram']['comments']);
                
                $overViewData['review'] = (int) ($todayReport['munchado']['review']['review_alltime']['total_reviews'])+$overViewData['fb_review_comment']+$overViewData['instagram_comment'];
                $overViewData['avg_time_profile'] = $todayReport['social']['ga']['avg_time_profile'];
                $overViewData['fb_people_reached'] = (int) ($todayReport['social']['facebook']['people_reached']);
                
                $overViewData['fb_message'] = (int) ($todayReport['social']['facebook']['messages']);
                $overViewData['fb_most_comment'] = (int) ($todayReport['social']['facebook']['most_commented']);
                $overViewData['fb_most_like'] = (int) ($todayReport['social']['facebook']['most_liked']);
                $overViewData['fb_rating'] = (int) ($todayReport['social']['facebook']['rating']);
                $overViewData['instagram_people_reached'] = (int) ($todayReport['social']['instagram']['people_reached']);
                $overViewData['instagram_most_comment'] = (int) ($todayReport['social']['instagram']['most_commented']);
                $overViewData['instagram_most_like'] = (int) ($todayReport['social']['instagram']['most_liked']);
                $overViewData['email_sent'] = (int) ($todayReport['munchado']['emails']['total_mails_sent']);
                $overViewData['email_open'] = (int) ($todayReport['munchado']['emails']['total_mails_opened']);
                $overViewData['email_click'] = (int) ($todayReport['munchado']['emails']['total_mails_clicked']);
                //pr($overViewData,1);
        
        
        $overViewData['messagesentUpDown'] =  "down_0";
        $overViewData['peoplereachedUpDown'] = "down_0";
        $overViewData['followersUpDown'] =  "down_0";
        $overViewData['reviewUpDown'] =  "down_0";
        $overViewData['avgtimeprofileUpDown'] =  "down_0";
        $overViewData['fbfollowersUpDown'] =  "down_0";
        $overViewData['fbpeoplereachedUpDown'] = "down_0";
        $overViewData['fbreviewcommentUpDown'] =  "down_0";
        $overViewData['fbmessageUpDown'] =  "down_0";
        $overViewData['fbmostcommentUpDown'] =  "down_0";
        $overViewData['fbmostlikeUpDown'] =  "down_0";
        $overViewData['instagramfollowersUpDown'] =  "down_0";
        $overViewData['instagrampeoplereachedUpDown'] =  "down_0";
        $overViewData['instagramcommentUpDown'] =  "down_0";
        $overViewData['instagrammostcommentUpDown'] =  "down_0";
        $overViewData['instagrammostlikeUpDown'] =  "down_0";
        $overViewData['emailsentUpDown'] = "down_0";
        $overViewData['emailopenUpDown'] = "down_0";
        $overViewData['emailclickUpDown'] = "down_0";
        
        $end_date = date('Y-m-d',  strtotime($currentDate));
        $conditionStartDate = array('date' => $end_date, 'restaurant_id' =>(int) $restaurant_id);
        $chartData = $collection->find($conditionStartDate, array('date' => 1, 'reports.social.facebook.most_popular_content' => 1))->sort(array('date' => 1));
        $reportChartData = iterator_to_array($chartData, FALSE);
        $populatContent=array(); 
        if(count($reportChartData)> 0 && !empty($reportChartData)){
            foreach($reportChartData as $key=>$vl){
                foreach($vl['reports']['social']['facebook']['most_popular_content'] as $key1=>$vl){
                  $populatContent[]=  $vl;
                  $populatContent[$key1]['date_time']=gmdate("d F Y", strtotime($vl['date_time']));
                }
                
            }
        }
        $end_date = date('Y-m-d',  strtotime($currentDate));
        $conditionStartDate = array('date' => $end_date, 'restaurant_id' =>(int) $restaurant_id);
        
        $chartData = $collection->find($conditionStartDate, array('date' => 1, 'reports.social.instagram.most_popular_content' => 1))->sort(array('date' => 1));
        $reportChartData = iterator_to_array($chartData, FALSE);
        $populatInsagramContent=array(); 
        if(count($reportChartData)> 0 && !empty($reportChartData)){
            foreach($reportChartData as $key=>$vl){
                       if(isset($vl['reports']['social']['instagram']['most_popular_content']) && count($vl['reports']['social']['instagram']['most_popular_content']) >0 && !empty($vl['reports']['social']['instagram']['most_popular_content'])){
                            foreach($vl['reports']['social']['instagram']['most_popular_content'] as $key=>$vl){
                              $populatInsagramContent[]=  $vl;
                            }
                       }
                
            }
        }
        $populatContent=  array_merge($populatContent,$populatInsagramContent);
        $overViewData['popularcontent'] = $populatContent;
        return $overViewData;
    }

    public function downloadpdf($currentDate, $restaurant_id, $dateRefineToSearch, $collection) {
        if ($dateRefineToSearch['start_date'] != '' && $dateRefineToSearch['end_date'] != '') {
            return $this->downloadpdfWeekMonthData($dateRefineToSearch, $restaurant_id, $collection);
        } else {
            return $this->downloadpdfAllData($currentDate, $restaurant_id, $collection);
        }
    }
    
    public function downloadpdfWeekMonthData($dateRefineToSearch, $restaurant_id, $collection){
        $condition = array('date' => $dateRefineToSearch['end_date'], 'restaurant_id' => (int) $restaurant_id);
       
        $currentDateData = $collection->find($condition);
        $reportCurrentTodayDate = iterator_to_array($currentDateData, FALSE);
        if (count($reportCurrentTodayDate) == 0) {
            throw new \Exception('Data not found.');
        }

        $conditionStartDate = array('date' => $dateRefineToSearch['start_date'], 'restaurant_id' => (int) $restaurant_id);
        $lastStartDate = $collection->find($conditionStartDate);
        $reportCurrentWeekendDate = iterator_to_array($lastStartDate, FALSE);
        $todayReport = $reportCurrentTodayDate[0]['reports'];
       
        $LastReport = $reportCurrentWeekendDate[0]['reports']; 
//        $ReportLatDate = strtotime('-1 day', strtotime($dateRefineToSearch['end_date']));
//        $ReportLatDate = date('Y-m-d', $ReportLatDate);
        
         /* visit start*/
                    $chartCondition = array('date' => array('$gte' => $dateRefineToSearch['start_date'], '$lte' => $dateRefineToSearch['end_date']), 'restaurant_id' =>(int) $restaurant_id);
                    $chartData = $collection->find($chartCondition, array('date' => 1, 'reports.social.ga' => 1))->sort(array('date' => 1));
                    $reportChartData = iterator_to_array($chartData, FALSE);
                   
                    $varPattranCounter=0;
                     if (count($reportChartData) > 0) {
                         
                    foreach ($reportChartData as $key => $vl) {   
                                foreach($vl['reports']['social']['ga']['traffic_array'] as $key=>$v){
                                   $varVisitChartRecord[$varPattranCounter]['date'] = date("M d", strtotime($v['date']));
                                   $varVisitChartRecord[$varPattranCounter]['mob'] = $v['mobile']; 
                                   $varVisitChartRecord[$varPattranCounter]['web'] = $v['website']; 
                                    $varPattranCounter++; 
                                }
                            
                        }

                    }
                    /* visit end*/
                    
                    /*order start*/
                   
                    $chartCondition = array('date' => array('$gte' => $dateRefineToSearch['start_date'], '$lte' => $dateRefineToSearch['end_date']), 'restaurant_id' =>(int) $restaurant_id);
                    $chartData = $collection->find($chartCondition, array('date' => 1, 'reports.munchado.orders' => 1))->sort(array('date' => 1));
                    $reportChartData = iterator_to_array($chartData, FALSE);                  
                    
                    $varorderChartRecord = array();
                    $varPattranCounter=0;
                    if (count($reportChartData) > 0) {
                        foreach ($reportChartData as $key => $vl) {  
                               
                          $varorderChartRecord[$varPattranCounter]['date'] = date("M d", strtotime($vl['date']));
                         $varorderChartRecord[$varPattranCounter]['ordersCount'] = count($vl['reports']['munchado']['orders']['order_data']); 
                         $varPattranCounter++;      
                       }
                    }
                    /*order End*/
                    
                    /* engagement start*/
                    
                
        $conditionStartDate = array('date' => $dateRefineToSearch['end_date'], 'restaurant_id' =>(int) $restaurant_id);
        $chartData = $collection->find($conditionStartDate, array('date' => 1, 'reports.social.facebook.most_popular_content' => 1))->sort(array('date' => 1));
        $reportChartData = iterator_to_array($chartData, FALSE);
        $populatContent=array(); 
        if(count($reportChartData)> 0 && !empty($reportChartData)){
            foreach($reportChartData as $key=>$vl){
                foreach($vl['reports']['social']['facebook']['most_popular_content'] as $key1=>$vl){
                  $populatContent[]=  $vl;
                  $populatContent[$key1]['date_time']=gmdate("d F Y", strtotime($vl['date_time']));
                }
                
            }
        }
        $conditionStartDate = array('date' => $dateRefineToSearch['end_date'], 'restaurant_id' =>(int) $restaurant_id);
        $chartData = $collection->find($conditionStartDate, array('date' => 1, 'reports.social.instagram.most_popular_content' => 1))->sort(array('date' => 1));
        $reportChartData = iterator_to_array($chartData, FALSE);
        $populatInsagramContent=array(); 
        if(count($reportChartData)> 0 && !empty($reportChartData)){
            foreach($reportChartData as $key=>$vl){
                if(isset($vl['reports']['social']['instagram']['most_popular_content']) && count($vl['reports']['social']['instagram']['most_popular_content']) >0 && !empty($vl['reports']['social']['instagram']['most_popular_content'])){
                    foreach($vl['reports']['social']['instagram']['most_popular_content'] as $key1=>$vl){
                      $populatInsagramContent[]=  $vl;
                    }
                }
            }
        }
        $populatContent=  array_merge($populatContent,$populatInsagramContent);
        /* Start Dashboard Data */
        
        
                $overViewData['end_date'] =date('m/d/Y',  strtotime($dateRefineToSearch['end_date']));
                $overViewData['start_date'] =date('m/d/Y',  strtotime($dateRefineToSearch['start_date']));
                $overViewData['restaurant_logo'] =$reportCurrentWeekendDate[0]['restaurant_logo'];
                $overViewData['restaurant_name'] =$reportCurrentWeekendDate[0]['restaurant_name'];
                $overViewData['restaurant_address'] =$reportCurrentTodayDate[0]['restaurant_address'];
                $overViewData['total_customers'] = (int) $todayReport['munchado']['orders']['order_alltime']['total_customers'] - (int) $LastReport['munchado']['orders']['order_alltime']['total_customers'];
                $overViewData['total_orders'] = (int) $todayReport['munchado']['orders']['order_alltime']['total_orders'] - (int) $LastReport['munchado']['orders']['order_alltime']['total_orders'];
                $overViewData['ga_users'] = (int) $todayReport['social']['ga']['users'] - (int) $LastReport['social']['ga']['users'];
                $overViewData['people_reached'] = 0;
                $overViewData['visit'] = (int) $todayReport['social']['ga']['visit'] - (int) $LastReport['social']['ga']['visit'];
                
                $overViewData['visit_new_customer'] = (int) $todayReport['social']['ga']['new_customer'] - (int) $LastReport['social']['ga']['new_customer'];
                $overViewData['visit_returing_customer'] = (int) $todayReport['social']['ga']['returing_customer'] - (int) $LastReport['social']['ga']['returing_customer'];
                $overViewData['pageviews'] = (int) $todayReport['social']['ga']['page_views'] - (int) $LastReport['social']['ga']['page_views'];
                
                $overViewData['message_sent'] = (int) $todayReport['munchado']['emails']['message_sent'] - (int) $LastReport['munchado']['emails']['message_sent'];
                $overViewData['twitter_followers'] = (int) ($todayReport['social']['twitter']['followers']) - (int) ($LastReport['social']['twitter']['followers']);
                $overViewData['instagram_followers'] = (int) ($todayReport['social']['instagram']['followers']) - (int) ($LastReport['social']['instagram']['followers']);
                $overViewData['fb_followers'] = (int) ($todayReport['social']['facebook']['followers']) - (int) ($LastReport['social']['facebook']['followers']);
                $overViewData['followers'] = (int) $overViewData['instagram_followers']+$overViewData['fb_followers'];
                $overViewData['fb_review_comment'] = (int) ($todayReport['social']['facebook']['comments']) - (int) ($LastReport['social']['facebook']['comments']);
                $overViewData['instagram_comment'] = (int) ($todayReport['social']['instagram']['comments']) - (int) ($LastReport['social']['instagram']['comments']);
                $overViewData['review_comment'] = (int) ($todayReport['munchado']['review']['review_alltime']['total_reviews']) - (int) ($LastReport['munchado']['review']['review_alltime']['total_reviews']);
                $overViewData['review_comment']= (int) $overViewData['fb_review_comment']+$overViewData['review_comment']+$overViewData['instagram_comment'];
                
                $overViewData['avg_time_profile'] =$todayReport['social']['ga']['avg_time_profile'];
                $overViewData['total_revenue'] = $todayReport['munchado']['orders']['order_alltime']['total_revenue'] - $LastReport['munchado']['orders']['order_alltime']['total_revenue'];
                if($overViewData['total_orders']>0){
                $overViewData['avg_order_val'] = number_format($overViewData['total_revenue'] / $overViewData['total_orders'], 2);
                }else{
                $overViewData['avg_order_val'] = 0;    
                }
                $overViewData['item_per_order'] = number_format($todayReport['munchado']['orders']['order_alltime']['avg_item'],2);
                if($overViewData['ga_users']>0){
                $overViewData['order_conversion'] = number_format($overViewData['total_orders']/$overViewData['ga_users']*100, 2);
                }else{
                 $overViewData['order_conversion'] =0;
                }
                $overViewData['order_new_customers'] =(int) $todayReport['munchado']['orders']['order_alltime']['new_customers'] - $LastReport['munchado']['orders']['order_alltime']['new_customers'];
                $overViewData['order_returning_customers'] =(int) $todayReport['munchado']['orders']['order_alltime']['returning_customers'] - $LastReport['munchado']['orders']['order_alltime']['returning_customers'];
                $overViewData['email_sent'] = (int) ($todayReport['munchado']['emails']['total_mails_sent']) - (int) ($LastReport['munchado']['emails']['total_mails_sent']);
                $overViewData['popularcontent'] = $populatContent;
                /* end Dashboard Data */
                
                /* visit part start from here*/
                $overViewData['direct_traffic'] = (int) $todayReport['social']['ga']['direct'] - (int) $LastReport['social']['ga']['direct'];
                $overViewData['referral'] = (int) $todayReport['social']['ga']['referral'] - (int) $LastReport['social']['ga']['referral'];
                $overViewData['web_traffic'] = (int) $todayReport['social']['ga']['web_traffic'] - (int) $LastReport['social']['ga']['web_traffic'];
                $overViewData['mob_traffic'] = (int) $todayReport['social']['ga']['mob_traffic'] - (int) $LastReport['social']['ga']['mob_traffic'];
                $overViewData['display_add'] = (int) $todayReport['social']['ga']['display_add'] - (int) $LastReport['social']['ga']['display_add'];
                $overViewData['display_email'] = (int) $todayReport['social']['ga']['emails']- (int) $LastReport['social']['ga']['emails'];
                $overViewData['social_media'] = (int) $todayReport['social']['ga']['social_media'] - (int) $LastReport['social']['ga']['social_media'];
                $overViewData['others'] = (int) $todayReport['social']['ga']['others'] - (int) $LastReport['social']['ga']['others'];
                $overViewData['overview_page'] = (int) $todayReport['social']['ga']['overview_page'] - (int) $LastReport['social']['ga']['overview_page'];
                $overViewData['menu_page'] = (int) $todayReport['social']['ga']['menu_page'] - (int) $LastReport['social']['ga']['menu_page'];
//                $overViewData['story_page'] = (int) $todayReport['social']['ga']['story_page'] - (int) $LastReport['social']['ga']['story_page'];
                $overViewData['gallery_page'] = (int) $todayReport['social']['ga']['gallery_page'] - (int) $LastReport['social']['ga']['gallery_page'];
                $overViewData['review_page'] = (int) $todayReport['social']['ga']['review_page'] - (int) $LastReport['social']['ga']['review_page'];
                $overViewData['checkout_page'] = (int) $todayReport['social']['ga']['checkout_page'] - (int) $LastReport['social']['ga']['checkout_page'];
                $overViewData['dine_more_page'] = (int) $todayReport['social']['ga']['dine_more'] - (int) $LastReport['social']['ga']['dine_more'];
                $overViewData['popular_pages']=[];
                if($overViewData['visit']>0){
                    foreach ($overViewData as $key => $val) {
                if ($val < 0) {
                    $overViewData[$key] = 0;
                }
                }
                
                $otherVisit=$overViewData['dine_more_page']+$overViewData['gallery_page']+$overViewData['menu_page']+$overViewData['review_page']+$overViewData['checkout_page'];
                $overViewData['overview_percent'] = number_format($overViewData['overview_page'] / $otherVisit * 100,2);
                $overViewData['menu_percent'] = number_format($overViewData['menu_page'] / $otherVisit * 100,2);
//                $overViewData['story_percent'] = number_format($overViewData['story_page'] / $overViewData['pageviews'] * 100);
                $overViewData['gallery_percent'] =number_format($overViewData['gallery_page'] / $otherVisit * 100,2);
                $overViewData['review_percent'] = number_format($overViewData['review_page'] / $otherVisit * 100,2);
                $overViewData['checkout_percent'] = number_format($overViewData['checkout_page'] / $otherVisit * 100,2);
                $overViewData['dine_more_page_persent'] = number_format($overViewData['dine_more_page'] / $otherVisit * 100,2);
                $overViewData['new_percent'] = number_format($overViewData['visit_new_customer'] / $overViewData['ga_users'] * 100);
                $overViewData['returning_percent'] = number_format($overViewData['visit_returing_customer'] / $overViewData['ga_users'] * 100);
                
                if($overViewData['visit']>0){
                $getPagePerVisit=$overViewData['visit']+$overViewData['visit_new_customer']+$overViewData['visit_returing_customer'];
                $overViewData['page_per_visit'] =  number_format($overViewData['pageviews']/$getPagePerVisit,2);
                }else{
                $overViewData['page_per_visit'] =0;
                }
                $overViewData['other_page']=(int) $overViewData['pageviews']-$otherVisit;
                $overViewData['other_page_persent']=number_format($overViewData['other_page'] / $overViewData['pageviews'] * 100);
                 $overViewData['other_page_persent']=($overViewData['other_page_persent']>100)?100:$overViewData['other_page_persent'];
//                $overViewData['popular_pages'][0]['page'] = 'Overview';
//                $overViewData['popular_pages'][0]['count'] = $overViewData['overview_page'];
//                $overViewData['popular_pages'][0]['percent'] = $overViewData['overview_percent'];
                $overViewData['popular_pages'][0]['page'] = 'Menu';
                $overViewData['popular_pages'][0]['count'] = $overViewData['menu_page'];
                $overViewData['popular_pages'][0]['percent'] = $overViewData['menu_percent'];
//                $overViewData['popular_pages'][2]['page'] = 'story';
//                $overViewData['popular_pages'][2]['count'] = $overViewData['story_page'];
//                $overViewData['popular_pages'][2]['percent'] = $overViewData['story_percent'];

                $overViewData['popular_pages'][1]['page'] = 'Review';
                $overViewData['popular_pages'][1]['count'] = $overViewData['review_page'];
                $overViewData['popular_pages'][1]['percent'] = $overViewData['review_percent'];
                $overViewData['popular_pages'][2]['page'] = 'Checkout';
                $overViewData['popular_pages'][2]['count'] = $overViewData['checkout_page'];
                $overViewData['popular_pages'][2]['percent'] = $overViewData['checkout_percent'];
//                $overViewData['popular_pages'][4]['page'] = 'other';
//                $overViewData['popular_pages'][4]['count'] = $overViewData['other_page'];
//                $overViewData['popular_pages'][4]['percent'] = $overViewData['other_page_persent'];
                $overViewData['popular_pages'][3]['page'] = 'Dine & More';
                $overViewData['popular_pages'][3]['count'] = $overViewData['dine_more_page'];
                $overViewData['popular_pages'][3]['percent'] = $overViewData['dine_more_page_persent'];
                $overViewData['popular_pages'][4]['page'] = 'Gallery';
                $overViewData['popular_pages'][4]['count'] = $overViewData['gallery_page'];
                $overViewData['popular_pages'][4]['percent'] = $overViewData['gallery_percent'];
                }
                $active=[];
                if (count($overViewData['popular_pages']) > 0) {
                    foreach ($overViewData['popular_pages'] as $key => $values) {
                        $active[$key] = $values['count'];
                    }
                }
                array_multisort($active, SORT_DESC, $overViewData['popular_pages']);
                $overViewData['traffic_array'] = $varVisitChartRecord;
                /*visit part end from here*/
                
                /*engagment start from here*/
                $overViewData['fb_people_reached'] = (int) ($todayReport['social']['facebook']['people_reached']) - (int) ($LastReport['social']['facebook']['people_reached']);
                $overViewData['fb_message'] = (int) ($todayReport['social']['facebook']['messages']) - (int) ($LastReport['social']['facebook']['messages']);
                $overViewData['fb_most_comment'] = (int) ($todayReport['social']['facebook']['most_commented']) - (int) ($LastReport['social']['facebook']['most_commented']);
                $overViewData['fb_most_like'] = (int) ($todayReport['social']['facebook']['most_liked']) - (int) ($LastReport['social']['facebook']['most_liked']);
                $overViewData['fb_rating'] = (int) ($todayReport['social']['facebook']['rating']) - (int) ($LastReport['social']['facebook']['rating']);
                $overViewData['instagram_people_reached'] = (int) ($todayReport['social']['instagram']['people_reached']) - (int) ($LastReport['social']['instagram']['people_reached']);
                $overViewData['instagram_most_comment'] = (int) ($todayReport['social']['instagram']['most_commented']) - (int) ($LastReport['social']['instagram']['most_commented']);
                $overViewData['instagram_most_like'] = (int) ($todayReport['social']['instagram']['most_liked']) - (int) ($LastReport['social']['instagram']['most_liked']);
                $overViewData['email_sent'] = (int) ($todayReport['munchado']['emails']['total_mails_sent']) - (int) ($LastReport['munchado']['emails']['total_mails_sent']);
                $overViewData['email_open'] = (int) ($todayReport['munchado']['emails']['total_mails_opened']) - (int) ($LastReport['munchado']['emails']['total_mails_opened']);
                $overViewData['email_click'] = (int) ($todayReport['munchado']['emails']['total_mails_clicked']) - (int) ($LastReport['munchado']['emails']['total_mails_clicked']);
                /*engagment end here*/
                
                /*order start from here*/
                $overViewData['order_delivery'] =(int) $todayReport['munchado']['orders']['order_alltime']['delivery'] -(int) $LastReport['munchado']['orders']['order_alltime']['delivery'];
                $overViewData['order_takeout'] =(int) $todayReport['munchado']['orders']['order_alltime']['takeout'] - (int)$LastReport['munchado']['orders']['order_alltime']['takeout'];
                $overViewData['order_pattern'] = $varorderChartRecord;
                $todayReport['munchado']['orders']['most_popular_items']= array_slice($todayReport['munchado']['orders']['most_popular_items'],0,15);
                if (count($todayReport['munchado']['orders']['most_popular_items']) > 0) {
                     $totalSoldItem=0;
                    foreach ($todayReport['munchado']['orders']['most_popular_items'] as $key => $popItems) {
                    $totalSoldItem+=$popItems['total_items'];    
                    }
                    foreach ($todayReport['munchado']['orders']['most_popular_items'] as $key => $popItems) {
                        if($todayReport['munchado']['orders']['order_alltime']['total_orders']>0){
                        $todayReport['munchado']['orders']['most_popular_items'][$key]['percent'] = number_format($popItems['total_items'] / $totalSoldItem * 100,2);
                        }else{
                        $todayReport['munchado']['orders']['most_popular_items'][$key]['percent'] =0;    
                        }
                        $todayReport['munchado']['orders']['most_popular_items'][$key]['item'] =  html_entity_decode($popItems['item']);
                    }
                }
                $overViewData['most_popular_items'] = $todayReport['munchado']['orders']['most_popular_items'];
                if($overViewData['total_orders']>0){
                $overViewData['chart_takeout'] = number_format($overViewData['order_takeout'] / $overViewData['total_orders'] * 100);
                $overViewData['chart_delivery'] = number_format($overViewData['order_delivery'] / $overViewData['total_orders'] * 100);
                }else{
                $overViewData['chart_takeout'] =0;
                $overViewData['chart_delivery'] =0;
                }
                /*order end here*/
                
                foreach($overViewData as $key=>$val){ 
                    if($val<0){
                      $overViewData[$key]=0;  
                    }
                }
        $conditionLastweekEndDate = array('date' => $dateRefineToSearch['last_end_date'], 'restaurant_id' =>(int) $restaurant_id);
        $lastweekEndDate = $collection->find($conditionLastweekEndDate);
        $reportLastweekEndDate = iterator_to_array($lastweekEndDate, FALSE);

        // get last week all data
        $conditionLastweekStartDate = array('date' =>$dateRefineToSearch['last_start_date'], 'restaurant_id' =>(int) $restaurant_id);
        $lastweekStartDate = $collection->find($conditionLastweekStartDate);
        $reportLastweekStartDate = iterator_to_array($lastweekStartDate, FALSE);

        $todayReport = $reportLastweekEndDate[0]['reports'];
        $LastReport = $reportLastweekStartDate[0]['reports'];
         
        $lastOverviewData['total_orders'] = (int) $todayReport['munchado']['orders']['order_alltime']['total_orders'] - (int) $LastReport['munchado']['orders']['order_alltime']['total_orders'];
        $lastOverviewData['ga_users'] = (int) $todayReport['social']['ga']['users'] - (int) $LastReport['social']['ga']['users'];
        $lastOverviewData['people_reached'] = (int) $todayReport['social']['engagement']['people_reached'] - (int) $LastReport['social']['engagement']['people_reached'];
        $lastOverviewData['visit'] = (int) $todayReport['social']['ga']['visit'] - (int) $LastReport['social']['ga']['visit'];
       
        $lastOverviewData['visit_new_customer'] = (int) $todayReport['social']['ga']['new_customer'] - (int) $LastReport['social']['ga']['new_customer'];
        $lastOverviewData['visit_returing_customer'] = (int) $todayReport['social']['ga']['returing_customer'] - (int) $LastReport['social']['ga']['returing_customer'];
        $lastOverviewData['pageviews'] = (int) $todayReport['social']['ga']['page_views'] - (int) $LastReport['social']['ga']['page_views'];
        $lastOverviewData['overview_page'] = (int) $todayReport['social']['ga']['overview_page'] - (int) $LastReport['social']['ga']['overview_page'];
        $lastOverviewData['menu_page'] = (int) $todayReport['social']['ga']['menu_page'] - (int) $LastReport['social']['ga']['menu_page'];
        $lastOverviewData['story_page'] = (int) $todayReport['social']['ga']['story_page'] - (int) $LastReport['social']['ga']['story_page'];
        $lastOverviewData['gallery_page'] = (int) $todayReport['social']['ga']['gallery_page'] - (int) $LastReport['social']['ga']['gallery_page'];
        $lastOverviewData['review_page'] = (int) $todayReport['social']['ga']['review_page'] - (int) $LastReport['social']['ga']['review_page'];
        $lastOverviewData['checkout_page'] = (int) $todayReport['social']['ga']['checkout_page'] - (int) $LastReport['social']['ga']['checkout_page'];
        $lastOverviewData['dine_more_page'] = (int) $todayReport['social']['ga']['dine_more'] - (int) $LastReport['social']['ga']['dine_more'];
        $otherVisit =(int) $lastOverviewData['dine_more_page']+(int) $lastOverviewData['overview_page'] +(int) $lastOverviewData['menu_page'] + (int)$lastOverviewData['story_page'] + (int)$lastOverviewData['gallery_page'] + (int)$lastOverviewData['review_page'] + (int)$lastOverviewData['checkout_page'];
        $lastOverviewData['pageviews'] =$otherVisit;
        if($lastOverviewData['visit']>0){
        $lastOverviewData['page_per_visit'] =  number_format($lastOverviewData['pageviews']/$lastOverviewData['visit'],2);
        }else{
        $lastOverviewData['page_per_visit'] =0;
        }
        $lastOverviewData['message_sent'] = (int) $todayReport['munchado']['emails']['message_sent'] - (int) $LastReport['munchado']['emails']['message_sent'];
        $lastOverviewData['twitter_followers'] = (int) ($todayReport['social']['twitter']['followers']) - (int) ($LastReport['social']['twitter']['followers']);
        $lastOverviewData['instagram_followers'] = (int) ($todayReport['social']['instagram']['followers']) - (int) ($LastReport['social']['instagram']['followers']);
        $lastOverviewData['fb_followers'] = (int) ($todayReport['social']['facebook']['followers']) - (int) ($LastReport['social']['facebook']['followers']);
        $lastOverviewData['followers'] = (int) $lastOverviewData['instagram_followers']+ $lastOverviewData['fb_followers'];
        $lastOverviewData['fb_review_comment'] = (int) ($todayReport['social']['facebook']['comments']) - (int) ($LastReport['social']['facebook']['comments']);
        $lastOverviewData['review_comment'] = (int) ($todayReport['munchado']['review']['review_alltime']['total_reviews']) - (int) ($LastReport['munchado']['review']['review_alltime']['total_reviews']);
        $lastOverviewData['instagram_comment'] = (int) ($todayReport['social']['instagram']['comments']) - (int) ($LastReport['social']['instagram']['comments']);
        $lastOverviewData['review_comment']=$lastOverviewData['review_comment']+$lastOverviewData['fb_review_comment']+$lastOverviewData['instagram_comment'];
        
        $lastOverviewData['avg_time_profile'] = $todayReport['social']['ga']['avg_time_profile'];
        $lastOverviewData['total_revenue'] = $todayReport['munchado']['orders']['order_alltime']['total_revenue'] - $LastReport['munchado']['orders']['order_alltime']['total_revenue'];
        if($lastOverviewData['total_orders']>0){
        $lastOverviewData['avg_order_val'] = number_format($lastOverviewData['total_revenue'] / $lastOverviewData['total_orders'], 2);
        }else{
        $lastOverviewData['avg_order_val'] = $lastOverviewData['total_revenue'];
        }
        $lastOverviewData['item_per_order'] = number_format($todayReport['munchado']['orders']['order_alltime']['avg_item'],2);
        if($lastOverviewData['ga_users']>0){
        $lastOverviewData['order_conversion'] = number_format($lastOverviewData['total_orders'] / $lastOverviewData['ga_users'] * 100, 2);
        }else{
        $lastOverviewData['order_conversion'] =0;    
        }
        $lastOverviewData['order_new_customers'] =(int) $todayReport['munchado']['orders']['order_alltime']['new_customers'] - (int) $LastReport['munchado']['orders']['order_alltime']['new_customers'];
        $lastOverviewData['order_returning_customers'] =(int) $todayReport['munchado']['orders']['order_alltime']['returning_customers'] - (int) $LastReport['munchado']['orders']['order_alltime']['returning_customers'];
        $lastOverviewData['email_sent'] = (int) ($todayReport['munchado']['emails']['total_mails_sent']) - (int) ($LastReport['munchado']['emails']['total_mails_sent']);
        
        $overViewData['orderUpDown'] = self::getPercentVal($overViewData['total_orders'], $lastOverviewData['total_orders']);
        $overViewData['revenueUpDown'] = self::getPercentVal($overViewData['total_revenue'], $lastOverviewData['total_revenue']);
        $overViewData['peopleUpDown'] = self::getPercentVal($overViewData['people_reached'], $lastOverviewData['people_reached']);
        $overViewData['orderConversionUpDown'] = self::getPercentVal($overViewData['order_conversion'], $lastOverviewData['order_conversion']);
        $overViewData['gaUserUpDown'] = self::getPercentVal($overViewData['ga_users'], $lastOverviewData['ga_users']);
        $overViewData['visitUpDown'] = self::getPercentVal($overViewData['visit'], $lastOverviewData['visit']);
        $overViewData['pagepervisitUpDown'] = self::getPercentVal($overViewData['page_per_visit'], $lastOverviewData['page_per_visit']);
        $overViewData['pageviewsUpDown'] = self::getPercentVal($overViewData['pageviews'], $lastOverviewData['pageviews']);
        $overViewData['visitnewcustomerUpDown'] = self::getPercentVal($overViewData['visit_new_customer'], $lastOverviewData['visit_new_customer']);
        $overViewData['visitrepeatcustomerUpDown'] = self::getPercentVal($overViewData['visit_returing_customer'], $lastOverviewData['visit_returing_customer']);
        $overViewData['messagesentUpDown'] = self::getPercentVal($overViewData['message_sent'], $lastOverviewData['message_sent']);
         $overViewData['followersUpDown'] = self::getPercentVal($overViewData['followers'], $lastOverviewData['followers']);
         $overViewData['reviewUpDown'] = self::getPercentVal($overViewData['review_comment'], $lastOverviewData['review_comment']);
         $overViewData['avgTimePageUpDown'] = self::getPercentVal($overViewData['avg_time_profile'], $lastOverviewData['avg_time_profile']);
         $overViewData['avgordervalUpDown'] = self::getPercentVal($overViewData['avg_order_val'], $lastOverviewData['avg_order_val']);
         $overViewData['itemperorderUpDown'] = self::getPercentVal($overViewData['item_per_order'], $lastOverviewData['item_per_order']);
        $overViewData['orderNewuserUpDown'] = self::getPercentVal($overViewData['order_new_customers'], $lastOverviewData['order_new_customers']); 
         $overViewData['orderrepeatuserUpDown'] = self::getPercentVal($overViewData['order_returning_customers'], $lastOverviewData['order_returning_customers']);
         $overViewData['emailsentUpDown'] = self::getPercentVal($overViewData['email_sent'], $lastOverviewData['email_sent']);

        
        $lastOverviewData['fb_people_reached'] = (int) ($todayReport['social']['facebook']['people_reached']) - (int) ($LastReport['social']['facebook']['people_reached']);
        $lastOverviewData['fb_review_comment'] = (int) ($todayReport['social']['facebook']['reviews_comments']) - (int) ($LastReport['social']['facebook']['reviews_comments']);
        $lastOverviewData['fb_message'] = (int) ($todayReport['social']['facebook']['messages']) - (int) ($LastReport['social']['facebook']['messages']);
        $lastOverviewData['fb_most_comment'] = (int) ($todayReport['social']['facebook']['most_commented']) - (int) ($LastReport['social']['facebook']['most_commented']);
        $lastOverviewData['fb_most_like'] = (int) ($todayReport['social']['facebook']['most_liked']) - (int) ($LastReport['social']['facebook']['most_liked']);
        $lastOverviewData['instagram_people_reached'] = (int) ($todayReport['social']['instagram']['people_reached']) - (int) ($LastReport['social']['instagram']['people_reached']);
        
        $lastOverviewData['instagram_most_comment'] = (int) ($todayReport['social']['instagram']['most_commented']) - (int) ($LastReport['social']['instagram']['most_commented']);
        $lastOverviewData['instagram_most_like'] = (int) ($todayReport['social']['instagram']['most_liked']) - (int) ($LastReport['social']['instagram']['most_liked']);
        //$lastOverviewData['email_sent'] = (int) ($todayReport['munchado']['emails']['total_mails_sent']) - (int) ($LastReport['munchado']['emails']['total_mails_sent']);
        $lastOverviewData['email_open'] = (int) ($todayReport['munchado']['emails']['total_mails_opened']) - (int) ($LastReport['munchado']['emails']['total_mails_opened']);
        $lastOverviewData['email_click'] = (int) ($todayReport['munchado']['emails']['total_mails_clicked']) - (int) ($LastReport['munchado']['emails']['total_mails_clicked']);
        $overViewData['fbfollowersUpDown'] = self::getPercentVal($overViewData['fb_followers'], $lastOverviewData['fb_followers']);
        $overViewData['fbpeoplereachedUpDown'] = self::getPercentVal($overViewData['fb_people_reached'], $lastOverviewData['fb_people_reached']);
        $overViewData['fbreviewcommentUpDown'] = self::getPercentVal($overViewData['fb_review_comment'], $lastOverviewData['fb_review_comment']);
        $overViewData['fbmessageUpDown'] = self::getPercentVal($overViewData['fb_message'], $lastOverviewData['fb_message']);
        $overViewData['fbmostcommentUpDown'] = self::getPercentVal($overViewData['fb_most_comment'], $lastOverviewData['fb_most_comment']);
        $overViewData['fbmostlikeUpDown'] = self::getPercentVal($overViewData['fb_most_like'], $lastOverviewData['fb_most_like']);
        $overViewData['instagramfollowersUpDown'] = self::getPercentVal($overViewData['instagram_followers'], $lastOverviewData['instagram_followers']);
        $overViewData['instagrampeoplereachedUpDown'] = self::getPercentVal($overViewData['instagram_people_reached'], $lastOverviewData['instagram_people_reached']);
        $overViewData['instagramcommentUpDown'] = self::getPercentVal($overViewData['instagram_comment'], $lastOverviewData['instagram_comment']);
        $overViewData['instagrammostcommentUpDown'] = self::getPercentVal($overViewData['instagram_most_comment'], $lastOverviewData['instagram_most_comment']);
        $overViewData['instagrammostlikeUpDown'] = self::getPercentVal($overViewData['instagram_most_like'], $lastOverviewData['instagram_most_like']);
       // $overViewData['emailsentUpDown'] = self::getPercentVal($overViewData['email_sent'], $lastOverviewData['email_sent']);
        $overViewData['emailopenUpDown'] = self::getPercentVal($overViewData['email_open'], $lastOverviewData['email_open']);
        $overViewData['emailclickUpDown'] = self::getPercentVal($overViewData['email_click'], $lastOverviewData['email_click']);
        return $overViewData;
                
    }
    
    public function downloadpdfAllData($currentDate, $restaurant_id, $collection){
        $condition = array('restaurant_id' => (int) $restaurant_id);
        $currentDateData = $collection->find($condition)->sort(array('date' =>-1));
        $reportCurrentTodayDate = iterator_to_array($currentDateData, FALSE);
        if (count($reportCurrentTodayDate) == 0) {
            throw new \Exception('Data not found.');
        }
        $todayReport = $reportCurrentTodayDate[0]['reports'];
        
        /* visit start*/
        $end_date = date('Y-m-d',  strtotime($currentDate));
        $newdate = strtotime('-12 month', strtotime($end_date));
        $start_date = date('Y-m-d', $newdate);
        $chartCondition = array('date' => array('$gte' => $start_date, '$lte' => $end_date), 'restaurant_id' =>(int) $restaurant_id);
        
        $chartData = $collection->find($chartCondition, array('date' => 1, 'reports.social.ga' => 1))->sort(array('date' => 1));
        $reportChartData = iterator_to_array($chartData, FALSE);
                   $varVisitChartRecord=array();
                    $varPattranCounter=0;
                    if (count($reportChartData) > 0) {
                         
                    foreach ($reportChartData as $key => $vl) {   
                                foreach($vl['reports']['social']['ga']['traffic_array'] as $key=>$v){
                                   $varVisitChartRecord[$varPattranCounter]['date'] = date("M d", strtotime($v['date']));
                                   $varVisitChartRecord[$varPattranCounter]['mob'] = $v['mobile']; 
                                   $varVisitChartRecord[$varPattranCounter]['web'] = $v['website']; 
                                    $varPattranCounter++; 
                                }
                            
                        }
                        $newvarChartRecord = array();
            if (count($varVisitChartRecord) <= 20) {
                $lessTwCounter = 0;
                foreach ($varVisitChartRecord as $key => $v) {
                    $newvarChartRecord[$lessTwCounter]['date'] = $v['date'];
                    $newvarChartRecord[$lessTwCounter]['mob'] = $v['mob'];
                    $newvarChartRecord[$lessTwCounter]['web'] = $v['web'];
                    $lessTwCounter++;
                }
            } else if (count($varVisitChartRecord) <= 90) {
                $lessNintyCounter = 0;
                $mb=0;
                $wb=0;
                foreach ($varVisitChartRecord as $key => $v) {
                    $mb+=(int) $v['mob'];
                    $wb+=(int) $v['web'];
                    if ($key % 4 == 0) {
                        $newvarChartRecord[$lessNintyCounter]['date'] = $v['date'];
                        $newvarChartRecord[$lessNintyCounter]['mob'] =$mb;
                        $newvarChartRecord[$lessNintyCounter]['web'] = $wb;
                        $lessNintyCounter++;
                    }
                }
            } else if (count($varVisitChartRecord) <= 180) {
                $lessoneatiCounter = 0;
                $mb=0;
                $wb=0;
                foreach ($varVisitChartRecord as $key => $v) {
                    $mb+=(int) $v['mob'];
                    $wb+=(int) $v['web'];
                    if ($key % 15 == 0) {
                        $newvarChartRecord[$lessoneatiCounter]['date'] = $v['date'];
                        $newvarChartRecord[$lessoneatiCounter]['mob'] = $mb;
                        $newvarChartRecord[$lessoneatiCounter]['web'] = $wb;
                        $lessoneatiCounter++;
                    }
                }
            } else {
                $lessoneatiCounter = 0;
                $mb=0;
                $wb=0;
                foreach ($varVisitChartRecord as $key => $v) {
                    $mb+=(int) $v['mob'];
                    $wb+=(int) $v['web'];
                    if ($key % 30 == 0) {
                        $newvarChartRecord[$lessoneatiCounter]['date'] = $v['date'];
                        $newvarChartRecord[$lessoneatiCounter]['mob'] = $mb;
                        $newvarChartRecord[$lessoneatiCounter]['web'] = $wb;
                        $lessoneatiCounter++;
                    }
                }
            }
            $varVisitChartRecord = $newvarChartRecord;
                    }
                    /* visit end*/
                    
                    /*order start*/
                    
                    $end_date = date('Y-m-d',  strtotime($currentDate));
                    $newdate = strtotime('-12 month', strtotime($end_date));
                    $start_date = date('Y-m-d', $newdate);
                    $chartCondition = array('date' => array('$gte' => $start_date, '$lte' => $end_date), 'restaurant_id' =>(int) $restaurant_id);
                    $chartData = $collection->find($chartCondition, array('date' => 1, 'reports.munchado.orders' => 1))->sort(array('date' => 1));
                    $reportOrderChartData = iterator_to_array($chartData, FALSE);
                    $varorderChartRecord = array();
                    $varPattranCounter=0;
                    if (count($reportOrderChartData) > 0) {
                        foreach ($reportOrderChartData as $key => $vl) {  
                         $varorderChartRecord[$varPattranCounter]['date'] = date("M d", strtotime($vl['date']));
                         $varorderChartRecord[$varPattranCounter]['ordersCount'] = count($vl['reports']['munchado']['orders']['order_data']); 
                         $varPattranCounter++;      
                       }
                    }
                    /*order End*/
                    
                    $end_date = date('Y-m-d',  strtotime($currentDate));
        $conditionStartDate = array('date' => $end_date, 'restaurant_id' =>(int) $restaurant_id);
        
        $chartData = $collection->find($conditionStartDate, array('date' => 1, 'reports.social.facebook.most_popular_content' => 1))->sort(array('date' => 1));
        $reportChartData = iterator_to_array($chartData, FALSE);
        $populatContent=array(); 
        if(count($reportChartData)> 0 && !empty($reportChartData)){
            foreach($reportChartData as $key=>$vl){
                foreach($vl['reports']['social']['facebook']['most_popular_content'] as $key1=>$vl){
                  $populatContent[]=  $vl;
                   $populatContent[$key1]['date_time']=gmdate("d F Y", strtotime($vl['date_time']));
                }
                
            }
        }
        
        $end_date = date('Y-m-d',  strtotime($currentDate));
        $conditionStartDate = array('date' => $end_date, 'restaurant_id' =>(int) $restaurant_id);
        
        $chartData = $collection->find($conditionStartDate, array('date' => 1, 'reports.social.instagram.most_popular_content' => 1))->sort(array('date' => 1));
        $reportChartData = iterator_to_array($chartData, FALSE);
        $populatInsagramContent=array(); 
        if(count($reportChartData)> 0 && !empty($reportChartData)){
            foreach($reportChartData as $key=>$vl){
                if(isset($vl['reports']['social']['instagram']['most_popular_content']) && count($vl['reports']['social']['instagram']['most_popular_content']) >0 && !empty($vl['reports']['social']['instagram']['most_popular_content'])){
                    foreach($vl['reports']['social']['instagram']['most_popular_content'] as $key1=>$vl){
                      $populatInsagramContent[]=  $vl;
                    }
                }
            }
        }
        
        
        $populatContent=  array_merge($populatContent,$populatInsagramContent);
      $overViewData['popularcontent'] = $populatContent;
                $overViewData['end_date'] =date('m/d/Y',  strtotime($end_date));
                $overViewData['start_date'] =date('m/d/Y',  strtotime($reportCurrentTodayDate[0]['restaurant_register_date']));                
                $overViewData['restaurant_logo'] =$reportCurrentTodayDate[0]['restaurant_logo'];    
                $overViewData['restaurant_name'] =$reportCurrentTodayDate[0]['restaurant_name'];
                $overViewData['restaurant_address'] =$reportCurrentTodayDate[0]['restaurant_address'];
                $overViewData['total_customers'] = (int) $todayReport['munchado']['orders']['order_alltime']['total_customers'];
                $overViewData['total_orders'] = (int) $todayReport['munchado']['orders']['order_alltime']['total_orders'];
                $overViewData['ga_users'] = (int) $todayReport['social']['ga']['users'];
                $overViewData['people_reached'] = 0;
                $overViewData['visit'] = (int) $todayReport['social']['ga']['visit'];
                $overViewData['visit_new_customer'] = (int) $todayReport['social']['ga']['new_customer'];
                $overViewData['visit_returing_customer'] = (int) $todayReport['social']['ga']['returing_customer'];
                $overViewData['pageviews'] = (int) $todayReport['social']['ga']['page_views'];
                
                $overViewData['message_sent'] = (int) $todayReport['munchado']['emails']['message_sent'];
                $overViewData['twitter_followers'] = (int) ($todayReport['social']['twitter']['followers']);
                $overViewData['instagram_followers'] = (int) ($todayReport['social']['instagram']['followers']);
                $overViewData['fb_followers'] = (int) ($todayReport['social']['facebook']['followers']);
                $overViewData['followers'] = (int) $overViewData['instagram_followers']+$overViewData['fb_followers'];
                $overViewData['fb_review_comment'] = (int) ($todayReport['social']['facebook']['comments']);
                $overViewData['review_comment'] = (int) ($todayReport['munchado']['review']['review_alltime']['total_reviews']);
                $overViewData['instagram_comment'] = (int) ($todayReport['social']['instagram']['comments']);
                $overViewData['review_comment']= (int) $overViewData['fb_review_comment']+$overViewData['review_comment']+$overViewData['instagram_comment'];
                $overViewData['avg_time_profile'] =$todayReport['social']['ga']['avg_time_profile'];
                $overViewData['total_revenue'] = $todayReport['munchado']['orders']['order_alltime']['total_revenue'];
                if($overViewData['total_orders']>0){
                $overViewData['avg_order_val'] = number_format($overViewData['total_revenue'] / $overViewData['total_orders'], 2);
                }else{
                $overViewData['avg_order_val'] = 0;
                }
                $overViewData['item_per_order'] = number_format($todayReport['munchado']['orders']['order_alltime']['avg_item'],2);
                if($overViewData['ga_users']>0){
                $overViewData['order_conversion'] = number_format($overViewData['total_orders']/$overViewData['ga_users']*100, 2);
                }else{
                $overViewData['order_conversion'] =0;
                }
                $overViewData['order_new_customers'] =(int) $todayReport['munchado']['orders']['order_alltime']['new_customers'];
                $overViewData['order_returning_customers'] =(int) $todayReport['munchado']['orders']['order_alltime']['returning_customers'];
                $overViewData['email_sent'] = (int) ($todayReport['munchado']['emails']['total_mails_sent']);
                $overViewData['direct_traffic'] = (int) $todayReport['social']['ga']['direct'];
                $overViewData['referral'] = (int) $todayReport['social']['ga']['referral'];
                $overViewData['web_traffic'] = (int) $todayReport['social']['ga']['web_traffic'];
                $overViewData['mob_traffic'] = (int) $todayReport['social']['ga']['mob_traffic'];
                $overViewData['display_add'] = (int) $todayReport['social']['ga']['display_add'];
                $overViewData['display_email'] = (int) $todayReport['social']['ga']['emails'];
                $overViewData['social_media'] = (int) $todayReport['social']['ga']['social_media'];
                $overViewData['others'] = (int) $todayReport['social']['ga']['others'];
                $overViewData['overview_page'] = (int) $todayReport['social']['ga']['overview_page'];
                $overViewData['menu_page'] = (int) $todayReport['social']['ga']['menu_page'];
                $overViewData['story_page'] = (int) $todayReport['social']['ga']['story_page'];
                $overViewData['gallery_page'] = (int) $todayReport['social']['ga']['gallery_page'];
                $overViewData['review_page'] = (int) $todayReport['social']['ga']['review_page'];
                $overViewData['checkout_page'] = (int) $todayReport['social']['ga']['checkout_page'];
                $overViewData['dine_more_page'] = (int) $todayReport['social']['ga']['dine_more'];
                 $overViewData['popular_pages']=[];
                if($overViewData['visit']>0){
                $otherVisit=$overViewData['dine_more_page']+$overViewData['gallery_page']+$overViewData['menu_page']+$overViewData['review_page']+$overViewData['checkout_page'];
                
                $overViewData['overview_percent'] = number_format($overViewData['overview_page'] /$otherVisit * 100,2);
                $overViewData['menu_percent'] = number_format($overViewData['menu_page'] / $otherVisit * 100,2);
//                $overViewData['story_percent'] = number_format($overViewData['story_page'] / $overViewData['pageviews'] * 100);
                $overViewData['gallery_percent'] =number_format($overViewData['gallery_page'] / $otherVisit * 100,2);
                $overViewData['review_percent'] = number_format($overViewData['review_page'] / $otherVisit * 100,2);
                $overViewData['checkout_percent'] = number_format($overViewData['checkout_page'] / $otherVisit * 100,2);
                $overViewData['dine_more_page_persent'] = number_format($overViewData['dine_more_page'] / $otherVisit * 100);
                $overViewData['new_percent'] = number_format($overViewData['visit_new_customer'] / $overViewData['ga_users'] * 100);                 
                $overViewData['returning_percent'] = number_format($overViewData['visit_returing_customer'] / $overViewData['ga_users'] * 100);
                
                 
                 if($overViewData['visit']>0){
                $getPagePerVisit=$overViewData['visit']+$overViewData['visit_new_customer']+$overViewData['visit_returing_customer'];     
                $overViewData['page_per_visit'] =number_format($overViewData['pageviews']/$getPagePerVisit,2);
                }else{
                $overViewData['page_per_visit'] =0;    
                }
                $overViewData['other_page']=(int) $overViewData['pageviews']-$otherVisit;
                $overViewData['other_page_persent']=number_format($overViewData['other_page'] / $overViewData['pageviews'] * 100);
                $overViewData['other_page_persent']=($overViewData['other_page_persent']>100)?100:$overViewData['other_page_persent'];
//                $overViewData['popular_pages'][0]['page'] = 'Overview';
//                $overViewData['popular_pages'][0]['count'] = $overViewData['overview_page'];
//                $overViewData['popular_pages'][0]['percent'] = $overViewData['overview_percent'];
                $overViewData['popular_pages'][0]['page'] = 'Menu';
                $overViewData['popular_pages'][0]['count'] = $overViewData['menu_page'];
                $overViewData['popular_pages'][0]['percent'] = $overViewData['menu_percent'];
//                $overViewData['popular_pages'][2]['page'] = 'story';
//                $overViewData['popular_pages'][2]['count'] = $overViewData['story_page'];
//                $overViewData['popular_pages'][2]['percent'] = $overViewData['story_percent'];

                $overViewData['popular_pages'][1]['page'] = 'Review';
                $overViewData['popular_pages'][1]['count'] = $overViewData['review_page'];
                $overViewData['popular_pages'][1]['percent'] = $overViewData['review_percent'];
                $overViewData['popular_pages'][2]['page'] = 'Checkout';
                $overViewData['popular_pages'][2]['count'] = $overViewData['checkout_page'];
                $overViewData['popular_pages'][2]['percent'] = $overViewData['checkout_percent'];
//                $overViewData['popular_pages'][6]['page'] = 'other';
//                $overViewData['popular_pages'][6]['count'] = $overViewData['other_page'];
//                $overViewData['popular_pages'][6]['percent'] = $overViewData['other_page_persent'];
                $overViewData['popular_pages'][3]['page'] = 'Dine & more';
                $overViewData['popular_pages'][3]['count'] = $overViewData['dine_more_page'];
                $overViewData['popular_pages'][3]['percent'] = $overViewData['dine_more_page_persent'];
                               $overViewData['popular_pages'][4]['page'] = 'gallery';
               $overViewData['popular_pages'][4]['count'] = $overViewData['gallery_page'];
                $overViewData['popular_pages'][4]['percent'] = $overViewData['gallery_percent'];
                }
                $active=[];
                if (count($overViewData['popular_pages']) > 0) {
                    foreach ($overViewData['popular_pages'] as $key => $values) {
                        $active[$key] = $values['count'];
                    }
                }
                array_multisort($active, SORT_DESC, $overViewData['popular_pages']);
                $overViewData['fb_people_reached'] = (int) ($todayReport['social']['facebook']['people_reached']);
                $overViewData['fb_message'] = (int) ($todayReport['social']['facebook']['messages']);
                $overViewData['fb_most_comment'] = (int) ($todayReport['social']['facebook']['most_commented']);
                $overViewData['fb_most_like'] = (int) ($todayReport['social']['facebook']['most_liked']);
                $overViewData['fb_rating'] = (int) ($todayReport['social']['facebook']['rating']);
                $overViewData['instagram_people_reached'] = (int) ($todayReport['social']['instagram']['people_reached']);
                
                $overViewData['instagram_most_comment'] = (int) ($todayReport['social']['instagram']['most_commented']);
                $overViewData['instagram_most_like'] = (int) ($todayReport['social']['instagram']['most_liked']);
               
                $overViewData['email_sent'] = (int) ($todayReport['munchado']['emails']['total_mails_sent']);
                $overViewData['email_open'] = (int) ($todayReport['munchado']['emails']['total_mails_opened']);
                $overViewData['email_click'] = (int) ($todayReport['munchado']['emails']['total_mails_clicked']);
                
                $overViewData['order_delivery'] =(int) $todayReport['munchado']['orders']['order_alltime']['delivery'];
                $overViewData['order_takeout'] =(int) $todayReport['munchado']['orders']['order_alltime']['takeout'];
                $todayReport['munchado']['orders']['most_popular_items']= array_slice($todayReport['munchado']['orders']['most_popular_items'],0,15);
                if (count($todayReport['munchado']['orders']['most_popular_items']) > 0) {
                     $totalSoldItem=0;
                    foreach ($todayReport['munchado']['orders']['most_popular_items'] as $key => $popItems) {
                    $totalSoldItem+=$popItems['total_items'];    
                    }
                    foreach ($todayReport['munchado']['orders']['most_popular_items'] as $key => $popItems) {
                        if($todayReport['munchado']['orders']['order_alltime']['total_orders']>0){
                        $todayReport['munchado']['orders']['most_popular_items'][$key]['percent'] = number_format($popItems['total_items'] / $totalSoldItem * 100,2);
                        }else{
                        $todayReport['munchado']['orders']['most_popular_items'][$key]['percent'] =0;
                        }
                        $todayReport['munchado']['orders']['most_popular_items'][$key]['item'] =  html_entity_decode($popItems['item']);
                    }
                }
                $overViewData['most_popular_items'] = $todayReport['munchado']['orders']['most_popular_items'];
                if($overViewData['total_orders']>0){
                $overViewData['chart_takeout'] = number_format($overViewData['order_takeout'] / $overViewData['total_orders'] * 100);
                $overViewData['chart_delivery'] = number_format($overViewData['order_delivery'] / $overViewData['total_orders'] * 100);
                }else{
                $overViewData['chart_takeout'] =0;
                $overViewData['chart_delivery'] =0;    
                }
                
                
                /*order end here*/
                foreach($overViewData as $key=>$val){ 
                    if($val<0){
                      $overViewData[$key]=0;  
                    }
                }
                    
                    
        
        
        
        $overViewData['traffic_array'] = $varVisitChartRecord;
        $overViewData['order_pattern'] = $varorderChartRecord;
        
        
        $overViewData['orderUpDown'] ="down_0";
        $overViewData['revenueUpDown'] = "down_0";
        $overViewData['peopleUpDown'] = "down_0";
        $overViewData['orderConversionUpDown'] ="down_0";
        $overViewData['gaUserUpDown'] = "down_0";
        $overViewData['visitUpDown'] = "down_0";
        $overViewData['pagepervisitUpDown'] = "down_0";
        $overViewData['pageviewsUpDown'] = "down_0";
        $overViewData['visitnewcustomerUpDown'] = "down_0";
        $overViewData['visitrepeatcustomerUpDown'] = "down_0";
        $overViewData['messagesentUpDown'] ="down_0";
         $overViewData['followersUpDown'] = "down_0";
         $overViewData['reviewUpDown'] = "down_0";
         $overViewData['avgTimePageUpDown'] = "down_0";
         $overViewData['avgordervalUpDown'] = "down_0";
         $overViewData['itemperorderUpDown'] = "down_0";
        $overViewData['orderNewuserUpDown'] = "down_0"; 
         $overViewData['orderrepeatuserUpDown'] = "down_0";
         $overViewData['emailsentUpDown'] = "down_0";
         $overViewData['fbfollowersUpDown'] = "down_0";
        $overViewData['fbpeoplereachedUpDown'] = "down_0";
        $overViewData['fbreviewcommentUpDown'] = "down_0";
        $overViewData['fbmessageUpDown'] = "down_0";
        $overViewData['fbmostcommentUpDown'] = "down_0";
        $overViewData['fbmostlikeUpDown'] = "down_0";
        $overViewData['instagramfollowersUpDown'] = "down_0";
        $overViewData['instagrampeoplereachedUpDown'] = "down_0";
        $overViewData['instagramcommentUpDown'] = "down_0";
        $overViewData['instagrammostcommentUpDown'] = "down_0";
        $overViewData['instagrammostlikeUpDown'] = "down_0";
        $overViewData['emailsentUpDown'] = "down_0";
        $overViewData['emailopenUpDown'] = "down_0";
        $overViewData['emailclickUpDown'] = "down_0";
        return $overViewData;
    }
    
    public static function getPercentVal($current = 0, $previous = 0) {
        if ($current > 0) {
            if ($current > $previous) {
                if ($previous < 0) {
                    return 'up_0';
                }
                //$num=(number_format(($current - $previous) * 100 / $current)==100)?0:number_format(($current - $previous) * 100 / $current);
                return 'up_' . number_format(($current - $previous) * 100 / $current);
            } else {
                //$num=(number_format(($previous - $current) * 100 / $previous)==100)?0:number_format(($previous - $current) * 100 / $previous);
                return 'down_' .number_format(($previous - $current) * 100 / $previous);;
            }
        } else if ($previous > 0) {
            //$num=(number_format(($previous - $current) * 100 / $previous)==100)?0:number_format(($previous - $current) * 100 / $previous);
            return 'down_' .number_format(($previous - $current) * 100 / $previous);;
        } else {
            return 'down_0';
        }
        //hide up down if want to run uncomment above code
        // return 'down_0';
    }

}